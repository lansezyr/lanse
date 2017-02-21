<?PHP
/**
 * @author    lanse
 * 数据库操作的类库
 */

namespace ROOT\Library\Util\Mysql\Mysqli;

if (!defined('SLOW_QUERY_MIN')) {
    define('SLOW_QUERY_MIN', 200);
}
if (!defined('SLOW_QUERY_SAMPLE')) {
    define('SLOW_QUERY_SAMPLE', 10);
}

/**
 * @class: DBConst
 * @PURPOSE:  DBConst 可以认为是一个名字空间， 其中定义了若干数据库相关的常量， 如编码等
 */
class DBConst
{
    // 数据库编码相关
    const ENCODING_GBK = 0; ///< GBK 编码定义
    const ENCODING_UTF8 = 1; ///< UTF8 编码定义
    const ENCODING_LATIN = 2; ///< LATIN1 编码定义
    const ENCODING_UTF8MB4 = 3; ///< UTF8MB4 编码定义, 4字节emoji表情要用,http://punchdrunker.github.io/iOSEmoji/table_html/flower.html
    // 数据库句柄需要ping 重连
    const HANDLE_PING = 100;

    // 数据库句柄不能 重连
    const NOT_HANDLE_PING = 200;

}

/**
 * @class: DBMysql
 * @PURPOSE:  DBMysql 可以认为是一个名字空间， 其中定义了若干操作数据库的静态方法
 */
class DBMysql
{
    /**
     * @biref 数据库句柄是否需要ping
     * @var mix
     */
    private static $_HANDLE_PING = false;

    //handle key 与数据库配置文件映射
    private static $_HANDLE_CONFIG_MAP = [];
    /**
     * 已打开的db handle
     * @var array
     */
    private static $_HANDLE_ARRAY = array();

    private static function _getHandleKey($params)
    {
        ksort($params);
        return md5(implode('_', $params));
    }

    /**
     * @brief 设置 handle ping的属性
     * @param $value
     * @return void
     * @example
     *      如果是crontabe 建议在开始是增加
     *      DBMysql::setHandlePing(DBConst::HANDLE_PING); 句柄失效可以重连
     *      DBMysql::setHandlePing(DBConst::NOT_HANDLE_PING); 句柄失效不能重连
     *      如果关闭可以设置 false, 默认是不开启的
     *      DBMysql::setHandlePing();
     */
    public static function setHandlePing($value = false)
    {
        self::$_HANDLE_PING = $value;
    }

    /**
     * 创建一个面向对象的DBHandle，参数描述同createDBHandle
     * @param $db_config_array
     * @param $db_name
     * @param int $encoding
     * @return bool|DBHandle
     */
    public static function createDBHandle2($db_config_array, $db_name, $encoding = DBConst::ENCODING_UTF8)
    {
        $handle = self::createDBHandle($db_config_array, $db_name, $encoding);
        if ($handle) {
            include_once(dirname(__FILE__) . "/DBHandle.php");
            return new DBHandle($handle);
        }
        return FALSE;
    }

