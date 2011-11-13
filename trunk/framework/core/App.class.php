<?php
/**
 +----------------------------------------------------------------
 * 初始化相关参数
 * 
 +----------------------------------------------------------------
 */

//注册自动载入方法
spl_autoload_register( 'App::autoload' );
//注册错误处理方法
set_error_handler('Debug::error');
//注册异常处理方法
set_exception_handler('Debug::exception');
//设置时区
date_default_timezone_set('PRC');
//某些参数无变量时的判定值
define('KEY_NOT_SET_VALUE','KEY_NOT_SET_VALUE');
//屏蔽其他页面入口
define('IN_SYSTEM',true);


if( !defined('DATA_PATH') ){
    define('DATA_PATH', APP_PATH.'/data');
}
if( !defined('VIEW_PATH') ){
    define('VIEW_PATH', APP_PATH.'/view');
}

/**
 +-------------------------------------------------------------------
 * 框架入口操作类 存放基础数据和所有实例化的对象
 * 这是一个纯静态类，不需要实例化操作
 * 
 * @package framework
 * @author zhaohe
 * @copyright 2011
 * @version $Id$
 * @access public
 +-------------------------------------------------------------------
 */
class App {
    
    /**
     * 存储实例化的所有对象
     */
    private static $_instance;

     /**
     * 存储已经包含的类文件
     */
    private static $_import_files;

    /**
     * 当前应用的数据模式 
     * 当前支持 html/text/amf
     */
    private static $model = 'html'; 

    /**
     * 存储当前所有的输入变量
     */
    private static $input ;

    /**
     * 存储当前所有的输出变量
     */
    private static $output ;
    
    
    /**
     * 存储当前所有的缓存信息
     */
    private static $cache = array();

	private static $db ;
    
    private static $db_default = false ;
    
    private static $is_inited = false ;
    
    
    public static $controller_name ;
    public static $action_name ;
    
    
    /**
     * 当前应用的配置信息 
     * 载入默认配置然后用应用配置覆盖默认配置
     */
    private static $config = array();
    
    private function __construct(){}

    public static function init(){
        if( self::$is_inited ) return true ;
        /** 载入基本配置 **/
        $app_config = self::loadConfig();
        $default_config = require(FRAME_PATH.'/config/default.php');
        self::$config = array_merge($default_config,$app_config);
                
        /** 初始化缓存信息 **/
        self::$cache = self::getInstance(ucfirst(self::$config['cache_type']).'Cache');
        
        /** 初始化DB配置及连接 **/
        foreach( self::$config['db_source'] as $key=>$val ) {
    	    /** 通过URL方式配置获得[scheme],[user],[pass],[host],[port]索引变量 **/
	        $val = parse_url($val);
			self::$db[$key] = self::getInstance(ucfirst($val['scheme']).'Db');
			self::$db[$key]->init($val,self::$config['db_charset']);
			self::$config['db.'.$key] = $val;
			
			if( self::$db_default === false ) {
				self::$db_default = $key ;
			}
		}
				
		self::$input = self::getInstance('AppInput') ;

		self::$output = self::getInstance('AppOutput') ;

		/** 处理当前请求类型 **/
        if( isset($_SERVER["CONTENT_TYPE"])&&$_SERVER['CONTENT_TYPE']=='application/x-amf' ) {
            self::$model = 'amf';
        }
        
        /** 进行输入处理 **/
        if( self::$model=='text' ) {
            $argv 	= $_SERVER['argv'];
			$script = array_shift( $argv );
			parse_str( implode( '&', $argv ), $args );
			$args['script'] = $script;
			self::in($args);
        }
        else {
            $uri = self::getInstance('URI');
            
            $data_array = array_merge($_GET,$_POST,$uri->getVars());
            self::in( $data_array );
            /*
            foreach( $data_array as $key=>$val ) {
                self::$input->$key = self::addslash($val);
            }
            */
            unset($_GET,$_POST);
            
        }
        
        if( !empty(self::$config['session_start']) ) {
        	session_start();
        }
        
       self::$is_inited = true ;
    }

