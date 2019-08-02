<?php
/**
 * Login Page Action
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
use Teapot\StatusCode\RFC\RFC7231;
use VuBib\Repository\UserAuthenticationInterface;
use Zend\Db\Adapter\Adapter;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Expressive\Router;
use Zend\Expressive\Template;
use Zend\Form\Form;

/**
 * Class Definition for LoginPageAction.
 *
 * @category VuBib
 * @package  Code
 * @author   Falvey Library <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 *
 * @link https://
 */
class LoginPageAction implements MiddlewareInterface
{
    /**
     * String
     *
     * @var $PAGE_TEMPLATE
     */
    const PAGE_TEMPLATE = 'vubib::login-page';

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
     * UserAuthenticationInterface
     *
     * @var $userAuthenticationService
     */
    protected $userAuthenticationService;

    /**
     * String
     *
     * @var $defaultRedirectUri
     */
    protected $defaultRedirectUri;

    /**
     * Zend\Session\Container
     *
     * @var $session
     */
    protected $session;

    /**
     * LoginPageAction constructor.
     *
     * @param Router\RouterInterface             $router                    for
     *                                                                      routes
     * @param Template\TemplateRendererInterface $template                  for
     *                                                                      templates
     * @param UserAuthenticationInterface        $userAuthenticationService to
     *                                                                      authenticate
     *                                                                      username,
     *                                                                      password
     * @param string                             $defaultRedirectUri        to
     *                                                                      url
     *                                                                       redirect
     * @param Adapter                            $adapter                   for
     *                                                                      db
     *                                                                      connection
     * @param Session                            $session                   session
     *                                                                      variable
     */
    public function __construct(
        Router\RouterInterface $router,
        Template\TemplateRendererInterface $template,
        UserAuthenticationInterface $userAuthenticationService,
        $defaultRedirectUri, Adapter $adapter, $session
    ) {
        $this->router = $router;
        $this->template = $template;
        $this->userAuthenticationService = $userAuthenticationService;
        $this->defaultRedirectUri = $defaultRedirectUri;
        $this->adapter = $adapter;
        $this->session = $session;
    }

    /**
     * Authenticate and login user.
     *
     * @param Array $post contains posted elements of form
     *
     * @return Array $user1
     */
    protected function doLogin($post)
    {
        $user1 = [];
        if ($post['action'] == 'login') {
            $table = new \VuBib\Db\Table\User($this->adapter);
            $user = $table->checkUserAuthentication(
                $post['user_name'], $post['user_pwd']
            );
            $user1 = array_reduce($user, 'array_merge', []);
        }
        return $user1;
    }

    /**
     * Set access as per user level.
     *
     * @param Array $user1 contains user details
     *
     * @return empty
     */
    protected function setModuleAccess($user1)
    {
        $this->session->id = $user1['id'];
        if (isset($user1['level'])) {
            if ($user1['level'] == 1) {
                $this->session->role = 'role_a';
            } else {
                $this->session->role = 'role_su';
            }
        } else {
            $this->session->role = 'role_u';
        }
        $table = new \VuBib\Db\Table\Module_Access($this->adapter);
        $modules = $table->getModules($this->session->role);
        foreach ($modules as $row) {
            $mods[] = $row['module'];
        }
        $this->session->modules_access = $mods;
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
        if ($request->getMethod() == 'POST') {
            $post = [];
            $post = $request->getParsedBody();
            if (!empty($post['action'])) {
                $user1 = $this->doLogin($post);
            }
            if (isset($user1['id'])) {
                if (!(null === $user1['id'])) {
                    $this->setModuleAccess($user1);
                    return new RedirectResponse(
                        $this->getRedirectUri($request),
                        RFC7231::FOUND
                    );
                }
            }
            $flash = $request->getAttribute('flash');
            $flash->addMessage('error', 'Invalid username or password.');
            return new RedirectResponse(
                $this->getRedirectUri($request),
                RFC7231::FOUND
            );
        }
        if (array_key_exists('logout', $request->getQueryParams())) {
            if ($request->getQueryParams()['logout'] == 'y') {
                $toUrl = $this->getRedirectUri($request);
                $sessionManager = $this->session->getManager();
                $sessionManager->destroy();

                return new RedirectResponse(
                    $toUrl,
                    RFC7231::FOUND
                );
            }
        }

        return $this->renderLoginFormResponse($request);
    }

    /**
     * Render an HTML reponse, containing the login form.
     *
     * Provide the functionality required to let a user authenticate,
     * based on using an HTML form.
     *
     * @param ResquestInterface $request server-side request
     *
     * @return HtmlResponse
     */
    protected function renderLoginFormResponse($request)
    {
        $flash = $request->getAttribute('flash');
        $messages = $flash->getMessages();
        return new HtmlResponse(
            $this->template->render(
                self::PAGE_TEMPLATE, ['request' => $request,
                'adapter' => $this->adapter, 'flashMessages' => $messages]
            )
        );
    }

    /**
     * Get the URL to redirect the user to.
     *
     * The value returned here is where to send the user to after a
     * successful authentication has taken place. The intent is to
     * avoid the user being redirected to a generic route after login,
     * requiring them to have to specify where they want to navigate to.
     *
     * @param ServerRequestInterface $request server-side request.
     *
     * @return string
     */
    protected function getRedirectUri(ServerRequestInterface $request)
    {
        if (array_key_exists('logout', $request->getQueryParams())) {
            $reqParams = $request->getServerParams();
            //$baseUrl = $uri->getScheme() . '://' . $uri->getHost()
            //. '/' . $uri->getPath();
            $toUrl = 'http' . '://' . $reqParams['HTTP_HOST']
                . '/' . $reqParams['REDIRECT_URL'];
            //return $toUrl.'?redirect_to=/VuBib/public/';

            return $toUrl . '?redirect_to=' . $reqParams['REDIRECT_BASE'];
        }
        if (array_key_exists('redirect_to', $request->getQueryParams())) {
            return $request->getQueryParams()['redirect_to'];
        }
        return $this->defaultRedirectUri;
    }
}
