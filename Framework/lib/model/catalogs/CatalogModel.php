<?php

require_once('lib/model/base/BaseObjectModel.php');

class CatalogModel extends BaseObjectModel
{
   const kind = 'catalogs';
   
   public function __construct($type, array& $options = array())
   {
      parent::__construct(self::kind, $type, $options);
      
      $this->setAttribute('Code', $this->generateCode($options));
   }
   
   /**
    * (non-PHPdoc)
    * @see lib/model/base/BaseModel#setup($kind, $type)
    */
   protected function initialize($kind, $type)
   {
      if (!parent::initialize($kind, $type)) return false;
      
      $confname = self::getConfigurationName($kind, $type);
      
      // owners
      if (!isset(self::$config[$confname]['owners']))
      {
         if (!isset($CManager)) $CManager = $this->container->getConfigManager();
         
         self::$config[$confname]['owners'] = $CManager->getInternalConfiguration($kind.'.owners', $type);
      }
      
      // hierarchy
      if (!isset(self::$config[$confname]['hierarchy']))
      {
         if (!isset($CManager)) $CManager = $this->container->getConfigManager();
         
         self::$config[$confname]['hierarchy'] = $CManager->getInternalConfiguration($kind.'.hierarchy', $type);
      }
      
      // use
      if (!isset(self::$config[$confname]['use']))
      {
         if (!isset($CManager)) $CManager = $this->container->getConfigManager();
         
         self::$config[$confname]['use'] = $CManager->getInternalConfiguration($kind.'.field_use', $type);
      }
      
      if ($this->isHierarchical())
      {
         $this->updateConfiguration();
      }
      
      return true;
   }
   
   /**
    * Update configuration for this object
    * 
    * @return boolean
    */
   protected function updateConfiguration()
   {
      if (!$this->isHierarchical())
      {
         throw new Exception(__METHOD__.': Not supported operation updateConfiguration');
      }
      
      $mode = empty($this->attributes[$this->conf['db_map']['folder']]) ? SystemConstants::USAGE_WITH_ITEM : SystemConstants::USAGE_WITH_FOLDER;
         
      if (!isset($this->conf['use'][$mode])) return false;
      
      $this->conf['attributes'] =& $this->conf['use'][$mode];
      
      $confname = self::getConfigurationName($this->kind, $this->type);
      $required = array_intersect($this->conf['attributes'], self::$config[$confname]['required']);
      
      $this->conf['required'] =& $required;
      
      return true;
   }
   
   
   /**
    * (non-PHPdoc)
    * @see BaseEntityModel#load($id, $options)
    */
   public function load($id, array& $options = array())
   {
      // Check permissions
      if (defined('IS_SECURE') && !$this->container->getUser()->hasPermission($this->kind.'.'.$this->type.'.Read'))
      {
         return false;
      }
      
      // Execute method
      $ret = parent::load($id, $options);
      
      if ($ret && $this->isHierarchical())
      {
         $this->updateConfiguration();
      }
      
      return $ret;
   }
   
   /**
    * Load entity with Code = 'Code'
    * 
    * @param string $code
    * @param array& $options
    * @return boolean
    */
   public function loadByCode($code, array& $options = array())
   {
      // Check permissions
      if (defined('IS_SECURE') && !$this->container->getUser()->hasPermission($this->kind.'.'.$this->type.'.Read'))
      {
         return false;
      }
      
      // Execute method
      $code  = (string) $code;
      $pkey  = $this->conf['db_map']['pkey'];
      $query = "SELECT * FROM `".$this->conf['db_map']['table']."` WHERE `Code`='".$code."'";
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
      
      if ($this->isHierarchical())
      {
         $this->updateConfiguration();
      }
      
      return true;
   }
   
   
   
   /**
    * (non-PHPdoc)
    * @see lib/model/base/BaseEntityModel#validateAttributes($names)
    */
   protected function validateAttributes($names, array& $options = array())
   {
      if ($this->isHierarchical())
      {
         $names = array_intersect($names, $this->conf['attributes']);
      }
      
      $errors = parent::validateAttributes($names, $options);
      
      // Check Code
      if (!isset($errors['Code']) && ($this->isNew || !empty($this->modified['Code'])))
      {
         if (!$this->checkCode($options)) $errors['Code'][] = 'Catalog with this Code already exists';
      }
      
      // Check owner
      if ($this->hasOwner() && !isset($errors['OwnerType']) && !isset($errors['OwnerId']))
      {
         $hcheck = ($this->isHierarchical() && !isset($errors['Parent']));
         
         if ($own_err = $this->checkOwner($hcheck))
         {
            $errors = array_merge($errors, $own_err);
         }
      }
      
      return $errors;
   }
   
