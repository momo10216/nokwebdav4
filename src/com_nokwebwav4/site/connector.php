<?php
/**
* @version	$Id$
* @package	Joomla
* @subpackage	NoKWebDAV
* @copyright	Copyright (c) 2017 Norbert Kümin. All rights reserved.
* @license	http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE
* @author	Norbert Kuemin
* @authorEmail	momo_102@bluemail.ch
*/

define('_JEXEC', 1);

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

function getInfosFromPath() {
	global $_SERVER;
	if (!isset($_SERVER['PATH_INFO'])) {
		WebDAVHelper::debugAddArray($_SERVER, '_SERVER');
		return array('','');
	}
	Log::add('getInfosFromPath Path: '.$_SERVER['PATH_INFO'], Log::DEBUG);
	$locElements = explode('/',$_SERVER['PATH_INFO']);
	if (count($locElements) < 2) { return array('',''); }
	$containerName = $locElements[1];
	unset($locElements[1]);
	$location = implode('/',$locElements);
	if (empty($location)) { $location = '/'; }
	Log::add('getInfosFromPath containerName: '.$containerName, Log::DEBUG);
	Log::add('getInfosFromPath location: '.$location, Log::DEBUG);
	return array($containerName, $location);
}

function getTargetInfosFromUrl($url,$containerName) {
	global $_SERVER;

	$location = str_replace($_SERVER['SCRIPT_NAME'],'',$url);
	$location = '/'.explode('/'.$containerName.'/',$location)[1];
	return array($containerName, $location);
}

function getAccess($id) {
	$commands = array('read','create','change','delete');
	$access = array();
	$user = JFactory::getUser();
	$assetName = 'com_nokwebdav.container.'.$id;
	foreach($commands as $command) {
		$access[$command] = $user->authorise('content.'.$command, $assetName);
	}
	return $access;
}

function handleAuthentication() {
	global $_SERVER;

	if (!isset($_SERVER['PHP_AUTH_USER']) || empty($_SERVER['PHP_AUTH_USER'])) {
		if (isset($_SERVER['HTTP_AUTHORIZATION']) && !empty($_SERVER['HTTP_AUTHORIZATION'])) {
			list($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) = explode(':' , base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6)));
		} else {
			if (isset($_GET['Authorization']) && preg_match('/Basic\s+(.*)$/i', $_GET['Authorization'], $matches)) {
				list($name, $password) = explode(':', base64_decode($matches[1]));
				$_SERVER['PHP_AUTH_USER'] = strip_tags($name);
				$_SERVER['PHP_AUTH_PW'] = strip_tags($password);
			}
		}
	}
	//Log::add('SERVER: '.json_encode($_SERVER), Log::DEBUG);
	if (isset($_SERVER['PHP_AUTH_USER']) && !empty($_SERVER['PHP_AUTH_USER'])) {
		$user = JFactory::getUser();
		if ($user->username != $_SERVER['PHP_AUTH_USER']) {
			$user = '';
			$password = '';
			if (isset($_SERVER['PHP_AUTH_USER'])) { $user = $_SERVER['PHP_AUTH_USER']; }
			if (isset($_SERVER['PHP_AUTH_PW'])) { $password = $_SERVER['PHP_AUTH_PW']; }
			$app = JFactory::getApplication();
			if (!empty($user)) {
				$credentials = array('username' => $user,'password' => $password);
				$options = array();
				if (!$app->login($credentials, $options)) {
					Log::add('Login failed for user "'.$user.'"!', Log::ERROR);
					return false;
				} else {
					Log::add('Login success for user "'.$user.'"!', Log::DEBUG);
				}
			}
		} else {
			Log::add('User "'.$user->username.'" already logged in.', Log::DEBUG);
		}
	} else {
		Log::add('No username provided!', Log::ERROR);
		return false;
	}
	return true;
}

function getUriLoaction() {
	global $_SERVER;
	if (isset($_SERVER["HTTPS"]) && !empty($_SERVER["HTTPS"])) {
		$uri = 'https';
	} else {
		$uri = 'http';
	}
	$uri .= '://'.$_SERVER["SERVER_NAME"];
	if ($_SERVER["SERVER_PORT"] != 80 ) { $uri .= ':'.$_SERVER["SERVER_PORT"]; }
	$uri .= $_SERVER["REQUEST_URI"];
	return $uri;
}

register_shutdown_function(function(){
    $err = error_get_last();
    if(is_array($err) && isset($err['type']) && ($err['type'] == E_ERROR || $err['type'] == E_PARSE)) {
        error_log("Fatal error: ".var_export($err, true), 1);
    }
});

