<?php

class Constants
{
   const kind = 'Constants';
   const type = null;
   
   protected static
      $container = null,
      $constants = null,
      $instance  = array();
   
   protected function __construct(array& $options = array())
   {
      self::initialize($options);
   }
   
   /**
    * Get instance
    *
    * @return this
    */
   public static function getInstance(array $options = array())
   {
      if (empty(self::$instance))
      {
         self::$instance = new self($options);
      }

      return self::$instance;
   }

   
   protected static function initialize(array $options = array())
   {
      self::$container = Container::getInstance();
      
      $item = self::$container->getModel(self::kind, self::type, $options);
      $item->load();
      
      self::$constants = $item->toArray();
   }
   
   
   /**
    * Return params for EditForm
    * 
    * @param array $headline
    * @param array $options
    * @return array
    */
   public function displayEditForm(array $options = array())
   {
      $errors = array();
      $model  = self::$container->getModel(self::kind, self::type, $options); 
      
      return array(
         'status' => true, 
         'result' => array(
            'item' =>   self::$constants,
            'select' => $model->retrieveSelectDataForRelated(array(), $options)
         ),
         'errors' => $errors
      );
   }
   
   /**
    * Update entity
    * 
    * @param array $values
    * @param array $options
    * @return array
    */
   public function update(array $values, array $options = array())
   {
      unset($values['_id']);
      
      $return = $this->processFrom($values, $options);
      
      if ($return['status'])
      { 
         $return['result']['msg'] = 'Updated succesfully';
      }
      else
      {
         $return['result']['msg'] = 'Not updated';
      }
      
      return $return;
   }
   
   /**
    * Process entity HTML-form
    * 
    * @param $values
    * @param $options
    * @return array
    */
   protected function processFrom($values, array $options = array())
   {
      $item   = self::$container->getModel(self::kind, self::type, $options);
      $errors = $item->fromArray($values);
      
      if ($errors) return array('status' => false, 'errors' => $errors);
      
      $res = $item->save($options);
      
      if (empty($res))
      {
         self::$constants = $item->toArray();
         
         return array('status' => true, 'result' => array());
      }
      else return array('status' => false, 'errors' => $res);
   }
   
   /**
    * Get constant by name
    * 
    * @param string $name
    * @return mixed
    */
   public function get($name)
   {
      if (self::$constants === null)
      {
         self::initialize();
      }
      
      return isset(self::$constants[$name]) ? self::$constants[$name] : null;
   }
}