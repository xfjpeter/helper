<?php

/**
 * User: johnxu <fsyzxz@163.com> 549716096
 * HomePage: http://www.johnxu.net
 * Date: 2017/5/15
 */

namespace tinyphp\helper;

class Cache
{
    static private $instance = null;
    static private $config
        = array(
            'path'   => './cache/', // 缓存保存路径
            'expire' => 10, // 缓存过期时间（单位s）,如果为0表示永不过期
            'ext'    => '.cache', // 缓存后缀名称
        );
    static private $prefix = 'john_'; // 文件前缀

    /**
     * Cache constructor.
     * @param array $config
     */
    private function __construct($config = array())
    {
        foreach ($config as $key => $item) {
            if (array_key_exists($key, static::$config)) {
                static::$config[ $key ] = $item;
            }
        }
    }

    /**
     * 初始化类
     * @param array $config 配置文件
     * @return \tinyphp\Cache
     */
    static public final function getInstance($config = array())
    {
        if (!static::$instance instanceof self) {
            static::$instance = new self($config);
        }

        return static::$instance;
    }

    /**
     * 写入缓存
     * @param string $key
     * @param mixed  $value
     * @param int    $expire
     * @return \tinyphp\Cache
     */
    public function write($key, $value, $expire = 10)
    {
        $expire = !empty($expire) ? $expire : static::$config['expire'];

        if (!is_dir(static::$config['path'])) { // 检查目录是否存在，不存在创建
            static::mkdirs(static::$config['path']);
        }

        // 创建文件名称
        $fileName = md5(static::$prefix . $key) . static::$config['ext'];

        // 将数据进行json格式化转换
        $data = array(
            'data'   => $value,
            'expire' => $expire
        );
        $data = json_encode($data);

        // 将数据写入文件中
        if (file_put_contents(static::$config['path'] . $fileName, $data)) {
            return static::$instance;
        } else {
            return false;
        }
    }

    /**
     * 读取缓存数据
     * @param string $key
     * @param bool   $isJson 是返回json还是返回object
     * @return bool|mixed|null|string
     */
    public function read($key, $isJson = true)
    {
        // 获取文件名
        $file = static::$config['path'] . md5(static::$prefix . $key) . static::$config['ext'];
        if (!file_exists($file)) {
            return null;
        }

        // 读取文件内容
        $data = file_get_contents($file);
        $res  = json_decode($data);

        // 获取文件创建的时间
        $filemtime = filemtime($file);

        if ( static::$config['expire'] !== 0 ) {
            if ((time() - $filemtime) > $res->expire) {
                // 文件过期了，删除文件
                unlink($file);

                return null;
            }
        }

        $data = json_decode($data, $isJson);
        if ($isJson) { // 去除多余数据
            unset($data['expire']);
            return $data['data'];
        } else {
            unset($data->expire);
            return $data->data;
        }
    }

    /**
     * 递归创建目录
     * @param string $path
     * @return bool
     */
    static private final function mkdirs($path)
    {
        if (!is_dir($path)) {
            if (!static::mkdirs(dirname($path))) {
                return false;
            }
            if (!mkdir($path, 0777)) {
                return false;
            }
        }

        return true;
    }
}