<?php
/**
 * Entry point of the Joomla JSON RPC component.
 * 
 * @package Joomla.JSON RPC
 * @subpackage Components
 * @link https://github.com/flomll/jsonrpc-joomla
 * @license	GNU/GPL
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

define ( 'DEFAULT_SERVICE',           'rpc');

require_once (JPATH_COMPONENT.DS.'server'.DS.'server.class.php');

$server = new Server();
//$server->process() or die('no request');
$server->process();
exit;
//// Require the base controller
//require_once (JPATH_COMPONENT.DS.'controller.php');

//// Require specific controller if requested
//if($controller = JRequest::getVar('controller')) {
//	require_once (JPATH_COMPONENT.DS.'controllers'.DS.$controller.'.php');
//}

//// Create the controller
//$classname	= 'RpcJsonUsrController'.$controller;
//$controller = new $classname();

// Perform the Request task
//$controller->execute( JRequest::getVar('task'));

// Redirect if set by the controller
//$controller->redirect();
?>
