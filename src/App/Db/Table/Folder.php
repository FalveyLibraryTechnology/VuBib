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

use Zend\Db\Sql\Select;
use Zend\Db\ResultSet\ResultSet;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Db\Adapter\Adapter;
use Zend\Paginator\Paginator;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Expression;

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
class Folder extends \Zend\Db\TableGateway\TableGateway
{
	//Private $file;
   /**
     * Constructor
     */
    public function __construct($adapter)
    {
        parent::__construct('folder', $adapter);				
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
    
    public function findParent()
    {
        $select = $this->sql->select()->where(['parent_id' => null]);
        $paginatorAdapter = new DbSelect($select, $this->adapter);
        return new Paginator($paginatorAdapter);
    }
	
	public function exportClassification($parent)
	{
		$fl = new Folder($this->adapter);
		$callback = function ($select) {
            $select->columns(['*']);
			$select->where('parent_id IS NULL');
		};
		$row = $this->select($callback)->toArray();
		$escaper = new \Zend\Escaper\Escaper('utf-8');
		header("Content-Type: text/csv");
		header("Content-Disposition: attachment; filename=test_export.csv");
		$file = fopen('php://output','w') or die("Unable to open file!");
		//add BOM to fix UTF-8 in Excel
		fputs($file, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) ));
		foreach($row as $t):
			$content = $t['id'] . ' ' . $escaper->escapeHtml($t['text_fr']) . ' ';
			fputcsv($file, array($content));
			$fl->getDepth($t['id'], $file, $content);				
		endforeach;
		fflush($file);
		fclose($file);
		exit;
	}
	
	public function getDepth($id, $file, $content)
	{
		$fl = new Folder($this->adapter);
		$escaper = new \Zend\Escaper\Escaper('utf-8');
		$con = $content;
		$current_parent_id = $id;
		$callback = function ($select) use ($current_parent_id){
			$select->columns(['*']);
			$select->where->equalTo('parent_id', $current_parent_id);
		};
		$rc = $this->select($callback)->toArray(); 
		if(count($rc) != 0) {				
			for($i = 0;$i<count($rc);$i++) {
				$con1 = ' ' . $escaper->escapeHtml($rc[$i]['text_fr']) . ' '; 
				//$con .= $con1;			
				fputcsv($file, array($con . $con1)); 
				$current_parent_id = $rc[$i]['id'];	
				$fl->getDepth($current_parent_id, $file, $con . $con1); 
			}	
		}
	}	
	
	public function getChild($parent)
	{
		$callback = function ($select) use ($parent){
			$select->columns(['*']);
			$select->where->equalTo('parent_id', $parent);
		};
		$rows = $this->select($callback)->toArray(); 
		//var_dump("count is " . count($rows));
		return $rows;
	}
	
	public function getParent($child)
	{
		$rowset = $this->select(array('id' => $child));
        $row = $rowset->current();
        return($row);
	}
	
	public function getHierarchyRecords($id)
	{
		echo 'id is ' . $id;
		$rowset = $this->select(array('id' => $id));
        $row = $rowset->current();
		
		/*$parent = $row['parent_id'];
		echo 'parent id is ' . $parent;*/
		
		/*$callback = function ($select) use ($parent){
			$select->columns(['*']);
			$select->where->equalTo('parent_id', $parent);
		};
		$rows = $this->select($callback)->toArray(); 
		echo "<pre>";print_r($rows);echo "</pre>";*/
		//return $row;
	}
	
	public function getTrail_old($id, $r)
	{
		echo 'id is: ' . $id . ' and r is: ' . $r;
		$fl = new Folder($this->adapter);
		$escaper = new \Zend\Escaper\Escaper('utf-8');
		$ts = array();
		$callback = function ($select) use ($id){
			$select->columns(['*']);
			$select->where->equalTo('id', $id);
		};
		$rc = $this->select($callback)->toArray();
		//echo '<pre>'; print_r($rc); echo '</pre>';
		//$ts = $fl->getParent($rc[0]['parent_id']);
		$r = $r . ":" . $rc[0]['text_fr'];

		if($rc[0]['parent_id'] != NULL)
		{
			$cid = $rc[0]['parent_id'];
			$fl->getTrail($cid,$r);
		}
		//echo 'r is ' . $r;
		//$str = $r;
		//echo 'str is ' . $r;
		return $r;
	}	
	
	public function getTrail($id,$r)
	{
		$str = "";
		$str = $r;
		$fl = new Folder($this->adapter);
		$callback = function ($select) use ($id) {
            $select->columns(['*']);
            $select->join(
                ['b' => 'folder'], 'folder.id = b.parent_id',
				['parent_id']
            );
			$select->where->equalTo('b.id', $id);			
        };
		$rc = $this->select($callback)->toArray();
		if(count($rc) == 0)
		{
			return $str . ':' . 'Top';						
		} 
		else
		{
			$r = $r . ":" . $rc[0]['text_fr'] . "*" . $rc[0]['id']; //top:Millieu
			$r = $fl->getTrail($rc[0]['parent_id'],$r); //getTrail( 1,top:Millieu:Biographie)
			return $r;
		}
	}
	
	public function findRecordById($id)
    {
        $rowset = $this->select(array('id' => $id));
        $row = $rowset->current();
        return($row);
    }
	
	public function insertRecords($parent_id, $text_en, $text_fr, $text_de, $text_nl, $text_es, $text_it, $sort_order)
    {
        $this->insert(
            [
				'parent_id' => $parent_id,
				'text_en' => $text_en,
                'text_fr' => $text_fr,
                'text_de' => $text_de,
                'text_nl' => $text_nl,
				'text_es' => $text_es,
				'text_it' => $text_it,
				'sort_order' => $sort_order,
            ]
        );
    }
	
	public function updateRecord($id, $text_en, $text_fr, $text_de, $text_nl, $text_es, $text_it, $sort_order)
    {
        $this->update(
            [
                'text_en' => $text_en,
                'text_fr' => $text_fr,
                'text_de' => $text_de,
                'text_nl' => $text_nl,
				'text_es' => $text_es,
				'text_it' => $text_it,
				'sort_order' => $sort_order,
            ],
            ['id' => $id]
        );
    }
}