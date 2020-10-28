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
use Zend\Db\Adapter\Adapter;
use Zend\Db\TableGateway\TableGateway;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Router;
use Zend\Expressive\Template;
use Zend\Paginator\Paginator;
use Zend\Session;

/**
 * Class Definition for MoveClassificationAction.
 *
 * @category VuBib
 * @package  Code
 * @author   Falvey Library <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 *
 * @link https://
 */
class MoveClassificationAction implements MiddlewareInterface
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
     * MoveClassificationAction constructor.
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
        $query = $request->getQueryParams();
        $id = $query['id'];

        //fetch folders
        $classifications = [];
        $folderTable = new \VuBib\Db\Table\Folder($this->adapter);
        $parentChain = $folderTable->getParentChain($id);
        $parentId = null;
        foreach ($parentChain as $folderId) {
            $siblings = [];
            $folder = $folderTable->findRecordById($folderId);
            $parentId = $folder['parent_id'];

            $folderSiblings = $folderTable->getSiblings($folder['parent_id']);
            foreach ($folderSiblings as $sibling) {
                if ($sibling['id'] == $folderId && $sibling['id'] != $id) {
                    $sibling['selected'] = true;
                }
                $siblings[] = $sibling;
            }
            $classifications[] = $siblings;
        }

        return new HtmlResponse(
            $this->template->render(
                'vubib::classification/move',
                [
                    'id' => $id,
                    'parent_id' => $parentId,
                    'classifications' => $classifications,
                    'request' => $request,
                    'adapter' => $this->adapter
                ]
            )
        );
    }
}
