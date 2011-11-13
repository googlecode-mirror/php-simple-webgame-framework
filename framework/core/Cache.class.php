<?php
/**
 * 缓存基础抽象类
 * 
 * @package framework
 * @author zhaohe
 * @copyright 2011
 * @version $Id$
 * @access public
 */
abstract class Cache {
    
    /**
     * 获得缓存数据
     * 
     * @param mixed $key
     * @return void
     */
    abstract function get($key);
    
    /**
     * 设置缓存数据
     * 
     * @param mixed $key
     * @param mixed $value
     * @return void
     */
    abstract function set($key,$value=KEY_NOT_SET_VALUE);

    /**
     * 删除一条缓存数据
     * 
     * @param mixed $key
     * @return void
     */
    abstract function delete($key);
    
    /**
     * 清除所有缓存
     * 
     * @return void
     */
    abstract function clear();
    
    /**
     * 关闭缓存连接
     * 
     * @return void
     */
    abstract function close();
    
    /**
     * Cache::__destruct()
     * 
     * @return void
     */
    public function __destruct(){
        $this->close();
    }
}
