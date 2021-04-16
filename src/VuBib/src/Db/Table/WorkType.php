<?php
/**
 * Table Definition for worktype.
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
 * Table Definition for worktype.
 *
 * @category VuBib
 * @package  Code
 * @author   Falvey Library <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 *
 * @link https://
 */
class WorkType extends \Zend\Db\TableGateway\TableGateway
{
    use TranslationTrait;
    protected $translatable = ['type'];

    /**
     * WorkType constructor.
     *
     * @param Adapter $adapter for db connection
     */
    public function __construct($adapter)
    {
        $this->setTableName('worktype', 'type');
        parent::__construct('worktype', $adapter);
    }

    /**
     * Insert record
     *
     * @param string $type work type
     *
     * @return empty
     */
    public function insertRecords($type)
    {
        $this->insertTranslated($type, ['text_fr' => 'type']);
    }

    /**
     * Get records using worktype id
     *
     * @param Integer $id worktype id
     *
     * @return Array $row worktype record
     */
    public function findRecordById($id)
    {
        $callback = function ($select) use ($id) {
            $select->where(['worktype.id' => $id]);
            $this->joinTranslations($select);
        };
        $rowset = $this->select($callback);
        $row = $this->translateCurrent($rowset);

        return $row;
    }

    /**
     * Update record
     *
     * @param Integer $id   worktype id
     * @param string  $type worktype
     *
     * @return empty
     */
    public function updateRecord($id, $type)
    {
        $type['id'] = $id;
        $this->updateTranslated(
            $type,
            ['id' => $id],
            ['text_fr' => 'type']
        );
    }

    /**
     * Delete record
     *
     * @param Integer $id worktype id
     *
     * @return empty
     */
    public function deleteRecord($id)
    {
        $this->delete(['id' => $id]);
    }

    /**
     * Get all records
     *
     * @return Paginator $paginatorAdapter all worktype records
     */
    public function fetchAllWorkTypes()
    {
        $callback = function ($select) {
            $this->joinTranslations($select);
        };
        $rows = $this->select($callback);
        return $this->translatedArray($rows);
    }
}
