<?php
/**
 * Created by PhpStorm.
 * User: LIKANG
 * Date: 2017/12/8
 * Time: 16:21
 */

interface ICache
{
    public function set_array($key, $value, $expire = 86400);

    public function get_array($key);

    public function set($key,$value,$expire=86400);

    public function get($key);

    public function delete($key);
}