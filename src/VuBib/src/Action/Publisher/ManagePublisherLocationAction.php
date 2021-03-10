<?php
/**
 * Manage Publisher Location Action
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
namespace VuBib\Action\Publisher;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Teapot\StatusCode\RFC\RFC7231;
use Zend\Db\Adapter\Adapter;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Expressive\Router;
use Zend\Expressive\Template;

/**
 * Class Definition for ManagePublisherLocationAction.
 *
 * @category VuBib
 * @package  Code
 * @author   Falvey Library <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 *
 * @link https://
 */
class ManagePublisherLocationAction implements MiddlewareInterface
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
     * ManagePublisherLocationAction constructor.
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
     * Delete publisher location.
     *
     * @param Array $post  contains posted elements of form
     * @param Array $query url query parameters
     *
     * @return empty
     */
    protected function doDelete($post, $query)
    {
        if ($post['submitt'] == 'Delete') {
            if (null !== $post['id'] && ((count($post['locids'])) >= 0)) {
                $table = new \VuBib\Db\Table\WorkPublisher($this->adapter);
                $table->removePublisherLocations($query['id'], $post['locids']);

                $table = new \VuBib\Db\Table\PublisherLocation($this->adapter);
                $table->deletePublisherRecordById($post['id'], $post['locids']);
            }
        }
    }

    /**
     * Merge publisher locations.
     *
     * @param Array $post  contains posted elements of form
     * @param Array $query url query parameters
     *
     * @return empty
     */
    protected function doMerge($post, $query)
    {
        if ($post['submitt'] == 'Merge') {
            if (null !== $post['id']) {
                $table = new \VuBib\Db\Table\WorkPublisher($this->adapter);
                $table->updatePublisherLocationId(
                    $query['id'],
                    $post['sourceids'], $post['destid']
                );

                $table = new \VuBib\Db\Table\PublisherLocation($this->adapter);
                $table->deletePublisherRecordById($query['id'], $post['sourceids']);
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
        //add a new publisher
        if ($post['action'] == 'new') {
            if ($post['submitt'] == 'Save') {
                $table = new \VuBib\Db\Table\PublisherLocation($this->adapter);
                $table->addPublisherLocation(
                    $query['id'],
                    $post['add_publisherloc']
                );
            }
        }

        //add a new publisher
        if ($post['action'] == 'edit') {
            if ($post['submitt'] == 'Save') {
                $table = new \VuBib\Db\Table\PublisherLocation($this->adapter);
                $table->updatePublisherLocation(
                    $post['location_id'],
                    $post['location_newname']
                );
            }
        }

        //delete a location for a publisher
        if ($post['action'] == 'delete') {
            $this->doDelete($post, $query);
        }

        //Merge publisher locations
        if ($post['action'] == 'merge') {
            $this->doMerge($post, $query);
        }
    }

    /**
     * Get records to display.
     *
     * @param Array $query url query parameters
     * @param Array $post  contains posted elements of form
     *
     * @return Paginator                  $paginator
     */
    public function getPaginator($query, $post)
    {
        //add location based on action query parameter
        if (!empty($post['action'])) {
            //add delete merge publisher locations
            $this->doAction($post, $query);
        }

        // default: blank/missing search
        $table = new \VuBib\Db\Table\PublisherLocation($this->adapter);
        $paginator = $table->findPublisherLocations($query['id']);

        return $paginator;
    }

    /**
     * Invokes required template
     *
     * @param ServerRequestInterface  $request server-side request.
     * @param RequestHandlerInterface $handler Response Handler.
     *
     * @return ResponseInterface
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $simpleAction = new \VuBib\Action\SimpleRenderAction(
            'vubib::publisher/manage_location', $this->router,
            $this->template, $this->adapter
        );
        list($query, $post) = $simpleAction->getQueryAndPost($request);

        $paginator = $this->getPaginator($query, $post);
        $paginator->setDefaultItemCountPerPage(15);
        $pgs = $simpleAction->getNextPrevious($paginator, $query);

        //Merge publisher locations
        if (($post['action'] ?? null) == 'merge') {
            $reqParams = $request->getServerParams();
            $redirectUrl = $reqParams['REDIRECT_BASE']
                . $this->router->generateUri('manage_publisher')
                . '?merge=success';
            return new RedirectResponse($redirectUrl, RFC7231::FOUND);
        }

        return new HtmlResponse(
            $this->template->render(
                'vubib::publisher/manage_location',
                [
                    'rows' => $paginator,
                    'previous' => $pgs['prev'],
                    'next' => $pgs['nxt'],
                    'countp' => $pgs['cp'],
                    //'searchParams' => implode('&', $searchParams),
                    'request' => $request,
                    'adapter' => $this->adapter,
                ]
            )
        );
    }
}
