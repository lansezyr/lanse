<?php

/**
 * Created by PhpStorm.
 * User: lanse
 * Date: 17-2-22
 * Time: 下午2:25
 */
namespace Root\App\Admin\Base;

class AdminBaseController extends \Root\App\Assists\BaseController
{
    public function init() {

    }

    /**
     * @param string $data
     * @param string $tpl
     */
    protected function render($data, $tpl = '')
    {
        echo service('template')->setChannel('admin')->getView()->make($tpl, $data)->render();
        exit;
    }
}