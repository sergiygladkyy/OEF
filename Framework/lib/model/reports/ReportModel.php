<?php

require_once('lib/model/base/BaseNotStorageEntityModel.php');

class ReportModel extends BaseNotStorageEntityModel
{
   const kind = 'reports';
   
   public function __construct($type, array& $options = array())
   {
      parent::__construct(self::kind, $type, $options);
   }
   
   /**
    * Create report
    * 
    * @param string& $buffer
    * @return array - errors
    */
   public function generate(& $buffer)
   {
      // Check permissions
      if (defined('IS_SECURE') && !$this->container->getUser()->hasPermission($this->kind.'.'.$this->type.'.Use'))
      {
         return array('Access denied');
      }
      
      // Execute method
      $errors = $this->validateAttributes($this->conf['attributes']);
      
      if (!empty($errors)) return $errors;
      
      $event = $this->container->getEvent($this, $this->kind.'.'.$this->type.'.onGenerate');
      $event['headline'] = $this->toArray();
      
      ob_start();
      
      $this->container->getEventDispatcher()->notify($event);
      
      $buffer = ob_get_clean();
      
      return array();
   }
   
   /**
    * Decode report item
    * 
    * @param mixed $parameters
    * @return mixed
    */
   public function decode($parameters)
   {
      // Check permissions
      if (defined('IS_SECURE') && !$this->container->getUser()->hasPermission($this->kind.'.'.$this->type.'.Use'))
      {
         return array('errors' => array('Access denied'));
      }
      
      // Execute method
      $event = $this->container->getEvent($this, $this->kind.'.'.$this->type.'.onDecode');
      $event['parameters'] = $parameters;
      
      $this->container->getEventDispatcher()->notify($event);

      return $event->getReturnValue();
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
      if (defined('IS_SECURE') && !$this->container->getUser()->hasPermission($this->kind.'.'.$this->type.'.Use'))
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
}