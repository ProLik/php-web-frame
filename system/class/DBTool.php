<?php
/**
 * Created by PhpStorm.
 * User: LIKANG
 * Date: 2017/12/8
 * Time: 16:00
 */

class DBTool
{
    public $pdo_list;
    private $memcache = null;
    private $redis = null;
    private $mongo_client_list = array();

    public static $instance;

    public function __construct()
    {
        //设置用户自定义的错误处理函数
        set_error_handler("cf_error_handler");
        cf_require_class("ConfigTool");
        cf_require_class("DebugTool");
        DebugTool::get_instance()->debug("DbTool loaded");
    }

    public static function get_instance(){
        if(!self::$instance){
            self::$instance = new DBTool();
        }
        return self::$instance;
    }

    public function get_pdo($db_config)
    {
        $key = md5($db_config["db"] . $db_config['host'] . $db_config['port'] . $db_config['user']);

        if($this->pdo_list[$key]){
            return $this->pdo_list[$key];
        }

        $db_string = 'mysql:host=' . $db_config['host'] . ';port=' . $db_config['port'] . ';dbname=' . $db_config['db'];
        DebugTool::get_instance()->debug($db_string);
        try {
            $pdo  =  new PDO($db_string, $db_config['user'], $db_config['pass']);
        } catch  (PDOException $e) {
            print "Error!: " . $e->getMessage() . "<br/>";
            die();
        }
        $pdo->exec("SET CHARACTER SET utf8");
        $this->pdo_list[$key] = $pdo;
        return $pdo;
    }


    public function get_memcache()
    {
        cf_require_class("MyMemcache");
        if($this->memcache){
            return $this->memcache;
        }

        $this->memcache = new MyMemcache();
        $domain = ConfigTool::get_instance()->get_config("memcache");

        $domains = explode(":", $domain);
        $this->memcache->addServer($domains[0], intval($domain[1]));

    }



}