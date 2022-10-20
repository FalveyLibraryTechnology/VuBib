<?php
/**
 *
 * Copyright (C) Villanova University 2007.
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
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 */
namespace VuBib\Indexer;

use ArrayObject;
use VuBib\Db\Table\Agent;
use VuBib\Db\Table\Folder;
use VuBib\Db\Table\Work;
use VuBib\Db\Table\Work_Folder;
use VuBib\Db\Table\Work_WorkAttribute;
use VuBib\Db\Table\WorkAgent;
use VuBib\Db\Table\WorkPublisher;
use VuBib\Db\Table\WorkType;
use Laminas\Paginator\Paginator;
use Laminas\Paginator\Adapter\DbTableGateway;

class VuFindIndexer
{
    use ConsoleWriterTrait;

    protected $work;      // database connection
    protected $folder;
    protected $agent;
    protected $work_folder;
    protected $work_agent;
    protected $work_type;
    protected $work_publisher;
    protected $work_workattribute;
    protected $folderTops = [];
    protected $folderTitles = [];
    protected $solr;      // index connection

    // Statistics:
    protected $success = 0;
    protected $failure = 0;
    protected $total = 0;

    // Containers for information about the current work in progress:
    protected $currentRecord;
    protected $allFields;

    // List of languages we use for indexing:
    protected $languages = ['en', 'fr', 'de', 'nl', 'es', 'it'];

    public function __construct($solr, $adapter)
    {
        $this->solr = $solr;
        $this->folder = new Folder($adapter);
        $this->agent= new Agent($adapter);
        $this->work = new Work($adapter);
        $this->work_folder = new Work_Folder($adapter);
        $this->work_agent = new WorkAgent($adapter);
        $this->work_type = new WorkType($adapter);
        $this->work_publisher = new WorkPublisher($adapter);
        $this->work_workattribute = new Work_WorkAttribute($adapter);
        //$this->work->whereAdd('status in (1,0)');
    }

    public function getSuccess()
    {
        return $this->success;
    }

    public function getFailure()
    {
        return $this->failure;
    }

    public function getTotal()
    {
        return $this->total;
    }

    public function indexAll($type = null, $offset = null, $limit = null, $commit = true)
    {
        $changesIndexed = false;

        if ((!$type || $type === 'folder')) {
            //Get folder records
            if ($offset !== null && $limit !== null) {
                $paginator = $this->folder->getFolderRecordsByLimitOffset($limit, $offset);
            } else {
                $paginator = new Paginator(new DbTableGateway($this->folder));
            }
            $fl_cnt = $paginator->getTotalItemCount();
            $fl_pg_cnt = $paginator->count();
            if ($fl_cnt > 0) {
                $pg = 1;
                while ($pg <= $fl_pg_cnt) {
                    $paginator->setCurrentPageNumber($pg);
                    foreach ($paginator as $row) {
                        $this->processCurrentFolder($row);
                    }
                    //$changesIndexed = true;
                    $pg += 1;
                }
                $changesIndexed = true;
            }
        }

        if ((!$type || $type === 'agent')) {
            //Get agent records
            if ($offset !== null && $limit !== null) {
                $ag_paginator = $this->agent->getAgentRecordsByLimitOffset($limit, $offset);
            } else {
                $ag_paginator = new Paginator(new DbTableGateway($this->agent));
            }
            $ag_cnt = $ag_paginator->getTotalItemCount();
            $ag_pg_cnt = $ag_paginator->count();
            if ($ag_cnt > 0) {
                $pg = 1;
                while ($pg <= $ag_pg_cnt) {
                    $ag_paginator->setCurrentPageNumber($pg);
                    foreach ($ag_paginator as $row) {
                        $this->processCurrentAgent($row);
                    }
                    $pg += 1;
                }
                $changesIndexed = true;
            }
        }
        if ((!$type || $type === 'work')) {
            //Get work records
            if ($offset !== null && $limit !== null) {
                $wk_paginator = $this->work->getWorkRecordsByLimitOffset($limit, $offset);
            } else {
                $wk_paginator = new Paginator(new DbTableGateway($this->work));
            }
            $wk_cnt = $wk_paginator->getTotalItemCount();
            $wk_pg_cnt = $wk_paginator->count();
            if ($wk_cnt > 0) {
                // If we skipped the folder section above, we're missing some key
                // information that needs to be loaded now!
                if (empty($this->folderTops)) {
                    $this->writeLine('Loading folder details...');
                    $fl_paginator = new Paginator(new DbTableGateway($this->folder));
                    $fl_cnt = $fl_paginator->getTotalItemCount();
                    $fl_pg_cnt = $fl_paginator->count();
                    if ($fl_cnt > 0) {
                        $pg = 1;
                        while ($pg <= $fl_pg_cnt) {
                            $fl_paginator->setCurrentPageNumber($pg);
                            foreach ($fl_paginator as $row) {
                                $this->fl = $row;
                                $this->folderTitles[$this->fl->id] = $this->getBestTitle($this->fl);
                                $parents = $this->folder->getParentChainRecord($this->fl->id);
                                if (isset($parents[0])) {
                                    $topFolder = $parents[count($parents) - 1];
                                    $this->folderTops[$this->fl->id] = $topFolder->id;
                                } else {
                                    $this->folderTops[$this->fl->id] = $this->fl->id;
                                }
                            }
                            $pg += 1;
                        }
                    }
                }
                $pg = 1;
                while ($pg <= $wk_pg_cnt) {
                    $wk_paginator->setCurrentPageNumber($pg);
                    foreach ($wk_paginator as $row) {
                      $this->processCurrentWork($row);
                    }
                    $pg += 1;
                }
                $changesIndexed = true;
            }
        }
    }

