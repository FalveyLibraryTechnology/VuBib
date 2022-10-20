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
use Laminas\Paginator\Paginator;

/**
 * Table Definition for workattribute.
 *
 * @category VuBib
 * @package  Code
 * @author   Falvey Library <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 *
 * @link https://
 */
class WorkAttribute_SubAttribute extends \Laminas\Db\TableGateway\TableGateway
{
    /**
     * WorkAttribute constructor.
     *
     * @param Adapter $adapter for db connection
     */
    public function __construct($adapter)
    {
        parent::__construct('workattribute_subattribute', $adapter);
    }

    /**
     * Add attribute
     *
     * @param Integer $wkattr_id work attribute id
     * @param string  $subattr   sub attribute name
     *
     * @return id newly inserted subattribute id
     */
    public function addSubAttributeReturnId($wkattr_id, $subattr)
    {
        $this->insert(
            [
            'workattribute_id' => $wkattr_id,
            'subattribute' => $subattr,
            ]
        );

        $id = $this->getLastInsertValue();
        return $id;
    }

    /**
     * Update record
     *
     * @param Integer $id      sub attribute id
     * @param Integer $attr_id workattribute id
     * @param string  $subattr sub attribute name
     *
     * @return empty
     */
    public function editSubAttribute($id, $attr_id, $subattr)
    {
        $this->update(
            [
                'subattribute' => $subattr,
            ],
            ['id' => $id, 'workattribute_id' => $attr_id]
        );
    }

    /**
     * Find record using workattribute id
     *
     * @param Integer $wkattr_id workattribute id
     *
     * @return Array $row sub atrribute
     */
    public function findRecordByAttributeId($wkattr_id)
    {
        $rowset = $this->select(['workattribute_id' => $wkattr_id]);
        $row = $rowset->current();

        return $row;
    }

    /**
     * Find record using workattribute id
     *
     * @param Integer $wkattr_id workattribute id
     *
     * @return Array $row sub atrribute
     */
    public function findRecordsByWorkAttrId($wkattr_id)
    {
        $rowset = $this->select(['workattribute_id' => $wkattr_id]);
        $row = $rowset->current();

        //return $rowset->toArray();
        return $row;
    }

    /**
     * Find record using id
     *
     * @param Integer $id id
     *
     * @return Array $row sub atrribute
     */
    public function findRecordById($id)
    {
        $rowset = $this->select(['id' => $id]);
        $row = $rowset->current();

        return $row;
    }

    /**
     * Fetch sub attribute records
     *
     * @param Integer $wkat_id workattribute id
     *
     * @return Paginator $paginatorAdapter sub attributes
     */
    public function displaySubAttributes($wkat_id)
    {
        $rowset = $this->select(['workattribute_id' => $wkat_id]);
        $row = $rowset->current();

        return $row;
    }

    /**
     * Delete record by id
     *
     * @param Integer $subattr_id id
     *
     * @return empty
     */
    public function deleteRecordById($subattr_id)
    {
        $this->delete(['id' => $subattr_id]);
    }
}
