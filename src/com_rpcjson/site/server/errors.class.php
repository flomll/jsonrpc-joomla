<?php
/**
* Error Class -JSON RPC 2.0-
*
* This Singleton Class manages error codes for
* json - rpc.
*
* @author Diego Resendez <diego.resendez@zero-oneit.com>
* @version 1.0
* @package Server
* @subpackage Errors 
*/
define ( 'JSON_PARSER_ERROR',           -32700);
define ( 'INVALID_REQUEST',             -32600);
define ( 'METHOD_NOT_FOUND',            -32601);
define ( 'INVALID_PARAMS',              -32602);
define ( 'INTERNAL_ERROR',              -32603);
define ( 'CLASS_NOT_FOUND',             -32604);

class Errors {
    
    /**
    *
    * Error Messages
    * @var mixed
    */
    private $error = array(
        JSON_PARSER_ERROR 	=> 'JSON Parser Error',
        INVALID_REQUEST 	=> 'Invalid Request',
        METHOD_NOT_FOUND 	=> 'Method not found',
        INVALID_PARAMS 		=> 'Invalid Params',
        INTERNAL_ERROR 		=> 'Internal Error',
        CLASS_NOT_FOUND     => 'Class Not Found'
    );
    
    /**
     * Singleton Instance
     * @var Object
     */
    private static $instance; 

    /**
     *  Avoid Object Instances
     */
    private function __construct(){
        
    }
    
     /**
     * Obtain a singleton Instance
     * @return Object
     */
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            $class = __CLASS__;
            self::$instance = new $class;
        }
        return self::$instance;
    }
    
    /**
     * Obtain error message
     * @param Integer $error_code
     * @return Object
     */
    public function getError($error_code){
        $response = new stdClass();
        $response->jsonrpc = Server::JSON_RPC_VERSION;
        $response->error = new stdClass();
        $response->error->code = $error_code;
        $response->error->message = $this->error[$error_code];
        $response->id = null;
        
        return ($response);
    }
    
    /**
     *  Avoid Object Clone
     */
    public function __clone()
    {
        trigger_error('No se permite la clonación.', E_USER_ERROR);
    }
    
    /**
     *  Avoid Object Serialize
     */
    public function __wakeup()
    {
        trigger_error('No se permite deserializar.', E_USER_ERROR);
    }
}