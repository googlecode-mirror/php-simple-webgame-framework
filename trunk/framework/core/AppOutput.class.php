<?php
/**
 +-------------------------------------------------------------------
 * 框架输出对象类 存放一组输出信息
 * 
 * @package framework
 * @author zhaohe
 * @copyright 2011
 * @version $Id$
 * @access public
 +-------------------------------------------------------------------
 */
class AppOutput extends Base {
	/**
	 * @var string 控制器名
	 */
	public $controllerName = null;

	/**
	 * @var string 方法名
	 */
	public $actionName = null;

	/**
	 * @var array 传出数据参数
	 */
	public $data = array();

	/**
	 * @var string 回调函数标识
	 */
	public $callBack = '';

	/**
	 * @var integer 传出状态
	 */
	public $state = 1;

	/**
	 * 构造方法
	 *
	 * @return void
	 */
	public function __construct() {
		
	}

	/**
	 * 设置方法名
	 *
	 * @param string $actionName 方法名
	 * @return void
	 */
	public function setActionName ( $actionName ) {
		$this->actionName = $actionName;
	}


	/**
	 * 设置控制器名
	 *
	 * @param string $actionName 方法名
	 * @return void
	 */
	public function setControllerName ( $controllerName ) {
		$this->controllerName = $controllerName;
	}

	/**
	 * 设置全部数据值
	 *
	 * @param array $data
	 * @return void
	 */
	public function setData ( $data ) {
		$this->data = $data;
	}

	/**
	 * 设置回调函数标识
	 *
	 * @param string $callBack 回调函数标识
	 * @return void
	 */
	public function setCallBack( $callBack = '' ) {
		$this->callBack = $callBack;
	}

	/**
	 * 设置传出状态
	 *
	 * @param integer $state
	 * @return void
	 */
	public function setState( $state = 1 ) {
		$this->state = $state;
	}

	/**
	 * 获取数据值
	 *
	 * @param string $name 参数键名
	 * @return mixed 参数值
	 */
	public function __get( $name ) {
		return isset( $this->data[$name] ) ? $this->data[$name] : null;
	}

	/**
	 * 设置数据值
	 *
	 * @param string $name 参数键名
	 * @param mixed $value 参数值
	 * @return void
	 */
	public function set( $name, $value = null ) {
		if( is_object( $name ) ) {
			$name = get_object_vars( $name );
		}
		elseif( is_array( $name ) ) {
			$this->data = array_merge( ( array ) $this->data, $name );
		}
		else {
			$this->data[$name] = $value;
		}
	}

	public function __set( $name, $value = null ) {
		$this->set($name,$value);
	}

	public function __isset( $name )
	{
		return isset( $this->data[$name] );
	}
}