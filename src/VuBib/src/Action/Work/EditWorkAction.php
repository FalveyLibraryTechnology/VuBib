<?php
/**
 * Manage Work Action
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
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Router;
use Zend\Expressive\Template;
use Zend\Paginator\Paginator;
use Zend\Session;

/**
 * Class Definition for ManageWorkAction.
 *
 * @category VuBib
 * @package  Code
 * @author   Falvey Library <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 *
 * @link https://
 */
class EditWorkAction implements MiddlewareInterface
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
     * @var $this->adapter
     */
    protected $adapter;

    /**
     * Zend\Session\Container
     *
     * @var $this->session
     */
    protected $session;

    /**
     * ManageWorkAction constructor.
     *
     * @param Router\RouterInterface             $router   for routes
     * @param Template\TemplateRendererInterface $template for templates
     * @param Adapter                            $adapter  for db connection
     */
    public function __construct(Router\RouterInterface $router,
        Template\TemplateRendererInterface $template = null, Adapter $adapter,
        Session\Container $session
    ) {
        $this->router = $router;
        $this->template = $template;
        $this->adapter = $adapter;
        $this->session = $session;
    }

    /**
     * Check user access level
     *
     * @return string $usr
     */
    private function getUserType()
    {
        $table = new \VuBib\Db\Table\User($this->adapter);
        $usr = $table->findRecordById($this->session->id);

        if (isset($usr['level'])) {
            if ($usr['level'] == 1) {
                $usr['level'] = 'Administrator';
            } else {
                $usr['level'] = 'Super User';
            }
        } else {
            $usr['level'] = 'User';
        }

        return $usr;
    }

    private function render($template, $params = [])
    {
        // TODO: Remove
        if (!isset($params['adapter'])) {
            $params['adapter'] = $this->adapter;
        }
        // TODO: Remove
        $params['escaper'] = new \Zend\Escaper\Escaper('utf-8');

        return new HtmlResponse(
            $this->template->render($template, $params)
        );
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
        $params = $request->getqueryParams();
        $workId = $params['id'];

        // - New - //
        if ($workId == 'NEW') {
            return $this->render('vubib::work/edit', ['formAction' => 'work_new']);
        }

        $viewData = [];
        $workTable = new \VuBib\Db\Table\Work($this->adapter);
        $workRow = $workTable->findRecordById($workId);
        $user = $this->getUserType();

        //fetch parent work
        if ($workRow['work_id'] != null) {
            //fetch name based on id
            $pr_wrk_row = $workTable->findRecordById($workRow['work_id']);
            $workRow['parent'] = [
                'id' => $pr_wrk_row['id'],
                'title' => $pr_wrk_row['title'],
            ];
        }
        $viewData['work'] = $workRow;

        //fetch folders
        $workClassifications = [];
        $workTable = new \VuBib\Db\Table\Work_Folder($this->adapter);
        $parentRows = $workTable->findRecordsByWorkId($workId);
        $folderTable = new \VuBib\Db\Table\Folder($this->adapter);
        foreach($parentRows as $parent) {
            $classification = [];
            $parentChain = $folderTable->getParentChain($parent['folder_id']);
            foreach ($parentChain as $folderId) {
                $siblings = [];
                $folder = $folderTable->findRecordById($folderId);
                $folderSiblings = $folderTable->getSiblings($folder['parent_id']);
                foreach ($folderSiblings as $sibling) {
                    if ($sibling['id'] == $folderId) {
                        $sibling['selected'] = true;
                    }
                    $siblings[] = $sibling;
                }
                $classification[] = $siblings;
            }
            $workClassifications[] = $classification;
        }
        $viewData['classifications'] = $workClassifications;

        // default classifications
        $table = new \VuBib\Db\Table\Folder($this->adapter);
        $viewData['rootFolders'] = $table->getFoldersWithNullParent();

        //fetch publishers attached to work
        $table = new \VuBib\Db\Table\WorkPublisher($this->adapter);
        $pub_rows = $table->findRecordByWorkId($workId);
        $table = new \VuBib\Db\Table\PublisherLocation($this->adapter);
        for ($i = 0; $i < count($pub_rows); $i++) {
            $locs = $table->getPublisherLocations($pub_rows[$i]['publisher_id']);
            usort($locs, function($a, $b) {
                return strcmp($a['location'], $b['location']);
            });
            $pub_rows[$i]['locations'] = $locs;
        }
        $viewData['publishers'] = $pub_rows;


        // fetch agent types
        $table = new \VuBib\Db\Table\AgentType($this->adapter);
        $agentTypes = $table->fetchAgentTypes();
        $itemsCount = $agentTypes->getTotalItemCount();
        $agentTypes->setItemCountPerPage($itemsCount);
        $viewData['agentTypes'] = $agentTypes;

        //fetch agents attached to work
        $table = new \VuBib\Db\Table\WorkAgent($this->adapter);
        $viewData['agents'] = $table->findRecordByWorkId($workId);

        // worktypes
        $table = new \VuBib\Db\Table\WorkType($this->adapter);
        $viewData['workTypes'] = $table->fetchAllWorkTypes();
        $itemsCount = $viewData['workTypes']->getTotalItemCount();
        $viewData['workTypes']->setItemCountPerPage($itemsCount);

        // citations
        $table = new \VuBib\Db\Table\WorkAttribute($this->adapter);
        $citationTypes = $table->getAttributesForWorkType($workRow['type_id']);
        $itemsCount = $citationTypes->getTotalItemCount();
        $citationTypes->setItemCountPerPage($itemsCount);
        $viewData['citationTypes'] = $citationTypes;

        $table = new \VuBib\Db\Table\Work_WorkAttribute($this->adapter);
        $citations = $table->findRecordByWorkId($workId);
        $citationMap = [];
        foreach ($citations as $cite) {
            $citationMap[$cite['workattribute_id']] = $cite['value'];
        }
        $viewData['citations'] = $citationMap;

        // - View - //
        if ($workRow['status'] == 1 && $user['level'] == 'User') {
            return $this->render('vubib::work/view', $viewData);
        }

        // - Edit - //
        $viewData['formAction'] = 'work_edit';
        return $this->render('vubib::work/edit', $viewData);
    }
}
