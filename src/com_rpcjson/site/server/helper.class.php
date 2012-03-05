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

class rpcjson_helper {

    function get_joomlaversion () {
		$version = new JVersion;
		return $version->getShortVersion();
	}

	function is_joomla15 () {
		return (substr(rpcjson_helper::get_joomla_version(), 0, 3) == '1.5');
	}
	
    function login_user ($username, $password) {
		$userId = -1;		$credentials = array('username' => $username, 'password' => $password);
		$options = array('silent' => true);

		$app = JFactory::getApplication();

		// Save our guest session so we can destroy it.
		$session = JFactory::getSession();
		$guestSessionId = $session->getId();

		// Login will create a new session
		$successfulLogin = $app->login($credentials, $options);

		// Destroy guest session row.  Login forks the session and we will end up with a bunch of guest rows if we don't.
        jimport('joomla.database.table');
        $storage = & JTable::getInstance('session');
        $storage->delete($guestSessionId);

		/*
		// We used to do it this way.  Switching for better non-1.5 support.
		// We need the user to be stored in the session.  This initially started when non-1.5 didn't store the created_user_id for categories.

		// Get the global JAuthentication object
		jimport('joomla.user.authentication');
		$auth = & JAuthentication::getInstance();
		$response = $auth->authenticate($credentials, $options);

		if($response->status === JAUTHENTICATE_STATUS_SUCCESS)
		*/
		if($successfulLogin)
		{
			$userId = JUserHelper::getUserId($username);
			$user = JUser::getInstance($userId);

			//$minusergroup = JoomlaAdminMobileHelper::getComponentParameter('minusergroup', 25);
			$minusergroup = 2;
			
			// FIXME:
		}



		return $userId;
	}

	function logout_user () {
		$app = JFactory::getApplication();
		$app->logout();
	}
}
?>