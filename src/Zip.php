<?php

namespace tinyphp\helper;

use ZipArchive;

class Zip extends \ZipArchive
{
    static protected $_instance = null; // 类对象

    static protected $error = ''; // 保存错误信息

    /**
     * 初始化
     * @return \tinyphp\Zip
     */
    static public function getInstance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new static;
        }

        return self::$_instance;
    }

    /**
     * 压缩文件，压缩文件和文件夹(1、穿文件夹目录，压缩下面的所有；2、需要压缩的文件，可以是数组，也可以是单个文件)
     * @param  string|array $path   需要压缩的文件或文件夹
     * @param  string $output 压缩后的文件名
     * @param  string $ext    压缩文件类型
     * @return string|bool
     */
    public function zzip( $path, $output = '', $ext = 'zip' )
    {
        // 构建压缩文件名
        $filename = ($output ? $output : ('johnxu_' . time())) . '.' . trim($ext, '.');
        $res = $this->open($filename, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        if ( $res === true ) {
            // 判断$file是数组还是字符串
            if ( is_array( $path ) ) {
                // 数组
                foreach($path as $item) {
                    if (is_dir($item)) {
                        $this->addDir($item);
                    } elseif (is_file($item)) {
                        $this->addFile(trim($item, '.,/'));
                    }
                }
            } else {
                // 字符串，判断是目录还是文件，如果是文件直接压缩，如果是目录，遍历目录再压缩
                if (is_file( $path )) {
                    // 直接压缩
                    $this->addFile($path);
                } elseif (is_dir($path)) {
                    // 遍历目录再压缩
                    $this->addDir($path);
                } else {
                    self::$error = '啥也不是，压缩个毛线啊！';
                    return false;
                }
            }
            return $filename;
        } else {
            self::$error = '压缩失败！';
            return false;
        }
    }

    /**
     * 创建文件夹
     * @param string $path 文件夹路径
     */
    protected function addDir( $path )
    {
        $this->addEmptyDir(iconv('gbk', 'utf-8', trim($path, '.,/')));
        $dir = opendir($path);
        $nodes = array();
        while(($file = readdir($dir)) !== false) {
            if ( $file != '.' && $file != '..' ) {
                $nodes[] = rtrim($path, '/') . '/' . $file;
            }
        }
        closedir($dir);
        foreach ((array)$nodes as $node) {
            //$node = iconv('gbk', 'utf-8', $node);
            echo '<br>' . $node;
            if (is_dir($node)) {
                $this->addDir($node);
            } elseif (is_file($node)) {
                $this->addFile(trim($node, '.,/'));
            }
        }
    }

    /**
     * 解压文件
     * @param  string $file 压缩包
     * @param  string $path 解压地址
     * @return array|bool
     */
    public function unzip( $file, $path = './' )
    {
        if (!is_dir($path)) {
            // 创建目录
            $this->mkdir($path);
        }
        if ( $this->open($file) !== false ) {
            $list = array();
            $this->extractTo($path);
            for ( $i = 0; $i < $this->numFiles; $i++ ) {
                $list[] = $this->getNameIndex($i);
            }
            return $list;
        } else {
            self::$error = '解压失败！';
            return false;
        }
    }

    /**
     * 递归创建目录
     * @param  string $path 路径
     * @return bool
     */
    public function mkdir($path)
    {
        if (!is_dir($path)) {
            if (!$this->mkdir(dirname($path))) {
                return false;
            }
            if (!mkdir($path, 0777)) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * 返回错误信息
     * @method getError
     * @return string
     */
    public function getError()
    {
        return self::$error;
    }
}