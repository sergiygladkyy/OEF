<?php

require_once('lib/model/base/BaseModel.php');

abstract class BaseNotStorageEntityModel extends BaseModel
{
   /* This entity params */
   protected $attributes = null;
   protected $isModified = false;
   protected $modified   = array();
   protected $container  = null;

   public function __construct($kind, $type, array& $options = array())
   {
      $this->container = Container::getInstance();
      $this->initialize($kind, $type);
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
      
      $this->attributes = null;
      $this->isModified = false;
      $this->modified   = array();
      
      return true;
   }
   
   
   
   /**
    *  
    * @param string $name - attribute name
    * @return boolean
    */
   public function hasAttribute($name)
   {
      return in_array($name, $this->conf['attributes']);
   }

   /**
    * Set attribute value
    * 
    * @throws Exception
    * @param string $name - attribute name
    * @param mixed $value
    * @return boolean
    */
   public function setAttribute($name, $value)
   {
      if (!$this->hasAttribute($name))
      {
         if ($name != 'extra')
         {
            throw new Exception(__METHOD__.': Attribute "'.$name.'" not exists.');
         }
         
         // Set extra attribute
         $this->attributes[$name] = $value;
         
         return true;
      }
      
      if (!$this->checkAttributeValueType($name, $value)) return false;
      if (isset($this->attributes[$name]) && $this->attributes[$name] == $value) return true;
      
      $this->isModified = true;
      $this->modified[$name]   = true;
      $this->attributes[$name] = $value;
      
      return true;
   }
   
   /**
    * Get attribute value
    * 
    * @throws Exception
    * @param string $name - attribute name
    * @return mixed
    */
   public function getAttribute($name)
   {
      if (!$this->hasAttribute($name))
      {
         if ($name != 'extra')
         {
            throw new Exception(__METHOD__.': Attribute "'.$name.'" not exists.');
         }
         
         // Extra attribute
         return isset($this->attributes[$name]) ? $this->attributes[$name] : null;
      }

      // Not set
      if (!isset($this->attributes[$name])) return null;
      
      // Link (return object)
      if ($this->conf['types'][$name] == 'reference')
      {
         if ($this->attributes[$name] <= 0) return null; // Empty link
         
         $ref   =& $this->conf['references'][$name];
         $model =  $this->container->getModel($ref['kind'], $ref['type']);
         
         return ($model->load($this->attributes[$name]) ? $model : null);
      }
      
      // Simple attribute
      return $this->attributes[$name];
   }
   
   /**
    * Check value type
    * 
    * @param string $name - attribute name
    * @param mixed& $value
    * @return boolean
    */
   protected function checkAttributeValueType($name, & $value)
   {
      $type = strtolower($this->conf['types'][$name]);
      
      switch($type)
      {
         case 'bool':
            $value = $value ? 1 : 0;
            break; 
         
         case 'int':
         case 'timestamp':
            $val = (int) $value;
            
            if ($value != (string) $val) return false;
            
            $value = $val;
            break;
         
         case 'float':
            $val = (float) $value;
            
            if ($value != (string) $val) return false;
            
            $value = $val;
            break;

         // Types having string value
         case 'string':
         case 'password':
         case 'text':
         case 'date': 
         case 'datetime':
         case 'time':
         case 'year':
         case 'file':
            $value = (string) $value;
            break;

         // Enumeration (int number >= 0 or string)
         case 'enum':
            $val = (int) $value;
            
            if ($value != (string) $val)
            {
               $value = (string) $value;
            }
            elseif ($val >= 0)
            {
               $value = $val;
            }
            else return false;
            
            break;
         
         // Link (object or int >= 0)
         case 'reference':
            
            if (!is_object($value))
            {
               $val = (int) $value;
            
               if ($value != (string) $val || $val < 0) return false;
               
               $value = $val;
            }
            
            break;
            
         default:
            throw new Exception(__METHOD__.': Not supported internal type "'.$type.'"');
      }
      
      return true;
   }
   
   /**
    * Check value precision for attribute $name
    * 
    * @param mixed $names - array or string
    * @param array& $options
    * @return array - errors
    */
   protected function checkAttributesPrecision($names, array& $options = array())
   {
      if (!is_array($names)) $names = array($names);
      
      $result    = array();
      $validator = $this->container->getValidator($options);
      
      foreach ($names as $attr)
      {
         // Validation
         if (empty($this->conf['precision'][$attr])) continue;
         
         foreach ($this->conf['precision'][$attr] as $prec => $params)
         {
            $errors = $validator->$prec($this->conf['types'][$attr], $this->attributes[$attr], $params);
            
            if (!empty($errors)) $result[$attr] = $errors;
         }
      }
      
      return $result;
   }
   
