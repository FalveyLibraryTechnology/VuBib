<?php
/**
 * Table Definition for record
 *
 * PHP version 5
 *
 * Copyright (C) Villanova University 2010.
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
 * @category VuFind
 * @package  Db_Table
 * @author   Markus Beh <markus.beh@ub.uni-freiburg.de>
 * @author   Ere Maijala <ere.maijala@helsinki.fi>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org Main Site
 */
namespace App\Db\Table;
use Zend\Db\Sql\Predicate\Expression;
use Zend\Db\Sql\Where;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ConnectionInterface;
use Zend\Db\RowGateway\RowGateway;
use Zend\Db\ResultSet\ResultSet;
use Zend\Paginator\Adapter\DbTableGateway;
use Zend\Paginator\Paginator;

/**
 * Table Definition for record
 *
 * @category VuFind
 * @package  Db_Table
 * @author   Markus Beh <markus.beh@ub.uni-freiburg.de>
 * @author   Ere Maijala <ere.maijala@helsinki.fi>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org Main Site
 */
class TranslateLanguage extends \Zend\Db\TableGateway\TableGateway
{
    /**
     * Constructor
     */	
    public function __construct($adapter)
    {
        parent::__construct('translate_language', $adapter);
    }
    
    /**
     * Update an existing entry in the record table or create a new one
     *
     * @param string $id      Record ID
     * @param string $source  Data source
     * @param string $rawData Raw data from source
     *
     * @return Updated or newly added record
     */
    public function selectRecords($de1, $en1, $es1, $fr1, $it1, $nl1)
    {   	
	   $this->insert([
		'text_de'=> $de1,
		'text_en'=> $en1,
		'text_es'=> $es1,
		'text_fr'=> $fr1,
		'text_it'=> $it1,
		'text_nl'=> $nl1,
	    ]);
    }
    
    public function updateRecord($id, $de1, $en1, $es1, $fr1, $it1, $nl1)
    {   	
	  $this->update(array(
                'text_de'=> $de1,
                'text_en'=> $en1,
                'text_es'=> $es1,
                'text_fr'=> $fr1,
                'text_it'=> $it1,
                'text_nl'=> $nl1,),
                array('id' => $id)
                ); 
    } 
    
    public function deleteRecord($id) 
    {
        //echo $id;
        $this->delete(['id'=>$id]);
       //$this->tableGateway->delete(['id' => $id]);
    }
}