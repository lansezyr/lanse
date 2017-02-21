<?php
/**
 * Created by PhpStorm.
 * User: lanse
 * Date: 17-2-21
 * Time: 下午3:58
 */
require_once dirname(__DIR__) . '/bootstrap.php';

use \ROOT\Library\Model\TypicmsMenusModel;
use \ROOT\Library\Util\PinyinUtil;
use \ROOT\Library\Util\Client\CommClient;

$appInfo = [];
$sendParam = [];
$client = new CommClient($appInfo['key'], $appInfo['secret'],[
    'base_uri' => 'http://call.guazi.com/internal/',
    'timeout'  => 5.0,
    'verify'   => false
]);
$returnData = $client->get('call_sound_record/find', $sendParam);

$list = TypicmsMenusModel::getInstance()->getList('*','1=1');
var_dump($list);
$pinyin = PinyinUtil::chinese2pinyin('木蝴蝶', 0, mb_strlen('木蝴蝶', 'UTF-8'), 'utf8', false);
echo $pinyin;die;

