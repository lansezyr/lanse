<?php
/**
 * Created by PhpStorm.
 * User: lanse
 * Date: 17-3-7
 * Time: 下午2:01
 */

namespace ROOT\App;

class Start
{
    //后台 首页
    const DEFAULT_CONTROLLER = 'Root\\App\\Admin\\Controller\\IndexController';

    private static $slimApp;

    protected static $basePath;

    /**
     * @param $basePath
     */
    public static function init($basePath)
    {
        self::$basePath = $basePath;

        // 注册服务
        $container = new \Slim\Container(getConfig('service'));
        $app = new \Slim\App($container);
        self::$slimApp = $app;

        //调试
        if ($container['settings']['displayErrorDetails']) {
            ini_set('display_errors', 1);
            //error_reporting(E_ALL ^ E_DEPRECATED ^ E_STRICT ^ E_NOTICE ^ E_WARNING);
            error_reporting(E_ALL);
        }

        //session对应的cookie域名
        ini_set('session.cookie_domain', $container['settings']['cookie_domain']);

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

    }

    public static function serve()
    {
        $routes = service('routes');
        foreach ($routes as $route) {
            $fastRoute = self::getSlim()->map($route[0], $route[1], $route[2]);
            //middleware
            if ($route[3] && is_callable($route[3])) {
                $fastRoute->add($route[3]);
            }
        }

        self::getSlim()->run();
    }

    /**
     * @param $req
     * @param $res
     * @param $args
     * @return mixed
     */
    public static function handlePath($req, $res, $args)
    {
        $path = $args['path_'];
        unset($args['path_']);
        $class = self::pathToClass($path);
        if (!class_exists($class)) {
            return self::handleNotFound($req, $res, $args);
        }
        $oClass = new $class;

        return $oClass->defaultAction($req, $res, $args);
    }

    /**
     * @param $req
     * @param $res
     * @param $args
     * @return mixed
     */
    protected static function handleNotFound($req, $res, $args)
    {
        return $res->write('Page not found.');
    }

    /**
     * get slim app
     * @return mixed
     */
    public static function getSlim()
    {
        return self::$slimApp;
    }

    /**
     * @return mixed
     */
    public static function getBasePath()
    {
        return self::$basePath;
    }

    /**
     * Transform path to class("abc/def_gh" => "\coffee\api\Abc\DefGh")
     * @param  string $path url path
     * @return string       class
     */
    protected static function pathToClass($path)
    {
        if (empty($path)) {
            return self::DEFAULT_CONTROLLER;
        }
        $path = trim($path, '/');
        $path = explode('/', $path);
        foreach ($path as $key => $value) {
            $value = explode('_', $value);
            $path[$key] = implode('', array_map('ucfirst', $value));
        }

        $class = implode('\\', $path);
        $class = '\\App\\Controller\\' . $class . 'Controller';

        return $class;
    }

    /**
     * @param $filename
     * @return mixed
     */
    public static function getConfig($filename)
    {
        return require self::getBasePath() . '/config/' . $filename . '.php';
    }

}