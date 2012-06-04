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
 
class TableContent extends JTable
{
	var $id = null;
	var $title = null;
	var $alias = null;
	var $title_alias = null;
	var $introtext = null;
	var $fulltext = null;
	var $state = null;
	var $sectionid = null;
	var $mask = null;
	var $catid = null;
	var $created = null;
	var $created_by = null;
	var $created_by_alias = null;
	var $modified = null;
	var $modified_by = null;
	var $checked_out = null;
	var $checked_out_time = null;
	var $publish_up = null;
	var $publish_down  = null;
	var $images = null;
	var $urls = null;
	var $attribs  = null;
	var $version  = null;
	var $parentid = null;
	var $ordering = null;
	var $metakey  = null;
	var $metadesc = null;
	var $access = null;
	var $hits = null;
	var $metadata  = null;
 
	function __construct(&$db)
	{
		parent::__construct( '#__content', 'id', $db );
	}
	
	function getData()
	{
	    $result = new stdClass();
	    $result->id = $this->id;
	    $result->title = $this->title;
	    $result->fulltext = $this->fulltext;
	    $result->createdby = $this->createdby;
	    
	    return $result;
	}
}