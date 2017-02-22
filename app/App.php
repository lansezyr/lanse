<?php
/**
 * Created by PhpStorm.
 * User: lanse
 * Date: 17-2-21
 * Time: 下午7:00
 */
namespace ROOT\App;

class App
{
    protected static $uriPath = '';

    public static function init()
    {
    }
    public static function run()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri    = $_SERVER['REQUEST_URI'];
        $path   = self::$uriPath = parse_url($uri, PHP_URL_PATH);
        $rules  = self::getConfig('routes');
        $dispatcher = \FastRoute\simpleDispatcher(function(\fastRoute\RouteCollector $r) use ($rules) {
            foreach ($rules as $rule) {
                $r->addRoute($rule[0], $rule[1], $rule[2]);
            }
        });
        self::checkPath($path);

        $routeInfo = $dispatcher->dispatch($method, $path);
        switch ($routeInfo[0]) {
            case \FastRoute\Dispatcher::NOT_FOUND:
                self::handleNotFound();
                break;
            case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                header('405 Method Not Allowed');
                break;
            case \FastRoute\Dispatcher::FOUND:

                $handler = $routeInfo[1];
                $vars    = $routeInfo[2];
                if (!self::handle($handler, $vars)) {
                    self::handleNotFound();
                }
                break;
        }

    }

    public static function getConfig($filename){
        return require dirname(__DIR__) . '/config/' . $filename . '.php';
    }


    //路由解析回调
    protected static function handle($handler, $args = array()) {
        $_GET           = array_merge($args, $_GET);
        $_REQUEST       = array_merge($args, $_REQUEST);
        if ($handler instanceof clousure || is_callable($handler)) {
            call_user_func($handler, $args);
            return true;
        } elseif (is_string($handler) && class_exists($handler)) {
            $controller = new $handler;
            $controller->run($args);
            return true;
        } else {
            return false;
        }
    }
    //path默认值添加
    protected static function checkPath(&$path){
        if($path == '/' || !$path){
            $path = '/admin/index/default/';
            return;
        }
        $pathArray = explode('/', $path);
        if($pathArray[0] == ''){
            array_shift($pathArray);
        }
        if(end($pathArray) == ''){
            array_pop($pathArray);
        }
        $countPath = count($pathArray);
        switch($countPath){
            case 3:
                $path = '/' . join('/', $pathArray) . '/';
                break;
            case 2:
                $path = '/' . join('/', $pathArray) . '/default' . '/';
                break;
            case 1:
                $path = '/' . join('/', $pathArray) . '/index/default' . '/';
                break;
            default:
                break;
        }
        return;
    }
    //路由解析失败
    protected static function handleNotFound() {
        $requestExt = pathinfo(self::$uriPath, PATHINFO_EXTENSION);
        if(empty($requestExt) && substr(self::$uriPath, -1) != '/'){
            self::$uriPath .= '/';
            $protocol       =  'http';
            if (!empty ($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on"){
                $protocol  .= 's';
            }
            $protocol .= '://';
            $url       = $protocol . $_SERVER['HTTP_HOST'] . self::$uriPath;
            if(!empty($_SERVER['QUERY_STRING'])){
                $url  .= '?' . $_SERVER['QUERY_STRING'];
            }
            \ROOT\Library\Util\HttpRequestUtil::redirect($url);
        }
    }


}