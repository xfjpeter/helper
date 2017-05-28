<?php
/**
 * User: johnxu <fsyzxz@163.com>
 * HomePage: http://www.johnxu.net
 * Date: 2017/4/23
 */

namespace tinyphp\helper;
/**
 * Class Response
 * @package tinyphp
 */
class Response
{
    /**
     * 返回json格式的数据接口
     *
     * @param int     $code
     * @param  string $message
     * @param array   $data
     * @param string  $charset
     *
     * @return bool
     */
    public static function json($code = 200, $message, $data = array(), $charset = 'UTF-8')
    {
        # 判断状态码是否是整形
        if (!is_numeric($code)) {
            return false;
        }

        $res = array(
            'code'    => $code,
            'message' => $message
        );
        $res = array_merge($res, (array)$data);
        // 设置返回的页面信息
        header('Content-Type: application/json;charset=' . $charset);
        // 告诉客户端浏览器不使用缓存
        header("Cache-Control: no-cache, must-revalidate");
        // 参数（与以前的服务器兼容）,即兼容HTTP1.0协议
        header("Pragma: no-cache");
        header('HTTP/1.1 ' . $code);
        echo json_encode($res);
        exit;
    }

    /**
     * xml方式返回数据
     * @param int    $code
     * @param string $message
     * @param array  $data
     * @param string $charset
     *
     * @return bool
     */
    public static function xml($code = 200, $message = '', $data = array(), $charset = 'UTF-8')
    {
        // 判断状态码是否是整形
        if (!is_numeric($code)) {
            return false;
        }
        // 设置返回的页面信息
        header('Content-Type: text/xml;charset=' . $charset);
        // 告诉客户端浏览器不使用缓存
        header("Cache-Control: no-cache, must-revalidate");
        // 参数（与以前的服务器兼容）,即兼容HTTP1.0协议
        header("Pragma: no-cache");
        header('HTTP/1.1 ' . $code);
        $xml = "<?xml version='1.0' encoding='UTF-8' ?>";
        $xml .= "<root>";
        $xml .= "<code>{$code}</code>";
        $xml .= "<message>{$message}</message>";
        $xml .= static::dataXml($data);
        $xml .= "</root>";
        echo $xml;
        exit;
    }

    /**
     * 输出html消息
     * @param int    $code
     * @param        $content
     * @param string $charset
     */
    public static function html($code = 200, $content, $charset = 'UTF-8')
    {
        header('HTTP/1.1 ' . $code);
        // 设置返回的页面信息
        header('Content-Type: text/xml;charset=' . $charset);
        # 设置返回的页面信息
        header('Content-Type: text/html;charset=' . $charset);
        # 告诉客户端浏览器不使用缓存
        header("Cache-Control: no-cache, must-revalidate");
        # 告诉客户端浏览器不使用缓存
        header("Cache-Control: no-cache, must-revalidate");
        # 参数（与以前的服务器兼容）,即兼容HTTP1.0协议
        echo $content;
        exit;
    }

    /**
     * 组装xml数据
     *
     * @param array $data
     *
     * @return string
     */
    private static function dataXml($data = array())
    {
        $xml = '';
        foreach ($data as $key => $item) {
            if (!is_numeric($key)) {
                $xml .= "<{$key}>";
                $xml .= is_array($item) ? static::dataXml($item) : $item;
                $xml .= "</{$key}>";
            } else {
                $xml .= "<item key='{$key}'>";
                $xml .= is_array($item) ? static::dataXml($item) : $item;
                $xml .= "</item>";
            }
        }

        return $xml;
    }
}
