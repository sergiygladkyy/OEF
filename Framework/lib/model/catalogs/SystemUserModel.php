<?php

require_once('lib/model/catalogs/CatalogModel.php');

class SystemUserModel extends CatalogModel
{
   const kind = 'catalogs';
   const type = 'SystemUsers';
   
   public function __construct(array $options = array())
   {
      parent::__construct(self::type, $options);
   }
   
   /**
    * Load by user login and auth type
    * 
    * @param string $user - login
    * @param string $auth - auth type
    * @param array $options
    * @return boolean
    */
   public function loadByUser($user, $auth, array $options = array())
   {
      if (empty($user) || empty($auth))
      {
         return false;
      }
      
      $pkey  = $this->conf['db_map']['pkey'];
      $query = "SELECT * FROM `".$this->conf['db_map']['table']."` WHERE `User`='".$user."' AND `AuthType`='".$auth."'";
      $db    = $this->container->getDBManager($options);
      
      $values = $db->loadAssoc($query);
      
      if (empty($values)) return false;
      
      $this->id = $values[$pkey];
      unset($values[$pkey]);
      
      $this->attributes = $values;
      unset($values);
         
      $this->isNew = false;
      $this->isDeleted  = false;
      $this->isModified = false;
      $this->modified   = array();
      
      return true;
   }
   
   /**
    * (non-PHPdoc)
    * @see ext/AE/lib/model/base/BaseEntityModel#prepareToImport($values, $options)
    */
   protected function prepareToImport(array& $values, array& $options = array())
   {
      $errors = array();
      $pkey   = $this->conf['db_map']['pkey'];
      
      if (empty($values[$pkey]))
      {
         if (!empty($options['replace']))
         {
            if (!empty($values['Code'])) // Load by Code
            {
               if ($this->loadByCode($values['Code'], $options)) return array();
            }
            
            if (!empty($values['User']) && !empty($values['AuthType']))
            {
               if ($this->loadByUser($values['User'], $values['AuthType'], $options)) return array();
            }
         }
         
         // New
         $this->id         = null;
         $this->isNew      = true;
         $this->isDeleted  = false;
         $this->attributes = null;
         $this->isModified = false;
         $this->modified   = array();
         
         if (empty($values['Code']))
         {
            $this->setAttribute('Code', $this->generateCode($options));
         }
      }
      else if (!$this->load($values[$pkey], $options)) // Load by id
      {
         $errors[$pkey] = 'Invalid entity id';
      }
      
      unset($values[$pkey]);
      
      return $errors;
   }
   
   /**
    * (non-PHPdoc)
    * @see ext/OEF/Framework/lib/model/base/BaseNotStorageEntityModel#checkRequired()
    */
   protected function checkRequired()
   {
      $errors = array();
      
      foreach ($this->conf['required'] as $attribute)
      {
         if (!isset($this->attributes[$attribute]) || ($this->conf['types'][$attribute] != 'bool' && empty($this->attributes[$attribute])))
         {
            if ($attribute == 'Description')
            {
               $description  = (isset($this->attributes['User']) ? $this->attributes['User'] : '').' (';
               $description .= (isset($this->attributes['AuthType']) ? $this->attributes['AuthType'] : '').')';
               
               $this->setAttribute('Description', $description);
            }
            else $errors[$attribute] = "Required";
         }
      }
      
      return $errors;
   }
   
   /**
    * (non-PHPdoc)
    * @see ext/OEF/Framework/lib/model/catalogs/CatalogModel#validateAttributes($names, $options)
    */
   protected function validateAttributes($names, array& $options = array())
   {
      $errors = parent::validateAttributes($names, $options);
      
      // Check Login and AuthType
      if (!isset($errors['User']) && !isset($errors['AuthType']) &&
         ($this->isNew || $this->modified['User'] || $this->modified['AuthType']))
      {
         if (!$res = $this->checkUser($options))
         {
            $errors['User'][] = $res === null ? 'Validation error' : 'This user already exists';
         }
      }
      
      return $errors;
   }
   
   /**
    * Check login and auth type
    * 
    * @param array $options
    * @return boolean or null
    */
   protected function checkUser(array $options = array())
   {
      $db_map =& $this->conf['db_map'];
      
      $where[] = "`User`='".$this->attributes['User']."'";
      $where[] = "`AuthType`='".$this->attributes['AuthType']."'";
      
      if (!$this->isNew)
      {
         $where[] = "`".$db_map['pkey']."`<>".$this->id;
      }
      
      $query  = "SELECT count(*) AS `cnt` FROM `".$db_map['table']."` ";
      $query .= "WHERE ".implode(' AND ', $where);
      $db     = $this->container->getDBManager($options);
      $res    = $db->loadAssoc($query);
      
      return $res ? ($res['cnt'] ? false : true) : null;
   }
}
