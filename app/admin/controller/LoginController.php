<?php
/**
 * Created by PhpStorm.
 * User: lanse
 * Date: 17-3-10
 * Time: 下午5:08
 * 登录页面
 */

namespace Root\App\Admin\Controller;

use \Root\App\Admin\Base\AdminBaseController;
use \Root\Library\Util\HttpRequestUtil;

class LoginController extends AdminBaseController
{
    public function init() {
        parent::init();
    }

    public function defaultAction() {
        $this->render('login.default', ['a' => 'hello world!']);
    }
}