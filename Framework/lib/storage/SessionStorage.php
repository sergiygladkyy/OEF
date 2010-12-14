<?php

require_once('lib/storage/Storage.php');

class SessionStorage extends Storage
{
   static protected
      $idRegenerated = false,
      $started       = false;

   /**
    * Initialize storage object
    * 
    * Available options:
    *
    *  - session_name:            The cookie name (oef by default)
    *  - session_id:              The session id (null by default)
    *  - auto_start:              Whether to start the session (true by default)
    *  - session_cookie_lifetime: Cookie lifetime
    *  - session_cookie_path:     Cookie path
    *  - session_cookie_domain:   Cookie domain
    *  - session_cookie_secure:   Cookie secure
    *  - session_cookie_httponly: Cookie http only (only for PHP >= 5.2)
    *
    * @param array& $options
    */
   public function initialize(array& $options = array())
   {
      $cookieDef = session_get_cookie_params();
      
      $options = array_merge(array(
         'session_name'            => 'oef',
         'session_id'              => null,
         'auto_start'              => true,
         'session_cookie_lifetime' => $cookieDef['lifetime'],
         'session_cookie_path'     => $cookieDef['path'],
         'session_cookie_domain'   => $cookieDef['domain'],
         'session_cookie_secure'   => $cookieDef['secure'],
         'session_cookie_httponly' => isset($cookieDef['httponly']) ? $cookieDef['httponly'] : false,
         'session_cache_limiter'   => 'none',
      ), $options);
      
      parent::initialize($options);

      // Set session name
      $sessionName = $this->options['session_name'];

      session_name($sessionName);

      if (!(boolean) ini_get('session.use_cookies') && $sessionId = $this->options['session_id'])
      {
         session_id($sessionId);
      }

      $lifetime = $this->options['session_cookie_lifetime'];
      $path     = $this->options['session_cookie_path'];
      $domain   = $this->options['session_cookie_domain'];
      $secure   = $this->options['session_cookie_secure'];
      $httpOnly = $this->options['session_cookie_httponly'];
      
      session_set_cookie_params($lifetime, $path, $domain, $secure, $httpOnly);

      if (null !== $this->options['session_cache_limiter'])
      {
         session_cache_limiter($this->options['session_cache_limiter']);
      }

      if ($this->options['auto_start'] && !self::$started)
      {
         session_start();
         self::$started = true;
      }
   }

   /**
    * (non-PHPdoc)
    * @see Storage#read($key, $default)
    */
   public function read($key, $default = null)
   {
      return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
   }
   
   /**
    * (non-PHPdoc)
    * @see Storage#write($key, $data)
    */
   public function write($key, $data)
   {
      $_SESSION[$key] = $data;
   }

   /**
    * (non-PHPdoc)
    * @see Storage#remove($key)
    */
   public function remove($key)
   {
      unset($_SESSION[$key]);
   }

   /**
    * (non-PHPdoc)
    * @see Storage#regenerate($destroy)
    */
   public function regenerate($destroy = true)
   {
      if (self::$idRegenerated)
      {
         return;
      }

      session_regenerate_id($destroy);

      self::$idRegenerated = true;
   }

   /**
    * (non-PHPdoc)
    * @see Storage#shutdown()
    */
   public function shutdown()
   {
      session_write_close();
   }
}
