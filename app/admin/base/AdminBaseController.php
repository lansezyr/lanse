<?php

/**
 * Created by PhpStorm.
 * User: lanse
 * Date: 17-2-22
 * Time: 下午2:25
 */
namespace Root\App\Admin\Base;

use \Root\App\Assists\BaseController;

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
        return self::renderTpl('admin',$file, $params);
    }
}