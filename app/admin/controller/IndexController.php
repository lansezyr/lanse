<?php
/**
 * Created by PhpStorm.
 * User: lanse
 * Date: 17-2-21
 * Time: 下午7:18
 */
namespace Root\App\Admin\Controller;

use \Root\App\Admin\Base\AdminBaseController;

class IndexController extends AdminBaseController {
    public function init() {
        parent::init();
    }

    public function defaultAction() {
        $this->render('index.default', ['a' => 'hello world!']);
    }
}
