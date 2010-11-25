<?php

require_once('lib/model/base/BaseNotStorageEntityModel.php');

class WebServiceModel extends BaseNotStorageEntityModel
{
   const kind = 'web_services';
   
   protected $action  = null;
   protected $actions = array();
   
   public function __construct($type, array& $options = array())
   {
      parent::__construct(self::kind, $type, $options);
   }
   
   /**
    * (non-PHPdoc)
    * @see lib/model/base/BaseNotStorageEntityModel#initialize($kind, $type)
    */
   protected function initialize($kind, $type)
   {
      $this->kind = $kind;
      $this->type = $type;
      
      $confname = self::getConfigurationName($kind, $type);
      
      if (isset(self::$config[$confname])) return true;
      
      $CManager = $this->container->getConfigManager();
      $this->actions = $CManager->getInternalConfiguration($kind.'.actions.actions', $type);
      
      self::$config[$confname] = array(
         'attributes' => array(),
         'types'      => array(),
         'precision'  => array(),
         'references' => array(),
         'required'   => array()
      );
      
      $this->conf =& self::$config[$confname];
      
      return true;
   }
   
   
   
   /**
    * Set current action
    * 
    * @throws Exception
    * @param string $name - action name
    * @return boolean
    */
   public function setAction($name)
   {
      if ($this->action == $name) return true;
      
      if (!$this->hasAction($name))
      {
         throw new Exception(__METHOD__.': Action "'.$name.'" not exists.');
      }
      
      $kind     = $this->kind.'.'.$this->type.'.actions';
      $confname =  self::getConfigurationName($kind, $name);
      
      if (!isset(self::$config[$confname]) && !self::setup($kind, $name))
      {
         return false;
      }
      
      $this->conf   =& self::$config[$confname];
      $this->action =  $name;
      
      return true;
   }
   
   /**
    * Get current action
    * 
    * @return string
    */
   public function getAction()
   {
      return $this->action;
   }
   
   /**
    * Has action with with the specified name
    * 
    * @param string $name - action name
    * @return boolean
    */
   public function hasAction($name)
   {
      return in_array($name, $this->actions);
   }

   
   
   /**
    * Execute specified action
    * 
    * @param array& $result - return data
    * @param string $action - action name
    * @param array  $attributes - action attributes
    * @return array - errors
    */
   public function execute(& $result, $action = null, array $attributes = array(), array $options = array())
   {
      // Prepare object
      if (!empty($action))
      {
         // Set action
         if (!$this->hasAction($action)) return array('Unknow action "'.$action.'"');
         if (!$this->setAction($action)) return array('Initialize error');
         
         // Set attributes
         if (!empty($attributes))
         {
            $errors = $this->fromArray($attributes, $options);
            
            if ($errors) return $errors;
         }
      }
      elseif (empty($this->action))
      {
         $result = array();
         
         return array('Action not set');
      }
      
      // Validate attributes
      if (!empty($this->conf['attributes']))
      {
         $errors = $this->validateAttributes($this->conf['attributes'], $options);
         
         if (!empty($errors)) return $errors;
      }
      else $this->attributes = array();
      
      // Execute action
      try {
         $classname = ucfirst($this->kind).ucfirst($this->type);
         $result = call_user_func(array($classname, $this->action), $this->attributes);
      }
      catch (Exception $e)
      {
         return array($e->getMessage());
      }
      
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