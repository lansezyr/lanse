<?php
/**
 * Created by PhpStorm.
 * User: lanse
 * Date: 17-3-10
 * Time: 下午5:45
 */

namespace Root\Library\Util;


class ArrUtil
{
    /**
     * @brief 二维数组指定列搜索
     * @param $arr 被搜索的数组
     * @param $key 二维数组key
     * @param $val 二维数组val
     * @return bool|int|string
     */
    public static function arraySearch($arr, $key, $val)
    {
        if (empty($arr)) {
            return false;
        }
        foreach ($arr as $idx => $item) {
            if ($item[$key] == $val) {
                return $idx;
            }
        }
        return false;
    }

    public static function arrayColumn($arr, $key)
    {
        $newArr = [];
        if (! empty($arr)) {
            foreach($arr as $idx => $item) {
                if(isset($item[$key])) {
                    $newArr[$idx] = $item[$key];
                }
            }
        }
        return $newArr;
    }

    public static function arrayFormat($arr)
    {
        $newArr = [];
        if (! empty($arr)) {
            foreach ($arr as $field => $valArr) {
                foreach ($valArr as $idx => $value) {
                    $newArr[$idx][$field] = $value;
                }
            }
        }
        return $newArr;
    }

    public static function arraySplitColumn($arr, $key = ['id','name'])
    {
        $newArr = [];
        if(! empty($key) AND is_array($key) AND count($key) == 2) {
            list($id, $name) = $key;
            if (!empty($arr)) {
                foreach ($arr as $idx => $item) {
                    $newArr[] = [
                        $id   => $idx,
                        $name => $item,
                    ];
                }
            }
        }
        return $newArr;
    }

}