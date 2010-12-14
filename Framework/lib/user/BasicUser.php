<?php

require_once('lib/user/BaseUser.php');

class BasicUser extends BaseUser
{
   const ADMIN_ROLE = 'Admin';
   
   /**
    * Create current User object
    * 
    * @param string $storage
    * @return this
    */
   static public function getCurrent(Storage $storage)
   {
      return new self($storage);
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
            $authRecords = $this->loadAuthRecords($this->attributes['_id']);
            
            if (!empty($authRecords))
            {
               $this->roles   = (isset($authRecords['roles']) && is_array($authRecords['roles'])) ? $authRecords['roles'] : array();
               $this->isAdmin = in_array(self::ADMIN_ROLE, $this->roles);
            }
         }
         else $this->logout();
      }
      register_shutdown_function(array($this, 'shutdown'));
   }
   
   /**
    * Get user name
    * 
    * @return string
    */
   public function getUsername()
   {
      return $this->attributes['username'];
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
         throw new Exception('User alredy authenticated');
      }
      
      $container = Container::getInstance();
      $user = $container->getModel('catalogs', 'SystemUsers');
      
      if (!$user->loadByCode($login)) return false;
      
      $userId      = $user->getId();
      $authRecords = $this->loadAuthRecords($userId);
      
      if (empty($authRecords) || $authRecords['password'] != $password) return false;
      
      $this->attributes['_id']      = $userId;
      $this->attributes['login']    = $login;
      $this->attributes['username'] = $user->getAttribute('Description');
      
      $this->roles   = (isset($authRecords['roles']) && is_array($authRecords['roles'])) ? $authRecords['roles'] : array();
      $this->isAdmin = in_array(self::ADMIN_ROLE, $this->roles);
      
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
    * Load AuthenticationRecords
    * 
    * @param int $userId
    * @return array or null
    */
   protected function loadAuthRecords($userId)
   {
      $container   = Container::getInstance();
      $authRecords = $container->getCModel('information_registry', 'AuthenticationRecords');
      $criterion   = 'WHERE User='.(int) $userId." AND AuthType='Basic'";
      
      if (null === ($res = $authRecords->getEntities(null, array('criterion' => $criterion))))
      {
         return null;
      }
      
      return empty($res) ? array() : unserialize($res[0]['Attributes']);
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
