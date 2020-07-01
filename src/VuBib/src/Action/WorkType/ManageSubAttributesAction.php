<?php
/**
 * Attribute Manage Options Action
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

/**
 * Class Definition for ManageSubAttributesAction.
 *
 * @category VuBib
 * @package  Code
 * @author   Falvey Library <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 *
 * @link https://
 */
class ManageSubAttributesAction implements MiddlewareInterface
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
     * AttributeManageOptionsAction constructor.
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
     * Adds attribute options.
     *
     * @param Array $post contains posted elements of form
     *
     * @return empty
     */
    protected function doAdd($post)
    {
        if ($post['submitt'] == 'Save') {
            $table = new \VuBib\Db\Table\WorkAttribute_SubAttribute($this->adapter);
            $subattr_id = $table->addSubAttributeReturnId(
                $post['wkat_id'], $post['new_subattr']
            );

            //get option ids of work attribute
            $table = new \VuBib\Db\Table\WorkAttribute_Option($this->adapter);
            $optIds = $table->getOptionIdsForAttribute($post['wkat_id']);

            //add a record for each option in attribute_option_subattribute table
            $table = new \VuBib\Db\Table\Attribute_Option_SubAttribute(
                $this->adapter
            );
            foreach ($optIds as $optId) {
                $table->insertRecord($post['wkat_id'], $optId, $subattr_id);
            }
        }
    }

    /**
     * Edits attribute options.
     *
     * @param Array $post contains posted elements of form
     *
     * @return empty
     */
    protected function doEdit($post)
    {
        if ($post['submitt'] == 'Save') {
            if (null !== $post['id']) {
                $table = new \VuBib\Db\Table\WorkAttribute_SubAttribute(
                    $this->adapter
                );
                $table->editSubAttribute(
                    $post['id'], $post['wkat_id'],
                    $post['edit_subattr']
                );
            }
        }
    }

    /**
     * Deletes sub attributes.
     *
     * @param Array $post  contains posted elements of form
     * @param Array $query url query parameters
     *
     * @return empty
     */
    protected function doDelete($post, $query)
    {
        if (isset($post['submitt'])) {
            if ($post['submitt'] == 'Delete') {
                if (null !== $post['subattr_id']) {
                    $table = new \VuBib\Db\Table\Attribute_Option_SubAttribute(
                        $this->adapter
                    );
                    $table->deleteRecordBySubAttributeId($post['subattr_id']);

                    $table = new \VuBib\Db\Table\WorkAttribute_SubAttribute(
                        $this->adapter
                    );
                    $table->deleteRecordById($post['subattr_id']);
                }
            }
        }
    }

    /**
     * Action based on action parameter.
     *
     * @param Array $post  contains posted elements of form
     * @param Array $query url query parameters
     *
     * @return empty
     */
    protected function doAction($post, $query)
    {
        //add new option
        if ($post['action'] == 'new') {
            $this->doAdd($post);
        }
        //edit option
        if ($post['action'] == 'edit') {
            $this->doEdit($post);
        }
        //delete option
        if ($post['action'] == 'delete') {
            $this->doDelete($post, $query);
        }
    }

    /**
     * Call aprropriate function for each action.
     *
     * @param Array $query url query parameters
     * @param Array $post  contains posted elements of form
     *
     * @return Paginator                  $paginator
     */
    protected function getPaginator($query, $post)
    {
        $order = "";
        $wkat_id = $post['wkat_id'] ?? $query['wkat_id'];
        if (!empty($post['action'])) {
            //add edit delete sub attribute
            $this->doAction($post, $query);

            //Cancel add\edit\delete
            if ($post['submitt'] == 'Cancel') {
                $table = new \VuBib\Db\Table\WorkAttribute_SubAttribute(
                    $this->adapter
                );
                $subattrs = $table->displaySubAttributes($wkat_id);

                return $subattrs;
            }
        }

        // default: blank for listing in manage
        $table = new \VuBib\Db\Table\WorkAttribute_SubAttribute($this->adapter);
        $subattrs = $table->displaySubAttributes($wkat_id, $order);

        return $subattrs;
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
        $countPages = 0;
        $query = $request->getqueryParams();

        $post = [];
        if ($request->getMethod() == 'POST') {
            $post = $request->getParsedBody();
        }
        $subattrs = $this->getPaginator($query, $post);
        /*$paginator->setDefaultItemCountPerPage(15);
        //$allItems = $paginator->getTotalItemCount();
        $countPages = $paginator->count();

        $currentPage = isset($query['page']) ? $query['page'] : 1;
        if ($currentPage < 1) {
            $currentPage = 1;
        }
        $paginator->setCurrentPageNumber($currentPage);

        if ($currentPage == $countPages) {
            $next = $currentPage;
            $previous = $currentPage - 1;
        } elseif ($currentPage == 1) {
            $next = $currentPage + 1;
            $previous = 1;
        } else {
            $next = $currentPage + 1;
            $previous = $currentPage - 1;
        }*/

        $searchParams = [];
        if (!empty($query['wkat_id'])) {
            $searchParams[] = 'wkat_id=' . urlencode($query['wkat_id']);
        }
        $wkat_id = $post['wkat_id'] ?? $query['wkat_id'];

        return new HtmlResponse(
            $this->template->render(
                'vubib::worktype/manage_subattributes',
                [
                'rows' => $subattrs,
                'wkat_id' => $wkat_id,
                'searchParams' => implode('&', $searchParams),
                'request' => $request,
                'adapter' => $this->adapter,
                ]
            )
        );
    }
}
