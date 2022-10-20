<?php
/**
 * Table Definition for agenttype.
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
use Laminas\Paginator\Adapter\DbSelect;
use Laminas\Paginator\Paginator;

/**
 * Table Definition for agenttype.
 *
 * @category VuBib
 * @package  Code
 * @author   Falvey Library <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 *
 * @link https://
 */
class AgentType extends \Laminas\Db\TableGateway\TableGateway
{
    /**
     * AgentType constructor.
     *
     * @param Adapter $adapter for db connection
     */
    public function __construct($adapter)
    {
        parent::__construct('agenttype', $adapter);
    }

    /**
     * Insert agenttype record.
     *
     * @param String $type type of agent
     *
     * @return empty
     */
    public function insertRecords($type)
    {
        $this->insert(
            [
            'type' => $type,
            ]
        );
    }

    /**
     * Update agenttype record.
     *
     * @param Number $id   id of agenttype
     * @param String $type type of agent
     *
     * @return empty
     */
    public function updateRecord($id, $type)
    {
        $this->update(
            [
                'type' => $type,
            ],
            ['id' => $id]
        );
    }

    /**
     * Delete agent record.
     *
     * @param Number $id id of agenttype
     *
     * @return empty
     */
    public function deleteRecord($id)
    {
        $this->delete(['id' => $id]);
    }

    /**
     * Find agenttype record.
     *
     * @param Number $id id of agenttype
     *
     * @return Array $row agenttype record
     */
    public function findRecordById($id)
    {
        $rowset = $this->select(['id' => $id]);
        $row = $rowset->current();

        return $row;
    }

    /**
     * Fetch all agenttype records.
     *
     * @return Paginator $paginatorAdapter agent type records
     */
    public function fetchAgentTypes()
    {
        $select = $this->sql->select();
        $paginatorAdapter = new DbSelect($select, $this->adapter);

        return new Paginator($paginatorAdapter);
    }
}
