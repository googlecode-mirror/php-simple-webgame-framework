<?php

/**
 * @author zhaohe
 * @copyright 2011
 */

class DbException extends Exception {
    
    public function getMsg(){
        $message = $this->getMessage()." : ".App::db()->getError();
		$message .= "\nERROR SQL:".App::db()->lastSql();
        return $message ;
    }

}

