<?php

require_once('lib/model/base/BaseModel.php');

abstract class BaseEntitiesModel extends BaseModel
{
   protected $container = null;
   
   protected function __construct($kind, $type, array& $options = array())
   {
      $this->container = Container::getInstance();
      $this->initialize($kind, $type);
   }
   
   /**
    * Return true if entities exists
    * [
    *    options = array(
    *       with_link_desc => [ true | false ]
    *    )
    * ]
    * @param mixed $values - array or value
    * @param array $options
    * @return array or null
    */
   public function getEntities($values = null, array $options = array())
   {
      // Retrieve entities
      $db_map =& $this->conf['db_map'];
      $params =  $this->retrieveCriteriaQuery($db_map, $values, $options);
      
      if (!empty($params['errors'])) return array('errors' => $params['errors']);
      
      $db    = $this->container->getDBManager($options);
      $query = "SELECT * FROM `".$db_map['table']."` ".$params['criteria'];
      $res   = $db->loadAssocList($query, $options);
      
      if (empty($options['with_link_desc']) || is_null($res)) return $res;
      
      // Retrieve links descriptions
      $result['list'] = $res;
      
      unset($res);
      
      $result['links'] = $this->retrieveLinksDescriptions($result['list'], $options);
      
      return $result;
   }

   /**
    * Return true if entities exists
    * 
    * @param mixed $values - array or value
    * @param array $options
    * @return boolean or null
    */
   public function hasEntities($values, array $options = array())
   {
      if (empty($values)) return false;
      
      $db_map =& $this->conf['db_map'];
      $params =  $this->retrieveCriteriaQuery($db_map, $values, $options);
      
      if (!empty($params['errors'])) return $params['errors'];
      
      $db    =  $this->container->getDBManager($options);
      $query = "SELECT count(*) AS cnt FROM `".$db_map['table']."` ".$params['criteria'];
      $res   = $db->loadAssoc($query);
      
      return (is_null($res) ? null : ($res['cnt'] ? true : false));
   }
   
   /**
    * Count entities
    * 
    * @param mixed $values - array or value
    * @param array $options
    * @return int or null
    */
   public function countEntities($values = null, array $options = array())
   {
      $db_map =& $this->conf['db_map'];
      $params =  $this->retrieveCriteriaQuery($db_map, $values, $options);
      
      if (!empty($params['errors'])) return array('errors' => $params['errors']);
      
      $db    = $this->container->getDBManager($options);
      $query = "SELECT count(*) AS cnt FROM `".$db_map['table']."` ".$params['criteria'];
      
      if (!$res = $db->loadAssoc($query)) return null;
      
      return $res['cnt'] ? $res['cnt'] : 0;
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
      
      $db_map =& $this->conf['db_map'];
      $params =  $this->retrieveCriteriaQuery($db_map, $values, $options);
      
      if (!empty($params['errors'])) return $params['errors'];
      
      $db = $this->container->getDBManager($options);
      
      // Remove files
      if (!empty($this->conf['files']))
      {
         $errors  = array();
         $f_attrs = array_keys($this->conf['files']);
         
         $query = "SELECT `".$db_map['pkey']."`, `".implode("`, `", $f_attrs)."` FROM`".$db_map['table']."` ".$params['criteria'];
         
         if (null === ($res = $db->executeQuery($query)))
         {
            return array($db->getError());
         }
         
         while ($row = $db->fetchAssoc($res))
         {
            $can_deleted = true;
            
            foreach ($f_attrs as $attr)
            {
               if (!empty($row[$attr]) && ($err = $this->removeFiles($attr, $row[$attr])))
               {
                  $errors = array_merge($errors, $err);
                  $can_deleted = false;
               }
            }
            
            if ($can_deleted) $ids[] = $row[$db_map['pkey']];
         }
         
         if (!empty($ids))
         {
            $query = 'DELETE FROM `'.$db_map['table'].'` WHERE `'.$db_map['pkey'].'` IN ('.implode(',', $ids).')';
            
            if (!$db->executeQuery($query))
            {
               $errors[] = $db->getError();
            }
         }
         
         return $errors;
      }
      
      $query = 'DELETE FROM `'.$db_map['table'].'` '.$params['criteria'];
      
      if (!$db->executeQuery($query))
      {
         return array($db->getError());
      }
      
      return array();
   }
   
   
   
   /**
    * Retrieve values for select box (references)
    * 
    * @param mixed $fields
    * @param array $options
    * @return array
    */
   public function retrieveSelectDataForRelated($fields = array(), array $options = array())
   {
      $result = array();
      
      if (!empty($fields))
      {
         if (!is_array($fields)) $fields = array($fields => true);
         
         $ref = array_intersect_key($this->conf['references'], $fields);
      }
      else $ref =& $this->conf['references'];
      
      foreach ($ref as $field => $params)
      {
         $model = $this->container->getCModel($params['kind'], $params['type'], $options);
         $result[$field] = $model->retrieveSelectData($options);
      }
      
      return $result;
   }
   
