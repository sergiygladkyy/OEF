<?php

require_once('lib/model/base/BaseEntityModel.php');

class BaseObjectModel extends BaseEntityModel
{
   /**
    * If standard processing for input on basis was performed - contents true
    * 
    * @var boolean
    */
   private $stInputOnBasis = false;
   
   /**
    * Constructor
    * 
    * @param string $kind
    * @param string $type
    * @param array& $options
    * @return void
    */
   public function __construct($kind, $type, array& $options = array())
   {
      parent::__construct($kind, $type, $options);
      
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
      $CManager = $this->container->getConfigManager();
      
      // relations
      if (!isset(self::$config[$confname]['relations']))
      {
         $conf = $CManager->getInternalConfiguration('relations', $kind);

         self::$config[$confname]['relations'] = isset($conf[$type]) ? $conf[$type] : array();
      }
      
      // basis_for
      if (!isset(self::$config[$confname]['basis_for']))
      {
         self::$config[$confname]['basis_for'] = $CManager->getInternalConfiguration($kind.'.basis_for', $type);
      }
      
      // input_on_basis
      if (!isset(self::$config[$confname]['input_on_basis']))
      {
         self::$config[$confname]['input_on_basis'] = $CManager->getInternalConfiguration($kind.'.input_on_basis', $type);
      }
      
      return true;
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
      
      if (empty($values))
      {
         return false;
      }
      
      return $this->loadAttributes($values, $options);
   }
   
   /**
    * (non-PHPdoc)
    * @see lib/model/base/BaseEntityModel#validateAttributes($names)
    */
   protected function validateAttributes($names, array& $options = array())
   {
      $errors = parent::validateAttributes($names, $options);
      
      // Check Code
      if (!isset($errors['Code']) && ($this->isNew || !empty($this->modified['Code'])))
      {
         if (!$this->checkCode($options)) $errors['Code'][] = 'Record with this Code already exists';
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
      
      // @todo addition of code to a maximum length - for a corect sort
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
         
         if (empty($values['Code'])) $this->setAttribute('Code', $this->generateCode($options));
      }
      else if (!$this->load($values[$pkey], $options)) // Load by id
      {
         $errors[$pkey] = 'Invalid entity id';
      }
      
      unset($values[$pkey]);
      
      return $errors;
   }
   
   
   
   
   /**
    * Mark for deletion
    * 
    * @param array& $options
    * @return array - errors
    */
   public function markForDeletion(array& $options = array())
   {
      return $this->changeMarkForDeletion(true, $options);
   }
   
   /**
    * Unmark for deletion
    * 
    * @param array& $options
    * @return array - errors
    */
   public function unmarkForDeletion(array& $options = array())
   {
      return $this->changeMarkForDeletion(false, $options);
   }
   
   /**
    * Change MarkForDeletion flag
    * 
    * @param boolean $mark
    * @param array&  $options
    * @return array - errors
    */
   protected function changeMarkForDeletion($mark, array& $options = array())
   {
      if ($this->isNew) return array();
      
      $db    =  $this->container->getDBManager($options);
      $dbmap =& $this->conf['db_map'];
      $query =  "UPDATE `".$dbmap['table']."` SET `".$dbmap['deleted']."` = ".($mark ? 1 : 0)." WHERE `".$dbmap['pkey']."`=".$this->id;
       
      if (!$db->executeQuery($query))
      {
         return array($db->getError());
      }
      
      return array();
   }
   
   /**
    * Check mark for deletion flag
    * 
    * @return boolean (true if entity marked for deletion)
    */
   public function isMarkedForDeletion()
   {
      if ($this->isNew) return false;
      
      return !empty($this->attributes[$this->conf['db_map']['deleted']]);
   }
   
   /**
    * Input on basis
    * 
    * @param mixed $object  - BaseObjectModel or array('kind' => <string>, 'type' => <string>, 'pkey' => <int>)
    * @param array $options
    * @return array - errors
    */
   public function inputOnBasis($object, array $options = array())
   {
      if (!$this->isNew)
      {
         throw new Exception(__METHOD__.': Only new object can be input on basis');
      }
      
      $input =& $this->conf['input_on_basis'];
       
      // Check data
      if (is_array($object))
      {
         $kind = isset($object['kind']) ? (string) $object['kind'] : '';
         $type = isset($object['type']) ? (string) $object['type'] : '';
         $id   = isset($object['id'])   ? (int)    $object['id']   : 0;
      }
      elseif (!is_a($model, 'BaseObjectModel'))
      {
         return array('Invalid object');
      }
      else
      {
         $kind = $object->getKind();
         $type = $object->getType();
      }
      
      if (!isset($input[$kind]) || !in_array($type, $input[$kind]))
      {
         return array('Invalid basic object');
      }
      
      if (is_array($object))
      {
         if ($id <= 0)
         {
            return array('Invalid basic object');
         }
         
         $object = $this->container->getModel($kind, $type, $options);
         
         if (!$object->load($id, $options))
         {
            return array('Unknow basic object');
         }
      }
      elseif ($object->isNew())
      {
         return array('Basic object not must be new');
      }
      
      // Filing object
      $event = $this->container->getEvent($this, $this->kind.'.'.$this->type.'.model'.'.onInputOnBasis');
      $event->setReturnValue(true);
      $event['subject'] = $this;
      $event['object']  = $object;
      $event['standard_processing'] = true;
      
      try
      {
         $this->container->getEventDispatcher()->notify($event);
      }
      catch(Exception $e)
      {
         return array($e->getMessage());
      }
      
      if (!$event->getReturnValue())
      {
         return array('Not processed. Module error');
      }
      
      if ($event['standard_processing'])
      {
         return $this->standardProcessingInputOnBasis($object, $options);
      }
      
      return array();
   }
   
   /**
    * Standard processing input on basis action
    * 
    * @param BaseObjectModel $object
    * @param array& $options
    * @return array - errors
    */
   protected function standardProcessingInputOnBasis(BaseObjectModel $object, array& $options = array())
   {
      if ($this->stInputOnBasis) return array();
      
      $this->stInputOnBasis = true;
      
      $errors = array();
      $otypes = $this->container->getConfigManager($options)->getInternalConfiguration($object->getKind().'.field_type', $object->getType());
      $types  = $this->conf['types'];
      $values = $object->toArray();
      
      unset($types['Code']);
      
      if ($this->kind == 'documents')
      {
         if (!$this->setAttribute('Date', date('Y-m-d H:i:s')))
         {
            $errors['Date'] = 'Invalid value type';
         }
         
         unset($types['Date']);
      }
      // @todo moved this in DocumentModel
      
      foreach ($types as $attr => $type)
      {
         if (isset($otypes[$attr]) && $otypes[$attr] == $type && isset($values[$attr]))
         {
            if (!$this->setAttribute($attr, $values[$attr]))
            {
               $errors[$attr] = 'Invalid value type';
            }
         }
      }
      
      return $errors;
   }
}