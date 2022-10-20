<?php
/**
 * Table Definition for folder_merge_history.
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

/**
 * Table Definition for folder_merge_history.
 *
 * @category VuBib
 * @package  Code
 * @author   Falvey Library <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 *
 * @link https://
 */
class Folder_Merge_History extends \Laminas\Db\TableGateway\TableGateway
{
    /**
     * Folder_Merge_History constructor.
     *
     * @param Adapter $adapter for db connection
     */
    public function __construct($adapter)
    {
        parent::__construct('folder_merge_history', $adapter);
    }

    /**
     * Update folder merge history record.
     *
     * @param Number $sid id of source folder
     * @param Number $did id of destination folder
     *
     * @return empty
     */
    public function mergeFlMgHistUpdate($sid, $did)
    {
        $this->update(
            [
                'dest_folder_id' => $did,
            ],
            ['dest_folder_id' => $sid]
        );
    }

    /**
     * Insert folder merge history record.
     *
     * @param Number $sid id of source folder
     * @param Number $did id of destination folder
     *
     * @return empty
     */
    public function insertRecord($sid, $did)
    {
        $this->insert(
            [
            'source_folder_id' => $sid,
            'dest_folder_id' => $did,
            ]
        );
    }
}
