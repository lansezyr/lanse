<?php
/**
 * EloquentDb.php
 *
 * User: lanse
 */

namespace ROOT\Util\Mysql;

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;

/**
 * Class EloquentDb
 * @package Pub\Mysql
 */
class EloquentDb
{
    /**
     * @var null
     */
    private static $_instance   = null;

    /**
     * @var DB|null
     */
    private $capsule            = null;

    /**
     * EloquentDb constructor.
     */
    public function __construct()
    {
        $this->capsule  = new DB;

        $this->capsule->setEventDispatcher(new Dispatcher(new Container));
        $this->capsule->setAsGlobal();
        $this->capsule->bootEloquent();
    }

    /**
     * @return null|EloquentDb
     */
    public static function getInstance() {
        if (self::$_instance instanceof self) {
            return self::$_instance;
        }

        self::$_instance    = new self();

        return self::$_instance;
    }

    /**
     * @param array $master
     * @param array $slave
     * @param string $name
     */
    public function addConnection($master=[], $slave=[], $name='') {
        $default    = [
//          'host'      => '',
//          'database'  => '',
//          'username'  => '',
//          'password'  => '',
//          'port'      => '',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
        ];

        $master     = array_merge($default, $master);
        $slave      = array_merge($default, $slave);

        $config     = [
            'driver'    => 'mysql',
            'read'      => $slave,
            'write'     => $master
        ];

        $this->capsule->addConnection($config, $name);
    }
}