    protected function startNewRecord()
    {
        // Create Record
        $this->currentRecord = '<?xml version="1.0" encoding="utf-8"?>' . "\n";
        $this->currentRecord .= '<add>' . "\n";
        $this->currentRecord .= '  <doc>' . "\n";

        // Initialize "all fields" list:
        $this->allFields = [];
    }

    protected function addFieldToRecord($name, $values, $saveToAllFields = true)
    {
        $values = (array)$values;
        foreach ($values as $value) {
            // Normalize whitespace, do not index empty values:
            $value = trim($value);
            if (strlen($value) == 0) {
                continue;
            }

            // Sanitize the value for XML legality:
            $regex = '/[^\x{0009}\x{000a}\x{000d}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]+/u';
            $value = trim(preg_replace($regex, ' ', $value, -1));

            // Add the field:
            foreach ((array)$name as $field) {
                $this->currentRecord .= '    <field name="' . $field . '">' .
                    htmlspecialchars($value) . '</field>' . "\n";
            }

            // If requested, save the value into the "all fields" list:
            if ($saveToAllFields) {
                $this->allFields[] = $value;
            }
        }
    }

    protected function endCurrentRecord()
    {
        // Put in the "all fields" list as the final value:
        $this->addFieldToRecord('allfields', implode(' ', $this->allFields), false);

        // Close the record:
        $this->currentRecord .= '  </doc>' . "\n";
        $this->currentRecord .= '</add>' . "\n";
    }

    protected function saveCurrentRecord($id)
    {
        // Clean up content
        try {
            $this->solr->save($this->currentRecord);
            /*$handle = fopen(APPLICATION_PATH . '/exported_Newdata_limit.xml', 'a');
            fputs($handle, $this->currentRecord);
            fclose($handle);*/
            $this->success++;
        } catch (\Exception $e) {
            $this->failure++;
            echo "Failure: " . $e->getMessage() . " : $this->currentRecord\n";
            file_put_contents('import-log', '[' . date('r') .
                '] Could not import record ' . $id . ':' .
                $e->getMessage() . "\n", FILE_APPEND);
        }
    }

