<?php

require_once('lib/model/base/BaseEntitiesModel.php');

class BaseRegistersModel extends BaseEntitiesModel
{
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
