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

/* Required PHP Version for toKernel */
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
define('TK_CLI_MODE', 'cli');
define('TK_HTTP_MODE', 'http');

/* Application main configuration file */
define('TK_APP_INI', 'application.ini');

/* Name of Application HTTP and CLI mode routing */
define('TK_ROUTES_INI', 'routes.ini');

/* Name of Databases configuration file */
define('TK_DB_CONFIG_INI', 'databases.ini');

/* Name of Email sending configuration file */
define('TK_EMAIL_CONFIG_INI', 'email.ini');

/* Name of Caching configuration file */
define('TK_CACHING_CONFIG_INI', 'caching.ini');

/* Name of File transfer configuration file */
define('TK_FILE_TRANSFER_INI', 'file_transfer.ini');

/* Name of HTTP status codes reference file */
define('TK_HTTP_STATUS_CODES_INI', 'status_codes.ini');

/* End of file */