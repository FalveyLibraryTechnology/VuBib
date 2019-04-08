<?php
/**
 * Form Helpers Middleware
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

use Zend\Form\View\HelperConfig as FormHelperConfig;
use Zend\View\HelperPluginManager;

/**
 * Class Definition for Form Helpers Middleware.
 *
 * @category VuBib
 * @package  Code
 * @author   Falvey Library <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 *
 * @link https://
 */
class FormHelpersMiddleware
{
    /**
     * Zend view Helpers
     *
     * @var HelperPluginManager
     */
    private $_helpers;

    /**
     * Form Helpers Middleware constructor
     *
     * @param HelperPluginManager $helpers zend view helper
     *
     * @return empty
     */
    public function __construct(HelperPluginManager $helpers)
    {
        $this->_helpers = $helpers;
    }

    /**
     * Passes request and response
     *
     * @param ServerRequestInterface $request  server-side request.
     * @param ResponseInterface      $response response to client side.
     * @param callable               $next     CallBack Handler.
     *
     * @return callable $next
     */
    public function __invoke($request, $response, callable $next)
    {
        $config = new FormHelperConfig();
        $config->configureServiceManager($this->_helpers);

        return $next($request, $response);
    }
}
