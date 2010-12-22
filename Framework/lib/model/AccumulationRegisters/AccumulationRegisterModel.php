<?php

require_once('lib/model/base/BaseRegisterModel.php');

class AccumulationRegisterModel extends BaseRegisterModel
{
   const kind = 'AccumulationRegisters';
   
   protected static $lines = array();
   
   protected
      $total   = null,
      $options = array(
         'auto_update_total' => true
      );
   
   public function __construct($type, array& $options = array())
   {
      parent::__construct(self::kind, $type, $options);
      
      // Create total object
      if ($this->conf['register_type'] == 'Balances')
      {
         require_once('lib/model/AccumulationRegisters/BalancesModel.php');
         
         $classname = 'BalancesModel';
      }
      else
      {
         require_once('lib/model/AccumulationRegisters/TurnoversModel.php');
         
         $classname = 'TurnoversModel';
      }
      
      if (!class_exists($classname))
      {
         throw new Exception(__METHOD__.': model class "'.$classname.'" does not exist');
      }
      
      $this->total = new $classname($this->conf);
   }
   
   /**
    * (non-PHPdoc)
    * @see lib/model/base/BaseModel#setup($kind, $type)
    */
   protected function initialize($kind, $type)
   {
      if (!parent::initialize($kind, $type)) return false;
      
      $confname = self::getConfigurationName($kind, $type);

      if (!isset(self::$config[$confname]['register_type']))
      {
         self::$config[$confname]['register_type'] = $this->container->getConfigManager()->getInternalConfigurationByKind($kind.'.register_type', $type);
      }
      
      $this->attributes[$this->conf['db_map']['active']] = true;
      
      if ($this->conf['register_type'] == 'Balances')
      {
         $this->attributes[$this->conf['db_map']['operation']] = 1;
      }
      
      return true;
   }
   
   /**
    * Get register type
    * 
    * @return string
    */
   public function getRegisterType()
   {
      return $this->conf['register_type'];
   }
   
   /**
    * If register type is Balances, return true
    * 
    * @return boolean
    */
   public function isBalances()
   {
      return ($this->conf['register_type'] == 'Balances');
   }
   
   /**
    * Set active flag
    * 
    * @param boolean $value
    * @return void
    */
   public function setActive($value)
   {
      $this->attributes[$this->conf['db_map']['active']] = $value ? true : false;
   }
   
   /**
    * If is active - return true
    * 
    * @return boolean
    */
   public function isActive()
   {
      return !empty($this->attributes[$this->conf['db_map']['active']]);
   }
   
   /**
    * Set operation
    * 
    * @param mixed $type - '+', true OR '-', false
    * @return void
    */
   public function setOperation($type)
   {
      if ($this->conf['register_type'] != 'Balances') return;
      
      $this->attributes[$this->conf['db_map']['operation']] = ($type == '+' || $type === true) ? 1 : 0;
   }
   
   /**
    * Get options
    * 
    * @return array
    */
   public function getOptions()
   {
      return $this->options;
   }
   
   /**
    * Set option
    * 
    * @param string $name
    * @param mixed $value
    * @return void
    */
   public function setOption($name, $value)
   {
      $this->options[$name] = $value;
   }
   
   /**
    * (non-PHPdoc)
    * @see BaseRegisterModel#addSystemToInsert($fields, $values)
    */
   protected function addSystemToInsert(& $fields, & $values)
   {
      parent::addSystemToInsert($fields, $values);
      
      // Set line number
      list($rec_type, $rec_id) = $this->getRecorder();
      
      $lkey  =  $rec_type.$rec_id;
      $dbmap =& $this->conf['db_map'];
      
      if (!empty($this->conf['periodical']))
      {
         $pfield = $this->conf['periodical']['field'];
         $lkey  .= $this->attributes[$pfield];
      }
      
      if (!isset(self::$lines[$lkey]))
      {
         $where[] = '`'.$dbmap['recorder_type']."`='".$rec_type."'";
         $where[] = '`'.$dbmap['recorder_id'].'`='.$rec_id;
         
         if (!empty($pfield))
         {
            $where[] = '`'.$pfield."`='".$this->attributes[$pfield]."'";
         }
         
         $query = 'SELECT `'.$dbmap['line'].'` FROM `'.$dbmap['table'].'` '.
                  'WHERE '.implode(' AND ', $where).' '.
                  'ORDER BY `'.$dbmap['line'].'` DESC'
         ;
         $db  = $this->container->getDBManager();
         $res = $db->loadAssoc($query);
         
         if (is_null($res)) throw new Exception($db->getError());
         
         self::$lines[$lkey] = empty($res) ? 1 : ($res[$dbmap['line']] + 1);
      }
      
      $fields .= ", `".$dbmap['line']."`";
      $values .= ", ".self::$lines[$lkey];
      
      self::$lines[$lkey]++;
      
      // Set operation
      if ($this->conf['register_type'] == 'Balances')
      {
         $fields .= ", `".$dbmap['operation']."`";
         $values .= ", ".$this->attributes[$dbmap['operation']];
      }
      
      // Set active
      $fields .= ", `".$dbmap['active']."`";
      $values .= ", ".($this->attributes[$dbmap['active']] ? 1 : 0);
   }
   
   
   
   
   /************************** For control access rights **************************************/
   
   
   
   /**
    * (non-PHPdoc)
    * @see BaseEntityModel#delete($options)
    */
   public function delete(array& $options = array())
   {
      // Check permissions
      if (defined('IS_SECURE') && !$this->container->getUser()->hasPermission($this->kind.'.'.$this->type.'.Update'))
      {
         return array('Access denied');
      }
      
      // Execute method
      $errors = parent::delete($options);
      
      if (!$errors && $this->options['auto_update_total'])
      {
         $from = $this->attributes[$this->conf['periodical']['field']];
         
         $errors = $this->total->countTotals($from);
      }
      
      return $errors;
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
      return parent::load($id, $options);
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
      if (defined('IS_SECURE') && !$this->container->getUser()->hasPermission($this->kind.'.'.$this->type.'.Update'))
      { 
         return array('Access denied');
      }
      
      // Execute method
      $errors = parent::save($options);
      
      if (!$errors && $this->options['auto_update_total'])
      {
         $from = $this->attributes[$this->conf['periodical']['field']];
         
         $errors = $this->total->countTotals($from);
      }
      
      return $errors;
   }
}
