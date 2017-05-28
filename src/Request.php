<?php
/**
 * User: johnxu <fsyzxz@163.com> 549716096
 * HomePage: http://www.johnxu.net
 * Date: 2017/4/21
 */

namespace tinyphp\helper;

/**
 * @method static get(string $name, string $default = '', string $filter = '');
 * @method static post(string $name, string $default = '', string $filter = '');
 * @method static delete(string $name, string $default = '', string $filter = '');
 * @method static put(string $name, string $default = '', string $filter = '');
 * @method static patch(string $name, string $default = '', string $filter = '');
 * @method static head(string $name, string $default = '', string $filter = '');
 */

class Request
{
    // 默认的过滤方法
    static $filters = array('htmlspecialchars', 'strip_tags', 'addslashes'); // 过滤方法

    /**
     * @param string $name
     * @param string $method
     * @param string $default
     * @param string $filter
     * @return string
     */
    public static function exec($name, $method, $default = '', $filter = '')
    {
        $method = strtolower($method);
        switch ($method) {
            case 'get':
            case 'head':
                $input = $_GET;
                break;
            case 'post':
                $input = $_POST;
                break;
            case 'put':
            case 'delete':
            case 'patch':
                parse_str(file_get_contents('php://input'), $input);
                break;
            case 'server':
                $input = $_SERVER;
                break;
            case 'session':
                $input = $_SESSION;
                break;
            default:
                $input = $_GET;
        }
        // 如果不指定键名，返回所有
        if (empty($name)) {
            return $input;
        }
        
        // 如果存在键名，返回指定的值
        $result = isset($input[ $name ]) ? $input[ $name ] : $default;

        // 如果采用了过滤方法，进行过滤，只支持字符串形式，数组不行
        if (function_exists($filter) && in_array($filter, static::$filters) && is_string($result)) {
            $result = $filter($result);
        }

        return $result;
    }

    /**
     * 获取提交的方式
     * @return string
     */
    public static function method()
    {
        return isset($_SERVER['REQUEST_METHOD']) ? strtolower($_SERVER['REQUEST_METHOD']) : 'get';
    }

    /**
     * 是否是ajax提交数据
     * @return boolean
     */
    public static function isAjax()
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) ? true : false;
    }

    /**
     * 是否是post提交数据
     * @return boolean
     */
    public static function isPost()
    {
        return (static::method() == 'post') ? true : false;
    }

    /**
     * 是否是delete提交数据
     * @return boolean
     */
    public static function isDelete()
    {
        return (static::method() == 'delete') ? true : false;
    }

    /**
     * 是否是get提交数据
     * @return boolean
     */
    public static function isGet()
    {
        return (static::method() == 'get') ? true : false;
    }

    /**
     * 是否是put提交数据
     * @return boolean
     */
    public static function isPut()
    {
        return (static::method() == 'put') ? true : false;
    }

    /**
     * 是否是delete提交数据
     * @return boolean
     */
    public static function isPatch()
    {
        return (static::method() == 'patch') ? true : false;
    }

    /**
     * 是否是head提交数据
     * @return boolean
     */
    public static function isHead()
    {
        return (static::method() == 'head') ? true : false;
    }

    /**
     * 获取客户端IP地址
     * @return mixed
     */
    public static function getClientIp()
    {
        if (isset($_SERVER)) {
            if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
                $realip = $_SERVER["HTTP_X_FORWARDED_FOR"];
            } else if (isset($_SERVER["HTTP_CLIENT_IP"])) {
                $realip = $_SERVER["HTTP_CLIENT_IP"];
            } else {
                $realip = $_SERVER["REMOTE_ADDR"];
            }
        } else {
            if (getenv("HTTP_X_FORWARDED_FOR")) {
                $realip = getenv("HTTP_X_FORWARDED_FOR");
            } else if (getenv("HTTP_CLIENT_IP")) {
                $realip = getenv("HTTP_CLIENT_IP");
            } else {
                $realip = getenv("REMOTE_ADDR");
            }
        }

        return $realip;
    }

    /**
     * @param string $method
     * @param mixed  $params
     * @return mixed
     */
    public static function __callStatic($method, $params)
    {
        $name = isset($params[0]) ? $params[0] : '';
        $default = isset($params[1]) ? $params[1] : '';
        $filter = isset($params[2]) ? $params[2] : static::$filters[0];
        return call_user_func(array('self', 'exec'), $name, $method, $default, $filter);
    }
}