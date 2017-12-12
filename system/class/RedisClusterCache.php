<?php
/**
 * redis 集群
 * Created by PhpStorm.
 * User: LIKANG
 * Date: 2017/12/12
 * Time: 11:26
 */

class RedisClusterCache implements ICache
{
    public static $redis_cluster;

    public function get_redis_cluster()
    {
        if(self::$redis_cluster){
            return self::$redis_cluster;
        }
        return new RedisCluster(NULL,ConfigTool::get_instance()->get_config("redis_cluster", "database"));
    }

    public function set_array($key, $value, $expire = 86400)
    {
        if(is_array($value)){
            $string = serialize($value);
        }else{
            $string = $value;
        }
        $res = $this->get_redis_cluster()->set($key, $string, $expire);
        return $res;
    }

    public function get_array($key)
    {
        $res = $this->get_redis_cluster()->get($key);
        return unserialize($res);
    }

    public function set($key, $value, $expire = 86400)
    {
        return $this->get_redis_cluster()->set($key, $value, $expire);
    }

    public function get($key)
    {
        return $this->get_redis_cluster()->get($key);
    }

    public function delete($key)
    {
        $this->get_redis_cluster()->del($key);
    }
}