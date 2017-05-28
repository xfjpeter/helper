<?php
/**
 * User: johnxu <fsyzxz@163.com> 549716096
 * HomePage: http://www.johnxu.net
 * Date: 2017/5/25
 */

namespace tinyphp\helper;

class Image
{
    protected static $_instance = null;

    protected static $option
        = array(
            'path'    => './thumb/', // 生成的缩略图保存位置
            'resize'  => '1', // 缩略图比列
            'ext'     => 'png', // 生成的缩略图后缀名称
            'isrand'  => false, // 是否随机生成文件名
            'quality' => 100, // 保存的图像质量
        );

    protected static $return
        = array(
            'error'    => '', // 错误说明
            'errno'    => 0, // 错误号
            'path'     => '', // 路径+文件名
            'fileName' => '', // 生成的新文件名（包含后缀）
        );

    /**
     * 构造函数
     * @method __construct
     * @param  array $option 配置文件参数设置
     */
    protected function __construct(array $option = array())
    {
        if (!empty($option)) {
            foreach ($option as $key => $item) {
                if (array_key_exists($key, self::$option)) {
                    self::$option[ $key ] = $item;
                }
            }
        }

        // 判断要保存的图片目录是否存在，如果不存在，创建
        if (!static::checkPath()) {
            static::setError(4, '自动创建目录失败，请手动创建！');

            return false;
        }
    }

    /**
     * 初始化
     * @method getInstance
     * @param  array $option 配置文件参数设置
     * @return \tinyphp\Image
     */
    public static function getInstance(array $option = array())
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new static($option);
        }

        return self::$_instance;
    }

    /**
     * 生成缩略图
     * @param string $originPath 原图路径
     * @param int    $width      缩略图宽度
     * @param int    $height     缩略图高度
     * @param bool   $zoom       是否等比缩放，true等比，false固定缩放
     * @return \tinyphp\Image|bool
     */
    public function thumb($originPath, $width = 300, $height = 300, $zoom = true)
    {
        $width  = $dst_w = intval($width);
        $height = $dst_h = intval($height);

        // 获取源图片属性
        if (!(list($src_w, $src_h, $mime) = self::getImgSize($originPath))) {
            return false;
        }

        // 获取图片类型
        $type = substr(image_type_to_extension($mime), 1);

        // 创建图片函数
        $createFunc = 'imagecreatefrom' . $type;

        // 保存图片函数
        $toFunc = 'image' . $type;

        // 创建文件名
        self::getFileName($originPath, $type);

        /**
         * ↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
         * 图片操作逻辑
         * ↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
         */

        // 如果缩略图的宽度超过了原图高度
        $dst_w = ($dst_w > $src_w) ? $src_w : $width;

        // 如果缩略图的高度超过了原图高度
        $dst_h = ($dst_h > $src_h) ? $src_h : $height;

        if ($zoom) {
            // 等比缩放
            $ratio = $src_w / $src_h;
            if ($dst_w / $dst_h < $ratio) {
                $dst_w = $dst_h * $ratio;
            } else {
                $dst_h = $dst_w / $ratio;
            }
        }

        /**
         * ↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
         * 图片操作逻辑结束
         * ↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
         */

        // 创建源图片资源
        $src_img = $createFunc($originPath);

        // 创建目标图片资源
        $dst_img = imagecreatetruecolor($dst_w, $dst_h);

        // 将图片复制过去
        if (function_exists('imagecopyresampled')) {
            imagecopyresampled($dst_img, $src_img, 0, 0, 0, 0, $dst_w, $dst_h, $src_w, $src_h);
        } else {
            imagecopyresized($dst_img, $src_img, 0, 0, 0, 0, $dst_w, $dst_h, $src_w, $src_h);
        }

        // 保存图片
        if ($toFunc($dst_img, self::$return['path'], self::$option['quality'])) {
            imagedestroy($dst_img);
            imagedestroy($src_img);

            return self::$_instance;
        }

        return false;

    }

    /**
     * 图片翻转
     * @param string $originPath 需要翻转的图片路径
     * @param string $mode       翻转类型（x：水平翻转，y：垂直翻转）
     * @return \tinyphp\Image|bool
     */
    public function flip($originPath, $mode = 'y')
    {
        // 获取源图片属性
        if (!(list($src_w, $src_h, $mime) = self::getImgSize($originPath))) {
            return false;
        }

        // 获取图片类型
        $type = substr(image_type_to_extension($mime), 1);

        // 创建图片函数
        $createFunc = 'imagecreatefrom' . $type;

        // 保存图片函数
        $toFunc = 'image' . $type;

        // 创建文件名
        self::getFileName($originPath, $type);

        $src_img = $createFunc($originPath);
        $dst_img = imagecreatetruecolor($src_w, $src_h);
        if (strtolower($mode) == 'y') { // 垂直翻转
            for ($i = 0; $i < $src_w; $i++) {
                imagecopy($dst_img, $src_img, $src_w - $i - 1, 0, $i, 0, 1, $src_h);
            }
        } else { // 水平翻转
            for ($i = 0; $i < $src_h; $i++) {
                imagecopy($dst_img, $src_img, 0, $src_h - $i - 1, 0, $i, $src_w, 1);
            }
        }

        // 保存图片
        if ($toFunc($dst_img, self::$return['path'], self::$option['quality'])) {
            imagedestroy($dst_img);
            imagedestroy($src_img);

            return self::$_instance;
        }

        return false;
    }

    /**
     * 创建文件名
     * @param string $originPath
     * @param string $type
     */
    private static function getFileName($originPath, $type)
    {
        // 新文件名称
        $rand = mb_substr(md5('john' . time() . uniqid()), 0, 11);

        self::$return['fileName'] = static::$option['isrand'] ? ($rand . '.' . $type) : pathinfo($originPath, PATHINFO_FILENAME) . '.' . $type;

        // 带路径的文件名称
        self::$return['path'] = rtrim(self::$option['path'], '/') . '/' . self::$return['fileName'];
    }

    /**
     * 检查源图片是否正确
     * @method getImgSize
     * @param  string $originPath 源图片路径
     * @return bool
     */
    private static function getImgSize($originPath)
    {
        if ($imgsize = getimagesize($originPath)) {
            return $imgsize;
        } else {
            self::setError(1, '读取源图片出错！');

            return false;
        }
    }

    /**
     * 获取结果
     * @param $key
     * @return bool|mixed
     */
    public function get($key)
    {
        if (array_key_exists($key, static::$return)) {
            return static::$return[ $key ];
        }

        return false;
    }

    /**
     * 设置错误信息
     * @param int    $errno 错误号
     * @param string $error 错误信息
     */
    private static function setError($errno = 0, $error = '')
    {
        self::$return['errno'] = $errno;
        self::$return['error'] = $error;
    }

    /**
     * 检测要保存的图片文件目录是否存在
     * @return bool
     */
    private static function checkPath()
    {
        if (!is_dir(static::$option['path'])) {
            // 创建
            if (!static::mkdirs(static::$option['path'])) {
                return false;
            }
        }

        return true;
    }

    /**
     * 创建目录
     * @param string $path
     * @return bool
     */
    static private function mkdirs($path)
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

    // 添加水印

    // 等比缩放
}