    protected function addFolderChainForFolder($folder, $appendCurrent = true)
    {
        //Get parent chain for 'Topic'
        $topicParts = ['id' => []];
        foreach ($this->languages as $lang) {
            $topicParts[$lang] = [];
        }
        $chain = $this->folder->getParentChainRecord($folder->folder_id, true);
        if ($appendCurrent) {
            //Append actual folder record to
            $chain[] = $folder;
        }
        $hierarchies = [];
        foreach ($chain as $i => $row) {
            $topicParts['id'][] = $row['id'];
            foreach ($this->languages as $lang) {
                $topicParts[$lang][] = $row['text_' . $lang] ?? '[' . $row['text_fr'] . ']';
            }
        }
        $delimiter = '§';
        $this->addFieldToRecord(
            'topic_id_str_mv',
            implode($delimiter, $topicParts['id']) . $delimiter
        );
        foreach ($this->languages as $lang) {
            // Add a delimited version for display purposes:
            $this->addFieldToRecord(
                'topic_text_' . $lang . '_str_mv',
                implode($delimiter, $topicParts[$lang]) . $delimiter
            );
            // Also add a searchable version that will get tokenized:
            $this->addFieldToRecord('topic', implode(' ', $topicParts[$lang]));
            foreach ($topicParts[$lang] as $i => $part) {
                $current = $i . '/';
                for ($j = 0; $j <= $i; $j++) {
                    $escSlash = str_replace(
                        '/',
                        '$slash$',
                        $topicParts[$lang][$j]
                    );
                    $current .= $escSlash . '/';
                }
                $this->addFieldToRecord(
                    'topic_hierarchy_' . $lang . '_str_mv', $current
                );
            }
        }
    }

    protected function processFolderList($wk_id, $wk_title)
    {
        $folderList = $this->work_folder->getFoldersByWorkId($wk_id);
        foreach ($folderList as $folder) {
            $root = $this->folder->findRecordById($folder->id);
            //$root = Folder::staticGet('id', $folder->id);
            $this->addFieldToRecord('hierarchy_parent_id', 'folder-' . $folder->id, false);
            $this->addFieldToRecord('hierarchy_parent_title', $this->getBestTitle($folder));
            $this->addFieldToRecord('hierarchy_browse', $this->getBestTitle($folder) . '{{{_ID_}}}' . $folder->id, false);
            $this->addFieldToRecord('hierarchy_top_id', 'folder-' . $this->folderTops[$folder->id], false);
            $this->addFieldToRecord('hierarchy_top_title', $this->folderTitles[$this->folderTops[$folder->id]]);
            $this->addFieldToRecord('hierarchy_sequence', $this->getSortTitle($wk_title), false);
            $this->addFolderChainForFolder($folder);
        }
    }

    protected function getSortTitle($wk_title)
    {
        $title = str_replace(['<i>', '</i>'], '', $wk_title);
        return trim(SolrUtils::stripArticles($title));
    }

    protected function processTitle($row)
    {
        $this->wk = $row;
        // Fill in the title_short, title_sub, title and title_full with different
        // slices of the title.  Note that we only put the final $fullTitle value
        // into the "all fields" list to avoid redundancy.
        $this->addFieldToRecord('title_short', $this->wk->title, false);
        $fullTitle = $this->wk->title;
        if ($this->wk->subtitle != '') {
            $this->addFieldToRecord('title_sub', $this->wk->subtitle, false);
            $fullTitle .= ': ' . $this->wk->subtitle;
        }
        $this->addFieldToRecord('title', $fullTitle, false);
        $sortTitle = $this->getSortTitle($fullTitle);
        $this->addFieldToRecord('title_sort', $sortTitle, false);
        $this->addFieldToRecord('title_sort_txt', $sortTitle, true);
        if ($this->wk->paralleltitle != '') {
            $this->addFieldToRecord('title_alt', $this->wk->paralleltitle);
        }
        $this->addFieldToRecord('title_full', $fullTitle);
    }

    protected function getBestTitle($fields)
    {
        return empty($fields->text_en) ? $fields->text_fr : $fields->text_en;
    }