   /**
    * Check unique Code
    * 
    * @param array& $options
    * @return array - errors
    */
   protected function checkCode(array& $options = array())
   {
      $db_map =& $this->conf['db_map'];
      
      $where[] = "`Code`='".$this->attributes['Code']."'";
      
      if (!$this->isNew)
      {
         $where[] = "`".$db_map['pkey']."`<>".$this->id;
      }
      
      $query  = "SELECT count(*) AS `cnt` FROM `".$db_map['table']."` ";
      $query .= "WHERE ".implode(' AND ', $where);
      $db     = $this->container->getDBManager($options);
      $res    = $db->loadAssoc($query);
      
      return (!$res || $res['cnt']) ? false : true;
   }
   
   /**
    * Generate default value for system attribute 'Code'
    * 
    * @param array& $options
    * @return string
    */
   protected function generateCode(array& $options = array())
   {
      /* Custom generate */
      $event = $this->container->getEvent($this, $this->kind.'.'.$this->type.'.model.onGenerateCode');
      $event['object'] = $this;
      
      $event->setReturnValue(null);
      $this->container->getEventDispatcher()->notify($event);
      $code = $event->getReturnValue();
      
      if (!empty($code)) return $code;
      
      /* Default generate */
      $dbmap =& $this->conf['db_map'];
      $query = "SELECT LENGTH(`Code`) AS `length`, `Code` FROM `".$dbmap['table']."` GROUP BY `length` DESC, `Code` DESC LIMIT 1"; 
      $db    = $this->container->getDBManager($options);
      
      if (!$row = $db->loadAssoc($query))
      { 
         if ($db->getErrno() != 0) return null;
         
         return '1';
      }
      
      // Generate next Code
      $code   = $row['Code'];
      $length = $row['length'] - 1; //strlen($code) - 1;
      
      for ($i = $length; $i >= 0; $i--)
      {
         $val = (int) $code{$i};
         
         // Integer value
         if ($code{$i} == (string) $val)
         {
            if ($val < 9)
            {
               $code{$i} = ++$val;
               break;
            }
            
            $code{$i} = 0;
            continue;
         }
         
         // Char (A-Z -> 65-90, a-z -> 97-122)
         $val = ord($code{$i});
         
         if ($val < 65 || (90 < $val && $val < 97) || $val > 122) continue;
         
         if ($val != 122)
         {
            if ($val != 90)
            {
               $code{$i} = chr(++$val);
               break;
            }
            else $code{$i} = 'A';
         }
         else $code{$i} = 'a';
      }
      
      if ($i == -1)
      {
         if (($length + 1) >= $this->conf['precision']['Code']['max_length']) return null;
         
         $code = '1'.$code;
      }
      
      // @todo addition of code to a maximum length
      // $code = str_pad($code, $this->conf['precision']['Code']['max_length'], '0', STR_PAD_LEFT);  
      
      return $code;
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
         if (!empty($values['Code']) && !empty($options['replace'])) // Load by Code
         {
            if ($this->loadByCode($values['Code'], $options)) return array();
         }
         
         // New
         $this->id         = null;
         $this->isNew      = true;
         $this->isDeleted  = false;
         $this->attributes = null;
         $this->isModified = false;
         $this->modified   = array();
      }
      else if (!$this->load($values[$pkey], $options)) // Load by id
      {
         $errors[$pkey] = 'Invalid entity id';
      }
      
      unset($values[$pkey]);
      
