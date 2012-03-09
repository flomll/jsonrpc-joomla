<?php
/**
* Response Class -JSON RPC 2.0-
*
* This Class contains the response implementation for
* json - rpc.
*
* @author Diego Resendez <diego.resendez@zero-oneit.com>
* @version 1.0
* @package server
*/

class Response {

    /**
     * 
     * Check if request is a batch request.
     * @var boolean
     */
    private $isBatch          = false;
    
    /**
     * 
     * Check if request is a notification.
     * @var boolean
     */
    private $isNotification   = false;
    
    /**
     * 
     * Check if request is correct.
     * @var boolean
     */
    private $hasError         = false;
    
    /**
     * 
     * Request Object
     * @var object
     */
    private $oRequest         = null;
    
    /**
     * 
     * Response Object
     * @var object
     */
    private $oResponse        = null;
   
    
    public function __construct($request =  null ){
        $this->oRequest = $request;
        if ( is_null($this->oRequest ) ) {
            $this->oResponse = errors::getInstance()->getError(JSON_PARSER_ERROR);
            $this->hasError = true;
        }
        else {
            $this->isBatch = ( is_array($this->oRequest ) );
        }
    }
    
    /**
     * 
     * Execute a overload function for response
     * an API request
     * @param string $method
     * @param array $params
     */
    public function __call($method, $params){
        if ($method ==  'getResponse' ) {
            if ($this->isBatch){
			    return $this->batchResponse();
			}
			return $this->singleResponse();
        }
    }
    
    /**
     * 
     * Make a batch request
     * @return Ambigous <object, NULL>
     */
    public function batchResponse() {
        
        if ( empty($this->oRequest) ) {
            return errors::getInstance()->getError(INVALID_REQUEST);
        }
        $requests = $this->oRequest;
		
        foreach ($requests as $r)
        {
            $this->oRequest = $r;
			
            $this->formResponse();
			
            if (!$this->isNotification){
				 $response[] = $this->oResponse;
            }
           
        }
        return $response;       
    }
    
    /**
     * 
     * Make a single request
     * @return Ambigous <object, NULL>
     */
    public function singleResponse(){
       	$this->formResponse();
		
		if (!$this->isNotification) {
			return $this->oResponse;
        }
    }
    
    /**
     * 
     * Call a Response Method from a Class
     */
    private function execMethod() {
       
        $methodRaw = $this->oRequest->method;
        $methodRaw = explode('.', $methodRaw);
         
        if ( count( $methodRaw) == 1 ) {
            $class = DEFAULT_SERVICE;
            $method= $methodRaw[0];
        }else {
            list($class, $method) = $methodRaw;  
        }
		 
		if ( !file_exists( JPATH_COMPONENT.DS.'services'.DS."{$class}.class.php") ) {
			$this->oResponse = errors::getInstance()->getError(CLASS_NOT_FOUND);
            return;
		}
        require_once (JPATH_COMPONENT.DS.'services'.DS."{$class}.class.php");
		 
        $ServerClass          = new ReflectionClass($class);
        $service              = $ServerClass->newInstanceArgs();
        $methodObject         = $ServerClass->getMethod($method);
        $methodParameters     = $methodObject->getParameters();

        try{
            $this->oResponse = new stdClass();
            $this->oResponse->jsonrpc 	= Server::JSON_RPC_VERSION;

            $this->oResponse->result = $methodObject->invokeArgs($service, $this->oRequest->params);
        }catch (Exception $e){
            print_r($e);
            $this->oResponse = errors::getInstance()->getError(INVALID_PARAMS);
            return;
        }
        
 //       // Changed: flomll
 //       $this->oResponse->jsonrpc 	= Server::JSON_RPC_VERSION;
        $this->oResponse->id        = $this->oRequest->id;
        
        $this->isNotification = (is_null($this->oResponse->result));
    } 
    
    /**
     * 
     * generate a response object.
     * @return object
     */
    private function formResponse(){
        
        // FIXME: Is this method a duplicated -> look at rpcjson.php
        if ( ! Server::__checkRequest($this->oRequest) ){
            return $this->oResponse = errors::getInstance()->getError( INVALID_REQUEST );
        }
        	
        try {
            $this->execMethod();
           
        }
        catch (Exception $e) {
            return $this->oResponse = errors::getInstance()->getError(METHOD_NOT_FOUND);
        }
    }   
}
?>