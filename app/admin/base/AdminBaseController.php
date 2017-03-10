<?php

/**
 * Created by PhpStorm.
 * User: lanse
 * Date: 17-2-22
 * Time: 下午2:25
 */
namespace Root\App\Admin\Base;

use \Root\App\Assists\BaseController;
use \Root\Library\Util\HttpRequestUtil;

class AdminBaseController extends BaseController
{
    public function init() {

    }

    /**
     * 模板渲染
     * @param $file
     * @param $params
     * @return mixed
     */
    protected function render($file, $params)
    {
        //引入菜单
        $menu = service('menu');
        if(!isset($params['menuBar'])) {
            $params['menuBar'] = $menu;
        }
        //获取当前页面
        $currentUri = HttpRequestUtil::getCurrentUri();
        if(!isset($params['currentUri'])) {
            $params['currentUri'] = $currentUri;
        }

        return self::renderTpl('admin',$file, $params);
    }
}