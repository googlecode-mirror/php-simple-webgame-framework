<?php

class Model extends Base {
    
    protected $db ;
    
    protected $table ;
    
    protected $db_name ;
    
    protected static $colums = array() ;
    
    public function __construct(){
        $this->db = App::db();
        $db_map = App::conf('db_map');
        $this->db_name = current( array_keys($db_map) );
		$this->db->usedb( $this->db_name );
    }
    
    public function save($table_name,$data,$condition=1,$db_name=''){
		if( empty($db_name) ) {
			$db_name = $this->db_name ;
		}
		return $this->db->usedb($db_name)->table($table_name)->where($condition)->data($data)->update();	
    }
	
    
    /**
     * 插入一条数据库记录
     * 
     * @param mixed $table_name
     * @param mixed $data
     * @param string $db_name
     * @return
     */
    public function add($table_name,$data,$db_name=''){
        if( empty($db_name) ) {
        	$db_name = $this->db_name ;
        }
        //filter not exists keys.
        $insert_data = array();
		$columns = $this->getTableColumns($table_name,$db_name,false);
        foreach( $data as $key=>$val ) {
        	if( !in_array($key,$columns) ) {
				continue ;		
			}
        	$insert_data[$key] = $val ;
        }
        $this->db->usedb($db_name)->table($table_name)->data($insert_data)->insert();
        return $this->db->lastInsertId() ;
    }
    
    public function begin(){
        $this->db->begin();
    }
    
    public function commit(){
        $this->db->commit();
    }
    
	/**
	 * 缓存并返回一个表的所有字段
	 * 
	 * @param mixed $table_name
	 * @param mixed $db_name
	 * @param bool $use_cache
	 * @return Array
	 */
	public function getTableColumns($table_name,$db_name,$use_cache=true){
		$key = __METHOD__.$db_name.$table_name;
		if( !isset( self::$colums[$key] ) ) {
			$cache_data = App::cache($key);
			if( !$cache_data||!$use_cache ) {
				$cache_data = $this->db->usedb($db_name)->getColumns($table_name);
				App::cache($key,$cache_data);
			}
			self::$colums[$key] = $cache_data ;
		}
		return self::$colums[$key] ;
	}
}


