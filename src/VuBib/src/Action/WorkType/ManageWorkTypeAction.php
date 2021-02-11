<?php
/**
 * Manage WorkType Action
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
 * Class Definition for ManageWorkTypeAction.
 *
 * @category VuBib
 * @package  Code
 * @author   Falvey Library <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 *
 * @link https://
 */
class ManageWorkTypeAction implements MiddlewareInterface
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
     * string
     */
    protected $flashMessage = null;

    /**
     * ManageWorkTypeAction constructor.
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
     * Adds worktype.
     *
     * @param Array $post contains posted elements of form
     *
     * @return empty
     */
    protected function doAdd($post)
    {
        if ($post['submitt'] == 'Save') {
            $table = new \VuBib\Db\Table\WorkType($this->adapter);
            $table->insertRecords([
                'text_fr' => $post['text_fr'],
                'text_en' => $post['text_en'] ?? null,
                'text_de' => $post['text_de'] ?? null,
                'text_es' => $post['text_es'] ?? null,
                'text_it' => $post['text_it'] ?? null,
                'text_nl' => $post['text_nl'] ?? null
            ]);
        }
    }

    /**
     * Edits worktype.
     *
     * @param Array $post contains posted elements of form
     *
     * @return empty
     */
    protected function doEdit($post)
    {
        if ($post['submitt'] == 'Save') {
            if (null !== $post['id']) {
                $table = new \VuBib\Db\Table\WorkType($this->adapter);
                $table->updateRecord($post['id'], [
                    'text_fr' => $post['text_fr'],
                    'text_en' => $post['text_en'] ?? null,
                    'text_de' => $post['text_de'] ?? null,
                    'text_es' => $post['text_es'] ?? null,
                    'text_it' => $post['text_it'] ?? null,
                    'text_nl' => $post['text_nl'] ?? null
                ]);
            }
        }
    }

    /**
     * Deletes worktype.
     *
     * @param Array $post contains posted elements of form
     *
     * @return empty
     */
    protected function doDelete($post)
    {
        if (isset($post['submitt'])) {
            if ($post['submitt'] == 'Delete') {
                if (null !== $post['worktype_id']) {
                    foreach ($post['worktype_id'] as $worktype_Id) {
                        $table = new \VuBib\Db\Table\Work($this->adapter);
                        $table->updateWorkTypeId($worktype_Id);
                        $table = new \VuBib\Db\Table\WorkType_WorkAttribute(
                            $this->adapter
                        );
                        $table->deleteRecordByWorkType($worktype_Id);
                        $table = new \VuBib\Db\Table\WorkType($this->adapter);
                        $table->deleteRecord($worktype_Id);
                    }
                }
            }
        }
    }

    /**
     * Removes attributes from a worktype.
     *
     * @param Array $post contains posted elements of form
     *
     * @return empty
     */
    protected function removeAttribute($post)
    {
        $attrs_to_remove = [];
        preg_match_all('/,?id_\d+/', $post['remove_attr'], $matches);
        foreach ($matches[0] as $id) {
            $attrs_to_remove[] = (int)preg_replace(
                "/^,?\w{2,3}_/", '', $id
            );
        }
        if (null !== $attrs_to_remove) {
            if (count($attrs_to_remove) != 0) {
                //remove attributes from a work type
                $table = new \VuBib\Db\Table\WorkType_WorkAttribute($this->adapter);
                $table->deleteAttributeFromWorkType($post['id'], $attrs_to_remove);
            }
        }
    }

    /**
     * Adds attributes to a worktype.
     *
     * @param Array $post contains posted elements of form
     *
     * @return empty
     */
    protected function addAttribute($post)
    {
        $attrs_to_add = [];
        preg_match_all('/,?nid_\d+/', $post['sort_order'], $matches);
        foreach ($matches[0] as $id) {
            $attrs_to_add[] = (int)preg_replace(
                "/^,?\w{2,3}_/", '', $id
            );
        }
        if (null !== $attrs_to_add) {
            if (count($attrs_to_add) != 0) {
                //Add attributes to work type
                $table = new \VuBib\Db\Table\WorkType_WorkAttribute($this->adapter);
                $table->addAttributeToWorkType($post['id'], $attrs_to_add);
            }
        }
    }

    /**
     * Sort attributes of a worktype.
     *
     * @param Array $post contains posted elements of form
     *
     * @return empty
     */
    protected function doAttributeSort($post)
    {
        if (
            $post['submitt'] == 'Save' &&
            !empty($post['remove_attr']) &&
            !empty($post['sort_order'])
        ) {
            if (!empty($post['remove_attr'])) {
                $this->removeAttribute($post);
            }
            if (!empty($post['sort_order'])) {
                $this->addAttribute($post);
            }
            //after adding attrs to work type, adjust ranks
            $table = new \VuBib\Db\Table\WorkType_WorkAttribute($this->adapter);
            $table->updateWorkTypeAttributeRank($post['id'], $post['sort_order']);
            $this->flashMessage = 'New order saved.';
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
        //add a new work type
        if ($post['action'] == 'new') {
            $this->doAdd($post);
        }
        //edit a work type
        if ($post['action'] == 'edit') {
            $this->doEdit($post);
        }
        //delete a work type
        if ($post['action'] == 'delete') {
            $this->doDelete($post);
        }
        //add, remove attributes to work type
        if ($post['action'] == 'sortable') {
            $this->doAttributeSort($post);
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
        //add, edit, delete actions on worktype
        if (!empty($post['action'])) {
            //add edit delete worktypes and manage attributes
            $this->doAction($post);

            //Cancel add\edit\delete
            if ($post['submitt'] == 'Cancel') {
                $table = new \VuBib\Db\Table\WorkType($this->adapter);

                return new Paginator(
                    new \Zend\Paginator\Adapter\DbTableGateway($table)
                );
            }
        }
        // default: blank for listing in manage
        $table = new \VuBib\Db\Table\WorkType($this->adapter);

        return new Paginator(new \Zend\Paginator\Adapter\DbTableGateway($table));
    }

    /**
     * Invokes required template
     *
     * @param ServerRequestInterface  $request  server-side request.
     * @param RequestHandlerInterface $response response to client side.
     *
     * @return HtmlResponse
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $simpleAction = new \VuBib\Action\SimpleRenderAction(
            'vubib::worktype/manage', $this->router,
            $this->template, $this->adapter
        );
        list($query, $post) = $simpleAction->getQueryAndPost($request);

        $paginator = $this->getPaginator($post);
        $paginator->setDefaultItemCountPerPage(15);
        //$allItems = $paginator->getTotalItemCount();

        $simpleAction = new \VuBib\Action\SimpleRenderAction(
            'vubib::worktype/manage', $this->router,
            $this->template, $this->adapter
        );
        $pgs = $simpleAction->getNextPrevious($paginator, $query);

        $searchParams = [];

        if (isset($post['action']) && $post['action'] == 'sortable'
            && $post['submitt'] == 'Save'
        ) {
            return new HtmlResponse(
                $this->template->render(
                    'vubib::worktype/manageattribute',
                    [
                        'rows' => $paginator,
                        'previous' => $pgs['prev'],
                        'next' => $pgs['nxt'],
                        'countp' => $pgs['cp'],
                        'request' => $request,
                        'adapter' => $this->adapter,
                        'searchParams' => implode('&', $searchParams),
                        'flashMessage' => $this->flashMessage,
                    ]
                )
            );
        } else {
            return new HtmlResponse(
                $this->template->render(
                    'vubib::worktype/manage',
                    [
                        'rows' => $paginator,
                        'previous' => $pgs['prev'],
                        'next' => $pgs['nxt'],
                        'countp' => $pgs['cp'],
                        'searchParams' => implode('&', $searchParams),
                        'flashMessage' => $this->flashMessage,
                    ]
                )
            );
        }
    }
}
