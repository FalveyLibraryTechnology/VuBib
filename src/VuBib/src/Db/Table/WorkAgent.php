<?php
/**
 * Table Definition for work_agent.
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
 * Table Definition for work_agent.
 *
 * @category VuBib
 * @package  Code
 * @author   Falvey Library <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 *
 * @link https://
 */
class WorkAgent extends \Zend\Db\TableGateway\TableGateway
{
    /**
     * WorkAgent constructor.
     *
     * @param Adapter $adapter for db connection
     */
    public function __construct($adapter)
    {
        parent::__construct('work_agent', $adapter);
    }

    /**
     * Delete record by agentType id
     *
     * @param Integer $id agentType id
     *
     * @return empty
     */
    public function deleteRecordByAgentTypeId($id)
    {
        $this->delete(['agenttype_id' => $id]);
        //$this->tableGateway->delete(['id' => $id]);
    }

    /**
     * Delete record by agent id
     *
     * @param Integer $id agent id
     *
     * @return empty
     */
    public function deleteRecordByAgentId($id)
    {
        $this->delete(['agent_id' => $id]);
    }

    /**
     * Find records by agentType id
     *
     * @param Integer $id agentType id
     *
     * @return Paginator $paginatorAdapter work agent records
     */
    public function countRecordsByAgentType($id)
    {
        $select = $this->sql->select()->where(['agenttype_id' => $id]);
        $paginatorAdapter = new DbSelect($select, $this->adapter);

        return new Paginator($paginatorAdapter);
    }

    /**
     * Find records by agent id
     *
     * @param Integer $id agent id
     *
     * @return Paginator $paginatorAdapter work agent records
     */
    public function countRecordsByAgent($id)
    {
        $select = $this->sql->select()->where(['agent_id' => $id]);
        $paginatorAdapter = new DbSelect($select, $this->adapter);

        return new Paginator($paginatorAdapter);
    }

    /**
     * Insert records
     *
     * @param Integer $wk_id  work id
     * @param Integer $ag_id  agent id
     * @param Integer $agt_id agentType id
     *
     * @return empty
     */
    public function insertRecords($wk_id, $ag_id, $agt_id)
    {
        for ($i = 0; $i < count($ag_id); ++$i) {
            if ($ag_id[$i] != null && $agt_id[$i] != null) {
                $this->insert([
                    'work_id' => $wk_id,
                    'agent_id' => $ag_id[$i],
                    'agenttype_id' => $agt_id[$i],
                ]);
            }
        }
    }

    /**
     * Find records by work id
     *
     * @param Integer $wk_id work id
     *
     * @return Array $rows work agent records
     */
    public function findRecordByWorkId($wk_id)
    {
        $rows = [];
        $callback = function ($select) use ($wk_id) {
            $select->columns(['*']);
            $select->join(
                'agenttype', 'work_agent.agenttype_id = agenttype.id',
                ['type'], 'left'
            );
            $select->join(
                'agent', 'work_agent.agent_id = agent.id',
                ['fname', 'lname', 'alternate_name', 'organization_name'],
                'left'
            );
            $select->where(['work_id' => $wk_id]);
        };

        $rows = $this->select($callback)->toArray();

        return $rows;
    }

    /**
     * Delete records by work id
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
     * Find records by agent id
     *
     * @param Integer $ag_id agent id
     *
     * @return Array $rows work agent records
     */
    public function findRecordByAgentId($ag_id)
    {
        $callback = function ($select) use ($ag_id) {
            $select->columns(['*']);
            $select->where->equalTo('agent_id', $ag_id);
        };
        $rows = $this->select($callback)->toArray();

        return $rows;
    }

    /**
     * Update records by agent id
     *
     * @param Integer $src_ag_id agent id
     * @param Integer $dst_ag_id agent id
     *
     * @return empty
     */
    public function updateRecordByAgentId($src_ag_id, $dst_ag_id)
    {
        $this->update(
            [
                'agent_id' => $dst_ag_id,
            ],
            ['agent_id' => $src_ag_id]
        );
    }

    /**
     * Update records
     *
     * @param Integer $wk_id      work id
     * @param Integer $agent_id   agent id
     * @param Integer $agent_type agentType id
     *
     * @return empty
     */
    public function updateRecords($wk_id, $agent_id, $agent_type)
    {
        for ($i = 0; $i < count($agent_type); ++$i) {
            if (empty($agent_id[$i])) {
                $agent_id[$i] = 0;
            }
            $this->update(
                [
                'agent_id' => $agent_id[$i],
                'agenttype_id' => $agent_type[$i],
                ],
                ['work_id' => $wk_id]
            );
        }
    }
}