    protected function processCurrentFolder($row)
    {
        $this->startNewRecord();
        if (gettype($row) == 'array') {
            // create an instance of the ArrayObject class
            $row1 = new ArrayObject($row, ArrayObject::ARRAY_AS_PROPS);

            $this->fl = $row1;
        } else {
            $this->fl = $row;
        }

        // Basic fields:
        $this->addFieldToRecord('id', 'folder-' . $this->fl->id);
        $this->addFieldToRecord('record_format', 'augustinefolder');
        $this->addFieldToRecord('collection', 'Bibliography');

        // Title fields:
        $defaultTitle = $this->getBestTitle($this->fl);
        $bracketedTitle = "[$defaultTitle]";
        $this->folderTitles[$this->fl->id] = $defaultTitle;
        $this->addFieldToRecord('title_short', $defaultTitle);
        $this->addFieldToRecord('title', $defaultTitle);
        foreach ($this->languages as $lang) {
            $value = trim($this->fl->{'text_' . $lang} ?? $bracketedTitle);
            $value = strlen($value) === 0 ? $bracketedTitle : $value;
            $this->addFieldToRecord('title_' . $lang . '_str', $value);
        }
        $sortTitle = $this->getSortTitle($defaultTitle);
        $this->addFieldToRecord('title_sort', $sortTitle);

        // Hierarchy fields:
        $this->addFieldToRecord('is_hierarchy_id', 'folder-' . $this->fl->id);
        $this->addFieldToRecord('is_hierarchy_title', $defaultTitle);

        if (null !== $this->fl->sort_order) {
            $this->fl->sort_order = str_pad($this->fl->sort_order, 6, '0', STR_PAD_LEFT);
            $new_seq = $this->fl->sort_order . $sortTitle;
            $this->addFieldToRecord('hierarchy_sequence', $new_seq, false);
        } else {
            $this->addFieldToRecord('hierarchy_sequence', $sortTitle, false);
        }

        $parents = $this->folder->getParentChainRecord($this->fl->id);
        if (isset($parents[0])) {
            $this->addFieldToRecord('hierarchy_parent_id', 'folder-' . $parents[0]->id);
            $this->addFieldToRecord('hierarchy_parent_title', $this->getBestTitle($parents[0]));
            $this->addFieldToRecord('hierarchy_browse', $this->getBestTitle($parents[0]) . '{{{_ID_}}}' . $parents[0]->id);
            $topFolder = $parents[count($parents) - 1];
            $this->addFieldToRecord('hierarchy_top_id', 'folder-' . $topFolder->id);
            $this->addFieldToRecord('hierarchy_top_title', $this->getBestTitle($topFolder));
            $this->folderTops[$this->fl->id] = $topFolder->id;
        } elseif (!isset($parents[0]) && $this->fl->parent_id == $this->fl->id) {
            $this->addFieldToRecord('hierarchy_parent_id', 'folder-' . $this->fl->parent_id);
            $this->addFieldToRecord('hierarchy_parent_title', $this->getBestTitle($this->fl));
            $this->addFieldToRecord('hierarchy_browse', $this->getBestTitle($this->fl) . '{{{_ID_}}}' . $this->fl->id);
            $this->addFieldToRecord('hierarchy_top_id', 'folder-' . $this->fl->id);
            $this->addFieldToRecord('hierarchy_top_title', $this->getBestTitle($this->fl));
            $this->folderTops[$this->fl->id] = $this->fl->id;
        } else {
            $this->addFieldToRecord('hierarchy_top_id', 'folder-' . $this->fl->id);
            $this->addFieldToRecord('hierarchy_top_title', $this->getBestTitle($this->fl));
            $this->folderTops[$this->fl->id] = $this->fl->id;
        }

        // Format data to the layout expected by addFolderChainForFolder...
        $folderObj = new \ArrayObject;
        $folderObj->folder_id = $this->fl->id;
        $this->addFolderChainForFolder($folderObj, false);

        $this->endCurrentRecord();
        $this->saveCurrentRecord('folder-' . $this->fl->id);
        $this->total++;
        $this->writeLine("Wrote record $this->total (folder {$this->fl->id})");
    }

