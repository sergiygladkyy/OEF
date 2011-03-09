<?php

require_once('lib/model/catalogs/CatalogsModel.php');
require_once('lib/model/base/IHierarchyCModel.php');

class CatalogsHierarchyModel extends CatalogsModel implements IHierarchyCModel 
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
    * @see BaseObjectsModel#initialize($kind, $type)
    */
   protected function initialize($kind, $type)
   {
      if (!parent::initialize($kind, $type)) return false;
      
      $confname = self::getConfigurationName($kind, $type);
      
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
      
      return true;
   }
   
   /**
    * (non-PHPdoc)
    * @see IHierarchyCModel#getHierarchically($nodeId, $options)
    */
   public function getHierarchically($nodeId = null, array $options = array())
   {
      $list = array();
      $pkey = $this->conf['db_map']['pkey'];
      $res  = $this->getChildren($nodeId, $options);
      
      foreach ($res as $row)
      {
         $list[] = $row;
         $list   = array_merge($list, $this->getHierarchically($row[$pkey], $options));
      }
      
      return $list;
   }
   
   /**
    * (non-PHPdoc)
    * @see IHierarchyCModel#getChildren($nodeId, $options)
    */
   public function getChildren($nodeId, array $options = array())
   {
      unset($options['attributes'], $options['values']);
      
      $options['criterion'] = "WHERE `Parent`=".(empty($nodeId) ? 0 : (int) $nodeId);
      
      return $this->getEntities(null, $options);
   }
   
   /**
    * (non-PHPdoc)
    * @see IHierarchyCModel#getParent($nodeId, $options)
    */
   public function getParent($nodeId, array $options = array())
   {
      $model = $this->container->getModel($this->kind, $this->type);
      
      if (!$model->load($nodeId)) return null;
      
      $parent = $model->getAttribute('Parent');
      
      return empty($parent) ? array() : $parent->toArray($options);
   }
   
   /**
    * (non-PHPdoc)
    * @see IHierarchyCModel#getParents($nodeId, $options)
    */
   public function getParents($nodeId, array $options = array())
   {
      $res = array();
      
      $parent = $this->container->getModel($this->kind, $this->type);
      
      if (!$parent->load($nodeId)) return null;
      
      $res[] = $parent->toArray($options);
      
      while ($parent = $parent->getAttribute('Parent'))
      {
         array_unshift($res, $parent->toArray($options));
      }
      
      return $res;
   }
   
   /**
    * (non-PHPdoc)
    * @see IHierarchyCModel#getSiblings($nodeId, $options)
    */
   public function getSiblings($nodeId, array $options = array())
   {
      $model = $this->container->getModel($this->kind, $this->type);
      
      if (!$model->load($nodeId)) return null;
      
      $parent = $model->getAttribute('Parent');
      
      $parent = empty($parent) ? 0 : $parent->getId();
      
      return $this->getChildren($parent, $options);
   }
}
