<?php

require_once('lib/model/base/BaseEntityModel.php');

class InfRegistryModel extends BaseEntityModel
{
   const kind = 'information_registry';
   
   public function __construct($type, array& $options = array())
   {
      parent::__construct(self::kind, $type, $options);
   }
   
   /**
    * (non-PHPdoc)
    * @see lib/model/base/BaseModel#setup($kind, $type)
    */
   protected function initialize($kind, $type)
   {
      if (!parent::initialize($kind, $type)) return false;
      
      $confname = self::getConfigurationName($kind, $type);

      if (!isset(self::$config[$confname]['dimensions']))
      {
         self::$config[$confname]['dimensions'] = $this->container->getConfigManager()->getInternalConfigurationByKind($kind.'.dimensions', $type);
      }
      
      if (!isset(self::$config[$confname]['periodical']))
      {
         self::$config[$confname]['periodical'] = $this->container->getConfigManager()->getInternalConfigurationByKind($kind.'.periodical', $type);
      }
      
      if (!isset(self::$config[$confname]['recorders']))
      {
         self::$config[$confname]['recorders'] = $this->container->getConfigManager()->getInternalConfigurationByKind($kind.'.recorders', $type);
      }
      
      return true;
   }
   
   /**
    * (non-PHPdoc)
    * @see lib/model/base/BaseEntityModel#validateAttributes($names)
    */
   protected function validateAttributes($names, array& $options = array())
   {
      $errors = parent::validateAttributes($names, $options);
      
      // Check recorder
      if ($this->hasRecorders())
      {
         $rec_err = $this->checkRecorder();
      }
      
      // Check dimensions
      if (empty($rec_err))
      {
         if (!empty($this->conf['dimensions']) && ($this->isNew || array_intersect($this->conf['dimensions'], array_keys($this->modified))))
         {
            $not_valid_dim = array_intersect($this->conf['dimensions'], array_keys($errors));

            if (empty($not_valid_dim))
            {
               if (!$this->checkDimensions($options)) $errors[] = ('Record with this dimensions already exists');
            }
         }
      }
      else $errors = array_merge($errors, $rec_err);
      
      return $errors;
   }
   
   /**
    * Check dimensions
    * 
    * @return boolean
    */
   protected function checkDimensions(array& $options = array())
   {
      $db_map     =& $this->conf['db_map'];
      $dimensions =& $this->conf['dimensions'];
      $periodical =& $this->conf['periodical'];
      
      if (!$this->isNew) $where[] = '`'.$db_map['pkey'].'`<>'.$this->id;
      
      if ($this->hasRecorders())
      {
         list($rtype, $rid) = $this->getRecorder();
         $where[] = '`'.$db_map['recorder_type']."`='".$rtype."'";
         $where[] = '`'.$db_map['recorder_id'].'`='.$rid;
      }
      
      foreach ($dimensions as $attribute)
      {
         $where[] = '`'.$attribute.'`='.$this->getValueForSQL($attribute, $this->attributes[$attribute]);
      }
      
      if (!empty($periodical))
      {
         $field   = $periodical['field'];
         $matches = array();
         if (!preg_match('/(\d{4})-(\d{2})-(\d{2})\s(\d{2}):(\d{2}):(\d{2})/', $this->attributes[$field], $matches))
         {
            throw new Exception(__METHOD__.': Incorrect date format');
         }
         list($date, $year, $month, $day, $hour, $minute, $second) = $matches;
         
         switch($periodical['period'])
         {
            case 'second':
               $where[] = '`'.$field."` = '".$date."'";
               break;
            case 'day':
               $where[] = '`'.$field."` >= '".$year.'-'.$month.'-'.$day." 00:00:00'";
               $where[] = '`'.$field."` < '".date("Y-m-d H:i:s", mktime (0, 0, 0, $month, $day+1, $year))."'";
               break;
            case 'month':
               $where[] = '`'.$field."` >= '".$year.'-'.$month."-01 00:00:00'";
               $where[] = '`'.$field."` < '".date("Y-m-d H:i:s", mktime (0, 0, 0, $month+1, 1, $year))."'";
               break;
            case 'quarter':
               $quarter = ceil($month/3)-1;
               $where[] = '`'.$field."` >= '".date("Y-m-d H:i:s", mktime (0, 0, 0, ($quarter*3+1), 1, $year));
               $where[] = '`'.$field."` < '".date("Y-m-d H:i:s", mktime (0, 0, 0, ($quarter*3+4), 1, $year));
               break;
            case 'year':
               $where[] = '`'.$field."` >= '".$year."-01-01 00:00:00'";
               $where[] = '`'.$field."` < '".($year+1)."-01-01 00:00:00'";
               break;
            default:
               throw new Exception(__METHOD__.': not supported period');
         }
      }
      
      $query  = "SELECT count(*) AS `cnt` FROM `".$db_map['table']."` ";
      $query .= "WHERE ".implode(' AND ', $where);
      $db     = $this->container->getDBManager($options);
      $res    = $db->loadAssoc($query);
      
      return (!$res || $res['cnt']) ? false : true;
   }
   
