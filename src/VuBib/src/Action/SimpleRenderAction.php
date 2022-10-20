<?php
/**
 * Simple Render Action
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
namespace VuBib\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Db\Adapter\Adapter;
use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Router;
use Mezzio\Template;

/**
 * Class Definition for SimpleRenderAction.
 *
 * @category VuBib
 * @package  Code
 * @author   Falvey Library <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 *
 * @link https://
 */
class SimpleRenderAction implements MiddlewareInterface
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
     * SimpleRenderAction constructor.
     *
     * @param String                             $templateName Name of the template
     * @param Router\RouterInterface             $router       for routes
     * @param Template\TemplateRendererInterface $template     for templates
     * @param Adapter                            $adapter      for db connection
     */
    public function __construct($templateName,
        Router\RouterInterface $router,
        Template\TemplateRendererInterface $template = null, Adapter $adapter
    ) {
        $this->templateName = $templateName;
        $this->router = $router;
        $this->template = $template;
        $this->adapter = $adapter;
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
        return new HtmlResponse(
            $this->template->render(
                $this->templateName, [
                'request' => $request, 'adapter' => $this->adapter]
            )
        );
    }

    /**
     * Get query params and post values
     *
     * @param ServerRequestInterface $request server-side request.
     *
     * @return Array
     */
    public function getQueryAndPost(ServerRequestInterface $request)
    {
        $query = [];
        $query = $request->getqueryParams();
        $post = [];
        if ($request->getMethod() == 'POST') {
            $post = $request->getParsedBody();
        }
        return [$query, $post];
    }

    /**
     * Get previous and next values for pagination
     *
     * @param Paginator $paginator paginator records
     * @param Array     $query     query params
     *
     * @return Array $pgs
     */
    public function getNextPrevious($paginator, $query)
    {
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

        $pgs['cp'] = $countPages;
        $pgs['prev'] = $previous;
        $pgs['nxt'] = $next;

        return $pgs;
    }
}
