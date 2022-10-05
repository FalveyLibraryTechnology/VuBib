<?php
/**
 * Manage AgentType Action
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
namespace VuBib\Action\AgentType;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Db\Adapter\Adapter;
use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Router;
use Mezzio\Template;
use Laminas\Paginator\Paginator;

/**
 * Class Definition for ManageAgentTypeAction.
 *
 * @category VuBib
 * @package  Code
 * @author   Falvey Library <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 *
 * @link https://
 */
class ManageAgentTypeAction implements MiddlewareInterface
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
     * Laminas\Db\Adapter\Adapter
     *
     * @var $adapter
     */
    protected $adapter;

    //private $dbh;
    //private $qstmt;

    /**
     * ManageAgentTypeAction constructor.
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
     * Action based on action parameter.
     *
     * @param Array $post contains posted elements of form
     *
     * @return empty
     */
    protected function doAction($post)
    {
        //add a new agent type
        if ($post['action'] == 'new') {
            if ($post['submitt'] == 'Save') {
                $table = new \VuBib\Db\Table\AgentType($this->adapter);
                $table->insertRecords($post['new_agenttype']);
            }
        }
        //edit an agent type
        if ($post['action'] == 'edit') {
            if ($post['submitt'] == 'Save') {
                if (null !== $post['id']) {
                    $table = new \VuBib\Db\Table\AgentType($this->adapter);
                    $table->updateRecord($post['id'], $post['edit_agenttype']);
                }
            }
        }
        //delete an agent type
        if ($post['action'] == 'delete') {
            if ($post['submitt'] == 'Delete') {
                if (null !== $post['agType_id']) {
                    foreach ($post['agType_id'] as $agentTypeId) {
                        $table = new \VuBib\Db\Table\WorkAgent(
                            $this->adapter
                        );
                        $table->deleteRecordByAgentTypeId($agentTypeId);

                        $table = new \VuBib\Db\Table\AgentType(
                            $this->adapter
                        );
                        $table->deleteRecord($agentTypeId);
                    }
                }
            }
        }
    }

    /**
     * Get records to display.
     *
     * @param Array $post contains posted elements of form
     *
     * @return Paginator                  $paginator
     */
    protected function getPaginator($post)
    {
        //edit, delete actions on agenttype
        if (!empty($post['action'])) {
            //add edit delete agenttype
            $this->doAction($post);

            //Cancel edit\delete
            if ($post['submitt'] == 'Cancel') {
                $table = new \VuBib\Db\Table\AgentType($this->adapter);

                return new Paginator(
                    new \Laminas\Paginator\Adapter\DbTableGateway($table)
                );
            }
        }
        // default: blank for listing in manage
        $table = new \VuBib\Db\Table\AgentType($this->adapter);
        return new Paginator(
            new \Laminas\Paginator\Adapter\DbTableGateway($table, null, 'type')
        );
    }

    /**
     * Invokes required template
     *
     * @param ServerRequestInterface  $request  server-side request.
     * @param RequestHandlerInterface $response request handler.
     *
     * @return RequestHandlerInterface
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $simpleAction = new \VuBib\Action\SimpleRenderAction(
            'vubib::agenttype/manage', $this->router,
            $this->template, $this->adapter
        );
        list($query, $post) = $simpleAction->getQueryAndPost($request);

        $paginator = $this->getPaginator($post);
        $paginator->setDefaultItemCountPerPage(15);
        //$allItems = $paginator->getTotalItemCount();

        $simpleAction = new \VuBib\Action\SimpleRenderAction(
            'vubib::agenttype/manage', $this->router,
            $this->template, $this->adapter
        );
        $pgs = $simpleAction->getNextPrevious($paginator, $query);

        return new HtmlResponse(
            $this->template->render(
                'vubib::agenttype/manage',
                [
                    'rows' => $paginator,
                    'previous' => $pgs['prev'],
                    'next' => $pgs['nxt'],
                    'countp' => $pgs['cp'],
                ]
            )
        );
    }
}
