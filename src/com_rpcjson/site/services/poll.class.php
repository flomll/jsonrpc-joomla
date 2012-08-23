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

jimport( 'joomla.form.form' );

/** 
 * @brief This class implements the service 'user'.
 *
 * @description TODO
 */
class Poll {	
	/**
	 * \brief Get list of polls from $start until $limit.
	 * \details The $start and $limit parameters should be used to load more 
	 *          if you need to emulating pages.
	 * \param $username
	 * \param $password
	 * \param $start
	 * \param $limit
	 * \return A list of polls limited by the parameters $start and $limit.
	 */
	public function getPolls($username, $password, $start, $limit)
	{
	
	    // Set the parameter to build the query.
	    $where = "published = 1 "; // FIXME: Check the access level 
	    $order = "#__mfpolls.id DESC ";
	    	    
	    $db =& JFactory::getDBO();
   	    if( rpcjson_helper::is_joomla15() ) { 
	        $query = rpcjson_helper::build_db_query(
	        "SELECT ".
	            "   #__mfpolls.id, ".
				"	#__mfpolls.title, ".
				"	#__mfpolls.voters AS 'voters' ".
				"FROM #__mfpolls ",
				$start, $limit, $where, $order);
	    }
	    // FIXME: Add the query code for joomla > 1.5

	    // Take the query and load the data from the database. This function is secured 
	    // by password and username. It logs the user in on execution and log out on return.
	    return rpcjson_helper::build_payload ($username, $password, get_class($this), $query, TRUE);
	}
	
	/**
	 * \brief Get usernames which have submit for the selected poll.
	 * 
	 * \param $username Username to get access.
	 * \param $password Password to get access.
	 * \param $id Identification number to select the poll.
	 * \return Usernames which have submit the selected poll.
	 */
	public function getPollUsers($username, $password, $id)
	{
	    // Get database object
	    $db =& JFactory::getDBO();

	    // Set the parameter to build the query.
	    $where = "#__mfpolls_users.pid = ".$db->quote($id)." ";
	    $order = "";
	    $start = 0;
	    $limit = 100;
	    	    
   	    if( rpcjson_helper::is_joomla15() ) { 
	        $query = rpcjson_helper::build_db_query(
	        "SELECT ".
				"	#__users.name ".
				"FROM #__users ".
				"LEFT JOIN #__mfpolls_users ".
				"	ON #__users.id = #__mfpolls_users.uid ",
				$start, $limit, $where, $order);
	    }
	    // FIXME: Add the query code for joomla > 1.5

	    // Take the query and load the data from the database. This function is secured 
	    // by password and username. It logs the user in on execution and log out on return.
	    return rpcjson_helper::build_payload ($username, $password, get_class($this), $query, TRUE);
	}
	
	/**
	 * \brief Get list of data for selected poll.
	 * 
	 * \param $username Username to get access.
	 * \param $password Password to get access.
	 * \param $id Identification number to select the poll.
	 * \return A list of data for selected poll.
	 */
	public function getPollData($username, $password, $id)
	{
	    // Get database object
	    $db =& JFactory::getDBO();

	    // Set the parameter to build the query.
	    $where = " #__mfpoll_data.pollid = ".$db->quote($id)."  AND #__mfpoll_data.text != ''";
	    $order = " #__mfpoll_data.hits DESC ";
	    unset($start);
	    unset($limit);
	    	    
   	    if( rpcjson_helper::is_joomla15() ) { 
	        $query = rpcjson_helper::build_db_query(
	        "SELECT ".
	        	" #__mfpoll_data.id, ".
				" #__mfpoll_data.text, ".
				" #__mfpoll_data.hits ".
				"FROM #__mfpoll_data ",
				$start, $limit, $where, $order);
	    }
	    // FIXME: Add the query code for joomla > 1.5

	    // Take the query and load the data from the database. This function is secured 
	    // by password and username. It logs the user in on execution and log out on return.
	    return rpcjson_helper::build_payload ($username, $password, get_class($this), $query, TRUE);
	}
	
	public function vote($username, $password, $pollid, $optionid)
	{
		$response = new stdClass();
	    // Check the authority of the user. This should be done on every service function.
   	    if( rpcjson_helper::login_user($username, $password, $service) <= 0 ) {
 	        $response->failure = 'Invalid username or password!';
 	        return $response;
 	    }
 	    
 	    $pollid = (int)$pollid;
 	    $optionid = (int)$optionid;

		$isLoaded = JPluginHelper::importPlugin( 'mfpoll', 'mfpollsystem' );
		$dispatcher =& JDispatcher::getInstance();
		
		// The user is authenticated so we are ready to fire the onVote event to do the
		// operation.
		$res = $dispatcher->trigger( 'onVote', array($pollid, $optionid) );
		
		if( !in_array(false, $res, true) ) {
			$response->code = 400; // Return code 400 - something wrong.
		}else
			$response->code = 200; // Return code 200 - successfully proceeded.
			    
		rpcjson_helper::logout_user();
		return $response;
	}	
}
?>