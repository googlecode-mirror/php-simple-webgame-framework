<?php
/**
 * 模板基础处理类
 * 
 * @package fw
 * @author zhaohe
 * @copyright 2011
 * @version $Id$
 * @access public
 */
class View extends Base {
    
    protected $var_map = array();
	protected $template_ext = '.php';
    
    /**
     * 实例化方法
     * 
     * @return void
     */
    public function __construct(){
        $this->init();
    }
    
    /**
     * 初始化模板对象
     * 对同一个模板对象重复使用可通过该方法清空/重置所有模板变量
     * 
     * @return void
     */
    public function init(){
        $this->var_map = array();
        $this->var_map['static_url'] = App::conf('static_url');
		$config_ext = App::conf('template_ext');
		if(!empty($config_ext)) {
			$this->template_ext = $config_ext ;
		}
    }
    
    /**
     * 模板赋值操作
     * 
     * @param mixed $var 数组或者字符串索引
     * @param mixed $value 赋值
     * @return void
     */
    public function assign($var,$value=false) {
        if( is_array($var) ) {
            $this->var_map = array_merge($this->var_map,$var);
        }
        else {
            $this->var_map[$var] = $value ;
        }
    }
    
    /**
     * 显示模板
     * 
     * @param mixed $template_file
     * @return void
     */
    public function show($template_file){
        if( empty($template_file) ) {
            throw new AppException('template file empty.');
        }
        elseif( !$this->templateExists($template_file) ) {
            throw new AppException('template file '.$template_file.' not exists.');
        }
        extract($this->var_map);
        include( $this->getTrueFile($template_file) );
    }
    
    /**
     * 判断模板文件是否存在
     * 
     * @param mixed $template_file
     * @return
     */
    public function templateExists($template_file){
        return is_file( $this->getTrueFile($template_file) );
    }
    
    /**
     * 解析并获得真实的模板文件
     * 
     * @param mixed $template_file
     * @return 
     */
	public function getTrueFile($template_file) {
		return VIEW_PATH.'/'.$template_file.$this->template_ext ;
	}

}


