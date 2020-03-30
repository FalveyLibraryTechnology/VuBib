<?php
/**
 * Table Definition for publisher.
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
 * Table Definition for publisher.
 *
 * @category VuBib
 * @package  Code
 * @author   Falvey Library <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 *
 * @link https://
 */
class Publisher extends \Zend\Db\TableGateway\TableGateway
{
    /**
     * Publisher constructor.
     *
     * @param Adapter $adapter for db connection
     */
    public function __construct($adapter)
    {
        parent::__construct('publisher', $adapter);
    }

    /**
     * Insert publisher record.
     *
     * @param String $name publisher name
     *
     * @return empty
     */
    public function insertRecords($name)
    {
        $this->insert(
            [
            'name' => $name,
            ]
        );
    }

    /**
     * Insert publisher record.
     *
     * @param String $name publisher name
     *
     * @return int $id last inserted publisher id
     */
    public function insertPublisherAndReturnId($name)
    {
        $this->insert(
            [
            'name' => $name,
            ]
        );

        $id = $this->getLastInsertValue();
        return $id;
    }

    /**
     * Find publisher by name
     *
     * @param string $name publisher name
     *
     * @return Paginator $paginatorAdapter publisher record
     */
    public function findRecords($name)
    {
        //$escaper = new \Zend\Escaper\Escaper('utf-8');
        $select = $this->sql->select();
        $select->where->like(
            new Expression('LOWER(name)'),
            mb_strtolower($name) . '%'
        );
        //$select->where->expression('LOWER(name) LIKE ?',
        //mb_strtolower($escaper->escapeHtml($name)).'%');
        $paginatorAdapter = new DbSelect($select, $this->adapter);

        return new Paginator($paginatorAdapter);
    }

    /**
     * Find publisher by id
     *
     * @param Number $id publisher id
     *
     * @return Array $row publisher record
     */
    public function findRecordById($id)
    {
        $rowset = $this->select(['id' => $id]);
        $row = $rowset->current();

        return $row;
    }

    /**
     * Update publisher record.
     *
     * @param Number $id   publisher id
     * @param String $name publisher name
     *
     * @return empty
     */
    public function updateRecord($id, $name)
    {
        $this->update(
            [
                'name' => $name,
            ],
            ['id' => $id]
        );
    }

    /**
     * Delete publisher
     *
     * @param Number $id publisher id
     *
     * @return empty
     */
    public function deleteRecord($id)
    {
        $this->delete(['id' => $id]);
        //$this->tableGateway->delete(['id' => $id]);
    }

    /**
     * Find distinct initial letters of publishers.
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
                    ['name'],
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
     * Get records with publisher name like given string
     *
     * @param string $letter starting letter of publisher name
     *
     * @return Paginator $paginatorAdapter publisher records
     */
    public function displayRecordsByName($letter)
    {
        $select = $this->sql->select();
        $select->where->like('name', $letter . '%');
        $paginatorAdapter = new DbSelect($select, $this->adapter);

        return new Paginator($paginatorAdapter);
    }

    /**
     * Publisher records with name like given string.
     *
     * @param string $name part of name of publisher
     *
     * @return Array $rows publisher records as array
     */
    public function getLikeRecords($name)
    {
        throw new \Error('Publisher::getLikeRecords');
        $callback = function ($select) use ($name) {
            $select->where->like(
                new Expression('LOWER(name)'),
                mb_strtolower($name) . '%'
            );
            //$select->where->like('name', '%'.$name.'%');
            $select->order('name');
        };
        $rows = $this->select($callback)->toArray();

        return $rows;
    }

    /**
     * AC suggestions from GetWorkDetailsAction
     *
     * @param string $query search query from
     *
     * @return Array $rows folder records
     */
    public function getSuggestions($query)
    {
        $callback = function ($select) use ($query) {
            $select->where->like(
                new Expression('LOWER(name)'),
                mb_strtolower($query) . '%'
            );
            //$select->where->like('name', '%'.$name.'%');
            $select->order('name');
        };
        $rows = $this->select($callback)->toArray();

        return $rows;
        foreach ($rows as $i => $row) {
            $rows[$i]['value'] = $row['name'];
            $rows[$i]['label'] = $row['name'];
            $rows[$i]['id'] = $row['id'];
        }
        return $rows;
    }
}
