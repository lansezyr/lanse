<?php
/**
 * PdoManager.php
 *
 * User: lanse
 * Date: 16-08-01 上午11:12
 */

namespace Root\Library\Util\Mysql;

use PDO;
use Exception;

class PdoManager
{
    /**
     * @var PDO
     */
    protected $masterHandler = null;     //主库句柄
    protected $masterHandlerKey = '';
    /**
     * @var PDO
     */
    protected $slaveHandler = null;     //从库句柄
    protected $slaveHandlerKey = '';

    protected $fieldTypes = array();  //字段配置
    protected $tableName = '';       //表名
    protected $sqlBuilder = null;     //sql构造器
    private $masterConfig = array();  //主库配置
    private $slaveConfig = array();  //从库配置
    private $database = '';       //数据库名
    private $logger = null;     //日志对象
    private $errorInfo = [];       //错误信息
    private $discardFields = [];   //丢弃的字段
    private $affectedRows = 0;    //影响的行数

    /**
     * @param $masterServer
     * @param $slaveServer
     * @param $database
     * @return bool
     */
    protected function setHandler($masterServer, $slaveServer, $database)
    {
        if (empty($masterServer) || empty($database)) {
            return false;
        }

        if (empty($slaveServer)) {
            $slaveServer = $masterServer;
        }

        $masterServer['database'] = $database;
        $slaveServer['database'] = $database;

        $this->masterConfig = $masterServer;
        $this->slaveConfig = $slaveServer;
        $this->database = $database;

     //   $this->logger = new Logger('/data/service_logs/services/', 'mysql');

        return true;
    }

    /**
     * @return null|PDO
     */
    protected function getMasterDb()
    {
        return $this->getMasterHander();
    }

    /**
     * @return null|PDO
     * @throws Exception
     */
    protected function getMasterHander()
    {
        if (!is_null($this->masterHandler) && $this->_checkHandler($this->masterHandler)) {
            return $this->masterHandler;
        }

        PdoHandle::removeHandle($this->masterHandlerKey);

        for ($i = 1; $i <= 3; $i++) {
            try {
                $handler = PdoHandle::getInstance($this->masterConfig);
                if (empty($handler->handler)) {
                    continue;
                }
                $this->masterHandler = $handler->handler;
                $this->masterHandlerKey = $handler->handler_key;

//              if (!$this->_checkHandler($this->masterHandler)) {
//                  continue;
//              }

                return $this->masterHandler;
            } catch (Exception $e) {
                $tmp = $this->masterConfig;
                unset($tmp['password']);

                $message = "PdoManager:数据库链接失败，请检主库配置 配置：（" . json_encode($tmp);
                $message .= "） 数据表：" . $this->tableName;
                $this->throwException($e, $message);
            }
        }

        return null;
    }

    /**
     * @return null|PDO
     */
    protected function getSlaveDb()
    {
        return $this->getSlaveHandler();
    }

    /**
     * @throws Exception
     */
    protected function getSlaveHandler()
    {
        if (!is_null($this->slaveHandler) && $this->_checkHandler($this->slaveHandler)) {
            return $this->slaveHandler;
        }

        PdoHandle::removeHandle($this->slaveHandlerKey);

        for ($i = 1; $i <= 3; $i++) {
            try {
                $handler = PdoHandle::getInstance($this->slaveConfig);
                if (empty($handler->handler)) {
                    continue;
                }
                $this->slaveHandler = $handler->handler;
                $this->slaveHandlerKey = $handler->handler_key;

//              if (!$this->_checkHandler($this->slaveHandler)) {
//                  continue;
//              }

                return $this->slaveHandler;
            } catch (Exception $e) {
                $tmp = $this->slaveConfig;
                unset($tmp['password']);

                $message = "PdoManager:数据库链接失败，请检从库配置 配置：（" . json_encode($tmp);
                $message .= "） 数据表：" . $this->tableName;
                $this->throwException($e, $message);
            }
        }

        return null;
    }

