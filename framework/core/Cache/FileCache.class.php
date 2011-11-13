<?php
/**
 * 文件缓存操作
 * 
 * @package framework
 * @author zhaohe
 * @copyright 2011
 * @version $Id$
 * @access public
 */
class FileCache extends Cache {
    
    /**
     * 缓存文件存储路径
     */
    private $path ;
    
    
    /**
     * 缓存目录级别0表示所有缓存都存在path定义的目录
     * 等于N时按照hash值的前N个字母以此建立相关目录
     * 考虑到程序的复杂度，最大不超过3级目录
     */
    private $cache_level = 1;
    
    /**
     * 文件索引追加key后获得hash值
     */
    private $hash_key = '!@#$%';
    
    private $content_prefix = '<?php //' ; 
    
    /**
     * 构造方法，初始化相关参数并判断是否可以写入
     * 
     * @return void
     */
    public function __construct(){
        if( $cache_level = App::conf('file_cache_level') ) {
            if($cache_level>3) $cache_level = 3 ;
            $this->cache_level = intval($cache_level) ;
        }
        $this->path = DATA_PATH.'/cache';
        
        if( (!is_dir($this->path)&&!mkdir($this->path,0777,true))||!touch($this->path.'/write_test.data') ) {
            throw new AppException('Path '.$this->path.' not writable!');
        }
    }
    
    public function get($key){
        $file = $this->getFile($key);
        if( is_file($file) ){
            $data = str_replace($this->content_prefix,'',file_get_contents($file));
            return unserialize($data);
        }
        else {
            return false ;
        }
    }
    
    public function set($key,$value=KEY_NOT_SET_VALUE){
        if( is_array($key) ) {
            foreach( $key as $k=>$v ) {
                $this->set($k,$v);
            }
        }
        elseif($value==KEY_NOT_SET_VALUE) {
            $this->delete($key);
        }
        else {
            $file = $this->getFile($key);
            $data = $this->content_prefix.serialize($value);
            file_put_contents($file,$data);
        }
    }

    public function delete($key){
        $file = $this->getFile($key);
        if( is_file($file) ) unlink($file);
    }
    
    public function clear($path=''){
        if( empty($path) ) $path = $this->path;
        else $path = $this->path.'/'.$path;
        
        if( !is_dir($path) ) return false ;
        
        $d = dir($path);
        while( $file = $d->read() ) {
            if( $file=='.'||$file=='..' ) {
                continue ;
            }
            elseif( is_dir($this->path.'/'.$file) ) {
                $this->clear($file);
            }
            else{
                unlink($path.'/'.$file);
            }
        }
        return true ;
    }
    
    public function close(){
        return true ;
    }
    
    private function getHash($key){
        return substr(md5($key.$this->hash_key),8,16);
    }
    
    private function getFile($key){
        $hash = $this->getHash($key);
        $sub_paths = array();
        for($i=0;$i<$this->cache_level;$i++){
            $sub_paths[] = $hash[$i];
        }
        $sub_path = implode('/',$sub_paths);
        $sub_path = $this->path.(empty($sub_path)?'':'/'.$sub_path);
        if( !is_dir($sub_path) ) mkdir($sub_path,0777,true);
        return $sub_path.'/'.$hash.'.php';
    }
}
