<?php
/**
 * 使用单例模式的model
 * @Copyright (c)
 * @date 2017-02-21 15:57:37
 * (使用library/Util/scripts/create_db_model.php生成)
 */

namespace ROOT\Library\Model;

use \ROOT\Library\Util\Mysql\PdoManager as BaseModel;

class CanvasUsersModel extends BaseModel {

    /**
     * 实例
     */
    private static $_instance = null;

    /**
     * 表名
     */
    protected $tableName = 'canvas_users';

    /**
     * 表字段
     */
    protected $fieldTypes = array(
        'id' => 'INT',
        'first_name' => 'VARCHAR',
        'last_name' => 'VARCHAR',
        'display_name' => 'VARCHAR',
        'role' => 'INT',
        'url' => 'VARCHAR',
        'twitter' => 'VARCHAR',
        'facebook' => 'VARCHAR',
        'github' => 'VARCHAR',
        'linkedin' => 'VARCHAR',
        'resume_cv' => 'VARCHAR',
        'address' => 'VARCHAR',
        'city' => 'VARCHAR',
        'country' => 'VARCHAR',
        'bio' => 'VARCHAR',
        'job' => 'VARCHAR',
        'phone' => 'VARCHAR',
        'gender' => 'VARCHAR',
        'relationship' => 'VARCHAR',
        'birthday' => 'VARCHAR',
        'email' => 'VARCHAR',
        'password' => 'VARCHAR',
        'remember_token' => 'VARCHAR',
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