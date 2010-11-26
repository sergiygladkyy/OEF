<?php

class Request
{
   const GET    = 'GET';
   const POST   = 'POST';
   const PUT    = 'PUT';
   const DELETE = 'DELETE';
   const HEAD   = 'HEAD';

   protected
      $options      = array(),
      $content      = null,
      $method       = null,
      $headers      = null,
      $languages    = null,
      $charsets     = null,
      $contentTypes = null,
      $get     = array(),
      $post    = array(),
      $request = array(),
      $cookie  = array(),
      $files   = array();
      
   protected static $instance = null;
   
   /**
    * Create new instance
    *
    * @throws Exception
    * @param array $parameters
    * @param array $options
    * @return this
    */
   public static function createInstance(array $parameters = array(), array $options = array())
   {
      if (is_null(self::$instance))
      {
         self::$instance = new self($parameters, $options);
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
    * @param array& $parameters
    * @param array& $options
    */
   protected function __construct(array& $parameters = array(), array& $options = array())
   {
      $this->initialize($parameters, $options);
   }

   /**
    * Initialize this object.
    * Function can overwrite arrays $_GET, $_POST, $_COOKIE, $_REQUEST.
    * 
    * @param array& $parameters
    * @param array& $options
    * @return void
    */
   public function initialize(array& $parameters = array(), array& $options = array())
   {
      $this->options = array_merge(array('request_info' => 'SERVER'), $options);
      
      if (!empty($parameters))
      {
         // GET
         if (isset($parameters[self::GET]) && is_array($parameters[self::GET]))
         {
            $_GET     = array_merge($_GET, $parameters[self::GET]);
            $_REQUEST = array_merge($_REQUEST, $parameters[self::GET]);
         }
         
         // POST
         if (isset($parameters[self::POST]) && is_array($parameters[self::POST]))
         {
            $_POST    = array_merge($_POST, $parameters[self::POST]);
            $_REQUEST = array_merge($_REQUEST, $parameters[self::POST]);
         }
         
         // COOKIE
         if (isset($parameters[self::COOKIE]) && is_array($parameters[self::COOKIE]))
         {
            $_COOKIE  = array_merge($_COOKIE, $parameters[self::COOKIE]);
            $_REQUEST = array_merge($_REQUEST, $parameters[self::COOKIE]);
         }
      }
      
      // Method
      if (isset($_SERVER['REQUEST_METHOD']))
      {
         switch ($_SERVER['REQUEST_METHOD'])
         {
            case 'GET':
               $this->method = self::GET;
               break;

            case 'POST':
               $this->method = self::POST;
               break;

            case 'PUT':
               $this->method = self::PUT;
               if ('application/x-www-form-urlencoded' === $this->getContentType())
               {
                  parse_str($this->getContent(), $_POST);
               }
               break;

            case 'DELETE':
               $this->method = self::DELETE;
               if ('application/x-www-form-urlencoded' === $this->getContentType())
               {
                  parse_str($this->getContent(), $_POST);
               }
               break;

            case 'HEAD':
               $this->method = self::HEAD;
               break;

            default:
               $this->method = self::GET;
         }
      }
      else
      {
         $this->method = self::GET;
      }
      
      // Parameters
      $this->get     = Utility::escapeRecursive($_GET);
      $this->post    = Utility::escapeRecursive($_POST);
      $this->request = Utility::escapeRecursive($_REQUEST);
      $this->cookie  = Utility::escapeRecursive($_COOKIE);
      $this->files   =& $_FILES;
   
      // Request info
      $this->initializeRequestInfo();
   }
   
   /**
    * Initialize request info variable
    * 
    * @return void
    */
   protected function initializeRequestInfo()
   {
      if ($this->options['request_info'] == 'SERVER')
      {
         $this->headers =& $_SERVER;
      }
      else $this->headers =& $_ENV;
   }


   /**
    * Return options
    *
    * @return array
    */
   public function getOptions()
   {
      return $this->options;
   }

   
   
   /**
    * Gets the request method
    *
    * @return string
    */
   public function getMethod()
   {
      return $this->method;
   }

   /**
    * Sets the request method
    *
    * @throws Exception
    * @param string $method
    * @return void
    */
   public function setMethod($method)
   {
      $method = strtoupper(trim($method));
      
      if (!in_array($method, array(self::GET, self::POST, self::PUT, self::DELETE, self::HEAD)))
      {
         throw new Exception('Invalid request method: '.$method.'.');
      }

      $this->method = $method;
   }
   
   /**
    * Checks if the request method is the given one
    *
    * @param string $method
    * @return boolean true if the current method is the given one, false otherwise
    */
   public function isMethod($method)
   {
      return strtoupper($method) == $this->method;
   }
   
   
   
   /**
    * Get http header
    * 
    * @param string $name
    * @param string $prefix
    * @return string
    */
   public function getHttpHeader($name, $prefix = 'HTTP')
   {
      if ($prefix) $prefix = strtoupper($prefix).'_';
      
      $name = $prefix.strtoupper(strtr($name, '-', '_'));

      return isset($this->headers[$name]) ? $this->headers[$name] : null;
   }
   
   /**
    * Return content type of the current request
    *
    * @return string
    */
   public function getContentType()
   {
      $type = $this->getHttpHeader('Content-Type', null);

      return (false !== ($pos = strpos($type, ';'))) ? substr($type, 0, $pos) : $type;
   }

   /**
    * Return content of the current request
    *
    * @return mixed - string or false
    */
   public function getContent()
   {
      if ($this->content === null)
      {
         if (($this->content = file_get_contents('php://input')) !== false)
         {
            $this->content = trim($this->content);
         }
      }

      return $this->content;
   }
   
   
   
   /**
    * Get $_GET parameters
    * 
    * @return array
    */
   public function getGetParameters()
   {
      return $this->get;
   }

   /**
    * Get $_POST parameters
    * 
    * @return array
    */
   public function getPostParameters()
   {
      return $this->post;
   }

   /**
    * Get $_REQUEST parameters
    * 
    * @return array
    */
   public function getRequestParameters()
   {
      return $this->request;
   }
   
   /**
    * Get $_COOKIE parameters
    * 
    * @return array
    */
   public function getCookies()
   {
      return $this->cookie;
   }
   
   /**
    * Get $_FILES parameters
    *
    * @return array
    */
   public function getFiles()
   {
      return $this->files;
   }
   
   
   /**
    * Returns the value of a GET parameter
    *
    * @param string $name
    * @param string $default
    * @return mixed - parameter value
    */
   public function getGetParameter($name, $default = null)
   {
      if (isset($this->get[$name]))
      {
         return $this->get[$name];
      }
      
      return Utility::getArrayValueByPath($this->get, $name, $default);
   }

   /**
    * Returns the value of a POST parameter
    *
    * @param string $name
    * @param string $default
    * @return mixed - parameter value
    */
   public function getPostParameter($name, $default = null)
   {
      if (isset($this->post[$name]))
      {
         return $this->post[$name];
      }
      
      return Utility::getArrayValueByPath($this->post, $name, $default);
   }

   /**
    * Returns the value of a REQUEST parameter
    *
    * @param string $name
    * @param string $default
    * @return mixed - parameter value
    */
   public function getRequestParameter($name, $default = null)
   {
      if (isset($this->request[$name]))
      {
         return $this->request[$name];
      }
      
      return Utility::getArrayValueByPath($this->request, $name, $default);
   }
   
   /**
    * Gets a cookie value
    *
    * @param string $name
    * @param string $default
    * @return mixed
    */
   public function getCookie($name, $default = null)
   {
      return isset($this->cookie[$name]) ? $this->cookie[$name] : $default;
   }
   
   /**
    * Add GET parameters
    * 
    * @param array $parameters
    * @return void
    */
   public function addGetParameters(array $parameters)
   {
      $this->get     = array_merge($this->get,     $parameters);
      $this->request = array_merge($this->request, $parameters);
      
      $_GET     = array_merge($_GET,     $parameters);
      $_REQUEST = array_merge($_REQUEST, $parameters);
   }
   
   /**
    * Add POST parameters
    * 
    * @param array $parameters
    * @return void
    */
   public function addPostParameters(array $parameters)
   {
      $this->post    = array_merge($this->post,    $parameters);
      $this->request = array_merge($this->request, $parameters);
      
      $_POST    = array_merge($_POST,    $parameters);
      $_REQUEST = array_merge($_REQUEST, $parameters);
   }
   
   /**
    * Add COOKIE parameters
    * 
    * @param array $parameters
    * @return void
    */
   public function addCookieParameters(array $parameters)
   {
      $this->cookie  = array_merge($this->cookie,  $parameters);
      $this->request = array_merge($this->request, $parameters);
      
      $_COOKIE  = array_merge($_COOKIE,  $parameters);
      $_REQUEST = array_merge($_REQUEST, $parameters);
   }
   
   
         
         
   /**
    * Gets a list of languages acceptable by the client browser
    *
    * @return array
    */
   public function getLanguages()
   {
      if (!is_null($this->languages)) return $this->languages;

      if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))
      {
         $this->languages = array_keys($this->splitHttpAcceptHeader($_SERVER['HTTP_ACCEPT_LANGUAGE']));
      }
      else $this->languages = array();
      
      return $this->languages;
   }
   
