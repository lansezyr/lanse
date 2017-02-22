<?php
/**
 * Created by PhpStorm.
 * User: lanse
 * Date: 17-2-21
 * Time: 下午1:20
 */
$rootDir = dirname(__DIR__);
$configDir = dirname(__FILE__);
$pluginDir = $rootDir . '/library/plugins';

if(!defined('ROOT')){
    define('ROOT',            $rootDir);
}

if(!defined('PLUGINS')){
    define('PLUGINS',         $pluginDir);
}

if(!defined('PHPEXCEL')){
    define('PHPEXCEL',        $pluginDir . '/phpexcel');
}

if(!defined('API')){
    define('API',            $rootDir . '/library/Api');
}

if(!defined('MODEL')){
    define('MODEL',            $rootDir . '/library/Model');
}

if(!defined('UTIL')){
    define('UTIL',            $rootDir . '/library/Util');
}

if(!defined('CONF')){
    define('CONF',            $configDir);
}

if(!defined('APP')) {
    define('APP',            $rootDir . '/app');
}


