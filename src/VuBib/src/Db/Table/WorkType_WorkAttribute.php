<?php
/**
 * Table Definition for worktype_workattribute.
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
use Laminas\Db\Sql\Expression;
use Laminas\Paginator\Adapter\DbSelect;
use Laminas\Paginator\Paginator;

/**
 * Table Definition for worktype_workattribute.
 *
 * @category VuBib
 * @package  Code
 * @author   Falvey Library <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 *
 * @link https://
 */
class WorkType_WorkAttribute extends \Laminas\Db\TableGateway\TableGateway
{
    /**
     * WorkType_WorkAttribute constructor.
     *
     * @param Adapter $adapter for db connection
     */
    public function __construct($adapter)
    {
        parent::__construct('worktype_workattribute', $adapter);
    }

    /**
     * Get records ordered by rank
     *
     * @param Integer $id worktype id
     *
     * @return Paginator $paginatorAdapter worktype workattribute records
     */
    public function displayRanks($id)
    {
        $select = $this->sql->select();
        $select->join(
            'workattribute',
            'worktype_workattribute.workattribute_id = workattribute.id',
            ['field'], 'inner'
        );
        $select->where(['worktype_id' => $id]);
        $select->order('rank');

        $paginatorAdapter = new Paginator(new DbSelect($select, $this->adapter));
        //$cnt = $paginatorAdapter->getTotalItemCount();

        return $paginatorAdapter;
    }

    /**
     * Get records along with workattribute field
     *
     * @param Integer $id worktype id
     *
     * @return Paginator $paginatorAdapter worktype workattribute records
     */
    public function getWorkAttributeQuery($id)
    {
        $subselect = $this->sql->select();
        $subselect->join(
            'workattribute',
            'worktype_workattribute.workattribute_id = workattribute.id',
            ['field'], 'inner'
        );
        $subselect->where(['worktype_id' => $id]);
        $subselect->order('rank');

        $paginatorAdapter = new Paginator(new DbSelect($subselect, $this->adapter));
        $cnt = $paginatorAdapter->getTotalItemCount();

        $paginatorAdapter->setDefaultItemCountPerPage($cnt);
        $fieldRows = [];
        foreach ($paginatorAdapter as $row) {
            $fieldRows[] = $row['field'];
        }

        return $fieldRows;
    }

    /**
     * Add records
     *
     * @param Integer $wkt_id   worktype id
     * @param Array   $wkat_ids workattribute ids
     *
     * @return empty
     */
    public function addAttributeToWorkType($wkt_id, $wkat_ids)
    {
        $cnt = count($wkat_ids);
        for ($i = 0; $i < $cnt; ++$i) {
            $this->insert(
                [
                'worktype_id' => $wkt_id,
                'workattribute_id' => $wkat_ids[$i],
                'rank' => new Expression('999' . $i),
                ]
            );
        }
    }

    /**
     * Delete records
     *
     * @param Integer $wkt_id   worktype id
     * @param Array   $wkat_ids workattribute ids
     *
     * @return empty
     */
    public function deleteAttributeFromWorkType($wkt_id, $wkat_ids)
    {
        $callback = function ($select) use ($wkt_id, $wkat_ids) {
            $select->where->in('workattribute_id', $wkat_ids);
            $select->where->equalTo('worktype_id', $wkt_id);
            //$select->order('rank');
        };
        $rows = $this->select($callback)->toArray();
        $cnt = count($rows);
        for ($i = 0; $i < $cnt; ++$i) {
            $this->delete($callback);
        }
    }

    /**
     * Update records
     *
     * @param Integer $wkt_id  worktype id
     * @param Array   $wkatids workattribute ids
     *
     * @return empty
     */
    public function updateWorkTypeAttributeRank($wkt_id, $wkatids)
    {
        $wkat_ids = explode(',', $wkatids);
        foreach ($wkat_ids as $id) {
            $sort_wkatids[] = (int)preg_replace("/^\w{2,3}_/", '', $id);
        }
        $callback = function ($select) use ($wkt_id) {
            $select->where->equalTo('worktype_id', $wkt_id);
            $select->order('rank');
        };
        $rows = $this->select($callback)->toArray();
        $cnt = count($rows);
        //to avoid primary key conflicts, set records rank wise first
        for ($i = 0; $i < $cnt; ++$i) {
            $this->update(
                [
                'rank' => new Expression('1999' . $i),
                ],
                ['workattribute_id' => $sort_wkatids[$i]]
            );
        }
        //update ranks
        for ($i = 0; $i < $cnt; ++$i) {
            $this->update(
                [
                'rank' => $i,
                ],
                ['workattribute_id' => $sort_wkatids[$i]]
            );
        }
    }

    /**
     * Get records
     *
     * @param Integer $wkat_id workattribute id
     *
     * @return Array
     */
    public function countWorkTypesByWorkAttributes($wkat_id)
    {
        $callback = function ($select) use ($wkat_id) {
            $select->columns(
                [
                'count_worktypes' => new Expression(
                    'COUNT(?)', ['worktype_id'],
                    [Expression::TYPE_IDENTIFIER]
                ),
                ]
            );
            $select->where->equalTo('workattribute_id', $wkat_id);
        };

        return $this->select($callback)->toArray();
    }

    /**
     * Delete record
     *
     * @param Integer $wkt_id worktype id
     *
     * @return empty
     */
    public function deleteRecordByWorkType($wkt_id)
    {
        $callback = function ($select) use ($wkt_id) {
            $select->where->equalTo('worktype_id', $wkt_id);
        };
        $rows = $this->select($callback)->toArray();
        $cnt = count($rows);
        for ($i = 0; $i < $cnt; ++$i) {
            $this->delete($callback);
        }
    }

    /**
     * Delete record
     *
     * @param Array $wkat_id workattribute ids
     *
     * @return empty
     */
    public function deleteAttributeFromAllWorkTypes($wkat_id)
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
     * Find record
     *
     * @param Array $wkt_id worktype id
     *
     * @return Array $rows array of records
     */
    public function findRecordById($wkt_id)
    {
        $callback = function ($select) use ($wkt_id) {
            $select->columns(['*']);
            $select->where->equalTo('worktype_id', $wkt_id);
        };
        $rows = $this->select($callback)->toArray();

        return $rows;
    }
}
