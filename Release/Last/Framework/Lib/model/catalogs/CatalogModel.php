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
    * Load entity with Code = 'Code'
    * 
    * @param string $code
    * @param array& $options
    * @return boolean
    */
   public function loadByCode($code, array& $options = array())
   {
      $code  = (string) $code;
      $pkey  = $this->conf['db_map']['pkey'];
      $query = "SELECT * FROM `".$this->conf['db_map']['table']."` WHERE `Code`='".$code."'";
      $db    = $this->container->getDBManager($options);
      
      $values = $db->loadAssoc($query);
      
      if (is_null($values)) return false;
      
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
    * @see lib/model/base/BaseEntityModel#validateAttributes($names)
    */
   protected function validateAttributes($names, array& $options = array())
   {
      $errors = parent::validateAttributes($names, $options);
      
      // Check Code
      if (!isset($errors['Code']) && ($this->isNew || $this->modified['Code']))
      {
         if (!$this->checkCode($options)) $errors['Code'][] = 'Catalog with this Code already exists';
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
      $event = $this->container->getEvent($this, $this->kind.'.'.$this->type.'.onGenerateCode');
      $event['object'] = $this;
      
      $event->setReturnValue(null);
      $this->container->getEventDispatcher()->notify($event);
      $code = $event->getReturnValue();
      
      if (!empty($code)) return $code;
      
      /* Default generate */
      $dbmap =& $this->conf['db_map'];
      $query = "SELECT `Code` FROM `".$dbmap['table']."` ORDER BY `Code` DESC LIMIT 1";
      $db    = $this->container->getDBManager($options);
      
      if (!$row = $db->loadAssoc($query))
      { 
         if ($db->getErrno() != 0) return null;
         
         return '1';
      }
      
      // Generate next Code
      $code   = $row['Code'];
      $length = strlen($code) - 1;
      
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
         
         $code = '0'.$code;
      }
      
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
         $this->id        = null;
         $this->isNew     = true;
         $this->isDeleted = false;
      }
      else if (!$this->load($values[$pkey], $options)) // Load by id
      {
         $errors[$pkey] = 'Invalid entity id';
      }
      
      return $errors;
   }
}