   /**
    * Check references
    * 
    * @param mixed $names - array or string
    * @return array - errors
    */
   protected function checkReferences($names)
   {
      if (!is_array($names)) $names = array($names);
      
      $errors = array();
      
      foreach ($names as $attr)
      {
         // Validation
         if (empty($this->conf['references'][$attr]))
         {
            throw new Exception(__METHOD__.': attribute "'.$attr.'" ('.$this->type.') is not reference');
         }
         
         $ref =& $this->conf['references'][$attr];
         $val =& $this->attributes[$attr];
         
         // Can related ?
         if (is_object($val))
         {
            if (!is_a($val, 'BaseEntityModel'))
            {
               throw new Exception(__METHOD__.': not supported model class "'.get_class($val).'"');
            }

            if ($ref['kind'] != $val->getKind()) $err[] = '"'.$this->type.'" can\'t related with '.$val->getKind();
            if ($ref['type'] != $val->getType()) $err[] = '"'.$this->type.'" can\'t related with "'.$val->getType().'"';

            if (!empty($err))
            {
               $errors[$attr] = $err;
               continue;
            }
            
            $val = $val->getId(); 
         }
         
         // Has entity ?
         if ($val != 0 && !self::hasEntity($ref['kind'], $ref['type'], $val))
         {
            $errors[$attr] = 'Not exists';
         }
      }

      return $errors;
   }
   
   /**
    * Check required attributes
    * 
    * @return array - errors
    */
   protected function checkRequired()
   {
      $errors = array();
      
      foreach ($this->conf['required'] as $attribute)
      {
         if (!isset($this->attributes[$attribute]) || ($this->conf['types'][$attribute] != 'bool' && empty($this->attributes[$attribute])))
         {
            $errors[$attribute] = "Required";
         }
      }
      
      return $errors;
   }
   
   /**
    * Validate attribbutes
    * 
    * @param mixed $names - array or string
    * @param array& $options
    * @return array - errors
    */
   protected function validateAttributes($names, array& $options = array())
   {
      $errors = array();
      $not_valid = array();
      
      /* Standard validation */
      
      // Check required
      $errors = $this->checkRequired();
      
      if (!empty($errors)) $not_valid = array_keys($errors);
      
      // Validation all fields
      $err = $this->checkAttributesPrecision(array_diff($names, $not_valid), $options);
      
      if (!empty($err))
      {
         $errors = array_merge($errors, $err);
         $not_valid = array_keys($errors);
      }
      
      // Validation references
      $references = array_intersect(array_diff($names, $not_valid), array_keys($this->conf['references']));
      $err = $this->checkReferences($references);
      
      if (!empty($err)) $errors = array_merge($errors, $err);
      
      if (!empty($errors)) return $errors;
      
      
      /* onAfterValidation */
      
      $event = $this->container->getEvent($this, $this->kind.'.'.$this->type.'.model.onBeforeAddingRecord');
      $event->setReturnValue(array());
      
      try {
         $this->container->getEventDispatcher()->notify($event);
         
         $errors = $event->getReturnValue();
      }
      catch (Exception $e)
      {
         $errors = $e->getMessage();
      }
      
      if (!empty($errors)) $errors = is_array($errors) ? $errors : array($errors);
      
      return $errors;
   }
   
   
   
   /**
    * Set entity params from array
    * 
    * @param array $values
    * @param array $options
    * @return array - errors
    */
   public function fromArray(array $values, array $options = array())
   {
      $errors = array();
      
      // set attributes
      foreach ($this->conf['attributes'] as $name)
      {
         if (isset($values[$name]))
         {
             if (!$this->setAttribute($name, $values[$name]))
             {
                $errors[$name] = 'Invalid value type';
             }
             
             unset($values[$name]);
         }
      }
      
      if (!empty($values)) $this->setAttribute('extra', $values);
      
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
      $result = $this->attributes;
      
      if (!empty($options['with_link_desc']))
      {
         foreach ($this->conf['references'] as $field => $param)
         {
            if (!empty($result[$field]))
            {
               $cmodel = $this->container->getCModel($param['kind'], $param['type'], $options);
               $data   = $cmodel->retrieveLinkData($result[$field]);
               $result[$field] = $data[$result[$field]];
            }
            else $result[$field] = array();
         }
      }
      
      return $result;
   }
}