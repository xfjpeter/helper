<?php
/**
 * User: johnxu <fsyzxz@163.com>
 * HomePage: http://www.johnxu.net
 * Date: 2017/5/7
 */

namespace tinyphp\helper;

/**
 * @method static post($uri, $params = array(), $header = array());
 * @method static get($uri, $params = array(), $header = array());
 * @method static put($uri, $params = array(), $header = array());
 * @method static delete($uri, $params = array(), $header = array());
 * @method static head($uri, $params = array(), $header = array());
 * @method static patch($uri, $params = array(), $header = array());
 * @method static upload($uri, $params = array());
 */

class Http
{
    private static $ch = null;
    private static $timeout = 30; // 设置超时时间30s
    private static $instance = null; // 此类对象，连贯操作使用
    private static $data; // 数据结果
    private static $errno; // 错误号
    private static $error; // 错误信息
    private static $headerSize; // 头部信息大小
    
    /**
     * @param  string $uri
     * @param array   $params
     * @param array   $header
     * @param array   $opt
     * @param string  $method
     *
     * @return \tinyphp\Http
     */
    public static function exec($uri, $params = array(), $header = array('Expect:'), $opt = array(), $method = 'get')
    {
        if (empty($uri)) { // 如果不存在uri地址直接返回false
            return false;
        }
        
        $opts = array();
        
        static::$ch = curl_init(); // 初始化curl
        
        // 解析uri地址，分离参数和域名部分
        $parseUri = parse_url($uri);
        $scheme   = (isset($parseUri['scheme']) ? $parseUri['scheme'] : 'http') . '://'; // 协议类型
        $host     = isset($parseUri['host']) ? $parseUri['host'] : ''; //域名
        $path     = isset($parseUri['path']) ? $parseUri['path'] : ''; // 路径
        $query    = isset($parseUri['query']) ? $parseUri['query'] : ''; // 参数
        $uri      = $scheme . $host . $path . '?' . $query;
        
        // get提交，拼接uri地址
        if ($method === 'get') {
            $uri .= '&' . http_build_query($params);
        } else { // post之类的提交方式
            $uri = trim($uri, '?,&'); // 去除两端的？和&
            // 判断是不是上传文件
            if ($method === 'upload') {
                // 上传文件，格式array('mediaId', '文件路径')
                $params = array_values($params);
                if (version_compare(PHP_VERSION, '5.5.0', '>=')) {
                    $data = array(
                        $params[0] => new \CURLFile(realpath($params[1])),
                    );
                } else {
                    $data = array(
                        $params[0] => '@' . $params[1],
                    );
                }
                $params = $data;
                $method = 'post';
            }
            $opts = array(
                CURLOPT_POST          => true, # post请求方式
                CURLOPT_POSTFIELDS    => $params, # 设置post提交的参数,类似于para1=val1&para2=val2&...
                CURLOPT_HTTPHEADER    => $header,
                CURLOPT_CUSTOMREQUEST => strtoupper($method),
            );
            if (is_array($opt)) {
                foreach ($opt as $key => $value) {
                    $opts[ $key ] = $value;
                }
            }
        }
        
        static::setOpt($uri, $opts); // 设置curl_setopt选项
        
        static::$data = curl_exec(static::$ch); // 执行
        
        // 设置错误号和错误信息
        if (curl_errno(static::$ch)) {
            static::$errno = curl_errno(static::$ch);
            static::$error = curl_error(static::$ch);
        } else {
            static::$headerSize = curl_getinfo(static::$ch, CURLINFO_HEADER_SIZE);
        }
        
        return static::$instance;
    }
    
    /**
     * 获取状态码
     * @return mixed
     */
    public function getCode()
    {
        return curl_getinfo(static::$ch, CURLINFO_HTTP_CODE);
    }
    
    /**
     * 获取内容信息
     *
     * @param bool   $is_json  是否将结果json解析为数据或对象
     * @param bool   $is_array 是将结果解析喂数据还是对象
     * @param string $is_html  是否使用html获取函数过滤html标记
     *
     * @return string
     */
    public function getBody($is_json = true, $is_array = false, $is_html = '')
    {
        $result = mb_substr(static::$data, static::$headerSize);
        
        $result = $is_html ? $is_html($result) : ($is_json ? json_decode($result, $is_array) : $result);
        
        return $result;
    }
    
    /**
     * 获取头部信息
     * @return string
     */
    public function getHead()
    {
        $result = mb_substr(static::$data, 0, static::$headerSize);
        
        return $result;
    }

    /**
     * 获取错误号
     * @return int
     */
    public function getErrno()
    {
        return static::$errno;
    }

    /**
     * 获取错误信息
     * @return string
     */
    public function getError()
    {
        return static::$errno;
    }
    
    /**
     * 设置curl参数
     *
     * @param string $uri
     * @param array  $opt
     */
    private static function setOpt($uri, $opt = array())
    {
        $options = array(
            CURLOPT_URL            => $uri, // 访问地址
            CURLOPT_RETURNTRANSFER => true, # 返回原生数据
            CURLOPT_FRESH_CONNECT  => true, # 强制获取一个连接,而不是缓存连接
            CURLOPT_HEADER         => true, # 启用时将输出header头
            CURLOPT_CONNECTTIMEOUT => static::$timeout, # 设置超时时间
            CURLOPT_TIMEOUT        => static::$timeout, # 允许curl执行的最大时间
            // CURLOPT_PORT           => 80,
            CURLOPT_SSL_VERIFYPEER => 0, // 关闭curlhttps验证
            CURLOPT_SSL_VERIFYHOST => 2, // 关闭curlhttps验证
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_0,
        );
        
        if (is_array($opt)) {
            foreach ($opt as $key => $value) {
                $options[ $key ] = $value;
            }
        }
        
        curl_setopt_array(static::$ch, $options);
    }
    
    /**
     * 静态调用
     *
     * @param string $name
     * @param array  $arguments
     *
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        $arguments[0] = isset($arguments[0]) ? $arguments[0] : '';
        $arguments[1] = isset($arguments[1]) ? $arguments[1] : array();
        $arguments[2] = isset($arguments[2]) ? $arguments[2] : array();
        $arguments[3] = isset($arguments[3]) ? $arguments[3] : array();
        $arguments[4] = strtolower($name); // 设置提交方式
        
        static::$instance = new self();
        
        return call_user_func(array('self', 'exec'), ...$arguments); // 可变参数提交
    }
}