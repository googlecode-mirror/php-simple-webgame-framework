<?php
/**
 +-------------------------------------------------------------------
 * 框架传入数据管理类
 * 
 * @package framework
 * @author zhaohe
 * @copyright 2011
 * @version $Id$
 * @access public
 +-------------------------------------------------------------------
 */
class AppInput extends Base {
	/**
	 * @var string 控制器名
	 */
	public $controllerName;

	/**
	 * @var string 方法名
	 */
	public $actionName;

	/**
	 * @var array 传入数据参数
	 */
	public $args = array();
	
	/**
	 * 初始化传入参数
	 * 
	 * @param string $args 传入变量
	 * @return void 
	 */
	public function __construct ( $args = null ) {
		if( is_object($args) ) {
			$args = get_object_vars( $args );
		}
		if( is_array($args) ) {
			$this->args = $args;
		}
	}

	
	/**
	 * 获取所有参数
	 * 
	 * @return array 参数数组 
	 */
	public function getArgs() {
		return $this->args;
	}
	
	/**
	 * 获取参数值
	 * 
	 * @param string $name 参数名
	 * @return mixed 参数值
	 */
	public function __get( $name )
	{
		return isset( $this->args[$name] ) ? $this->args[$name] : false;	
	}
	
	/**
	 * 设置参数值
	 * 
	 * @param string $name 参数名
	 * @param string $value 参数值 
	 * @return void 
	 */
	public function set( $name, $value = null )
	{
		if( is_object($this->args) ) {
			$this->args = get_object_vars( $this->args );
		}

		if( is_object( $name ) ) {
			$name = get_object_vars( $name );	
		}

		if( is_array( $name ) ) {
			$this->args = empty($this->args)?$name:array_merge( $this->args, $name );

		} 
		else {
			$this->args[$name] = $value;
		}	
	}
	
	public function __set( $name, $value = null ) {
		$this->set($name,$value) ;
	}
	
	public function __isset( $name )
	{
		return !empty($this->args)&&is_array($this->args)&&isset( $this->args[$name] );
	}	
	
	/**
	 * 获取整数型参数值
	 * 
	 * @param string $name 参数名 
	 * @return integer 整数型参数值
	 */
	public function getIntval( $name ) {
		return intval( $this->get( $name ) );	
	}

	/**
	 * 获取去掉空白的参数值
	 * 
	 * @param string $name 参数名 
	 * @return string 去掉空白的参数值 
	 */	
	public function getTrim( $name )
	{
		return trim( $this->get( $name ) );	
	}
}
