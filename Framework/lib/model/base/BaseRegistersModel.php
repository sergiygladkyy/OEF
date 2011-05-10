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

      if (!isset(self::$config[$confname]['dimensions']))
      {
         self::$config[$confname]['dimensions'] = $this->container->getConfigManager()->getInternalConfigurationByKind($kind.'.dimensions', $type);
      }
      
      if (!isset(self::$config[$confname]['recorders']))
      {
         self::$config[$confname]['recorders'] = $this->container->getConfigManager()->getInternalConfigurationByKind($kind.'.recorders', $type);
      }
      
      return true;
   }
   
   /**
    * This entities has recorders?
    * 
    * @return boolean
    */
   public function hasRecorders()
   {
      return !empty($this->conf['recorders']);
   }
   
   /**
    * (non-PHPdoc)
    * @see lib/model/base/BaseEntitiesModel#retrieveLinksDescriptions($list, $options)
    */
   protected function retrieveLinksDescriptions(array& $list, array& $options = array())
   {
      if (!$this->hasRecorders()) return parent::retrieveLinksDescriptions($list, $options);
      
      //@todo next code duplicating code parent::retrieveLinksDescriptions for optimizing
      
      $ids   = array();
      $recs  = array();
      $rtype = $this->conf['db_map']['recorder_type']; 
      $rid   = $this->conf['db_map']['recorder_id'];
      
      // retrieve related ids
      foreach ($list as $entity)
      {
         foreach ($this->conf['references'] as $field => $param)
         {
            if ($entity[$field] > 0) $ids[$field][$entity[$field]] = $entity[$field];
         }
         
         $recs[$entity[$rtype]][$entity[$rid]] = $entity[$rid];
      }
      
      $result = array();
      
      // retrieve link descriptions
      foreach ($this->conf['references'] as $field => $param)
      {
         if (empty($ids[$field])) continue;
         
         $cmodel = $this->container->getCModel($param['kind'], $param['type'], $options);
         
         $result[$field] = $cmodel->retrieveLinkData($ids[$field]);
      }
      
      // retrieve recorder descriptions
      foreach ($recs as $type => $ids)
      {
         $cmodel = $this->container->getCModel('documents', $type, $options);
         
         $result[$rid][$type] = $cmodel->retrieveLinkData($ids);
      }
      
      return $result;
   }
}
