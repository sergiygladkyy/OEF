<?php

class DataProcessorsController
{
   protected $kind = 'data_processors';
   protected $type = null;
   protected $container = null;
   
   protected static $instance = array();
   
   protected function __construct($type, array& $options = array())
   {
      $this->type = $type;
      
      $this->container = Container::getInstance();
   }
   
   /**
    * Get instance
    *
    * @return this
    */
   public static function getInstance($type, array $options = array())
   {
      if(empty(self::$instance[$type]))
      {
         self::$instance[$type] = new self($type, $options);
      }

      return self::$instance[$type];
   }

   
   
   
   /**
    * Return params for ImportForm
    * 
    * @param array $options
    * @return array
    */
   public function displayImportForm(array $options = array())
   {
      $errors = array();
      
      $model = $this->container->getModel($this->kind, $this->type, $options); 
      
      return array('status' => true, 
                   'result' => array(
                      'select' => $model->retrieveSelectDataForRelated(array(), $options)
                   ),
                   'errors' => $errors
      );
   }
   
   /**
    * Data import
    * 
    * @param array $headline
    * @param array $options
    * @return array
    */
   public function import(array $headline = array(), array $options = array())
   {
      $errors = array();
      
      $model = $this->container->getModel($this->kind, $this->type, $options);
      
      if (!empty($headline)) $errors = $model->fromArray($headline);
      
      if (!empty($errors)) return array('status' => false, 'result' => array('msg' => 'File not imported'), 'errors' => $errors);
      
      $errors = $model->import($headline, $options);
      
      if (!empty($errors)) return array('status' => false, 'result' => array('msg' => 'File not imported'), 'errors' => $errors);
      
      return array('status' => true,
                   'result' => array(
                      'msg' => 'Imported succesfully'
                   ),
                   'errors' => $errors
      );
   }
}