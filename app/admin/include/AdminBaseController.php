<?php

/**
 * Created by PhpStorm.
 * User: lanse
 * Date: 17-2-22
 * Time: 下午2:25
 */
require_once APP . '/assists/BaseController.php';

class AdminBaseController extends BaseController
{
    public function init() {

    }

    /**
     * @param string $data
     * @param string $tpl
     */
    protected function render($data, $tpl = '')
    {
        echo View::getView('admin')->make($tpl, $data)->render();
        exit;
    }
}