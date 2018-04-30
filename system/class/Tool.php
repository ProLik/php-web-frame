<?php

/**
 * Created by PhpStorm.
 * User: pc
 * Date: 2018/4/30
 * Time: 10:37
 */
class Tool
{


    /**
     * 格式化日期
    * @param $time
     * @return bool|string
     * */
    public static function format_date($time) {
        $date = date("Ymd", $time);
        $cur = date("Ymd", time());
        if ($date == $cur) {
            return date('今天H:i', $time);
        } else if ($date == ($cur - 1)) {
            return date('昨天H:i', $time);
        } else {
            return date('Y-m-d H:i:s', $time);
        }
    }

    /**
     * 支持富文本的过滤防xss函数
     * @param $html_string
     * @return mixed
     * */
    public static function no_xss($html_string) {
        //第一步过滤script标签
        $reg1 = "/<\/*script[^>]*>/i";
        //忽略大小写
        while (preg_match($reg1, $html_string)) {
            $html_string = preg_replace($reg1, '', $html_string);
        }
        //第二步过滤 expression
        $reg2 = "/expression/i";
        while (preg_match($reg2, $html_string)) {
            $html_string = preg_replace($reg2, '', $html_string);
        }
        //第三步 过滤属性 onerror onload
        $reg3 = "/ (on[a-zA-Z]+)/i";
        while (preg_match($reg3, $html_string)) {
            $html_string = preg_replace($reg3, ' ', $html_string);
        }
        //第四步 过滤协议类型 src=jav scr="" href="" href=
        $reg4 = "/(href|src) *\= *('|\"){0,1}([^>^ ^\"^']+)('|\"){0,1}/i";
        preg_match_all($reg4, $html_string, $result);
        $url_list = $result[3];
        foreach ($url_list as $v) {
            if (!preg_match("/^http\:\/\//i", $v)) {
                $html_string = str_replace($v, '', $html_string);
            }
        }
        //第五步 过滤 link标签
        $reg5 = "/<\/*link[^>]*>/i";
        //忽略大小写
        while (preg_match($reg5, $html_string)) {
            $html_string = preg_replace($reg5, '', $html_string);
        }
        //第六步 过滤iframe 标签
        $reg6 = "/<\/*iframe[^>]*>/i";
        //忽略大小写
        while (preg_match($reg6, $html_string)) {
            $html_string = preg_replace($reg6, '', $html_string);
        }
        return $html_string;
    }


    public static function post_str($url, $str, $header = array()) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $str);
        if($header){
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }
        if(DIRECTORY_SEPARATOR=='\\'){
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        }
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/36.0.1985.143');
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }

    public static function get($url,$header=array()) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, false);
        if($header){
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/36.0.1985.143');
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }

    public static function post($url, $datas,$time_out=-1,$connect_out=-1) {
        $c = http_build_query($datas);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $c);
        if($time_out>-1) {
            curl_setopt($ch, CURLOPT_TIMEOUT,$time_out);
        }
        if($connect_out>-1) {
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,$connect_out);
        }
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/36.0.1985.143');
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }

    public static function post_file($image_path, $url) {
        $ch = curl_init();
        $data = array('file' => new CURLFile($image_path));
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $result = curl_exec($ch);
        curl_close($ch);
        return json_decode($result, true);
    }
}