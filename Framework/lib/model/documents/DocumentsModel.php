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
      $db_map =& $this->conf['db_map'];
      $query  = "SELECT `".$db_map['pkey']."`, `date` FROM `".$db_map['table']."` WHERE `".$db_map['deleted']."`=0 ORDER BY `date` ASC";
      
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
      if (empty($ids)) return array();
      
      if (!is_array($ids)) $ids = array($ids);
      
      $db_map =& $this->conf['db_map'];
      $query  = "SELECT `".$db_map['pkey']."`, `".$db_map['deleted']."`, `date` FROM `".$db_map['table']."` WHERE `".$db_map['pkey']."` IN (".implode(',', $ids).")";
      
      $db  = $this->container->getDBManager($options);
      $res = $db->executeQuery($query);
      
      if (is_null($res)) return array();
      
      $list = array();
      
      while ($row = $db->fetchArray($res)) $list[$row[0]] = array('value' => $row[0], 'text' => $this->type.' '.date("Y-m-d H:i:s", strtotime($row[2])), 'deleted' => $row[1]);
      
      return $list;
   }
   
   /**
    * (non-PHPdoc)
    * @see lib/model/base/BaseObjectsModel#markAsRemoved($values, $options)
    */
   public function markAsRemoved($values, array& $options = array())
   {
      if (empty($values)) return array();
      
      if (!$this->hasEntities($values, $options)) return array();
      
      $errors = $this->unpost($values, $options);
      
      if (!empty($errors)) return $errors;
      
      return $this->changeRemovedMark($values, true, $options);
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
      $db_map =& $this->conf['db_map'];
      $params =  $this->retrieveQueryParams($db_map, $values, $options);
      
      if (!empty($params['errors'])) return $params['errors'];
      
      $where = empty($params['where']) ? 'WHERE `'.$db_map['post'].'` <> 0' : str_replace('WHERE', 'WHERE `'.$db_map['post'].'` <> 0 AND (', $params['where']).')';
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
            $errors[] = 'Can\'t load document "'.$this->type.'" with id "'.((int) $id).'"';
            continue;
         }
          
         $err = $model->unpost($options);

         if ($err)
         {
            $errors[] = 'Document "'.$this->type.'" not unposted: '.implode(", ", $err);
         }
      }
      
      return $errors;
   }
}  