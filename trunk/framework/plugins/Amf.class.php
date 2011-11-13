<?php

class Amf {
	
	/**
	 * AmfPHP的执行文件路径
	 * 
	 */
    private $amfphp_path;

    /**
     * AmfPHP入口函数 
     * 
     */
    private $gateway;

    public function __construct() {
        $this->amfphp_path = realpath(dirname(__FILE__) . '/../libs/amfphp');
        include $this->amfphp_path . '/ClassLoader.php';
        $config = new Amfphp_Core_Config();
        $config->serviceFolderPaths = array(dirname(__FILE__).'/AmfService/');

        $content_type = null;
        if (App::in("content_type")) {
            $content_type = App::in("content_type");
        } 
        elseif (isset($_SERVER["CONTENT_TYPE"])) {
            $content_type = $_SERVER["CONTENT_TYPE"];
        }
		/*
		else {
			$content_type = 'application/x-amf';
		}*/
        $rawInputData = isset($GLOBALS['HTTP_RAW_POST_DATA'])?$GLOBALS['HTTP_RAW_POST_DATA']:file_get_contents('php://input');
		
        $this->gateway = new Amfphp_Core_Gateway(App::in(),App::in(), $rawInputData, $content_type, $config);
        
    }

    /**
     * 执行AmfPHP处理逻辑并输出返回的数据
     * 
     */
    public function run() {
        $data = $this->gateway->service();
        foreach($this->gateway->getResponseHeaders() as $header){
            header($header);
        }
        echo $data;
    }
    
}
