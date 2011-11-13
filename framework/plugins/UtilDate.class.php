<?php

/**
 * 日期时间扩展
 * @author YHS
 * @time 16:10 2010-10-28
 * @version 1.0
 */
class UtilDate {
    /**
     * @var integer 当前时间戳
     */
    public static $currentTime = 0;

    /**
     * 获取微秒时间
     * 
     * @return mixed 微秒时间
     */
    public static function getMicrotime() {
        return microtime( true );
    }

    /**
     * 获取当前时间戳
     * 
     * @return int 当前时间戳
     */
    public static function getTime() {
        return time();
    }

    /**
     * getTime 的别名
     * 
     * @return mixed 当前时间戳
     */
    public static function now() {
        return self::getTime();
    }

    /**
     * 获取日期/时间信息
     * 
     * @param mixed $sec 时间戳, 默认为当前时间
     * @return array 日期/时间信息
     */
    public static function getInfo( $sec = null ) {
        if ( !$sec )
            $sec = self::getTime();
        $rs = getdate( $sec );
        $info = array( 'year' => $rs['year'], 'month' => $rs['mon'], 'day' => $rs['mday'],
            'week' => $rs['wday'], 'hour' => $rs['hours'], 'minute' => $rs['minutes'],
            'second' => $rs['seconds'], 'time' => $sec, );
        foreach ( $info as & $value ) {
            if ( $value < 10 ) {
                $value = '0' . $value;
            }
        }
        unset( $value );
        return $info;
    }

    /**
     * 格式化时间
     * 
     * @param mixed $sec 时间戳, 默认为当前时间
     * @param string $type 转换格式
     * @return string 转换后的时间 
     */
    public static function format( $sec = null, $type = 'Y-m-d H:i:s' ) {
        if ( !$sec )
            $sec = self::getTime();
        return date( $type, $sec );
    }

    /**
     * 加减时间,返回运算后的时间字符串表示
     * 
     * @param mixed $time 时间
     * @param integer $addTime 添加或者减少的时间
     * @return string 运算后的时间字符串表示
     */
    public static function add( $time, $addTime = 0 ) {
        if ( !is_numeric( $time ) ) {
            $time = strtotime( $time );
        }
        $time += $addTime;
        return self::format( $time );
    }

    /**
     * 根据秒数返回类似电子钟的格式 00:00:00
     * 
     * @param integer $sec 时间戳
     * @return string  电子钟字符串
     */
    public static function getClock( $sec ) {
        $h = 0;
        $m = 0;
        if ( $sec >= 3600 ) {
            $h = floor( $sec / 3600 );
            $sec = $sec % 3600;
        }
        if ( $sec >= 60 ) {
            $m = floor( $sec / 60 );
            $sec = $sec % 60;
        }
        $reArr = array();
        if ( $h < 10 ) {
            $h = '0' . $h;
        }
        if ( $m < 10 ) {
            $m = '0' . $m;
        }
        if ( $sec < 10 ) {
            $sec = '0' . $sec;
        }
        $restr = implode( ':', array( $h, $m, $sec ) );
        return $restr;
    }
}
