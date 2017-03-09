<?php
/**
 * @class http分析与跳转的一些操作
 * 包括分析url、ip，获取$_GET、$_POST、$_REQUEST数据，页面跳转等
 * @author lanse
 */

namespace Root\Library\Util;

class HttpRequestUtil
{

    /**
     * @brief 取得http头信息
     * @param $header
     * @return bool|null
     */
    public static function header($header)
    {
        if (empty($header)) {
            return null;
        }

        // Try to get it from the $_SERVER array first
        $temp = 'HTTP_' . strtoupper(str_replace('-', '_', $header));
        if (isset($_SERVER[$temp]) && $_SERVER[$temp] !== '') {
            return $_SERVER[$temp];
        }

        // This seems to be the only way to get the Authorization header on
        // Apache
        if (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            if (!empty($headers[$header])) {
                return $headers[$header];
            }
        }
        return false;
    }

    /**
     * @brief 判断是否是ajax操作的数据
     * @return bool
     */
    public static function isAjax()
    {
        return ('XMLHttpRequest' == self::header('X_REQUESTED_WITH'));
    }

    /**
     * @breif 判断页面是否是数据post过来
     * @return boolean
     */
    public static function isPost()
    {
        return $_SERVER['REQUEST_METHOD'] == 'POST';
    }

    /**
     * @brief 判断 http  method 是否为get
     * @return bool
     */
    public static function isGet()
    {
        return $_SERVER['REQUEST_METHOD'] == 'GET';
    }

    /**
     * @brief 除去url尾部的#号
     * @param $url 要修改的url
     * @return mixed 返回修改后的url
     */
    private static function makeSafeUrlForRedirect($url)
    {
        $url = htmlspecialchars_decode($url);
        if (preg_match('/#$/', $url)) {
            $url = str_replace('#', '', $url);
        }
        return preg_replace("/[\"\'\n\r<>]+/", "", $url);
    }

