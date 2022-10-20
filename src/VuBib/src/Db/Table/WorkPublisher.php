<?php
/**
 * Table Definition for work_publisher.
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

use Laminas\Db\Sql\Expression;

/**
 * Table Definition for work_publisher.
 *
 * @category VuBib
 * @package  Code
 * @author   Falvey Library <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 *
 * @link https://
 */
class WorkPublisher extends \Laminas\Db\TableGateway\TableGateway
{
    /**
     * WorkPublisher constructor.
     *
     * @param Adapter $adapter for db connection
     */
    public function __construct($adapter)
    {
        parent::__construct('work_publisher', $adapter);
    }

    /**
     * Set publisher location null
     *
     * @param Integer $pub_id  publisher id
     * @param Array   $loc_ids publisher location ids
     *
     * @return empty
     */
    public function removePublisherLocations($pub_id, $loc_ids)
    {
        $callback = function ($select) use ($pub_id, $loc_ids) {
            $select->where->equalTo('publisher_id', $pub_id);
            $select->where->in('location_id', $loc_ids);
        };
        $this->update(['location_id' => null], $callback);
    }

    /**
     * Update publisher location id
     *
     * @param Integer $pub_id     publisher id
     * @param Array   $source_ids publisher location ids
     * @param Array   $dest_id    publisher location ids
     *
     * @return empty
     */
    public function updatePublisherLocationId($pub_id, $source_ids, $dest_id)
    {
        $callback = function ($select) use ($pub_id, $source_ids) {
            $select->where->equalTo('publisher_id', $pub_id);
            $select->where->in('location_id', $source_ids);
        };
        $this->update(['location_id' => $dest_id], $callback);
    }

    /**
     * Delete record
     *
     * @param Integer $pub_id publisher id
     *
     * @return empty
     */
    public function deleteRecordByPub($pub_id)
    {
        $this->delete(['publisher_id' => $pub_id]);
        //$this->tableGateway->delete(['id' => $id]);
    }

    /**
     * Find work publisher records
     *
     * @param Integer $id publisher id
     *
     * @return Array $rows array of work publisher records
     */
    public function findNoofWorks($id)
    {
        $callback = function ($select) use ($id) {
            $select->columns(
                [
                'cnt' => new Expression(
                    'COUNT(DISTINCT(?))', ['work_id'],
                    [Expression::TYPE_IDENTIFIER]
                ),
                ]
            );
            $select->where->equalTo('publisher_id', $id);
        };
        $rows = $this->select($callback)->toArray();

        return $rows;
    }

    /**
     * Insert record
     *
     * @param Integer $wk_id     work id
     * @param Integer $pub_id    publisher id
     * @param Integer $pub_locid publisher location id
     * @param Integer $pub_yr    publisher start year
     * @param Integer $pub_yrEnd publisher end year
     *
     * @return empty
     */
    public function insertRecords($wk_id, $pub_id, $pub_locid, $pub_yr, $pub_yrEnd)
    {
        for ($i = 0; $i < count($pub_id); ++$i) {
            if (empty($pub_id[$i]) && $pub_id[$i] != '0') {
                continue;
            }
            if (empty($pub_locid[$i])) {
                $pub_locid[$i] = null;
            }
            $this->insert(
                [
                'work_id' => $wk_id,
                'publisher_id' => $pub_id[$i],
                'location_id' => $pub_locid[$i],
                'publish_month' => 0,
                'publish_year' => (empty($pub_yr[$i])) ? null : $pub_yr[$i],
                'publish_month_end' => null,
                'publish_year_end' =>
                   (empty($pub_yrEnd[$i])) ? null : $pub_yrEnd[$i],
                ]
            );
        }
    }

    /**
     * Find work publisher records using work id
     *
     * @param Integer $wk_id work id
     *
     * @return Array $rows array of work publisher records
     */
    public function findRecordByWorkId($wk_id)
    {
        $callback = function ($select) use ($wk_id) {
            $select->columns(['*']);
            $select->join(
                'publisher',
                'work_publisher.publisher_id = publisher.id',
                ['name'], 'left'
            );
            $select->join(
                'publisher_location',
                'work_publisher.location_id = publisher_location.id',
                ['location'], 'left'
            );
            $select->where(['work_id' => $wk_id]);
        };
        $rows = $this->select($callback)->toArray();

        return $rows;
    }

    /**
     * Delete record
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
     * Find work publisher records using publisher id
     *
     * @param Integer $pub_id publisher id
     *
     * @return Array $rows array of work publisher records
     */
    public function findRecordByPublisherId($pub_id)
    {
        $callback = function ($select) use ($pub_id) {
            $select->columns(['*']);
            $select->where->equalTo('publisher_id', $pub_id);
        };
        $rows = $this->select($callback)->toArray();

        return $rows;
    }

    /**
     * Find work publisher records using publisher location id
     *
     * @param Integer $loc_id publisher location id
     *
     * @return Array $rows array of work publisher records
     */
    public function findRecordByLocationId($loc_id)
    {
        $callback = function ($select) use ($loc_id) {
            $select->columns(['*']);
            $select->where->equalTo('location_id', $loc_id);
        };
        $rows = $this->select($callback)->toArray();

        return $rows;
    }

    /**
     * Move publisher
     *
     * @param Integer $pub_src_id  publisher id
     * @param Integer $pub_dest_id publisher id
     * @param Integer $src_loc_id  publisher location id
     *
     * @return empty
     */
    public function movePublisher($pub_src_id, $pub_dest_id, $src_loc_id)
    {
        //update workpub set pubid=destpubid
        //where pubid=srcpubid and locid = $source_locid
        $this->update(
            [
                'publisher_id' => $pub_dest_id,
            ],
            ['publisher_id' => $pub_src_id, 'location_id' => $src_loc_id]
        );
    }

    /**
     * Merge publisher
     *
     * @param Integer $pub_src_id  publisher id
     * @param Integer $pub_dest_id publisher id
     * @param Integer $src_loc_id  publisher location id
     * @param Integer $dest_loc_id publisher location id
     *
     * @return empty
     */
    public function mergePublisher($pub_src_id, $pub_dest_id,
        $src_loc_id, $dest_loc_id
    ) {

        //update workpub set pubid=destpubid and locid=mrgpublocid
        //where pubid=srcpubid and locid=$source_locid
        $this->update(
            [
                'publisher_id' => $pub_dest_id,
                'location_id' =>  $dest_loc_id,
            ],
            ['publisher_id' => $pub_src_id, 'location_id' => $src_loc_id]
        );
    }
}
