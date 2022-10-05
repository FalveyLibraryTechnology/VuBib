<?php
/**
 * IsUser
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
namespace VuBib\View\Helper;

use Interop\Container\ContainerInterface;

/**
 * Class Definition for IsUser
 *
 * @category VuBib
 * @package  Code
 * @author   Falvey Library <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 *
 * @link https://
 */
class IsUser extends \Laminas\View\Helper\AbstractHelper
{
    /**
     * Laminas\Session\Container
     *
     * @var $session
     */
    private $_session;

    /**
     * Laminas\Db\Adapter\Adapter
     *
     * @var $adapter
     */
    private $_adapter;

    /**
     * ManageAgentAction constructor.
     *
     * @param Laminas\Db\Adapter\Adapter $adapter for db connection
     * @param Laminas\Session\Container  $session for zend session
     */
    public function __construct(\Laminas\Db\Adapter\Adapter $adapter, $session)
    {
        $this->_adapter = $adapter;
        $this->_session = $session;
    }

    /**
     * Start session
     *
     * @return Object $this
     */
    public function __invoke()
    {
        //test_cnt(ContainerInterface $container);
        //$table = new \VuBib\Db\Table\User($this->_adapter);
        //$user = $table->isAdmin();
        //var_dump($this->_session->modules_access);
        //return 'hello, ' . $this->_session->id . 'has access to';
        //. implode($this->_session->modules_access)
        return $this;
        //return $this->session->modules_access;
    }

    /**
     * Check if user has access to a module
     *
     * @return bool $this->_session->modules_access
     */
    public function hasPermission()
    {
        return $this->_session->modules_access;
    }

    /**
     * Check if user is logged in
     *
     * @return bool
     */
    public function isLogged()
    {
        if ($this->_session->id != null) {
            return true;
        }

        return false;
    }

    /**
     * Get user id
     *
     * @return null|Integer
     */
    public function getUser()
    {
        if ($this->_session->id != null) {
            return $this->_session->id;
        }

        return null;
    }

    /**
     * Check instructions for the page
     *
     * @param string $str page name
     *
     * @return string $ins
     */
    public function isInsSet($str)
    {
        //fetch instructions
        $table = new \VuBib\Db\Table\Page_Instructions($this->_adapter);
        $ins = $table->findRecordByPageName($str);

        return $ins;
    }

    /**
     * Check user access level
     *
     * @return string $usr
     */
    public function getUserType()
    {
        // $usr_typ = '';

        $table = new \VuBib\Db\Table\User($this->_adapter);
        $usr = $table->findRecordById($this->_session->id);

        if (isset($usr['level'])) {
            if ($usr['level'] == 1) {
                $usr['level'] = 'Administrator';
            } else {
                $usr['level'] = 'Super User';
            }
        } else {
            $usr['level'] = 'User';
        }

        return $usr;
    }

    /**
     * Get pending review works count
     *
     * @return Integer $cnt
     */
    public function getRvwCount()
    {
        $table = new \VuBib\Db\Table\Work($this->_adapter);
        $cnt = $table->getPendingReviewWorksCount();

        return $cnt;
    }
}
