<?php
/**
 * EloquentModel.php
 *
 * User: liushuangxi
 * Date: 16-08-31 上午11:12
 */

namespace ROOT\Library\Util\Mysql;

use Illuminate\Database\Eloquent\Model;

/**
 * Class EloquentModel
 * @package Pub\Mysql
 */
class EloquentModel extends Model
{
    protected $connection   = '';
    protected $table        = '';
    protected $primaryKey   = '';
    public    $timestamps   = false;
}