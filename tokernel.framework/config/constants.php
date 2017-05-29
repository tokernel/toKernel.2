<?php 
/**
 * toKernel - Universal PHP Framework.
 * Framework predefined constants.
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
 * @category   configuration
 * @package    framework
 * @subpackage configuration
 * @author     toKernel development team <framework@tokernel.com>
 * @copyright  Copyright (c) 2017 toKernel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @version    2.1.0
 * @link       http://www.tokernel.com
 * @since      File available since Release 1.0.0
 */

/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

/* Required PHP Version for toKernel framework functionality */
define('TK_PHP_VERSION_REQUIRED', '5.6');

/* Project short name */
define('TK_SHORT_NAME', 'toKernel');

/* Project description */
define('TK_DESCRIPTION', 'Universal PHP Framework');

/* Project version */
define('TK_VERSION', '2.3.0');

/* Short name for DIRECTORY_SEPARATOR */
define('TK_DS', DIRECTORY_SEPARATOR);

/* Define run mode constants */
define('TK_HTTP_MODE', 'http');
define('TK_CLI_MODE', 'cli');

/* End of file */