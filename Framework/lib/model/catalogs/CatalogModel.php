<?php

require_once('lib/model/base/BaseObjectModel.php');

class CatalogModel extends BaseObjectModel
{
   const kind = 'catalogs';
   
   public function __construct($type, array& $options = array())
   {
      parent::__construct(self::kind, $type, $options);
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
      
      $mode = $this->isFolder() ? SystemConstants::USAGE_WITH_FOLDER : SystemConstants::USAGE_WITH_ITEM;
      
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
      
      if ($ret && $this->isFolderHierarchical())
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
      $ret = parent::loadByCode($code, $options);
      
      if ($ret && $this->isFolderHierarchical())
      {
         $this->updateConfiguration();
      }
      
      return $ret;
   }
   
   
   
   /**
    * (non-PHPdoc)
    * @see lib/model/base/BaseObjectModel#validateAttributes($names, $options)
    */
   protected function validateAttributes($names, array& $options = array())
   {
      if ($this->isHierarchical())
      {
         $names = array_intersect($names, $this->conf['attributes']);
      }
      
      $errors = parent::validateAttributes($names, $options);
      
      // Check owner
      if ($this->hasOwner() && !isset($errors['OwnerType']) && !isset($errors['OwnerId']))
      {
         $hcheck = ($this->isHierarchical() && !isset($errors['Parent']));
         
         if ($own_err = $this->checkOwner($hcheck))
         {
            $errors = array_merge($errors, $own_err);
         }
      }
      
      // Check Parent
      if ($this->isHierarchical() && !isset($errors['Parent']))
      {
         if ($parent_err = $this->checkParent())
         {
            $errors = array_merge($errors, $parent_err);
         }
      }
      
      return $errors;
   }
   
   /**
    * (non-PHPdoc)
    * @see ext/AE/lib/model/base/BaseObjectModel#prepareToImport($values, $options)
    */
   protected function prepareToImport(array& $values, array& $options = array())
   {
      $errors = parent::prepareToImport($values, $options);
      
      if (!$errors && $this->isNew && $this->isFolderHierarchical())
      {
         $folder = $this->conf['db_map']['folder'];

         $this->setFolder(!empty($values[$folder]));

         unset($values[$folder]);
      }
         
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
    * Is folder hierarchical
    * 
    * @return boolean
    */
   public function isFolderHierarchical()
   {
      return (!empty($this->conf['hierarchy']) && $this->conf['hierarchy']['type'] == 2);
   }
   
   /**
    * Is folder
    * 
    * @return boolean
    */
   public function isFolder()
   {
      return $this->isFolderHierarchical() && $this->attributes[$this->conf['db_map']['folder']];
   }
   
   /**
    * Set folder flag
    * 
    * @param bool $value
    * @return bool
    */
   public function setFolder($value)
   {
      if (!$this->isFolderHierarchical())
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
         return array('Validation error');
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
            return array('Validation error');
         }
         
         if ($row['OwnerType'] != $type || $row['OwnerId'] != $id)
         {
            return array('Parent' => 'Catalog with that Owner can\'t have this parent');
         }
      }
         
      return array();
   }
   
   /**
    * Check parent
    * 
    * @return array - errors
    */
   protected function checkParent()
   {
      if (empty($this->attributes['Parent'])) return array();
      
      $errors = array();
      $parent = $this->attributes['Parent'];
      $dbmap  =& $this->conf['db_map'];
      
      $id = $this->getId();
      
      if (!$this->isNew && $parent == $id)
      {
         return array('Parent' => 'Invalid parent');
      }
      
      if ($this->isFolderHierarchical())
      {
         $query = "SELECT * FROM `".$dbmap['table']."` WHERE `".$dbmap['pkey']."`=".$parent;
         
         $db = $this->container->getDBManager();
         
         if (null === ($row = $db->loadAssoc($query)))
         {
            return array('Validation error');
         }
         
         if ($row[$dbmap['folder']] != 1)
         {
            $errors['Parent'][] = 'Parent must be a group';
         }
         
         if ($row[$dbmap['deleted']])
         {
            $errors['Parent'][] = 'Parent could not be marked for deletion';
         }
      }
      
      if (!$this->isNew)
      {
         $cmodel = $this->container->getCModel($this->kind, $this->type);
         
         if (null === ($res = $cmodel->getParents($parent)) || $res['errors'])
         {
            return array('Validation error');
         }
         
         foreach ($res as $row)
         {
            if ($row['Parent'] == $id)
            {
               $errors['Parent'][] = 'Child node could not be a parent';
               
               break;
            }
         }
      }
      
      return $errors;
   }
   
   /**
    * (non-PHPdoc)
    * @see BaseEntityModel#addSystemToInsert($fields, $values)
    */
   protected function addSystemToInsert(& $fields, & $values)
   {
      if ($this->isFolderHierarchical() && !empty($this->modified['folder']))
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
      if ($this->isFolderHierarchical() && !empty($this->modified['folder']))
      {
         $fields[] = "`".$this->conf['db_map']['folder']."`=".($this->attributes[$this->conf['db_map']['folder']] ? 1 : 0);
      }
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
      $item = parent::toArray($options);
      
      if (!empty($options['with_link_desc']) && $this->hasOwner() && !empty($item['OwnerType']) && !empty($item['OwnerId']))
      {
         $cmodel = $this->container->getCModel($this->kind, $item['OwnerType'], $options);
         $data   = $cmodel->retrieveLinkData($item['OwnerId']);
         $item['OwnerId'] = $data[$item['OwnerId']];
      }
      
      return $item;
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
