<?php
/**
 * @brief: 一般请求的主入口
 * @author: lanse
 */
namespace ROOT\App;

use \ROOT\Library\Util\HttpRequestUtil;

class Basic
{
    /*
     *
     *常规类型url 如:/admin/controller/action
     */
    public static function handle()
    {
        $controller       = $url =  isset($_GET['controller']) && !empty($_GET['controller']) ? $_GET['controller'] : 'index';
        $act              = (isset($_GET['act']) && !empty($_GET['act'])) ? trim($_GET['act']) : 'default';
        if ( !preg_match('/^[\w-]+$/', $controller) || !preg_match('/^[\w-]+$/', $act) ) {
            self::notFound();
        }
        /*兼容迁移前的路由规则*/
        $_GET['channel'] = $dir = isset($_GET['dir']) && !empty($_GET['dir']) ? $_GET['dir'] : 'admin';
        $_GET['module'] = $controller;
        $_GET['action'] = $act;

        /*兼容结束*/

        $controller       = self::formatClass($controller) . 'Controller';
        $act              = self::formatAct($act) . 'Action';
        $allChannelList = \AdminPageConfig::getChannels();
        if(!array_key_exists($_GET['channel'],$allChannelList)){
            self::notFound();
        }
        $channelInfo = $allChannelList[$_GET['channel']];
        if(!empty($channelInfo['dir'])){
            $_GET['channel_dir'] = $channelInfo['dir'];
            $rootDir = $channelInfo['dir'].'/controller/';
        }else{
            $_GET['channel_dir'] = BACKEND . '/app/' . $dir;
            $rootDir = BACKEND . '/app/' . $dir . '/controller/';
        }

        //获取当前所在channel和channel_dir
        if(empty($_GET['current_channel'])||!array_key_exists($_GET['current_channel'],$allChannelList)){
            $_GET['current_channel'] = array_keys($allChannelList)[0];
        }
        $currentChannelInfo = $allChannelList[$_GET['current_channel']];
        if(!empty($currentChannelInfo['dir'])){
            $_GET['current_channel_dir'] = $currentChannelInfo['dir'];
        }else{
            $_GET['current_channel_dir'] = APP . '/app/admin/';
        }

        $file             = $rootDir . $controller . '.php';
        if( !is_file($file) ){
            //默认控制器
            $controller   = 'IndexPage';
            $file         = $rootDir . $controller . '.php';
            $_GET['url']  = $_REQUEST['url'] = $url;
        }
        if(!file_exists($file)){
            self::notFound();
        }
        require $file ;
        $oController = new $controller();
        $oController->init();
        if(method_exists($oController, $act)){
            call_user_func(array($oController, $act));
        }
    }



    //ab_cd_ef => abCdEf
    protected static function formatAct($str){
        if(empty($str)){
            return '';
        }
        if(false !== strpos($str, '_')){
            $str = array_reduce(explode('_', $str), function($v1, $v2){
                $v1 .= ucfirst($v2);
                return $v1;
            });
        }
        return lcfirst($str) ;
    }
    //ab_cd_ef => AbCdEf
    protected static function formatClass($str){
        return ucfirst(self::formatAct($str));
    }
    //路由解析失败
    protected static function notFound() {
        $protocol       =  'http';
        if (!empty ($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on"){
            $protocol  .= 's';
        }
        $protocol .= '://';
        $url       = $protocol . $_SERVER['HTTP_HOST'];
        if(!empty($_SERVER['QUERY_STRING'])){
            $url  .= '?' . $_SERVER['QUERY_STRING'];
        }
        HttpRequestUtil::redirect($url);
        exit;

    }

}