    /**
     * @brief 取得当前页面的url
     * @param null $show_script_name
     * @return string
     */
    public static function getCurrentUrl($show_script_name = NULL)
    {
        $pageURL = 'http';
        if (!empty ($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") $pageURL .= "s";
        $pageURL .= "://";
        //donkey-proxy 会向业务线后端机器发送第一级的端口http_port
        $port = isset($_SERVER['HTTP_PORT']) ? $_SERVER['HTTP_PORT'] : $_SERVER["SERVER_PORT"];
        if ($port != "80") {
            $pageURL .= $_SERVER["HTTP_HOST"] . ":" . $port . ($show_script_name ? $_SERVER['SCRIPT_NAME'] : $_SERVER["REQUEST_URI"]);
        } else {
            $pageURL .= $_SERVER["HTTP_HOST"] . ($show_script_name ? $_SERVER['SCRIPT_NAME'] : $_SERVER["REQUEST_URI"]);
        }
        return $pageURL;
    }

    /**
     * @brief 302跳转
     * @param $url
     * @param bool $inIframe
     */
    public static function redirect($url, $inIframe = false)
    {
        self::_redirect($url, 302, $inIframe);
    }

    /**
     * @param $url
     * @param int $code
     * @param bool $inIframe
     */
    private static function _redirect($url, $code = 302, $inIframe = false)
    {
        $url = self::makeSafeUrlForRedirect($url);
        if (!$inIframe) {
            header("HTTP/1.1 {$code} Moved Temporarily");
            header('Location: ' . $url);
        } else {
            echo '<html>
<head>
<title>redirect</title>
</head>
<body>
<script src="http://sta.ganji.com/cgi/ganji_sta.php?file=ganji" type="text/javascript"></script>
<script type="text/javascript">
GJ.use("talk_to_parent", function(){
    window.location.href = "' . $url . '";
});
</script>
</body>
</html>';
        }
        exit;
    }

    /**
     * @brief 父类跳转
     * @param $url
     */
    public static function redirectParent($url)
    {
        $url = self::makeSafeUrlForRedirect($url);

        echo '<html>
<head>
<title>redirect</title>
</head>
<body>
<script src="http://sta.ganji.com/cgi/ganji_sta.php?file=ganji" type="text/javascript"></script>
<script type="text/javascript">
GJ.use("talk_to_parent", function(){
    GJ.talkToParent.parentRedirect("' . $url . '");
});
</script>
</body>
</html>';
        exit;
    }

    /**
     * @breif 301跳转
     * @param $url
     * @param bool $inIframe
     */
    public static function redirectPermanent($url, $inIframe = false)
    {
        self::_redirect($url, 301, $inIframe);
    }

    /**
     * @brief 跳转到页面本身
     */
    public static function redirectToSelf()
    {
        $url = $_SERVER['REQUEST_URI'];
        self::redirect($url);
    }


    /**
     * @brief 获取POST中的数据
     * @param $key
     * @param bool $default 如果数据不存在，默认返回的值。默认情况下为false
     * @param bool $enableHtml 返回的结果中是否允许html标签，默认为false
     * @return bool|string
     */
    public static function getPOST($key, $default = false, $enableHtml = false)
    {
        if (isset ($_POST[$key])) {
            if (!$enableHtml && is_array($_POST[$key])) {
                $value = array();
                foreach ($_POST[$key] as $pkey => $pval) {
                    if (is_string($pval)) {
                        $value[$pkey] = strip_tags($pval);
                    } else {
                        $value[$pkey] = $pval;
                    }
                }
                return $value;
            } else {
                return !$enableHtml ? strip_tags($_POST[$key]) : $_POST[$key];
            }
        }
        return $default;
    }

    /**
     * @brief 获取GET中的数据
     * @param $key
     * @param bool $default 如果数据不存在，默认返回的值。默认情况下为false
     * @param bool $enableHtml 返回的结果中是否允许html标签，默认为false
     * @return bool|string
     */
    public static function getGET($key, $default = false, $enableHtml = false)
    {
        if (isset ($_GET[$key])) {
            return !$enableHtml ? strip_tags(urldecode($_GET[$key])) : urldecode($_GET[$key]);
        }
        return $default;
    }

    /**
     * @brief 获取REQUEST中的数据
     * @param $key
     * @param bool $default 如果数据不存在，默认返回的值。默认情况下为false
     * @param bool $enableHtml 返回的结果中是否允许html标签，默认为false
     * @return bool|string
     */
    public static function getREQUEST($key, $default = false, $enableHtml = false)
    {
        if (isset ($_REQUEST[$key])) {
            if (is_array($_REQUEST[$key])) {
                return $_REQUEST[$key];
            }
            return !$enableHtml ? strip_tags(urldecode($_REQUEST[$key])) : urldecode($_REQUEST[$key]);
        }
        return $default;
    }

    /**
     * @brief 获取COOKIE中的数据
     * @param $key
     * @param bool $default 如果数据不存在，默认返回的值。默认情况下为false
     * @param bool $enableHtml 返回的结果中是否允许html标签，默认为false
     * @return bool|string
     */
    public static function getCOOKIE($key, $default = false, $enableHtml = false)
    {
        if (isset ($_COOKIE[$key])) {
            return !$enableHtml ? strip_tags($_COOKIE[$key]) : $_COOKIE[$key];
        }
        return $default;
    }

    /**
     * @brief 获得当前页面的前一个页面URL
     * @param bool $default
     * @return bool
     */
    public static function getReferUrl($default = false)
    {
        if (isset ($_SERVER['HTTP_REFERER']))
            return $_SERVER['HTTP_REFERER'];
        else
            return $default;
    }

    /**
     * @brief 获取当前的域名
     * @return mixed
     */
    public static function getHost()
    {
        return $_SERVER['HTTP_HOST'];
    }

    /**
     * @brief 获取用户ip
     * @param boolean $useInt 是否将ip转为int型，默认为true
     * @param boolean $returnAll 如果有多个ip时，是否会部返回。默认情况下为false
     * @return string|array|false
     */
    public static function getIp($useInt = true, $returnAll = false)
    {
        $realIp = false;

        //先从 HTTP_CLIENT_IP 获取城市IP
        if ($realIp === false) {
            $ip = getenv('HTTP_CLIENT_IP');
            if ($ip && strcasecmp($ip, "unknown")) {
                $realIp = $ip;
            } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
                $ip = $_SERVER['HTTP_CLIENT_IP'];
                if ($ip && strcasecmp($ip, "unknown")) {
                    $realIp = $ip;
                }
            }
        }

        //从 HTTP_X_FORWARDED_FOR 获取城市IP
        if ($realIp === false) {
            $ip = getenv('HTTP_X_FORWARDED_FOR');
            if ($ip && strcasecmp($ip, "unknown")) {
                $realIp = $ip;
            } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
                if ($ip && strcasecmp($ip, "unknown")) {
                    $realIp = $ip;
                }
            }
        }

        //从 REMOTE_ADDR 获取城市IP
        if ($realIp === false) {
            $ip = getenv('REMOTE_ADDR');
            if ($ip && strcasecmp($ip, "unknown")) {
                $realIp = $ip;
            } elseif (isset($_SERVER['REMOTE_ADDR'])) {
                $ip = $_SERVER['REMOTE_ADDR'];
                if ($ip && strcasecmp($ip, "unknown")) {
                    $realIp = $ip;
                }
            }
        }

        // 不是内网私有
        if ($realIp && !self::_isPrivateIp($realIp)) {
            return self::_returnIp($realIp, $useInt, $returnAll);
        }

        return false;
    }

