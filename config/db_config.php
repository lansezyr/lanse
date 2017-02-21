<?php
/**
 * Created by PhpStorm.
 * User: lanse
 * Date: 17-2-21
 * Time: 下午3:01
 * 数据库相关配置
 */
class DataBaseConfig {
    const COMMON_HOST     = '127.0.0.1'; //本地
    const COMMON_USERNAME = 'root';
    const COMMON_PASSWD   = '123';
    const COMMON_PORT     = '3306';

    public static $SERVER_LOCALHOST_MASTER = array(
        'host'      => self::COMMON_HOST,
        'username'  => self::COMMON_USERNAME,
        'password'  => self::COMMON_PASSWD,
        'port'      => self::COMMON_PORT,
    );

    public static $SERVER_LOCALHOST_SLAVE = array(
        'host'      => self::COMMON_HOST,
        'username'  => self::COMMON_USERNAME,
        'password'  => self::COMMON_PASSWD,
        'port'      => self::COMMON_PORT,
    );

}