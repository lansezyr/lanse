<?php

/**
 * Created by PhpStorm.
 * User: lanse
 * Date: 17-3-10
 * Time: 下午2:22
 */
namespace Root\App\Web\Controller;

use \Root\App\Web\Base\WebBaseController;

class IndexController extends WebBaseController
{
    public function init() {
        parent::init();
    }

    public function defaultAction() {
        $this->render('index.default', ['a' => 'hello world!']);
    }
}