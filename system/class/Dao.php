<?php
/**
 * Created by PhpStorm.
 * User: LIKANG
 * Date: 2017/12/12
 * Time: 14:58
 */

abstract class Dao
{
    abstract public function get_table_name();

    abstract public function get_pk_id();

    abstract public function get_db_name();

    private $slave_pdo;
    private $master_pdo;
    public $force_master = false;
    private $db_config;

    public function force_from_master()
    {
        $this->force_master = true;
    }

    public function __construct()
    {
        $db_config = ConfigTool::get_instance()->get_config('db', 'database');
        $this->db_config = $db_config[$this->get_db_name()];
        if (!$this->db_config) {
            DebugTool::get_instance()->debug("mysql db not found", "code error");
        }
    }

    /**
     * 获取主库
     * @return PDO
     */
    private function get_master_pdo()
    {
        if (!$this->master_pdo) {
            $this->master_pdo = DBTool::get_instance()->get_pdo($this->db_config['master']);
        }
        return $this->master_pdo;
    }

    /**
     * 获取从库
     * @return PDO
     */
    private function get_slave_pdo()
    {
        if ($this->force_master) {
            return $this->get_master_pdo();
        }
        if (!$this->slave_pdo) {
            $this->slave_pdo = DBTool::get_instance()->get_pdo($this->db_config['slave']);
        }
        return $this->slave_pdo;
    }


    public function get_by_where($where, $order = "", $limit = "0,1000", $fields = "*")
    {
        $sql = "select {$fields} from `" . $this->get_table_name() . "`";
        $where_data = $this->build_where($where);
        $sql .= $where_data['where'];
        $data = $where_data['data'];
        if ($order) {
            $order = " order by " . $order;
        }

        if ($limit) {
            $limit = " limit " . $limit;
        }

        $sql .= $order . $limit;

        $pst = $this->get_slave_pdo()->prepare($sql);

        $result = array();
        $start_time = microtime(1);
        if ($pst->execute($data)) {
            while ($row = $pst->fetch(PDO::FETCH_ASSOC)) {
                $result[] = $row;
            }
        }

        DebugTool::get_instance()->debug($sql . ' Time coast:' . microtime(1) - $start_time, 'sql get');
        return $result;
    }

    private function build_where($where)
    {
        if (empty($where)) {
            return array("where" => "", "data" => array());
        }

        $where_sql = " where";
        $where_data = array();
        $is_first = true;

        foreach ($where as $k => $v) {
            $field = explode(" ", $k);
            $operator = $field[1];
            if ($operator == "") {
                $operator = " = ";
            } elseif ($operator == "in") {
                $data_value = " (" . $this->data_implode($v) . ")";
                $operator_result = "in" . $data_value;
                $where_sql .= ($is_first ? "" : " and") . " `$k` $operator_result";
                $is_first = false;
                continue;
            } elseif ($operator = "notin") {
                $data_value = "(" . $this->data_implode($v) . ")";
                $operator_result = " not in" . $data_value;
                $where_sql .= ($is_first ? "" : " and") . " `$k` $operator_result";
                $is_first = false;
                continue;
            }
            $where_sql .= ($is_first ? "" : " and") . " `$k` $operator ?";
            $where_data[] = $v;
            $is_first = false;

        }
        return array("where" => $where_sql, "data" => $where_data);
    }

    private function data_implode($data)
    {
        $result = '';
        if (empty($data)) {
            return $result;
        }
        foreach ($data as $v) {
            $result .= ",$v";
        }
        return substr($result, 1);
    }

    public function get_by_id_array($id_array, $order = "", $fields = "*")
    {
        $where = array(
            $this->get_pk_id() . "in" => $id_array
        );
        return $this->get_by_where($where, $order, "0,1000", $fields);
    }

    public function get_by_id($id, $fields = "*")
    {
        $sql = "select $fields from `" . $this->get_table_name() . "` where `" . $this->get_pk_id() . "=?";
        DebugTool::get_instance()->debug($sql, "sql select");
        $pst = $this->get_slave_pdo()->prepare($sql);
        $pst->execute(array($id));
        $result = $pst->fetch(PDO::FETCH_ASSOC);
        return $result ?: false;
    }

    public function get_single_by_where($where, $order = '', $fields = '*')
    {
        $info = $this->get_by_where($where, $order, '0,1', $fields);
        return $info[0];
    }

