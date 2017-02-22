<?php
/**
 * Created by PhpStorm.
 * User: lanse
 * Date: 17-2-21
 * Time: 下午7:18
 */
require_once APP . '/admin/include/AdminBaseController.php';

class IndexController extends AdminBaseController {
    public function init() {
        parent::init();
    }

    public function defaultAction() {
        $this->render(['a' => 'hello world!'], 'index.default');
    }
}
