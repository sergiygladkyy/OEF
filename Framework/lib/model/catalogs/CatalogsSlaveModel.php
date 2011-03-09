<?php

require_once('lib/model/catalogs/CatalogsModel.php');
require_once('lib/model/base/ISlaveCModel.php');

class CatalogsSlaveModel extends CatalogsModel implements ISlaveCModel
{
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
    * @see CatalogsHierarchyModel#initialize($kind, $type)
    */
   protected function initialize($kind, $type)
   {
      if (!parent::initialize($kind, $type)) return false;
      
      $confname = self::getConfigurationName($kind, $type);
      
      // owners
      if (!isset(self::$config[$confname]['owners']))
      {
         $CManager = $this->container->getConfigManager();
         
         self::$config[$confname]['owners'] = $CManager->getInternalConfiguration($kind.'.owners', $type);
      }
      
      return true;
   }
   
   /**
    * (non-PHPdoc)
    * @see ISlaveCModel#getByOwner($type, $id, $options)
    */
   public function getByOwner($type, $id, array $options = array())
   {
      if (!in_array($type, $this->conf['owners']))
      {
         return null;
      }
      
      unset($options['attributes'], $options['values']);
      
      $options['criterion'] = "WHERE `OwnerType`='".$type."' AND `OwnerId`=".((int) $id);
      
      return $this->getEntities(null, $options);
   }
   
   /**
    * (non-PHPdoc)
    * @see ISlaveCModel#markForDeletionByOwner($type, $id, $options)
    */
   public function markForDeletionByOwner($type, $id, array $options = array())
   {
      if (!in_array($type, $this->conf['owners']))
      {
         return array('Unknow owner type '.$type);
      }
      
      unset($options['attributes'], $options['values']);
      
      $options['criterion'] = "WHERE `OwnerType`='".$type."' AND `OwnerId`=".((int) $id);
      
      return $this->markForDeletion(true, $options);
   }
   
   /**
    * (non-PHPdoc)
    * @see ISlaveCModel#deleteByOwner($type, $id, $options)
    */
   public function deleteByOwner($type, $id, array $options = array())
   {
      if (!in_array($type, $this->conf['owners']))
      {
         return array('Unknow owner type '.$type);
      }
      
      unset($options['attributes'], $options['values']);
      
      $options['criterion'] = "WHERE `OwnerType`='".$type."' AND `OwnerId`=".((int) $id);
      
      return $this->delete(true, $options);
   }
}
