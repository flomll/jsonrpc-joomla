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
 * Copyright 2012 Florian Mueller <florian.mueller@mublasafu.net>
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

// Includes some useful functions. It groupes the security and reuse functions.
require_once (JPATH_COMPONENT.DS.'server'.DS.'helper.class.php');

// Define all Joomla default objects to build a response
define (TAG_TYPE_JOOMLAERROR,   'jerror');
define (TAG_TYPE_ARTICLE,       'article');
define (TAG_TYPE_USERINFO,      'userinfo');
define (TAG_TYPE_POLL,          'poll');
define (TAG_TYPE_NEWSFEED,      'newsfeed');
define (TAG_TYPE_CONTACT,       'contact');

// Define the custom objects to build a response
define (TAG_TYPE_PIXC,          'pixc');
define (TAG_TYPE_EVENT,         'event');

/** 
 * @brief This class implements the service 'user'.
 *
 * @description TODO
 */
class user {
    
    public function login ($username, $password) {
        return 'NOT IMPLEMENTED';
    }
    
    public function logout () {
        return 'NOT IMPLEMENTED';
    }

    /**
     * @brief Reads some stories from the database selected by the given parameter.
     * 
     * @param integer $start
     * @param integer $limit
     * @param string $where
     * @param string $order
     *
     * @param string $username
     * @param string $password
     *
     * @return 
     */
    public function get_articles_intro ($start, $limit, $where, $order, $username, $password) {
        // Check inputs
//         if( $username == null || $password == null ) {
//             // Kann man irgendwie dem Server sagen, dass er einen error mit 'Invalid parameters' returnen soll?
//             //echo json_encode( Errors::getInstance()->getError(INVALID_PARAMS) );
//  	        $result->failure = 'Invalid username or password!';
//  	        return $result;
//         }
    
	    $result = new stdClass();
	    $result->type = TAG_TYPE_ARTICLE;
	    	    
	    // TODO: Add a virtual column 'rtype' to specify the 
	    //SELECT id,title, IF(sectionid = 1, 'enabled', 'disabled') AS rtype FROM `jos_content` WHERE 1
   	    if( rpcjson_helper::is_joomla15() ) { 
	        $query = rpcjson_helper::build_db_query(
	        "SELECT #__content.id, ".
				"	#__content.title, ".
				"	LEFT(#__content.introtext,50) introtext, ".
				"	#__content.created, ".
				"	#__content.access, ".
				"	#__content.state, ".
				"	#__categories.title category_title, ".
				"	#__users.name created_by_name ".
				"   ".
				"FROM #__content ".
				"LEFT JOIN #__categories ".
				"	ON #__categories.id = #__content.catid ".
				"LEFT JOIN #__users ".
				"	ON #__users.id = #__content.created_by ",
				$start, $limit, $where, $order);
	    }
	    // FIXME: Add the query code for joomla > 1.5

	    // Take the query and load the data from the database. This function is secured 
	    // by password and username. It logs the user in on execution and log out on return.
	    $result->data = rpcjson_helper::build_payload ($username, $password, $query, 0);
	    	    
		return $result;
	}
	
	/**
	 */
	public function get_article ($username, $password, $id) {
	    return 'NOT IMPLEMENTED';
	}

	public function get_user ($username, $password) {
	    return 'NOT IMPLEMENTED';
	}
	
	public function update_user ($username, $password) {
	    return 'NOT IMPLEMENTED';
	}
	
	public function upload_image ($username, $password, $filename, $filecontents) {
	    return 'NOT IMPLEMENTED';
	}
	
	public function get_users ($username, $password, $start, $limit, $where, $order) {
	    return 'NOT IMPLEMENTED';
	}
	
	/**
	 * @brief Register a new user.
	 *
	 * @param string $name Name to display.
	 * @param string $username Username to register. It must be unique.
	 * @param string $email E-Mail address of the user.
	 * @param string $password The password string.
	 * @param string $password_retype The retyped password string.
	 * @param boolean $license TRUE if the user accept the license. Otherwise FALSE.
	 * @param string $code Is a generated token to verify the registration comes from a user.
	 *
	 * @return 
	 */
	public function register_user ($name, $username, $email, $password, $password_retype, $license, $code) {
	    return 'NOT IMPLEMENTED';
	}
	
	public function get_event ($username, $password) {
	    return 'NOT IMPLEMENTED';
	}
	
	public function get_events ($username, $password) {
	    return 'NOT IMPLEMENTED';
	}	
}
?>