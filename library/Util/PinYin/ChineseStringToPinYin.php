<?php
/**
 * 汉字字符串转换成对应的拼音字符串，数字、字母不变
 */
namespace Root\Library\Util\PinYin;

class ChineseStringToPinYin
{
    /**
     * Set the Chinese String
     *
     * @access private
     * @var string
     */
    private $chineseString    = '';
    /**
     * the Pinyin Data File Content
     *
     * @access private
     * @var string
     */
    private $content          = null;
    /**
     * eAcc Key of the Pinyin Data File Content
     *
     * @access private
     * @var string
     */
    private $eaccKey          = 'CHINESESTRINGTOPINYIN';
    /**
     * File Pointer of the Pinyin Data File
     *
     * @access private
     * @var resource
     */
    private $fp               = null;
    /**
     * First Char of the Pinyin String
     *
     * @access private
     * @var string
     */
    private $firstChar        = '';
    /**
     * Default String Encoding
     *
     * @access private
     * @var boolean
     */
    private $isGbk            = true;
    /**
     * Need the Tone Number or not
     *
     * @access private
     * @var boolean
     */
    private $isTone           = false;
    /**
     * the Path of the Pinyin Data File
     *
     * @access private
     * @var string
     */
    private $pinyinDataFile   = '/data/py.dat';
    /**
     * the Pinyin String Transfer From the Chinese String
     *
     * @access private
     * @var string
     */
    private $pinyinString     = '';
    /**
     * Default Split Char Between the Pinyin or Number or English Words
     *
     * @access private
     * @var string
     */
    private $splitChar        = '';
    /**
     * the Construct of the ChineseStringToPinYin Class
     *
     * @param string $chineseString
     * @param string $splitChar
     * @param boolean $isTone
     * @param string $encoding
     * @return void
     */
    public function __construct()
    {
        $this->pinyinDataFile = __DIR__ . $this->pinyinDataFile;
        if (function_exists('eaccelerator_get'))
        {
            $this->content = eaccelerator_get($this->eaccKey);
            if (is_null($this->content))
            {
                if (($this->fp = @fopen($this->pinyinDataFile, 'rb')) === false)
                {
                    $this->chineseString = false;
                    $this->pinyinString  = false;
                    $this->firstChar     = false;
                    return false;
                }
                if (($this->content = @fread($this->fp, filesize($this->pinyinDataFile))) === false)
                {
                    @fclose($this->fp);
                    $this->chineseString = false;
                    $this->pinyinString  = false;
                    $this->firstChar     = false;
                    return false;
                }
                else
                {
                    eaccelerator_put($this->eaccKey, $this->content);
                    @fclose($this->fp);
                }
            }
        }
        else
        {
            if (($this->fp = @fopen($this->pinyinDataFile, 'rb')) === false)
            {
                $this->chineseString = false;
                $this->pinyinString  = false;
                $this->firstChar     = false;
                return false;
            }
            if (($this->content = @fread($this->fp, filesize($this->pinyinDataFile))) === false)
            {
                @fclose($this->fp);
                $this->chineseString = false;
                $this->pinyinString  = false;
                $this->firstChar     = false;
                return false;
            }
            @fclose($this->fp);
        }
        if (empty($this->content))
        {
            $this->chineseString = false;
            $this->pinyinString  = false;
            $this->firstChar     = false;
            return false;
        }
        if (func_num_args() == 4)
        {
            $this->setSplitChar(func_get_arg(1));
            $this->setChineseString(func_get_arg(0), func_get_arg(2), func_get_arg(3));
        }
        else if (func_num_args() == 3)
        {
            $this->setSplitChar(func_get_arg(1));
            $this->setChineseString(func_get_arg(0), func_get_arg(2));
        }
        else if (func_num_args() == 2)
        {
            $this->setSplitChar(func_get_arg(1));
            $this->setChineseString(func_get_arg(0));
        }
        else if (func_num_args() == 1)
        {
            $this->setChineseString(func_get_arg(0));
        }
        else
        {
            //nothing to do
        }
    }
    /**
     * Get the Chinese Char Pinyin From ChineseCharData
     *
     * @param string the Chinese Char
     * @return string the Pinyin String
     */
    private function getCharPinyin($chineseChar)
    {
        if (strlen($chineseChar) != 2)
        {
            return $chineseChar;
        }
        $high   = ord($chineseChar[0]) - 0x81;
        $low    = ord($chineseChar[1]) - 0x40;
        $offset = (($high << 8) + $low - ($high * 0x40)) * 8;
        if ($offset < 0)
        {
            return $chineseChar;
        }
        $ret = rtrim(substr($this->content, $offset, 8), "\0");
        if ($this->isTone === false)
        {
            $ret = substr($ret, 0, -1);
        }
        return $ret;
    }
    /**
     * Return the First Char of the Pinyin String
     *
     * @param void
     * @return string
     */
    public function getFirstChar()
    {
        return $this->firstChar;
    }
    /**
     * Return the Status of the Encoding
     *
     * @param void
     * @return boolean
     */
    public function getIsGbk()
    {
        return $this->isGbk;
    }
    /**
     * Return the Status of the Tone
     *
     * @param void
     * @return boolean
     */
    public function getIsTone()
    {
        return $this->isTone;
    }
    /**
     * Return the Pinyin String of the Chinese String
     *
     * @param void
     * @return string
     */
    public function getPinyinString()
    {
        return $this->pinyinString;
    }
    /**
     * Return the Split Char of the Pinyin String
     *
     * @param void
     * @return string
     */
    public function getSplitChar()
    {
        return $this->splitChar;
    }
    /**
     * Set the Chinese String, Converse the Encoding of the string, Transfer the String to Pinyin
     *
     * @param string
     * @return string $this->pinyinString
     */
    public function setChineseString($chineseString, $isTone = false, $encoding = 'UTF-8')
    {
        $this->isTone        = $isTone;
        $this->chineseString = $chineseString;
        if (strcasecmp($encoding, 'GBK') !== 0)
        {
            $this->isGbk = false;
            $this->encodingConverse($encoding);
        }
        else
        {
            $this->isGbk = true;
        }
        $this->transferChineseString();
    }
    /**
     * Set the Split Char
     *
     * @param string
     * @return string $this->splitChar
     */
    public function setSplitChar()
    {
        if (func_num_args() == 1)
        {
            $this->splitChar = func_get_arg(0);
        }
        else
        {
            $this->splitChar = '';
        }
    }
    /**
     * Transfer the Encoding of the Chinese String To GB2312
     *
     * @param $this->chineseString
     * @return $this->chineseString
     */
    private function encodingConverse($encoding = 'UTF-8')
    {
        $this->chineseString = iconv($encoding, 'GBK', $this->chineseString);
    }
    /**
     * Transfer the Chinese String to the Pinyin String
     *
     * @param string $this->chineseString
     * @param array $this->chineseCharData
     * @return $this->pinyinString
     */
    private function transferChineseString()
    {
        $this->pinyinString = '';
        $preChar            = '';
        $strLen             = strlen($this->chineseString);
        for($i = 0;$i < $strLen;$i++)
        {
            $curChar = $this->chineseString{$i};
            if (ord($curChar) >= 128)
            {
                $curChar = $this->chineseString{$i} . $this->chineseString{++$i};
            }
            $pinyin = $this->getCharPinyin($curChar);
            if ((strlen($curChar) != 1 || strlen($preChar) != 1) && strlen($preChar) > 0)
            {
                $pinyin = $this->splitChar . $pinyin;
            }
            $this->pinyinString .= $pinyin;
            $preChar = $curChar;
        }
        if (strlen($this->pinyinString) > 0)
        {
            $this->firstChar = $this->pinyinString{0};
        }
        else
        {
            $this->firstChar = '';
        }
    }
    /**
     * the Destruct of the ChineseStringToPinYin Class
     *
     * @param void
     * @return void
     */
    public function __destruct()
    {}
}