   /**
    * Gets a list of charsets acceptable by the client browser
    *
    * @return array
    */
   public function getCharsets()
   {
      if (!is_null($this->charsets)) return $this->charsets;

      if (isset($_SERVER['HTTP_ACCEPT_CHARSET']))
      {
         $this->charsets = array_keys($this->splitHttpAcceptHeader($_SERVER['HTTP_ACCEPT_CHARSET']));
      }
      else $this->charsets = array();

      return $this->charsets;
   }

   /**
    * Gets a list of content types acceptable by the client browser
    *
    * @return array
    */
   public function getAcceptableContentTypes()
   {
      if (!is_null($this->contentTypes)) return $this->contentTypes;

      if (isset($_SERVER['HTTP_ACCEPT']))
      {
         $this->contentTypes = array_keys($this->splitHttpAcceptHeader($_SERVER['HTTP_ACCEPT']));
      }
      else $this->contentTypes = array();

      return $this->contentTypes;
   }
   
   /**
    * Split HTTP ACCEPT header
    *
    * @param string $header
    * @return array
    */
   public function splitHttpAcceptHeader($header)
   {
      $values = explode(',', $header);
      $result = array();
      
      foreach ($values as $value)
      {
         if ($pos = strpos($value, ';'))
         {
            $q     = (float) trim(substr($value, strpos($value, '=') + 1));
            $value = substr($value, 0, $pos);
         }
         else $q = 1;

         $result[trim($value)] = $q;
      }

      return arsort($result);
   }
   
   
   
   
   /**
    * Returns current host name
    *
    * @return string
    */
   public function getHost()
   {
      if (isset($this->headers['HTTP_X_FORWARDED_HOST']))
      {
         return $this->headers['HTTP_X_FORWARDED_HOST'];
      }
      
      return isset($this->headers['HTTP_HOST']) ? $this->headers['HTTP_HOST'] : '';
   }
   
