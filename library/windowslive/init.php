<?php
/**
 * Init.php
 *
 * Settings files that contains global constants used by the OAuthWRAPHandler.php
 *
 * PHP version 5
 *
 * @category  OAuthWRAP_Settings
 * @package   OAuthWRAP
 * @author    Microsoft <microsoft@microsoft.com>
 * @copyright 2010 Microsoft Corporation. All rights reserved.
 * @license   http://www.php.net/license/3_01.txt  PHP License 3.01
 * @version   SVN: 1001
 * @link      GetStarted.htm
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');

session_start();

// Application Specific Globals
define('WRAP_CLIENT_ID', 		'000000004C054A1C');
define('WRAP_CLIENT_SECRET', 	'2C2YcFsljtUGjfbwmzhoNY8U5Yk5TkNf');
define('WRAP_SCOPE', 			'WL_Contacts.View,WL_Profiles.View');
define('WRAP_CALLBACK', 		'http://ameling.dyndns-home.com/test.php');

// Live URLs required for making requests.
define('WRAP_CONSENT_URL', 		'https://consent.live.com/Connect.aspx');
define('WRAP_ACCESS_URL', 		'https://consent.live.com/AccessToken.aspx');
define('WRAP_REFRESH_URL', 		'https://consent.live.com/RefreshToken.aspx');

require_once 'lib/logic/OAuthHandler.php';
?>
