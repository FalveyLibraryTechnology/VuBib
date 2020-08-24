<?php
/**
 * Attributes WorkType Action
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
namespace VuBib\Action\WorkType;

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
 * Class Definition for AttributesWorkTypeAction.
 *
 * @category VuBib
 * @package  Code
 * @author   Falvey Library <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 *
 * @link https://
 */
class AttributesWorkTypeAction implements MiddlewareInterface
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
     * Adds worktype attributes.
     *
     * @param Array $post contains posted elements of form
     *
     * @return empty
     */
    protected function doAdd($post)
    {
        if ($post['submitt'] == 'Save') {
            $table = new \VuBib\Db\Table\WorkAttribute($this->adapter);
            $table->addAttribute($post['new_attribute'], $post['field_type']);
        }
    }

    /**
     * Edits worktype attributes.
     *
     * @param Array $post contains posted elements of form
     *
     * @return empty
     */
    protected function doEdit($post)
    {
        if ($post['submitt'] == 'Save') {
            if (null !== $post['id']) {
                $table = new \VuBib\Db\Table\WorkAttribute($this->adapter);
                $table->updateRecord($post['id'], $post['edit_attribute']);
            }
        }
    }

    /**
     * Deletes worktype attributes.
     *
     * @param Array $post contains posted elements of form
     *
     * @return empty
     */
    protected function doDelete($post)
    {
        if (isset($post['submitt'])) {
            if ($post['submitt'] == 'Delete') {
                if (null !== $post['workattr_id']) {
                    foreach ($post['workattr_id'] as $workattr_Id) {
                        //no
                        $table = new \VuBib\Db\Table\Work_WorkAttribute(
                            $this->adapter
                        );
                        $table->deleteWorkAttributeFromWork($workattr_Id);
                        //yes
                        $table = new \VuBib\Db\Table\WorkType_WorkAttribute(
                            $this->adapter
                        );
                        $table->deleteAttributeFromAllWorkTypes($workattr_Id);
                        //yes
                        $table = new \VuBib\Db\Table\WorkAttribute_Option(
                            $this->adapter
                        );
                        $table->deleteWorkAttributeOptions($workattr_Id);
                        //no
                        $table = new \VuBib\Db\Table\WorkAttribute(
                            $this->adapter
                        );
                        $table->deleteRecord($workattr_Id);
                    }
                }
            }
        }
    }

    /**
     * Adds worktype attributes.
     *
     * @param Array $post contains posted elements of form
     *
     * @return empty
     */
    protected function doAddSubAttribute($post)
    {
        if ($post['submitt'] == 'Save') {
            $table = new \VuBib\Db\Table\WorkAttribute_SubAttribute($this->adapter);
            $table->addSubAttribute($post['wkattr_id'], $post['subattribute']);
        }
    }

    /**
     * Edits worktype sub attribute.
     *
     * @param Array $post contains posted elements of form
     *
     * @return empty
     */
    protected function doEditSubAttribute($post)
    {
        if ($post['submitt'] == 'Save') {
            if (null !== $post['subattr_id']) {
                $table = new \VuBib\Db\Table\WorkAttribute_SubAttribute(
                    $this->adapter
                );
                $table->editSubAttribute(
                    $post['subattr_id'], $post['attr_id'],
                    $post['edit_subattribute']
                );
            }
        }
    }

    /**
     * Edits worktype sub attribute.
     *
     * @param Array $post contains posted elements of form
     *
     * @return empty
     */
    protected function doDeleteSubAttribute($post)
    {
        if ($post['submitt'] == 'Save') {
            if (null !== $post['subattr_id']) {
                $table = new \VuBib\Db\Table\WorkAttribute_SubAttribute(
                    $this->adapter
                );
                $table->deleteSubAttribute(
                    $post['subattr_id'], $post['attr_id'],
                    $post['edit_subattribute']
                );
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
        //add new attribute
        if ($post['action'] == 'new') {
            $this->doAdd($post);
        }
        //edit attribute
        if ($post['action'] == 'edit') {
            $this->doEdit($post);
        }
        //delete attribute
        if ($post['action'] == 'delete') {
            $this->doDelete($post);
        }
        //add sub attribute
        if ($post['action'] == 'add_subattribute') {
            $this->doAddSubAttribute($post);
        }
        //edit sub attribute
        if ($post['action'] == 'edit_subattribute') {
            $this->doEditSubAttribute($post);
        }
        //delete sub attribute
        if ($post['action'] == 'delete_subattribute') {
            $this->doDeleteSubAttribute($post);
        }
    }

    /**
     * Call aprropriate function for each action.
     *
     * @param Array $post contains posted elements of form
     *
     * @return Paginator                  $paginator
     */
    protected function getPaginator($post)
    {
        //add, edit, delete actions on attribute
        if (!empty($post['action'])) {
            //add edit delete attribute
            $this->doAction($post);
        }
        // default: blank for listing in manage
        $table = new \VuBib\Db\Table\WorkAttribute($this->adapter);

        return new Paginator(
            new \Zend\Paginator\Adapter\DbTableGateway(
                $table, null, ['type','field']
            )
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
        $simpleAction = new \VuBib\Action\SimpleRenderAction(
            'vubib::worktype/attributes_worktype', $this->router,
            $this->template, $this->adapter
        );
        list($query, $post) = $simpleAction->getQueryAndPost($request);

        $paginator = $this->getPaginator($post);
        $paginator->setDefaultItemCountPerPage(15);
        //$allItems = $paginator->getTotalItemCount();

        $simpleAction = new \VuBib\Action\SimpleRenderAction(
            'vubib::worktype/attributes_worktype', $this->router,
            $this->template, $this->adapter
        );
        $pgs = $simpleAction->getNextPrevious($paginator, $query);

        $searchParams = [];

        if (isset($post['action']) && $post['action'] == 'edit_subattribute') {
            $searchParams[] = urlencode($post['attr_id']);
            return new HtmlResponse(
                $this->template->render(
                    'vubib::worktype/subattribute',
                    [
                        'request' => $request,
                        'adapter' => $this->adapter,
                        'searchParams' => implode('&', $searchParams),
                    ]
                )
            );
        } else {
            return new HtmlResponse(
                $this->template->render(
                    'vubib::worktype/attributes_worktype',
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
}