    /**
     * 检查PDO可用性
     * @param PDO $handler
     * @return bool
     */
    private function _checkHandler($handler = null)
    {
        if (PHP_SAPI != 'cli') {
            return true;
        }

        try {
            if (empty($handler) || !($handler instanceof PDO)) {
                return false;
            }

            $stmt = $handler->prepare('select curdate()');
            return $stmt->execute();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * @return $this
     */
    public function forceMaster()
    {
        $this->tempSlaveHandler = $this->getSlaveDb();
        $this->slaveHandler = $this->getMasterDb();

        return $this;
    }

    /**
     * @return bool
     */
    public function releaseForceMaster()
    {
        if (empty($this->tempSlaveHandler)) {
            return false;
        }

        $this->slaveHandler = $this->tempSlaveHandler;
    }

    /**
     * @param $data
     * @return bool|int
     */
    public function insert($data)
    {
        if (empty($data)) {
            return false;
        }

        try {
            return $this->pdoInsert($this->tableName, $data);
        } catch (Exception $e) {
            $this->throwException($e, 'PdoManager:操作失败，数据添加失败');
            return false;
        }
    }

    /**
     * 批量插入数据
     * @param array $data
     * @param int $number
     * @return bool
     */
    public function batchInsert($data = [], $number = 1000)
    {
        if (empty($data)) {
            return false;
        }

        $demo = end($data);

        if (!is_array($demo) || empty($demo)) {
            return false;
        }

        $count = 0;

        try {
            //数据分组
            $batch = array_chunk($data, $number);

            foreach ($batch as $items) {
                if (empty($items)) {
                    continue;
                }

                //表字段
                $fields = implode(',', array_map(function ($field) {
                    return $this->checkField($field);
                }, array_keys($demo)));

                //表数据
                $values = implode(',', array_map(function ($item) {
                    $value = implode(',', array_map(function ($data) {
                        return "'$data'";
                    }, array_values($item)));

                    return "($value)";
                }, array_values($items)));

                $sql = "INSERT INTO $this->tableName ($fields) VALUES $values;";

                $result = $this->pdoExecute($sql);
                if (!$result) {
                    return false;
                }

                $count += count($items);
            }
        } catch (Exception $e) {
            $this->throwException($e, 'PdoManager:操作失败，数据添加失败');
            return false;
        }

        return intval($count);
    }

    /**
     * @param array $where
     * @return bool
     * @throws Exception
     */
    public function delete($where)
    {
        if (empty($where)) {
            return false;
        }

        try {
            if (is_array($where)) {
                return $this->pdoDelete($this->tableName, $where);
            } else {
                if (empty(trim($where))) {
                    return false;
                }
                $sql = <<<EOF
DELETE FROM $this->tableName WHERE $where
EOF;

                return $this->pdoExecute($sql);
            }
        } catch (Exception $e) {
            $this->throwException($e, 'PdoManager:操作失败，数据删除失败');
            return false;
        }
    }

    /**
     * @param $id
     * @param $update
     * @return bool
     */
    public function updateById($id, $update)
    {
        $id = intval($id);
        if (empty($id) || empty($update)) {
            return false;
        }

        return $this->update(['id' => $id], $update);
    }

    /**
     * @param $where
     * @param $update
     * @return bool|mixed
     */
    public function update($where, $update)
    {
        if (empty($where) || empty($update)) {
            return false;
        }

        try {
            return $this->pdoUpdate($this->tableName, $update, $where);
        } catch (Exception $e) {
            $this->throwException($e, 'PdoManager:操作失败，数据更新失败');
            return false;
        }
    }

    /**
     * @param $field
     * @param $where
     * @return bool|string
     */
    public function getOne($field, $where)
    {
        if (empty($field) || !is_string($field)) {
            return false;
        }

        $row = $this->getRow($where, $field);

        if (!empty($row) && is_array($row)) {
            return current($row);
        }

        return false;
    }

    /**
     * @param $where
     * @return mixed
     */
    public function getCount($where)
    {
        return intval($this->getOne('COUNT(1) as total', $where));
    }

    /**
     * @param string $fields
     * @param string $where
     * @param string $order
     * @param int $limit
     * @param int $offset
     * @return array|mixed
     */
    public function getList($fields = '*', $where = '', $order = '', $limit = 0, $offset = 0)
    {
        if (empty($fields)) {
            return [];
        }

        $order = trim($order) != '' ? ' ORDER BY ' . $order : '';

        $limit = intval($limit);
        $offset = intval($offset);

        if (is_array($where)) {
            return $this->pdoQuery($this->tableName, $fields, $where, $order, $limit, $offset);
        } else {
            if (!empty($where)) {
                $where_sql = "WHERE $where";
            } else {
                $where_sql = "";
            }

            if ($limit > 0) {
                $limit_sql = "LIMIT $offset, $limit";
            } else {
                $limit_sql = "";
            }

            $sql = <<<EOF
SELECT $fields FROM $this->tableName $where_sql $order $limit_sql
EOF;
            return $this->query($sql);
        }
    }

    /**
     * @param string $id
     * @return array|mixed
     */
    public function getRowById($id)
    {
        $id = intval($id);
        if (empty($id)) {
            return [];
        }

        return $this->getRow(['id' => $id]);
    }

    /**
     * @param array $where
     * @param string $fields
     * @param string $order
     * @return mixed
     * @throws Exception
     */
    public function getRow($where, $fields = '*', $order = '')
    {
        if (empty($where) || empty($fields)) {
            return [];
        }

        $order = trim($order) != '' ? ' ORDER BY ' . $order : '';

        if (is_array($where)) {
            $result = $this->pdoQuery($this->tableName, $fields, $where, $order, 1);
            $result = isset($result[0]) ? $result[0] : $result;
        } else {
            $sql = <<<EOF
SELECT $fields FROM $this->tableName WHERE $where $order
EOF;
            if (strpos(strtolower($sql), 'limit 1') === false) {
                $sql .= " LIMIT 1";
            }

            $result = $this->query($sql, false);
        }

        if ($result === false) {
            return false;
        }

        if (empty($result)) {
            return [];
        }

        return $result;
    }

    /**
     * 查询(仅作为查询方法使用)
     * @param string $sql
     * @param bool $all
     * @return array
     * @throws Exception
     */
    public function query($sql, $all = true)
    {
        if (empty($sql)) {
            return false;
        }

        for ($retry = 1; $retry <= 2; $retry++) {
            try {
                $pdo = $this->getSlaveHandler();
                if (is_null($pdo)) {
                    continue;
                }

                $stmt = $pdo->prepare($sql);

                $result = $stmt->execute();
                if (!$result) {
                    $this->errorInfo = $stmt->errorInfo();

                    $msg = "PdoManager:操作失败，数据获取失败";
                    $msg .= " SQL: " . $stmt->queryString;
                    $msg .= " ErrorInfo: " . json_encode($this->errorInfo);
                    $this->logError($msg);

                    continue;
                }

                if ($all) {
                    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                } else {
                    $data = $stmt->fetch(PDO::FETCH_ASSOC);
                }

                return $data;
            } catch (Exception $e) {
                $msg = "PdoManager:操作失败，数据获取失败";

                $this->throwException($e, $msg);
                continue;
            }
        }

        return false;
    }

    /**
     * @param string $msg
     */
    public function logError($msg = '')
    {
 //       $this->logger->logError($msg . "\n");
//      throw new Exception($msg);
    }

    /**
     * @param null $exception
     * @param $msg
     * @throws Exception
     */
    public function throwException($exception = null, $msg = '')
    {
 //       $this->logger->logError($msg . "\n" . $exception->getMessage());
//      throw new Exception($msg);
    }

    /**
     * @param array $data
     * @param array $fieldTypes
     * @return bool
     */
    public function filterFields(&$data = [], $fieldTypes = [])
    {
        if (empty($fieldTypes)) {
            $fieldTypes = $this->fieldTypes;
        }

        $this->discardFields = [];
        $validData = array();
        foreach ($data as $key => $value) {
            //未注册的字段丢弃掉
            if (!isset($fieldTypes[$key])) {
                $this->discardFields[] = "字段($key)不存在";
                continue;
            }

            //根据类型验证
            if ($fieldTypes[$key] == 'INT') {
                if (!is_numeric($value)) {
                    $this->discardFields[] = "字段($key)值($value)不是数字";
                    continue;
                }
                $value = intval($value);
            } elseif ($fieldTypes[$key] == 'VARCHAR') {
                if (!is_string($value)) {
                    $this->discardFields[] = "字段($key)值($value)不是字符串";
                    continue;
                }
            } else {
                $this->discardFields[] = "字段($key)值($value)类型不正确";
                continue;
            }

            $validData[$key] = $value;
        }

        $data = $validData;

        return true;
    }

    /**
     * @param string $fieldName
     * @return string
     */
    public function checkField($fieldName = '')
    {
        $fieldName = preg_replace('/[^\w\.]/', '', $fieldName);

        $pos = strpos($fieldName, '.');
        if ($pos > 0) {
            $fieldName = str_replace('.', "`.`", $fieldName);
        } elseif ($pos === 0) {
            $fieldName = substr($fieldName, 1);
        }

        return "`{$fieldName}`";
    }

    /**
     * 获取PDO对象
     * @return null|PDO
     */
    public function getPdo()
    {
        return $this->getMasterHander();
    }

    /**
     *
     */
    public function pdoBeginTransaction()
    {
        $this->getPdo()->beginTransaction();
    }

    /**
     *
     */
    public function pdoCommit()
    {
        $this->getPdo()->commit();
    }

    /**
     *
     */
    public function pdoRollBack()
    {
        $this->getPdo()->rollBack();
    }

    /**
     * @return bool
     */
    public function pdoInTransaction()
    {
        return $this->getPdo()->inTransaction();
    }

    /**
     * 执行(仅作为非查询使用)
     * @param string $sql
     * @return bool
     */
    public function pdoExecute($sql = '')
    {
        if (empty($sql)) {
            return false;
        }

        for ($retry = 1; $retry <= 2; $retry++) {
            try {
                $pdo = $this->getMasterHander();
                if (is_null($pdo)) {
                    continue;
                }

                $stmt = $pdo->prepare($sql);

                $result = $stmt->execute();
                if (!$result) {
                    $this->errorInfo = $stmt->errorInfo();

                    $msg = "PdoManager:操作失败，SQL执行失败";
                    $msg .= " SQL: " . $stmt->queryString;
                    $msg .= " ErrorInfo: " . json_encode($this->errorInfo);
                    $this->logError($msg);

                    continue;
                }

                $this->affectedRows = $stmt->rowCount();

                return $result;
            } catch (Exception $e) {
                $msg = "PdoManager:操作失败，SQL执行失败";

                $this->throwException($e, $msg);
                continue;
            }
        }

        return false;
    }

    /**
     * @param string $table
     * @param array $data
     * @return int
     */
    public function pdoInsert($table = '', $data = [])
    {
        $this->filterFields($data);
        if (empty($data)) {
            return false;
        }

        $fields = @implode(',', array_map(function ($key) {
            return $this->checkField($key);
        }, array_keys($data)));

        $values = @implode(',', array_map(function ($key) {
            return ":" . $key;
        }, array_keys($data)));

        for ($retry = 1; $retry <= 2; $retry++) {
            try {
                $pdo = $this->getMasterHander();
                if (is_null($pdo)) {
                    continue;
                }

                $stmt = $pdo->prepare("INSERT INTO $table ($fields) VALUES ($values)");

                foreach ($data as $k => $v) {
                    $stmt->bindValue(":" . $k, $v);
                }

                $result = $stmt->execute();
                if (!$result) {
                    $this->errorInfo = $stmt->errorInfo();

                    $msg = "PdoManager:操作失败，数据插入失败";
                    $msg .= " SQL: " . $stmt->queryString . " INSERT: " . json_encode($data);
                    $msg .= " ErrorInfo: " . json_encode($this->errorInfo);
                    $this->logError($msg);

                    continue;
                }

                $id = $pdo->lastInsertId();

                $this->affectedRows = $stmt->rowCount();

                return $id ? $id : false;
            } catch (Exception $e) {
                $msg = "PdoManager:操作失败，数据插入失败";

                $this->throwException($e, $msg);
                continue;
            }
        }

        return false;
    }

    /**
     * @param string $table
     * @param array $where
     * @return mixed
     */
    public function pdoDelete($table = '', $where = [])
    {
        $this->filterFields($where);
        if (empty($where)) {
            return false;
        }

        $fields = @implode(" AND ", array_map(function ($key) {
            return $this->checkField($key) . "=:" . $key;
        }, array_keys($where)));

        for ($retry = 1; $retry <= 2; $retry++) {
            try {
                $pdo = $this->getMasterHander();
                if (is_null($pdo)) {
                    continue;
                }

                $stmt = $pdo->prepare("DELETE FROM $table WHERE $fields");

                foreach ($where as $k => $v) {
                    $stmt->bindValue(":" . $k, $v);
                }

                $result = $stmt->execute();
                if (!$result) {
                    $this->errorInfo = $stmt->errorInfo();

                    $msg = "PdoManager:操作失败，数据删除失败";
                    $msg .= " SQL: " . $stmt->queryString . " WHERE: " . json_encode($where);
                    $msg .= " ErrorInfo: " . json_encode($this->errorInfo);
                    $this->logError($msg);

                    continue;
                }

                $this->affectedRows = $stmt->rowCount();

                return $result;
            } catch (Exception $e) {
                $msg = "PdoManager:操作失败，数据删除失败";

                $this->throwException($e, $msg);
                continue;
            }
        }

        return false;
    }

    /**
     * @param string $table
     * @param array $update
     * @param array $where
     * @return mixed
     */
    public function pdoUpdate($table = '', $update = [], $where = [])
    {
        if (is_array($update)) {
            $this->filterFields($update);

            $fk = @implode(",", array_map(function ($key) {
                return $this->checkField($key) . "=:f_" . $key;
            }, array_keys($update)));
        } else {
            $fk = $update;
        }

        if (is_array($where)) {
            $this->filterFields($where);

            $wk = @implode(" AND ", array_map(function ($key) {
                return $this->checkField($key) . "=:w_" . $key;
            }, array_keys($where)));
        } else {
            $wk = $where;
        }

        if (empty($fk) || empty($wk)) {
            return false;
        }

        for ($retry = 1; $retry <= 2; $retry++) {
            try {
                $pdo = $this->getMasterHander();
                if (is_null($pdo)) {
                    continue;
                }

                $stmt = $pdo->prepare("UPDATE $table SET " . $fk . " WHERE " . $wk);

                if (is_array($update)) {
                    foreach ($update as $k => $v) {
                        $stmt->bindValue(":f_" . $k, $v);
                    }
                }

                if (is_array($where)) {
                    foreach ($where as $k => $v) {
                        $stmt->bindValue(":w_" . $k, $v);
                    }
                }

                $result = $stmt->execute();
                if (!$result) {
                    $this->errorInfo = $stmt->errorInfo();

                    $msg = "PdoManager:操作失败，数据更新失败";
                    $msg .= " SQL: " . $stmt->queryString;
                    $msg .= " WHERE: " . json_encode($where) . " UPDATE: " . json_encode($update);
                    $msg .= " ErrorInfo: " . json_encode($this->errorInfo);
                    $this->logError($msg);

                    continue;
                }

                $this->affectedRows = $stmt->rowCount();

                return $result;
            } catch (Exception $e) {
                $msg = "PdoManager:操作失败，数据更新失败";

                $this->throwException($e, $msg);
                continue;
            }
        }

        return false;
    }

    /**
     * @param string $table
     * @param string $fields
     * @param array $where
     * @param string $order
     * @param int $limit
     * @param int $offset
     * @return mixed
     * @throws Exception
     */
    public function pdoQuery($table = '', $fields = '', $where = [], $order = '', $limit = 0, $offset = 0)
    {
        $this->filterFields($where);

        $limit = intval($limit);
        $offset = intval($offset);

        $params = @implode(" AND ", array_map(function ($key) {
            return $this->checkField($key) . "=:" . $key;
        }, array_keys($where)));
        $params = trim($params);

        if (!empty($params)) {
            $where_sql = "WHERE $params";
        } else {
            $where_sql = "";
        }

        if ($limit > 0) {
            $limit_sql = "LIMIT $offset, $limit";
        } else {
            $limit_sql = "";
        }

        for ($retry = 1; $retry <= 2; $retry++) {
            try {
                $pdo = $this->getSlaveHandler();
                if (is_null($pdo)) {
                    continue;
                }

                $stmt = $pdo->prepare("SELECT $fields FROM $table $where_sql $order $limit_sql");

                foreach ($where as $k => $v) {
                    $stmt->bindValue(":" . $k, $v);
                }

                $result = $stmt->execute();
                if (!$result) {
                    $this->errorInfo = $stmt->errorInfo();

                    $msg = "PdoManager:操作失败，数据获取失败";
                    $msg .= " SQL: " . $stmt->queryString . " WHERE: " . json_encode($where);
                    $msg .= " ErrorInfo: " . json_encode($this->errorInfo);
                    $this->logError($msg);

                    continue;
                }

                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                $msg = "PdoManager:操作失败，数据获取失败";

                $this->throwException($e, $msg);
                continue;
            }
        }

        return false;
    }

    /**
     * 返回错误信息
     * @return array
     */
    public function getErrorInfo()
    {
        return $this->errorInfo;
    }

    /**
     * 丢弃的字段
     * @return array
     */
    public function getDiscardFields()
    {
        return $this->discardFields;
    }

    /**
     * PDO::quote处理字符串
     * @param string $string
     * @param int $type
     * @return string
     */
    public function quoteString($string = '', $type = PDO::PARAM_STR)
    {
        $pdo = $this->getSlaveHandler();

        return $pdo->quote($string, $type);
    }

    /**
     * 获取影响行数
     * @return mixed
     */
    public function getAffectedRows()
    {
        return intval($this->affectedRows);
    }
}
