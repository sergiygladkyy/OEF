<?php

require_once('lib/controller/base/ObjectsController.php');

class DocumentsController extends ObjectsController
{
   const kind = 'documents';
   
   protected static $instance = array();
   
   protected function __construct($type, array& $options = array())
   {
      parent::__construct(self::kind, $type, $options);
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
    * Post document
    * 
    * @param int $id
    * @param array $options
    * @return array
    */
   public function post($id, array $options = array())
   {
      $status = true;
      $errors = array();

      $model  = $this->container->getModel($this->kind, $this->type, $options);
      
      if (!$model->load($id, $options))
      {
         return array('status' => false, 'result' => array('msg' => 'Document "'.$this->type.'" with id "'.((int) $id).'" not exists'), 'errors' => $errors);
      }
      
      $errors = $model->post($options);
      
      if ($errors)
      { 
         $status = false;
         $result['msg'] = 'Document "'.$this->type.'" not posted';
      }
      else $result['msg'] = 'Document "'.$this->type.'" posted succesful';
      
      return array('status' => $status, 'result' => $result, 'errors' => $errors);
   }
   
   /**
    * Unpost document
    * 
    * @param int $id
    * @param array $options
    * @return array
    */
   public function unpost($id, array $options = array())
   {
      $status = true;
      $errors = array();

      $model  = $this->container->getModel($this->kind, $this->type, $options);
      
      if (!$model->load($id, $options))
      {
         return array('status' => false, 'result' => array('msg' => 'Document "'.$this->type.'" with id "'.((int) $id).'" not exists'), 'errors' => $errors);
      }
      
      $errors = $model->unpost($options);
      
      if ($errors)
      { 
         $status = false;
         $result['msg'] = 'Document "'.$this->type.'" not unposted';
      }
      else $result['msg'] = 'Document "'.$this->type.'" unposted succesful';
      
      return array('status' => $status, 'result' => $result, 'errors' => $errors);
   }
}
