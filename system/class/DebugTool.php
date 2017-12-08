<?php
/**
 * Created by PhpStorm.
 * User: LIKANG
 * Date: 2017/12/8
 * Time: 16:03
 */

class DebugTool
{
    private $debug_list = array();
    public $debug = true;

    public static $instance;

    public static function get_instance(){
        if(!self::$instance){
            self::$instance = new DebugTool();

        }
        return self::$instance;
    }

    public function debug($error_str,$title='system') {
        if($this->debug) {
            $this->debug_list[] = array($error_str, $title);
        }
    }

    public function show_debug_message()
    {
        $html = '<table border=1 style="font-size:12px; margin-top:30px; width:800px; margin-left:30px;">';
        $html .= '<tr><td style="border:1px solid black" colspan=2>DPS Debug Message:</td></tr>';
        foreach ($this->debug_list as $debug){
            $str = $debug[0];
            if(is_array($str)||is_object($str)) {
                $str = '<pre>'.print_r($str,TRUE).'</pre>';
            }
            $title = $debug[1];
            $html .= '<tr><td style="border:1px solid black;padding:0 5px 0 5px;">'.$title.'</td><td style="border:1px solid black"  >'.$str.'</td></tr>';
        }
        $html .= "</table>";
        echo $html;
    }


}