<?php
/**
 * Created by PhpStorm.
 * User: LIKANG
 * Date: 2017/12/8
 * Time: 16:27
 */

class ConfigTool
{
    private static $instance;

    public static function get_instance(){
        if(!self::$instance){
            self::$instance = new ConfigTool();
        }
        return self::$instance;
    }

    /**
     * 获取指定的配置文件
     * @param $key
     * @param string $file
     * @return mixed
     */
    public function get_config($key, $file = "common")
    {
        global $CONFIG_PATH;
        foreach ($CONFIG_PATH as $k => $v){
            $config_path = $v . "/" . $file . ".php";
            if(file_exists($config_path)){
                require $config_path;//why not use requireonce
            }
        }
        return $config[$key];
    }
}