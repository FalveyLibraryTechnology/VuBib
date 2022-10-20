<?php
/**
 * Home Page Action
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
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Router;
use Mezzio\Template;
use Laminas\View\Model\ViewModel;

/**
 * Class Definition for HomePageAction.
 *
 * @category VuBib
 * @package  Code
 * @author   Falvey Library <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 *
 * @link https://
 */
class HomePageAction implements RequestHandlerInterface
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

    //private $instructions;

    /**
     * HomePageAction constructor.
     *
     * @param Router\RouterInterface             $router   for routes
     * @param Template\TemplateRendererInterface $template for templates
     */
    public function __construct(Router\RouterInterface $router,
        Template\TemplateRendererInterface $template = null
    ) {
        $this->router = $router;
        $this->template = $template;
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
    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        return new HtmlResponse($this->template->render('vubib::home-page', $this));

        $layout = new ViewModel([
            'encoding' => 'utf-8',
            'cssPath'  => '/css/blog/',
        ]);
        $layout->setTemplate('layout::default');
        return new HtmlResponse($this->template->render('vubib::home-page', ['layout' => $layout]));
    }
}
