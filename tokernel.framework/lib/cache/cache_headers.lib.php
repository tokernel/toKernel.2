<?php
/**
 * toKernel - Universal PHP Framework.
 * Library for manipulate Cached content output headers.
 *
 * This file is part of toKernel.
 *
 * toKernel is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * toKernel is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with toKernel. If not, see <http://www.gnu.org/licenses/>.
 *
 * @category   library
 * @package    framework
 * @subpackage library
 * @author     toKernel development team <framework@tokernel.com>
 * @copyright  Copyright (c) 2017 toKernel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @version    1.0.0
 * @link       http://www.tokernel.com
 * @since      File available since Release 2.0.0
 */

/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

/**
 * cache_headers_lib class library.
 *
 * @author David A. <tokernel@gmail.com>
 */
class cache_headers_lib  {

    public function output($last_modified_timestamp, $max_age) {
        if($this->is_modified_since($last_modified_timestamp)) {
            $this->set_last_modified_header($last_modified_timestamp, $max_age);
        } else {
            $this->set_not_modified_header($max_age);
        }
    }

    /**
     * Return true if is modified
     *
     * @access private
     * @param int $last_modified_timestamp
     * @return bool
     */
    private function is_modified_since($last_modified_timestamp) {

        $all_headers = getallheaders();

        if (array_key_exists('If-Modified-Since', $all_headers)) {

            $gmt_since_date = $all_headers['If-Modified-Since'];
            $since_timestamp = strtotime($gmt_since_date);

            // If the browser can get from cache
            if ($since_timestamp != false and $last_modified_timestamp <= $since_timestamp) {
                return false;
            }
        }

        return true;

    } // End func is_modified_since

    /**
     * Output 304 Not modified headers and exit.
     *
     * @access private
     * @param int $max_age
     * @return void
     */
    private function set_not_modified_header($max_age) {

        $expires_date = gmdate("D, j M Y H:i:s", $max_age)." GMT";

        header("HTTP/1.1 304 Not Modified", true);
        header("Cache-Control: public, max-age=" . $max_age, true);
        header("Expires: " . $expires_date);
        exit();

    } // End func set_not_modified_header

    /**
     * Set last modified header
     *
     * @access private
     * @param int $last_modified_timestamp
     * @param int $max_age
     * @return void
     */
    private function set_last_modified_header($last_modified_timestamp, $max_age) {

        $last_modified_date = gmdate("D, j M Y H:i:s", $last_modified_timestamp)." GMT";
        $expires_date = gmdate("D, j M Y H:i:s", $max_age)." GMT";

        // Set headers
        header("HTTP/1.1 200 OK", true);
        header("Cache-Control: public, max-age=" . $max_age, true);
        header("Last-Modified: " . $last_modified_date, true);
        header("Expires: " . $expires_date);

    } // End func set_last_modified_header

} /* End of class cache_headers_lib */