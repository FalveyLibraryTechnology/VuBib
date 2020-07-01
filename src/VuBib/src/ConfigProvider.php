<?php
/**
 * Configuration functionality
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
namespace VuBib;

use VuBib\Action\LoginPageAction;
use VuBib\Action\LoginPageFactory;
use VuBib\Middleware\AuthenticationMiddleware;
use VuBib\Middleware\AuthenticationMiddlewareFactory;
use VuBib\Repository\UserAuthenticationFactory;
use VuBib\Repository\UserAuthenticationInterface;
use VuBib\Repository\UserTableAuthentication;
use VuBib\View\Helper;
use Zend\ServiceManager\Factory\InvokableFactory;

/**
 * Class Definition for ConfigProvider.
 *
 * @category VuBib
 * @package  Code
 * @author   Falvey Library <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 *
 * @link https://
 */
class ConfigProvider
{
    /**
     * Provide the configuration for the module.
     *
     * This class handles the setup of the configuration for this module. This
     * configuration will be merged into the wider application configuration if
     * it's enabled.
     *
     * @return array
     */
    public function __invoke()
    {
        return [
            'dependencies' => $this->getDependencyConfig(),
            'routes' => $this->getRouteConfig(),
            'vubib' => $this->getAppConfig(),
            'templates' => [
                'paths' => [
                    'error'  => [__DIR__ . '/../templates/error'],
                    'layout' => [__DIR__ . '/../templates/layout'],
                    'vubib'  => [__DIR__ . '/../templates/vubib'],
                ],
            ],
            'view_helpers' => [
                'aliases' => [
                    'isUser' => Helper\IsUser::class,
                    'sanitizeHtml' => Helper\SanitizeHtml::class,
                ],
                'factories' => [
                    Helper\IsUser::class => Helper\IsUserFactory::class,
                    Helper\SanitizeHtml::class => InvokableFactory::class,
                ],
            ],

        ];
    }

    /**
     * Default redirection to home page
     *
     * @return array
     */
    public function getAppConfig()
    {
        return [
            'authentication' => [
                'default_redirect_to' => '/',
            ],
            'view_helpers' => [
                'aliases' => [
                    'isUser' => Helper\IsUser::class,
                    'sanitizeHtml' => Helper\SanitizeHtml::class,
                ],
                'factories' => [
                    Helper\IsUser::class => Helper\IsUserFactory::class,
                    Helper\SanitizeHtml::class => InvokableFactory::class,
                ],
            ],
        ];
    }

    /**
     * Provides the namespace's route configuration.
     *
     * @return array
     */
    public function getRouteConfig()
    {
        return [
            [
                'name' => 'login',
                'path' => '/login',
                'middleware' => [
                    SlimFlashMiddleware::class,
                    LoginPageAction::class,
                ],
                'allowed_methods' => ['GET', 'POST'],
            ],
        ];
    }

    /**
     * Provides the namespace's dependency configuration.
     *
     * @return array
     */
    public function getDependencyConfig()
    {
        return [
            'delegators' => [
                \Zend\Expressive\Application::class => [
                    \Zend\Expressive\Container\ApplicationConfigInjectionDelegator::class,
                ],
            ],
            'factories' => [
                AuthenticationMiddleware::class =>
                      AuthenticationMiddlewareFactory::class,
                LoginPageAction::class => LoginPageFactory::class,
                UserTableAuthentication::class =>
                     UserAuthenticationFactory::class,
                Middleware\WhoopsErrorResponseGenerator::class =>
                    Container\WhoopsErrorResponseGeneratorFactory::class,
                SanitizeHtml::class => InvokableFactory::class,
                /*
                 * Register a class that will handle the user authentication.
                 * The one registered here provides only a
                       generic sample implementation
                 * and is not meant to be taken seriously.
                 *
                 * UserTableAuthentication::class =>
                        UserAuthenticationFactory::class,
                 * */
            ],
            'aliases' => [
                UserAuthenticationInterface::class => UserTableAuthentication::class,
                /*
                 * This is a sample setup whereby the specific
                         implementation is never
                 * referenced anywhere in the codebase, instead
                        using a generic alias.
                 *
                 * UserAuthenticationInterface::class =>
                        UserTableAuthentication::class
                 * */
            ],
        ];
    }
}
