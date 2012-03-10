<?php
/**
 * This file implements the default service "rpc." for the project Joomla JSON RPC.
 * 
 * @package Joomla.JSON RPC
 * @subpackage Components
 * @link https://github.com/flomll/jsonrpc-joomla
 * @license	GNU/GPL
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class rpc {
    
    /**
     * 
     */
	public function version () {
	    $jversion = new JVersion;
		
		$object = new stdClass();
	    
	    $object->joomla = $jversion->getShortVersion();
	    $object->modul = 0;//Server::MODULVERSION;
	    
		return $object;
	}
	
	public function copyright () {
	    $object = new stdClass();
	    $object->author = 'Florian Müller BSc.';
	    $object->license = 'GNU';
	    $object->email = 'florian.mueller@mublasafu.net';
	    $object->copyright = 'Copyright (c) by www.mublasafu.net';
	    
	    return $object;
	}
	
	/**
	 */
	public function listmethods () {
	    return "NOT IMPLEMENTED!";
	}
}
?>