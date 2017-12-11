<?php
/**
 * Created by PhpStorm.
 * User: LIKANG
 * Date: 2017/12/8
 * Time: 17:55
 */

class MyRedis implements ICache
{

    public function __construct()
    {
        cf_require_class("ConfigTool");
        cf_require_class("DebugTool");
    }

    private function get_master_redis() {
        return $this->get_redis('master');
    }

    private function get_slave_redis() {
        return $this->get_redis('slave');
    }

    public function get_redis($type = "master"){
        if(self::$$type){
            return self::$$type;
        }

        self::$$type = new Redis();
        $redis_config = $this->get_redis_config($type);
        self::$$type->pconnect($redis_config['host'], $redis_config['port']);
        if($redis_config["password"]){
            self::$$type->auth($redis_config['password']);
        }
        return self::$$type;

    }

    public function set_array($key, $value, $expire = 0)
    {
        if (is_array($value)) {
            $string = serialize($value);
        } else {
            $string = $value;
        }
        
        $res = $this->set($key, $string, $expire);
   
        DebugTool::get_instance()->debug($res, 'redis');
        return $res;
    }

    public function get_array($key)
    {
        $res = $this->get($key);
        DebugTool::get_instance()->debug($res, 'redis');
        return unserialize($res);
    }

    public function set($key, $value, $expire = 0)
    {
        $res = $this->get_master_redis()->set($key, $value, $expire);
        DebugTool::get_instance()->debug($res, 'redis');
        return $res;
    }

    public function get($key)
    {
        $res = $this->get_slave_redis()->get($key);
        DebugTool::get_instance()->debug($res, 'redis');
        return $res;
    }

    public function delete($key)
    {
        return $this->get_master_redis()->delete($key);
    }

    private function get_redis_config($type = "master")
    {
        $redis_config = ConfigTool::get_instance()->get_config("redis", "common");
        return $redis_config[$type];
    }

    /**
     * 获取所有指定键的值。如果一个或多个键不存在，数组将在键的位置包含false。
     * @param $key_array array('key1', 'key2', 'key3')
     * @return array
     */
    public function getMultiple($key_array)
    {
        return $this->get_slave_redis()->getMultiple($key_array);
    }

    /**
     * @param $key
     * @param int $ttl The key's remaining Time To Live, in seconds.
     */
    public function expire($key, $ttl = 60)
    {
        $res = $this->get_master_redis()->expire($key, $ttl);
        DebugTool::get_instance()->debug($res,"redis expire");
    }

    /**
     * Increment the number stored at key by one.
     * @param $key
     * @return int
     */
    public function incr($key)
    {
        $res = $this->get_master_redis()->incr($key);
        DebugTool::get_instance()->debug($res,"redis incr");
        return $res;
    }

    /**
     * Decrement the number stored at key by one.
     * @param $key
     * @return int
     */
    public function decr($key)
    {
        $res = $this->get_master_redis()->decr($key);
        DebugTool::get_instance()->debug($res,"redis decr");
        return $res;
    }

    /**
     * Increment the number stored at key by one. If the second argument is filled, it will be used as the integer
     * value of the increment.
     * @param $key
     * @param $value
     * @return int
     */
    public function incrby($key, $value)
    {
        $res = $this->get_master_redis()->incrby($key, $value);
        DebugTool::get_instance()->debug($res,"redis incrby");
        return $res;
    }

    /**
     * Adds a values to the set value stored at key.
     * If this value is already in the set, FALSE is returned.
     * @param $key
     * @param $value
     * @return int
     */
    public function sAdd($key, $value)
    {
        $res = $this->get_master_redis()->sAdd($key, $value);
        DebugTool::get_instance()->debug($res,"redis sAdd");
        return $res;
    }

    /** Returns the cardinality of the set identified by key.
     * 返回集合数
     * @param $key
     * @return int
     */
    public function sCard($key)
    {
        $res = $this->get_master_redis()->sCard($key);
        DebugTool::get_instance()->debug($res,"redis sCard");
        return $res;
    }

    /**
     * Removes and returns a random element from the set value at Key.
     * @param $key
     * @return string
     */
    public function sPop($key)
    {
        $res = $this->get_master_redis()->sPop($key);
        DebugTool::get_instance()->debug($res,"redis sPop");
        return $res;
    }

