<?php
/**
 * Memcache缓存
 * Created by PhpStorm.
 * User: johnxu
 * Date: 2017/4/10
 * Time: 13:14
 */

namespace tinyphp\helper;

class Memcached
{
    static private $config = [];
    static private $_instance = null;
    static private $_mem = null;
    
    /**
     * Memcached constructor.
     *
     * @param array $config
     */
    private function __construct($config = [])
    {
        static::$config = $config ? $config : Config::get('MEMCACHED');
        static::$_mem   = new \Memcache();
        static::addServer();
    }
    
    /**
     * 添加服务器
     */
    private static function addServer()
    {
        if (is_array(static::$config) && !empty(static::$config)) {
            foreach (static::$config as $item) {
                static::$_mem->addServer($item['host'], $item['port'], $item['persistent'], $item['timeout'], $item['retry_interval'], $item['status']);
                if ($item['persistent']) {
                    static::pconnect($item['host'], $item['port'], $item['timeout']);
                } else {
                    static::connect($item['host'], $item['port'], $item['timeout']);
                }
            }
        }
    }
    
    /**
     * 普通连接(完成数据操作后就关闭连接)
     *
     * @param     $host    主机地址
     * @param int $port    端口号
     * @param int $timeout 超时时间
     */
    private static function connect($host, $port = 11211, $timeout = 1)
    {
        static::$_mem->connect($host, $port, $timeout);
    }
    
    /**
     * 持久化连接
     *
     * @param     $host    主机地址
     * @param int $port    端口号
     * @param int $timeout 超时时间
     */
    private static function pconnect($host, $port = 11211, $timeout = 1)
    {
        static::$_mem->pconnect($host, $port, $timeout);
    }
    
    /**
     * 增加一个条目到缓存服务器
     *
     * @param      $name   键名
     * @param      $value  值
     * @param int  $expire 过期时间
     * @param bool $flag   标志
     *
     * @return mixed
     */
    public function set($name, $value, $expire = 0, $flag = false)
    {
        return static::$_mem->set($name, $value, $flag, $expire);
    }
    
    /**
     * 增加一个条目到缓存服务器
     *
     * @param      $name   键名
     * @param      $value  值
     * @param int  $expire 过期时间
     * @param bool $flag   标志
     *
     * @return mixed
     */
    public function add($name, $value, $expire = 0, $flag = false)
    {
        return static::$_mem->add($name, $value, $flag, $expire);
    }
    
    /**
     * 从服务端检回一个元素
     *
     * @param      $name 键名
     * @param bool $flag
     *
     * @return mixed
     */
    public function get($name, $flag = false)
    {
        return static::$_mem->get($name, $flag);
    }
    
    /**
     * 从服务端删除一个元素
     *
     * @param     $name 键名
     * @param int $timeout
     *
     * @return mixed
     */
    public function delete($name, $timeout = 0)
    {
        return static::$_mem->delete($name, $timeout);
    }
    
    /**
     * 增加一个元素的值
     *
     * @param     $name 键名
     * @param int $value
     *
     * @return mixed
     */
    public function increment($name, $value = 1)
    {
        return static::$_mem->increment($name, $value);
    }
    
    /**
     * 替换已经存在的元素的值
     *
     * @param      $name   键名
     * @param      $value  值
     * @param int  $expire 过期时间
     * @param bool $flag   标志
     *
     * @return mixed
     */
    public function replace($name, $value, $expire = 0, $flag = false)
    {
        return static::$_mem->replace($name, $value, $flag, $expire);
    }
    
    /**
     * 开启大值自动压缩
     *
     * @param int   $threshold  控制多大值进行自动压缩的阈值。
     * @param float $min_saving 指定经过压缩实际存储的值的压缩率，支持的值必须在0和1之间。默认值是0.2表示20%压缩率。
     *
     * @return mixed
     */
    public function setCompressThreshold($threshold, $min_saving = 0.2)
    {
        return static::$_mem->setCompressThreshold($threshold, $min_saving);
    }
    
    /**
     * 缓存服务器池中所有服务器统计信息
     * @return mixed
     */
    public function getExtendedStats()
    {
        return static::$_mem->getExtendedStats();
    }
    
    /**
     * 用于获取一个服务器的在线/离线状态
     * @return mixed
     */
    public function getServerStatus()
    {
        return static::$_mem->getServerStatus();
    }
    
    /**
     * 获取服务器统计信息
     * @return mixed
     */
    public function getStats()
    {
        return static::$_mem->getStats();
    }
    
    /**
     * 返回服务器版本信息
     * @return mixed
     */
    public function getVersion()
    {
        return static::$_mem->getVersion();
    }
    
    /**
     * 清洗（删除）已经存储的所有的元素
     * @return mixed
     */
    public function flush()
    {
        return static::$_mem->flush();
    }
    
    /**
     * 实例化
     * @param array $config 配置文件(二位数据)
     *
     * @return null
     */
    public static function instance($config = [])
    {
        if (!static::$_instance instanceof self) {
            static::$_instance = new self($config);
        }
        
        return static::$_instance;
    }
    
    /**
     * 获取memcache对象
     * @return null
     */
    public function getMemObj()
    {
        return static::$_mem ? static::$_mem : null;
    }
    
    /**
     * @param $name
     * @param $arguments
     *
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return call_user_func_array([static::$_mem, $name], $arguments);
    }
    
    /**
     * @param $name
     * @param $arguments
     *
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        return call_user_func_array(['self', 'instance'], $arguments);
    }
}