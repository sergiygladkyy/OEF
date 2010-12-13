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
      $response = $this->container->getResponse();
      
      $user = $this->container->getUser();
      
      if (!$user->hasPermission('global.UseRemoteCalls'))
      {
         $response->setStatusCode('200');
         $response->setContent('{"status": false, "result": [], "errors": ["Access Denied"]}');
         $response->send();
         exit;
      }
      
      $m_opt = isset($options['model']) ? $options['model'] : array();
      $model = $this->container->getModel($this->kind, $this->type, $m_opt);
      
      $action = $request->getGetParameter('action', '');
      
      if (!$model->hasAction($action))
      {
         $response->setStatusCode('400');
         $response->sendHttpHeaders();
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
      
      $response->setStatusCode('200');
      $response->setContent($content);
      $response->send();
      exit;
   }
}