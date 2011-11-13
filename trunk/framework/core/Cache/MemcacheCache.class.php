<?php
/**
 * Memcache缓存操作
 * 
 * @package framework
 * @author zhaohe
 * @copyright 2011
 * @version $Id$
 * @access public
 */
class MemcacheCache extends Cache {
    private $memcache ;
    private $expire = 0 ;
    
    public function __construct(){
		if(!extension_loaded('memcache')) {
			throw new AppException('Memcache extension not loaded');
        }
        $this->memcache = new Memcache();
        if( $temp = explode(':',App::conf('memcache_host')) ){
            $host = !empty($temp[0])?$temp[0]:'localhost' ;
            $port = !empty($temp[1])?intval($temp[1]):11211 ;
            $this->memcache->connect($host,$port);
        }
    }
    
    public function get($key){
        return $this->memcache->get($key);
    }
    
    public function set($key,$value=KEY_NOT_SET_VALUE){
        if( is_array($key) ){
            foreach( $key as $k=>$v ) $this->set($k,$v);
        }
        elseif( $value==KEY_NOT_SET_VALUE ) {
            $this->delete($key);
        }
        else {
            $this->memcache->set($key,$value,MEMCACHE_COMPRESSED);
        }
    }

    public function delete($key){
        $this->memcache->delete($key);
    }
    
    public function clear(){
        $this->memcache->flush();
    }
    
    public function close(){
        $this->memcache->close();
    }
}
