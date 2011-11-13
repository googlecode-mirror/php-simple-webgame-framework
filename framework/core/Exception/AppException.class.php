<?php

/**
 * @author zhaohe
 * @copyright 2011
 */

class AppException extends Exception {
    
    public function getMsg(){
        return $this->getMessage();
    }

	public function getKey() {
		return strtolower(str_replace("Exception","",__CLASS__));
	}

}

