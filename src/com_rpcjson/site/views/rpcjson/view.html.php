<?php
jimport( 'joomla.application.component.view');

/**
 * HTML View class for 
 *
 * @package		Joomla.Tutorials
 
 * @subpackage	Components
 */
class RpcJsonViewRpcJson extends JView
{
	function display($tpl = null)
	{
	$greeting = $this->get( 'Greeting' );
		
		$this->assignRef( 'greeting',	$greeting );

		parent::display($tpl);
	}
}
?>
