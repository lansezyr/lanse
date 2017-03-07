<?php
/**
 * PinyinNamespace
 */
namespace Root\Library\Util;

use \ROOT\Library\Util\PinYin\PinyinConfig;
class PinyinUtil{
    /* {{{ chinese2pinyin */
    /**
     * @brief 汉字转拼音
     *
     * @param $ch string 要获得拼音的汉字
     * @param $offset int 从第几个字开始取拼音
     * @param $length int 取多少个字的拼音
     * @param $encoding string 编码
     * @param $yindiao  boolean true =>带音调 false=>不带
     * @return   string
     */
    public static function chinese2pinyin($ch, $offset=0, $length=1, $encoding='utf-8', $yindiao = false) {
        $tmpArr = Array();
        for ($i = 0; $i < $length; $i+=1) {
            $c = mb_substr($ch, $i, 1, $encoding);
            $uni = mb_convert_encoding($c, 'UCS-2', $encoding);
            $unicode = (ord($uni[0])<<8) +  ord($uni[1]);
            $pinyin_dict = PinyinConfig::$PINYIN_DICT;
            if (array_key_exists($unicode, $pinyin_dict)) {
                $tmpArr[$i] = $pinyin_dict[$unicode][0];

                //ascii直接转换的没有音调
                if (false === $yindiao && $unicode > 122) {
                    $tmpArr[$i] = substr($tmpArr[$i], 0, -1);
                }
            } else {
                $tmpArr[$i] = "zzz";
            }
        }
        return join('', $tmpArr);
    }//}}}
}