    public function get_by_where_group_by($where, $group = '', $limit = '0,2000', $fields = '*')
    {
        $sql = "select {$fields} from `" . $this->get_table_name() . "`";

        $where_data = $this->build_where($where);
        $sql .= $where_data['where'];
        $data = $where_data['data'];
        if ($group) {
            $group = ' group by ' . $group;
        }
        if ($limit) {
            $limit = ' limit ' . $limit;
        }
        $sql .= "{$group}{$limit}";


        $pst = $this->get_slave_pdo()->prepare($sql);
        $result = array();

        $start_time = microtime(1);
        if ($pst->execute($data)) {
            while ($row = $pst->fetch(PDO::FETCH_ASSOC)) {
                $result[] = $row;
            }
        }
        $coast = microtime(1) - $start_time;
        DebugTool::get_instance()->debug($sql . ' Time coast:' . $coast, 'sql select');
        return $result;
    }

    public function get_by_where_for_update($where)
    {
        $sql = "select * from `" . $this->get_table_name() . "`";

        $where_data = $this->build_where($where);
        $sql .= $where_data['where'];
        $data = $where_data['data'];
        $sql .= " for update";

        $stmt = $this->get_master_pdo()->prepare($sql);
        $result = array();

        $st = microtime(1);
        if ($stmt->execute($data)) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $result[] = $row;
            }
        }
        $coast = microtime(1) - $st;
        DebugTool::get_instance()->debug($sql . ' Time coast:' . $coast, 'sql select for update');
        return $result;
    }

    public function count_by_where($where)
    {
        $sql = "select count(*) as num from `" . $this->get_table_name() . "`";
        $where_data = $this->build_where($where);
        $sql .= $where_data["where"];
        $data = $where_data["data"];

        $pst = $this->get_slave_pdo()->prepare($sql);
        if ($pst->execute($data)) {
            $row = $pst->fetch(PDO::FETCH_ASSOC);
            return $row["num"];
        }
        return false;
    }

    public function update_by_where($where, $data)
    {
        if (empty($data)) {
            return false;
        }

        $set_string = "";
        $set_data = array();
        foreach ($data as $k => $v) {
            $set_string .= ",`$k`=?";
            $set_data[] = $v;
        }
        $set_string = substr($set_string, 1);
        $sql = "update `" . $this->get_table_name() . "` set " . $set_string;
        $where_data = $this->build_where($where);
        $sql .= $where_data['where'];
        $wh_data = $where_data['data'];

        DebugTool::get_instance()->debug($sql, 'sql update');

        $stmt = $this->get_master_pdo()->prepare($sql);
        return $stmt->execute(array_merge($set_data, $wh_data));
    }

    public function insert($data)
    {
        if (empty($data)) {
            return false;
        }
        $value_string = '';
        $insert_data = array();
        $field_string = '';

        foreach ($data as $k => $v) {
            $field_string .= ",`{$k}`";
            $value_string .= ",?";
            $insert_data[] = $v;
        }
        $field_string = substr($field_string, 1);
        $value_string = substr($value_string, 1);
        $sql = "insert into `" . $this->get_table_name() . "` ({$field_string}) values ($value_string)";

        DebugTool::get_instance()->debug($sql, 'sql insert');

        $pst = $this->get_master_pdo()->prepare($sql);

        if (!$pst->execute($insert_data)) {
            $error = $pst->errorInfo();
            throw new Exception('SQL ERROR:' . $sql . ';INFO:' . $error[2]);
            return false;
        }
        return $this->get_master_pdo()->lastInsertId();
    }


    public function exeSQL($sql)
    {
        DebugTool::get_instance()->debug($sql, 'sql exe');
        $stmt = $this->get_slave_pdo()->prepare($sql);
        $result = array();
        if ($stmt->execute()) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $result[] = $row;
            }
        }
        return $result;
    }


    public function del_by_id($id)
    {
        $sql = "delete from `" . $this->get_table_name() . "` where `" . $this->get_pk_id() . "`=?";
        DebugTool::get_instance()->debug($sql, 'sql del');
        $stmt = $this->get_master_pdo()->prepare($sql);
        return $stmt->execute(array($id));
    }


    public function del_by_where($where)
    {
        if (empty($where)) {
            return false;
        }
        $sql = "delete from `" . $this->get_table_name() . "`";
        $where_data = $this->build_where($where);
        $sql .= $where_data["where"];
        $data = $where_data["data"];

        DebugTool::get_instance()->debug($sql, 'sql del');
        $stmt = $this->get_master_pdo()->prepare($sql);
        return $stmt->execute($data);
    }

    public function begin()
    {
        $this->get_master_pdo()->beginTransaction();
    }

    public function commit()
    {
        $this->get_master_pdo()->commit();
    }

    public function rollback()
    {
        $this->get_master_pdo()->rollBack();
    }

    public function lock_table()
    {
        return $this->exeSQL("LOCK TABLE {$this->get_table_name()} WRITE");
    }

    public function unlock_table() {
        return $this->exeSQL("UNLOCK TABLE");
    }
}