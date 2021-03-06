<?php

require_once('lib/persistent/config/SystemConstants.php');

abstract class BaseUser
{
   protected static $instance;
   
   protected
      $attributes    = array(),
      $roles         = array(),
      $permissions   = array(),
      $roles_perm    = array(),
      $authenticated = null,
      $storage       = null,
      $isAdmin       = false;
   
   /**
    * Get current User object
    *  
    * @return this
    */
   static public function getCurrent()
   {
      if (!is_object(self::$instance))
      {
         throw new Exception('The object was not created', 2);
      }
      
      return self::$instance;
   }

   /**
    * Constructor
    * 
    * @return void
    */
   protected function __construct()
   {
   }
   
   /**
    * This object is not clonable
    * 
    * @return this
    */
   protected function __clone()
   {
   }
   
   /**
    * Get all roles
    * 
    * @return array
    */
   public function getRoles()
   {
      return $this->roles;
   }
   
   /**
    * Check role by name
    * 
    * @param string $role
    * @return boolean
    */
   public function hasRole($role)
   {
      return in_array($role, $this->roles);
   }
   
   /**
    * Get all permissions
    * 
    * @return array
    */
   public function getPermissions()
   {
      // @todo Get only loaded permission
      return $this->permissions;
   }
   
   /**
    * Check permission by name
    * 
    * @param string $permission
    * @return boolean
    */
   public function hasPermission($permission)
   {
      if ($this->isAdmin) return true;
      
      if (isset($this->permissions[$permission]))
      {
         return $this->permissions[$permission];
      }
      
      if (empty($this->roles))
      {
         return ($this->permissions[$permission] = false);
      }
      
      $this->permissions[$permission] = true;
      
      foreach ($this->roles as $role)
      {
         if (!isset($this->roles_perm[$role]))
         {
            if (!isset($CManager))
            {
               $CManager = Container::getInstance()->getConfigManager();
            }
            
            $this->roles_perm[$role] = $CManager->getInternalConfiguration('security.permissions', $role);
         }

         if (empty($this->roles_perm[$role][$permission]))
         {
            $this->permissions[$permission] = false;
            break;
         }
      }

      return $this->permissions[$permission];
   }
   
   /**
    * Return true if current user is Authenticated
    * 
    * @return boolean
    */
   public function isAuthenticated()
   {
      return $this->authenticated ? true : false;
   }
   
   /**
    * Return true if current user is Admin
    * 
    * @return boolean
    */
   public function isAdmin()
   {
      return $this->isAdmin;
   }
   
   /**
    * Get user name
    * 
    * @return string
    */
   abstract public function getUsername();
   
   /**
    * Get auth type
    * 
    * @return string
    */
   abstract public function getAuthType();
   
   /**
    * Get user id
    * 
    * @return int
    */
   abstract public function getId();
 }