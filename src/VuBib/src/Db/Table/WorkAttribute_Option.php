<?php
/**
 * Table Definition for workattribute_option.
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
use Zend\Db\Sql\Expression;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;

/**
 * Table Definition for workattribute_option.
 *
 * @category VuBib
 * @package  Code
 * @author   Falvey Library <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 *
 * @link https://
 */
class WorkAttribute_Option extends \Zend\Db\TableGateway\TableGateway
{
    /**
     * WorkAttribute_Option constructor.
     *
     * @param Adapter $adapter for db connection
     */
    public function __construct($adapter)
    {
        parent::__construct('workattribute_option', $adapter);
    }

    /**
     * Delete record
     *
     * @param Integer $wkat_id workattribute id
     *
     * @return empty
     */
    public function deleteWorkAttributeOptions($wkat_id)
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
     * Fetch attribute options record
     *
     * @param Integer $wkat_id workattribute id
     * @param string  $order   records order asc or descworkattribute
     *
     * @return Paginator $paginatorAdapter attribute options
     */
    public function displayAttributeOptions($wkat_id, $order="")
    {
        $select = $this->sql->select();
        $select->where->equalTo('workattribute_id', $wkat_id);

        if (isset($order) && $order !== '') {
            $select->order($order);
        }

        $paginatorAdapter = new DbSelect($select, $this->adapter);

        return new Paginator($paginatorAdapter);
    }

    /**
     * Add record
     *
     * @param Integer $wkat_id workattribute id
     * @param string  $title   workattribute option title
     * @param string  $val     workattribute option value
     *
     * @return empty
     */
    public function addOption($wkat_id, $title, $val="")
    {
        $this->insert(
            [
            'workattribute_id' => $wkat_id,
            'title' => $title,
            'value' => $val,
            ]
        );
    }

    /**
     * Insert attribute option record.
     *
     * @param String $attrId work attribute id
     * @param String $title  work attribute option title
     * @param String $val    work attribute option value
     *
     * @return int   $id id of newly inserted option record
     */
    public function insertOptionAndReturnId($attrId, $title, $val="")
    {
        $this->insert(
            [
            'workattribute_id' => $attrId,
            'title' => $title,
            'value' => $val,
            ]
        );

        $id = $this->getLastInsertValue();
        return $id;
    }

    /**
     * Fetch record by option id
     *
     * @param Integer $id workattribute option id
     *
     * @return Array $row attribute option record
     */
    public function findRecordById($id)
    {
        $rowset = $this->select(['id' => $id]);
        $row = $rowset->current();

        return $row;
    }

    /**
     * Update attribute option
     *
     * @param Integer $id    workattribute option id
     * @param string  $title workattribute option title
     * @param string  $val   workattribute option value
     *
     * @return empty
     */
    public function updateOption($id, $title, $val="")
    {
        $this->update(
            [
                'title' => $title,
                'value' => $val,
            ],
            ['id' => $id]
        );
    }

    /**
     * Delete attribute option
     *
     * @param Integer $wkat_id workattribute id
     * @param Integer $id      workattribute option id
     *
     * @return empty
     */
    public function deleteOption($wkat_id, $id)
    {
        $this->delete(
            [
                'workattribute_id' => $wkat_id,
                'id' => $id,
            ]
        );
    }

    /**
     * Fetch duplicate options of an attribute
     *
     * @param Integer $wkat_id workattribute id
     *
     * @return Paginator $paginatorAdapter attribute duplicate options
     */
    public function getDuplicateOptions($wkat_id)
    {
        $select = $this->sql->select();
        $select->where->equalTo('workattribute_id', $wkat_id);
        $select->group('title');
        $select->having('count(title) > 1');

        $paginatorAdapter = new DbSelect($select, $this->adapter);

        return new Paginator($paginatorAdapter);
    }

    /**
     * Fetch duplicate option records
     *
     * @param Integer $wkat_id          workattribute id
     * @param string  $option_dup_title workattribute option title
     * @param Integer $option_dup_id    workattribute option id
     *
     * @return Array $rows attribute duplicate option records
     */
    public function getDuplicateOptionRecords($wkat_id,
        $option_dup_title, $option_dup_id
    ) {
        $callback = function ($select) use (
            $wkat_id, $option_dup_title, $option_dup_id
        ) {
            $select->where->equalTo('title', $option_dup_title);
            $select->where->equalTo('workattribute_id', $wkat_id);
            $select->where->notEqualTo('id', $option_dup_id);
        };
        $rows = $this->select($callback)->toArray();

        return $rows;
    }

    /**
     * Get options of an attribute
     *
     * @param string  $opt_title workattribute option title
     * @param Integer $wkat_id   workattribute id
     *
     * @return Array $rows attribute options
     */
    public function getAttributeOptions($opt_title, $wkat_id)
    {
        $callback = function ($select) use ($opt_title, $wkat_id) {
            //$select->where->like('title', $opt_title.'%');
            $select->where->like(
                new Expression('LOWER(title)'),
                mb_strtolower($opt_title) . '%'
            );
            $select->where->equalTo('workattribute_id', $wkat_id);
        };
        $rows = $this->select($callback)->toArray();

        return $rows;
    }

    /**
     * Get option ids of an attribute
     *
     * @param Integer $wkat_id   workattribute id
     * @param string  $opt_title workattribute option title
     *
     * @return Array $rows attribute options
     */
    public function getOptionIds($wkat_id, $opt_title)
    {
        $rows = [];
        for ($i = 0; $i < count($wkat_id); ++$i) {
            $wkatid = $wkat_id[$i];
            $opttitle = $opt_title[$i];
            $callback = function ($select) use ($wkatid, $opttitle) {
                $select->where->equalTo('workattribute_id', $wkatid);
                $select->where->equalTo('title', $opttitle);
            };

            $rows = $rows + $this->select($callback)->toArray() + $rows;

            return $rows;
        }
    }

    /**
     * Get a particular option
     *
     * @param Integer $id      workattribute option id
     * @param Integer $wkat_id workattribute id
     *
     * @return Array $row attribute option
     */
    public function getOptionTitle($id, $wkat_id)
    {
        $rowset = $this->select(['id' => $id, 'workattribute_id' => $wkat_id]);
        $row = $rowset->current();

        return $row;
    }

    /**
     * Find attribute option by title
     *
     * @param string  $name    option title
     * @param Integer $wkat_id workattribute id
     *
     * @return Paginator $paginatorAdapter attribute option record
     */
    public function findRecords($name, $wkat_id)
    {
        $select = $this->sql->select();
        $select->where->like(
            new Expression('LOWER(title)'),
            '%' . mb_strtolower($name) . '%'
        )->and
            ->equalTo(
                'workattribute_id', $wkat_id
            );
        $paginatorAdapter = new DbSelect($select, $this->adapter);

        return new Paginator($paginatorAdapter);
    }

    /**
     * Get option Ids for an attribute
     *
     * @param Integer $wkat_id workattribute id
     *
     * @return Array $row attribute option Ids
     */
    public function getOptionIdsForAttribute($wkat_id)
    {
        $callback = function ($select) use ($wkat_id) {
            $select->columns(['id']);
            $select->where->equalTo('workattribute_id', $wkat_id);
        };

        $rows = array_column($this->select($callback)->toArray(), "id");
        return $rows;
    }
}
