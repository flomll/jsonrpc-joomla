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

// Define all Joomla default objects to build a response
define (TAG_TYPE_JOOMLAERROR,   'jerror');
define (TAG_TYPE_ARTICLE,       'article');
define (TAG_TYPE_USER,          'user');
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
class User {
    
    private function login ($username, $password) {
        return 'NOT IMPLEMENTED';
    }
    
    private function logout () {
        return 'NOT IMPLEMENTED';
    }

    /**
     * @brief Reads some storie intros from the database selected by the given parameter.
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
    private function get_articles_intro ($username, $password, $start, $limit, $where, $order) {
        //FIXME: Check inputs
    
//	    $result = new stdClass();
//	    $tagtype = TAG_TYPE_ARTICLE;
	    	    
        $db =& JFactory::getDBO();
   	    if( rpcjson_helper::is_joomla15() ) { 
	        $query = rpcjson_helper::build_db_query(
	        "SELECT IFNULL(NULL, ".$db->quote($tagtype).") AS 'respt', ".
	            "   #__content.id, ".
				"	#__content.title, ".
				"	LEFT(#__content.introtext,50) introtext, ".
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
				$start, $limit, $where, $order);
	    }
	    // FIXME: Add the query code for joomla > 1.5

	    // Take the query and load the data from the database. This function is secured 
	    // by password and username. It logs the user in on execution and log out on return.
	    $contents = rpcjson_helper::build_payload ($username, $password, get_class($this), $query, TRUE);

        // Clear the html tags from the introtext
	    foreach($contents->data as $content)
	    {
	        $content->introtext = strip_tags($content->introtext);
	    }
	    
	    return $contents;
	}
	
	private function filter($data, $limit = null)
	{
        // Initialise variables.
        $return = array();
 
        // The data must be an object or array.
        if (!is_object($data) && !is_array($data)) {
                return false;
        }
 
        // Get some system objects.
        $config = JFactory::getConfig();
        $user   = JFactory::getUser();
 
        // Convert objects to arrays.
        if (is_object($data)) {
                // Handle a JRegistry/JParameter object.
                if ($data instanceof JRegistry) {
                        $data = $data->toArray();
                }
                // Handle a JObject.
                elseif ($data instanceof JObject) {
                        $data = $data->getProperties();
                }
                // Handle other types of objects.
                else {
                        $data = (array)$data;
                }
        }
 
        // Static input filters for specific settings.
        static $noHtmlFilter;
        static $safeHtmlFilter;
 
        // Get the safe HTML filter if not set.
        if (is_null($safeHtmlFilter)) {
                $safeHtmlFilter = &JFilterInput::getInstance(null, null, 1, 1);
        }
 
        // Get the no HTML filter if not set.
        if (is_null($noHtmlFilter)) {
                $noHtmlFilter = &JFilterInput::getInstance(/* $tags, $attr, $tag_method, $attr_method, $xss_auto */);
        }
 
        foreach($this->_fieldsets as $group => $fieldset)
        {
                if(isset($fieldset['parent']))
                {
                        $this->_groups[$fieldset['parent']] = array_merge($this->_groups[$fieldset['parent']], $this->_groups[$group]);
                }
        }
 
        // Iterate through the groups.
        foreach ($this->_groups as $group => $fields) {
                $array = $this->_fieldsets[$group]['array'];
                if ($array === true) {
                        if(isset($this->_fieldsets[$group]['parent'])) {
                                $groupControl = $this->_fieldsets[$group]['parent'];
                        } else {
                                $groupControl = $group;
                        }
                } else {
                        $groupControl = $array;
                }
                // Filter if no group is specified or if the group matches the current group.
                if ($limit === null || ($limit !== null && $group === $limit)) {
                        // If the group name matches the name of a group in the data and the value is not scalar, recurse.
                        if (isset($data[$groupControl]) && !is_scalar($data[$groupControl]) && !is_resource($data[$groupControl]))
                        {
                                if (isset($return[$groupControl])) {
                                        $return[$groupControl] = array_merge($return[$groupControl], $this->filter($data[$groupControl], $group));
                                } else {
                                        $return[$groupControl] = $this->filter($data[$groupControl], $group);
                                }
                        } else {
                                // Filter the fields.
                                foreach ($fields as $name => $field)
                                {
                                        // Get the field information.
                                        $filter = (string)$field->attributes()->filter;
 
                                        // Check for a value to filter.
                                        if (isset($data[$name])) {
                                                // Handle the different filter options.
                                                switch (strtoupper($filter)) {
                                                        case 'RULES':
                                                                $return[$name] = array();
                                                                foreach ((array) $data[$name] as $action => $ids) {
                                                                        // Build the rules array.
                                                                        $return[$name][$action] = array();
                                                                        foreach ($ids as $id => $p) {
                                                                                if ($p !== '') {
                                                                                        $return[$name][$action][$id] = ($p == '1' || $p == 'true') ? true : false;
                                                                                }
                                                                        }
                                                                }
                                                                break;
 
                                                        case 'UNSET':
                                                                // Do nothing.
                                                                break;
 
                                                        case 'RAW':
                                                                // No Filter.
                                                                $return[$name] = $data[$name];
                                                                break;
 
                                                        case 'SAFEHTML':
                                                                // Filter safe HTML.
                                                                $return[$name] = $safeHtmlFilter->clean($data[$name], 'string');
                                                                break;
 
                                                        case 'SERVER_UTC':
                                                                // Convert a date to UTC based on the server timezone offset.
                                                                if (intval($data[$name])) {
                                                                        $offset = $config->getValue('config.offset');
 
                                                                        $date   = JFactory::getDate($data[$name], $offset);
                                                                        $return[$name] = $date->toMySQL();
                                                                } else {
                                                                        $db = &JFactory::getDbo();
                                                                        $return[$name]= $db->getNullDate();
                                                                }
                                                                break;
 
                                                        case 'USER_UTC':
                                                                // Convert a date to UTC based on the user timezone offset.
                                                                if (intval($data[$name])) {
                                                                        $offset = $user->getParam('timezone', $config->getValue('config.offset'));
 
                                                                        $date = JFactory::getDate($data[$name], $offset);
                                                                        $return[$name] = $date->toMySQL();
                                                                }
                                                                break;
 
                                                        default:
                                                                // Check for a callback filter.
                                                                if (strpos($filter, '::') !== false && is_callable(explode('::', $filter))) {
                                                                        // Filter using the callback method.
                                                                        $return[$name] = call_user_func(explode('::', $filter), $data[$name]);
                                                                } else if (function_exists($filter)) {
                                                                        // Filter using the callback function.
                                                                        $return[$name] = call_user_func($filter, $data[$name]);
                                                                } else {
                                                                        // Filter using JFilterInput. All HTML code is filtered by default.
                                                                        $return[$name] = $noHtmlFilter->clean($data[$name], $filter);
                                                                }
                                                                break;
                                                }
                                        }
                                }
                        }
                }
        }
 
        return $return;
}
	
	/**
	 * \brief Reads some articles from the main database.
	 * 
	 */
	private function get_article ($username, $password, $id) {
	    //FIXME: Check inputs
    
//	    $result = new stdClass();
//	    $tagtype = TAG_TYPE_ARTICLE;
	    
	    // Set the parameter to build the query.
	    $start = 0;
	    $limit = 1;
	    $where = "#__content.id = $id";
	    $order = "";
	    	    
	    $db =& JFactory::getDBO();
   	    if( rpcjson_helper::is_joomla15() ) { 
	        $query = rpcjson_helper::build_db_query(
	        "SELECT IFNULL(NULL, ".$db->quote($tagtype).") AS 'respt', ".
	            "   #__content.id, ".
				"	#__content.title, ".
				"	#__content.fulltext AS 'fulltext', ".
				"	#__content.created, ".
				"	#__content.access, ".
				"	#__content.state, ".
				"	#__categories.title AS 'category_title', ".
				"	#__users.name created_by_name ".
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
	    $result = rpcjson_helper::build_payload ($username, $password, get_class($this), $query, FALSE);
	    	    
		return $result;
	}


	/**
	 * \brief Update or create a new content.
	 * \param $unmae Username to get access.
	 * \param $passwd Password to get 
	 * \param $table Database table to get input the data
	 * \param $input
	 */
	private function update_content($uname, $passwd, $table, $sectionid, $catid, $input)
	{
	    // Login with given user information
	    $user = rpcjson_helper::login_user($uname, $passwd, get_class($this) );
	    if($user < 0) {
	        $result->failure = 'Invalid username or password';
	        return $result;
	    }
	    
        // Static input filters for specific settings.
        static $noHtmlFilter;
        static $safeHtmlFilter;

        // Get the safe HTML filter if not set.
        if (is_null($safeHtmlFilter)) {
                $safeHtmlFilter = &JFilterInput::getInstance(null, null, 1, 1);
        }
 
        // Get the no HTML filter if not set.
        if (is_null($noHtmlFilter)) {
                $noHtmlFilter = &JFilterInput::getInstance(/* $tags, $attr, $tag_method, $attr_method, $xss_auto */);
        }
	    
	    $date   = JFactory::getDate();

        // FIXME: Filter all input data and verify the data!!
	    $data = new stdClass();
	    $data->id = NULL;
	    $data->title = $noHtmlFilter->clean($input->title, $filter);
	    $data->fulltext = $input->fulltext;
	    $data->sectionid = (int)$sectionid;
	    $data->catid = (int)$catid;
	    $data->state = (int)$input->state;
	    $data->created = $date->toMySQL();
	    $data->created_by = (int)$user;
	    
	    // Input data to the database	              
	    $db =& JFactory::getDBO();
	    $result = $db->insertObject('#__content', $data, id);
	    
	    // Logout the current user.
	    rpcjson_helper::logout_user();
        
        return $result;
	}
	
	
// -----------------------------------------------------------------------------
// User 	
// -----------------------------------------------------------------------------	

	public function get_user ($username, $password) 
	{
	    if(!is_string($username) || !is_string($password) )
	    {
	        $result->failure = 'Invalid parameter';
	        return $result;
	    }
    
	    $result = new stdClass();
	    $tagtype = TAG_TYPE_USER;
	    
	    // Set the parameter to build the query.
	    $start = 0;
	    $limit = 1;
	    $where = "";
	    $order = "";
	    
	    $id = rpcjson_helper::login_user($username, $password, get_class($this) );
	    if($id < 0) {
	        $result->failure = 'Invalid username or password';
	        return $result;
	    }
	    
	    $db = &JFactory::getDBO();
   	    if( rpcjson_helper::is_joomla15() ) { 
			$query = "SELECT #__users.id, ".
			    "   IFNULL(NULL, ".$db->quote($tagtype).") AS 'respt', ".
				"	#__users.name, ".
				"	#__users.username, ".
				"	#__users.block, ".
				"	#__users.email, ".
				"	#__users.sendEmail, ".
				"	#__users.lastvisitDate, ".
				"	#__users.registerDate ".
				" FROM #__users ".
				" WHERE #__users.id = ".$db->quote($id);
	    } else {
			$query = "SELECT #__users.id, ".
				"	#__users.name, ".
				"	#__users.username, ".
				"	#__users.block, ".
				"	#__users.usertype, ".
				"	#__users.gid, ".
				"	#__users.email, ".
				"	#__users.sendEmail, ".
				"	#__users.lastvisitDate, ".
				"	#__users.registerDate ".
				"FROM #__users ".
				"WHERE #__users.id = ".$db->quote($id);
		}

	    // Take the query and load the data from the database. This function is secured 
	    // by password and username. It logs the user in on execution and log out on return.
	    $result->data = rpcjson_helper::build_payload ($username, $password, get_class($this), $query, 0);
	    	    
		return $result;
	}
	
	public function update_user ($username, $password, $input)
	{
	    return 'NOT IMPLEMENTED';
	}
		
	public function get_users ($username, $password, $start, $limit, $where, $order) 
	{
	    if(!is_int($start) || $start < 0 || !is_int($limit) || $limit < 0 ||
	        !is_string($username) || !is_string($password) || !is_string($where) || !is_string($order) )
	    {
	        $result->failure = 'Invalid parameter';
	        return $result;
	    }
    
	    $result = new stdClass();
	    $tagtype = TAG_TYPE_USER;
	    	    
	    $id = rpcjson_helper::login_user($username, $password, get_class($this) );
	    if($id < 0) {
	        $result->failure = 'Invalid username or password';
	        return $result;
	    }
	    
	    $db = &JFactory::getDBO();
   	    if( rpcjson_helper::is_joomla15() ) { 
			$query = "SELECT #__users.id, ".
			    "   IFNULL(NULL, ".$db->quote($tagtype).") AS 'respt', ".
				"	#__users.name, ".
				"	#__users.username, ".
				"	#__users.block, ".
				"	#__users.email, ".
				"	#__users.sendEmail, ".
				"	#__users.lastvisitDate, ".
				"	#__users.registerDate ".
				" FROM #__users ".
				" LIMIT ".$start.",".$limit;
	    } else {
			$query = "SELECT #__users.id, ".
				"	#__users.name, ".
				"	#__users.username, ".
				"	#__users.block, ".
				"	#__users.usertype, ".
				"	#__users.gid, ".
				"	#__users.email, ".
				"	#__users.sendEmail, ".
				"	#__users.lastvisitDate, ".
				"	#__users.registerDate ".
				" FROM #__users ".
				" LIMIT ".$start.",".$limit;
		}

	    // Take the query and load the data from the database. This function is secured 
	    // by password and username. It logs the user in on execution and log out on return.
	    $result->data = rpcjson_helper::build_payload ($username, $password, get_class($this), $query, 0);
	    	    
		return $result;
	}
	
	public function get_contatc($username, $password, $id)
	{
	    return 'NOT IMPLEMENTED';
	}
	
	public function get_contacts($username, $password, $id)
	{
	    return 'NOT IMPLEMENTED';
	}

// -----------------------------------------------------------------------------
// News
// -----------------------------------------------------------------------------	
	public function getNewsIntro($username, $password, $start, $number) 
	{
	    $where = "#__content.catid = 34 ";
	    $order = "#__content.created DESC";
	    return $this->get_articles_intro($username, $password, $start, $number, $where, $order);
	}
	
	public function getNews($username, $password, $id)
	{
	    return $this->get_article ($username, $password, $id);
	}
	
	public function update_blog($username, $password, $input) 
	{
	    $sectionid = 5;
	    $catid = 34;
	    
	    return $this->update_content($username, $password, "#__content", $sectionid, $catid, $input);
	}
	
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
	    $order = "#__polls.id DESC ";
	    	    
	    $db =& JFactory::getDBO();
   	    if( rpcjson_helper::is_joomla15() ) { 
	        $query = rpcjson_helper::build_db_query(
	        "SELECT ".
	            "   #__polls.id, ".
				"	#__polls.title, ".
				"	#__polls.voters AS 'voters' ".
				"FROM #__polls ",
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
	    $where = "#__polls_users.pid = ".$db->quote($id)." ";
	    $order = "";
	    $start = 0;
	    $limit = 100;
	    	    
   	    if( rpcjson_helper::is_joomla15() ) { 
	        $query = rpcjson_helper::build_db_query(
	        "SELECT ".
				"	#__users.name ".
				"FROM #__users ".
				"LEFT JOIN #__polls_users ".
				"	ON #__users.id = #__polls_users.uid ",
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
	    $where = " #__poll_data.pollid = ".$db->quote($id)."  AND #__poll_data.text != ''";
	    $order = " #__poll_data.hits DESC ";
	    unset($start);
	    unset($limit);
	    	    
   	    if( rpcjson_helper::is_joomla15() ) { 
	        $query = rpcjson_helper::build_db_query(
	        "SELECT ".
				" #__poll_data.text, ".
				" #__poll_data.hits ".
				"FROM #__poll_data ",
				$start, $limit, $where, $order);
	    }
	    // FIXME: Add the query code for joomla > 1.5

	    // Take the query and load the data from the database. This function is secured 
	    // by password and username. It logs the user in on execution and log out on return.
	    return rpcjson_helper::build_payload ($username, $password, get_class($this), $query, TRUE);
	}
	
	
	public function getPollDataFull($username, $password, $id)
	{
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
	 * @return a response type 'user' if registration success. Otherwise returns an 'jerror' response type.
	 */
	private function register_user ($name, $username, $email, $password, $password_retype, $license, $code) {
	    return 'NOT IMPLEMENTED';
	}
	
	private function upload_image ($username, $password, $filename, $filecontents) {
	    return 'NOT IMPLEMENTED';
	}

	private function get_event ($username, $password) {
	    return 'NOT IMPLEMENTED';
	}
	
	private function get_events ($username, $password) {
	    return 'NOT IMPLEMENTED';
	}	
}
?>