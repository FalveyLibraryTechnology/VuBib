<?php
/**
 * Table Definition for agent.
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
 * Table Definition for agent.
 *
 * @category VuBib
 * @package  Code
 * @author   Falvey Library <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 *
 * @link https://
 */
class Agent extends \Zend\Db\TableGateway\TableGateway
{
    private $_escaper;

    /**
     * Agent constructor.
     *
     * @param Adapter $adapter for db connection
     */
    public function __construct($adapter)
    {
        parent::__construct('agent', $adapter);
    }

    /**
     * Insert agent record.
     *
     * @param String $fname   first name of agent
     * @param String $lname   last name of agent
     * @param String $altname alternate name of agent
     * @param String $orgname organization name of agent
     * @param String $mail    email of agent
     *
     * @return empty
     */
    public function insertRecords($fname, $lname, $altname, $orgname, $mail)
    {
        $this->insert(
            [
            'fname' => $fname,
            'lname' => $lname,
            'alternate_name' => $altname,
            'organization_name' => $orgname,
            'email' => $mail,
            ]
        );
    }

    /**
     * Insert agent record.
     *
     * @param String $fname   first name of agent
     * @param String $lname   last name of agent
     * @param String $altname alternate name of agent
     * @param String $orgname organization name of agent
     * @param String $mail    email of agent
     *
     * @return int   $id id of newly inserted agent record
     */
    public function insertAgentAndReturnId($fname, $lname, $altname, $orgname, $mail)
    {
        $this->insert(
            [
            'fname' => $fname,
            'lname' => $lname,
            'alternate_name' => $altname,
            'organization_name' => $orgname,
            'email' => $mail,
            ]
        );
        $id = $this->getLastInsertValue();
        return $id;
    }

    /**
     * Update agent record.
     *
     * @param Number $id      id of agent
     * @param String $fname   first name of agent
     * @param String $lname   last name of agent
     * @param String $altname alternate name of agent
     * @param String $orgname organization name of agent
     * @param String $mail    email of agent
     *
     * @return empty
     */
    public function updateRecord($id, $fname, $lname, $altname, $orgname, $mail)
    {
        $this->update(
            [
                'fname' => $fname,
                'lname' => $lname,
                'alternate_name' => $altname,
                'organization_name' => $orgname,
                'email' => $mail,
            ],
            ['id' => $id]
        );
    }

    /**
     * Delete agent record.
     *
     * @param Number $id id of agent
     *
     * @return empty
     */
    public function deleteRecord($id)
    {
        //echo "id to del is " . $id . "<br />";
        $this->delete(['id' => $id]);
    }

    /**
     * Find agent record.
     *
     * @param Number $id id of agent
     *
     * @return Array $row agent record
     */
    public function findRecordById($id)
    {
        $rowset = $this->select(['id' => $id]);
        $row = $rowset->current();

        return $row;
    }

    /**
     * Find distinct initial letters of agents.
     *
     * @return Array
     */
    public function findInitialLetter()
    {
        $callback = function ($select) {
            $select->columns(
                [
                'letter' => new Expression(
                    'substring(?, 1, 1)',
                    ['fname'],
                    [Expression::TYPE_IDENTIFIER]
                ),
                ]
            );
            $select->quantifier('DISTINCT');
            $select->order('letter');
            //('fname ASC');
        };

        return $this->select($callback)->toArray();
    }

    /**
     * Find agent record by name.
     *
     * @param String $letter first letter of agent first name
     *
     * @return Paginator $paginatorAdapter agent records as paginator
     */
    public function displayRecordsByName($letter)
    {
        $select = $this->sql->select();
        $select->where->like('fname', $letter . '%');
        $paginatorAdapter = new DbSelect($select, $this->adapter);

        return new Paginator($paginatorAdapter);
    }

    /**
     * Search for an agent.
     *
     * @param string $name name of agent
     * @param string $type type of agent name
     *
     * @return Paginator $paginatorAdapter agent records as paginator
     */
    public function findRecords($name, $type)
    {
        $select = $this->sql->select();
        if ($type == 'fname') {
            $select->where->like(
                new Expression('LOWER(fname)'),
                mb_strtolower($name) . '%'
            );
        } elseif ($type == 'lname') {
            $select->where->like(
                new Expression('LOWER(lname)'),
                mb_strtolower($name) . '%'
            );
        } elseif ($type == 'altname') {
            $select->where->like(
                new Expression('LOWER(alternate_name)'),
                mb_strtolower($name) . '%'
            );
        } elseif ($type == 'orgname') {
            $select->where->like(
                new Expression('LOWER(organization_name)'),
                mb_strtolower($name) . '%'
            );
        }
        $paginatorAdapter = new DbSelect($select, $this->adapter);

        return new Paginator($paginatorAdapter);
    }

    /**
     * Agent records with last name like given string.
     *
     * @param string $name part of last name of agent
     *
     * @return Array $rows agent records as array
     */
    public function getLikeRecords($name)
    {
        $callback = function ($select) use ($name) {
            $select->where->like(
                new Expression('LOWER(lname)'),
                mb_strtolower($name) . '%'
            );
            //$select->where->expression('LOWER(lname) LIKE ?',
               //'%'.mb_strtolower($this->_escaper->escapeHtml($name)).'%');
        };
        $rows = $this->select($callback)->toArray();

        return $rows;
    }

    /**
     * Agent records with last name starting with given string.
     *
     * @param string $name initial part of last name of agent
     *
     * @return Array $rows agent records as array
     */
    public function getLastNameLikeRecords($name)
    {
        $callback = function ($select) use ($name) {
            $select->where->like('lname', $name . '%');
        };
        $rows = $this->select($callback)->toArray();

        return $rows;
    }

    /**
     * Find agent records by limit,offset.
     *
     * @param integer $limit  limit the number of records to be fectched
     * @param integer $offset specify the offset to start fetching records
     *
     * @return Paginator $paginatorAdapter agent records as paginator
     */
    public function getAgentRecordsByLimitOffset($limit, $offset)
    {
        $callback = function ($select) use ($limit, $offset) {
            $select->limit($limit)->offset($offset);
        };
        $rows = $this->select($callback)->toArray();

        $arrayAdapter = new ArrayAdapter($rows);

        $paginator = new Paginator($arrayAdapter);

        return $paginator;
    }
}