    /**
     * 应用执行入口
     * 
     * @return void
     */
    public static function run($controller='', $action='', $debug_info=true) {
        if( $debug_info )Debug::start();
	
		//初始化实例参数
		self::init();

		if( !empty($controller) ) {
	    	self::$controller_name = ucfirst($controller) ;
		}
		else {
	    	self::$controller_name = ucfirst(!self::in('c')?self::$config['cotroller_default']:strtolower(self::in('c')));
		}

		if( !empty($action) ){
	    	self::$action_name = $action ;
		}
        else {
	    	self::$action_name = !self::in('a')?self::$config['action_default']:self::in('a');
		}
        
        /** 运行逻辑模型 **/
        try {
			self::$output->setControllerName( self::$controller_name ) ;
			self::$output->setActionName( self::$action_name ) ;

            $controller = self::getInstance(self::$controller_name.'Controller') ;
            
			$before_result = true ;
			if( method_exists($controller,'_beforeAction') ) {
				$before_result = $controller -> _beforeAction(self::$controller_name,self::$action_name);
			}
			
			if( $before_result ) {
				$controller -> {self::$action_name}();
			}

			if( method_exists($controller,'_afterAction') ) {
				$controller -> _afterAction(self::$controller_name,self::$action_name);
			}
        }
        catch( AppException $e) {
        	//throw $e ;
            header('HTTP/1.1 404 Not found');
			
            exit('Path not exists. '.self::$controller_name.'/'.self::$action_name);
        }
        
        /** 进行输出处理 **/
        
        //self::finish();
        
        if( $debug_info ) Debug::over();
    }
    
    /**
     * 获得一个实例化的对象
     * 
     * @param mixed $classname 类名
     * @param string $tag 类标识，不同的类标识对应一个具体的实例化的类
     * @return
     */
    public static function getInstance($classname,$tag='main'){
        if( !isset(self::$_instance[$tag][$classname]) ) {
            if( class_exists($classname) ) {
                self::$_instance[$tag][$classname] = new $classname ;
            }
            else {
                throw new AppException('Class "'.$classname.'" Not Exists');
            }
        }
        return self::$_instance[$tag][$classname];
        
    }


    /**
     * 自动载入类文件处理方法
     * 
     * @param mixed $classname
     * @return void
     */
    public static function autoload($classname,$ext='.class.php') {
        if ($classname != 'Model' && substr($classname, -5) == 'Model') {
            $path = APP_PATH . '/model';
        } elseif ($classname != 'Controller' && substr($classname, -10) == 'Controller') {
            $path = APP_PATH . '/controller';
        } elseif (substr($classname, -9) == 'Exception') {
            $path = FRAME_PATH . '/core/Exception';
        } elseif ($classname != 'Db' && substr($classname, -2) == 'Db') {
            $path = FRAME_PATH . '/core/Db';
        } elseif ($classname != 'Cache' && substr($classname, -5) == 'Cache') {
            $path = FRAME_PATH . '/core/Cache';
        } else {
            if( is_file(APP_PATH.'/plugins/' . $classname . $ext) ) {
                $path = APP_PATH.'/plugins';
            }
            elseif( is_file(FRAME_PATH.'/plugins/' . $classname . $ext) ) {
                $path = FRAME_PATH.'/plugins';
            }
            else{
                $path = dirname(__file__);
            }
        }

        $filename = $path . '/' . $classname . $ext;
        return self::requireCache($filename);
    }

    /**
     * 类文件包含缓存计算
     * 
     * @param mixed $file 真实文件名
     * @return Boolean true/false 是否载入文件成功（如果文件不存在会返回失败）
     */
    public static function requireCache($file) {
	
		$filename = realpath($file);
        if (!isset(self::$_import_files[$filename])) {
            if( !is_file($filename) ) {
				//throw new Exception( "file '".(empty($filename)?$file:$filename)."' not exists.");
                return false;
            }
            else {
                require $filename;
                self::$_import_files[$filename] = true;
            }
        }
        return self::$_import_files[$filename];
    }
    
    public static function addslash($val,$strict=false){
        if( get_magic_quotes_gpc()&&!$strict ) {
            return $val;
        }
        else {
            if( is_array($val) ) {
                $array = array();
                foreach( $val as $k=>$v ) $array[$k] = self::addslash($v,$strict);
                return $array ; 
            }
            else {
                return addslashes($val);
            }
        }
    }
    
