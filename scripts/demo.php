<?php
/**
 * Created by PhpStorm.
 * User: lanse
 * Date: 17-2-21
 * Time: 下午3:58
 */
require_once dirname(__DIR__) . '/bootstrap.php';

use \ROOT\Library\Model\TypicmsMenusModel;

$list = TypicmsMenusModel::getInstance()->getList('*','1=1');
var_dump($list);die;