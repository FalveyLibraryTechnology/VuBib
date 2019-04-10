<?php
/**
 * Manage Agent Action
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
namespace VuBib\Action\Agent;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Db\Adapter\Adapter;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Router;
use Zend\Expressive\Template;
use Zend\Paginator\Paginator;

/**
 * Class Definition for ManageAgentAction.
 *
 * @category VuBib
 * @package  Code
 * @author   Falvey Library <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 *
 * @link https://
 */
class ManageAgentAction implements MiddlewareInterface
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
     * ManageAgentAction constructor.
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
     * Agent > Search.
     *
     * @param Array $params url query parameters
     *
     * @return Array
     */
    protected function searchAgent($params)
    {
        // search by letter
        if (!empty($params['letter'])) {
            $table = new \VuBib\Db\Table\Agent($this->adapter);

            return $table->displayRecordsByName($params['letter']);
        }
        // search by first name
        if (!empty($params['find_agentfname'])) {
            $table = new \VuBib\Db\Table\Agent($this->adapter);

            return $table->findRecords($params['find_agentfname'], 'fname');
        }
        // search by last name
        if (!empty($params['find_agentlname'])) {
            $table = new \VuBib\Db\Table\Agent($this->adapter);

            return $table->findRecords($params['find_agentlname'], 'lname');
        }
        // search by alternate name
        if (!empty($params['find_agentaltname'])) {
            $table = new \VuBib\Db\Table\Agent($this->adapter);

            return $table->findRecords($params['find_agentaltname'], 'altname');
        }
        // search by organization name
        if (!empty($params['find_agentorgname'])) {
            $table = new \VuBib\Db\Table\Agent($this->adapter);

            return $table->findRecords($params['find_agentorgname'], 'orgname');
        }
    }

    /**
     * Merge agents.
     *
     * @param Array $post contains posted elements of form
     *
     * @return empty
     */
    protected function doMerge($post)
    {
        // Switch Agent
        $table = new \VuBib\Db\Table\WorkAgent($this->adapter);
        $table->updateRecordByAgentId($post['mrg_src_id'], $post['mrg_dest_id']);
        // Purge
        $table = new \VuBib\Db\Table\Agent($this->adapter);
        $table->deleteRecord($post['mrg_src_id']);
    }

    /**
     * Action based on action parameter.
     *
     * @param Array $post contains posted elements of form
     *
     * @return empty
     */
    protected function doAction($post)
    {
        //add a new agent
        if ($post['action'] == 'new') {
            if ($post['submitt'] == 'Save') {
                $table = new \VuBib\Db\Table\Agent($this->adapter);
                $table->insertRecords(
                    $post['new_agentfirstname'], $post['new_agentlastname'],
                    $post['new_agentaltname'], $post['new_agentorgname'],
                    $post['new_agentemail']
                );
            }
        }
        //edit an agent
        if ($post['action'] == 'edit') {
            if ($post['submitt'] == 'Save') {
                if (null !== $post['id']) {
                    $table = new \VuBib\Db\Table\Agent($this->adapter);
                    $table->updateRecord(
                        $post['id'], $post['edit_agentfirstname'],
                        $post['edit_agentlastname'],
                        $post['edit_agentaltname'], $post['edit_agentorgname'],
                        $post['edit_agentemail']
                    );
                }
            }
        }
        //delete an agent
        if ($post['action'] == 'delete') {
            if ($post['submitt'] == 'Delete') {
                $this->doDelete($post);
            }
        }
        //merge agents
        if ($post['action'] == 'merge') {
            $this->doMerge($post);
        }
    }

    /**
     * Delete agent.
     *
     * @param Array $post contains posted elements of form
     *
     * @return empty
     */
    protected function doDelete($post)
    {
        if ($post['action'] == 'delete') {
            if ($post['submitt'] == 'Delete') {
                if (null !== $post['agent_id']) {
                    foreach ($post['agent_id'] as $agentId) {
                        $table = new \VuBib\Db\Table\WorkAgent($this->adapter);
                        $table->deleteRecordByAgentId($agentId);
                        $table = new \VuBib\Db\Table\Agent($this->adapter);
                        $table->deleteRecord($agentId);
                    }
                }
            }
        }
    }

    /**
     * Get records to display.
     *
     * @param Array $params url query parameters
     * @param Array $post   contains posted elements of form
     *
     * @return Paginator $paginator
     */
    protected function getPaginator($params, $post)
    {
        //search
        if (!empty($params)) {
            if (!empty($params['letter']) || !empty($params['find_agentfname'])
                || !empty($params['find_agentlname'])
                || !empty($params['find_agentaltname'])
                || !empty($params['find_agentorgname'])
            ) {
                return $this->searchAgent($params);
            }
        }

        //edit, delete actions on agent
        if (!empty($post['action'])) {
            //add edit delete merge agent
            $this->doAction($post);

            //Cancel edit\delete
            if ($post['submitt'] == 'Cancel') {
                $table = new \VuBib\Db\Table\Agent($this->adapter);

                return new Paginator(
                    new \Zend\Paginator\Adapter\DbTableGateway($table)
                );
            }
        }
        // default: blank for listing in manage
        $table = new \VuBib\Db\Table\Agent($this->adapter);

        return new Paginator(new \Zend\Paginator\Adapter\DbTableGateway($table));
    }

    /**
     * Set search parameters for pagination.
     *
     * @param Array $query url query parameters
     *
     * @return Array $searchParams
     */
    protected function getSearchParams($query)
    {
        $searchParams = [];
        if (!empty($query['find_agentfname'])) {
            $searchParams[] = 'find_agentfname='
                . urlencode(
                    $query['find_agentfname']
                );
        }
        if (!empty($query['find_agentlname'])) {
            $searchParams[] = 'find_agentlname='
                . urlencode(
                    $query['find_agentlname']
                );
        }
        if (!empty($query['find_agentaltname'])) {
            $searchParams[] = 'find_agentaltname='
                . urlencode(
                    $query['find_agentaltname']
                );
        }
        if (!empty($query['find_agentorgname'])) {
            $searchParams[] = 'find_agentorgname='
                . urlencode(
                    $query['find_agentorgname']
                );
        }
        if (!empty($query['letter'])) {
            $searchParams[] = 'letter=' . urlencode($query['letter']);
        }
        return $searchParams;
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
        $table = new \VuBib\Db\Table\Agent($this->adapter);
        $characs = $table->findInitialLetter();

        $simpleAction = new \VuBib\Action\SimpleRenderAction(
            'vubib::agent/manage', $this->router,
            $this->template, $this->adapter
        );
        list($query, $post) = $simpleAction->getQueryAndPost($request);

        $paginator = $this->getPaginator($query, $post);
        $paginator->setDefaultItemCountPerPage(15);

        $simpleAction = new \VuBib\Action\SimpleRenderAction(
            'vubib::agent/manage', $this->router,
            $this->template, $this->adapter
        );
        $pgs = $simpleAction->getNextPrevious($paginator, $query);

        $searchParams = $this->getSearchParams($query);

        if (null !== $searchParams) {
            $searchParams = implode('&', $searchParams);
        } else {
            $searchParams = '';
        }

        return new HtmlResponse(
            $this->template->render(
                'vubib::agent/manage',
                [
                    'rows' => $paginator,
                    'previous' => $pgs['prev'],
                    'next' => $pgs['nxt'],
                    'countp' => $pgs['cp'],
                    'searchParams' => $searchParams,
                    'carat' => $characs,
                ]
            )
        );
    }
}