    /**
     * 载入配置目录的所有配置文件
     * 
     * @return $config_array
     */
    public static function loadConfig(){
    	$config_path = APP_PATH.'/config';
    	$dir = dir($config_path);
   	 	$config = array(); 
		while( $file=$dir->read() ){
   	 		if( !$file||strpos($file,'.')===0 ) {
   	 			continue ;
   	 		}
   	 		//	var_dump($config_path.'/'.$file,require($config_path.'/'.$file));
   	 		$loaded_config = require($config_path.'/'.$file);
   	 		if( empty($loaded_config)||!is_array($loaded_config) ) {
   	 			continue ;
   	 		}
   	 		$config = array_merge($config,$loaded_config);
   	 	}
   	 	return $config ;
    }
    
    
    /**
     * 获得/设置配置变量
     * 
     * @param mixed $key 索引值/覆盖的数组设置
     * @param string $value 设置值
     * @return void/mixed 返回对应值
     */
    public static function conf($key,$value=KEY_NOT_SET_VALUE) {
        if( is_array($key) ) {
            self::$config = array_merge(self::$config,$key);
        }
        else {
            if( $value==KEY_NOT_SET_VALUE ){
                return isset(self::$config[$key])?self::$config[$key]:false; 
            }
            else {
                self::$config[$key] = $value;
            }
        }
    }
    
    /**
     * 获得/设置输入变量
     * 
     * @param mixed $key 索引值/或者多项索引的数组
     * @param string $value 设置值
     * @return void/mixed 如果未设定key则返回所有值否则返回对应值
     */
    public static function in($key='',$value=KEY_NOT_SET_VALUE){
        if( empty($key) ){
            return self::$input->getArgs() ;
        }
        elseif( is_array($key) ) {
        	self::$input->set($key) ;
        }
        elseif( $value==KEY_NOT_SET_VALUE ) {
        	return self::$input->$key ;
        }
        else {
        	self::$input->$key = $value ;
        }
    }
    
    /**
     * 获得/设置输出变量
     * 
     * @param string $key 索引值/或者多项索引的数组
     * @param string $value 设置值
     * @return void/mixed 如果未设定key则返回所有值否则返回对应值
     */
    public static function out($key='',$value=KEY_NOT_SET_VALUE){
        if( empty($key) ){
            return self::$output->getArgs() ;
        }
        elseif( is_array($key) ) {
        	self::$output->set($key) ;
        }
        elseif( $value==KEY_NOT_SET_VALUE ) {
        	return self::$output->$key ;
        }
        else {
        	self::$output->$key = $value ;
        }
    }

	/**
	 * 获得当前应用的DB操作对象
	 *
	 */
	public static function db($key=KEY_NOT_SET_VALUE){
		if( $key==KEY_NOT_SET_VALUE ) {
			$key = self::$db_default ;
		}

		return self::$db[$key] ;
	}
	
	public static function output(){
		return self::$output ;
	}
	
	public static function input(){
		return self::$input ;
	}
    
    /**
     * 获得/设置缓存信息
     * 
     * @param string $key 索引值/或者多项索引的数组
     * @param string $value 设置值
     * @return void/mixed 返回对应值
     */
    public static function cache($key,$value=KEY_NOT_SET_VALUE){
        if( is_array($key) ){
            self::$cache->set($key);
        }
        else {
            if( $value==KEY_NOT_SET_VALUE ){
                return self::$cache->get($key);
            }
            else {
                self::$cache->set($key,$value);
            }
        }
    }
    
    /**
     * 清除所有系统缓存
     * 
     * @return bool
     */
    public static function clearCache(){
        return self::$cache->clear();
    }
    
    public static function setModel($model){
    	self::$model = $model ;
    }
    
    
    /**
     * 输出相关错误信息
     * 
     * @param mixed $message
     * @param integer $code
     * @return
     */
    public static function error( $message,$code=-1 ){
        self::$output['state'] = $code ;
        self::$output['message'] = $message ;
        return false ;
    }


    public static function finish($msg='',$halt=false,$title='App finish'){
        switch ( self::$model ) {
            case 'text' :
                echo $msg ;
                break;
            case 'html' : 
                //echo nl2br(str_replace(' ','&nbsp;',$msg));
                echo "<fieldset><legend>$title</legend><pre>$msg</pre></fieldset>";
                break ;
		    case 'amf':
				
				break ;
            default : 
                exit('Config APP Model ERROR!');
            
        }
    	if($halt) { exit; }
    }
    
	/**
	 * 输出调试信息到系统日志
	 *
	 * @param string $message 消息内容 
	 */
	public static function trace( $message ) {
		Debug::log( 'application',$message );	
	}

}
