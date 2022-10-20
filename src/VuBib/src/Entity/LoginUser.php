<?php
/**
 * User login
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
namespace VuBib\Entity;

use Laminas\Form\Annotation;

/**
 * Class Definition for user login.
 *
 * @category VuBib
 * @package  Code
 * @author   Falvey Library <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 *
 * @link https://
 */
class LoginUser implements AuthUserInterface
{
    /**
     * Username
     *
     * @Annotation\Type("Laminas\Form\Element\Text")
     * @Annotation\Required({"required":"true"})
     * @Annotation\Validator({"name":"NotEmpty"})
     * @Annotation\Filter({"name":"StripTags"})
     * @Annotation\Options({"label":"Username:"})
     *
     * @var int
     */
    private $_username;

    /**
     * Password
     *
     * @Annotation\Type("Laminas\Form\Element\Text")
     * @Annotation\Required({"required":"false"})
     * @Annotation\Validator({"name":"NotEmpty"})
     * @Annotation\Filter({"name":"StripTags"})
     * @Annotation\Filter({"name":"StringTrim"})
     * @Annotation\Options({"label":"Password:"})
     * @Annotation\Validator({"name":"StringLength", "options":{"min":"1"}})
     *
     * @var string
     */
    private $_password;

    /**
     * Zend form submit
     *
     * @Annotation\Type("Laminas\Form\Element\Submit")
     * @Annotation\Attributes({"value":"Submit"})
     *
     * @var string
     */
    private $_submit;

    /**
     * Set username and password
     *
     * @param array $data user data
     *
     * @return empty
     */
    public function populate($data)
    {
        $this->_username = $data['username'];
        $this->_password = $data['password'];
    }

    /**
     * Get array
     *
     * @return array
     */
    public function getArrayCopy()
    {
        return get_object_vars($this);
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->_username;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->_password;
    }
}