    /**
     * Returns a random element(s) from the set value at Key, without removing it.
     * @param $key
     * @param $count
     * @return array|string
     */
    public function sRandMember($key, $count)
    {
        $res = $this->get_master_redis()->sRandMember($key, $count);
        DebugTool::get_instance()->debug($res,"redis sRandMember");
        return $res;
    }

    /**
     * Decrement the number stored at key by one. If the second argument is filled, it will be used as the integer
     * value of the decrement.
     * @param $key
     * @param int $value
     * @return int
     */
    public function decrby($key, $value = 1)
    {
        $res = $this->get_master_redis()->decrby($key, $value);
        DebugTool::get_instance()->debug($res,"redis decrby");
        return $res;
    }

    /**
     * Adds the string values to the head (left) of the list. Creates the list if the key didn't exist.
     * If the key exists and is not a list, FALSE is returned.
     * @param $key
     * @param $value
     * @return int
     */
    public function lPush($key, $value)
    {
        $res = $this->get_master_redis()->lPush($key, $value);
        DebugTool::get_instance()->debug($res,"redis lPush");
        return $res;
    }

    /**
     * Adds the string values to the tail (right) of the list. Creates the list if the key didn't exist.
     * If the key exists and is not a list, FALSE is returned.
     * @param $key
     * @param $value
     * @return int
     */
    public function rPush($key, $value)
    {
        $res = $this->get_master_redis()->rPush($key, $value);
        DebugTool::get_instance()->debug($res,"redis rPush");
        return $res;
    }

    /**
     * Returns and removes the first element of the list.
     * @param $key
     * @return string
     */
    public function lPop($key)
    {
        $res = $this->get_master_redis()->lPop($key);
        DebugTool::get_instance()->debug($res,"redis lPop");
        return $res;
    }

    /**
     * Returns and removes the last element of the list.
     * @param $key
     * @return string
     */
    public function rPop($key)
    {
        $res = $this->get_master_redis()->rPop($key);
        DebugTool::get_instance()->debug($res,"redis rPop");
        return $res;
    }

    /**
     * Increments the score of a member from a sorted set by a given amount.
     * @param $key
     * @param $value
     * @param $member
     * @return float
     */
    public function zIncrBy($key, $value, $member)
    {
        $res = $this->get_master_redis()->zIncrBy($key, $value, $member);
        DebugTool::get_instance()->debug($res,"redis zIncrBy");
        return $res;
    }

    /**
     * Increments the value of a member from a hash by a given amount.
     * @param $key
     * @param $value
     * @param $member
     * @return int
     */
    public function hIncrBy($key, $value, $member)
    {
        $res = $this->get_master_redis()->hIncrBy($key, $value, $member);
        DebugTool::get_instance()->debug($res,"redis hIncrBy");
        return $res;
    }

    /**
     * Gets a value from the hash stored at key.
     * If the hash table doesn't exist, or the key doesn't exist, FALSE is returned.
     * @param $key
     * @param $hashKey
     * @return string
     */
    public function hGet($key, $hashKey)
    {
        $res = $this->get_slave_redis()->hGet($key, $hashKey);
        DebugTool::get_instance()->debug($res,"redis hGet");
        return $res;
    }

    /**
     * Returns a range of elements from the ordered set stored at the specified key,
     * with values in the range [start, end]. start and stop are interpreted as zero-based indices:
     * 0 the first element,
     * 1 the second ...
     * -1 the last element,
     * -2 the penultimate ...
     * @param $key
     * @param $start
     * @param $end
     * @param $withscores
     * @return array
     */
    public function zRange($key, $start, $end, $withscores) {
        return $this->get_slave_redis()->zRange($key,$start,$end,$withscores);
    }

    /**
     * Returns the score of a given member in the specified sorted set.
     * @param $key
     * @param $member
     * @return float
     */
    public function zScore($key,$member) {
        return $this->get_slave_redis()->zScore($key,$member);
    }

    /**
     * Returns the specified elements of the list stored at the specified key in
     * the range [start, end]. start and stop are interpretated as indices: 0 the first element,
     * 1 the second ... -1 the last element, -2 the penultimate ...
     * @param string $key
     * @param int $start
     * @param int $end
     * @return Array
     */
    public function lRange($key,$start,$end) {
        return $this->get_slave_redis()->lRange($key,$start,$end);
    }

    /**
     * Publish messages to channels. Warning: this function will probably change in the future.
     * @param $channel
     * @param $message
     * @return int
     */
    public function publish($channel,$message) {
        return $this->get_master_redis()->publish($channel,$message);
    }
}