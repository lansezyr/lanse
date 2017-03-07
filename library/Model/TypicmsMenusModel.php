<?php
/**
 * 使用单例模式的model
 * @Copyright (c)
 * @date 2017-02-21 16:04:14
 * (使用library/Util/scripts/create_db_model.php生成)
 */

namespace Root\Library\Model;

use \Root\Library\Util\Mysql\PdoManager as BaseModel;

class TypicmsMenusModel extends BaseModel {

    /**
     * 实例
     */
    private static $_instance = null;

    /**
     * 表名
     */
    protected $tableName = 'typicms_menus';

    /**
     * 表字段
     */
    protected $fieldTypes = array(
        'id' => 'INT',
        'name' => 'VARCHAR',
        'class' => 'VARCHAR',
        'created_at' => 'VARCHAR',
        'updated_at' => 'VARCHAR',
    );

    protected function __construct() {
        require_once CONF . '/db_config.php';
        $this->setHandler(\DataBaseConfig::$SERVER_LOCALHOST_MASTER, \DataBaseConfig::$SERVER_LOCALHOST_SLAVE, 'lanse');
    }

    public static function getInstance() {
        if (self::$_instance instanceof self) {
            return self::$_instance;
        }

        self::$_instance = new self();
        return self::$_instance;
    }
}