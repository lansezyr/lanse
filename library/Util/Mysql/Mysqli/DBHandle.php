<?php
/**
 * lanse
 * OO版本的MySQL访问接口对象，由DBMysql::createDBHandle2创建
 */

namespace ROOT\Library\Util\Mysql\Mysqli;

class DBHandle
{
    /**
     * @var handle DBMysql的句柄
     */
    protected $_handle;

    public function __construct($handle)
    {
        $this->_handle = $handle;
    }

    /**
     * 执行sql语句， 该语句必须是insert, update, delete, create table, drop table等更新语句
     * @param[in] string $sql, 具体执行的sql语句
     * @return TRUE:表示成功， FALSE:表示失败
     */
    public function execute($sql)
    {
        assert($this->_handle);
        return DBMysql::execute($this->_handle, $sql);
    }

    /**
     * 执行insert sql语句，并获取执行成功后插入记录的id
     * @param[in] handle $handle, 操作数据库的句柄
     * @param[in] string $sql, 具体执行的sql语句
     * @return FALSE表示执行失败， 否则返回insert的ID
     */
    public function insertAndGetID($sql)
    {
        assert($this->_handle);
        return DBMysql::insertAndGetID($this->_handle, $sql);
    }

    /**
     * 将所有结果存入数组返回
     * @param[in] handle $handle, 操作数据库的句柄
     * @param[in] string $sql, 具体执行的sql语句
     * @return FALSE表示执行失败， 否则返回执行的结果, 结果格式为一个数组，数组中每个元素都是mysqli_fetch_assoc的一条结果
     */
    public function query($sql)
    {
        assert($this->_handle);
        return DBMysql::query($this->_handle, $sql);
    }

    /**
     * 将查询的第一条结果返回
     * @param[in] handle $handle, 操作数据库的句柄
     * @param[in] string $sql, 具体执行的sql语句
     * @return FALSE表示执行失败， 否则返回执行的结果, 结果格式为一个数组，数组中每个元素都是mysqli_fetch_assoc的一条结果
     */
    public function queryFirst($sql)
    {
        assert($this->_handle);
        return DBMysql::queryFirst($this->_handle, $sql);
    }

    public function getAll($sql)
    {
        assert($this->_handle);
        return DBMysql::getAll($this->_handle, $sql);
    }

    /**
     * alias to queryFirst
     * @param $sql
     * @return FALSE
     */
    public function getRow($sql)
    {
        assert($this->_handle);
        return DBMysql::getRow($this->_handle, $sql);
    }

    /**
     * alias to DBMysql::getOne
     * @param $sql
     * @return FALSE|mixed
     */
    public function getOne($sql)
    {
        assert($this->_handle);
        return DBMysql::getOne($this->_handle, $sql);
    }

    /**
     * 得到最近一次操作影响的行数
     * @param[in] handle $handle, 操作数据库的句柄
     * @return FALSE表示执行失败， 否则返回影响的行数
     */
    public function lastAffected()
    {
        assert($this->_handle);
        return DBMysql::lastAffected($this->_handle);
    }

    /**
     * 得到最近一次操作错误的信息
     * @param[in] handle $handle, 操作数据库的句柄
     * @return FALSE表示执行失败， 否则返回 'errorno: errormessage'
     */
    public function getLastError()
    {
        assert($this->_handle);
        return DBMysql::getLastError($this->_handle);
    }

    /**
     * 关闭DB对象
     */
    public function close()
    {
        return DBMysql::releaseDBHandle($this->_handle);
    }

    public function mysqli_real_escape_string($string)
    {
        assert($this->_handle);
        return mysqli_real_escape_string($this->_handle, $string);
    }
}