error_reporting(E_ERROR | E_PARSE);
$component = 'com_nokwebdav';

// Init from /includes/app.php
if (file_exists(dirname(__DIR__) . '../../includes/defines.php')) {
	include_once dirname(__DIR__) . '../../includes/defines.php';
}
if (!defined('_JDEFINES'))
{
	define('JPATH_BASE', dirname(__DIR__) . '../..');
	require_once JPATH_BASE . '/includes/defines.php';
}
require_once JPATH_BASE . '/includes/framework.php';
$container = \Joomla\CMS\Factory::getContainer();
$container->alias('session.web', 'session.web.site')
	->alias('session', 'session.web.site')
	->alias('JSession', 'session.web.site')
	->alias(\Joomla\CMS\Session\Session::class, 'session.web.site')
	->alias(\Joomla\Session\Session::class, 'session.web.site')
	->alias(\Joomla\Session\SessionInterface::class, 'session.web.site');
$app = $container->get(\Joomla\CMS\Application\SiteApplication::class);
\Joomla\CMS\Factory::$application = $app;

// Logging
use Joomla\CMS\Log\Log;
Log::addLogger(
	array(
		'text_file' => 'nokwebdav.log',
		'text_file_path' => '../../tmp/',
		'text_entry_format' => '{DATETIME} {PRIORITY} {MESSAGE}'
	),
//	Log::ERROR | Log::DEBUG
//	Log::ALL
	Log::ERROR
);
ini_set("log_errors", 1);
ini_set("error_log", JPATH_BASE.'/tmp/nokwebdav.log');

// Auth
handleAuthentication();

// Init controller
$controller = JControllerLegacy::getInstance('NoKWebDAV');
$container = $controller->getModel('container');

JLoader::register('WebDAVHelper', JPATH_COMPONENT_ADMINISTRATOR.'/helpers/webdav.php', true);
$uriLocation = getUriLoaction();
list ($containerName, $location) = getInfosFromPath();
$item = $container->getItemByName($containerName);
if ($item === false || !$item->published) {
	Log::add('Container "'.$containerName.'" not found.', Log::ERROR);
	WebDAVHelper::sendHttpStatusAndHeaders(WebDAVHelper::$HTTP_STATUS_ERROR_NOT_FOUND);
} else {
	Log::add('Container "'.$containerName.'" found.', Log::DEBUG);
	$webdavHelper = '';
	switch($item->type) {
		case 'files':
			Log::add('Container contains files.', Log::DEBUG);
			$baseDir = $item->filepath;
			Log::add('File path: '.$baseDir, Log::DEBUG);
			if ((strlen($baseDir) < 1) || (substr($baseDir,0,1) != '/')) {
				// relative path
				$baseDir = WebDAVHelper::joinDirAndFile(JPATH_BASE,$item->filepath);
			}
			Log::add('Root dir: '.$baseDir, Log::DEBUG);
			$fileLocation = WebDAVHelper::joinDirAndFile($baseDir, $location);
			$access = getAccess($item->id);
			$targetFileLocation = '';
			$targetAccess = array();
			$quota = round($item->quotaValue*pow(1024, $item->quotaExp),0);
			Log::add('quota: '.$quota, Log::DEBUG);
			if (isset($_SERVER["HTTP_DESTINATION"]) && !empty($_SERVER["HTTP_DESTINATION"])) {
				list ($targetContainerName, $targetLocation) = getTargetInfosFromUrl($_SERVER['HTTP_DESTINATION'], $containerName);
				Log::add($_SERVER["HTTP_DESTINATION"].' => ('.$targetContainerName.', '.$targetLocation.')', Log::DEBUG);
				if ($targetContainerName == $containerName) {
					$targetFileLocation = WebDAVHelper::joinDirAndFile($baseDir, $targetLocation);
					$targetAccess = $access;
				} else {
					$targetItem = $container->getItemByName($containerName);
					$targetBaseDir = $targetItem->filepath;
					if ((strlen($targetBaseDir) < 1) || (substr($targetBaseDir,0,1) != '/')) {
						// relative path
						$targetBaseDir = WebDAVHelper::joinDirAndFile(JPATH_BASE,$targetItem->filepath);
					}
					$targetFileLocation = WebDAVHelper::joinDirAndFile($targetBaseDir, $targetLocation);
					$targetAccess = getAccess($item->id);
				}
			}
			$webdavHelper = WebDAVHelper::getFilesInstance($access, $baseDir, $fileLocation, $targetAccess, $targetFileLocation, $uriLocation, $quota);
			break;
		default:
			break;
	}
	$webdavHelper->run();
}

// Exit
flush();
$app->close();
?>
