<?php 

require_once('lib/model/base/BaseObjectsModel.php');

class DocumentsModel extends BaseObjectsModel
{
   const kind = 'documents';
   
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
    * @see lib/model/base/BaseEntitiesModel#retrieveArrayForSelect($fields, $options)
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
      $query  = "SELECT `".$db_map['pkey']."`, `Date` FROM `".$db_map['table']."` WHERE `".$db_map['deleted']."`=0 ORDER BY `Date` ASC";
      
      $db  = $this->container->getDBManager($options);
      $res = $db->executeQuery($query);
      
      if (is_null($res)) return array();
      
      $list = array();
      
      while ($row = $db->fetchArray($res)) $list[] = array('value' => $row[0], 'text' => $this->type.' '.date("Y-m-d H:i:s", strtotime($row[1])));
      
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
      $query = "SELECT `".$db_map['pkey']."`, `".$db_map['deleted']."`, `Date` FROM `".$db_map['table']."` ".$params['criteria'];
      
      $res = $db->executeQuery($query);
      
      if (is_null($res)) return null;
      
      $list = array();
      
      while ($row = $db->fetchArray($res))
      {
         $list[$row[0]] = array('value' => $row[0], 'text' => $this->type.' '.date("Y-m-d H:i:s", strtotime($row[2])), 'deleted' => $row[1]);
      }
      
      return $list;
   }
   
   /**
    * (non-PHPdoc)
    * @see lib/model/base/BaseObjectsModel#markForDeletion($values, $options)
    */
   public function markForDeletion($values, array& $options = array())
   {
      if (empty($values)) return array();
      
      $errors = $this->unpost($values, $options);
      
      if (!empty($errors)) return $errors;
      
      return $this->changeDeletionMark($values, true, $options);
   }
   
   /**
    * Unpost by criteria
    * 
    * @param mixed $values
    * @param array $options
    * @return array - errors
    */
   public function unpost($values, array $options = array())
   {
      // Check permissions
      if (defined('IS_SECURE') && !$this->container->getUser()->hasPermission($this->kind.'.'.$this->type.'.UndoPosting'))
      {
         return array('Access denied');
      }
      
      // Execute method
      $db_map =& $this->conf['db_map'];
      $params =  $this->retrieveCriteriaQuery($db_map, $values, $options);
      
      if (!empty($params['errors'])) return $params['errors'];
      
      $where = empty($params['criteria']) ? 'WHERE `'.$db_map['post'].'` <> 0' : str_replace('WHERE', 'WHERE `'.$db_map['post'].'` <> 0 AND (', $params['criteria']).')';
      $query = 'SELECT `'.$db_map['pkey'].'` FROM `'.$db_map['table'].'` '.$where;
      $db    = $this->container->getDBManager($options);
      $res   = $db->loadArrayList($query, array('field' => $db_map['pkey']));
      
      if (is_null($res)) return array($db->getError());
      
      $model  = $this->container->getModel($this->kind, $this->type, $options);
      $errors = array();
      
      foreach ($res as $id)
      {
         if (!$model->load($id, $options))
         {
            $errors[] = 'Can\'t load document '.$this->type.' with id "'.((int) $id).'"';
            continue;
         }
          
         $err = $model->unpost($options);

         if ($err)
         {
            $errors[] = 'Document '.$this->type.' '.$model->getAttribute('Date').' not unposted: '.implode(", ", $err);
         }
      }
      
      return $errors;
   }
   
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
      
      if (empty($values)) return array();
      
      $errors = $this->unpost($values, $options);
      
      if (!empty($errors)) return $errors;
      
      // Execute method
      return parent::delete($values, $options);
   }
   
   
   
   /************************** For control access rights **************************************/
   
   
   
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