   /**
    * Returns referer
    *
    * @return string
    */
   public function getReferer()
   {
      return isset($this->headers['HTTP_REFERER']) ? $this->headers['HTTP_REFERER'] : '';
   }

   /**
    * Returns current script name
    *
    * @return string
    */
   public function getScriptName()
   {
      if (isset($this->headers['SCRIPT_NAME']))
      {
         return $this->headers['SCRIPT_NAME'];
      }
      
      return isset($this->headers['ORIG_SCRIPT_NAME']) ? $this->headers['ORIG_SCRIPT_NAME'] : '';
   }

   /**
    * Returns the remote IP address
    *
    * @return string
    */
   public function getRemoteAddress()
   {
      return $this->headers['REMOTE_ADDR'];
   }


   

   /**
    * Returns true if the request is a XMLHttpRequest
    *
    * @return boolean
    */
   public function isXmlHttpRequest()
   {
      return ($this->getHttpHeader('X_REQUESTED_WITH') == 'XMLHttpRequest');
   }

   /**
    * Returns true if the current request is secure (HTTPS protocol)
    *
    * @return boolean
    */
   public function isSecure()
   {
      if (isset($this->header['HTTPS']) && (strtolower($this->header['HTTPS']) == 'on' || $this->header['HTTPS'] == 1))
      {
         return true;
      }
      
      if (isset($this->header['HTTP_SSL_HTTPS']) && (strtolower($this->header['HTTP_SSL_HTTPS']) == 'on' || $this->header['HTTP_SSL_HTTPS'] == 1))
      {
         return true;
      }
      
      if (isset($this->header['HTTP_X_FORWARDED_PROTO']) && strtolower($this->header['HTTP_X_FORWARDED_PROTO']) == 'https')
      {
         return true;
      }
      
      return false;
   }
   
   
   
   /**
    * Get URI for the current web request.
    *
    * @return string
    */
   public function getUri()
   {
      $uri = isset($this->headers['REQUEST_URI']) ? $this->headers['REQUEST_URI'] : '';
      
      return $this->isAbsUri() ? $uri : $this->getUriPrefix().$uri;
   }

   /**
    * See if the client is using absolute uri
    *
    * @return boolean
    */
   public function isAbsUri()
   {
      return isset($this->headers['REQUEST_URI']) ? preg_match('/^http/i', $this->headers['REQUEST_URI']) : false;
   }

   /**
    * Returns Uri prefix (protocol, hostname and server port)
    *
    * @return string
    */
   public function getUriPrefix()
   {
      if ($this->isSecure())
      {
         $sPort    = '443';
         $protocol = 'https';
      }
      else
      {
         $sPort    = '80';
         $protocol = 'http';
      }

      $host = explode(':', $this->getHost());
      
      if (count($host) == 1)
      {
         $host[] = isset($this->headers['SERVER_PORT']) ? $this->headers['SERVER_PORT'] : '';
      }

      if ($host[1] == $sPort || empty($host[1])) unset($host[1]);
      
      return $protocol.'://'.implode(':', $host);
   }   
}
