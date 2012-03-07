<?php
/**
 * This file implements the user service for the project Joomla JSON RPC.
 * 
 * @package Joomla.JSON RPC
 * @subpackage Components
 * @link https://github.com/flomll/jsonrpc-joomla
 * @license	GNU/GPL
 *
 * COPYRIGHT
 *
 * Copyright 2012 Stijn Van Campenhout <stijn.vancampenhout@gmail.com>
 * 
 * This file is part of JSON-RPC2PHP.
 *
 * Joomla RPC JSON is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Joomla RPC JSON is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Joomla RPC JSON; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

require_once (JPATH_COMPONENT.DS.'server'.DS.'helper.class.php');

class user {

    public function login ($auth) {
    }
    
    public function logout ($auth) {
    }

    //public function get_stories ($username = null, $password = null) {
    public function get_stories ($auth) {
	//public function get_stories() {
	    $result = new stdClass();
	    	    
 	    //if( rpcjson_helper::login_user($a[0], $a[1]) < 60 ) {
 	    //    $result->failure = 'Invalid username or password!';
 	    //    return $result;
 	    //}
 	    
        
	    // FIXME: your code.
	    $result->data = "coming soon...";
	    
	    // Logout the user
	    rpcjson_helper::logout_user();
	    
		return $result;
	}
}
?>