   /**
    * Get list of links descriptions
    * 
    * @param array& $list
    * @param array& $options
    * @return array
    */
   protected function retrieveLinksDescriptions(array& $list, array& $options = array())
   {
      $ids = array();
      
      // retrieve related ids
      foreach ($list as $entity)
      {
         foreach ($this->conf['references'] as $field => $param)
         {
            if ($entity[$field] > 0) $ids[$field][] = $entity[$field];
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
      
      return $result;
   }
   
   
   
   
   
   /* ---------------------------- Criteria for queries ------------------------------------ */
   
   
   /**
    * Return criteria query
    * 
    * @param array& $db_map
    * @param mixed $values
    * @param array& $options
    * @return array
    */
   protected function retrieveCriteriaQuery(array& $db_map, $values, array& $options)
   {
      $fields = array();
      
      if (!empty($values) || (!empty($options['attributes']) && !empty($options['criterion'])))
      {
         // Retrieve WHERE
         if (!empty($options['attributes']))
         {
            // Check values
            if (!is_array($values)) $values = array($values);
             
            if (!is_array($options['attributes']))
            {
               $fields[] = $options['attributes'];
            }
            else $fields = $options['attributes'];

            if (empty($options['criterion']))
            {
               $criteria = $this->generateWhere($db_map['table'], $fields, $values);
            }
            else
            {
               $criteria = $this->generateWhereByCriteria($db_map, $fields, $values, $options['criterion']);
            }  
         }
         elseif (!empty($options['criterion']))
         {
            $criteria = (string) $options['criterion'];
         }
         else
         {
            $criteria = 'WHERE `'.$db_map['pkey'].'`'.(is_array($values) ? ' IN ('.implode(',', $values).')' : '='.(int) $values);
         }
      }
      elseif (!empty($options['criterion']))
      {
         $criteria = (string) $options['criterion'];
      }
      else $criteria = '';
      
      
      if (is_null($criteria)) $errors[] = 'Invalid option attributes or values';
      
      return empty($errors) ? array('fields' => $fields, 'values' => $values, 'criteria' => $criteria) : array('errors' => $errors);
   }
   
   /**
    * Generate WHERE
    * 
    * $values = array(..)
    *
    * $options = array(
    *   attributes => array(attribute_names) // <- list fields (only one table)
    * )
    * 
    * Embeded by OR. If fields have different types (numeric, string) - return null
    * 
    * @param string $table - table name
    * @param array& $fields
    * @param array& $values
    * @return string or null
    */
   protected function generateWhere($table, array& $fields, array& $values)
   {
      $numeric_types = array('int', 'float', 'timestamp', 'bool', 'reference');
      $is_numerics   = null;
      
      foreach ($fields as $field)
      {
         if (!in_array($field, $this->conf['attributes']))
         {
            return null;
         }
         
         if (in_array($this->conf['types'][$field], $numeric_types))
         {
            if ($is_numerics === null)
            {
               $is_numerics = true;
               
               $in = implode(',', $values);
            }
            elseif (!$is_numerics) return null;
         }
         elseif ($is_numerics === null)
         {
            $is_numerics = false;
            
            $in = "'".implode("','", $values)."'";
         }
         elseif ($is_numerics)
         {
            return null;
         }
         
         $where[] = '`'.$table.'`.`'.$field.'` IN ('.$in.')';
      }
      
      return 'WHERE '.implode(' OR ', $where);
   }
   
   /**
    * Generate WHERE
    * 
    * $values = array(
    *   %pkey => array(..) or value, // <- this system field
    *   owner => array(..) or value
    * )
    *
    * $options = array(
    *   attributes => array(%pkey, owner) or string // <- list fields (only one table)
    *   criterion  => 'owner IN (%%owners%%) AND %pkey NOT IN (%%ids%%)' // <- template for WHERE
    * )
    * 
    * @param $table
    * @param $fields
    * @param $values
    * @return unknown_type
    */
   protected function generateWhereByCriteria($dbmap, array $fields, array $values, $template)
   {
      $numeric_types = array('int', 'float', 'timestamp', 'bool', 'reference');
      
      foreach ($fields as $field)
      {
         if ($field{0} == '%')
         {
            $oldname  = $field;
            $field    = substr($field, 1);
            
            if (!isset($dbmap[$field])) return null;
            
            $field    = $dbmap[$field];
            $template = str_replace($oldname, $field, $template);
            
            if (isset($values[$oldname]))
            {
               if (is_array($values[$oldname]))
               {
                  $values[$oldname] = implode(',', $values[$oldname]);
               }
               
               $template = str_replace('%'.$field.'%%', $values[$oldname], $template);
            }
            
            continue;
         }
         elseif (!in_array($field, $this->conf['attributes']))
         {
            return null;
         }
         
         if (!isset($values[$field])) return null;
         
         if (in_array($this->conf['types'][$field], $numeric_types))
         {
            if (is_array($values[$field]))
            {
               $values[$field] = implode(',', $values[$field]);
            }
         }
         else
         {
            if (is_array($values[$field]))
            {
               $values[$field] = "'".implode(',', $values[$field])."'";
            }
            else $values[$field] = "'".$values[$field]."'";
         }
         
         $template = str_replace('%%'.$field.'%%', $values[$field], $template);
      }
      
      return 'WHERE '.$template;
   }
}
