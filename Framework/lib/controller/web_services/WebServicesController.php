<?php

class WebServicesController
{
   protected $kind = 'web_services';
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
    * Execute action
    * 
    * @param array $options
    * @return array
    */
   public function execute(array $options = array())
   {
      $errors   = array();
      $result   = array();
      $request  = $this->container->getRequest();
      $responce = $this->container->getResponce();
      
      if (!$this->checkPermission())
      {
         $responce->setStatusCode('401');
         $responce->sendHttpHeaders();
         exit;
      }
      
      $m_opt = isset($options['model']) ? $options['model'] : array();
      $model = $this->container->getModel($this->kind, $this->type, $m_opt);
      
      $action = $request->getGetParameter('action', '');
      
      if (!$model->hasAction($action))
      {
         $responce->setStatusCode('400');
         $responce->sendHttpHeaders();
         exit;
      }
      
      $attrs  = $request->getGetParameters();
      $errors = $model->execute($result, $action, $attrs);
      
      $return = array(
         'status' => $errors ? false : true,
         'result' => $result,
         'errors' => $errors
      );
      
      $content = Utility::convertArrayToJSONString($return);
      
      $responce->setStatusCode('200');
      $responce->setContent($content);
      $responce->send();
      exit;
   }
   
   /**
    * Check permissions for current user
    * 
    * @return boolean
    */
   protected function checkPermission()
   {
      $user = $this->container->getUser();
      
      return $user->hasPermission('asd');
   }
}