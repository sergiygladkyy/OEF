<?php

require_once('lib/model/base/BaseNotStorageEntityModel.php');

class DataProcessorModel extends BaseNotStorageEntityModel
{
   const kind = 'data_processors';
   
   public function __construct($type, array& $options = array())
   {
      parent::__construct(self::kind, $type, $options);
   }
   
   /**
    * Data import
    * 
    * @param array $options
    * @return array errors
    */
   public function import(array $options = array())
   {
      $errors = $this->validateAttributes($this->conf['attributes']);
      
      if (!empty($errors)) return $errors;
      
      $event = $this->container->getEvent($this, $this->kind.'.'.$this->type.'.onImport');
      $event['headline'] = $this->toArray();
      
      $event->setReturnValue(true);
      try
      {
         $this->container->getEventDispatcher()->notify($event);
      }
      catch(Exception $e)
      {
         return array($e->getMessage());
      }
      
      if (!$event->getReturnValue()) return array('Data not imported. Module error.');
      
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