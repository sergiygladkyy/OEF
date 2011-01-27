<?php

require_once('lib/user/BaseUser.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/ext/include/config.php');

define('MT_DEKIWIKI_API', ExternalConfig::$extconfig['installer']['api']);

class MTUser extends BaseUser
{
   const ANONYMOUS_NAME = 'Anonymous';
   
   /**
    * Create current User object by authtoken
    * 
    * @param string $authtoken
    * @return this
    */
   static public function createInstance($authtoken)
   {
      if (is_object(self::$instance))
      {
         throw new Exception('User alredy exists', 1);
      }
      
      self::createUser(array('authtoken' => $authtoken));
      
      return self::$instance;
   }
   
   /**
    * Create current User object by id
    * 
    * @param int $userId
    * @return this
    */
   static public function createInstanceById($userId)
   {
      if (is_object(self::$instance))
      {
         throw new Exception('User alredy exists', 1);
      }
      
      self::createUser(array('id' => $userId));
      
      return self::$instance;
   }
   
   /**
    * Create User object by parameters
    * 
    * @param array $parameters
    * @return void
    */
   static private function createUser(array $parameters)
   {
      if (!defined('MT_DEKIWIKI_API'))
      {
         throw new Exception('MindTouch extensions not initialized', 3);
      }
      
      $curl = curl_init();
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
      
      if (!isset($parameters['id']))
      {
         $uri = MT_DEKIWIKI_API.'/deki/users/current?dream.out.format=php';
         $authtoken = isset($parameters['authtoken']) ? $parameters['authtoken'] : '';
         
         curl_setopt($curl, CURLOPT_HTTPHEADER, array('X-Authtoken: '.$authtoken));
      }
      else $uri = MT_DEKIWIKI_API.'/deki/users/'.$parameters['id'].'?dream.out.format=php';
      
      curl_setopt($curl, CURLOPT_URL, $uri);
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

      if ($result['status'] == 200 && $result['body']['user']['username'] != self::ANONYMOUS_NAME)
      {
         $result =& $result['body']['user'];
         
         $attributes  = array('username' => $result['username']);
         $roles       = array();
         
         // Roles
         if (!empty($result['groups']['group']))
         {
            $groups =& $result['groups']['group'];
            
            if (!isset($groups['@id']))
            {
               foreach ($groups as $group) $roles[] = $group['groupname'];
            }
            else $roles[] = $groups['groupname'];
         }
         
         self::$instance = new self($attributes, true, $roles);
      }
      else
      {
         $attributes  = array('username' => self::ANONYMOUS_NAME);
         
         self::$instance = new self($attributes);
      }
   }
   
   /**
    * New from array
    * 
    * @param string $username
    * @return void
    */
   protected function __construct(array& $attributes = array(), $authenticated = false, array& $roles = array())
   {
      $this->attributes    = $attributes;
      $this->roles         = $roles;
      $this->authenticated = $authenticated;
      $this->isAdmin       = in_array(SystemConstants::ADMIN_ROLE, $this->roles);
      
      if ($authenticated)
      {
         $container = Container::getInstance();
         $CManager  = $container->getConfigManager();
         $db    = $container->getDBManager();
         $dbmap = $CManager->getInternalConfiguration('db_map');
         $table = $dbmap['catalogs']['SystemUsers']['table'];
         
         $query = "SELECT count(*) as 'cnt' FROM `".$table."` WHERE `User`='".$this->attributes['username']."' AND `AuthType`='MTAuth'";
         
         if (null !== ($row = $db->loadAssoc($query)) && $row['cnt'] == 0)
         {
            $user  = $container->getModel('catalogs', 'SystemUsers');
            $query = "INSERT INTO `".$table."`(`Code`, `Description`, `User`, `AuthType`) VALUES(".
                     "'".$user->getAttribute('Code')."', ".
                     "'".$this->attributes['username']." (MTAuth)', ".
                     "'".$this->attributes['username']."', ".
                     "'MTAuth')"
            ;
            
            unset($user);
            
            $db->executeQuery($query);
         }
      }
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