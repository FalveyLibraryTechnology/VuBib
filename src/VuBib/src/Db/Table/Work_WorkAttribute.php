<?php
/**
 * Table Definition for work_workattribute.
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
 * @author   Falvey Library <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 *
 * @link https://
 */
namespace VuBib\Db\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;

/**
 * Table Definition for work_workattribute.
 *
 * @category VuBib
 * @package  Code
 * @author   Falvey Library <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 *
 * @link https://
 */
class Work_WorkAttribute extends \Zend\Db\TableGateway\TableGateway
{
    /**
     * Work_WorkAttribute constructor.
     *
     * @param Adapter $adapter for db connection
     */
    public function __construct($adapter)
    {
        parent::__construct('work_workattribute', $adapter);
    }

    /**
     * Delete record by work attribute id
     *
     * @param Integer $wkat_id work attribute id
     *
     * @return empty
     */
    public function deleteWorkAttributeFromWork($wkat_id)
    {
        $callback = function ($select) use ($wkat_id) {
            $select->where->equalTo('workattribute_id', $wkat_id);
        };
        $rows = $this->select($callback)->toArray();
        $cnt = count($rows);
        for ($i = 0; $i < $cnt; ++$i) {
            $this->delete($callback);
        }
    }

    /**
     * Find record by workattribute id and their options
     *
     * @param Integer $wkat_id work attribute id
     * @param Integer $id      work attribute option id
     *
     * @return Paginator $paginatorAdapter array of work folder records
     */
    public function countRecordsByAttributeOption($wkat_id, $id)
    {
        $select = $this->sql->select()->where(
            ['workattribute_id' => $wkat_id, 'value' => $id]
        );
        $paginatorAdapter = new DbSelect($select, $this->adapter);

        return new Paginator($paginatorAdapter);
    }

    /**
     * Delete record by work attribute id and value
     *
     * @param Integer $wkat_id work attribute id
     * @param Integer $val     value of work attribute
     *
     * @return empty
     */
    public function deleteRecordByValue($wkat_id, $val)
    {
        $this->delete(['workattribute_id' => $wkat_id, 'value' => $val]);
    }

    /**
     * Update record
     *
     * @param Integer $wkat_id         work attribute id
     * @param Integer $option_first_id new option value
     * @param Integer $val             value of work attribute
     *
     * @return empty
     */
    public function updateWorkAndWorkAttributeValue(
        $wkat_id, $option_first_id, $val
    ) {
        $callback = function ($select) use ($wkat_id, $val) {
            $select->where->equalTo('workattribute_id', $wkat_id);
            $select->where->equalTo('value', $val);
        };
        $rows = $this->select($callback)->toArray();
        for ($i = 0; $i < count($rows); ++$i) {
            $this->update(
                [
                    'value' => $option_first_id,
                ],
                ['value' => $rows[$i]['value']]
            );
        }
    }

    /**
     * Insert record
     *
     * @param Integer $wk_id      work attribute id
     * @param Integer $wkat_id    new option value
     * @param Integer $wkaopt_val work attribute option
     *
     * @return empty
     */
    public function insertRecords($wk_id, $wkat_id, $wkaopt_val)
    {
        for ($i = 0; $i < count($wkat_id); ++$i) {
            $wkatid = $wkat_id[$i];
            $wkaoptval = $wkaopt_val[$i];
            $this->insert(
                [
                'work_id' => $wk_id,
                'workattribute_id' => $wkatid,
                'value' => $wkaoptval,
                ]
            );
        }
    }

    /**
     * Delete record by work id
     *
     * @param Integer $id work id
     *
     * @return empty
     */
    public function deleteRecordByWorkId($id)
    {
        $this->delete(['work_id' => $id]);
    }

    /**
     * Find record by work id
     *
     * @param Integer $wk_id work id
     *
     * @return Array $rows array of work folder records
     */
    public function findRecordByWorkId($wk_id)
    {
        $callback = function ($select) use ($wk_id) {
            $select->columns(['*']);
            $select->where->equalTo('work_id', $wk_id);
        };
        $rows = $this->select($callback)->toArray();

        return $rows;
    }

    /**
     * Find work attribute name and type
     *
     * @param Integer $wk_id work id
     *
     * @return String $output json list of workattributes and their types
     */
    public function findWorkAttributeTypesByWorkId($wk_id)
    {
        $select = $this->sql->select();
        $select->join(
            'workattribute',
            'work_workattribute.workattribute_id = workattribute.id',
            ['field', 'type'], 'inner'
        );
        $select->where(['work_id' => $wk_id]);

        $paginatorAdapter = new Paginator(new DbSelect($select, $this->adapter));
        $cnt = $paginatorAdapter->getTotalItemCount();
        $paginatorAdapter->setDefaultItemCountPerPage($cnt);

        if ($cnt != 0) {
            foreach ($paginatorAdapter as $row) {
                $fieldRows[] = $row;
            }
        } else {
            $fieldRows = [];
        }
        $output = json_encode($fieldRows);

        return $output;
    }

    /**
     * Find work attribute record for work
     *
     * @param Integer $wk_id   work id
     * @param Integer $wkat_id work attribute id
     *
     * @return Array $row
     */
    public function getRecord($wk_id, $wkat_id)
    {
        $rowset = $this->select(
            ['work_id' => $wk_id,
            'workattribute_id' => $wkat_id]
        );
        $row = $rowset->current();

        return $row;
    }

    /**
     * Find attribute records
     *
     * @param Integer $wk_id     work id
     * @param string  $attribute work attribute
     *
     * @return Array $row
     */
    public function getAttributeValue($wk_id, $attribute, $detailed = false)
    {
        $wa = new WorkAttribute($this->adapter);
        $wa_row = $wa->getAttributeRecord($attribute);

        $attr_id = $wa_row['id'];
        $wk_wkat = $this->select(
            ['work_id' => $wk_id,
            'workattribute_id' => $attr_id]
        );
        $wk_wkat_row = $wk_wkat->current();

        if ($wa_row['type'] == 'Select') {
            if (isset($wk_wkat_row['value']) && $wk_wkat_row['value'] != '') {
                $wa_opt = new WorkAttribute_Option($this->adapter);
                $wa_opt_row = $wa_opt->findRecordById($wk_wkat_row['value']);
                if ($detailed) {
                    $subTable = new Attribute_Option_SubAttribute($this->adapter);
                    $sub = $subTable->findRecordByOption($wa_opt_row['id'], null, true);
                    return ['type' => 'Select', 'raw' => $wa_opt_row, 'sub_attribs' => $sub, 'display' => $wa_opt_row['title']];
                } else {
                    return  $wa_opt_row['title'];
                }
            }
        } else {
            return $detailed
                ? ['type' => $wa_row['type'], 'raw' => $wk_wkat_row, 'sub_attribs' => [], 'display' => $wk_wkat_row['value']]
                : $wk_wkat_row['value'];
        }
    }

    /**
     * Find work attribute option records using option and attribute id
     *
     * @param Integer $wkat_id work attribute id
     * @param Integer $opt_id  work attribute option id/value
     *
     * @return Array $rows array of work publisher records
     */
    public function findRecordByOptionId($wkat_id, $opt_id)
    {
        $callback = function ($select) use ($wkat_id, $opt_id) {
            $select->columns(['*']);
            $select->where->equalTo('workattribute_id', $wkat_id);
            $select->where->equalTo('value', $opt_id);
        };
        $rows = $this->select($callback)->toArray();

        return $rows;
    }
}
