<?php

class Response
{
   protected
      $headers     = array(),
      $cookies     = array(),
      $content     = null,
      $statusCode  = '200',
      $statusText  = 'OK',
      $options     = array();

   protected static  $statusTexts = array(
      '100' => 'Continue',
      '101' => 'Switching Protocols',
      '200' => 'OK',
      '201' => 'Created',
      '202' => 'Accepted',
      '203' => 'Non-Authoritative Information',
      '204' => 'No Content',
      '205' => 'Reset Content',
      '206' => 'Partial Content',
      '300' => 'Multiple Choices',
      '301' => 'Moved Permanently',
      '302' => 'Found',
      '303' => 'See Other',
      '304' => 'Not Modified',
      '305' => 'Use Proxy',
      '306' => '(Unused)',
      '307' => 'Temporary Redirect',
      '400' => 'Bad Request',
      '401' => 'Unauthorized',
      '402' => 'Payment Required',
      '403' => 'Forbidden',
      '404' => 'Not Found',
      '405' => 'Method Not Allowed',
      '406' => 'Not Acceptable',
      '407' => 'Proxy Authentication Required',
      '408' => 'Request Timeout',
      '409' => 'Conflict',
      '410' => 'Gone',
      '411' => 'Length Required',
      '412' => 'Precondition Failed',
      '413' => 'Request Entity Too Large',
      '414' => 'Request-URI Too Long',
      '415' => 'Unsupported Media Type',
      '416' => 'Requested Range Not Satisfiable',
      '417' => 'Expectation Failed',
      '500' => 'Internal Server Error',
      '501' => 'Not Implemented',
      '502' => 'Bad Gateway',
      '503' => 'Service Unavailable',
      '504' => 'Gateway Timeout',
      '505' => 'HTTP Version Not Supported',
   );
  
   protected static $instance = null;
   
   /**
    * Create new instance
    *
    * @param array $options
    * @return this
    */
   public static function createInstance(array $options = array())
   {
      if (is_null(self::$instance))
      {
         self::$instance = new self($options);
      }

      return self::$instance;
   }

   /**
    * Get this instance
    *
    * @throws Exception
    * @return this
    */
   public static function getInstance()
   {
      if (is_null(self::$instance))
      {
         throw new Exception(__METHOD__.": Instance is not exists");
      }

      return self::$instance;
   }

   /**
    * This is not clonable object
    */
   private function __clone() {;}
   
   /**
    * Constructor
    * 
    * @param array& $options
    */
   protected function __construct(array& $options = array())
   {
      $this->initialize($options);
   }
   
   /**
    * Initialize Responce object
    * 
    * @param array& $options
    * @return boolean
    */
   protected function initialize(array& $options = array())
   {
      $this->options = array_merge(array('protocol' => 'HTTP/1.0'), $options);
      
      return true;
   }
   
   /**
    * Set HTTP header
    * 
    * @param string $name
    * @param string $value
    * @param boolean $replace
    * @return boolean
    */
   public function setHttpHeader($name, $value, $replace = true)
   {
      if (!$replace && isset($this->headers[$name]))
      {
         return false;
      }
      
      $this->headers[$name] = (string) $value;
      
      return true;
   }
   
   /**
    * Get HTTP header
    * 
    * @param string $name
    * @param mixed $default
    * @return string or null
    */
   public function getHttpHeader($name, $default = null)
   {
      return isset($this->headers[$name]) ? $this->headers[$name] : $default;
   }
   
   /**
    * Remove HTTP header
    * 
    * @param string $name
    * @return void
    */
   public function removeHttpHeader($name)
   {
      unset($this->headers[$name]);
   }
   
   /**
    * Set content
    * 
    * @param string $content
    * @param boolean $replace
    * @return boolean
    */
   public function setContent($content, $replace = true)
   {
      if (!$replace && !is_null($content)) return false;
      
      $this->content = (string) $content;
      
      return true;
   }
   
   /**
    * Get content
    * 
    * @return string or null
    */
   public function getContent()
   {
      return $this->content;
   }
   
   /**
    * Set status code
    * 
    * @param string $code
    * @param string $text
    * @return void
    */
   public function setStatusCode($code, $text = null)
   {
      $this->statusCode = $code;
      $this->statusText = is_null($text) ? (isset(self::$statusTexts[$code]) ? self::$statusTexts[$code] : '') : $text;
   }
   
   /**
    * Get status code
    * 
    * @return string
    */
   public function getStatusCode()
   {
      return $this->statusCode;
   }
   
   /**
    * Get status text
    * 
    * @return string
    */
   public function getStatusText()
   {
      return $this->statusText;
   }
   
   /**
    * Set cookies. Params description see in PHP manual for standard PHP function setrawcookie(..)
    * 
    * @throws Exception
    * @param $name
    * @param $value
    * @param $expire
    * @param $path
    * @param $domain
    * @param $secure
    * @param $httpOnly
    * @return void
    */
   public function setCookie($name, $value, $expire = null, $path = '/', $domain = '', $secure = false, $httpOnly = false)
   {
      if (!is_null($expire))
      {
         if (!is_numeric($expire))
         {
            $expire = strtotime($expire);
            
            if ($expire === false || $expire == -1)
            {
               throw new Exception('Expire parameter is wrong');
            }
         }
         else $expire = (int) $expire;
      }

      $this->cookies[$name] = array(
         'name'     => $name,
         'value'    => $value,
         'expire'   => $expire,
         'path'     => $path,
         'domain'   => $domain,
         'secure'   => $secure ? true : false,
         'httpOnly' => $httpOnly
      );
   }
   
   /**
    * Get HTTP cookie
    * 
    * @param string $name
    * @param mixed $default
    * @return mixed
    */
   public function getCookie($name, $default = null)
   {
      return isset($this->cookies[$name]) ? $this->cookies[$name] : $default;
   }
   
   
   
   /**
    * Send responce
    * 
    * @return void
    */
   public function send()
   {
      $this->sendHttpHeaders();
      $this->sendContent();
   }
   
   /**
    * Send HTTP headers
    * 
    * @return void
    */
   public function sendHttpHeaders()
   {
      // Status
      header($this->options['protocol'].' '.$this->statusCode.' '.$this->statusText);
      
      // Headers
      foreach ($this->headers as $name => $value)
      {
         header($name.': '.$value);
      }
      
      // Cookies
      foreach ($this->cookies as $cookie)
      {
         setrawcookie($cookie['name'], $cookie['value'], $cookie['expire'], $cookie['path'], $cookie['domain'], $cookie['secure'], $cookie['httpOnly']);
      }
   }
   
   /**
    * Send content
    * 
    * @return void
    */
   public function sendContent()
   {
      echo $this->content;
   }
}