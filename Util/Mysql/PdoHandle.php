<?php
/**
 * PdoHandle.php
 *
 * User: lanse
 * Date: 16-09-09 上午11:12
 */

namespace ROOT\Util\Mysql;

use PDO;
use Exception;
use Pub\Log\Logger;

class PdoHandle
{
    private static $_instance = [];

    public $handler = null;

    public $handler_key = '';

    public $logger = null;

    /**
     * PdoHandle constructor.
     * @param array $config
     */
    public function __construct($config = [])
    {
        $this->logger = new Logger('/data/service_logs/services/', 'mysql');

        for ($i = 1; $i < 3; $i++) {
            try {
                $dsn = "mysql:host=" . $config['host'] . ";port=" . $config['port'];
                $dsn .= ";dbname=" . $config['database'] . ";charset=utf8";

                $this->handler = new Pdo($dsn, $config['username'], $config['password']);

                break;
            } catch (Exception $e) {
                $tmp = $config;
                unset($tmp['password']);

                $message = "PdoManager:数据库链接失败，请检查数据库配置 配置：（" . json_encode($tmp) . "）";
                $this->throwException($e, $message);
            }
        }
    }

    /**
     * @param array $config
     * @return mixed
     */
    public static function getInstance($config = [])
    {
        ksort($config);

        $key = md5(serialize($config));

        if (isset(self::$_instance[$key]) && self::$_instance[$key] instanceof self) {
            return self::$_instance[$key];
        }

        self::$_instance[$key] = new self($config);

        self::$_instance[$key]->handler_key = $key;

        return self::$_instance[$key];
    }

    /**
     * @param string $key
     */
    public static function removeHandle($key = '')
    {
        if (!empty($key)) {
            unset(self::$_instance[$key]);
        }
    }

    /**
     * @param null $exception
     * @param $msg
     */
    public function throwException($exception = null, $msg = '')
    {
        $this->logger->logError($msg . "\n" . $exception->getMessage());
    }
}