<?php
defined('IN_ROOT') || exit('Access Denied');
/**
 * HTTP请求数据核心类
 *
 * @author		LiuRuiTao xpbb@qq.com
 * @version		v1.0
 * @copyright	Copyright (c) 2013,phpk.cn
 * @filesource
 */

class Http
{

    /**
     * curl操作类型
     *
     * @var curl
     */
    const TYPE_CURL = 'Curl';

    /**
     * fsockopen操作类型
     *
     * @var string
     */
    const TYPE_FSOCK = 'Fsock';

    /**
     * 默认操作类型
     *
     * @var string $default_type
     */
    protected static $default_type = null;

    /**
     * 当前使用操作类型
     *
     * @var string
     */
    protected $type;

    /**
     * 驱动
     *
     * @var HttpCurl
     */
    protected $driver;

    /**
     * 客户端信息
     * @var string
     */
    protected static $agent = '';

    /**
     * @param string $type 指定驱动类型
     */
    function __construct($type = null)
    {
        if ($type)
        {
            $this->type = $type;
        }
        elseif (Http::$default_type)
        {
            $this->type = Http::$default_type;
        }
        elseif ( SUPPORT_CURL )
        {
            $this->type = Http::TYPE_CURL;
        }
        else
        {
            $this->type = Http::TYPE_Fsock;
        }
        $this->driver = L($this->type,'http');
    }

    /**
     * 设置$agent
     * @param string $agent
     * @return HttpClient
     */
    public function setAgent($agent = null)
    {
        $this->driver->setAgent($agent);
        return $this;
    }

    /**
     * 设置$cookie
     *
     * @param string $cookie
     * @return HttpClient
     */
    public function setCookies($cookies)
    {
        $this->driver->setCookies($cookies);
        return $this;
    }

    /**
     * 设置$referer
     *
     * @param string $referer
     * @return HttpClient
     */
    public function setReferer($referer)
    {
        $this->driver->setReferer($referer);
        return $this;
    }

    /**
     * 设置请求页面的IP地址
     *
     * @param string $ip
     * @return HttpClient
     */
    public function setIp($ip)
    {
        $this->driver->setIp($ip);
        return $this;
    }

    /**
     * 设置参数
     *
     * @param $key
     * @param $value
     * @return HttpClient
     */
    public function setOption($key, $value)
    {
        $this->driver->setOption($key, $value);

        return $this;
    }

    /**
     * 设置多个列队默认排队数上限
     *
     * @param int $num
     * @return HttpClient
     */
    public function setMultiMaxNum($num=0)
    {
        $this->driver->setMultiMaxNum();
        return $this;
    }

    /**
     * HTTP GET方式请求
     *
     * 支持多并发进程，这样可以大大缩短API请求时间
     *
     * @param string/array $url 支持多个URL
     * @param array $data
     * @param $timeout
     * @return string
     * @return HttpResult 但个URL返回当然内容对象
     * @return Arr 多个URL时将返回一个数组对象
     */
    public function get($url, $timeout = 10)
    {
        $this->driver->get($url, $timeout);
        $data = $this->driver->getResutData();

        if ( is_array($url) )
        {
            # 如果是多个URL
            $result = new Arr();
            foreach ( $data as $key => $item )
            {
                $result[$key] = new HttpResult($item);
            }
        }
        else
        {
            $result = new HttpResult($data);
        }

        return $result;
    }

    /**
     * POST方式请求
     *
     * @param $url
     * @param $data
     * @param $timeout
     * @return HttpResult
     */
    public function post($url, $data=array(), $timeout = 30)
    {
        $time = microtime(true);
        $this->driver->post($url, $data, $timeout);
        $time = microtime(true) - $time;
        $data = $this->driver->getResutData();
        $data['total_time'] = $time;

        return new HttpResult($data);
    }

    public function __call($method, $params)
    {
        if ( method_exists($this->driver, $method) )
        {
            return call_user_func_array(array($this->driver, $method), $params);
        }
    }
}

class HttpResult
{

    protected $data;

    protected $code = 0;

    protected $headers = array();

    protected $cookies = array();

    protected $time = 0;

    public function __construct($data)
    {
        if ( isset($data['code']) ) $this->code = $data['code'];
        if ( isset($data['time']) ) $this->time = $data['time'];
        if ( isset($data['data']) ) $this->data = $data['data'];

        if ( isset($data['header']) && is_array($data['header']) ) foreach ( $data['header'] as $item )
        {
            if ( preg_match('#^([a-zA-Z0-9\-]+): (.*)$#', $item, $m) )
            {
                if ( $m[1] == 'Set-Cookie' )
                {
                    if ( preg_match('#^([a-zA-Z0-9\-_]+)=(.*)$#', $m[2], $m2) )
                    {
                        $this->cookies[$m2[1]] = $m2[2];
                    }
                }
                else
                {
                    $this->headers[$m[1]] = $m[2];
                }
            }
        }
    }

    public function __toString()
    {
        return (string)$this->data();
    }

    public function code()
    {
        return $this->code;
    }

    public function data()
    {
        return $this->data;
    }

    public function time()
    {
        return $this->time;
    }

    public function header($key = null)
    {
        if ( null === $key )
        {
            return $this->headers;
        }
        else
        {
            return $this->headers[$key];
        }
    }

    public function cookie($key = null)
    {
        if ( null === $key )
        {
            return $this->cookies;
        }
        else
        {
            return $this->cookies[$key];
        }
    }
}