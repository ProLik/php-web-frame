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

    public function get_pre_key()
    {
        return $this->get_db_name() . "_" . $this->get_table_name() . "_";
    }

    public function exeSQL($sql)
    {
        $this->set_update();
        return parent::exeSQL($sql);
    }

    public function set_update()
    {
        $cache = DBTool::get_instance()->get_cache();
        $cache->set($this->get_pre_key(), time(), $this->cache_time);
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

    }
}