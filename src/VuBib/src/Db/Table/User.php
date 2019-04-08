<?php
/**
 * Table Definition for user.
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
 * Table Definition for user.
 *
 * @category VuBib
 * @package  Code
 * @author   Falvey Library <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 *
 * @link https://
 */
class User extends \Zend\Db\TableGateway\TableGateway
{
    /**
     * Zend\Session
     *
     * @var $session
     */
    protected $session;

    /**
     * Invokes session constructor.
     *
     * @param ContainerInterface $container for session
     *
     * @return empty
     */
    public function __invoke(ContainerInterface $container)
    {
        $this->session = $container->get(\Zend\Session\Container::class);
    }

    /**
     * User constructor.
     *
     * @param Adapter $adapter for db connection
     */
    public function __construct($adapter)
    {
        parent::__construct('user', $adapter);
    }

    /**
     * Insert user
     *
     * @param String  $newuser_name name of user
     * @param String  $new_username user name of user
     * @param String  $new_user_pwd password of user
     * @param Integer $access_level user role
     *
     * @return empty
     */
    public function insertRecords($newuser_name, $new_username,
        $new_user_pwd, $access_level
    ) {
        $this->insert(
            [
            'name' => $newuser_name,
            'username' => $new_username,
            'password' => $new_user_pwd,
            'level' => $access_level,
            ]
        );
    }

    /**
     * Find user
     *
     * @param Integer $id user id
     *
     * @return Array $row user details
     */
    public function findRecordById($id)
    {
        $rowset = $this->select(['id' => $id]);
        $row = $rowset->current();

        return $row;
    }

    /**
     * Delete user
     *
     * @param Integer $id user id
     *
     * @return empty
     */
    public function deleteRecord($id)
    {
        $this->delete(['id' => $id]);
    }

    /**
     * Update user
     *
     * @param Integer $id       user id
     * @param String  $name     name of user
     * @param String  $username user name of user
     * @param String  $pwd      password of user
     * @param Integer $level    user role
     *
     * @return empty
     */
    public function updateRecord($id, $name, $username, $pwd, $level)
    {
        if ($level == 'NULL') {
            $level = null;
        }

        if (null === $pwd) {
            $this->update(
                [
                'name' => $name,
                'username' => $username,
                'level' => $level,
                ],
                ['id' => $id]
            );
        } else {
            $this->update(
                [
                'name' => $name,
                'username' => $username,
                'password' => $pwd,
                'level' => $level,
                ],
                ['id' => $id]
            );
        }
    }

    /**
     * Authenticate user
     *
     * @param string $username username
     * @param string $pwd      password of user
     *
     * @return Array $row user details
     */
    public function checkUserAuthentication($username, $pwd)
    {
        $callback = function ($select) use ($username, $pwd) {
            $select->columns(['*']);
            $select->where->equalTo('username', $username);
            $select->where->equalTo('password', md5($pwd));
        };

        $row = $this->select($callback)->toArray();

        return $row;
    }

    /**
     * Change password
     *
     * @param Integer $id  user id
     * @param String  $pwd password of user
     *
     * @return empty
     */
    public function changePassword($id, $pwd)
    {
        $this->update(
            [
                'password' => md5($pwd),
            ],
            ['id' => $id]
        );
    }
}
