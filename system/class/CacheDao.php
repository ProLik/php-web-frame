<?php
/**
 * Created by PhpStorm.
 * User: LIKANG
 * Date: 2017/12/13
 * Time: 15:58
 */

abstract class CacheDao extends Dao
{
    public $is_cache = true;

    private $cache_time = 0;

    const CACHE_ONE_DAY = 86400;
    const CACHE_ONE_HOUR = 3600;
    const CACHE_ONE_MINUTE = 60;
    const CACHE_ONE_WEEK = 604800;

    public function set_cache_time($cache_time){
        $this->cache_time = $cache_time;
    }

    public function disable_cache() {
        $this->is_cache = false;
    }

    private function get_pre_key()
    {
        return $this->get_db_name() . "_" . $this->get_table_name() . "_";
    }

    public function exeSQL($sql)
    {
        $this->set_updated();
        return parent::exeSQL($sql);
    }

    private function set_updated()
    {
        $cache = DBTool::get_instance()->get_cache();
        $cache->set($this->get_pre_key(), time(), $this->cache_time);
    }

    private function is_updated($time)
    {
        if(empty($time)){
            return true;
        }

        $cache = DBTool::get_instance()->get_cache();
        $table_update_time = $cache->get($this->get_pre_key());

        if(empty($table_update_time)){
            return false;
        }

        if($time > $table_update_time){
            return false;
        }else{
            return true;
        }
    }

    public function count_by_where($where)
    {
        if(!$this->is_cache){
            return parent::count_by_where($where);
        }

        ksort($where);
        foreach ($where as $k => $v){
            $wh = explode("", $k);
            if($wh[1] == "in" && is_array($v)){
                //array_flip 交换数组中的键和值
                //array_keys 返回数组中部分的或所有的键名
                $v = array_keys(array_flip($v));
                $where[$k]  = $v;
            }
        }
        $cache = DBTool::get_instance()->get_cache();
        $key = $this->get_pre_key() . "count_".md5($where);
        $uncached = false;
        if($this->is_updated($cache->get($key.'_save_time'))) {
            $uncached = true;
        } else {
            $num = $cache->get($key);
            if($num===false) {
                $uncached = true;
            }
        }
        if($uncached) {
            $num = parent::count_by_where($where);
            $cache->set($key,$num,$this->cache_time);
            $cache->set($key.'_save_time',time(),$this->cache_time);
        } else {
            DebugTool::get_instance()->debug($key.'get_num from cache','from cache');
        }
        return $num;
    }

    public function get_by_id($id, $fields = '*'){

        if(empty($id)){
            return false;
        }

        if(!$this->is_cache){
            return parent::get_by_id($id, $fields);
        }

        $fields = "*";

        $cache = DBTool::get_instance()->get_cache();
        $key = $this->build_row_key($id, $fields);
        $result = $cache->get_array($key);

        $cache_time = $cache->get($key."_cache_time");
        if(is_array($result) && !empty($result) && !$this->is_updated($cache_time)){
            return $result;
        }else{
            $result = false;
        }

        if(empty($result) || !is_array($result)){
            $result = parent::get_by_id($id, $fields);
            if($result === false){
                $result = array();
            }
            $cache->set_array($key,$result,$this->cache_time);
            //缓存时间
            $cache->set($key."_cache_time",time(),$this->cache_time);
        }
        return $result;

    }

    private function build_row_key($id, $fields='*') {
        if($id!='') {
            return $this->get_pre_key().$id."_".$fields;
        } else {
            return $this->get_pre_key().'0'."_".$fields;
        }
    }

    public function get_by_where($where, $order='', $limit='0,2000', $fields = '*')
    {
        if(!$this->is_cache){
            return parent::get_by_where($where, $order, $limit, $fields);
        }

        ksort($where);
        foreach ($where as $k => $v){
            $wh = explode("", $k);
            if($wh[1] == "in" && is_array($v)){
                //array_flip 交换数组中的键和值
                //array_keys 返回数组中部分的或所有的键名
                $v = array_keys(array_flip($v));
                $where[$k]  = $v;
            }
        }
        $cache = DBTool::get_instance()->get_cache();
        $key = $this->get_pre_key() . "get_" . md5(serialize($where) . $order . $limit . $fields);
        $uncached = false;
        if($this->is_updated($cache->get($key . '_save_time'))) {
            $uncached = true;
        }

        if(!$uncached){
            $result = $cache->get_array($key . "data");
            if(empty($result) || !is_array($result)){
                $uncached = true;
            }
        }

        if($uncached){
            $result = parent::get_by_where($where,$order,$limit,$fields);
            $cache->set($key.'_save_time',time(),$this->cache_time);
            $cache->set_array($key.'data',$result,$this->cache_time);
        }
        return $result;
    }

    public function get_single_by_where($where, $order="", $limit="1", $field="*")
    {
        $result = $this->get_by_where($where, $order, $limit, $field);
        return $result;
    }

    public function get_by_id_array($id_array, $order="", $fields="*")
    {
        if(!$this->is_cache){
            return parent::get_by_id_array($id_array, $order, $fields);
        }
        $cache = DBTool::get_instance()->get_cache();
        sort($id_array);
        $key = $this->get_pre_key() . "get_id_array" . md5(serialize($id_array) . $order . $fields);

        $uncache = false;
        if($this->is_updated($cache->get($key . "_save_time"))){
            $uncache = true;
        }
        if($uncache){
            $result =  parent::get_by_id_array($id_array, $order, $fields);

            $cache->set($key . '_save_time', time(), $this->cache_time);
            $cache->set_array($key . '_data', $result, $this->cache_time);
        } else {
            $result = $cache->get_array($key . "_data");
        }
        return $result;

    }

    public function clear_row_cache($id) {
        $mem = DBTool::get_instance()->get_cache();
        $key = $this->build_row_key($id);
        DBTool::get_instance()->debug($key,'删除rowkey');
        $mem->delete($key);
    }


    public function update_by_id($id, $data)
    {
        $this->clear_row_cache($id);
        $this->set_updated();
        return parent::update_by_id($id, $data);
    }

    public function update_by_where($where,$data,$time_no_delay = false) {

        //可能出现延迟的问题,更新必须走master
        if($time_no_delay) {
            $tmp = $this->force_master;
            $this->force_master = true;
            $result = parent::get_by_where($where);
            $this->force_master = $tmp;
        } else {
            $result = $this->get_by_where($where);
        }
        $this->set_updated();
        foreach($result as $k=>$v) {
            $this->update_by_id($v[$this->get_pk_id()], $data);
        }
    }

    public function insert($data)
    {
        $this->set_updated();
        parent::insert($data);
    }

    public function del_by_id($id) {
        $this->clear_row_cache($id);
        $this->set_updated();
        return parent::del_by_id($id);
    }

    public function del_by_where($where) {
        $result = $this->get_by_where($where);
        foreach($result as $k=>$v) {
            $this->del_by_id($v[$this->get_pk_id()]);
        }
        return true;
    }
}