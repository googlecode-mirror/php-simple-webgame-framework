<?php
if( !defined('APP_PATH') ) {
    exit('Access Denied.');
}

define('FRAME_PATH',realpath(dirname(__FILE__)));

if( !defined('DATA_PATH') ){
    define('DATA_PATH', APP_PATH.'/data');
}
if( !defined('VIEW_PATH') ){
    define('VIEW_PATH', APP_PATH.'/view');
}


include FRAME_PATH.'/core/App.class.php';

