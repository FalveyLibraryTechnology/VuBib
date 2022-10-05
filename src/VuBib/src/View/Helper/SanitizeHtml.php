<?php
/**
 * Santitize: Escape HTML with exceptions
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

/**
 * Santitize: Escape HTML with exceptions
 *
 * @category VuBib
 * @package  Code
 * @author   Falvey Library <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 *
 * @link https://
 */
class SanitizeHtml extends \Laminas\View\Helper\AbstractHelper
{
    /**
     * Transforms to be performed in order
     *
     * @var array
     */
    private $_transforms = [
        '<' => '&lt;',
        '>' => '&gt;',
        '&lt;strong&gt;' => '<strong>',
        '&lt;/strong&gt;' => '</strong>',
        '&lt;em&gt;' => '<em>',
        '&lt;/em&gt;' => '</em>',
        '&lt;b&gt;' => '<b>',
        '&lt;/b&gt;' => '</b>',
        '&lt;i&gt;' => '<i>',
        '&lt;/i&gt;' => '</i>',
    ];

    /**
     * Start session
     *
     * @param string $str text to sanitize
     *
     * @return Object $this
     */
    public function __invoke($str)
    {
        // Skip if possible
        if (
            strlen($str) == 0 ||
            (
                strstr($str, '<') == false &&
                strstr($str, '>') == false
            )
        ) {
            return $str;
        }
        // Replace all in order
        return str_replace(
            array_keys($this->_transforms),
            array_values($this->_transforms),
            $str
        );
    }
}
