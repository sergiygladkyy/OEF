<?php

require_once('lib/model/base/BaseEntityModel.php');

class ConstantModel extends BaseEntityModel
{
   const kind = 'Constants';
   
   public function __construct(array& $options = array())
   {
      parent::__construct(self::kind, null, $options);
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
      // Check permissions
      if (defined('IS_SECURE') && !$this->container->getUser()->isAdmin())
      {
         return array();
      }
      
      // Execute method
      $result = array();
      
      if (!empty($fields))
      {
         if (is_array($fields)) $fields = array($fields);
         
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
    * (non-PHPdoc)
    * @see BaseEntityModel#save($options)
    */
   public function save(array& $options = array())
   {
      // Check permissions
      if (defined('IS_SECURE') && !$this->container->getUser()->isAdmin())
      { 
         return array('Access denied');
      }
      
      // Execute method
      try {
         return parent::save($options);
      }
      catch (Exception $e)
      {
         $errors = unserialize($e->getMessage());                
         
         return is_array($errors) ? $errors : array($errors);
      }
   }
   
   /**
    * (non-PHPdoc)
    * @see ext/OEF/Framework/lib/model/base/BaseEntityModel#generateInsertQuery($attributes, $options)
    */
   protected function generateInsertQuery(array& $attributes, array& $options = array())
   {
      if (null === ($res = self::hasEntity($this->kind, $this->type, 1)))
      {
         throw new Exception('Database error');
      }
      elseif ($res === true)
      {
         $this->id = 1;
         
         return $this->generateUpdateQuery($attributes, $options);
      }
      
      $fields = '`'.$this->conf['db_map']['pkey'].'`';
      $values = '1';

      // Attributes
      while (list($field, $value) = each($attributes))
      {
         $fields .= ", `".$field."`";
         $values .= ", ".$this->getValueForSQL($field, $value);
      }
      
      $query  = "INSERT INTO `".$this->conf['db_map']['table']."`(".$fields.") ";
      $query .= "VALUES(".$values.")";
      
      return $query;
   }
   
   /**
    * (non-PHPdoc)
    * @see ext/OEF/Framework/lib/model/base/BaseEntityModel#generateUpdateQuery($attributes, $options)
    */
   protected function generateUpdateQuery(array& $attributes, array& $options = array())
   {
      $fields = array();
      $errors = array();
      
      // Attributes
      foreach ($attributes as $field => $value)
      {
         $fields[] = "`".$field."`=".$this->getValueForSQL($field, $value);
         
         $event = $this->container->getEvent($this, $this->kind.'.model.onUpdate'.$field);
         $event->setReturnValue(true);
         try
         {
            $this->container->getEventDispatcher()->notify($event);
            
            if (!$event->getReturnValue())
            {
               $errors[$field] = 'Module error';
            }
            else
            {
               $fields[] = "`".$field."`=".$this->getValueForSQL($field, $value);
            }
         }
         catch (Exception $e)
         {
            $errors[$field] = $e->getMessage();
         }
      }
      
      if ($errors) throw new Exception(serialize($errors));
      
      $db_map =& $this->conf['db_map'];
      $query  =  "UPDATE `".$db_map['table']."` SET ".implode(", ", $fields)." WHERE `".$db_map['pkey']."`=".$this->id;
      
      return $query;
   }
   
   /**
    * (non-PHPdoc)
    * @see BaseEntityModel#load($id, $options)
    */
   public function load($id = 1, array& $options = array())
   {
      // Check permissions
      if (defined('IS_SECURE') && !$this->container->getUser()->isAdmin())
      {
         return false;
      }
      
      // Execute method
      $id = 1;
      
      return parent::load($id, $options);
   }
   
   /**
    * (non-PHPdoc)
    * @see BaseEntityModel#toArray($options)
    */
   public function toArray(array $options = array())
   {
      // Check permissions
      if (defined('IS_SECURE') && !$this->isNew && !$this->container->getUser()->isAdmin())
      {
         return array();
      }
      
      // Execute method
      $result = array();
      
      foreach ($this->conf['attributes'] as $name)
      {
         $result[$name] = isset($this->attributes[$name]) ? $this->attributes[$name] : null;
      }
      
      //$result[$this->conf['db_map']['pkey']] = $this->id;
      
      return $result;
   }
   
   /**
    * (non-PHPdoc)
    * @see ext/OEF/Framework/lib/model/base/BaseEntityModel#prepareToImport($values, $options)
    */
   protected function prepareToImport(array& $values, array& $options = array())
   {
      $errors = array();
      $pkey   = $this->conf['db_map']['pkey'];
      
      if (!empty($values[$pkey]))
      {
         unset($values[$pkey]);
      }
      
      if (!$this->load()) // New
      {
         $this->id         = null;
         $this->isNew      = true;
         $this->isDeleted  = false;
         $this->attributes = null;
         $this->isModified = false;
         $this->modified   = array();
      }
      
      return $errors;
   }
   
   
   
   
   /************************** For control access rights **************************************/
   
   
   /**
    * (non-PHPdoc)
    * @see BaseEntityModel#delete($options)
    */
   public function delete(array& $options = array())
   {
      // Check permissions
      if (defined('IS_SECURE') && !$this->container->getUser()->isAdmin())
      {
         return array('Access denied');
      }
      
      // Execute method
      return parent::delete($options);
   }
}
