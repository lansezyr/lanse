<?php
/**
 * MysqliManager.php
 *
 * User: lanse
 * Date: 16-08-01 上午11:12
 */

namespace Root\Library\Util\Mysql;

use Root\Library\Util\Mysql\Mysqli\SqlBuilder;
use Root\Library\Util\Mysql\Mysqli\DBMysql;

error_reporting(E_ERROR);

class MysqliManager
{
    protected $masterHandler = null;   //主库句柄
    protected $slaveHandler = null;    //从库句柄
    protected $fieldTypes = array();   //字段配置
    protected $tableName = '';         //表名
    protected $sqlBuilder = null;      //sql构造器
    private $masterConfig = array(); //主库配置
    private $slaveConfig = array();  //从库配置
    private $database = '';          //数据库名
    private $isUsedMaster = false; //是否强制使用主从库查询

    /**
     * 初始化句柄
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

        $this->masterConfig = $masterServer;
        $this->slaveConfig = $slaveServer;
        $this->database = $database;

        //sql构造器
        $this->sqlBuilder = new SqlBuilder($this->tableName, $this->fieldTypes, $this->getSlaveDb());

        return true;
    }

    /**
     * 构造函数，验证子类是否注册
     */
    protected function __construct()
    {
        if (empty($this->fieldTypes) || empty($this->tableName)) {
            trigger_error('fieldTypes or tableName should be set');
        }
    }

    /**
     * 单例模式不允许拷贝
     */
    private function __clone()
    {
    }

    /**
     * 获取主库句柄
     * @return null
     */
    protected function getMasterDb()
    {
        $this->masterHandler = DBMysql::createDBHandle($this->masterConfig, $this->database);
        return $this->masterHandler;
    }

    /**
     * 获取从库句柄
     * @return null
     */
    protected function getSlaveDb()
    {
        if ($this->isUsedMaster) {
            $this->slaveHandler = $this->getMasterDb();
            return $this->slaveHandler;
        }
        $this->slaveHandler = DBMysql::createDBHandle($this->slaveConfig, $this->database);
        return $this->slaveHandler;
    }

    /**
     * 强制读主库（读完强烈建议releaseForceMaster）
     * @return $this
     */
    public function forceMaster()
    {
        $this->isUsedMaster = true;
        $this->slaveHandler = $this->getMasterDb();
        return $this;
    }

    /**
     * 恢复读从库
     * @return bool
     */
    public function releaseForceMaster()
    {
        $this->isUsedMaster = false;
        $this->slaveHandler = $this->getSlaveDb();
    }


    /**
     * 插入数据
     * @param $data
     * @return bool
     */
    public function insert($data)
    {
        if (empty($data)) {
            return false;
        }
        $sql = $this->sqlBuilder->createInsertSql($data);

        return DBMysql::insertAndGetID($this->getMasterDb(), $sql);
    }

    /**
     * 删除记录
     * @param $where
     * @return bool
     */
    public function delete($where)
    {
        if (empty($where)) {
            return false;
        }

        $sql = $this->sqlBuilder->createDeleteSql($where);

        return DBMysql::execute($this->getMasterDb(), $sql);
    }

    /**
     * 根据id更新数据
     * @param $id
     * @param $updateData
     * @return bool
     */
    public function updateById($id, $updateData)
    {
        $id = intval($id);
        if (empty($id) || empty($updateData)) {
            return false;
        }

        return $this->update("id={$id}", $updateData);
    }

    /**
     * 更新数据
     * @param type $where 条件
     * @param array $updateData 更新的内容
     * @return boolean
     */
    public function update($where, $updateData)
    {
        if (empty($where) || empty($updateData)) {
            return false;
        }

        $sql = $this->sqlBuilder->createUpdateSql($updateData, $where);

        return DBMysql::execute($this->getMasterDb(), $sql);
    }

    /**
     * 获取一个字段值
     * @param $field
     * @param $where
     * @return array|bool|type
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

        return $row;
    }

    /**
     * 获取数量
     * @param $where
     * @return mixed
     */
    public function getCount($where)
    {
        return intval($this->getOne('count(1)', $where));
    }

    /**
     * 获取所有记录
     * @param string|array $where where条件
     *     string e.g. string 'id=3 and create_at<2222222 or type in (2,3)'
     *     array e.g. array('id' => 3, 'type' => 2)
     * @param string $orderBy 排序条件 e.g. 'id desc'
     * @param int $limit 数量
     * @param int $offset 偏移
     * @param string $fields 选择的字段 'id,type,create_at'
     * @return array
     */
    public function getList($fields = '*', $where = '', $orderBy = '', $limit = 0, $offset = 0)
    {
        if (empty($fields)) {
            return array();
        }

        $sql = $this->sqlBuilder->createSelectSql($fields, $where, $orderBy, $limit, $offset);
        if (empty($sql)) {
            return array();
        }

        return DBMysql::getAll($this->getSlaveDb(), $sql);
    }

    /**
     * 根据id获取记录
     * @param $id
     * @return array|type
     */
    public function getRowById($id)
    {
        $id = intval($id);
        if (empty($id)) {
            return array();
        }

        return $this->getRow("id={$id}");
    }

    /**
     * 根据where条件获取记录
     * @param string|array $where where条件
     *     string e.g. string 'id=3 and create_at<2222222 or type in (2,3)'
     *     array e.g. array('id' => 3, 'type' => 2)
     * @param string $fields 选择的字段 'id,type,create_at'
     * @param string $orderBy 排序条件 e.g. 'id desc'
     * @return type
     */
    public function getRow($where, $selectField = '*', $orderBy = '')
    {
        if (empty($where) || empty($selectField)) {
            return array();
        }

        if (strstr($selectField, 'count') || strstr($selectField, 'sum')) {
            $sql = $this->sqlBuilder->createSelectSql($selectField, $where);
        } else {
            $sql = $this->sqlBuilder->createSelectSql($selectField, $where, $orderBy, 1);
        }

        return DBMysql::getRow($this->getSlaveDb(), $sql);
    }

    /**
     * sql直接查询，暂时只支持读操作，需要写操作的时候再扩展
     * @param $sql
     * @return bool
     */
    public function query($sql)
    {
        if (empty($sql)) {
            return false;
        }

        return DBMysql::query($this->getSlaveDb(), $sql);
    }

    /**
     * 获取影响行数
     * @return mixed
     */
    public function getAffectedRows()
    {
        $rows = DBMysql::lastAffected($this->getMasterDb());

        return intval($rows);
    }
}
