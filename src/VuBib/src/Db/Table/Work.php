<?php
/**
 * Table Definition for work.
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
use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;

/**
 * Table Definition for work.
 *
 * @category VuBib
 * @package  Code
 * @author   Falvey Library <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 *
 * @link https://
 */
class Work extends \Zend\Db\TableGateway\TableGateway
{
    /**
     * Work constructor.
     *
     * @param Adapter $adapter for db connection
     */
    public function __construct($adapter)
    {
        parent::__construct('work', $adapter);
    }

    /**
     * Fetch works by work type
     *
     * @param Integer $id work type id
     *
     * @return Paginator $paginatorAdapter work records
     */
    public function countRecordsByWorkType($id)
    {
        $select = $this->sql->select()->where(['type_id' => $id]);
        $paginatorAdapter = new DbSelect($select, $this->adapter);

        return new Paginator($paginatorAdapter);
    }

    /**
     * Update work type id
     *
     * @param Integer $id work type id
     *
     * @return empty
     */
    public function updateWorkTypeId($id)
    {
        $this->update(
            [
                'type_id' => null,
            ],
            ['type_id' => $id]
        );
    }

    /**
     * Find distinct initial letters of works.
     *
     * @return Array
     */
    public function findInitialLetter()
    {
        $callback = function ($select) {
            $select->columns(
                [
                'letter' => new Expression(
                    'substring(UPPER(?), 1, 1)',
                    ['title'],
                    [Expression::TYPE_IDENTIFIER]
                ),
                ]
            );
            $select->quantifier('DISTINCT');
            $select->order('letter');
        };

        return $this->select($callback)->toArray();
    }

    /**
     * Find distinct initial letters of works under review.
     *
     * @return Array
     */
    public function findInitialLetterReview()
    {
        $callback = function ($select) {
            $select->columns(
                [
                'letter' => new Expression(
                    'substring(UPPER(?), 1, 1)',
                    ['title'],
                    [Expression::TYPE_IDENTIFIER]
                ),
                ]
            );
            $select->where->equalTo('status', 0);
            $select->quantifier('DISTINCT');
            $select->order('letter');
        };

        return $this->select($callback)->toArray();
    }

    /**
     * Find distinct initial letters of works to be classified.
     *
     * @return Array
     */
    public function findInitialLetterClassify()
    {
        $wid = new Work_Folder($this->adapter);
        $subselect = $wid->getWorkFolder();

        $callback = function ($select) use ($subselect) {
            $select->columns(
                [
                'letter' => new Expression(
                    'substring(UPPER(?), 1, 1)',
                    ['title'],
                    [Expression::TYPE_IDENTIFIER]
                ),
                ]
            );
            $select->where->notIn('id', $subselect);
            $select->quantifier('DISTINCT');
            $select->order('letter');
        };

        return $this->select($callback)->toArray();
    }

    /**
     * Get works by work title letter
     *
     * @param string $letter starting letter of work title
     * @param string $order  order of records asc or desc
     *
     * @return Paginator $paginatorAdapter work records
     */
    public function displayRecordsByName($letter, $order)
    {
        $select = $this->sql->select();
        $select->where->like('title', $letter . '%')->or
            ->like('title', strtolower($letter) . '%');
        if (isset($order) && $order !== '') {
            $select->order($order);
        }

        $paginatorAdapter = new DbSelect($select, $this->adapter);

        return new Paginator($paginatorAdapter);
    }

    /**
     * Get review works by work title letter
     *
     * @param string $letter starting letter of work title
     * @param string $order  order of records asc or desc
     *
     * @return Paginator $paginatorAdapter work records
     */
    public function displayReviewRecordsByLetter($letter, $order)
    {
        $select = $this->sql->select();
        $select->where->like('title', $letter . '%')->or
            ->like('title', strtolower($letter) . '%');
        $select->where->equalTo('status', 0);
        if (isset($order) && $order !== '') {
            $select->order($order);
        }

        $paginatorAdapter = new DbSelect($select, $this->adapter);

        return new Paginator($paginatorAdapter);
    }

    /**
     * Get classify works by work title letter
     *
     * @param string $letter starting letter of work title
     * @param string $order  order of records asc or desc
     *
     * @return Paginator $paginatorAdapter work records
     */
    public function displayClassifyRecordsByLetter($letter, $order)
    {
        $wid = new Work_Folder($this->adapter);
        $subselect = $wid->getWorkFolder();

        $select = $this->sql->select();
        $select->where->notIn('id', $subselect);
        $select->where->like('title', $letter . '%')->or
            ->like('title', strtolower($letter) . '%');
        if (isset($order) && $order !== '') {
            $select->order($order);
        }

        $paginatorAdapter = new DbSelect($select, $this->adapter);

        return new Paginator($paginatorAdapter);
    }

    /**
     * Get works to be reviewed
     *
     * @param string $order order of records asc or desc
     *
     * @return Paginator $paginatorAdapter work records
     */
    public function fetchReviewRecords($order)
    {
        $select = $this->sql->select()->where(['status' => 0]);

        if (isset($order) && $order !== '') {
            $select->order($order);
        }

        $paginatorAdapter = new DbSelect($select, $this->adapter);

        return new Paginator($paginatorAdapter);
    }

    /**
     * Get works to be classified
     *
     * @param string $order order of records asc or desc
     *
     * @return Paginator $paginatorAdapter work records
     */
    public function fetchClassifyRecords($order)
    {
        $wid = new Work_Folder($this->adapter);
        $subselect = $wid->getWorkFolder();

        $select = $this->sql->select();
        $select->where->notIn('id', $subselect);

        if (isset($order) && $order !== '') {
            $select->order($order);
        }

        $paginatorAdapter = new DbSelect($select, $this->adapter);

        return new Paginator($paginatorAdapter);
    }

