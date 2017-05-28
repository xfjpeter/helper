<?php
/**
 * User: johnxu <fsyzxz@163.com> 549716096
 * HomePage: http://www.johnxu.net
 * Date: 2017/4/21
 */

/**
 * 主要功能：上传图片、文件等（先实现单文件上传，后面做多文件上传）
 * 注意：手机上传和电脑上传获取的type类型不一致
 */

namespace tinyphp\helper;

class Upload
{
    // 配置文件
    static public $config
        = array(
            'size'   => 2 * 1024 * 1024, // 上传文件大小限制
            'ext'    => array('png', 'jpg', 'gif', 'jpeg'), // 上传文件后缀名限制
            'path'   => './uploads/', // 上传文件文件夹
            'isrand' => true, // 是否随机生成文件名
        );

    // 文件的属性
    static private $origin
        = array(
            'fileName'   => '', // 新文件名
            'path'       => '', // 路径+文件名
            'originName' => '', // 上传文件名
            'size'       => 0, // 上传文件的大小
            'type'       => '', // 上传文件的类型（例如：image/png）
            'ext'        => '', // 上传文件的后缀名称（不包含.）
            'tmp'        => '', // 临时文件的名称
        );

    static private $errno = 0; // 保存错误号
    static private $error = ''; // 保存错误信息

    static private $instance = null; // 类对象

    /**
     * 构造函数
     * @param array $config 配置参数
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
     * 初始化
     * @param array $config 配置参数
     * @return \tinyphp\Upload
     */
    static public function getInstance($config = array())
    {
        if (!static::$instance instanceof self) {
            static::$instance = new self($config);
        }

        return static::$instance;
    }

    /**
     * 上传文件（单个）
     * @param string $fileObjName 表单中file字段的name值
     * @return \tinyphp\Upload|bool
     */
    public function save($fileObjName)
    {
        $file = $_FILES[ $fileObjName ];

        if (empty($file)) { // 验证FILES是否为空，确保是否有文件上传
            static::setError(4, '没有文件被上传！');

            return false;
        }

        // 将上传文件的属性赋值给配置中
        static::$origin['originName'] = iconv('UTF-8', 'GBK', $file['name']);
        static::$origin['tmp']        = $file['tmp_name'];
        static::$origin['size']       = $file['size'];
        static::$origin['type']       = $file['type'];

        // 检查上传文件目录是否存在
        if (!static::checkPath()) {
            static::setError(10, '上传文件目录不存在！');

            return false;
        }


        // 验证上传文件是否有错误
        if (static::checkError($file['error'])) {
            return false;
        }

        // 验证文件大小
        if (!static::checkSize()) {
            return false;
        }

        // 验证文件后缀名是否符合要求
        if (!static::checkExt()) {
            return false;
        }

        // 判断是否通过post方式上传的
        if (!is_uploaded_file($file['tmp_name'])) {
            static::setError(9, '非法上传文件！');

            return false;
        }

        // 设置上传文件新的名字
        $rand                       = 'john' . time() . uniqid();
        static::$origin['fileName'] = static::$config['isrand'] ? (md5($rand) . '.' . static::$origin['ext']) : (static::$origin['originName']);

        // 设置完整路径
        static::$origin['path'] = static::$config['path'] . static::$origin['fileName'];

        // 移动上传文件
        if (!move_uploaded_file(static::$origin['tmp'], static::$origin['path'])) {
            static::setError(10, '移动文件失败！');

            return false;
        }

        return static::$instance;

    }

    /**
     * 获取结果
     * @param $key
     * @return bool|mixed
     */
    public function get($key)
    {
        if (array_key_exists($key, static::$origin)) {
            return static::$origin[ $key ];
        }

        return false;
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
        return static::$error;
    }
    
    /**
     * 检测上传文件目录是否存在
     * @return bool
     */
    static private function checkPath()
    {
        if (!is_dir(static::$config['path'])) {
            // 创建
            if (!static::mkdirs(static::$config['path'])) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * 创建目录
     *
     * @param string $path
     *
     * @return bool
     */
    static private function mkdirs($path)
    {
        if (!is_dir($path)) {
            if (!self::mkdirs(dirname($path))) {
                return false;
            }
            if (!mkdir($path, 0777)) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * 检测后缀名是否正确
     * @return bool
     */
    static private function checkExt()
    {
        $pathInfo              = pathinfo(static::$origin['originName']);
        static::$origin['ext'] = strtolower($pathInfo['extension']);
        if (!in_array(static::$origin['ext'], static::$config['ext'])) {
            static::setError(8, '上传文件类型不一致！');
            
            return false;
        }
        
        return true;
    }
    
    /**
     * 验证文件大小
     * @return bool
     */
    static private function checkSize()
    {
        if (static::$origin['size'] > static::$config['size']) {
            static::setError(1, '上传文件过大，不能超过：' . (static::$config['size'] / 1024 / 1024) . 'MB');
            
            return false;
        }
        
        return true;
    }
    
    /**
     * 验证上传文件是否正确
     *
     * @param int $errno 上传文件错误号
     *
     * @return int
     */
    static private function checkError($errno)
    {
        switch ($errno) {
            case 1:
                static::$error = '上传文件过大，不能超过：' . (static::$config['size'] / 1024 / 1024) . 'MB';
                break;
            case 2:
                static::$error = '上传文件的大小超过了 HTML 表单中 MAX_FILE_SIZE 选项指定的值！';
                break;
            case 3:
                static::$error = '文件只有部分被上传！';
                break;
            case 4:
                static::$error = '没有文件被上传！';
                break;
            case 6:
                static::$error = '找不到临时文件夹！';
                break;
            case 7:
                static::$error = '文件写入失败！';
                break;
        }
        static::$errno = $errno;
        
        return $errno;
    }
    
    /**
     * 设置错误信息
     *
     * @param int    $errno 错误号
     * @param string $error 错误信息
     */
    static private function setError($errno, $error)
    {
        static::$errno = $errno;
        static::$error = $error;
    }
}

