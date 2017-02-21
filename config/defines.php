<?php
/**
 * Created by PhpStorm.
 * User: lanse
 * Date: 17-2-21
 * Time: 下午1:20
 */
$rootDir = dirname(__DIR__);
$pluginDir = $rootDir . '/plugins';

if(!defined('ROOT')){
    define('ROOT',            $rootDir);
}

if(!defined('PLUGINS')){
    define('PLUGINS',            $pluginDir);
}

if(!defined('PHPEXCEL')){
    define('PHPEXCEL',            $pluginDir . '/phpexcel');
}


