<?php
/**
 * Hello World default controller
 * 
 * @package    Joomla.Tutorials
 * @subpackage Components
 * @link http://docs.joomla.org/Developing_a_Model-View-Controller_Component_-_Part_4
 * @license		GNU/GPL
 */ 
defined('_JEXEC') or die();
 
class TablePoll extends JTable
{
	var $id = null;
	var $title = null;
	var $alias = null;
	var $voters = null;
	var $checked_out = null;
	var $checked_out_time = null;
	var $published = 0;
	var $access = null;
	var $lag = null;
 
	function __construct(&$db)
	{
		parent::__construct( '#__polls', 'id', $db );
	}
	
	function getData()
	{
	    $result = new stdClass();
	    $result->id = $this->id;
	    $result->title = $this->title;
	    $result->voters = $this->voters;
	    
	    return $result;
	}
}