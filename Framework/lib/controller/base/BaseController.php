<?php

abstract class BaseController
{
   protected $kind;
   protected $type;
   protected $container = null;
   
   protected function __construct($kind, $type, array& $options = array())
   {
      $this->kind = $kind;
      $this->type = $type;
      
      $this->container = Container::getInstance();
   }
   
   public function getKind()
   {
      return $this->kind;
   }
   
   public function getType()
   {
      return $this->type;
   }
   
   /**
    * Notify form event
    * 
    * @param string $formName
    * @param string $eventName
    * @param array& $formData
    * @param array& $parameters
    * @return array
    */
   public function notifyFormEvent($formName, $eventName, array& $formData = array(), array& $parameters = array())
   {
      $event = $this->container->getEvent($this, $this->kind.'.'.$this->type.'.forms.'.$formName.'.'.$eventName);
      $event->setReturnValue(null);
      $event['formName'] = $formName;
      $event['formData'] = $formData;
      $event['parameters'] = $parameters;
      
      try
      {
         $this->container->getEventDispatcher()->notify($event);
      }
      catch(Exception $e)
      {
         return array(
            'status' => false,
            'result' => array(),
            'errors' => array($e->getMessage())
         );
      }
      
      $errors = array();
      $result = $event->getReturnValue();
      
      if (is_null($result))
      {
         $status = false;
         $errors = array('Event not processed. Module error');
      }
      elseif (!is_array($result))
      {
         $status = false;
         $errors = array('Module error');
      }
      elseif (!isset($result['type']) || !isset($result['data']))
      {
         $status = false;
         $errors = array('Module error');
      }
      else $status = true;
      
      return array(
         'status' => $status,
         'result' => $status ? $result : array(), 
         'errors' => $errors
      );
   }
   
   /**
    * Get default values for edit form
    * 
    * @param array& $options
    * @return array
    */
   protected function getDefaultValuesForEditForm($formName = 'Default', array $options = array())
   {
      $event = $this->container->getEvent($this, $this->kind.'.'.$this->type.'.forms.'.$formName.'.onBeforeOpening');
      $event->setReturnValue(null);
      $event['formName'] = $formName;
      $event['options']  = $options;
      
      try
      {
         $this->container->getEventDispatcher()->notify($event);
      }
      catch(Exception $e)
      {
         return array(
            'status' => false,
            'result' => array(),
            'errors' => array($e->getMessage())
         );
      }
      
      $errors = array();
      $result = $event->getReturnValue();
      
      if (is_null($result))
      {
         $status = false;
         $errors = array('Event not processed. Module error');
      }
      elseif (!is_array($result) || !(isset($result['attributes']) || isset($result['select']) || isset($result['tabulars'])))
      {
         $status = false;
         $errors = array('Module error');
      }
      else $status = true;
      
      return array(
         'status' => $status,
         'result' => $status ? $result : array(), 
         'errors' => $errors
      );
   }
}
