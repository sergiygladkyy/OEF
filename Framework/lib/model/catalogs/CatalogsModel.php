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
      $db_map =& $this->conf['db_map'];
      $query  = "SELECT `".$db_map['pkey']."`, `Description` FROM `".$db_map['table']."` WHERE `".$db_map['deleted']."`=0 ORDER BY `Description` ASC";
      
      $db  = $this->container->getDBManager($options);
      $res = $db->executeQuery($query);
      
      if (is_null($res)) return array();
      
      $list = array();
      
      while ($row = $db->fetchArray($res)) $list[] = array('value' => $row[0], 'text' => $row[1]);
      
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
      $query  = "SELECT `".$db_map['pkey']."`, `".$db_map['deleted']."`, `Description` FROM `".$db_map['table']."` WHERE `".$db_map['pkey']."` IN (".implode(',', $ids).")";
      
      $db  = $this->container->getDBManager($options);
      $res = $db->executeQuery($query);
      
      if (is_null($res)) return array();
      
      $list = array();
      
      while ($row = $db->fetchArray($res)) $list[$row[0]] = array('value' => $row[0], 'text' => $row[2], 'deleted' => $row[1]);
      
      return $list;
   }

}  