      return $errors;
   }
   
   
   
   
   /**
    * Has owner
    * 
    * @return boolean
    */
   public function hasOwner()
   {
      return !empty($this->conf['owners']);
   }
   
   /**
    * Is hierarchical
    * 
    * @return boolean
    */
   public function isHierarchical()
   {
      return !empty($this->conf['hierarchy']);
   }
   
   /**
    * Is folder
    * 
    * @return boolean
    */
   public function isFolder()
   {
      return $this->isHierarchical() && $this->attributes[$this->conf['db_map']['folder']];
   }
   
   /**
    * Set folder flag
    * 
    * @param bool $value
    * @return bool
    */
   public function setFolder($value)
   {
      if (!$this->isHierarchical())
      {
         throw new Exception(__METHOD__.': Not supported operation setFolder');
      }
      
      $value = $value ? true : false;
      
      if ($value == $this->attributes[$this->conf['db_map']['folder']])
      {
         return true;
      }
      
      $mode  = $value ? SystemConstants::USAGE_WITH_FOLDER : SystemConstants::USAGE_WITH_ITEM;
      $attrs = array_diff($this->conf['attributes'], $this->conf['use'][$mode]);
      
      foreach ($attrs as $attr)
      {
         $this->setAttribute($attr, null);
      }
      
      $this->attributes[$this->conf['db_map']['folder']] = $value;
      $this->isModified = true;
      $this->modified['folder'] = true;
      
      $this->updateConfiguration();
      
      return true;
   }
   
   /**
    * Check owner
    * 
    * @param bool $check_hierarchical
    * @return array - errors
    */
   protected function checkOwner($check_hierarchical = false)
   {
      $kind = 'catalogs';
      $type = $this->attributes['OwnerType'];
      $id   = $this->attributes['OwnerId'];
      
      if (!in_array($type, $this->conf['owners']))
      {
         return array('OwnerType' => 'Invalid owner type');
      }
      
      if (null === ($res = self::hasEntity('catalogs', $type, $id)))
      {
         return array('Database error');
      }
      elseif (!$res)
      {
         return array('OwnerId' => 'Catalog not exists');
      }
      
      // Check hierarchical
      if ($check_hierarchical && !empty($this->attributes['Parent']))
      {
         $parent = $this->attributes['Parent'];
         $query  = "SELECT `OwnerType`, `OwnerId` FROM `".$this->conf['db_map']['table']."` ".
                   "WHERE `".$this->conf['db_map']['pkey']."`=".$parent;
         
         $db = $this->container->getDBManager();
         
         if (null === ($row = $db->loadAssoc($query)))
         {
            return array('Database error');
         }
         
         if ($row['OwnerType'] != $type || $row['OwnerId'] != $id)
         {
            return array('Parent' => 'Catalog with that Owner can\'t have this parent');
         }
      }
         
      return array();
   }
   
   /**
    * (non-PHPdoc)
    * @see BaseEntityModel#addSystemToInsert($fields, $values)
    */
   protected function addSystemToInsert(& $fields, & $values)
   {
      if ($this->isHierarchical() && !empty($this->modified['folder']))
      {
         if ($fields)
         {
            $fields .= ', ';
            $values .= ', ';
         }
         else
         {
            $fields = '';
            $values = '';
         }

         $fields .= "`".$this->conf['db_map']['folder']."`";
         $values .= $this->attributes[$this->conf['db_map']['folder']] ? 1 : 0;
      }
   }
   
   /**
    * (non-PHPdoc)
    * @see BaseEntityModel#addSystemToUpdate($fields)
    */
   protected function addSystemToUpdate(& $fields)
   {
      if ($this->isHierarchical() && !empty($this->modified['folder']))
      {
         $fields[] = "`".$this->conf['db_map']['folder']."`=".($this->attributes[$this->conf['db_map']['folder']] ? 1 : 0);
      }
   }
   
   
   
   
   /************************** For control access rights **************************************/
   
   
   
   /**
    * (non-PHPdoc)
    * @see BaseObjectModel#delete($options)
    */
   public function delete(array& $options = array())
   {
      // Check permissions
      if (defined('IS_SECURE') && !$this->container->getUser()->hasPermission($this->kind.'.'.$this->type.'.Delete'))
      {
         return array();
      }
      
      // Execute method
      return parent::delete($options);
   }
   
   /**
    * (non-PHPdoc)
    * @see BaseEntityModel#toArray($options)
    */
   public function toArray(array $options = array())
   {
      // Check permissions
      if (defined('IS_SECURE') && !$this->isNew && !$this->container->getUser()->hasPermission($this->kind.'.'.$this->type.'.Read'))
      {
         return array();
      }
      
      // Execute method
      return parent::toArray($options);
   }
   
   /**
    * (non-PHPdoc)
    * @see BaseEntityModel#save($options)
    */
   public function save(array& $options = array())
   {
      // Check permissions
      if (defined('IS_SECURE'))
      { 
         if ($this->isNew)
         {
            $access = $this->container->getUser()->hasPermission($this->kind.'.'.$this->type.'.Insert');
         }
         else
         {
            $access = $this->container->getUser()->hasPermission($this->kind.'.'.$this->type.'.Update');
         }
         
         if (!$access)
         {
            return array('Access denied');
         }
      }
      
      // Execute method
      return parent::save($options);
   }
}