    protected function processCurrentAgent($row)
    {
        $this->startNewRecord();
        if (gettype($row) == 'array') {
            // create an instance of the ArrayObject class
            $row1 = new ArrayObject($row, ArrayObject::ARRAY_AS_PROPS);

            $this->ag = $row1;
        } else {
            $this->ag = $row;
        }

        // Basic fields:
        $this->addFieldToRecord('id', 'agent-' . $this->ag->id);
        $this->addFieldToRecord('record_format', 'augustineagent');
        $this->addFieldToRecord('collection', 'Bibliography');

        // Title fields:
        $title = trim($this->ag->fname . ' ' . $this->ag->lname);
        $this->addFieldToRecord('title_short', $title);
        $this->addFieldToRecord('title', $title);
        $this->addFieldToRecord('title_sort', $title);
        $this->addFieldToRecord('format', 'Agent');
        $this->addFieldToRecord('first_name_str', $this->ag->fname);
        $this->addFieldToRecord('last_name_str', $this->ag->lname);
        $this->addFieldToRecord('alt_name_str', $this->ag->alternate_name);
        $this->addFieldToRecord('org_name_str', $this->ag->organization_name);
        //$this->addFieldToRecord('email_str', $this->ag->email);

        $this->endCurrentRecord();
        $this->saveCurrentRecord('agent-' . $this->ag->id);
        $this->total++;
        $this->writeLine("Wrote record $this->total (agent {$this->ag->id})");
    }

    protected function checkDateMismatches($goodDates, $dateToCheck, $wk_id)
    {
        $foundMatch = false;
        foreach ($goodDates as $good) {
            $chunk = substr($dateToCheck, 0, strlen($good));
            if ($chunk == $good) {
                $foundMatch = true;
            }
        }
        if (!$foundMatch) {
            $this->writeLine("WARNING: date mismatch in {$wk_id} -- $dateToCheck / " . implode(', ', $goodDates));
        }
    }

    protected function figureOutDates($wk_id, $publisherDates)
    {
        $mmddyyyy = $this->work_workattribute->getAttributeValue($wk_id, 'Date (MM/DD/YYYY)');
        $textDate = $this->work_workattribute->getAttributeValue($wk_id, 'Date Name');

        $solrDates = [];
        $humanReadableDate = false;
        if (!empty($mmddyyyy)) {
            $humanReadableDate = $mmddyyyy;
            $solrMMDDYYYY = SolrUtils::sanitizeDate($mmddyyyy);
            $solrDates[] = $solrMMDDYYYY;
            if (empty($solrMMDDYYYY)) {
                $this->writeLine("WARNING: bad MM/DD/YYYY date in {$wk_id} -- $mmddyyyy");
            } else {
                $this->checkDateMismatches($publisherDates, $solrMMDDYYYY, $wk_id);
            }
        }

        if (!empty($textDate)) {
            $humanReadableDate = $textDate;
            $solrTextDate = SolrUtils::sanitizeDate($textDate);
            $solrDates[] = $solrTextDate;
            if (empty($solrTextDate)) {
                $this->writeLine("WARNING: bad human-readable date in {$wk_id} -- $textDate");
            } else {
                $this->checkDateMismatches($publisherDates, $solrTextDate, $wk_id);
            }
        }

        foreach ($publisherDates as $date) {
            $solrDates[] = SolrUtils::sanitizeDate($date);
        }

        $solrDates = array_unique($solrDates);
        $this->addFieldToRecord('all_dates_date_mv', $solrDates, false);
        if (count($solrDates) > 0) {
            $this->addFieldToRecord('sort_date', $solrDates[0], false);
        }
        if (!empty($humanReadableDate)) {
            $this->addFieldToRecord('human_readable_date_str', $humanReadableDate);
        }
    }

