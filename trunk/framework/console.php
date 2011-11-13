<?php
if( !defined('APP_PATH') ) {
    exit('Access Denied.');
}

define('FRAME_PATH',realpath(dirname(__FILE__)));

include FRAME_PATH.'/core/App.class.php';

App::setModel('text') ;
