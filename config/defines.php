<?php
/**
 * Created by PhpStorm.
 * User: lanse
 * Date: 17-2-21
 * Time: 下午1:20
 */
$pluginDir = dirname(__DIR__);


if(!defined('PLUGINS')){
    define('PLUGINS',            $pluginDir);
}

if(!defined('PHPEXCEL')){
    define('PHPEXCEL',            $pluginDir . '/phpexcel');
}


