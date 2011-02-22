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
    * Return link description for row (row = model->toArray())
    *  
    * @param array $row
    * @param array $options
    * @return array
    */
   abstract public function getLinkDataByRow(array $row, array $options = array());
   
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
   
   /**
    * Get marked for delition objects
    * 
    * @param array $options
    * @return array (link desc [retrieveLinkData]) or null
    */
   public function getMarkedForDeletion(array $options = array())
   {
      $options = array(
         'attributes' => array('%deleted'),
         'criterion'  => "`%deleted` = 1"
      );
      
      return $this->retrieveLinkData(null, $options);
   }
   
   /**
    * Get list of entities related with specified
    * 
    * @param mixed $ids - array or value
    * @param array $options
    * @return array or null
    */
   public function getRelatedEntities($ids, array $options = array())
   {
      $ret = array();
      $related =& $this->conf['relations'];
      
      if (!is_array($ids))
      {
         $ids = array($ids => $ids);
      }
      else
      {
         $ids = array_combine($ids, $ids);
      }
      
      static $checked = array();
      static $count   = 0;
      
      $count++;
      
      foreach ($related as $kind => $types)
      {
         foreach ($types as $type => $params)
         {
            if (!empty($checked[$kind.$type])) continue;

            $checked[$kind.$type] = true;

            // Constants
            if ($kind == 'Constants')
            {
               $ret[$kind][$type] = $params['attributes'];
               continue;
            }
            
            // Other entities
            $cmodel = $this->container->getCModel($kind, $type, $options);
            
            if (isset($params['attributes']))
            {
               $attributes =& $params['attributes'];

               if (null === ($res = $cmodel->getEntities($ids, array('attributes' => $attributes))) || $res['errors'])
               {
                  return null;
               }
               
               if (method_exists($cmodel, 'getLinkDataByRow'))
               {
                  $code = '$ret[$kind][$type][$row[\'_id\']] = $cmodel->getLinkDataByRow($row);';
               }
               else
               {
                  $code = '$ret[$kind][$type][$row[\'_id\']] = array(\'text\' => $kind.\' \'.$type, \'value\' => $row[\'_id\']);';
               }
               
               foreach ($res as $row)
               {
                  eval($code);
                  $ret[$kind][$type][$row['_id']]['rel'] = array();
                  
                  foreach ($attributes as $attr)
                  {
                     if (!isset($ids[$row[$attr]])) continue;
                     
                     $ret[$kind][$type][$row['_id']]['rel'][] = $row[$attr];
                  }
               }
            }
            
            // Tabular sections
            if (!isset($params['tabulars'])) continue;
            
            $owner = array();
            $rel   = array();
            
            foreach ($params['tabulars'] as $ttype => $attributes)
            {
               $opt = array(
                  'with_link_desc' => false,
                  'with_select' => false,
                  'attributes'  => $attributes
               );
               
               if (null === ($res = $cmodel->retrieveTabularSections($ids, $ttype, $opt)) || $res['errors'])
               {
                  return null;
               }
               
               foreach ($res[$ttype] as $row)
               {
                  if (!isset($rel[$row['Owner']]))
                  {
                     $rel[$row['Owner']]   = array();
                     $owner[$row['Owner']] = $row['Owner'];
                  }
                  
                  foreach ($attributes as $attr)
                  {
                     if (!isset($ids[$row[$attr]])) continue;
                     
                     $rel[$row['Owner']][] = $row[$attr];
                  }
               }
            }
            
            if (null === ($res = $cmodel->retrieveLinkData($owner)))
            {
               return null;
            }
            
            foreach ($res as $id => $desc)
            {
               if (isset($ret[$kind][$type][$id]))
               {
                  $ret[$kind][$type][$id]['rel'] = array_merge ($ret[$kind][$type][$id]['rel'], $rel[$id]);
                  $ret[$kind][$type][$id]['rel'] = array_unique($ret[$kind][$type][$id]['rel']);
               }
               else
               {
                  $ret[$kind][$type][$id] = $desc;
                  $ret[$kind][$type][$id]['rel'] = $rel[$id];
               }
            }
         }
      }
      
      $count--;
      
      if ($count == 0) $checked = array();
      
      return $ret;
   }
   
   /**
    * Delete marked for deletion
    * 
    * @param mixed  $ids
    * @param array& $options
    * @return array - errors
    */
   public function deleteMarkedForDeletion($ids, array $options = array())
   {
      if (empty($ids)) return array();
      
      if (!is_array($ids)) $ids = array($ids);
      
      $options = array(
         'attributes' => array('%pkey', '%deleted'),
         'criterion'  => "`%pkey` IN (".implode(',', $ids).") AND `%deleted`=1"
      );
      
      $errors = $this->delete(true, $options);
      
      if ($errors) return $errors;
      
      $rows  = count($ids);
      $arows = $this->container->getDBManager()->getAffectedRows();
      
      if ($rows > $arows) $errors[] = 'Don\'t delete all records'; 
      
      return $errors;
   }
}
