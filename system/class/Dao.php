<?php
/**
 * Created by PhpStorm.
 * User: LIKANG
 * Date: 2017/12/12
 * Time: 14:58
 */

abstract class Dao
{
    abstract public function get_table_name ();
    abstract public function get_pk_id();
    abstract public function get_db_name();
    private $slave_pdo;
    private $master_pdo;
    public $force_master = false;
    private $db_config;
    public function force_from_master() {
        $this->force_master = true;
    }

    public function __construct()
    {
        $db_config = ConfigTool::get_instance()->get_config('db','database');
        $this->db_config = $db_config[$this->get_db_name()];
        if(!$this->db_config){
            DebugTool::get_instance()->debug("mysql db not found", "code error");
        }
    }

    /**
     * 获取主库
     * @return PDO
     */
    private function get_master_pdo() {
        if(!$this->master_pdo) {
            $this->master_pdo = DBTool::get_instance()->get_pdo($this->db_config['master']);
        }
        return $this->master_pdo;
    }

    /**
     * 获取从库
     * @return PDO
     */
    private function get_slave_pdo() {
        if($this->force_master) {
            return $this->get_master_pdo();
        }
        if(!$this->slave_pdo) {
            $this->slave_pdo = DBTool::get_instance()->get_pdo($this->db_config['slave']);
        }
        return $this->slave_pdo;
    }


    public function get_by_where($where, $order = "", $limit = "", $fields="*"){
        $sql = "select {$fields} from `".$this->get_table_name()."` ";
        $where_data = $this->build_where($where);
        $sql .= $where_data['where'];
        $data = $where_data['data'];
        if($order){
            $order = " order by ". $order;
        }

        if($limit){
            $limit = " limit ". $limit;
        }

        $sql .= $order . $limit;

        $stmt = $this->get_slave_pdo()->prepare($sql);

        $result = array();
        $start_time = microtime(1);
        if($stmt->execute($data)){
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                $result[] = $row;
            }
        }

        DebugTool::get_instance()->debug($sql.' Time coast:'.microtime(1)-$start_time,'sql get');
        return $result;
    }

    private function build_where($where)
    {
        if(empty($where)){
            return array("where" => " ", "data" => array());
        }

        $where_sql = " where ";
        $where_data = array();
        $is_first = true;

        foreach ($where as $k => $v){
            $field = explode(" ", $k);
            $operator = $field[1];
            if($operator == ""){
                $operator = " = ";
            }elseif ($operator == "in"){
                $data_value = " (".$this->data_implode($v).") ";
                $operator = " in " . $v;
                
            }elseif ($operator = "notin"){

            }
        }

    }

    private function data_implode($data){
        $result = '';
        if(empty($data)) {
            return $result;
        }
        foreach($data as $v) {
            $result .= ",$v";
        }
        return substr($result, 1);
    }



}