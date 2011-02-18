<?php

abstract class BaseModel
{
   /* Entities configuration */
   protected static $config = array();
   protected static $db_map = array();
   
   /* This entity params */
   protected $kind = null;
   protected $type = null;
   protected $conf = null;
   
   
   /**
    * Return entity kind
    * 
    * @return string
    */
   public function getKind()
   {
      return $this->kind;
   }
   
   /**
    * Return type entity
    * 
    * @return string
    */
   public function getType()
   {
      return $this->type;
   }

   /**
    * Initialize entity object (retrieve configuration)
    * 
    * @param string $type - entity name
    * @return boolean
    */
   protected function initialize($kind, $type)
   {
      // Setup configuration
      if (!self::setup($kind, $type)) return false;
      
      $confname   =  self::getConfigurationName($kind, $type);
      $this->type =  $type;
      $this->kind =  $kind;
      $this->conf =& self::$config[$confname];
      
      return true;
   }
   
   
   /**
    * Setup entity configuration
    * 
    * @param string $kind - entity kind
    * @param string $type - entity type
    * @return boolean
    */
   protected static function setup($kind, $type = null)
   {
      $confname = self::getConfigurationName($kind, $type);
      
      if (isset(self::$config[$confname])) return true;
      
      $CManager = Container::getInstance()->getConfigManager();
      
      $pkind = Utility::parseKindString($kind);
      
      if ($type !== null)
      {
         // has entity with this type ?
         if (!in_array($type, $CManager->getInternalConfigurationByKind($kind.'.'.$pkind['kind'])))
         {
            throw new Exception(__METHOD__.': "'.ucfirst($kind).'" with name "'.$type.'" not exists.');
         }
      }
      
      // retrieve db_map
      if (!self::$db_map)
      {
         self::$db_map = $CManager->getInternalConfiguration('db_map');
      }
      
      // retrieve internal configuration
      self::$config[$confname]['attributes'] = $CManager->getInternalConfigurationByKind($kind.'.fields', $type);
      self::$config[$confname]['types']      = $CManager->getInternalConfigurationByKind($kind.'.field_type', $type);
      self::$config[$confname]['precision']  = $CManager->getInternalConfigurationByKind($kind.'.field_prec', $type);
      self::$config[$confname]['references'] = $CManager->getInternalConfigurationByKind($kind.'.references', $type);
      self::$config[$confname]['required']   = $CManager->getInternalConfigurationByKind($kind.'.required', $type);
      
      if ($kind == 'reports') return true;
      
      // retrieve internal db_map
      if (isset($pkind['main_kind']))
      {
         self::$config[$confname]['db_map'] =& self::$db_map[$pkind['main_kind']][$pkind['main_type']][$pkind['kind']][$type];
      }
      else
      {
         self::$config[$confname]['db_map'] =& self::$db_map[$pkind['kind']][$type];
      }
      
      return true;
   }
   
   /**
    * Get configuration name
    * 
    * @param string $kind - entity kind
    * @param string $type - entity type
    * @return string
    */
   protected static function getConfigurationName($kind, $type)
   {
      return ($kind ? $kind.'.' : '').$type;
   }
   
   /**
    * Return true if entity exist
    * 
    * @param string $kind - entity kind
    * @param string $type - entity type
    * @param int $id - entity id
    * @return boolean or null
    */
   protected static function hasEntity($kind, $type, $id, array $options = array())
   {
      $confname = $kind.".".$type;
      
      if (!isset(self::$config[$confname]))
      {
         if (!self::setup($kind, $type)) throw new Exception(__METHOD__.': Can\'t load internal configuration for "'.$kind.'.'.$type.'"');
      }
      
      if (!isset(self::$config[$confname]['db_map'])) throw new Exception(__METHOD__.': "'.$kind.'.'.$type.'" is not storage entity');
      
      $dbmap =& self::$config[$confname]['db_map'];
      $key   =  empty($options['pkey']) ? $dbmap['pkey'] : $options['pkey'];
      $query =  "SELECT count(*) AS cnt ".
                "FROM `".$dbmap['table']."` ".
                "WHERE `".$key."`=".(int) $id;
      $db  = Container::getInstance()->getDBManager();
      $res = $db->loadAssoc($query);
      
      return (is_null($res) ? null : ($res['cnt'] ? true : false));
   }
}
