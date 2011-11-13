<?php

class MysqlDb extends Db {
    private $link ;
    
    private $charset ;
    
    /**
     * 默认构造方法
     * 
     * @return void
     */
    public function __construct(){
    	
    }
    
    /**
     * 初始化数据库配置
     * 
     * @return void
     */
    public function init($config,$charset='utf8') {
    	$this->config = $config;
        $this->charset= $charset;
        $this->initSql();
    }

    /**
     * 链接并设置数据库
     * 
     * @return void
     */
    public function connect() {
        if( !($this->link = mysql_connect($this->config['host'],$this->config['user'],$this->config['pass'])) ) {
            throw new DbException('Connect Database Server Error!');
        }
        if( isset($this->config['path']) ) {
            $this->setDb(str_replace('/','',$this->config['path']));
        }
    }
    
    /**
     * 设置数据库
     * 
     * @param string $name
     * @return void
     */
    public function setDb($name){
        $db_map = App::conf('db_map');
        if( isset($db_map[$name]) ) {
            $this->dbname = $db_map[$name];
        }
        else {
            $this->dbname = $name ;
        }
        mysql_select_db($this->dbname, $this->link);
    }

    /**
     * 关闭数据库连接
     * 
     * @return void
     */
    public function close() {
        return mysql_close( $this->link );
    }

    /**
     * 开始事务
     * 
     * @return
     */
    public function begin() {
        return $this->query('BEGIN');
    }

    /**
     * 事务回滚
     * 
     * @return
     */
    public function rollBack() {
        return $this->query('ROLLBACK');
    }

    /**
     * 事务提交
     * 
     * @return
     */
    public function commit() {
        return $this->query('COMMIT');
    }

    /**
     * 执行数据库语句
     * 
     * @param string $sql
     * @param string $type
     * @return Boolean
     */
    public function query($sql='',$type='select') {
        if( !$this->link || !is_resource($this->link) ) {
            $this->connect();
            mysql_query('set names '.$this->charset,$this->link);
        }
        if( !empty($sql) ) {
            $this->sql = $sql;
        }
        else {
            $this->sql = $this->getSql($type);
        }

		if( !empty($this->conditions['usedb']) ) {
			$this->setDb($this->conditions['usedb']);
		 }

        $start_time = microtime(true);
        $result = mysql_query($this->sql,$this->link);
        $query_time = microtime(true)-$start_time;
        Debug::log('sql',$this->sql."| Time:".sprintf('%0.4f',$query_time).' | Error:'.mysql_error($this->link));
        $this->initSql();
        if( $result === false ){
            throw new DbException('Sql Error!');
        }
        else {
            $this->count(1);
            if( $insert_id=mysql_insert_id($this->link) ) {
                $this->last_insert_id = $insert_id ;
            }
            return $result;
        }
    }

    public function getOne($sql='') {
        $row = mysql_fetch_row($this->query($sql));
        return $row[0];
    }

    public function getRow($sql='') {
        return mysql_fetch_assoc($this->query($sql));
    }

    public function getAll($sql='') {
        $arr = array();
        $result = $this->query($sql);
        while( $row=mysql_fetch_assoc($result)  ) {
            $arr[] = $row;
        }
        return $arr ;
    }

	/**
	 * 获得数据库的所有字段名
	 */
	public function getColumns($table){
		$sql = "SHOW COLUMNS FROM `{$table}`";	
		$result = $this->getAll($sql);
		$columns = array();
		foreach( $result as $r ){
			$columns[] = $r['Field'];		
		}
		return $columns ;
	}

    
    /**
     * 生成Sql语句
     * 
     * @param mixed $type
     * @return
     */
    public function getSql($type){
        $field = empty($this->conditions['field'])?'*':$this->conditions['field'];
        $where = empty($this->conditions['where'])?'':'where '.$this->conditions['where'];
        $order = empty($this->conditions['order'])?'':'order by '.$this->conditions['order'];
        $limit = empty($this->conditions['limit'])?'':'limit '.$this->conditions['limit'];
        if( !$table = $this->conditions['table'] ) {
            throw new DbException('Table Name Empty!');
        }
        
        switch($type) {
            case 'insert' :
            case 'replace' :
                $data_keys = array();
                $data_vals = array();
                foreach( $this->conditions['data'] as $key=>$val ){
                    $data_keys[] = $key ;
                    $data_vals[] = mysql_escape_string($val) ;
                }
                $keys = implode('`,`',$data_keys);
                $vals = implode('\',\'',$data_vals);
                $sql = "{$type} into {$table}(`{$keys}`) values('{$vals}')";
                break;
            case 'update' :
                if( is_array($this->conditions['data']) ) {
                    $col_info = array();
                    foreach( $this->conditions['data'] as $key=>$val ) {
                        $col_info[] = "`$key`='$val'";
                    }
                    $col_string = implode(',',$col_info);
                }
                else {
                    $col_string = $this->conditions['data'];
                }
                $sql = "update {$table} set {$col_string} {$where} {$limit}";
                break;
            case 'delete' :
                $sql = "delete from {$table} {$where} {$order} {$limit}";
                break;
            case 'select' :
                $sql = "select {$field} from {$table} {$where} {$order} {$limit}";
                break;
        }
        return $sql ;
    }
    
    /**
     * 获得上一条执行的sql
     * 
     * @return string
     */
    public function lastSql(){
        return $this->last_sql;
    }
        
    /**
     * 获得上一条信息
     * 
     * @return
     */
    public function getError(){
        return mysql_error($this->link).'(#'.mysql_errno($this->link).')';
    }
}
