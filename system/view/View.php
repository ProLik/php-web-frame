<?php
/**
 * Created by PhpStorm.
 * User: LIKANG
 * Date: 2017/12/22
 * Time: 18:00
 */

abstract class View
{
    public function get_title()
    {
        $dps = DPS::get_instance();
        return $dps->get_config("name") . $dps->get_config("version");
    }
}