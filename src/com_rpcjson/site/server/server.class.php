<?php
/**
 * Server Class -JSON RPC 2.0-
 * 
 * This Class contains the core server implementation for
 * json - rpc response.
 * 
 * @author Diego Resendez <diego.resendez@zero-oneit.com>
 * @version 1.0
 * @package server
 */
 
require_once (JPATH_COMPONENT.DS.'server'.DS.'responses.class.php');
require_once (JPATH_COMPONENT.DS.'server'.DS.'errors.class.php');

class Server
{        
    const JSON_RPC_VERSION = "2.0";
    const DO_NOT_CONVERT_TO_ASSOC_ARRAY = false;

    private $request = null;

    public function __construct(){
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Content-type: application/json');
    }

    /**
     * 
     * Process requests
     */
    public function process(){

        $request_json = '';
        // GET Request
//         if (!empty($_GET)) {
//             if ( isset ($_GET['request'] ) ) {
//                 $request_json = $_GET['request'];
//                 $request_json = str_replace('\\', '', $request_json);    
//             }
//             else {
//                 $request = new stdClass();
//                 isset($_GET['jsonrpc'])? $request->jsonrpc = $_GET['jsonrpc']:null;
//                 isset($_GET['method'])? $request->method = $_GET['method']:null;
//                 isset($_GET['id'])?$request->id = $_GET['id']:null;
//                 isset($_GET['params'])? $request->params = $_GET['params']:null;
// 
//                 if ( ! $request_json = json_encode($request) ){
//                     $request_json = '';
//                 }
// 
//             }
//         } else {
//     	// POST Request
//     	
//     		//ob_start();
//         	$request_json = file_get_contents( 'php://input');
// 			//$request_json = str_replace('\\"', '"', $request_json);
// 	        //ob_clean();
// 	        	
// 	    }

			if ($_SERVER['REQUEST_METHOD'] != 'POST' || 
				empty($_SERVER['CONTENT_TYPE']) || 
				strpos($_SERVER['CONTENT_TYPE'], 'application/json') === false) {
				//throw new Exception($this->errorCodes['invalidRequest']);
				echo json_encode(Errors::getInstance()->getError(JSON_PARSER_ERROR));
                die;
			}
			
			$request_json = file_get_contents('php://input');
			
       if (!$this->request = $this->__parseJson($request_json) ){
           echo json_encode(Errors::getInstance()->getError(JSON_PARSER_ERROR));
           die;
       }
        
        if ( !$this->__checkRequest($this->request) ){
            echo json_encode(Errors::getInstance()->getError(INVALID_REQUEST));
            die;
        }
       
        $responses= new Response($this->request); 

        echo json_encode($responses->getResponse());
    }
    
    /**
     *
     * Check and return if json string is correct.
     * @param json $pData
     */
    private function __parseJson( $pData ){
        $data = json_decode( $pData, false );
        return $data;
    }

    /**
     * 
     * @param oData
     * @return boolean TRUE if request is right. Otherwise FALSE.
     */
    public function __checkRequest($oData){
        if ( !is_object( $oData ) || 
            !isset( $oData->jsonrpc ) ||
            $oData->jsonrpc !== self::JSON_RPC_VERSION ||   // Check for right version
            !isset($oData->method ) ||                      // The key 'method' is available
            !is_string( $oData->method ) ||                 // The key 'method' has right data type
            !$oData->method ||                              // 
            ( isset($oData->params ) && !is_array( $oData->params ) ) || 
            !isset( $oData->id )                            // The key 'id' is not set // FIXME: if no 'id' is set it is a NOTIFICATION 
            )
        {
    
            return false;
        }
    
    
        if ( is_null( $oData->params ) )
        {
            $oData->params = array();
        }
    
        return true;
    }
}

?>