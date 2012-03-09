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
        return 'NOT IMPLEMENTED';
    }
    
    public function logout ($auth) {
        return 'NOT IMPLEMENTED';
    }

    public function get_stories ($start, $limit, $where, $order_by, $order, $username = null, $password = null) {
        // Check inputs
//         if( $username == null || $password == null ) {
//             // Kann man irgendwie dem Server sagen, dass er einen error mit 'Invalid parameters' returnen soll?
//             //echo json_encode( Errors::getInstance()->getError(INVALID_PARAMS) );
//  	        $result->failure = 'Invalid username or password!';
//  	        return $result;
//         }
    
	    // Check the authority of the user. This should be done on every service function.
//    	    if( rpcjson_helper::login_user($username, $password) < 60 ) {
//  	        $result->failure = 'Invalid username or password!';
//  	        return $result;
//  	    }

	    $result = new stdClass();
	    $result->type = 'stories';
	    	    	    
	    // Encryption test
 	    //$source = "Dann wollen wir das einmal testen. Denn wenn wir hier alles encrypten, dann ist das sicher besser.";
	    //$cr = rpcjson_helper::encrypt ( $source );
	    //echo $res = rpcjson_helper::decrypt ( $cr );
	    
   	    if( rpcjson_helper::is_joomla15() ) { 
	        $query = rpcjson_helper::build_db_query( 
	        "SELECT #__content.id, ".
				"	#__content.title, ".
				"	LEFT(#__content.introtext,100) introtext, ".
				"	#__content.created, ".
				"	#__content.access, ".
				"	#__content.state, ".
				"	#__categories.title category_title, ".
				"	#__users.name created_by_name ".
				"FROM #__content ".
				"LEFT JOIN #__categories ".
				"	ON #__categories.id = #__content.catid ".
				"LEFT JOIN #__users ".
				"	ON #__users.id = #__content.created_by ",
				$start, $limit, $where, $order_by, $order);
	    }
	    // FIXME: Add the query code for joomla > 1.5
	    
	    // Take the query and load the data from the database. This function is secured 
	    // by password and username. It logs the user in on execution and log out on return.
	    $result->data = rpcjson_helper::build_payload ($username, $password, $query, 0);
	    
	    // Log the user out. This should be done on every service function.
//	    rpcjson_helper::logout_user();
	    
		return $result;
	}
	
	public function get_userinfo ($username = null, $password = null) {
	    return 'NOT IMPLEMENTED';
	}
}
?>