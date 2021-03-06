<?php

require_once('lib/model/base/BaseNotStorageEntityModel.php');

abstract class BaseEntityModel extends BaseNotStorageEntityModel
{
   /* This entity params */
   protected $id        = null;
   protected $isNew     = true;
   protected $isDeleted = false;


   public function __construct($kind, $type, array& $options = array())
   {
      parent::__construct($kind, $type, $options);
   }
   

   /**
    * Initialize entity object (retrieve configuration)
    * 
    * @param string $type - entity name
    * @return boolean
    */
   protected function initialize($kind, $type)
   {
      // Setup configuration
      if (!parent::initialize($kind, $type)) return false;
      
      $this->id        = null;
      $this->isNew     = true;
      $this->isDeleted = false;
      
      return true;
   }
   
   /**
    * Return entity id
    * 
    * @return int
    */
   public function getId()
   {
      return $this->isNew ? null : $this->id;
   }
   
   /**
    * Return true if this is a new object
    * 
    * @return boolean
    */
   public function isNew()
   {
      return $this->isNew;
   }
   
   
   /**
    * Load entity with id = $id
    * 
    * @param int $id - entity id
    * @param array& $options
    * @return boolean
    */
   public function load($id, array& $options = array())
   {
      if (!is_numeric($id) || $id <= 0) return false;
      
      $id    = (int) $id;
      $pkey  = $this->conf['db_map']['pkey'];
      $query = "SELECT * FROM `".$this->conf['db_map']['table']."` WHERE `".$pkey."`=".$id;
      $db    = $this->container->getDBManager($options);
      
      $values = $db->loadAssoc($query);
      
      if (empty($values))
      {
         return false;
      }
      
      return $this->loadAttributes($values, $options);
   }
   
   /**
    * Set entity attributes from array
    * 
    * @param array& $values
    * @param array& $options
    * @return boolean
    */
   protected function loadAttributes(array& $values, array& $options = array())
   {
      $pkey  =  $this->conf['db_map']['pkey'];
      $types =& $this->conf['types'];
      
      $this->id = (int) $values[$pkey];
      
      unset($values[$pkey]);
      
      $this->attributes = $values;
      
      foreach ($types as $name => $type)
      {
         switch ($type)
         {
            case 'bool':
            case 'int':
            case 'timestamp':
            case 'reference':
               $this->attributes[$name] = (int) $this->attributes[$name];
               break; 
            
            case 'float':
               $this->attributes[$name] = (float) $this->attributes[$name];
               break;

            case 'string':
            case 'password':
            case 'text':
            case 'date': 
            case 'datetime':
            case 'time':
            case 'year':
            case 'file':
            case 'enum':
               $this->attributes[$name] = empty($this->attributes[$name]) ? null : (string) $this->attributes[$name];
               break;
            /*
            case 'enum':
               $val = array_search($this->attributes[$name], $this->conf['precision'][$name]['in']);
               $this->attributes[$name] = ($val === false ? null : (int) $val);
               break;
            */
            default:
               throw new Exception(__METHOD__.': Not supported internal type "'.$type.'"');
         }
      }
      
      $this->isNew = false;
      $this->isDeleted  = false;
      $this->isModified = false;
      $this->modified   = array();
      
      return true;
   }
   
   
   
   
   /**
    * (non-PHPdoc)
    * @see BaseNotStorageEntityModel#setAttribute($name, $value)
    */
   public function setAttribute($name, $value)
   {
      if ($this->hasAttribute($name) && $this->conf['types'][$name] == 'file')
      {
         $old = $this->getAttribute($name);
         
         if ($old && $this->removeFiles($name, $old))
         {
            return false;
         }
      }
      
      return parent::setAttribute($name, $value);
   }
   
   /**
    * Set entity params from array
    * 
    * @param array $values
    * @param array& $options
    * @return array - errors
    */
   public function fromArray(array $values, array $options = array())
   {
      if ($errors = $this->prepareToImport($values, $options))
      {
         return $errors;
      }
      
      return parent::fromArray($values);
   }
   
   /**
    * Prepare object to import from array
    * 
    * @param array& $values
    * @param array& $options
    * @return array - errors
    */
   protected function prepareToImport(array& $values, array& $options = array())
   {
      $errors = array();
      $pkey   = $this->conf['db_map']['pkey'];
      
      if (!empty($values[$pkey])) // Load
      {
         if (!$this->load($values[$pkey]))
         {
            $errors[$pkey] = 'Invalid entity id';
         }
      }
      else // New
      {
         $this->id         = null;
         $this->isNew      = true;
         $this->isDeleted  = false;
         $this->attributes = null;
         $this->isModified = false;
         $this->modified   = array();
      }
      
      unset($values[$pkey]);
      
      return $errors;
   }
   
