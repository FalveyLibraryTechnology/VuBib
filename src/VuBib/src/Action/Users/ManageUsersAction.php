<?php
/**
 * Manage Users Action
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
namespace VuBib\Action\Users;

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
 * Class Definition for ManageUsersAction.
 *
 * @category VuBib
 * @package  Code
 * @author   Falvey Library <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 *
 * @link https://
 */
class ManageUsersAction implements MiddlewareInterface
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

    //private $dbh;
    //private $qstmt;

    /**
     * ManageUsersAction constructor.
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
     * Adds new user.
     *
     * @param Array $post contains posted elements of form
     *
     * @return empty
     */
    protected function doAdd($post)
    {
        if ($post['submitt'] == 'Save') {
            //echo "<pre>";print_r($post);echo"</pre>";
            $table = new \VuBib\Db\Table\User($this->adapter);
            $table->insertRecords(
                $post['newuser_name'], $post['new_username'],
                md5($post['new_user_pwd']), $post['access_level']
            );
        }
    }

    /**
     * Edit user.
     *
     * @param Array $post contains posted elements of form
     *
     * @return empty
     */
    protected function doEdit($post)
    {
        if ($post['submitt'] == 'Save') {
            if (null !== $post['id']) {
                if (empty($post['edit_user_pwd'])) {
                    $pwd = null;
                } else {
                    $pwd = md5($post['edit_user_pwd']);
                }
                $table = new \VuBib\Db\Table\User($this->adapter);
                $table->updateRecord(
                    $post['id'], $post['edituser_name'],
                    $post['edit_username'], $pwd,
                    $post['access_level']
                );
            }
        }
    }

    /**
     * Delete user.
     *
     * @param Array $post contains posted elements of form
     *
     * @return empty
     */
    protected function doDelete($post)
    {
        if ($post['submitt'] == 'Delete') {
            if (null !== $post['user_id']) {
                foreach ($post['user_id'] as $userId) {
                    $table = new \VuBib\Db\Table\User($this->adapter);
                    $table->deleteRecord($userId);
                }
            }
        }
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
        //add new user
        if ($post['action'] == 'new') {
            $this->doAdd($post);
        }
        //edit user
        if ($post['action'] == 'edit') {
            $this->doEdit($post);
        }
        //delete user
        if ($post['action'] == 'delete') {
            $this->doDelete($post);
        }
        //change user module access
        if ($post['action'] == 'users_access') {
            $table = new \VuBib\Db\Table\Module_Access($this->adapter);
            $all_modules = $table->getAllModules();
            foreach ($all_modules as $row) {
                if (isset($post['access'][$row])) {
                    if (isset($post['access'][$row]['Super User'])) {
                        $table = new \VuBib\Db\Table\Module_Access($this->adapter);
                        $table->setModuleAccess($row, 'Super User');
                    } elseif (!isset($post['access'][$row]['Super User'])) {
                        $table = new \VuBib\Db\Table\Module_Access($this->adapter);
                        $table->unsetModuleAccess($row, 'Super User');
                    }

                    if (isset($post['access'][$row]['User'])) {
                        $table = new \VuBib\Db\Table\Module_Access($this->adapter);
                        $table->setModuleAccess($row, 'User');
                    } elseif (!isset($post['access'][$row]['User'])) {
                        $table = new \VuBib\Db\Table\Module_Access($this->adapter);
                        $table->unsetModuleAccess($row, 'User');
                    }
                } else {
                    //for role superuser, set module to 0
                    $table = new \VuBib\Db\Table\Module_Access($this->adapter);
                    $table->unsetModuleAccess($row, 'Super User');

                    //for role user,set module to 0
                    $table = new \VuBib\Db\Table\Module_Access($this->adapter);
                    $table->unsetModuleAccess($row, 'User');
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
        //add, edit, delete actions on user
        if (!empty($post['action'])) {
            //add edit delete users
            $this->doAction($post);

            //Cancel add\edit\delete
            if ($post['submitt'] == 'Cancel') {
                $table = new \VuBib\Db\Table\User($this->adapter);

                return new Paginator(
                    new \Zend\Paginator\Adapter\DbTableGateway($table)
                ); //this
            }
        }
        // default: blank for listing in manage
        $table = new \VuBib\Db\Table\User($this->adapter);

        return new Paginator(new \Zend\Paginator\Adapter\DbTableGateway($table));
    }

    /**
     * Invokes required template
     *
     * @param ServerRequestInterface  $request  server-side request.
     * @param RequestHandlerInterface $handler  Request Handler.
     *
     * @return ResponseInterface
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $simpleAction = new \VuBib\Action\SimpleRenderAction(
            'vubib::users/manage', $this->router,
            $this->template, $this->adapter
        );
        list($query, $post) = $simpleAction->getQueryAndPost($request);

        $paginator = $this->getPaginator($post);
        $paginator->setDefaultItemCountPerPage(15);
        //$allItems = $paginator->getTotalItemCount();

        $simpleAction = new \VuBib\Action\SimpleRenderAction(
            'vubib::users/manage', $this->router,
            $this->template, $this->adapter
        );
        $pgs = $simpleAction->getNextPrevious($paginator, $query);

        $searchParams = [];

        return new HtmlResponse(
            $this->template->render(
                'vubib::users/manage',
                [
                    'rows' => $paginator,
                    'previous' => $pgs['prev'],
                    'next' => $pgs['nxt'],
                    'countp' => $pgs['cp'],
                    'searchParams' => implode('&', $searchParams),
                ]
            )
        );
    }
}
