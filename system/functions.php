<?php
/*引入composer 依赖管理工具*/
require_once ROOT_PATH . "vendor/autoload.php";

//todo all
/**
 * 注册给定的函数作为 __autoload 的实现
 */
spl_autoload_register(function ($class_name){

    if(substr($class_name, -10, strlen($class_name)) == "Controller"){
        if($class_name == "Controller"){
            cf_require_controller($class_name);
        }else{
            //如果提供了负数的 length，那么 string 末尾处的 length 个字符将会被省略（若 start 是负数则从字符串尾部算起）。
            cf_require_controller(substr($class_name, 0, -10));
        }
    }else if(substr($class_name, -4,strlen($class_name)) == "View"){
        if($class_name == "View"){
            cf_require_view($class_name);
        }else{
            cf_require_view(substr($class_name, 0,-4));
        }
    }else if(substr($class_name,-6,strlen($class_name))=='Plugin'){
        if($class_name == "Plugin"){
            cf_require_plugin($class_name);
        }else{
            cf_require_plugin(substr($class_name, 0,-4));
        }
    }else{
        cf_require_class($class_name);
    }



});

function cf_require_controller($class_name){
    cf_require($class_name, 'controller');
}

function cf_require_view($class_name){
    cf_require($class_name, "view");
}

function cf_require_plugin($class_name){
    cf_require($class_name, "plugin");
}


function cf_require_class($class_name) {
    cf_require($class_name);
}

function cf_require_template($class_name) {
    cf_require($class_name,'view','phtml');
}

function cf_require_interceptor($class_name){
    cf_require($class_name,'interceptor');
}

/**
 * 先在system 后再当前目录，再根据 $INCLUDE_PATH 目录下查找
 * 默认到class 目录下寻找,
 * @param $class_name
 * @param string $type
 * @param string $extend
 */
function cf_require($class_name, $type = "", $extend = "php"){
    global $INCLUDE_PATH;

    $folder_type = $type == "" ? "class" : $type;

    $build_path = cf_build_path($class_name, $folder_type);

    $rel_path  = SYS_PATH . $build_path . "." . $extend;

    $class_true_name = get_real_class_name($class_name, $type);

    if (file_exists($rel_path)) {
        if (!class_exists($class_true_name)) {
            require_once $rel_path;
        }
        return;
    }


    foreach ($INCLUDE_PATH as $v){
        $rel_path = $v . $build_path . "." . $extend;
        if(file_exists($rel_path)){

            if(!class_exists($class_true_name)){
                require_once $rel_path;
            }
            return;
        }
    }



}

/**
 * @param $class_name
 * @param $type
 * @return string
 * Shuidi_Archives_DetailShelfController
 * Shuidi_Archives_DetailShelf
 */
function cf_build_path($class_name, $type){
    if(strpos($class_name, "\\") !== false){
        $arr = explode("\\", $class_name);
    }else{
        $arr = explode("_", $class_name);
    }
    // 弹出数组最后一个单元（出栈）
    $name = array_pop($arr);
    $path = "";
    foreach ($arr as $v){
        $path .= "/".strtolower($v);
    }
    return "/". $type . $path . "/" . $name;

}

/**
 * 获取类名
 * eg. Shuidi_Archives_DetailShelf controller ==> Shuidi_Archives_DetailShelfController
 * eg. ShuidiRequest
 * @param $class_name
 * @param $type
 * @return string
 */
function get_real_class_name($class_name, $type){
    //将字符串转化为小写
    if(strtolower($class_name) == $type){
        return "";
    }

    if($type){
        $class_true_name = $class_name.strtoupper(substr($type, 0 ,1)) . substr($type, 1);
    }else{
        $class_true_name = $class_name;
    }
    return $class_true_name;
}


function cf_error_handler($error_no, $error_info){
    if($error_no!=8) {
        //debug_print_backtrace();
        echo $error_info;
    }
}


?>