<?php
/**
 * Manage Classification Action
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
namespace VuBib\Action\Classification;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Teapot\StatusCode\RFC\RFC7231;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\TableGateway\TableGateway;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Router;
use Mezzio\Template;
use Laminas\Paginator\Paginator;
use Laminas\Session;

/**
 * Class Definition for ManageClassificationAction.
 *
 * @category VuBib
 * @package  Code
 * @author   Falvey Library <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 *
 * @link https://
 */
class ManageClassificationAction implements MiddlewareInterface
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

    /**
     * ID to redirect to after an action
     *
     * @var $redirectID
     */
    protected $redirectID = null;

    /**
     * ManageClassificationAction constructor.
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
        // bulk delete from Classification/Manage
        if ($post['action'] == 'bulkdelete') {
            $table = new \VuBib\Db\Table\Folder($this->adapter);
            foreach ($post['id'] as $id) {
                $table->mergeDelete(['id' => $id]);
            }
            $this->messages[] = count($post['id']) . ' keywords deleted.';
        }

        //add folder
        if ($post['action'] == 'new') {
            if ($post['submit'] == 'Save') {
                $parentId = !empty($post['parent_id']) && $post['parent_id'] != '-1'
                    ? $post['parent_id']
                    : null;
                $sortorder = !empty($post['new_classif_sortorder'])
                    ? $post['new_classif_sortorder']
                    : null;
                $table = new \VuBib\Db\Table\Folder($this->adapter);
                $table->insertRecords(
                    $parentId,
                    $post['new_classif_engtitle'],
                    $post['new_classif_frenchtitle'],
                    $post['new_classif_germantitle'],
                    $post['new_classif_dutchtitle'],
                    $post['new_classif_spanishtitle'],
                    $post['new_classif_italiantitle'],
                    $sortorder
                );
            }
        }
        //edit folder
        if ($post['action'] == 'edit') {
            $table = new \VuBib\Db\Table\Folder($this->adapter);

            // Delete
            if ($post['submit'] == 'Delete') {
                $table->mergeDelete(['id' => $post['id']]);
            // Update
            } elseif ($post['submit'] == 'Save') {
                if (null !== $post['id']) {
                    $sortorder = !empty($post['edit_sortorder'])
                        ? $post['edit_sortorder']
                        : null;
                    $table = new \VuBib\Db\Table\Folder($this->adapter);
                    $table->updateRecord(
                        $post['id'], $post['edit_texten'], $post['edit_textfr'],
                        $post['edit_textde'], $post['edit_textnl'],
                        $post['edit_textes'], $post['edit_textit'],
                        $sortorder
                    );
                    // Move in case of edit
                    $this->doMove($post);
                }
            }
        }
        //move folder
        if ($post['action'] == 'move') {
            if (isset($post['submit_save'])) {
                $this->doMove($post);
            }
        }
        //merge folder
        if ($post['action'] == 'merge_classification') {
            $this->doMerge($post);
        }
    }

    /**
     * Move folder.
     *
     * @param Array $post contains posted elements of form
     *
     * @return empty
     */
    protected function doMove($post)
    {
        if ($post['submit_save'] == 'Save' || $post['submit'] == 'Save') {
            $newParent = $post['new_parent'] == -1
                ? NULL
                : $post['new_parent'];
            $table = new \VuBib\Db\Table\Folder($this->adapter);
            $table->moveFolder($post['id'], $newParent);
            $this->redirectID = $post['new_parent'];
        }
    }

    /**
     * Merge folders.
     *
     * @param Array $post contains posted elements of form
     *
     * @return empty
     */
    protected function doMerge($post)
    {
        $src_cnt = count($post['select_source_fl']);
        $dst_cnt = count($post['select_dest_fl']);

        if ($post['select_source_fl'][$src_cnt - 1] == '') {
            $source_id = $post['select_source_fl'][$src_cnt - 2];
        } else {
            $source_id = $post['select_source_fl'][$src_cnt - 1];
        }

        if ($post['select_dest_fl'][$dst_cnt - 1] == '') {
            $dest_id = $post['select_dest_fl'][$dst_cnt - 2];
        } else {
            $dest_id = $post['select_dest_fl'][$dst_cnt - 1];
        }
        // Move children
        $table = new \VuBib\Db\Table\Folder($this->adapter);
        $table->mergeFolder($source_id, $dest_id);

        //first delete potential duplicates to avoid key violation
        $table = new \VuBib\Db\Table\Work_Folder($this->adapter);
        $table->mergeWkFlDelete($source_id, $dest_id);

        // Move works
        $table = new \VuBib\Db\Table\Work_Folder($this->adapter);
        $table->mergeWkFlUpdate($source_id, $dest_id);

        //Track merge history, update previous history,then add the current merge:
        $table = new \VuBib\Db\Table\Folder_Merge_History($this->adapter);
        $table->mergeFlMgHistUpdate($source_id, $dest_id);

        //Track merge history, update previous history,then add the current merge:
        $table = new \VuBib\Db\Table\Folder_Merge_History($this->adapter);
        $table->insertRecord($source_id, $dest_id);

        // Purge
        $table = new \VuBib\Db\Table\Folder($this->adapter);
        $table->mergeDelete($source_id);

        // Redirect
        $this->redirectID = $dest_id;
    }

    /**
     * Get records to display.
     *
     * @param Array $query url query parameters
     * @param Array $post  contains posted elements of form
     *
     * @return Paginator                  $paginator
     */
    protected function getPaginator($query, $post)
    {
        if (!empty($post['action'])) {
            //add edit move merge folder
            $this->doAction($post);
        }

        if (!empty($query['action'])) {
            //manage classification hierarchy
            if ($query['action'] == 'get_children') {
                $table = new \VuBib\Db\Table\Folder($this->adapter);
                $rows = $table->getChild($query['id']);
                $paginator = new Paginator(
                    new \Laminas\Paginator\Adapter\ArrayAdapter($rows)
                );

                return $paginator;
            }
        }

        // default: blank for listing in manage
        $table = new \VuBib\Db\Table\Folder($this->adapter);
        $paginator = $table->findParent();

        return $paginator;
    }

    /**
     * Get trail to display as breadcrumb.
     *
     * @param Array $query url query parameters
     *
     * @return string                  $ts
     */
    protected function getFolderNameForViewLinks($query)
    {
        // get folder name for bread crumb
        if ($query['action'] == 'get_children') {
            $table = new \VuBib\Db\Table\Folder($this->adapter);
            return $table->getTrail($query['id']);
        }
        return [];
    }

    /**
     * Set search parameters for pagination.
     *
     * @param Array $query url query parameters
     *
     * @return Array                  $searchParams
     */
    protected function getSearchParams($query)
    {
        $searchParams = [];
        if (!empty($query['id']) && !empty($query['fl'])
            && $query['action'] == 'get_children'
        ) {
            $searchParams[] = 'id=' . urlencode($query['id']) .
              '&fl=' . urlencode($query['fl']) . '&action=get_children';
        }
        return $searchParams;
    }

    /**
     * Invokes required template
     *
     * @param ServerRequestInterface  $request server-side request.
     * @param RequestHandlerInterface $handler request handler.
     *
     * @return ResponseInterface
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $this->messages = [];

        $session = new Session\Container('manageClassifications');

        $query = $request->getqueryParams();
        $post = [];
        if ($request->getMethod() == 'POST') {
            $post = $request->getParsedBody();
            $query = $session->prevQuery;
        }
        $paginator = $this->getPaginator($query, $post);

        if (isset($query['merge']) && $query['merge'] == 'success') {
            $this->messages[] = 'Merge successful!';
        }

        // redirect to new folder after move
        if (!empty($this->redirectID)) {
            $reqParams = $request->getServerParams();
            $redirectUrl = $reqParams['REDIRECT_BASE']
                . $this->router->generateUri('manage_classification');
            // Check for home
            if ($this->redirectID != '-1') {
                $redirectUrl .= '?id=' . $this->redirectID . '&action=get_children';
            }
            $redirectUrl = str_replace('//', '/', $redirectUrl);
            return new RedirectResponse($redirectUrl, RFC7231::FOUND);
        }

        $session->prevQuery = $query;

        $paginator->setDefaultItemCountPerPage(1000);
        $countPages = $paginator->count();

        $currentPage = $query['page'] ?? 1;
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
        }

        $searchParams = $this->getSearchParams($query);

        if (null !== $searchParams) {
            $searchParams = implode('&', $searchParams);
        } else {
            $searchParams = '';
        }

        $ts = [];
        if (isset($query['action'])) {
            $ts = $this->getFolderNameForViewLinks($query);
        }

        return new HtmlResponse(
            $this->template->render(
                'vubib::classification/manage',
                [
                    'rows' => $paginator,
                    'previous' => $previous,
                    'next' => $next,
                    'countp' => $countPages,
                    //'previous_folder' => $previous_folder,
                    'trail' => $ts,
                    'request' => $request,
                    'searchParams' => $searchParams,
                    'flashMessages' => $this->messages
                ]
            )
        );
    }
}
