<?php
/**
 * Created by PhpStorm.
 * User: lanse
 * Date: 17-3-7
 * Time: 下午2:22
 * 模板引擎的封装
 */

namespace Root\Library\Util;

class ViewTemplateUtil
{
    protected $channel;
    //设置频道
    public function setChannel($channel) {
        $this->channel = $channel;
        return $this;
    }
    public function getView(){
        $viewPath = [APP.'/'.$this->channel.'/view'];
        $cachePath = APP.'/'.$this->channel.'/cache';
        $compiler = new \Xiaoler\Blade\Compilers\BladeCompiler($cachePath);
        $engine = new \Xiaoler\Blade\Engines\CompilerEngine($compiler);
        $finder = new \Xiaoler\Blade\FileViewFinder($viewPath);
        $factory = new \Xiaoler\Blade\Factory($engine,$finder);
        return $factory;
    }
}