   /**
    * Get entity params as array
    * [
    *    options = array(
    *       with_link_desc => [ true | false ]
    *    )
    * ]
    * @param array& $options
    * @return array or null
    */
   public function toArray(array $options = array())
   {
      //if ($this->isNew) return array();
      
      $result = parent::toArray($options);
      
      $result[$this->conf['db_map']['pkey']] = $this->id;
      
      return $result;
   }
   
   
   
   
   /**
    * Save entity
    * 
    * @param array& $options
    * @return array - errors
    */
   public function save(array& $options = array())
   {
      if ($this->isDeleted) return array('"'.ucfirst($this->type).'" have been deleted');
      
      if (!$this->isModified && !$this->isNew) return array();
      
      $db_map =& $this->conf['db_map'];
      
      // Validation
      $errors = $this->validateAttributes(array_keys($this->modified), $options);
      
      if (!empty($errors)) return $errors;
      
      // Save entity
      $fields = array_intersect_key($this->attributes, $this->modified);
      $db     = $this->container->getDBManager($options);
      $func   = $this->isNew ? 'generateInsertQuery' : 'generateUpdateQuery';
      $query  = $this->$func($fields, $options);

      if (!$db->executeQuery($query))
      {
         return array($db->getError());
      }

      if ($this->isNew)
      {
         $this->id = $db->getInsertId();
         $this->isNew = false;
      }

      $this->modified   = array();
      $this->isModified = false;
      
      return array();
   }
   
   /**
    * Generate INSERT SQL query
    * 
    * @param array& $attributes
    * @param array& $options
    * @return string
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
      $this->addSystemToInsert($fields, $values);
      
      // Insert
      $query  = "INSERT INTO `".$this->conf['db_map']['table']."`(".$fields.") ";
      $query .= "VALUES(".$values.")";
      $query  = "INSERT INTO `".$this->conf['db_map']['table']."`(".$fields.") ";
      $query .= "VALUES(".$values.")";
      
      return $query;
   }
   
   /**
    * Generate UPDATE SQL query
    * 
    * @param array& $attributes
    * @param array& $options
    * @return string
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
      $this->addSystemToUpdate($fields);
      
      // Update
      $db_map =& $this->conf['db_map'];
      $query  =  "UPDATE `".$db_map['table']."` SET ".implode(", ", $fields)." WHERE `".$db_map['pkey']."`=".$this->id;
      
      return $query;
   }
   
   /**
    * Add system attributes to insert query
    * 
    * @param string $fields
    * @param string $values
    * @return void
    */
   protected function addSystemToInsert(& $fields, & $values)
   {
      ;
   }
   
   /**
    * Add system attributes to insert update
    * 
    * @param array $fields
    * @return void
    */
   protected function addSystemToUpdate(& $fields)
   {
      ;
   }
   
   /**
    * Get value as string to mysql query
    * 
    * @param string $name - attribute name
    * @param mixed $value - attribute value
    * @return string
    */
   protected function getValueForSQL($name, $value)
   {
      switch ($this->conf['types'][$name])
      {
         case 'bool':
         case 'int':
         case 'float':
         case 'reference':
            return is_null($value) ? 0 : $value;
            break;
            
         case 'enum':
            return is_string($value) ? "'".$value."'" : (int) $value;
            break;
            
         default:
            return "'".$value."'";
      }
   }
   
   
   /**
    * Delete entity
    * 
    * @param array& $options
    * @return array - errors
    */
   public function delete(array& $options = array())
   {
      if ($this->isNew) return array();
      
      $errors = array();
      
      // Remove files
      if (!empty($this->conf['files']))
      {
         foreach ($this->conf['files'] as $attr => $prec)
         {
            if ($this->attributes[$attr] && ($err = $this->removeFiles($attr, $this->attributes[$attr])))
            {
               $errors = array_merge($errors, $err);
            }
         }
         
         if ($errors) return $errors;
      }
      
      // Remove this
      $errors = $this->removeThis($options);
      
      $this->isDeleted = true;
      
      return $errors;
   }
   
   /**
    * Remove this entity
    * 
    * @param array& $options
    * @return array - errors
    */
   protected function removeThis(array& $options = array())
   {
      $db = $this->container->getDBManager($options);
      $dbmap =& $this->conf['db_map'];
      $query =  "DELETE FROM `".$dbmap['table']."` WHERE `".$dbmap['pkey']."`=".$this->id;
       
      if (!$db->executeQuery($query))
      {
         return array($db->getError());
      }
      
      return array();
   }
}