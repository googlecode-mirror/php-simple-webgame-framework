<?php
/**
 * 调试及错误处理类
 * 这是一个不能被实例化的静态类
 * 
 * @package framework
 * @author zhaohe
 * @copyright 2011
 * @version $Id$
 * @access public
 */
class Debug extends Base {
    
    private static $msg_model = true ;
    
    private static $log_model = true ;
    
    private static $system ;
    
    private static $debug_info ;
    
    /**
     * 私有实例方法，禁止对象被实例化
     * 
     * @return void
     */
    private function __construct(){}
    
    
    /**
     * 错误处理方法
     * 
     * @param mixed $no
     * @param mixed $msg
     * @param mixed $file
     * @param mixed $line
     * @param mixed $content
     * @return void
     */
    public static  function error($no,$msg,$file,$line,$content){
		$error_code[E_ERROR] = 'ERROR';
		$error_code[E_WARNING] = 'WARNING';
		$error_code[E_PARSE] = 'PARSE_ERROR';
		$error_code[E_NOTICE] = 'NOTICE';
		$error_code[E_CORE_ERROR] = 'CORE_ERROR';
		$error_code[E_CORE_WARNING] = 'CORE_WARNING';
		$error_code[E_COMPILE_ERROR] = 'COMPILE_ERROR';
		$error_code[E_COMPILE_WARNING] = 'COMPILE_WARNING';
		$error_code[E_USER_ERROR] = 'USER_ERROR';
		$error_code[E_USER_WARNING] = 'USER_WARNING';
		$error_code[E_USER_NOTICE] = 'USER_NOTICE';
		$error_code[E_STRICT] = 'STRICT';
		$error_code[E_RECOVERABLE_ERROR] = 'RECOVERABLE_ERROR';
		$error_code[E_ALL] = 'E_ALL';

        $error_info  = "";
//        $error_info .= "\n+----------------------------------------------------------------+";
//        $error_info .= "ErrorNo:$no \n";
        $error_info .= "{$error_code[$no]}: $msg \n";
        $error_info .= "File:$file (line:$line)\n";
        $trace_array = debug_backtrace();
        //var_dump($trace_array);
        $trace_info = '';
        $basic_array = array(
            'class' => '',
            'type' 	=> '',
            'function' => '',
            'file'	=> '',
            'args'	=> array(),
            'line'	=> 0,
        );
        $i = 0 ;
        foreach( $trace_array as $t ) {
            $temp = array_merge($basic_array,$t);
            extract($temp);
            //$file = str_replace(array(APP_PATH,FRAME_PATH),'',$file);
            if( is_array($args) ) {
                $arg_info = array();
                foreach( $args as $arg ) {
                    if( is_array($arg) )
                        $arg_info[] = 'Array';
                    elseif( is_object($arg) )
                        $arg_info[] = 'Object';
                    else
                        $arg_info[] = $arg;
                }
                $args = implode(', ',$arg_info);
            }
            if( !empty($file)&&$class!=__CLASS__ ) {
                $trace_info .= "#{$i} $file($line)  {$class}{$type}{$function}($args)\n";
                $i ++ ;
            }
        }
        if( !empty($trace_info) ) $error_info .= "Trace Info:\n".$trace_info;

        self::message($error_info);
    }
    
    /**
     * 未捕捉的异常处理方法
     * 
     * @param Exception $e
     * @return void
     */
    public static function exception($e){
        $message = method_exists($e,'getMsg')?$e->getMsg():$e->getMessage();
        $msg = "Message:".$message."\n";
        $msg .= "Tracer:\n".$e->getTraceAsString();
        self::message($msg);
    }
    
    public static function setModel($msg_model=true,$log_model=true){
        self::$msg_model = $msg_model;
        self::$log_model = $log_model;
    }
    
    public static function message($msg,$type='error'){
        self::log($type,$msg);
        
        if( App::conf('debug_model') ){
        	App::finish($msg,true);
        } 
    }
    
    /**
     * 记录日志到文件
     * 
     * @param mixed $type
     * @param mixed $msg
     * @return void
     */
    public static function log($type,$msg){
        if( !self::$log_model )  return ;
        $path = DATA_PATH.'/logs/'.date('Ymd');
        if( !file_exists($path) ) {
			$oldumask = umask(0);
			mkdir($path,0777,true);
			umask($oldumask);
		}
        $log_file = $path.'/'.$type.'.log';
        if( !file_exists($log_file) ){
			touch($log_file);
			chmod($log_file,0777);
		}
		$msg = date('Y-m-d H:i:s | ').$msg."\n";
		return error_log($msg,3,$log_file);
    }
    
    /**
     * 模块调试开始
     * 
     * @param string $tag 标识
     * @return void
     */
    public static function start( $tag='main' ) {
        self::$debug_info[$tag] = array(
            'start_time'  => microtime(true),
            'start_memory'=> memory_get_usage(true),
        );
    }
    
    /**
     * 模块调试结束
     * 
     * @param string $tag 标识
     * @return void
     */
    public static function over( $tag='main' ) {
        if( !isset(self::$debug_info[$tag]['start_time']) ) {
            throw new AppException("Debug tag '$tag' not defined.");
        }
        self::$debug_info[$tag]['end_time']   = microtime(true);
        self::$debug_info[$tag]['end_memory'] = memory_get_usage(true);
        self::$debug_info[$tag]['time'] = sprintf("%0.4f",(self::$debug_info[$tag]['end_time']-self::$debug_info[$tag]['start_time']));
        self::$debug_info[$tag]['memory'] = sprintf("%0.1fK",(self::$debug_info[$tag]['end_memory']-self::$debug_info[$tag]['start_memory'])/1024);
        unset( self::$debug_info[$tag]['start_time'],self::$debug_info[$tag]['start_memory'],self::$debug_info[$tag]['end_time'],self::$debug_info[$tag]['end_memory'] );
    }
    
    /**
     * 获得调试信息
     * 
     * @param string $tag 标识符
     * @return array('time'=>执行时间,'memory'=>消耗的内存)
     */
    public static function getInfo( $tag='main' ){
        return self::$debug_info[$tag];
    }
    
    /**
     * 输出调试信息
     * 
     * @param mixed $obj
     * @param string $title
     * @return void
     */
    public static function dump($obj,$title='DUMP',$type='dump'){
		switch( $type ) {
			case 'print' :
				$content = print_r($obj,true);
				break;
			default:
        		ob_start();
        		var_dump($obj);
        		$content = ob_get_contents()."\n";
        		ob_end_clean();
        	}
		
		App::finish($content,false,$title);

    }
    
}