    /// 根据数据库表述的参数获取数据库操作句柄
    /// @param[in] array $db_config_array, 是一个array类型的数据结构，必须有host, username, password 三个熟悉, port为可选属性， 缺省值分别为3306
    /// @param[in] string $db_name, 数据库名称
    /// @param[in] enum $encoding, 从$DBConst中数据库编码相关的常量定义获取, 有缺省值 $DBConst::ENCODING_UTF8
    /// @return 非FALSE表示成功获取hadnle， 否则返回FALSE
    public static function createDBHandle($db_config_array, $db_name, $encoding = DBConst::ENCODING_UTF8)
    {
        $db_config_array['db_name'] = $db_name;
        $db_config_array['encoding'] = $encoding;
        $handle_key = self::_getHandleKey($db_config_array);

        if (isset(self::$_HANDLE_ARRAY[$handle_key]) && self::_checkHandle(self::$_HANDLE_ARRAY[$handle_key], 'mysqlns.createhandle')) {
            return self::$_HANDLE_ARRAY[$handle_key];
        }
        $port = 3306;
        do {
            if (!is_array($db_config_array))
                break;
            if (!is_string($db_name))
                break;
            if (strlen($db_name) == 0)
                break;
            if (!array_key_exists('host', $db_config_array))
                break;
            if (!array_key_exists('username', $db_config_array))
                break;
            if (!array_key_exists('password', $db_config_array))
                break;
            if (array_key_exists('port', $db_config_array)) {
                $port = (int)($db_config_array['port']);
                if (($port < 1024) || ($port > 65535))
                    break;
            }
            $host = $db_config_array['host'];
            if (strlen($host) == 0)
                break;
            $username = $db_config_array['username'];
            if (strlen($username) == 0)
                break;
            $password = $db_config_array['password'];
            if (strlen($password) == 0)
                break;

            //$conn_time = DateTime::getMicrosecond();
            // mysqli_connect(); will also throw a warning on an unsuccessfull connect. To avoid such warnings being shown prefix it with an "@" symbol.
            $handle = @mysqli_connect($host, $username, $password, $db_name, $port);
            // 如果连接失败，再重试2次
            for ($i = 1; ($i < 3) && (FALSE === $handle); $i++) {
                // 重试前需要sleep 50毫秒
                usleep(50000);
                $handle = @mysqli_connect($host, $username, $password, $db_name, $port);
            }
            //$conn_time = DateTime::getMicrosecond() - $conn_time;
            if (FALSE === $handle)
                break;

            $is_encoding_set_success = true;
            switch ($encoding) {
                case DBConst::ENCODING_UTF8 :
                    $is_encoding_set_success = mysqli_set_charset($handle, "utf8");
                    break;
                case DBConst::ENCODING_UTF8MB4:
                    $is_encoding_set_success = mysqli_set_charset($handle, "utf8mb4");
                    if ($is_encoding_set_success === FALSE) {
                        $is_encoding_set_success = mysqli_query($handle, "set names utf8mb4");
                    }
                    if ($is_encoding_set_success === FALSE) {
                        //self::logError(sprintf("Connect Set Charset Failed1:%s, db_config_array=%s", mysqli_error($handle), var_export($logArray, true)), 'mysqlns.connect');
                        $is_encoding_set_success = mysqli_set_charset($handle, "utf8");
                    }
                    break;
                case DBConst::ENCODING_GBK :
                    $is_encoding_set_success = mysqli_set_charset($handle, "gbk");
                    break;
                default:
            }
            if (FALSE === $is_encoding_set_success) {
                //self::logError(sprintf("Connect Set Charset Failed2:%s, db_config_array=%s", mysqli_error($handle), var_export($logArray, true)), 'mysqlns.connect');
                mysqli_close($handle);
                break;
            }
            self::$_HANDLE_ARRAY[$handle_key] = $handle;
            self::$_HANDLE_CONFIG_MAP[$handle_key] = $db_config_array;
            return $handle;
        } while (FALSE);
        // to_do, 连接失败，需要记log
        $password_part = isset($password) ? substr($password, 0, 5) . '...' : '';
        $logArray = $db_config_array;
        $logArray['password'] = $password_part;
     //   //self::logError(sprintf("Connect failed:db_config_array=%s", var_export($logArray, true) . '##param##' . var_export($_REQUEST, true)), 'mysqlns.connect');
        return FALSE;
    }

    /// 释放通过getDBHandle或者getDBHandleByName 返回的句柄资源
    /// @param[in] handle $handle, 你懂的
    /// @return void
    public static function releaseDBHandle($handle)
    {
        if (!self::_checkHandle($handle))
            return;
        foreach (self::$_HANDLE_ARRAY as $handle_key => $handleObj) {
            if ($handleObj->thread_id == $handle->thread_id) {
                unset(self::$_HANDLE_ARRAY[$handle_key]);
            }
        }
        mysqli_close($handle);
    }

    /// 执行sql语句， 该语句必须是insert, update, delete, create table, drop table等更新语句
    /// @param[in] handle $handle, 操作数据库的句柄
    /// @param[in] string $sql, 具体执行的sql语句
    /// @return TRUE:表示成功， FALSE:表示失败
    public static function execute($handle, $sql)
    {
        if (!self::_checkHandle($handle))
            return FALSE;
        $tm = DateTime::getMicrosecond();
        if (self::mysqliQueryApi($handle, $sql)) {
            $tm_used = intval((DateTime::getMicrosecond() - $tm) / 1000);
            if ($tm_used > SLOW_QUERY_MIN && rand(0, SLOW_QUERY_SAMPLE) == 1) {
                //self::logWarn("ms=$tm_used, SQL=$sql", 'mysqlns.slow');
            }
            @self::logFang($sql);
            return TRUE;
        }
        // to_do, execute sql语句失败， 需要记log
     //   //self::logError("SQL Error: $sql, errno=" . self::getLastError($handle), 'mysql');

        return FALSE;
    }

