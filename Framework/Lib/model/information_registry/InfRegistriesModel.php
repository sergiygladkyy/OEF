<?php

require_once('lib/model/base/BaseEntitiesModel.php');

class InfRegistriesModel extends BaseEntitiesModel
{
   const kind = 'information_registry';
   
   protected static $instance = array();

   protected function __construct($type, array& $options = array())
   {
      parent::__construct(self::kind, $type, $options);
   }
   
   /**
    * Get instance
    *
    * @return this
    */
   public static function getInstance($type, array $options = array())
   {
      if(empty(self::$instance[$type]))
      {
         self::$instance[$type] = new self($type, $options);
      }

      return self::$instance[$type];
   }
   
   /**
    * (non-PHPdoc)
    * @see lib/model/base/BaseModel#setup($kind, $type)
    */
   protected function initialize($kind, $type)
   {
      if (!parent::initialize($kind, $type)) return false;
      
      $confname = self::getConfigurationName($kind, $type);

      if (isset(self::$config[$confname]['dimensions'])) return true;
      
      self::$config[$confname]['dimensions'] = $this->container->getConfigManager()->getInternalConfigurationByKind($kind.'.dimensions', $type);
      
      return true;
   }
}
