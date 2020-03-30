<?php
/**
 * Table Definition for work_folder.
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
 * Table Definition for work_folder.
 *
 * @category VuBib
 * @package  Code
 * @author   Falvey Library <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 *
 * @link https://
 */
class Work_Folder extends \Zend\Db\TableGateway\TableGateway
{
    /**
     * Work_Folder constructor.
     *
     * @param Adapter $adapter for db connection
     */
    public function __construct($adapter)
    {
        parent::__construct('work_folder', $adapter);
    }

    /**
     * Fetch distinct work ids
     *
     * @return Array $workIds array of work ids
     */
    public function getWorkFolder()
    {
        $callback = function ($select) {
            $select->columns(
                [
                'work_id' => new Expression(
                    'DISTINCT(?)',
                    ['work_id'],
                    [Expression::TYPE_IDENTIFIER]
                ),
                ]
            );
        };

        $rows = $this->select($callback)->toArray();
        $workIds = [];
        foreach ($rows as $row) {
            $workIds[] = $row['work_id'];
        }

        return $workIds;
    }

    /**
     * Insert record
     *
     * @param Integer $wk_id     work id
     * @param Integer $folder_id folder ids
     *
     * @return empty
     */
    public function insertRecords($wk_id, $folder_id)
    {
        $this->insert(
            [
            'work_id' => $wk_id,
            'folder_id' => $folder_id,
            ]
        );
    }

    /**
     * Fetch record based on work id
     *
     * @param Integer $id work id
     *
     * @return Array $row work folder record
     */
    public function findRecordByWorkId($id)
    {
        $rowset = $this->select(['work_id' => $id]);
        $row = $rowset->current();

        return $row;
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
     * Fetch record based on folder id
     *
     * @param Integer $sid folder id
     *
     * @return Array $rows array of work ids
     */
    public function findRecordByFolderId($sid)
    {
        $callback = function ($select) use ($sid) {
            $select->columns(['work_id']);
            $select->where->equalTo('folder_id', $sid);
        };
        $rows = $this->select($callback)->toArray();

        return $rows;
    }

    /**
     * Delete record for folder merge
     *
     * @param Integer $sid folder id
     * @param Integer $did folder id
     *
     * @return empty
     */
    public function mergeWkFlDelete($sid, $did)
    {
        $wkfl = new self($this->adapter);
        $rows = array_map(
            function ($n) {
                return $n['work_id'];
            }, $wkfl->findRecordByFolderId($sid)
        );

        if (count($rows) >= 1) {
            $callback = function ($select) use ($did, $rows) {
                $select->where->in('work_id', $rows);
                $select->where->equalTo('folder_id', $did);
            };
            $this->delete($callback);
        }
    }

    /**
     * Update record for folder merge
     *
     * @param Integer $sid folder id
     * @param Integer $did folder id
     *
     * @return empty
     */
    public function mergeWkFlUpdate($sid, $did)
    {
        $this->update(
            [
                'folder_id' => $did,
            ],
            ['folder_id' => $sid]
        );
    }

    /**
     * Update record
     *
     * @param Integer $wk_id work id
     * @param Integer $fl_id folder id
     *
     * @return empty
     */
    public function updateRecords($wk_id, $fl_id)
    {
        $this->update(
            [
                'folder_id' => $fl_id,
            ],
            ['work_id' => $wk_id]
        );
    }

    /**
     * Insert record
     *
     * @param Integer $wk_id  work id
     * @param Array   $folder folder ids
     *
     * @return empty
     */
    public function insertWorkFolderRecords($wk_id, $folder)
    {
        for ($i = 0; $i < count($folder); ++$i) {
            if (empty($folder[$i]) && $folder[$i] !== '0') {
                continue;
            }
            $this->insert(
                [
                    'work_id' => $wk_id,
                    'folder_id' => $folder[$i],
                ]
            );
        }
    }

    /**
     * Fetch record based on work id
     *
     * @param Integer $id work id
     *
     * @return Array $rows array of records
     */
    public function findRecordsByWorkId($id)
    {
        $callback = function ($select) use ($id) {
            $select->columns(['*']);
            $select->where->equalTo('work_id', $id);
        };
        $rows = $this->select($callback)->toArray();

        return $rows;
    }

    /**
     * Fetch folders based on work id
     *
     * @param Integer $wk_id work id
     *
     * @return Array $rows array of records
     */
    public function getFoldersByWorkId($wk_id)
    {
        $flRows = [];
        $subselect = $this->sql->select();
        $subselect->join(
            'folder',
            'work_folder.folder_id = folder.id',
            ['*'], 'inner'
        );
        $subselect->where(['work_id' => $wk_id]);

        $paginatorAdapter = new Paginator(
            new DbSelect($subselect, $this->adapter)
        );
        $cnt = $paginatorAdapter->getTotalItemCount();
        if ($cnt > 0) {
            $paginatorAdapter->setDefaultItemCountPerPage($cnt);

            foreach ($paginatorAdapter as $row) {
                $flRows[] = $row;
            }
        }
        return $flRows;
    }
}