    protected function processCurrentWork($row)
    {
        $this->startNewRecord();
        if (gettype($row) == 'array') {
            // create an instance of the ArrayObject class
            $row1 = new ArrayObject($row, ArrayObject::ARRAY_AS_PROPS);

            $this->wk = $row1;
        } else {
            $this->wk = $row;
        }

        $this->addFieldToRecord('id', $this->wk->id);
        $this->addFieldToRecord('record_format', 'augustine');
        $this->addFieldToRecord('collection', 'Bibliography');
        $this->processFolderList($this->wk->id, $this->wk->title);
        $this->processTitle($this->wk);

        $wk_agents = $this->work_agent->findRecordByWorkId($this->wk->id);
        if (count($wk_agents) > 0) {
            foreach ($wk_agents as $agent) {
                $this->addFieldToRecord('author_role', $agent['type']);
                $this->addFieldToRecord('author', $agent['fname'] . ' ' . $agent['lname']);
            }
        }

        if ($this->wk->description != '') {
            $this->addFieldToRecord('description', $this->wk->description);
        }

        //index work status
        if (null === $this->wk->status) {
            $this->addFieldToRecord('status_str', 'Inactive');
        } elseif ($this->wk->status === 0 || $this->wk->status === 2) {
            $this->addFieldToRecord('status_str', 'Needs Review');
        } else {
            $this->addFieldToRecord('status_str', 'Active');
        }

        //Get Parent work (i.e., work title of work_id)
        $parent_work = [];
        if ($this->wk->work_id > 0) {
            $parent_work = $this->work->findRecordById($this->wk->work_id);
        }
        if (!empty($parent_work)) {
            $this->addFieldToRecord('parent_work_str', $parent_work['title']);
            $this->addFieldToRecord('parent_work_id_str', $parent_work['id']);
        }

        //Get child works i.e., works with current id ($this->wk->id) as work_id
        $child_works = [];
        $child_works = $this->work->select(['work_id' => $this->wk->id]);
        if (!empty($child_works)) {
            foreach ($child_works as $ch_wk) {
                $this->addFieldToRecord('child_work_str_mv', $ch_wk['title']);
                $this->addFieldToRecord('child_work_id_str_mv', $ch_wk['id']);
            }
        }

        $format = $this->work_type->findRecordById($this->wk->type_id);
        if (is_object($format)) {
            $this->addFieldToRecord('format', $format->type);
        }

        $attributeMap = [
            'ISBN' => 'isbn',
            'ISSN' => 'issn',
            'Volume' => 'container_volume',
            'Part' => 'part_str',
            'Issue' => 'container_issue',
            'Pages' => 'container_pages_str',
            'Total Pages' => 'container_total_pages_str',
            'Link to Full Text' => 'url',
            'URL' => 'url',
            'Abstract' => 'abstract_str',
            'Number' => 'number_str',
            'Notes' => 'notes_str',
            'Edition' => 'edition',
            'Original Language' => 'original_language_str',
            'Original Title' => 'original_title_str',
            'Proceedings Title' => 'proceedings_title_str',
            'Series' => 'series',
            'Periodical' => 'periodical_str',
            'Material Designation' => 'material_designation_str',
            'Institution' => 'degree_institution_str',
            //'Test_Attr' => 'test_attr_str',
        ];
        $subAttributeMap = [
            'ISSN' => 'issn',
        ];

        foreach ($attributeMap as $attr => $solrField) {
            $tmp = $this->work_workattribute->getAttributeValue($this->wk->id, $attr, true);
            $this->addFieldToRecord($solrField, $tmp['display']);
            foreach ($tmp['sub_attribs'] ?? [] as $sub) {
                if (isset($subAttributeMap[$sub['subattribute']])) {
                    $this->addFieldToRecord($subAttributeMap[$sub['subattribute']], $sub['subattr_value']);
                }
            }
        }

        $lang = $this->work_workattribute->getAttributeValue($this->wk->id, 'Language');
        if (!empty($lang)) {
            $langs = array_map('trim', explode(';', $lang));
            foreach ($langs as $current) {
                $this->addFieldToRecord('language', $current);
            }
        }

        //$publisherList = $this->work->getPublishers();
        $publisherList = $this->work_publisher->findRecordByWorkId($this->wk->id);
        if (!empty($publisherList) && !empty($publisherList[0]['publish_year'])) {
            $this->addFieldToRecord('publishDateSort', $publisherList[0]['publish_year']);
        }

        $fullDates = [];
        foreach ($publisherList as $publisher) {
            if (!empty($publisher['publish_year'])) {
                $dateSuffix = empty($publisher['publish_month'])
                    ? '' : '-' . $publisher['publish_month'];
                $fullDates[] = $publisher['publish_year'] . $dateSuffix;
            }
            $this->addFieldToRecord('publishDate', $publisher['publish_year']);
            $this->addFieldToRecord('publisher', $publisher['name']);
            $this->addFieldToRecord('publishPlace_str_mv', $publisher['location']);
        }
        $this->figureOutDates($this->wk->id, $fullDates);

        $this->endCurrentRecord();
        $this->saveCurrentRecord($this->wk->id);

        // Update Progress Bar
        $this->total++;
        $this->writeLine("Wrote record $this->total (work {$this->wk->id})");
    }
}
