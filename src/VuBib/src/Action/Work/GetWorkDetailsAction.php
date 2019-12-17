<?php
/**
 * Get Work Details Action
 *
 * PHP version 5
 *
 * Copyright (c) Falvey Library 2017.
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
 * @link     https:// Main Page
 */
namespace VuBib\Action\Work;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Db\Adapter\Adapter;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Expressive\Router;
use Zend\Expressive\Template;

/**
 * Class Definition for GetWorkDetailsAction.
 *
 * @category VuBib
 * @package  Code
 * @author   Falvey Library <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 *
 * @link https://
 */
class GetWorkDetailsAction implements MiddlewareInterface
{
    /**
     * Router\RouterInterface
     *
     * @var $router
     */
    protected $router;

    /**
     * Template\TemplateRendererInterface
     *
     * @var $template
     */
    protected $template;

    /**
     * Zend\Db\Adapter\Adapter
     *
     * @var $adapter
     */
    protected $adapter;

    /**
     * AttributesWorkTypeAction constructor.
     *
     * @param Router\RouterInterface             $router   for routes
     * @param Template\TemplateRendererInterface $template for templates
     * @param Adapter                            $adapter  for db connection
     */
    public function __construct(Router\RouterInterface $router,
        Template\TemplateRendererInterface $template = null, Adapter $adapter
    ) {
        $this->router = $router;
        $this->template = $template;
        $this->adapter = $adapter;
    }

    /**
     * Fetches publisher details.
     *
     * @param Array $post contains posted elements of form
     *
     * @return string $output
     */
    protected function pubName($post)
    {
        $no_of_wks = [];
        $name = $post['pub_name'];
        $table = new \VuBib\Db\Table\Publisher($this->adapter);
        $publisher_row = $table->findRecords($name);
        foreach ($publisher_row as $row) {
            $pub_row[] = $row;
        }
        foreach ($pub_row as $row) {
            $table = new \VuBib\Db\Table\WorkPublisher($this->adapter);
            $wks = $table->findRecordByPublisherId($row['id']);
            $no_wks = count($wks);
            $no_of_wks[] = $no_wks;
        }
        for ($i = 0; $i < count($no_of_wks); $i++) {
            $pub_row[$i]['works'] = $no_of_wks[$i];
        }
        return ['pub_row' => $pub_row];
    }

    /**
     * Fetches publisher location details.
     *
     * @param Array $post contains posted elements of form
     *
     * @return string $output
     */
    protected function publisherIdLocs($pub_id)
    {
        $table = new \VuBib\Db\Table\PublisherLocation($this->adapter);
        $pub_loc_rows = $table->getPublisherLocations($pub_id);
        foreach ($pub_loc_rows as $i => $row) {
            $pub_loc_rows[$i]['value'] = $row['location'];
            $pub_loc_rows[$i]['label'] = $row['location'];
            $pub_loc_rows[$i]['id'] = $row['id'];
        }
        foreach ($pub_loc_rows as $row) {
            $table = new \VuBib\Db\Table\WorkPublisher($this->adapter);
            $wks = $table->findRecordByLocationId($row['id']);
            $no_wks = count($wks);
            $no_of_wks[] = $no_wks;
        }
        for ($i = 0; $i < count($no_of_wks); ++$i) {
            $pub_loc_rows[$i]['works'] = $no_of_wks[$i];
        }
        /*
        usort($pub_loc_rows, function($a, $b) {
            return strcmp($a['label'], $b['label']);
        });
        */
        return ['publocs' => $pub_loc_rows];
    }

    /**
     * Fetches agent details.
     *
     * @param Array $post contains posted elements of form
     *
     * @return string $output
     */
    protected function agName($post)
    {
        $no_of_wks = [];
        $name = $post['ag_name'];
        $table = new \VuBib\Db\Table\Agent($this->adapter);
        $ag_row = $table->getLastNameLikeRecords($name);
        foreach ($ag_row as $row) {
            $table = new \VuBib\Db\Table\WorkAgent($this->adapter);
            $wks = $table->findRecordByAgentId($row['id']);
            $no_wks = count($wks);
            $no_of_wks[] = $no_wks;
        }
        for ($i = 0; $i < count($no_of_wks); ++$i) {
            $ag_row[$i]['works'] = $no_of_wks[$i];
        }
        return ['ag_row' => $ag_row];
    }

