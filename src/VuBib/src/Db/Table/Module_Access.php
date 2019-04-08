<?php
/**
 * Table Definition for module_access.
 *
 * PHP version 5
 *
 * Copyright (C) Villanova University 2017.
 * Copyright (C) University of Freiburg 2014.
 * Copyright (C) The National Library of Finland 2015.
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
 *
 * @link https://
 */
namespace VuBib\Db\Table;

use Interop\Container\ContainerInterface;

/**
 * Table Definition for module_access.
 *
 * @category VuBib
 * @package  Code
 * @author   Falvey Library <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 *
 * @link https://
 */
class Module_Access extends \Zend\Db\TableGateway\TableGateway
{
    /**
     * Zend\Session\Container
     *
     * @var $session
     */
    protected $session;

    /*public function _invoke(ContainerInterface $container) {

        $this->session = $container->get(\Zend\Session\Container::class);
    }*/

    /**
     * Module_Access constructor.
     *
     * @param Adapter $adapter for db connection
     */
    public function __construct($adapter)
    {
        parent::__construct('module_access', $adapter);

        //ContainerInterface $container;
        $this->session = new \Zend\Session\Container('Bibliography');
    }

    /**
     * Get modules for a role.
     *
     * @param string $role role of user
     *
     * @return Array $rows authorized modules
     */
    public function getModules($role)
    {
        $v_role = $role;
        $callback = function ($select) use ($v_role) {
            $select->columns(['module']);
            $select->where->equalTo($v_role, 1);
        };
        $row = $this->select($callback)->toArray();
        return $row;
    }

    /**
     * Set modules to access for a role.
     *
     * @param string $module module name
     * @param string $role   role of user
     *
     * @return empty
     */
    public function setModuleAccess($module, $role)
    {
        if ($role == 'Super User') {
            $role = 'role_su';
        } elseif ($role == 'User') {
            $role = 'role_u';
        }
        $this->update(
            [
                $role => 1,
            ],
            ['module' => $module]
        );
    }

    /**
     * UnSet access to module(s) for a role.
     *
     * @param string $module module name
     * @param string $role   role of user
     *
     * @return empty
     */
    public function unsetModuleAccess($module, $role)
    {
        if ($role == 'Super User') {
            $role = 'role_su';
        } elseif ($role == 'User') {
            $role = 'role_u';
        }
        $this->update(
            [
                $role => 0,
            ],
            ['module' => $module]
        );
    }

    /**
     * Get all the modules
     *
     * @return Array $mod_rows array of modules
     */
    public function getAllModules()
    {
        $callback = function ($select) {
            $select->columns(['module']);
        };
        $rows = $this->select($callback)->toArray();
        $mod_rows = array_column($rows, 'module');
        return $mod_rows;
    }
}
