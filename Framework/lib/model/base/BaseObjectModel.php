<?php

require_once('lib/model/base/BaseEntityModel.php');

class BaseObjectModel extends BaseEntityModel
{
   /**
    * (non-PHPdoc)
    * @see lib/model/base/BaseModel#setup($kind, $type)
    */
   protected function initialize($kind, $type)
   {
      if (!parent::initialize($kind, $type)) return false;
      
      $confname = self::getConfigurationName($kind, $type);

      if (isset(self::$config[$confname]['relations'])) return true;
      
      $relations = $this->container->getConfigManager()->getInternalConfiguration('relations', $kind);

      self::$config[$confname]['relations'] = isset($relations[$type]) ? $relations[$type] : array();
      
      return true;
   }
   
   /**
    * Mark for deletion
    * 
    * @param array& $options
    * @return array - errors
    */
   public function markForDeletion(array& $options = array())
   {
      return $this->changeMarkForDeletion(true, $options);
   }
   
   /**
    * Unmark for deletion
    * 
    * @param array& $options
    * @return array - errors
    */
   public function unmarkForDeletion(array& $options = array())
   {
      return $this->changeMarkForDeletion(false, $options);
   }
   
   /**
    * Change MarkForDeletion flag
    * 
    * @param boolean $mark
    * @param array&  $options
    * @return array - errors
    */
   protected function changeMarkForDeletion($mark, array& $options = array())
   {
      if ($this->isNew) return array();
      
      $db    =  $this->container->getDBManager($options);
      $dbmap =& $this->conf['db_map'];
      $query =  "UPDATE `".$dbmap['table']."` SET `".$dbmap['deleted']."` = ".($mark ? 1 : 0)." WHERE `".$dbmap['pkey']."`=".$this->id;
       
      if (!$db->executeQuery($query))
      {
         return array($db->getError());
      }
      
      return array();
   }
   
   /**
    * Check mark for deletion flag
    * 
    * @return boolean (true if entity marked for deletion)
    */
   public function isMarkedForDeletion()
   {
      if ($this->isNew) return false;
      
      return !empty($this->attributes[$this->conf['db_map']['deleted']]);
   }
}