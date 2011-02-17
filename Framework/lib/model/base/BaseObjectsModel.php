<?php

require_once('lib/model/base/BaseEntitiesModel.php');

abstract class BaseObjectsModel extends BaseEntitiesModel
{
   /**
    * (non-PHPdoc)
    * @see lib/model/base/BaseModel#setup($kind, $type)
    */
   protected function initialize($kind, $type)
   {
      if (!parent::initialize($kind, $type)) return false;
      
      $confname = self::getConfigurationName($kind, $type);

      if (isset(self::$config[$confname]['relations']) && isset(self::$config[$confname]['tabulars'])) return true;
      
      $CManager  = $this->container->getConfigManager();
      
      // relations
      if (!isset(self::$config[$confname]['relations']))
      {
         $relations = $CManager->getInternalConfiguration('relations', $kind);

         self::$config[$confname]['relations'] = isset($relations[$type]) ? $relations[$type] : array();
      }
      
      // tabulars
      if (!isset(self::$config[$confname]['tabulars']))
      {
         self::$config[$confname]['tabulars'] = $CManager->getInternalConfiguration($kind.'.tabulars.tabulars', $type);
      }
      
      return true;
   }
   
   
   
   /**
    * Mark for deletion
    * 
    * @param mixed $values - array or value
    * @param array& $options
    * @return array - errors
    */
   public function markForDeletion($values, array& $options = array())
   {
      if (empty($values)) return array();
      
      return $this->changeDeletionMark($values, true, $options);
   }
   
   /**
    * Unmark for deletion
    * 
    * @param mixed $values - array or value
    * @param array $options
    * @return array - errors
    */
   public function unmarkForDeletion($values, array $options = array())
   {
      if (empty($values)) return array();
      
      return $this->changeDeletionMark($values, false, $options);
   }
   
   /**
    * Change mark for deletion flag
    * 
    * @param mixed&  $values
    * @param boolean $mark
    * @param array&  $options
    * @return array - errors
    */
   protected function changeDeletionMark(& $values, $mark, array& $options = array())
   {
      $db_map =& $this->conf['db_map'];
      $params =  $this->retrieveCriteriaQuery($db_map, $values, $options);
      
      if (!empty($params['errors'])) return $params['errors'];
      
      extract($params, EXTR_OVERWRITE);
      
      $db =  $this->container->getDBManager($options);
      
      $query = 'UPDATE `'.$db_map['table'].'` SET `'.$db_map['deleted'].'`='.($mark ? 1 : 0).' '.$criteria;
      
      if (!$db->executeQuery($query))
      {
         return array($db->getError());
      }
      
      return array();
   }
   
   
   
   
   /**
    * Retrieve values for select box
    * 
    * @param array $options
    * @return array
    */
   abstract public function retrieveSelectData(array $options = array());
   
   /**
    * Retrieve values with references info
    * 
    * @param mixed $ids
    * @param array $options
    * @return array
    */
   abstract public function retrieveLinkData($ids, array $options = array());
   
   /**
    * Retrieve tabular sections params
    * 
    * @param mixed $ids   - parent ids (ids this entity)
    * @param mixed $types - tabular section types (by default - all)
    * @param array $options
    * @return array
    */
   public function retrieveTabularSections($ids, $types = array(), array $options = array())
   {
      $result = array();
      
      if (!empty($types))
      {
         if (!is_array($types)) $types = array($types);
         
         $types = array_intersect($this->conf['tabulars'], $types);
      }
      else $types = $this->conf['tabulars'];
      
      $options = array_merge(array('with_link_desc' => true, 'with_select' => true, 'attributes' => 'Owner'), $options);
      
      foreach ($types as $type)
      {
         $model = $this->container->getCModel($this->kind.'.'.$this->type.'.tabulars', $type, $options);
         if (!empty($ids)) $result[$type] = $model->getEntities($ids, $options);
         if (!empty($options['with_select']))
         {
            $result[$type]['select'] = $model->retrieveSelectDataForRelated(array(), $options);
         } 
      }
      
      return $result;
   }
   
   /**
    * Get tabular sections list
    * 
    * @param array $options
    * @return array
    */
   public function getTabularsList(array $options = array())
   {
      return $this->conf['tabulars'];
   }
}
