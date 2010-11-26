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
    * Delete entities with this kind and type
    * 
    * @param mixed $values - array or value
    * @param array $options
    * @return array - errors
    */
   public function delete($values, array $options = array())
   {
      if (empty($values)) return array();
      
      return $this->markAsRemoved($values, $options);
   }
   
   /**
    * Restore marked as delete entities with this kind and type
    * 
    * @param mixed $values - array or value
    * @param array $options
    * @return array - errors
    */
   public function restore($values, array $options = array())
   {
      if (empty($values)) return array();
      
      if (!$this->hasEntities($values, $options)) return array();
      
      return $this->changeRemovedMark($values, false, $options);
   }
   
   /**
    * Merk entities with this kind and type as removed
    * 
    * @param mixed $values - array or value
    * @param array& $options
    * @return array - errors
    */
   public function markAsRemoved($values, array& $options = array())
   {
      if (empty($values)) return array();
      
      if (!$this->hasEntities($values, $options)) return array();
      
      return $this->changeRemovedMark($values, true, $options);
   }
   
   /**
    * Change markRemove flag
    * 
    * @param mixed& $values
    * @param boolean $remove
    * @param array& $options
    * @return array - errors
    */
   protected function changeRemovedMark(& $values, $remove, array& $options = array())
   {
      $db_map =& $this->conf['db_map'];
      $params =  $this->retrieveCriteriaQuery($db_map, $values, $options);
      
      if (!empty($params['errors'])) return $params['errors'];
      
      extract($params, EXTR_OVERWRITE);
      
      $related =& $this->conf['relations'];
      $db      =  $this->container->getDBManager($options);
      
      // Change related
      if (!$remove)
      {
         $method = 'restoreRelated';
         
         if (!empty($related['catalogs']))  $rel['catalogs']  =& $related['catalogs'];
         if (!empty($related['documents'])) $rel['documents'] =& $related['documents'];
      }
      else
      {
         $method = 'removeRelated';
         $rel = $related;
      }
      
      if (!empty($rel))
      {
          // retrieve ids
         if (!in_array($db_map['pkey'], $fields))
         {
            $query  = 'SELECT `'.$db_map['pkey'].'` FROM `'.$db_map['table'].'` '.$criteria;
            $ids    = $db->loadArrayList($query, array('field' => $db_map['pkey']));
            
            if (is_null($ids)) return array($db->getError());
         }
         else $ids =& $values;
         
         if (!empty($ids)) $errors = $this->$method($rel, $ids, $options);
         
         if (!empty($errors)) return $errors;
      }
      
      // Change this mark
      $query  = 'UPDATE `'.$db_map['table'].'` SET `'.$db_map['deleted'].'`='.($remove ? 1 : 0).' '.$criteria;
      
      if (!$db->executeQuery($query))
      {
         return array($db->getError());
      }
      
      return array();
   }
   
   /**
    * Remove related entities
    * 
    * @param array& $related - relation map to this entities
    * @param array& $ids - ids this entities
    * @param array $options
    * @return array - errors
    */
   protected function removeRelated(array& $related, array& $ids, array& $options = array())
   {
      $errors = array();
      static $checked = array();
      static $count = 0;
      
      $count++;
      
      foreach ($related as $kind => $params)
      {
         foreach ($params as $type => $fields)
         {
            if (!empty($checked[$kind.$type])) continue;
            
            $checked[$kind.$type] = true;
            
            $model = $this->container->getCModel($kind, $type, $options);
            $err   = $model->delete($ids, array('attributes' => $fields));
            if (!empty($err))
            {
               $errors[$kind.'.'.$type] = $err;
            }
         }
      }
      
      $count--;
      
      if ($count == 0) $checked = array();
      
      return $errors;
   }
   
   /**
    * Restore related entities
    * 
    * @param array& $related - relation map to this entities
    * @param array& $ids - ids this entities
    * @param array $options
    * @return array - errors
    */
   protected function restoreRelated(array& $related, array& $ids, array& $options = array())
   {
      $errors = array();
      static $checked = array();
      static $count = 0;
      
      $count++;
      
      foreach ($related as $kind => $params)
      {
         foreach ($params as $type => $fields)
         {
            if (!empty($checked[$kind.$type])) continue;
            
            $checked[$kind.$type] = true;
            
            $model = $this->container->getCModel($kind, $type, $options);
            $err   = $model->restore($ids, array('attributes' => $fields));
            if (!empty($err))
            {
               $errors[$kind.'.'.$type] = $err;
            }
         }
      }
      
      $count--;
      
      if ($count == 0) $checked = array();
      
      return $errors;
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
