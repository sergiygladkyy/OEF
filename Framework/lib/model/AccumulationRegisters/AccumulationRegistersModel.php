<?php

require_once('lib/model/base/BaseRegistersModel.php');

class AccumulationRegistersModel extends BaseRegistersModel
{
   const kind = 'AccumulationRegisters';
   
   protected static $instance = array();
   
   protected
      $total   = null,
      $options = array(
         'auto_update_total' => true
      );
   
   protected function __construct($type, array& $options = array())
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
         require_once('lib/model/AccumulationRegisters/BalancesModel.php');
         
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

      if (!isset(self::$config[$confname]['periodical']))
      {
         self::$config[$confname]['periodical'] = $this->container->getConfigManager()->getInternalConfigurationByKind($kind.'.periodical', $type);
      }
      
      if (!isset(self::$config[$confname]['register_type']))
      {
         self::$config[$confname]['register_type'] = $this->container->getConfigManager()->getInternalConfigurationByKind($kind.'.register_type', $type);
      }
      
      return true;
   }
   
   /**
    * Get instance
    *
    * @return this
    */
   public static function getInstance($type, array $options = array())
   {
      if (empty(self::$instance[$type]))
      {
         self::$instance[$type] = new self($type, $options);
      }

      return self::$instance[$type];
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
    * Get totals
    * 
    * @param string $date
    * @param array  $options
    * @return array
    */
   public function getTotals($date = null, array $options = array())
   {
      // Check permissions
      if (defined('IS_SECURE') && !$this->container->getUser()->hasPermission($this->kind.'.'.$this->type.'.Read'))
      {
         return array();
      }
      
      // Execute method
      return $this->total->getTotals($date, $options);
   }
   
   /**
    * Count totals
    * 
    * @param string $from - date from
    * @return array - errors
    */
   public function countTotals($from = null)
   {
      // Check permissions
      if (defined('IS_SECURE') && !$this->container->getUser()->hasPermission($this->kind.'.'.$this->type.'.Read'))
      {
         return array('Access denied');
      }
      
      // Execute method
      return $this->total->countTotals($from);
   }
   
   
   
   
   /************************** For control access rights **************************************/
   
   
   
   /**
    * (non-PHPdoc)
    * @see BaseEntitiesModel#delete($values, $options)
    */
   public function delete($values, array $options = array())
   {
      // Check permissions
      if (defined('IS_SECURE') && !$this->container->getUser()->hasPermission($this->kind.'.'.$this->type.'.Update'))
      {
         return array('Access denied');
      }
      
      // Execute method
      if (empty($values)) return array();
      
      $db_map =& $this->conf['db_map'];
      $params =  $this->retrieveCriteriaQuery($db_map, $values, $options);
      
      if (!empty($params['errors'])) return $params['errors'];
      
      $db = $this->container->getDBManager($options);
      
      // Prepare auto update totals
      if ($this->options['auto_update_total'])
      {
         $periods = array();
         $pField  = $this->conf['periodical']['field'];
         $query   = 'SELECT `'.$db_map['pkey'].'`, `'.$pField.'` FROM `'.$db_map['table'].'` '.$params['criteria'];

         if (!($res = $db->executeQuery($query)))
         {
            return array($db->getError());
         }

         while ($row = $this->fetchAssoc($res))
         {
            $periods[$row[$pField]] = $row[$pField];
            $ids[] = $row[$db_map['pkey']];
         }

         if (empty($ids)) return array();

         $query = 'DELETE FROM `'.$db_map['table'].'` WHERE `'.$db_map['pkey'].'` IN ('.implode(',', $ids).')';
      }
      else $query = 'DELETE FROM `'.$db_map['table'].'` '.$params['criteria'];
      
      // Delete
      if (!$db->executeQuery($query))
      {
         return array($db->getError());
      }
      
      // Update totals
      if ($this->options['auto_update_total'])
      {
         return $this->total->countTotals($periods);
      }
      
      return array();   
   }
   
   /**
    * (non-PHPdoc)
    * @see BaseEntitiesModel#getEntities($values, $options)
    */
   public function getEntities($values = null, array $options = array())
   {
      // Check permissions
      if (defined('IS_SECURE') && !$this->container->getUser()->hasPermission($this->kind.'.'.$this->type.'.Read'))
      {
         return array();
      }
      
      // Execute method
      return parent::getEntities($values, $options);
   }

   /**
    * (non-PHPdoc)
    * @see BaseEntitiesModel#hasEntities($values, $options)
    */
   public function hasEntities($values, array $options = array())
   {
      // Check permissions
      if (defined('IS_SECURE') && !$this->container->getUser()->hasPermission($this->kind.'.'.$this->type.'.Read'))
      {
         return false;
      }
      
      // Execute method
      return parent::hasEntities($values, $options);
   }
   
   /**
    * (non-PHPdoc)
    * @see BaseEntitiesModel#countEntities($values, $options)
    */
   public function countEntities($values = null, array $options = array())
   {
      // Check permissions
      if (defined('IS_SECURE') && !$this->container->getUser()->hasPermission($this->kind.'.'.$this->type.'.Read'))
      {
         return 0;
      }
      
      // Execute method
      return parent::countEntities($values, $options);
   }

   
   /**
    * (non-PHPdoc)
    * @see BaseEntitiesModel#retrieveSelectDataForRelated($fields, $options)
    */
   public function retrieveSelectDataForRelated($fields = array(), array $options = array())
   {
      // Check permissions
      if (defined('IS_SECURE') && !$this->container->getUser()->hasPermission($this->kind.'.'.$this->type.'.Read'))
      {
         return array();
      }
      
      // Execute method
      return parent::retrieveSelectDataForRelated($fields, $options);
   }
}
