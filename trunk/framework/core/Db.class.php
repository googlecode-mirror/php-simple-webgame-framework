<?php
/**
 +-------------------------------------------------
 * 数据库基础操作类
 * 
 * @package framework
 * @author zhaohe
 * @copyright 2011
 * @version $Id$
 * @access public
 +-------------------------------------------------
 */
abstract class Db {
    protected $conditions = array() ;
    
    protected $do_keys = array(
        'select'    => 0 ,
        'update'    => 1 ,
        'delete'    => 1 ,
        'replace'   => 1 ,
        'insert'    => 1 ,
    );
    
    protected $sql ;
    protected $last_sql ;
    protected $dbname ;
    
    protected $last_insert_id ;
    
    protected $config ;
    
    abstract function init($config);
    
	/**
	 * 连接数据库
	 * 
	 * @return void 
	 */
	abstract function connect();
    
	/**
	 * 关闭数据库连接
	 * 
	 * @return void
	 */
	abstract function close();

	/**
	 * 开始事物机制
	 * 
	 * @return
	 */
	abstract function begin();

	/**
	 * 提交事务
	 * 
	 * @return
	 */
	abstract function commit();

	/**
	 * 执行sql
	 * 
	 * @param string $sql
	 * @param string $type
	 * @return Boolean 是否执行成功
	 */
	abstract function query($sql='',$type='select');

	/**
	 * 获得返回数据的第一个索引值
	 * 
	 * @param string $sql
	 * @return int/string 
	 */
	abstract function getOne($sql='');

	/**
	 * 返回sql执行的第一行
	 * 
	 * @param string $sql
	 * @return
	 */
	abstract function getRow($sql='');

	/**
	 * 返回sql执行的所有行的数组
	 * 
	 * @param string $sql
	 * @return
	 */
	abstract function getAll($sql='');

	/**
	 * 获得一个表的所有字段
	 */
	abstract function getColumns($table);

    /**
     * 设置当前使用的数据库
     * 
     * @param mixed $name
     * @return void
     */
    abstract function setDb($name);
    
    /**
     * 获得上一条执行的sql
     * 
     * @return string
     */
    abstract function lastSql();
    
    /**
     * 获得数据库的出错信息
     * 
     * @return
     */
    abstract function getError();
    
    /**
     * 获得上一次插入的自增ID
     * 
     * @return int
     */
    public function lastInsertId(){
        return $this->last_insert_id;
    }
    

    /**
     * 调用方法
     * 
     * @param mixed $name
     * @param mixed $args
     * @return
     */
    public function __call($name,$args){
        if( isset($this->conditions[$name])&&!empty($args[0]) ){
            $this->conditions[$name] = $args[0];
        }
        elseif( isset($this->do_keys[$name]) ) {
            $arg = isset($args[0])?$args[0]:'';
            $this->query($arg,$name);
        }
        else{
        	throw new AppException('Method '.$name.' not exists');
        }
        return $this;
    }
    
    /**
     * 重置sql关联参数
     * 
     * @return void
     */
    public function initSql(){
        $this->conditions = array(
            'usedb' => '',
            'table' => '' ,
            'data'  => array(),
            'field' => '*' ,
            'where' => '1' ,
            'limit' => '' ,
            'order' => '' ,
        );
        $this->last_sql = $this->sql;
        $this->sql = '';
    }
    
    public function count($set=0){
        static $query_count = 0 ;
        if( empty($set) ){
            return $query_count ;
        }
        else {
            $query_count ++ ;
        }
    }
    
}

