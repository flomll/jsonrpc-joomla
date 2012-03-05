<?php
/**
 * This file implements the server which handle the requests and responses 
 * for the project Joomla JSON RPC.
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

require_once (JPATH_COMPONENT.DS.'server'.DS.'errors.class.php');
require_once (JPATH_COMPONENT.DS.'server'.DS.'responses.class.php');

/**
 * This class builds a json-RPC 2.0 Server
 * http://www.jsonrpc.org/spec.html
 *
 * original idea from jsonrpcphp class of Sergio Vaccaro <sergio@inservibile.org>, http://jsonrpcphp.org/
 * @author stijn <stijn.vancampenhout@gmail.com>
 */
class Server {
	/// Includes a array of objects. (A object is registerd service. A service name is the class name)
	public $classes = array();
	/// Save the request to submit.
	public $request;
	/// Is the service name
	public $extension;
	public $response;
	public $methods;
	
	public $_errors;
	
	public $_logger;
	
// 	private $errorMessages = array(
// 		'-32700' => 'Parse error',
// 		'-32600' => 'Invalid request',
// 		'-32601' => 'Method not found',
// 		'-32602' => 'Invalid parameters',
// 		'-32603' => 'internal error',
// 		'-32000' => 'Extension not found'
// 		);
// 	private $errorMessagesFull = array(
// 		'-32700' => 'Invalid JSON was received by the server. An error occurred on the server while parsing the JSON string.',
// 		'-32600' => 'The JSON sent is not a valid Request object.',
// 		'-32601' => 'The method does not exist / is not available.',
// 		'-32602' => 'Invalid method parameters.',
// 		'-32603' => 'Internal Server error.',
// 		'-32000' => 'The requested extension does not exist / is not available.'
// 		);
// 	
// 	private $errorCodes = array(
// 		'parseError' 		=> '-32700',
// 		'invalidRequest'	=> '-32600',
// 		'methodNotFound'	=> '-32601',
// 		'invalidParameters'	=> '-32602',
// 		'internalError'		=> '-32603',
// 		'extensionNotFound'	=> '-32000'
// 		);
	
	/**
	 */
	public function Server() {
	    $this->_errors = new Errors();
	}
	 
	/**
	 * Constructor to register the class.
	 */
	 //public function jsonRPCServer($obj){
	 //	$this->registerClass($obj);
	 //}
	 
	/**
	 * Register a class as an extension
	 * methods will be available as [class].[method]
	 *
	 * @param object $obj
	 * return boolean
	 */
	public function register($obj){
		$this->classes[get_class($obj)] = $obj;
		
		foreach ($this->classes as $ext => $class){
	 		$this->methods[$ext] = get_class_methods($class);
	 	}
		return true;
	}
	
	/**
	 * responses to 'rpc.' calls.
	 *
	 */
	 public function rpcCalls() {
	 	if ($this->request['method'] == "listm"){
	 		foreach ($this->classes as $ext => $class){
	 			$methods[$ext] = get_class_methods($class);
	 		}
	 		if (isset($this->request['params']['extension'])){
	 			if (array_key_exists($this->request['params']['extension'],$this->classes)){
	 				$this->ok(array($this->request['params']['extension'] => $this->methods[$this->request['params']['extension']]));
	 				$this->sendResponse();
	 			} else {
	 				$this->error($this->errorCodes['extensionNotFound'],"requested extension not found in extension list." );
	 				$this->sendResponse();
	 			}
	 		} else {
	 				$this->ok($this->methods);
	 				$this->sendResponse();
	 		}
	 	// FIXME: Either we add all her or you delete the else statement
	 	// FIXME: change else if statement
	 	// DELTE BEGIN
	 	//} else if($this->methods['myClass'][0] == 'ping' ) {
	 	//	$this->_logger = "OK";
	 	} else {
	 		print_r($this->methods);
	 		$this->error($this->errorCodes['methodNotFound']);
	 		$this->sendResponse();
	 	}
	 	// DELETE END
	 	return true;
	 }
	 
