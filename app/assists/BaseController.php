<?php
/**
 * Created by PhpStorm.
 * User: lanse
 * Date: 17-2-21
 * Time: 下午7:22
 */

namespace Root\App\Assists;

class BaseController
{
    protected $request;
    protected $response;
    protected $args;
    protected $userInfo;

    protected $admins = [];
    protected $loginPinYinName = '';

    public function __construct()
    {
    }

    public function init() {

    }

    public function run($req, $res, $args)
    {
        $this->request = $req;
        $this->response = $res;
        $this->args = $args;

        $action = (isset($_GET['act']) && $_GET['act']) ? trim($_GET['act']) : '';
        if ($action && preg_match('/^\w+$/', $action) && ($action = strtolower($action) . 'Action') && method_exists($this, $action)) {
            return $this->$action();
        } else {
            $this->init();
            return $this->defaultAction();
        }
    }

    public function defaultAction()
    {
        return $this->response->write('hello world');
    }

    /**
     * 模板渲染
     * @param $channel
     * @param $file
     * @param $params
     * @return mixed
     */
    protected function renderTpl($channel, $file, $params)
    {
        $fetch = service('template')->setChannel($channel)->getView()->make($file, $params)->render();
        $this->response->write($fetch);
        return $this->response;
    }

    /**
     * @param $data array / string
     * @return mixed
     */
    protected function renderJson($data)
    {
        $this->response = $this->response->withHeader('Content-type', 'application/json');
        $this->response->write(json_encode($data));
        return $this->response;
    }
    /**
     * @return bool
     */
    protected function isAjax()
    {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 'xmlhttprequest' == strtolower($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            return true;
        }
        return false;
    }
}