    /**
     * Add/Edit instructions.
     *
     * @param Array $post contains posted elements of form
     *
     * @return empty
     */
    protected function insText($post)
    {
        $ins_str = $post['ins_text'];
        if (!empty($post['pg_id'])) {
            $pg_id = $post['pg_id'];
            $table = new \VuBib\Db\Table\Page_Instructions($this->adapter);
            $table->updateRecord($pg_id, $ins_str);
        } else {
            $pg_nm = $post['pg_nm'];
            $table = new \VuBib\Db\Table\Page_Instructions($this->adapter);
            $table->insertRecord($pg_nm, $ins_str);
        }
        return null;
    }

    /**
     * Fetches classification details.
     *
     * @param Array $post contains posted elements of form
     *
     * @return string $output
     */
    protected function folderId($post)
    {
        $fl_id = $post['folder_Id'];
        $table = new \VuBib\Db\Table\Folder($this->adapter);
        $rows = $table->getChild($fl_id);
        return ['folder_children' => $rows];
    }

    /**
     * Fetches classification parent trail.
     *
     * @param Array $post contains posted elements of form
     *
     * @return string $output
     */
    protected function flId($post)
    {
        $fl_id = $post['fl_id'];
        $table = new \VuBib\Db\Table\Folder($this->adapter);
        $src_row = $table->getParentChain($fl_id);
        return ['fl_row' => $src_row];
    }

    /**
     * Fetches Attribute options.
     *
     * @param Array $post contains posted elements of form
     *
     * @return string $output
     */
    protected function option($post)
    {
        $opt_title = $post['option'];
        $wkat_id = preg_replace("/^\w+:/", '', $post['attribute_Id']);
        //$wkat_id = $_POST['attribute_Id'];
        $table = new \VuBib\Db\Table\WorkAttribute_Option($this->adapter);
        $rows = $table->getAttributeOptions($opt_title, $wkat_id);
        return ['attribute_options' => $rows];
    }

    /**
     * Fetches workattribute options.
     *
     * @param Array $post contains posted elements of form
     *
     * @return string $output
     */
    protected function worktypeId($post)
    {
        $wkt_id = $post['worktype_Id'];
        $table = new \VuBib\Db\Table\WorkAttribute($this->adapter);
        $paginator = $table->getAttributesForWorkType($wkt_id);
        $itemsCount = $paginator->getTotalItemCount();
        $paginator->setItemCountPerPage($itemsCount);
        $rows = [];
        foreach ($paginator as $row) {
            $rows[] = $row;
        }
        return ['worktype_attribute' => $rows];
    }

    /**
     * Fetches publisher and location details.
     *
     * @param Array $post contains posted elements of form
     *
     * @return string $output
     */
    protected function publisherId($pub_id)
    {
        $table = new \VuBib\Db\Table\PublisherLocation($this->adapter);
        $rows = $table->getPublisherLocations($pub_id);
        foreach ($rows as $i => $row) {
            $rows[$i]['value'] = $row['location'];
            $rows[$i]['label'] = $row['location'];
            $rows[$i]['id'] = $row['id'];
        }
        return ['publoc' => $rows];
    }

    /**
     * Fetches agent and no of works for each.
     *
     * @param Array $post contains posted elements of form
     *
     * @return string $output
     */
    protected function agId($post)
    {
        $ag_id = $post['ag_id'];
        $table = new \VuBib\Db\Table\WorkAgent($this->adapter);
        $wks = $table->findRecordByAgentId($ag_id);
        return ['ag_no_of_wks' => count($wks)];
    }

    /**
     * Fetches work title
     *
     * @param Array $post contains posted elements of form
     *
     * @return string $output
     */
    public function getParentLookup($lookup_title)
    {
        $table = new \VuBib\Db\Table\Work($this->adapter);
        $rows = $table->fetchParentLookup($lookup_title);
        foreach ($rows as $i => $row) {
            $rows[$i]['id'] = $row['id'];
            $rows[$i]['label'] = $row['title'];
            $rows[$i]['type'] = $row['type'];
        }
        return ['prnt_lookup' => $rows];
    }

