<?php
/**
 * 后台任务管理器
 * 
 * @package Mixjoy
 * @author zhaohe
 * @copyright 2011
 * @version $Id$
 * @access public
 */
class ConsoleTask {
	
    /**
     * UNIX/LINUX 下获取正在运行的进程 ID 数组
     * 
     * @param string $script 执行的脚本名
     * @param string $bin 命令中的解析器路径, 如 ./php  python
     * @return array 
     */
    public static function getTaskId( $script, $bin='php' ) {
        $grepScript = preg_quote( $script );
        exec( "ps -ef | grep '$grepScript'", $output );
        $procIds = array();
        foreach ( $output as $opKey => $opItem ) {
            if ( strstr( $opItem, "$bin $script" ) ) {
                preg_match( "/^[^ ]+[ ]+([0-9]+).*$/", $opItem, $pregMatch );
                array_push( $procIds, $pregMatch[1] );
            }
        }
        return $procIds;
    }

    /**
     * UNIX/LINUX 下获取正在运行的进程的信息
     * 
     * @param string $script 执行的脚本名
     * @param string $bin 命令中的解析器路径, 如 ./php  python
     * @return array 
     */
    public static function getTaskInfo( $script, $bin='php' ) {
        $grepScript = preg_quote( $script );
        exec( "ps -ef | grep '$grepScript'", $output );
        $countProc = 0;
        $taskInfo = array( 'count' => 0, 'pid' => array(), 'cid' => array() );
        foreach ( $output as $opKey => $opItem ) {
            if ( strstr( $opItem, "$bin $script" ) ) {
                preg_match( "/^[^ ]+\s+(\d+).*cid\=(\d+)$/", $opItem, $pregMatch );
                if ( isset( $pregMatch[1] ) )
                    $taskInfo['pid'][] = $pregMatch[1];
                if ( isset( $pregMatch[2] ) )
                    $taskInfo['cid'][] = $pregMatch[2];
                $taskInfo['count']++;
            }
        }
        return $taskInfo;
    }

    /**
     * UNIX/LINUX 下获取正在运行的进程数量
     * 
     * @param string $script 执行的脚本名
     * @param string $bin 命令中的解析器路径, 如 ./php  python
     * @return integer  
     */
    public static function getTaskCount( $script, $bin='php' ) {
        $grepScript = preg_quote( $script );
        exec( "ps -ef | grep '$grepScript'", $output );
        $countProc = 0;
        foreach ( $output as $opKey => $opItem ) {
            if ( strstr( $opItem, "$bin $script" ) )
                $countProc++;
        }
        return $countProc;
    }

    /**
     * 分析任务执行时间
     * 
     * @param string $execTime 任务执行时间 
     * @return array 
     */
    public static function paserExecTime( $execTime ) {
        $execTime = Ext_Array::serialToArray( $execTime );
        foreach ( $execTime as $key => $value ) {
            $execTime[$key] = explode( ',', $value );
        }
        return $execTime;
    }

    /**
     * 检测是否到达执行时间
     * 
     * @param mixed $nowTime 当前时间
     * @param mixed $execTime 执行时间 
     * @return Boolean 
     */
    public static function checkExecTime( $nowTime, $execTime ) {
        $checked = true;
        foreach ( $nowTime as $key => $val ) {
            if ( isset( $execTime[$key] ) && !in_array( $val, $execTime[$key] ) ) {
                $checked = false;
            }
        }
        return $checked;
    }

}
