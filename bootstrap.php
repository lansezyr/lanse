<?php
/**
 * Created by PhpStorm.
 * User: lanse
 * Date: 17-2-21
 * Time: 下午3:17
 */

ini_set('display_errors', 1);
error_reporting(E_ALL ^ E_DEPRECATED ^ E_STRICT ^ E_NOTICE ^ E_WARNING);
//自动引入
require_once dirname(__FILE__) .'/vendor/autoload.php';

//常量定义
require_once dirname(__FILE__) .'/config/defines.php';

// 路由配置
require_once dirname(__FILE__) .'/config/routes.php';
//注册频道
require_once dirname(__FILE__) .'/config/config.inc.php';

//引入快捷方式
require_once dirname(__FILE__) .'/app/shortcut.php';

\Root\App\Start::init(ROOT);