    /// 执行insert sql语句，并获取执行成功后插入记录的id
    /// @param[in] handle $handle, 操作数据库的句柄
    /// @param[in] string $sql, 具体执行的sql语句
    /// @return FALSE表示执行失败， 否则返回insert的ID
    public static function insertAndGetID($handle, $sql)
    {
        if (!self::_checkHandle($handle))
            return false;
        do {
            if (self::mysqliQueryApi($handle, $sql) === FALSE)
                break;
            if (($result = self::mysqliQueryApi($handle, 'select LAST_INSERT_ID() AS LastID')) === FALSE)
                break;
            $row = mysqli_fetch_assoc($result);
            $lastid = $row['LastID'];
            mysqli_free_result($result);
            @self::logFang($sql);
            return $lastid;
        } while (FALSE);
        // to_do, execute sql语句失败， 需要记log
   //     //self::logError("SQL Error: $sql, errno=" . self::getLastError($handle), 'mysql');
        return FALSE;
    }

    /// 将所有结果存入数组返回
    /// @param[in] handle $handle, 操作数据库的句柄
    /// @param[in] string $sql, 具体执行的sql语句
    /// @return FALSE表示执行失败， 否则返回执行的结果, 结果格式为一个数组，数组中每个元素都是mysqli_fetch_assoc的一条结果
    public static function query($handle, $sql)
    {
        if (!self::_checkHandle($handle))
            return FALSE;
        do {
            $tm = DateTime::getMicrosecond();
            if (($result = self::mysqliQueryApi($handle, $sql)) === FALSE) {
                break;
            }
            if ($result === true) {
                if (rand(1, 10) == 1) {
                    //self::logWarn("err.func.query,SQL=$sql", 'mysqlns.query');
                }
                return array();
            }
            $tm_used = intval((DateTime::getMicrosecond() - $tm) / 1000);
            if ($tm_used > SLOW_QUERY_MIN && rand(0, SLOW_QUERY_SAMPLE) == 1) {
                //self::logWarn("ms=$tm_used, SQL=$sql", 'mysqlns.slow');
            }
            $res = array();
            while ($row = mysqli_fetch_assoc($result)) {
                $res[] = $row;
            }
            @self::logFang($sql);
            mysqli_free_result($result);
            return $res;
        } while (FALSE);
        // to_do, execute sql语句失败， 需要记log
    //    //self::logError("SQL Error: $sql, errno=" . self::getLastError($handle), 'mysql');

        return FALSE;
    }

    /// 将查询的第一条结果返回
    /// @param[in] handle $handle, 操作数据库的句柄
    /// @param[in] string $sql, 具体执行的sql语句
    /// @return FALSE表示执行失败， 否则返回执行的结果, 执行结果就是mysqli_fetch_assoc的结果
    public static function queryFirst($handle, $sql)
    {
        if (!self::_checkHandle($handle))
            return FALSE;
        do {
            $tm = DateTime::getMicrosecond();
            if (($result = self::mysqliQueryApi($handle, $sql)) === FALSE)
                break;
            $tm_used = intval((DateTime::getMicrosecond() - $tm) / 1000);
            if ($tm_used > SLOW_QUERY_MIN && rand(0, SLOW_QUERY_SAMPLE) == 1) {
                //self::logWarn("ms=$tm_used, SQL=$sql", 'mysqlns.slow');
            }

            $row = mysqli_fetch_assoc($result);
            mysqli_free_result($result);
            @self::logFang($sql);
            return $row;
        } while (FALSE);
        // to_do, execute sql语句失败， 需要记log
    //    //self::logError("SQL Error: $sql," . self::getLastError($handle), 'mysql');
        return FALSE;
    }

    /**
     * 将所有结果存入数组返回
     * @param Mysqli $handle 句柄
     * @param string $sql 查询语句
     * @return FALSE表示执行失败， 否则返回执行的结果, 结果格式为一个数组，数组中每个元素都是mysqli_fetch_assoc的一条结果
     */
    public static function getAll($handle, $sql)
    {
        return self::query($handle, $sql);
    }

    /**
     * 将查询的第一条结果返回
     * @param[in] Mysqli $handle, 操作数据库的句柄
     * @param[in] string $sql, 具体执行的sql语句
     * @return FALSE表示执行失败， 否则返回执行的结果, 执行结果就是mysqli_fetch_assoc的结果
     */
    public static function getRow($handle, $sql)
    {
        return self::queryFirst($handle, $sql);
    }

    /**
     * @param $handle
     * @param $sql
     * @return array|FALSE表示执行失败|mixed
     */
    public static function getOne($handle, $sql)
    {
        $row = self::getRow($handle, $sql);
        if (is_array($row))
            return current($row);
        return $row;
    }