    /**
     * Find works based on work title
     *
     * @param string $title work title
     *
     * @return Paginator $paginatorAdapter work records
     */
    public function findRecords($title)
    {
        $select = $this->sql->select();
        $select->where->expression(
            'LOWER(title) LIKE ?', mb_strtolower($title) . '%'
        );
        //->where(['name' => $name]);
        $paginatorAdapter = new DbSelect($select, $this->adapter);

        return new Paginator($paginatorAdapter);
    }

    /**
     * Find work based on work id
     *
     * @param Integer $id work id
     *
     * @return Array $row work record
     */
    public function findRecordById($id)
    {
        $rowset = $this->select(['id' => $id]);
        $row = $rowset->current();

        return $row;
    }

    /**
     * Insert work record.
     *
     * @param Integer $pr_workid      parent work id
     * @param Integer $type_id        work type id
     * @param String  $title          work title
     * @param String  $subtitle       work sub title
     * @param String  $paralleltitle  work parallel title
     * @param String  $description    work description
     * @param Date    $create_date    work created date
     * @param Date    $create_user_id id of user who created work
     * @param Integer $status         status of work
     * @param Integer $pub_yrFrom     publisher start year
     *
     * @return empty
     */
    public function insertRecords($pr_workid, $type_id, $title, $subtitle,
        $paralleltitle, $description, $create_date, $create_user_id,
        $status, $pub_yrFrom
    ) {
        if ($status === '00') {
            $status = null;
        }
        $this->insert([
            'work_id' => ($pr_workid !== -1) ? $pr_workid : null,
            'type_id' => $type_id,
            'title' => $title,
            'subtitle' => $subtitle,
            'paralleltitle' => $paralleltitle,
            'description' => $description,
            'create_date' => $create_date,
            'create_user_id' => $create_user_id,
            'modify_date' => $create_date,
            'modify_user_id' => $create_user_id,
            'status' => $status,
            'publish_month' => null,
            'publish_year' => $pub_yrFrom[0],
        ]);
        $id = $this->getLastInsertValue();

        return $id;
    }

    /**
     * Delete work
     *
     * @param Integer $id work id
     *
     * @return empty
     */
    public function deleteRecordByWorkId($id)
    {
        $this->delete(['id' => $id]);
    }

    /**
     * Update work record.
     *
     * @param Integer $pr_workid     parent work id
     * @param Integer $id            work id
     * @param Integer $type_id       work type id
     * @param String  $title         work title
     * @param String  $subtitle      work sub title
     * @param String  $paralleltitle work parallel title
     * @param String  $desc          work description
     * @param Date    $modify_date   work created date
     * @param Date    $modify_user   id of user who created work
     * @param Integer $status        status of work
     * @param Integer $pub_yrFrom    publisher start year
     *
     * @return empty
     */
    public function updateRecords($pr_workid, $id, $type_id, $title, $subtitle,
        $paralleltitle, $desc, $modify_date, $modify_user, $status, $pub_yrFrom
    ) {
        if ($status === '00') {
            $status = null;
        }
        $this->update(
            [
            'work_id' => ($pr_workid !== -1) ? $pr_workid : null,
            'type_id' => $type_id,
            'title' => $title,
            'subtitle' => $subtitle,
            'paralleltitle' => $paralleltitle,
            'description' => $desc,
            'modify_date' => $modify_date,
            'modify_user_id' => $modify_user,
            'status' => $status,
            'publish_month' => null,
            'publish_year' => is_numeric($pub_yrFrom[0]) ? $pub_yrFrom[0] : null,
            ],
            ['id' => $id]
        );
    }

    /**
     * Fetch review works
     *
     * @return Array
     */
    public function getPendingReviewWorksCount()
    {
        $callback = function ($select) {
            $select->columns(
                [
                'review_count' => new Expression(
                    'Count(?)',
                    ['*'],
                    [Expression::TYPE_IDENTIFIER]
                ),
                ]
            );
            $select->where->equalTo('status', 0);
        };

        return $this->select($callback)->toArray();
    }

    /**
     * Works with title like given string.
     *
     * @param string $title part of title of work
     *
     * @return Array $rows work records as array
     */
    public function fetchParentLookup($title)
    {
        /*$callback = function ($select) use ($title) {
          $select->where->like('title', $title.'%');
        };
        $rows = $this->select($callback)->toArray();
        return $rows;*/
    }

    /**
     * Find agent records by limit,offset.
     *
     * @param integer $limit  limit the number of records to be fectched
     * @param integer $offset specify the offset to start fetching records
     *
     * @return Paginator $paginatorAdapter agent records as paginator
     */
    public function getWorkRecordsByLimitOffset($limit, $offset)
    {
        $callback = function ($select) use ($limit, $offset) {
            $select->limit($limit)->offset($offset);
        };
        $rows = $this->select($callback)->toArray();

        $arrayAdapter = new ArrayAdapter($rows);

        $paginator = new Paginator($arrayAdapter);

        return $paginator;
    }

    /**
     * AC suggestions from GetWorkDetailsAction
     *
     * @param string $query search query from
     *
     * @return Array $rows folder records
     */
    public function getSuggestions($title)
    {
        $callback = function ($select) use ($title) {
            $select->columns(['id', 'title']);
            $select->join('worktype', 'work.type_id = worktype.id', ['type']);
            $select->where->expression(
                'LOWER(title) LIKE ?',
                mb_strtolower($title) . '%'
            );
        };
        return $this->select($callback)->toArray();
    }
}