   /**
    * This entity has recorders?
    * 
    * @return boolean
    */
   public function hasRecorders()
   {
      return !empty($this->conf['recorders']);
   }
   
   /**
    * Get recorder type and id
    * [
    *    return array(0 => <type>, 1 => <id>)
    * ]
    * @return array or null
    */
   public function getRecorder()
   {
      if (!$this->hasRecorders()) throw new Exception(__METHOD__.': Information registry "'.$this->type.'" has no recorders');
      
      $dbmap =& $this->conf['db_map'];
      
      if (!isset($this->attributes[$dbmap['recorder_type']]) || !isset($this->attributes[$dbmap['recorder_id']]))
      {
         return null;
      }
      
      return array($this->attributes[$dbmap['recorder_type']], $this->attributes[$dbmap['recorder_id']]);
   }
   
   /**
    * Set recorder to current entity
    * 
    * @param string $type
    * @param int $id
    * @return unknown_type
    */
   public function setRecorder($type, $id)
   {
      if (!$this->hasRecorders()) throw new Exception(__METHOD__.': Information registry "'.$this->type.'" has no recorders');
      
      $dbmap =& $this->conf['db_map'];
      
      if (!is_string($type)) return false;
      if (!(is_numeric($id) && (int) $id > 0)) return false;
      
      $this->attributes[$dbmap['recorder_type']] = $type;
      $this->attributes[$dbmap['recorder_id']] = (int) $id;
      
      $this->isModified = true;
      $this->modified['recorder_type'] = true;
      $this->modified['recorder_id']   = true;
      
      return true;
   }
   
   /**
    * Check current recorder
    * 
    * @return array - errors
    */
   public function checkRecorder()
   {
      if (!$this->hasRecorders()) throw new Exception(__METHOD__.': Information registry "'.$this->type.'" has no recorders');
      
      $dbmap =& $this->conf['db_map'];
      
      if (!isset($this->attributes[$dbmap['recorder_type']]) || !isset($this->attributes[$dbmap['recorder_id']]))
      {
         return array('Recorder not set');
      }
      
      $type = $this->attributes[$dbmap['recorder_type']];
      $id   = $this->attributes[$dbmap['recorder_id']];
      
      if (!in_array($type, $this->conf['recorders']))
      {
         return array('Invalid recorder "'.$type.'"');
      }
      
      if (!self::hasEntity('documents', $type, $id))
      {
         return array('Document "'.$type.'" with id "'.$id.'" is not exists');
      }
      
      return array();
   }
   