    /**
     * @brief 是否私有ip
     * @param string $ip
     * @return boolean true|是, false|不是
     */
    private static function _isPrivateIp($ip)
    {
        //私有地址排除应该是如下的地址段：
        //A类 10.0.0.0--10.255.255.255
        //B类 172.16.0.0--172.31.255.255
        //C类 192.168.0.0--192.168.255.255
        $privateIps = array(
            '127.',
            '10.',
            '192.168.',
        );
        foreach ($privateIps as $rangeIp) {
            $len = strlen($rangeIp);
            if (substr($ip, 0, $len) == $rangeIp) {
                return true;
            }
        }
        return false;
    }

    /**
     * @brief 获取客户端port，只有https才能得到真实的REMOTE_PORT，http得到为假port
     * @return  REMOTE_PORT int|''
     */
    public static function getIpPort()
    {
        return $_SERVER['REMOTE_PORT'] ? $_SERVER['REMOTE_PORT'] : '';
    }

    /**
     * @param $ip
     * @param $useInt
     * @param $returnAll
     * @return array|bool|string
     */
    private static function _returnIp($ip, $useInt, $returnAll)
    {
        if (!$ip) return false;

        $ips = preg_split("/[，, _]+/", $ip);
        if (!$returnAll) {
            $ip = $ips[count($ips) - 1];
            return $useInt ? self::ip2long($ip) : $ip;
        }

        $ret = array();
        foreach ($ips as $ip) {
            $ret[] = $useInt ? self::ip2long($ip) : $ip;
        }
        return $ret;
    }

    /**
     * @brief 对php原ip2long的封装，原函数在win系统下会出现负数
     * @param $ip
     * @return string
     */
    public static function ip2long($ip)
    {
        return sprintf('%u', ip2long($ip));
    }

    /**
     * @brief 对php原long2ip的封装
     * @param $long
     * @return string
     */
    public static function long2ip($long)
    {
        return long2ip($long);
    }

    /**
     * @brief 生成 uuid
     * @return string
     */
    public static function getUuid()
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    public static function parentRedirect($url)
    {
        echo '<html><head><title>redirect</title></head><body>
                <script src="http://sta.ganji.com/cgi/ganji_sta.php?file=ganji" type="text/javascript"></script>
                <script type="text/javascript">
                var win = window.parent || window.top || window.opener;
                win.location.href = "' . $url . '";
                </script>
              </body></html>';
    }

    /**
     * @return string
     */
    public static function getHostUrl()
    {
        return 'http://' . $_SERVER['HTTP_HOST'];
    }

    /**
     * @return string
     */
    public static function getBaseUrl()
    {
        $urlPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        return self::getHostUrl() . $urlPath;
    }

    /**
     * @param $uri
     * @param string $params
     * @return string
     */
    public static function urlFor($uri, $params = '')
    {
        $params = (!empty($params) && is_array($params)) ? http_build_query($params) : $params;
        $url = self::getHostUrl() . '/' . $uri;
        if (!empty($params)) {
            $url .= '?' . $params;
        }
        return $url;
    }

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

}