    /**
     * Autosuggest
     *
     * @param Array $autofor     entity for which autosuggest is required
     * @param Array $search_term auto search text
     *
     * @return Array $rows
     */
    protected function getAutoSuggest($autofor, $search_term)
    {
        if ($autofor == 'publisher') {
            $table = new \VuBib\Db\Table\Publisher($this->adapter);
            $rows = $table->getLikeRecords($search_term);
            foreach ($rows as $i => $row) {
                $rows[$i]['value'] = $row['name'];
                $rows[$i]['label'] = $row['name'];
                $rows[$i]['id'] = $row['id'];
            }
            return $rows;
        }
        if ($autofor == 'agent') {
            $table = new \VuBib\Db\Table\Agent($this->adapter);
            $rows = $table->getLikeRecords($search_term);
            foreach ($rows as $i => $row) {
                $rows[$i]['id'] = $row['id'];
                $rows[$i]['label'] = $row['lname'];
                $rows[$i]['fname'] = $row['fname'];
                $rows[$i]['lname'] = $row['lname'];
                $rows[$i]['alternate_name'] = $row['alternate_name'];
                $rows[$i]['organization_name'] = $row['organization_name'];
            }
            return $rows;
        }
        if ($autofor == 'lookup_title') {
            return $this->getParentLookup($search_term);
        }
        if ($autofor == 'publisher_loc') {
            return $this->publisherIdLocs($search_term);
        }
    }

    /**
     * Add a new publisher and send back newly added publisher details
     *
     * @param Array $post contains posted elements of form
     *
     * @return string $output
     */
    public function addAndGetNewPub($post)
    {
        $rows = [];

        $newPub_Name = $post['pubName'];
        $table = new \VuBib\Db\Table\Publisher($this->adapter);
        $newPub_id = $table->insertPublisherAndReturnId($newPub_Name);
        $row['pub_id'] = $newPub_id;
        $row['pub_name'] = $newPub_Name;

        if (isset($post['pubLocation']) && $post['pubLocation'] !== "") {
            $newPub_Loc = $post['pubLocation'];
            $table = new \VuBib\Db\Table\PublisherLocation($this->adapter);
            $newPubLoc_id = $table->addPublisherLocationAndReturnId(
                $newPub_id, $newPub_Loc
            );

            $row['pubLoc_id'] = $newPubLoc_id;
            $row['pub_loc'] = $newPub_Loc;
        }

        return ['newPublisher' => $row];
    }

    /**
     * Add a new agent and send back newly added agent details
     *
     * @param Array $post contains posted elements of form
     *
     * @return string $output
     */
    public function addAndGetNewAgent($post)
    {
        $rows = [];

        $newAg_FName = $post['agFName'];
        $newAg_LName = $post['agLName'];
        $newAg_AltName = $post['agAltName'];
        $newAg_OrgName = $post['agOrgName'];
        $newAg_Email = $post['agEmail'];
        $table = new \VuBib\Db\Table\Agent($this->adapter);
        $newAg_id = $table->insertAgentAndReturnId(
            $newAg_FName, $newAg_LName,
            $newAg_AltName, $newAg_OrgName, $newAg_Email
        );

        $row['ag_id'] = $newAg_id;
        $row['ag_fname'] = $newAg_FName;
        $row['ag_lname'] = $newAg_LName;
        $row['ag_altname'] = $newAg_AltName;
        $row['ag_orgname'] = $newAg_OrgName;
        $row['ag_email'] = $newAg_Email;

        return ['newAgent' => $row];
    }

    /**
     * Add a new attribute option and send back newly added option details
     *
     * @param Array $post contains posted elements of form
     *
     * @return string $output
     */
    public function addAndGetNewAttrOption($post)
    {
        $rows = [];

        $newOpt_AttrId = preg_replace("/^\w+:/", '', $post['attrId']);
        $new_Option = $post['attrOption'];
        //$new_OptType = $post['attrType'];

        $table = new \VuBib\Db\Table\WorkAttribute_Option($this->adapter);
        $newOpt_id = $table->insertOptionAndReturnId(
            $newOpt_AttrId,
            $new_Option
        );

        //fetch subattributes of attribute
        $table = new \VuBib\Db\Table\WorkAttribute_SubAttribute($this->adapter);
        $subattr = $table->findRecordsByWorkAttrId($newOpt_AttrId);

        if (count($subattr) > 0) {
            $row['subattr_id'] = $subattr['id'];
            //Insert option to subattribute table
            $table = new \VuBib\Db\Table\Attribute_Option_SubAttribute(
                $this->adapter
            );
            $table->insertRecord($newOpt_AttrId, $newOpt_id, $subattr['id']);
        }

        $row['attr_id'] = $newOpt_AttrId;
        $row['opt_id'] = $newOpt_id;
        $row['opt_title'] = $new_Option;
        //$row['opt_value'] = $new_OptType;

        return ['newOption' => $row];
    }