   /**
    * (non-PHPdoc)
    * @see lib/model/base/BaseEntityModel#generateInsertQuery($attributes, $options)
    */
   protected function generateInsertQuery(array& $attributes, array& $options = array())
   {
      // Attributes
      if (list($field, $value) = each($attributes))
      {
         $fields = "`".$field."`";
         $values = $this->getValueForSQL($field, $value);
      
         while (list($field, $value) = each($attributes))
         {
            $fields .= ", `".$field."`";
            $values .= ", ".$this->getValueForSQL($field, $value);
         }
      }
      
      // System attributes
      if ($this->hasRecorders() && ($this->modified['recorder_type'] || $this->modified['recorder_id']))
      {
         if ($fields)
         {
            $fields .= ', ';
            $values .= ', ';
         }
         else
         {
            $fields = '';
            $values = '';
         }

         $fields .= "`".$this->conf['db_map']['recorder_type']."`";
         $fields .= ", `".$this->conf['db_map']['recorder_id']."`";
         $values .= "'".$this->attributes[$this->conf['db_map']['recorder_type']]."'";
         $values .= ", ".$this->attributes[$this->conf['db_map']['recorder_id']];
      }
      
      $query  = "INSERT INTO `".$this->conf['db_map']['table']."`(".$fields.") ";
      $query .= "VALUES(".$values.")";
      
      return $query;
   }
   
   /**
    * (non-PHPdoc)
    * @see lib/model/base/BaseEntityModel#generateUpdateQuery($attributes, $options)
    */
   protected function generateUpdateQuery(array& $attributes, array& $options = array())
   {
      $fields = array();
      
      // Attributes
      foreach ($attributes as $field => $value)
      {
         $fields[] = "`".$field."`=".$this->getValueForSQL($field, $value);
      }
      
      // System attributes
      if ($this->hasRecorders() && ($this->modified['recorder_type'] || $this->modified['recorder_id']))
      {
         $fields[] = "`".$this->conf['db_map']['recorder_type']."`='".$this->attributes[$this->conf['db_map']['recorder_type']]."'";
         $fields[] = "`".$this->conf['db_map']['recorder_id']."`=".$this->attributes[$this->conf['db_map']['recorder_id']];
      }
      
      $db_map =& $this->conf['db_map'];
      $query  =  "UPDATE `".$db_map['table']."` SET ".implode(", ", $fields)." WHERE `".$db_map['pkey']."`=".$this->id;
      
      return $query;
   }

   
   
   
   /************************** For control access rights **************************************/
   
   
   
   /**
    * (non-PHPdoc)
    * @see BaseEntityModel#delete($options)
    */
   public function delete(array& $options = array())
   {
      // Check permissions
      if (defined('IS_SECURE') && !$this->container->getUser()->hasPermission($this->kind.'.'.$this->type.'.Update'))
      {
         return array('Access denied');
      }
      
      // Execute method
      return parent::delete($options);
   }
   
   /**
    * (non-PHPdoc)
    * @see BaseEntityModel#load($id, $options)
    */
   public function load($id, array& $options = array())
   {
      // Check permissions
      if (defined('IS_SECURE') && !$this->container->getUser()->hasPermission($this->kind.'.'.$this->type.'.Read'))
      {
         return false;
      }
      
      // Execute method
      return parent::load($id, $options);
   }
   
   /**
    * (non-PHPdoc)
    * @see BaseEntityModel#toArray($options)
    */
   public function toArray(array $options = array())
   {
      // Check permissions
      if (defined('IS_SECURE') && !$this->isNew && !$this->container->getUser()->hasPermission($this->kind.'.'.$this->type.'.Read'))
      {
         return array();
      }
      
      // Execute method
      return parent::toArray($options);
   }
   
   /**
    * (non-PHPdoc)
    * @see BaseEntityModel#save($options)
    */
   public function save(array& $options = array())
   {
      // Check permissions
      if (defined('IS_SECURE') && !$this->container->getUser()->hasPermission($this->kind.'.'.$this->type.'.Update'))
      { 
         return array('Access denied');
      }
      
      // Execute method
      return parent::save($options);
   }
}
