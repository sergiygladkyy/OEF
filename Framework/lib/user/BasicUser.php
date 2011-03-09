<?php

require_once('lib/user/BaseUser.php');

class BasicUser extends BaseUser
{
   /**
    * Create current User object
    * 
    * @param string $storage
    * @return this
    */
   static public function createInstance(Storage $storage)
   {
      if (is_object(self::$instance))
      {
         throw new Exception('User alredy exists', 1);
      }
      
      self::$instance = new self($storage);
      
      return self::$instance;
   }
   
   /**
    * Constructor
    * 
    * @return void
    */
   protected function __construct(Storage $storage)
   {
      $this->storage       = $storage;
      $this->attributes    = $this->storage->read('user.attributes', array());
      $this->authenticated = $this->storage->read('user.authenticated', false);
      
      if ($this->authenticated)
      {
         if (isset($this->attributes['_id']))
         {
            $params = $this->loadParameters($this->attributes['_id']);
            
            if (!empty($params))
            {
               $this->roles   = (isset($params['roles']) && is_array($params['roles'])) ? $params['roles'] : array();
               $this->isAdmin = in_array(SystemConstants::ADMIN_ROLE, $this->roles);
            }
         }
         else $this->logout();
      }
      register_shutdown_function(array($this, 'shutdown'));
   }
   
   /**
    * (non-PHPdoc)
    * @see ext/OEF/Framework/lib/user/BaseUser#getAuthType()
    */
   public function getAuthType()
   {
      return 'Basic';
   }
   
   /**
    * (non-PHPdoc)
    * @see ext/OEF/Framework/lib/user/BaseUser#getId()
    */
   public function getId()
   {
      return isset($this->attributes['_id']) ? $this->attributes['_id'] : 0;
   }
   
   /**
    * Get user name
    * 
    * @return string
    */
   public function getUsername()
   {
      return isset($this->attributes['username']) ? $this->attributes['username'] : '';
   }
   
   /**
    * Login
    * 
    * @param string $login
    * @param string $password
    * @return boolean
    */
   public function login($login, $password)
   {
      if ($this->authenticated)
      {
         throw new Exception('User alredy authenticated', 4);
      }
      
      $container = Container::getInstance();
      $db    = $container->getDBManager();
      $dbmap = $container->getConfigManager()->getInternalConfiguration('db_map', 'catalogs');
      $table = $dbmap['SystemUsers']['table'];
      
      $query = "SELECT * FROM `".$table."` WHERE `User`='".$login."' AND `AuthType`='Basic'";
      
      if (null === ($user = $db->loadAssoc($query)) || empty($user)) return false;
      
      $userId = $user['_id'];
      $params = unserialize($user['Attributes']);
      
      if (empty($params) || $params['password'] != $password) return false;
      
      $this->attributes['_id']      = $userId;
      $this->attributes['login']    = $login;
      $this->attributes['username'] = $user['Description'];
      
      $this->roles   = (isset($params['roles']) && is_array($params['roles'])) ? $params['roles'] : array();
      $this->isAdmin = in_array(SystemConstants::ADMIN_ROLE, $this->roles);
      
      $this->authenticated = true;
      //$this->shutdown();
      return true;
   }
   
   /**
    * Logout
    * 
    * @return boolean
    */
   public function logout()
   {
      if (!$this->authenticated) return true;
      
      $this->attributes  = array();
      $this->roles       = array();
      $this->permissions = array();
      $this->roles_perm  = array();
      
      $this->authenticated = false;
      $this->storage->regenerate(false);
      
      return true;
   }
   
   /**
    * Load user parameters
    * 
    * @param int $userId
    * @return array or null
    */
   protected function loadParameters($userId)
   {
      $container = Container::getInstance();
      $db    = $container->getDBManager();
      $dbmap = $container->getConfigManager()->getInternalConfiguration('db_map', 'catalogs');
      $table = $dbmap['SystemUsers']['table'];
      $pkey  = $dbmap['SystemUsers']['pkey'];
      
      $query = "SELECT `Attributes` FROM `".$table."` WHERE `".$pkey."`=".(int) $userId." AND `AuthType`='Basic'";
         
      if (null === ($res = $db->loadAssoc($query)))
      {
         return null;
      }
      
      return empty($res) ? array() : unserialize($res['Attributes']);
   }
   
   /**
    * Execute shutdown
    */
   public function shutdown()
   {
      $this->storage->write('user.attributes', $this->attributes);
      $this->storage->write('user.authenticated', $this->authenticated);
      $this->storage->shutdown();
   }
}
