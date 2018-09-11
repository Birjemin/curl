<?php
/**
 * Created by PhpStorm.
 * User: birjemin
 * Date: 09/08/2018
 * Time: 11:13
 */
namespace Birjemin\Curl;


use Exception;

/**
 * Class Curl
 * @package Birjemin\Curl
 */
class Curl
{
    const METHOD_POST       = 'POST';
    const METHOD_PUT        = 'PUT';
    const METHOD_DELETE     = 'DELETE';

    const CONTENT_TYPE_JSON = 'Content-Type: application/json';

    private $post = [];
    private $retry          = 0;
    private $custom         = [];
    private $option         = [
        'CURLOPT_HEADER'         => 0,
        'CURLOPT_TIMEOUT'        => 30,
        'CURLOPT_ENCODING'       => '',
        'CURLOPT_IPRESOLVE'      => 1,
        'CURLOPT_RETURNTRANSFER' => true,
        'CURLOPT_SSL_VERIFYPEER' => false,
        'CURLOPT_CONNECTTIMEOUT' => 10,
    ];

    private $info;
    private $data;
    private $error;
    private $message;
    private $method         = self::METHOD_POST;

    private static $instance;

    /**
     * Instance
     * @return self
     */
    public static function init()
    {
        (self::$instance === null) && self::$instance = new self;
        return self::$instance;
    }

    /**
     * Task info
     *
     * @return array
     */
    public function info()
    {
        return $this->info;
    }

    /**
     * Result Data
     *
     * @return string
     */
    public function data()
    {
        return $this->data;
    }

    /**
     * Error status
     *
     * @return integer
     */
    public function error()
    {
        return $this->error;
    }

    /**
     * Error message
     *
     * @return string
     */
    public function message()
    {
        return $this->message;
    }

    /**
     * @param $data
     * @param null $value
     *
     * @return Curl
     */
    public function put($data, $value = null)
    {
        $this->method = self::METHOD_PUT;
        return $this->post($data, $value);
    }

    /**
     * @param $data
     * @param null $value
     *
     * @return Curl
     */
    public function delete($data, $value = null)
    {
        $this->method = self::METHOD_DELETE;
        return $this->post($data, $value);
    }

    /**
     * Set POST data
     *
     * @param array|string $data
     * @param null|string $value
     *
     * @return self
     */
    public function post($data, $value = null)
    {
        is_array($data)
            ? $this->post += $data
            : ($value === null ? $this->post = $data : $this->post[$data] = $value);
        return $this;
    }

    /**
     * Request URL
     *
     * @param string $url
     *
     * @return self
     * @throws Exception
     */
    public function url($url)
    {
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            return $this->set('CURLOPT_URL', $url)->process();
        }
        throw new Exception('Target URL is required.', 500);
    }

    /**
     * Set option
     *
     * @param array|string $item
     * @param null|string $value
     *
     * @return self
     */
    public function set($item, $value = null)
    {
        is_array($item) ? $this->custom += $item : $this->custom[$item] = $value;
        return $this;
    }

    /**
     * Set retry times
     *
     * @param int $times
     *
     * @return self
     */
    public function retry($times = 0)
    {
        $this->retry = $times;
        return $this;
    }

    /**
     * Task process
     *
     * @param int $retry
     *
     * @return self
     */
    private function process($retry = 0)
    {
        $ch            = curl_init();
        $option        = array_merge($this->option, $this->custom);
        foreach ($option as $key => $val) {
            if (is_string($key)) {
                $key = constant(strtoupper($key));
            }
            curl_setopt($ch, $key, $val);
        }
        if ($this->post) {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this->method);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->getData());
        }
        $this->data    = (string)curl_exec($ch);
        $this->info    = curl_getinfo($ch);
        $this->error   = curl_errno($ch);
        $this->message = $this->error ? curl_error($ch) : '';
        curl_close($ch);
        if ($this->error && $retry < $this->retry) {
            $this->process($retry + 1);
        }
        $this->post    = [];
        $this->retry   = 0;
        return $this;
    }

    /**
     * @return string
     */
    private function getData()
    {
        if (isset($this->custom['CURLOPT_HTTPHEADER']) && is_array($this->custom['CURLOPT_HTTPHEADER'])) {
            foreach ($this->custom['CURLOPT_HTTPHEADER'] as $header) {
                if ($header == self::CONTENT_TYPE_JSON) {
                    return json_encode($this->convert($this->post));
                }
            }
        }
        return http_build_query($this->convert($this->post));
    }

    /**
     * Convert array
     *
     * @param array $input
     * @param string $pre
     *
     * @return array
     */
    private function convert($input, $pre = null)
    {
        if (is_array($input)) {
            $output    = [];
            foreach ($input as $key => $value) {
                $index = is_null($pre) ? $key : "{$pre}[{$key}]";
                is_array($value) ? $output += $this->convert($value, $index) : $output[$index] = $value;
            }
            return $output;
        }
        return $input;
    }
}
