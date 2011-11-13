<?php

/**
 * @author zhaohe
 * @copyright 2011
 */


class AmfRequest {
    
    public function response($input){
    	
        if( !empty($input->args) ) {
        	
        	if( is_object($input->args) ) {
        		$array = get_object_vars($input->args) ;
        	}
        	else {
        		$array = (array)$input->args ;
        	}
        	
            App::in( $array );
        }
        
        App::run($input->controllerName, $input->actionName, false);
        
        $data = App::output();
        
        return $data ;
    }
    
}

