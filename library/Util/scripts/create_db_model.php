<?php

require_once dirname(__DIR__) . '/../../bootstrap.php';
require_once CONF . '/db_config.php';
require_once UTIL . '/PhpCodeGenerator.php';

use \ROOT\Library\Util\Mysql\Mysqli\DBMysql;

class CreateDatabaseModel {

    /**
     * 根据表名生成类名，例如vehicle_c2c_car_source => VehicleC2cCarSourceModel
     * @param type $tableName
     * @return string
     */
    private static function _getClassName($tableName) {
        if (empty($tableName)) {
            return '';
        }

        $className = '';
        $tableNameParts = explode('_', $tableName);
        foreach ($tableNameParts as $part) {
            $className .= ucfirst($part);
        }
        $className .= 'Model';

        return $className;
    }

    /**
     * 根据数据库表配置生成model中的fieldTypes配置
     * @param type $dbFields
     * @return type
     */
    private static function _getFieldTypes($dbFields) {
        if (empty($dbFields)) {
            return array();
        }

        $fieldTypes = array();
        foreach($dbFields as $field) {
            $fieldType = strpos($field['Type'], 'int') !== FALSE ? 'INT' : 'VARCHAR';
            $fieldTypes[$field['Field']] = $fieldType;
        }
        return $fieldTypes;
    }

    /**
     * 批量创建model文件
     * @param $server 服务器配置，例如DBConfig::$SERVER_HAOCHE_SLAVE
     * @param $database 数据库，例如'ganji_vehicle'
     * @param $tableLike 表范围，例如'vehicle_c2c_***'
     * @param string $fileDir 放置model的目录
     * @return bool
     */
    public static function createModels($server, $database, $tableLike, $fileDir = '') {
        //获取表名称
        $dbr = DBMysql::createDBHandle($server, $database);
        $sql = "show tables like '{$tableLike}'";
        $tables = DBMysql::getAll($dbr, $sql);

        if (empty($tables)) {
            return false;
        }

        //为每个表创建model
        foreach ($tables as $tableInfo) {
            //获取表字段
            $tableName  = current($tableInfo);
            $sql = "DESC {$tableName}";
            $dbFields = DBMysql::getAll($dbr, $sql);

            //生成代码文件需要的变量
            $dateText = date('Y-m-d H:i:s');
            $className  = self::_getClassName($tableName);

            //fieldTypes
            $fieldTypes = self::_getFieldTypes($dbFields);
            $fieldTypesString = PhpCodeGenerator::genArray($fieldTypes, false, '    ');
            $fieldTypesString = trim($fieldTypesString);

            $databaseUp = strtoupper($database);

            $code = <<<modelCode
<?php
/**
 * 使用单例模式的model
 * @Copyright (c)
 * @date {$dateText}
 * (使用library/Util/scripts/create_db_model.php生成)
 */

namespace ROOT\Library\Model;

use \ROOT\Library\Util\Mysql\PdoManager as BaseModel;

class {$className} extends BaseModel {

    /**
     * 实例
     */
    private static \$_instance = null;

    /**
     * 表名
     */
    protected \$tableName = '{$tableName}';

    /**
     * 表字段
     */
    protected \$fieldTypes = {$fieldTypesString};

    protected function __construct() {
        require_once CONF . '/db_config.php';
        \$this->setHandler(\DataBaseConfig::\$SERVER_{$databaseUp}_MASTER, \DataBaseConfig::\$SERVER_{$databaseUp}_SLAVE, '{$database}');
    }

    public static function getInstance() {
        if (self::\$_instance instanceof self) {
            return self::\$_instance;
        }

        self::\$_instance = new self();
        return self::\$_instance;
    }
}
modelCode;

            $filePath = self::_getFilePath($fileDir, $className);
            file_put_contents($filePath, $code);
            echo "{$filePath} 生成成功!\n";
        }

        return true;
    }

    /**
     * 根据目录和类名创建文件路径
     * @param type $fileDir 目录位置
     * @param type $className 类名
     * @return type
     */
    private static function _getFilePath($fileDir, $className) {
        if (empty($fileDir)) {
            $fileDir = dirname(__FILE__);
        }

        return $fileDir . "/{$className}.php";
    }
}

$ret = CreateDatabaseModel::createModels(DataBaseConfig::$SERVER_LOCALHOST_SLAVE, 'lanse', 'typicms_menus', '/data/www/lanse/library/Model/');
echo $ret ? '创建成功' : '创建失败';
exit;
