<?php
/**
 * Table Definition for workattribute.
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

use Laminas\Db\Adapter\Adapter;

/**
 * Table Definition for attribute_option_subattribute.
 *
 * @category VuBib
 * @package  Code
 * @author   Falvey Library <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 *
 * @link https://
 */
class Attribute_Option_SubAttribute extends \Laminas\Db\TableGateway\TableGateway
{
    /**
     * WorkAttribute constructor.
     *
     * @param Adapter $adapter for db connection
     */
    public function __construct($adapter)
    {
        parent::__construct('attribute_option_subattribute', $adapter);
    }

    /**
     * Find record using workattribute id
     *
     * @param Integer $opt_id     workattribute option id
     * @param Integer $subattr_id sub attribute id
     *
     * @return Array $row sub atrribute
     */
    public function findRecordByOption($opt_id, $subattr_id, $includeNames = false)
    {
        $callback = function ($select) use ($opt_id, $subattr_id, $includeNames) {
            $select->where->equalTo('option_id', $opt_id);
            if (null !== $subattr_id) {
                $select->where->equalTo('subattribute_id', $subattr_id);
            }
            if ($includeNames) {
                $select->join(['ws' => 'workattribute_subattribute'], 'ws.id = attribute_option_subattribute.subattribute_id');
            }
        };

        $rowset = $this->select($callback);
        //$row = $rowset->current();

        //return $row;
        return $rowset->toArray();
    }

    /**
     * Add record
     *
     * @param string $wkat_id     work attribute id
     * @param string $opt_id      work attribute option id
     * @param string $subattr_id  sub attribute id
     * @param string $subattr_val value of sub atrribute
     *
     * @return id newly inserted subattribute id
     */
    public function insertRecord($wkat_id, $opt_id, $subattr_id, $subattr_val = "")
    {
        $this->insert(
            [
            'workattribute_id' => $wkat_id,
            'option_id' => $opt_id,
            'subattribute_id' => $subattr_id,
            'subattr_value' => $subattr_val,
            ]
        );
    }

    /**
     * Update record
     *
     * @param string $wkat_id     work attribute id
     * @param string $opt_id      work attribute option id
     * @param string $subattr_id  sub attribute id
     * @param string $subattr_val value of sub atrribute
     *
     * @return id newly inserted subattribute id
     */
    public function updateRecord($wkat_id, $opt_id, $subattr_id, $subattr_val = "")
    {
        $this->update(
            [
            'subattr_value' => $subattr_val,
            ],
            ['workattribute_id' => $wkat_id,'option_id' => $opt_id,
             'subattribute_id' => $subattr_id]
        );
    }

    /**
     * Delete attribute option
     *
     * @param string $wkat_id work attribute id
     * @param string $opt_id  work attribute option id
     *
     * @return empty
     */
    public function deleteRecordByOptionId($wkat_id, $opt_id)
    {
        $this->delete(
            [
                'workattribute_id' => $wkat_id,
                'option_id' => $opt_id,
            ]
        );
    }

    /**
     * Update record
     *
     * @param Integer $wkat_id       work attribute id
     * @param Integer $new_option_id new option id
     * @param Integer $old_option_id old option id
     *
     * @return empty
     */
    public function updateRecordOptionId($wkat_id, $new_option_id, $old_option_id)
    {
        $callback = function ($select) use ($wkat_id, $old_option_id) {
            $select->where->equalTo('workattribute_id', $wkat_id);
            $select->where->equalTo('option_id', $old_option_id);
        };
        $rows = $this->select($callback)->toArray();
        for ($i = 0; $i < count($rows); ++$i) {
            $this->update(
                [
                    'option_id' => $new_option_id,
                ],
                ['option_id' => $rows[$i]['option_id']]
            );
        }
    }

    /**
     * Delete record
     *
     * @param Integer $subat_id subattribute id
     *
     * @return empty
     */
    public function deleteRecordBySubAttributeId($subat_id)
    {
        $this->delete(
            [
                'subattribute_id' => $subat_id,
            ]
        );
    }
}