    /**
     * Fetches publisher details.
     *
     * @param Array $post contains posted elements of form
     *
     * @return string $output
     */
    protected function optName($post)
    {
        $no_of_wks = [];
        $name = $post['opt_name'];
        $wkat_id = $post['wkat_id'];
        $table = new \VuBib\Db\Table\WorkAttribute_Option($this->adapter);
        $option_row = $table->findRecords($name, $wkat_id);
        foreach ($option_row as $row) {
            $opt_row[] = $row;
        }
        foreach ($opt_row as $row) {
            $table = new \VuBib\Db\Table\Work_WorkAttribute($this->adapter);
            $wks = $table->findRecordByOptionId($wkat_id, $row['id']);
            $no_wks = count($wks);
            $no_of_wks[] = $no_wks;
        }
        for ($i = 0; $i < count($no_of_wks); ++$i) {
            $opt_row[$i]['works'] = $no_of_wks[$i];
        }
        return ['opt_row' => $opt_row];
    }

    /**
     * Get Sub attribute for an attribute
     *
     * @param Array $post contains posted elements of form
     *
     * @return string $output
     */
    public function getSubAttr($post)
    {
        //$row = [][];

        $post['attribute_Id'] = preg_replace("/^\w+:/", '', $post['attribute_Id']);
        //fetch subattributes of attribute
        $table = new \VuBib\Db\Table\WorkAttribute_SubAttribute($this->adapter);
        $subattr = $table->findRecordsByWorkAttrId($post['attribute_Id']);

        if (count($subattr) > 0) {
            $row['attr_id'] = $subattr['workattribute_id'];
            $row['opt_id'] = $post['option_id'];
            $row['subattr_id'] = $subattr['id'];
            $row['subattr'] = $subattr['subattribute'];

            //fetch subattribute values for option
            $table = new \VuBib\Db\Table\Attribute_Option_SubAttribute(
                $this->adapter
            );
            $opt_subattr_rows = $table->findRecordByOption(
                $row['opt_id'], $row['subattr_id']
            );
            for ($i = 0; $i < count($opt_subattr_rows); ++$i) {
                $arr[$i] = $opt_subattr_rows[$i]['subattr_value'];
            }
            $row['subattr_vals'] = $arr;
        }

        return ['subattr' => $row];
    }

    /**
     * Action based on post parameter set.
     *
     * @param Array $post contains posted elements of form
     *
     * @return empty
     */
    public function doPost($post)
    {
        if (isset($post['publisher_id'])) {
            return $this->publisherId($post);
        }
        if (isset($post['pub_name'])) {
            return $this->pubName($post);
        }
        if (isset($post['publisher_Id_locs'])) {
            return $this->publisherIdLocs($post);
        }
        if (isset($post['worktype_Id'])) {
            return $this->worktypeId($post);
        }
        if (isset($post['option'])) {
            return $this->option($post);
        }
        if (isset($post['folder_Id'])) {
            return $this->folderId($post);
        }
        if (isset($post['fl_id'])) {
            return $this->flId($post);
        }
        if (isset($post['ag_name'])) {
            return $this->agName($post);
        }
        if (isset($post['ag_id'])) {
            return $this->agId($post);
        }
        if (isset($post['ins_text'])) {
            return $this->insText($post);
        }
        if (isset($post['addAction'])) {
            if ($post['addAction'] == 'addNewPublisher') {
                return $this->addAndGetNewPub($post);
            } elseif ($post['addAction'] == 'addNewAgent') {
                return $this->addAndGetNewAgent($post);
            } elseif ($post['addAction'] == 'addNewOption') {
                return $this->addAndGetNewAttrOption($post);
            }
        }
        if (isset($post['opt_name'])) {
            return $this->optName($post);
        }
        if (isset($post['subattr'])) {
            return $this->getSubAttr($post);
        }
    }

    /**
     * Invokes required template
     *
     * @param ServerRequestInterface $request  server-side request.
     * @param ResponseInterface      $response response to client side.
     * @param callable               $next     CallBack Handler.
     *
     * @return HtmlResponse
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        if (isset($_GET['autofor'])) {
            $autofor = $_GET['autofor'];
            if (isset($_GET['term'])) {
                $search_term = $_GET['term'];
                $rows = $this->getAutoSuggest($autofor, $search_term);
            }
            return new JsonResponse($rows);
        }

        $post = [];
        if ($request->getMethod() == 'POST') {
            $post = $request->getParsedBody();
            return new JsonResponse($this->doPost($post));
        }
        return new JsonResponse(['error: default process bottom']);
    }
}