    /// 得到最近一次操作影响的行数
    /// @param[in] handle $handle, 操作数据库的句柄
    /// @return FALSE表示执行失败， 否则返回影响的行数
    public static function lastAffected($handle)
    {
        if (!is_object($handle))
            return FALSE;
        $affected_rows = mysqli_affected_rows($handle);
        if ($affected_rows < 0)
            return FALSE;
        return $affected_rows;
    }

    /*
     *  返回最后一次查询自动生成并使用的id
     *  @param[in] handle $handle, 操作数据库的句柄
     *  @return FALSE表示执行失败， 否则id
     */
    public static function getLastInsertId($handle)
    {
        if (!is_object($handle)) {
            return false;
        }
        if (($lastInsertId = mysqli_insert_id($handle)) <= 0) {
            return false;
        }
        return $lastInsertId;
    }

    /**
     * @param $inp
     * @return array|mixed
     */
    public static function mysqlEscapeMimic($inp)
    {
        if (is_array($inp)) {
            return array_map(__METHOD__, $inp);
        }
        if (!empty($inp) && is_string($inp)) {
            return str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $inp);
        }
        return $inp;
    }

    /// 得到最近一次操作错误的信息
    /// @param[in] handle $handle, 操作数据库的句柄
    /// @return FALSE表示执行失败， 否则返回 'errorno: errormessage'
    public static function getLastError($handle)
    {
//        if (!self::_checkHandle($handle))
//            return FALSE;
        if (($handle)) {
            return mysqli_errno($handle) . ': ' . mysqli_error($handle);
        }
        return FALSE;
    }

    /**
     * @brief 检查handle
     * @param[in] handle $handle, 操作数据库的句柄
     * @return boolean true|成功, false|失败
     */
    private static function _checkHandle($handle, $log_category = 'mysqlns.handle')
    {
        if (!is_object($handle) || $handle->thread_id < 1) {
            if ($log_category) {
       //         //self::logError(sprintf("handle Error: handle='%s'", var_export($handle, true)), $log_category);
            }
            return false;
        }
        return true;
    }

    /**
     * @param $sql
     */
    public static function logFang($sql)
    {
        return;
    }

    /**
     * @param $sql
     */
    public static function mobDbWriteLog($sql)
    {
        return;
    }

    /**
     * @param $handle
     * @param $sql
     * @return bool|mysqli_result
     */
    public static function mysqliQueryApi($handle, $sql)
    {
        do {
            $result = mysqli_query($handle, $sql);
            if ($result === FALSE) {
                if (!is_object($handle)) return false;

                //强制指定不能重连
                if (self::$_HANDLE_PING === DBConst::NOT_HANDLE_PING) {
                    return false;
                }
                //cli模式 或者 指定了重练
                if (PHP_SAPI === 'cli' || self::$_HANDLE_PING === DBConst::HANDLE_PING) {
                } else {
                    return false;
                }

                if (self::_reconnectHandle($handle)) {
                    $result = mysqli_query($handle, $sql);;
                }
                if ($result === FALSE) {
                    break;
                }
            }
            return $result;
        } while (0);
        return false;
    }

    /**
     * @param $handle
     * @return bool
     */
    private static function _reconnectHandle(&$handle)
    {
        if (!is_object($handle)) return false;
        $thread_id = $handle->thread_id;

        //MySQL server has gone away
        $errno = @mysqli_errno($handle);
        if ($thread_id > 0 && in_array($errno, array(2006, 2013))) {
            $hdk = '';
            foreach (self::$_HANDLE_ARRAY as $handleKey => $hd) {
                if ($hd == $handle) {
                    $hdk = $handleKey;
                    unset(self::$_HANDLE_ARRAY[$handleKey]);
                    break;
                }
            }
            if (empty($hdk)) {
                return false;
            }
            $config = self::$_HANDLE_CONFIG_MAP[$hdk];
            $handle = self::createDBHandle($config, $config['db_name'], $config['encoding']);
            return true;
        }
        return false;
    }

    /// 记录统一的错误日志
    /// @param[in] string $message, 错误消息
    /// @param[in] string $category, 错误的子类别
    protected static function logError($message, $category)
    {
//        if (class_exists('Logger')) {
//        $logger = new Logger('/data/service_logs/services/', 'mysql');
//        $logger->logError($message);
//        }
    }

    /// 记录统一的警告日志
    /// @param[in] string $message, 错误消息
    /// @param[in] string $category, 错误的子类别
    protected static function logWarn($message, $category)
    {
//        if (class_exists('Logger')) {
//        $logger = new Logger('/data/service_logs/services/', 'mysql');
//        $logger->logWarn($message);
//        }
    }
}
