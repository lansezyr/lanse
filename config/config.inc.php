<?php
/**
 * Created by PhpStorm.
 * User: lanse
 * Date: 17-2-22
 * Time: 下午1:42
 */
class AdminPageConfig {
    // 已注册的频道
    public static function getChannels() {
        return array(
            'admin' => array(
                'code' => 'admin',
                'text' => 'lanse',
                'dir' => APP.'/admin'
            )
        );
    }
}