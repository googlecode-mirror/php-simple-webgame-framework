<?php

/**
 * 控制器Controller基类
 * 
 * @package mixjoy
 * @author zhaohe
 * @copyright 2011
 * @version $Id$
 * @access public
 */
class Controller extends Base {
    
    /**
     * View操作类
     */
    protected $view ;
    
    /**
     * 当前默认的模板文件
     */
    protected $default_template_file ;
    
    public function __construct() {
    	if ( method_exists($this, 'init')) {
    		$this->init();
    	}
    }
    
    /**
     * 初始化模板操作类
     * 
     * @return void
     */
    public function initView() {
    	if( empty($this->view) ) {
	        $this->view = App::getInstance('View');
	        $this->default_template_file = strtolower(App::$controller_name).'/'.App::$action_name;
    	}
    }
    
    /**
     * 显示模板信息
     * 
     * @param string $template_file
     * @return void
     */
    public function display($template_file=''){
    	if( empty($this->view) ) {
    		$this->initView();
    	}
        if( empty($template_file) ) {
            $template_file = $this->default_template_file;
        }
        $this->view->show($template_file);
    }

    /**
     * 控制器跳转
     */
    public function redirect($controller,$action){
	   App::run($controller,$action,false); 
    }

    
    /**
     * 魔术回调方法
     * 如果只存在默认模板文件时则显示默认模板，否则抛出错误
     * 
     * @param string $name
     * @param array $params
     * @return void
     */
    public function __call($name, $params){
        if( empty($this->view) ) {
    		$this->initView();
    	}        
        if( $this->view->templateExists($this->default_template_file) ) {
            $this->view->show($this->default_template_file);
        }
        else {
            //exit('File Not Exists.');
            throw new AppException(App::$controller_name.'::'.$name.'() Not Exists.');
        }
    }


	public function _beforeAction($controller_name,$action_name) {
		return true ;
	}

	public function _afterAction($controller_name,$action_name) {
		return true ;
	}
}