	/**
	 * This function validates the incoming json string. It checks: 
	 * - request method (Only POST is accepted!)
	 * - content type (only 'application/json' is accepted!)
	 * - checks if we can parse the json
	 * - checks if the extension exists
	 * - checks if the method exists in the given extension
	 *
	 * @return boolean TRUE if everythings right. Otherwise FALSE.
	 */
	private function validate() {
		try {
			// TODO: Change this to Joomla methods
			if ($_SERVER['REQUEST_METHOD'] != 'POST' || 
				empty($_SERVER['CONTENT_TYPE']) || 
				strpos($_SERVER['CONTENT_TYPE'], 'application/json') === false) {
				throw new Exception($this->errorCodes['invalidRequest']);
			}
			
			$this->request = json_decode(file_get_contents('php://input'), true);
			if (empty($this->request)){
				throw new Exception($this->errorCodes['parseError']);
			}
			
			$requestMethod = explode('.',$this->request['method']);
			$this->extension = $requestMethod[0];
			if (!isset($this->classes[$this->extension]) && $this->extension != "rpc"){
				throw new Exception($this->errorCodes['extensionNotFound']);
			}
			
			$this->request['method'] = $requestMethod[1];
			if (!method_exists($this->classes[$this->extension],$this->request['method']) && $this->extension != "rpc"){
				throw new Exception($this->errorCodes['methodNotFound']);
			};
	
		} catch (Exception $e) {
				$this->error($e->getMessage());
				$this->sendResponse();
				return false;
		}
		return true;
	}
	
	/**
	 * Builds the error response
	 *
	 * @todo make this in a execption class
	 * @param string $c the error code
	 * @param string $fmsg the full message of the error
	 */
	private function error($c,$fmsg=false){
		$this->response = array (
				'jsonrpc'	=> '2.0',
				'id' => (isset($this->request['id'])) ? $this->request['id'] : NULL,
				'result' => NULL,
				'error'	=> array(
					'code'	=> (int)$c,
					'message' => (isset($this->errorMessages[$c])) ? $this->errorMessages[$c] : 'internalError',
					
					'data'	=> array(
						'request' => (isset($this->request)) ? $this->request : NULL,
						'extension' => (isset($this->extension)) ? $this->extension : NULL,
						'fullMessage' => ($fmsg) ? $fmsg : $this->errorMessagesFull[$c]
						)
					)
				);
		return true;
	}
	
	/**
	 * 
	 */
	private function toUtf8(array $array) { 
		$convertedArray = array(); 
    	
    	foreach($array as $key => $value) { 
      		if(!mb_check_encoding($key, 'UTF-8')) $key = utf8_encode($key); 
      		if(is_array($value)) $value = $this->toUtf8($value);
      		
      		$convertedArray[$key] = $value; 
    	} 
    	
    	return $convertedArray; 
    }
    
    /**
     * 
     */
	private function ok($result){		
		$this->response = array (
			'jsonrpc'	=> '2.0',
			'result' => $result,
			'id' => $this->request['id']
		);
	}
	
	/**
	 * check if there is a response needed & sends the response
	 *
	 */
	private function sendResponse(){
		if (!empty($this->request['id'])) { // notifications don't want response
			header('content-type: application/json');
			die( json_encode($this->response) );
		}
	}
	
	/**
	 * main class method. starts all the magic
	 *
	 */
	public function handle() {
		
		$this->validate();

		try {
		    // FIXME: Delete this because the default service is already defined
			if ($this->extension == "rpc"){
				$this->rpcCalls();
			}
			$obj = $this->classes[$this->extension];
			
			// FIXME: start to modify
			//print_r array($obj, $this->request['method']) );
			// Execute the registerd method and save the return value at the $result.
			// http://php.net/manual/en/function.call-user-func-array.php
			//print_r ( $this->request['params'] );
			if ( ($result = @call_user_func_array( array($obj, $this->request['method']), $this->request['params']) ) !== false) {
				$this->ok( (is_array($result)) ? $result : Array($result));
			} else {
				throw new Exception('Method function returned false.');
			}
		} catch (Exception $e) {
				$c = ($e->getCode() != 0) ? $e->getCode : $this->errorCodes['internalError'];
				$this->error($c,$e->getMessage());
		}
		// FIXME: display logger only if the debug modus is enabled.
		//print_r($this->_logger);
		$this->sendResponse();
		return true;
	}
}
?>
