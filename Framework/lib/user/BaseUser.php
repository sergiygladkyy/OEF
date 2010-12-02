<?php

abstract class BaseUser
{
   protected $attributes;
   
   /**
    * Create current User object
    * 
    * @param string $authtoken
    * @return this
    */
   abstract static public function getCurrent($authtoken);

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
   abstract public function getRoles();
   
   /**
    * Check role by name
    * 
    * @param string $role
    * @return boolean
    */
   abstract public function hasRole($role);
   
   /**
    * Get all permissions
    * 
    * @return array
    */
   public function getPermissions()
   {
      return array();
   }
   
   /**
    * Check permission by name
    * 
    * @param string $permission
    * @return boolean
    */
   public function hasPermission($permission)
   {
      return !$this->isAnonymous();
   }
   
   /**
    * Return true if current user is Anonymous
    * @return unknown_type
    */
   abstract public function isAnonymous();
}