<?php

/**
 * Created by PhpStorm.
 * User: lanse
 * Date: 17-2-21
 * Time: 下午6:53
 * Brief: 模板引擎的封装
 */
class View
{
    public static function getView($channel){
        $viewPath = [APP.'/'.$channel.'/view'];
        $cachePath = APP.'/'.$channel.'/cache';
        $compiler = new \Xiaoler\Blade\Compilers\BladeCompiler($cachePath);
        $engine = new \Xiaoler\Blade\Engines\CompilerEngine($compiler);
        $finder = new \Xiaoler\Blade\FileViewFinder($viewPath);
        $factory = new \Xiaoler\Blade\Factory($engine,$finder);
        return $factory;
    }
}