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
      
      $options['criterion'] = "WHERE `Parent`=".(empty($nodeId) ? 0 : (int) $nodeId).' ORDER BY ';
      
      if (isset($this->conf['db_map']['folder']))
      {
         $options['criterion'] .=  '`'.$this->conf['db_map']['folder'].'` DESC, ';
      }
      
      $options['criterion'] .= '`Description` ASC';
      
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
   
   /**
    * (non-PHPdoc)
    * @see lib/model/base/BaseEntitiesModel#retrieveSelectDataForRelated($fields, $options)
    */
   public function retrieveSelectDataForRelated($fields = array(), array $options = array())
   {
      // Check permissions
      if (defined('IS_SECURE') && !$this->container->getUser()->hasPermission($this->kind.'.'.$this->type.'.Read'))
      {
         return array();
      }
      
      // Execute method
      if (empty($fields))
      {
         $fields = $this->conf['types'];
      }
      
      if (isset($fields['Parent']))
      {
         $parent = $this->retrieveSelectParent($options); 
         
         unset($fields['Parent']);
      }
      
      if (!empty($fields))
      {
         $result = parent::retrieveSelectDataForRelated($fields, $options);
      }
      
      if (isset($parent)) $result['Parent'] = $parent;
      
      return $result;
   }
   
   /**
    * Get data for select
    * 
    * @param array $options
    * @return array
    */
   public function retrieveSelectParent(array $options = array())
   {
      // Check permissions
      if (defined('IS_SECURE') && !$this->container->getUser()->hasPermission($this->kind.'.'.$this->type.'.Read'))
      {
         return array();
      }
      
      // Execute method
      $db = $this->container->getDBManager($options);
      
      $db_map =& $this->conf['db_map'];
      $wh_add = ($this->conf['hierarchy']['type'] == 2) ? " AND `".$db_map['folder']."`=1" : '';
      
      if (isset($options['pkey']) && is_numeric($options['pkey']) && 0 < (int) $options['pkey'])
      {
         $id = (int) $options['pkey'];
         $ids[$id] = $id;
         $stack = array(0 => array($id));
         $cur   = 0;
         
         while (!empty($stack))
         {
            if (!$row = each($stack[$cur]))
            {
               unset($stack[$cur]);
               
               $cur--;
               
               if ($cur < 0) break;
               
               continue;
            }
            
            $query = "SELECT `".$db_map['pkey']."` FROM `".$db_map['table']."` WHERE `Parent`=".$row[1].$wh_add;
            
            if (null === ($res = $db->executeQuery($query)))
            {
               return array();
            }
            
            unset($stack[$cur][$row[0]]);
            
            if ($db->getNumRows($res))
            {
               $cur++;
               $stack[$cur] = array();
               
               while ($r = $db->fetchRow($res))
               {
                  $stack[$cur][] = $r[0];
                  $ids[$r[0]]    = $r[0];
               }
            }
         }
         
         $wh_add .= " AND `".$db_map['pkey']."` NOT IN(".implode(',', $ids).")";
      }
      
      $query = "SELECT `".$db_map['pkey']."`, `Description` FROM `".$db_map['table']."` WHERE `".$db_map['deleted']."`=0".$wh_add." ORDER BY `Description` ASC";
      
      if (null === ($res = $db->executeQuery($query)))
      {
         return array();
      }
      
      $list = array();
      
      while ($row = $db->fetchArray($res)) $list[/*$row[0]*/] = array('value' => $row[0], 'text' => $row[1]);
      
      return $list;
   }
}
