<?php 

require_once('lib/model/base/BaseObjectsModel.php');

class CatalogsModel extends BaseObjectsModel
{
   const kind = 'catalogs';
   
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
    * @see lib/model/base/BaseEntitiesModel#retrieveSelectData($options)
    */
   public function retrieveSelectData(array $options = array())
   {
      // Check permissions
      if (defined('IS_SECURE') && !$this->container->getUser()->hasPermission($this->kind.'.'.$this->type.'.View'))
      {
         return array();
      }
      
      // Execute method
      $db_map =& $this->conf['db_map'];
      $query  = "SELECT `".$db_map['pkey']."`, `Description`, `".$db_map['deleted']."` FROM `".$db_map['table']."` ORDER BY `Description` ASC";
      
      $db  = $this->container->getDBManager($options);
      $res = $db->executeQuery($query);
      
      if (is_null($res)) return array();
      
      $list = array();
      
      while ($row = $db->fetchArray($res)) $list[] = array('value' => $row[0], 'text' => $row[1], 'deleted' => $row[2]);
      
      return $list;
   }
   
   /**
    * (non-PHPdoc)
    * @see lib/model/base/BaseObjectsModel#retrieveLinkData($ids, $options)
    */
   public function retrieveLinkData($ids, array $options = array())
   {
      // Check permissions
      if (defined('IS_SECURE') && !$this->container->getUser()->hasPermission($this->kind.'.'.$this->type.'.View'))
      {
         return null;
      }
      
      // Execute method
      $db_map =& $this->conf['db_map'];
      $params =  $this->retrieveCriteriaQuery($db_map, $ids, $options);
      
      if (!empty($params['errors'])) return null;
      
      $db    = $this->container->getDBManager($options);
      $query = "SELECT `".$db_map['pkey']."`, `".$db_map['deleted']."`, `Description` FROM `".$db_map['table']."` ".$params['criteria'];
      
      $db  = $this->container->getDBManager($options);
      $res = $db->executeQuery($query);
      
      if (is_null($res)) return null;
      
      $list = array();
      
      while ($row = $db->fetchArray($res))
      {
         $list[$row[0]] = array('value' => $row[0], 'text' => $row[2], 'deleted' => $row[1]);
      }
      
      return $list;
   }
   
   /**
    * (non-PHPdoc)
    * @see lib/model/base/BaseObjectsModel#getLinkDataByRow($row, $options)
    */
   public function getLinkDataByRow(array $row, array $options = array())
   {
      $db_map =& $this->conf['db_map'];
      
      return array(
         'value'   => $row[$db_map['pkey']],
         'text'    => $row['Description'],
         'deleted' => $row[$db_map['deleted']]
      );
   }
   
   protected function retrieveLinksDescriptions(array& $list, array& $options = array())
   {
      $ids = array();
      $own = array();
      
      // retrieve related ids
      foreach ($list as $entity)
      {
         foreach ($this->conf['references'] as $field => $param)
         {
            if ($entity[$field] > 0) $ids[$field][] = $entity[$field];
         }
         
         if (!empty($this->conf['owners']))
         {
            if ($entity['OwnerId'] > 0)
            {
               $own[$entity['OwnerType']][$entity['OwnerId']] = $entity['OwnerId'];
            }
         }
      }
      
      $result = array();
      
      // retrieve link descriptions
      foreach ($this->conf['references'] as $field => $param)
      {
         if (empty($ids[$field])) continue;
         
         $ids[$field] = array_unique($ids[$field]);
         $cmodel = $this->container->getCModel($param['kind'], $param['type'], $options);
         
         $result[$field] = $cmodel->retrieveLinkData($ids[$field]);
      }
      
      foreach ($own as $otype => $ids)
      {
         $cmodel = $this->container->getCModel($this->kind, $otype, $options);
         
         $result['OwnerType'][$otype] = $cmodel->retrieveLinkData($ids);
      }
      
      return $result;
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
      if (!is_array($fields)) $fields = empty($fields) ? array() : array($fields => true);
      
      if (!empty($this->conf['owners']) && isset($fields['OwnerType']) || isset($options['otype']))
      {
         $owner = $this->retrieveSelectOwner($options);
         
         unset($fields['OwnerType']);
      }
      
      if (!(!empty($owner) && empty($fields)))
      {
         $result = parent::retrieveSelectDataForRelated($fields, $options);
      }
      
      if (isset($owner)) $result['OwnerType'] = $owner;
      
      return $result;
   }
   
   /**
    * Get select data for OwnerId field
    * 
    * @param array $options
    * @return array
    */
   public function retrieveSelectOwner(array $options = array())
   {
      if (empty($this->conf['owners'])) return array();
      
      // Check permissions
      if (defined('IS_SECURE') && !$this->container->getUser()->hasPermission($this->kind.'.'.$this->type.'.Read'))
      {
         return array();
      }
      
      // Execute method
      if (empty($options['otype']) || !in_array($options['otype'], $this->conf['owners']))
      {
         return array();
      }
      
      $otype = $options['otype'];
      $oid   = isset($options['oid']) ? (int) $options['oid'] : 0;
      
      $model = $this->container->getCModel($this->kind, $otype, $options);
      
      return $oid ? $model->retrieveLinkData($oid, $options) : $model->retrieveSelectData($options);
   }
   
   
   
   
   /************************** For control access rights **************************************/
   
   
   
   /**
    * (non-PHPdoc)
    * @see BaseObjectsModel#delete($values, $options)
    */
   public function delete($values, array $options = array())
   {
      // Check permissions
      if (defined('IS_SECURE') && !$this->container->getUser()->hasPermission($this->kind.'.'.$this->type.'.Delete'))
      {
         return array();
      }
      
      // Execute method
      return parent::delete($values, $options);
   }
   
   /**
    * (non-PHPdoc)
    * @see BaseObjectsModel#retrieveTabularSections($ids, $types, $options)
    */
   public function retrieveTabularSections($ids, $types = array(), array $options = array())
   {
      // Check permissions
      if (defined('IS_SECURE') && !$this->container->getUser()->hasPermission($this->kind.'.'.$this->type.'.Read'))
      {
         return array();
      }
      
      // Execute method
      return parent::retrieveTabularSections($ids, $types, $options);
   }
   
   /**
    * (non-PHPdoc)
    * @see BaseObjectsModel#getTabularsList($options)
    */
   public function getTabularsList(array $options = array())
   {
      // Check permissions
      if (defined('IS_SECURE') && !$this->container->getUser()->hasPermission($this->kind.'.'.$this->type.'.Read'))
      {
         return array();
      }
      
      // Execute method
      return parent::getTabularsList($options);
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
}  