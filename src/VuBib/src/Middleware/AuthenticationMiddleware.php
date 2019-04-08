<?php
/**
 * Authentication Middleware
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
namespace VuBib\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Teapot\StatusCode\RFC\RFC7231;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Expressive\Router;
use Zend\Expressive\Template;

/**
 * Class Definition for Authentication Middleware.
 *
 * @category VuBib
 * @package  Code
 * @author   Falvey Library <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 *
 * @link https://
 */
class AuthenticationMiddleware implements MiddlewareInterface
{
    /**
     * Router\RouterInterface
     *
     * @var $router
     */
    private $_router;

    /**
     * Template\TemplateRendererInterface
     *
     * @var $template
     */
    private $_template;

    /**
     * String
     *
     * @var basePath
     */
    private $_basePath;

    /**
     * Zend\Session\Container
     *
     * @var $session
     */
    private $_session;

    /**
     * AuthenticationMiddleware constructor.
     *
     * @param Router\RouterInterface             $router   for routes
     * @param Template\TemplateRendererInterface $template for templates
     * @param string                             $basePath base url path
     * @param Zend\Session\Container             $session  zend session
     *
     * @return empty
     */
    public function __construct(
        Router\RouterInterface $router,
        Template\TemplateRendererInterface $template,
        $basePath, $session
    ) {
        $this->_router = $router;
        $this->_template = $template;
        $this->_basePath = rtrim($basePath, '/');
        $this->_session = $session;
    }

    /**
     * Invokes required template
     *
     * @param ServerRequestInterface  $request server-side request.
     * @param RequestHandlerInterface $handler request handler
     *
     * @return ResponseInterface
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        if (!isset($this->_session->id)) {
            return new RedirectResponse(
                sprintf(
                    $this->_basePath . '/login?redirect_to=%s',
                    $this->_getCurrentRequest($request)
                ), RFC7231::FOUND
            );
        }

        return $handler->handle($request);
    }

    /**
     * Get the current request uri
     *
     * @param ServerRequestInterface $request server-side request.
     *
     * @return string
     */
    private function _getCurrentRequest(
        ServerRequestInterface $request
    ) {

        /**
         * Uri
         *
         * @var UriInterface $uri
         */
        $uri = $request->getUri();

        $redirectTo = $this->_basePath . $uri->getPath();

        if ($uri->getQuery() !== '') {
            $redirectTo .= '?' . $uri->getQuery();
        }

        if ($uri->getFragment() !== '') {
            $redirectTo .= '#' . $uri->getFragment();
        }

        return urlencode($redirectTo);
    }
}
