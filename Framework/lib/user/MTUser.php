<?php

require_once('lib/user/BaseUser.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/ext/include/config.php');

define('MT_DEKIWIKI_API', ExternalConfig::$extconfig['installer']['api']);

class MTUser extends BaseUser
{
   const ANONYMOUS_NAME = 'Anonymous';
   
   protected static $instance;
   
   /**
    * Create current User object
    * 
    * @param string $authtoken
    * @return this
    */
   public static function getCurrent($authtoken)
   {
      if (!defined('MT_DEKIWIKI_API'))
      {
         throw new Exception('MindTouch extensions not initialized');
      }
      
      $uri = MT_DEKIWIKI_API.'/deki/users/current?dream.out.format=php';
      
      $curl = curl_init();
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($curl, CURLOPT_URL, $uri);
      curl_setopt($curl, CURLOPT_HTTPHEADER, array('X-Authtoken: '.$authtoken));
      curl_setopt($curl, CURLOPT_HEADER, 1);
      
      // execute request
      $result   = array();
      $response = curl_exec($curl);
      
      /* BEG Code from DreamPlug::Invoke */
      
      $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
      $type   = curl_getinfo($curl, CURLINFO_CONTENT_TYPE);
      $result['errno'] = curl_errno($curl);
      $result['error'] = curl_error($curl);
      curl_close($curl);

      // header parsing
      $result['headers'] = array();
      // make sure ther response is not empty before trying to parse
      // also make sure there isn't a curl error
      if ($status != 0 && $result['errno'] == 0)
      {
         // split response into header and response body
         do
         {
            list($headers, $response) = explode("\r\n\r\n", $response, 2);
            $headers = explode("\r\n", $headers);
            // First line of headers is the HTTP response code
            $httpStatus = array_shift($headers); // remove!
            // check if there is another header chunk to parse
         } while ($httpStatus == 'HTTP/1.1 100 Continue');

         // put the rest of the headers in an array
         foreach ($headers as $headerLine)
         {
            list($header, $value) = explode(': ', $headerLine, 2);
            //$result['headers'][$header] .= $value."\n";
            // don't acknowledge multiple headers
            $result['headers'][$header] = trim($value);
         }
         // /header parsing
      }

      // check if we need to deserialize
      if (strpos($type, '/php'))
      {
         $response = unserialize($response);
      }

      $result['request'] = array('uri' => $uri, 'body' => /*$content*/'');
      $result['uri'] = $uri;
      $result['body'] = $response;
      $result['status'] = $status;
      $result['type'] = $type;

      /* END Code from DreamPlug::Invoke */

      if ($result['status'] == 200)
      {
         self::$instance = new self($result['body']['user']['username']);
      }
      else
      {
         self::getAnonymous();
      }
      
      return self::$instance;
   }
   
   /**
    * Return Anonymous user object
    * 
    * @return this
    */
   public static function getAnonymous()
   {
      self::$instance = new self(self::ANONYMOUS_NAME);
   }
   
   /**
    * New from array
    * 
    * @param string $username
    * @return void
    */
   protected function __construct($username)
   {
      $this->attributes['username'] = $username;
   }
   
   /**
    * (non-PHPdoc)
    * @see ext/OEF/Framework/lib/user/BaseUser#getRoles()
    */
   public function getRoles()
   {
      return array();
   }
   
   /**
    * (non-PHPdoc)
    * @see ext/OEF/Framework/lib/user/BaseUser#hasRole($role)
    */
   public function hasRole($role)
   {
      return false;
   }
   
   /**
    * (non-PHPdoc)
    * @see ext/OEF/Framework/lib/user/BaseUser#isAnonymous()
    */
   public function isAnonymous()
   {
      return $this->attributes['username'] == self::ANONYMOUS_NAME;
   }
   
   /**
    * (non-PHPdoc)
    * @see ext/OEF/Framework/lib/user/BaseUser#getUsername()
    */
   public function getUsername()
   {
      return $this->attributes['username'];
   }
}