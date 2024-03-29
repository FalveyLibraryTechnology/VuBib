<?php
/**
 * Table Definition for worktype.
 *
 * PHP version 5
 *
 * Copyright (C) Villanova University 2017.
 * Copyright (C) University of Freiburg 2014.
 * Copyright (C) The National Library of Finland 2015.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 2,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category VuBib
 * @package  Code
 * @author   Falvey Library <challber@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 *
 * @link https://
 */
namespace VuBib\Db\Table;

use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Join;
use Laminas\Db\Sql\Select;
use Laminas\Db\TableGateway\TableGateway;

trait TranslationTrait
{
    protected $fallbackColumn = '';

    protected function setDefaultTranslationColumn($fbCol = 'text_fr')
    {
        $this->fallbackColumn = $fbCol;
    }

    protected function separateRowTranslations($row, $cols)
    {
        $vals = [];
        foreach ($cols as $key => $col) {
            if (is_numeric($key)) {
                $key = $col;
            }
            $vals[$col] = $row[$key];
        }

        $trans = [];
        $defaultTrans = '[' . trim($row['text_fr'] ?? $row['text_en'], '[]') . ']';
        foreach ($row as $col => $val) {
            if (substr($col, 0, 5) == 'text_') {
                $lang = substr($col, 5);
                $trans[$lang] = $val ?? $defaultTrans;
            }
        }

        return ['values' => $vals, 'trans' => $trans];
    }

    protected function insertTranslated($row, $cols)
    {
        $separated = $this->separateRowTranslations($row, $cols);

        $this->insert($separated['values']);

        $transTable = new TableGateway('translations', $this->adapter);
        foreach($separated['trans'] as $lang => $text) {
            $transTable->insert([
                'id' => $this->lastInsertValue,
                'table' => $this->getTable(),
                'lang' => $lang,
                'text' => $text
            ]);
        }
    }

    protected function updateTranslated($row, $index, $cols)
    {
        if (!isset($row['id'])) {
            throw \Exception(
                'Translation of ' . $this->getTable()
                    . ': "id" needed to update translated item'
            );
        }

        $separated = $this->separateRowTranslations($row, $cols);

        $this->update($separated['values'], $index);

        $transTable = new TableGateway('translations', $this->adapter);
        foreach($separated['trans'] as $lang => $text) {
            $rowsAffected = $transTable->update(
                ['text' => $text],
                ['table' => $this->getTable(), 'id' => $row['id'], 'lang' => $lang]
            );
            // No rows affect? Check for existing entry
            if ($rowsAffected == 0) {
                $checkSet = $transTable->select([
                    'id' => $row['id'],
                    'table' => $this->getTable(),
                    'lang' => $lang
                ])->toArray();
                // insert if absent
                if (empty($checkSet)) {
                    $transTable->insert([
                        'id' => $row['id'],
                        'text' => $text,
                        'table' => $this->getTable(),
                        'lang' => $lang
                    ]);
                }
            }
        }
    }

    protected function joinTranslations(Select $select): Select
    {
        return $select->join(
            ['t' => 'translations'],
            new Expression(
                't.id = ' . $this->getTable() . '.id AND '
                . "t.table = '" . $this->getTable() . "'"
            ),
            ['t__lang' => 'lang', 't__text' => 'text'],
            Join::JOIN_LEFT
        );
    }

    protected function getTranslations($row)
    {
        $transTable = new TableGateway('translations', $this->adapter);
        $rows = $transTable->select([
                'id' => $row['id'],
                'table' => $this->getTable(),
            ])->toArray();

        $trans = [];
        foreach ($rows as $row) {
            $trans[$row['lang']] = $row['text'];
        }
        return $trans;
    }

    protected function getTranslation($row, $lang)
    {
        $trans = $this->getTranslations($row);
        return $trans[$lang];
    }

    private function transformRow($row)
    {
        $trans = $this->getTranslations($row);
        foreach ($this->translatable as $attr) {
            $row[$attr] = $trans;
        }
        return $row;
    }

    protected function translate($op)
    {
        if (!isset($this->translatable) || empty($this->translatable)) {
            return $op;
        }
        if (!is_array($op)) {
            return $this->transformRow($op);
        }
        $rows = [];
        foreach ($op as $row) {
            $rows[] = $this->transformRow($row);
        }
        return $rows;
    }

    /**
     * @return object|null
     */
    protected function translateCurrent(ResultSet $rowset)
    {
        $rowset->buffer();
        $ret = $rowset->current();
        $rows = !empty($ret) ? $rowset->toArray() : [];
        foreach ($rows as $row) {
            $ret['text_' . $row['t__lang']] = $row['t__text'];
        }
        unset($ret['t__lang']);
        unset($ret['t__text']);
        return $ret;
    }

    protected function translatedArray(ResultSet $rowset): array
    {
        $rows = $rowset->toArray();
        $ret = [];

        foreach ($rows as $row) {
            $id = $row['id'];

            // Missing lang keys check
            if (!array_key_exists('t__lang', $row)) {
                throw new \Exception(
                    'Calling translatedArray without joinTranslations'
                );
            }

            // NULL t__lang, this shouldn't happen
            if ($row['t__lang'] == NULL) {
                error_log(
                    'Empty t__lang (no translations for current row) ' .
                    print_r($row, true)
                );
                // TODO: DEBUG DO NOT SHIP
                $row['t__lang'] = 'fr';
                $row['t__text'] = '[' . $row[$this->fallbackColumn] . ']';
            }

            // Brackets for borrowed strings
            if ($row['t__text'] == NULL) {
                if ($row[$this->fallbackColumn] != NULL) {
                    $row['t__text'] = '[' . $row[$this->fallbackColumn] . ']';
                } else {
                    foreach (['fr', 'en', 'it'] as $lang) {
                        if (isset($row['text_' . $lang])) {
                            $row['t__text'] = '[' . $row['text_' . $lang] . ']';
                            break;
                        }
                    }
                }
            }

            // First encounter with new id
            if (!isset($ret[$id])) {
                $ret[$id] = $row;
                unset($ret[$id]['t__lang']);
                unset($ret[$id]['t__text']);
            }

            $ret[$id]['text_' . $row['t__lang']] = $row['t__text'];
        }
        return array_values($ret);
    }
}
