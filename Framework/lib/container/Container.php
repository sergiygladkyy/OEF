<?php

import('lib.config.ConfigManager');

class Container
{
   protected static $instance = null;

   protected
      $ConfigManager    = null,
      $db               = null,
      $o_db             = null,
      $model            = null,
      $cmodel           = null,
      $controller       = null,
      $validator        = null,
      $event            = null,
      $event_dispatcher = null,
      $modules_manager  = null,
      $request          = null,
      $response         = null,
      $user             = null,
      $storage          = null;

   
   /**
    * Create new instance
    *
    * @throws Exception
    * @param array $options
    * @return this
    */
   public static function createInstance(array $options = array())
   {
      if(is_object(self::$instance) && is_a(self::$instance, 'Container'))
      {
         return self::$instance;
      }
      
      if (isset($options['container']))
      {
         if (is_string($options['container']))
         {
            $classname = $options['container'];
            
            import('lib.container.'.$classname);
            
            if (!class_exists($classname))
            {
               throw new Exception(__METHOD__.': Container "'.$classname.'" does not exist');
            }
            
            self::$instance = call_user_func(array($classname, 'createInstance'), $options);
            
            if (!is_object(self::$instance))
            {
               throw new Exception(__METHOD__.': Can\'t create "'.$classname.'" container object');
            }
         }
         elseif (is_object($options['container']))
         {
            self::$instance = $options['container'];
         }
         
         if (!is_a(self::$instance, 'Container'))
         {
            throw new Exception(__METHOD__.': Not supported Container object');
         }
      }
      else
      {
         self::$instance = new Container($options);
      }
      
      return self::$instance;
   }

   /**
    * Get this instance
    *
    * @throws Exception
    * @return this
    */
   public static function getInstance()
   {
      if(is_null(self::$instance))
      {
         throw new Exception(__METHOD__.": Instance is not exists");
      }

      return self::$instance;
   }
   
   /**
    * Constructor
    * 
    * @param array& $options
    * @return void
    */
   protected function __construct(array& $options = array())
   {
      if (isset($options['config_manager']))
      {
         if (is_object($options['config_manager']))
         {
            $this->ConfigManager = $options['config_manager'];
         }
         elseif (is_string($options['config_manager']))
         {
            $classname = $options['config_manager'];
            
            import('lib.config.'.$classname);
            
            if (!class_exists($classname))
            {
               throw new Exception(__METHOD__.': Configuration Manager "'.$classname.'" does not exist');
            }
            
            $this->ConfigManager = call_user_func(array($classname, 'createInstance'), $options);
            
            if (!is_object($this->ConfigManager))
            {
               throw new Exception(__METHOD__.': Can\'t create "'.$classname.'" Configuration Manager object');
            }
         }
         else
         {
            throw new Exception(__METHOD__.': Invalid option "config_manager"');
         }
         
         if (!is_a($this->ConfigManager, 'ConfigManager'))
         {
            throw new Exception(__METHOD__.': Not supported Configuration Manager object');
         }
      }
      else
      {
         $this->ConfigManager = ConfigManager::createInstance($options);
      }
   }

   /**
    * Get configuration manager
    * 
    * @param array& $options
    * @return void
    */
   public function getConfigManager(array& $options = array())
   {
      return $this->ConfigManager;
   }
   
   /**
    * Get db-manager object
    * 
    * @throws Exception
    * @param array& $options
    * @return object
    */
   public function getDBManager(array& $options = array())
   {
      if(isset($this->db) && is_object($this->db)) return $this->db;
      
      $_conf = $this->ConfigManager->getDBConfiguration($options);

      $classname = $_conf['classname'];
      
      $_conf['options'] = is_array($_conf['options']) ? array_merge($_conf['options'], $options) : $options;

      import('lib.db.'.$classname);
      
      if (!class_exists($classname)) throw new Exception(__METHOD__.': DB driver "'.$classname.'" does not exist');
      
      $this->db = call_user_func(array($classname, 'createInstance'), $_conf);
      
      if (!is_object($this->db)) throw new Exception(__METHOD__.': DB driver "'.$classname.'" does not exist');
      
      return $this->db;
   }
   
   /**
    * Get object db-manager (for execution objective query)
    * 
    * @throws Exception
    * @param array& $options
    * @return object
    */
   public function getODBManager(array& $options = array())
   {
      if(isset($this->o_db) && is_object($this->o_db)) return $this->o_db;
      
      $_conf = $this->ConfigManager->getDBConfiguration($options);

      $classname = $_conf['oclassname'];
      
      $_conf['options'] = is_array($_conf['options']) ? array_merge($_conf['options'], $options) : $options;

      import('lib.db.'.$classname);
      
      if (!class_exists($classname)) throw new Exception(__METHOD__.': ODB driver "'.$classname.'" does not exist');
      
      $_conf['dbmap'] = $this->ConfigManager->getInternalConfiguration('db_map');
      
      $this->o_db = call_user_func(array($classname, 'createInstance'), $_conf);
      
      if (!is_object($this->o_db)) throw new Exception(__METHOD__.': ODB driver "'.$classname.'" does not exist');
      
      return $this->o_db;
   }
   
   /**
    * Get empty model object
    * 
    * @param string $kind
    * @param string $type
    * @param array& $options
    * @return object
    */
   public function getModel($kind, $type, array& $options = array())
   {
      if (empty($this->model[$kind.'.'.$type]))
      {
         $conf = $this->ConfigManager->getInternalConfigurationByKind($kind.'.model', $type, $options);

         if (empty($conf['modelclass'])) throw new Exception(__METHOD__.': unknow model for "'.$kind.'.'.$type.'"');

         $classname = $conf['modelclass'];

         $mathches = array();
         preg_match('/[^.]*$/', $kind, $mathches);
         import('lib.model.'.$mathches[0].'.'.$classname);

         if (!class_exists($classname)) throw new Exception(__METHOD__.': model class "'.$classname.'" does not exist');

         if ($mathches[0] != $kind)
         {
            $model = new $conf['modelclass']($kind, $type, $options);
         }
         else $model = new $conf['modelclass']($type, $options);
         
         if (!is_a($model, 'BaseNotStorageEntityModel'))
         {
            throw new Exception(__METHOD__.': not supported model class "'.$classname.'"');
         }

         $this->model[$kind.'.'.$type] = $model;
      }
      
      return clone $this->model[$kind.'.'.$type];
   }
   
   /**
    * Get cmodel object
    * 
    * @param string $kind
    * @param string $type
    * @param array& $options
    * @return object
    */
   public function getCModel($kind, $type, array& $options = array())
   {
      if (empty($this->cmodel[$kind.'.'.$type]))
      {
         $conf = $this->ConfigManager->getInternalConfigurationByKind($kind.'.model', $type, $options);

         if (empty($conf['cmodelclass'])) throw new Exception(__METHOD__.': unknow model for "'.$kind.'.'.$type.'"');

         $classname = $conf['cmodelclass'];
         
         $mathches = array();
         preg_match('/[^.]*$/', $kind, $mathches);
         import('lib.model.'.$mathches[0].'.'.$classname);

         if (!class_exists($classname)) throw new Exception(__METHOD__.': model class "'.$classname.'" does not exist');

         if ($mathches[0] != $kind)
         {
            $model = call_user_func(array($classname, 'getInstance'), $kind, $type, $options);
         }
         else $model = call_user_func(array($classname, 'getInstance'), $type, $options);

         if (!is_a($model, 'BaseEntitiesModel'))
         {
            throw new Exception(__METHOD__.': not supported model class "'.$classname.'"');
         }

         $this->cmodel[$kind.'.'.$type] = $model;
      }
      
      return clone $this->cmodel[$kind.'.'.$type];
   }
   
   /**
    * Get controller
    * 
    * @param string $kind
    * @param string $type
    * @param array& $options
    * @return object
    */
   public function getController($kind, $type, array& $options = array())
   {
      if (empty($this->controller[$kind.'.'.$type]))
      {
         $conf = $this->ConfigManager->getInternalConfigurationByKind($kind.'.controller', $type, $options);

         if (empty($conf['classname'])) throw new Exception(__METHOD__.': unknow controller for "'.$kind.'.'.$type.'"');

         $classname = $conf['classname'];

         $mathches = array();
         preg_match('/[^.]*$/', $kind, $mathches);
         import('lib.controller.'.$mathches[0].'.'.$classname);

         if (!class_exists($classname)) throw new Exception(__METHOD__.': controller class "'.$classname.'" does not exist');

         if ($mathches[0] != $kind)
         {
            $controller = call_user_func(array($classname, 'getInstance'), $kind, $type, $options);
         }
         else $controller = call_user_func(array($classname, 'getInstance'), $type, $options);
         
         if (!is_a($controller, 'BaseController') && 
             !is_a($controller, 'ReportsController') &&
             !is_a($controller, 'DataProcessorsController') &&
             !is_a($controller, 'WebServicesController')
         )
         {
            throw new Exception(__METHOD__.': not supported controller class "'.$classname.'"');
         }

         $this->controller[$kind.'.'.$type] = $controller;
      }
      
      return clone $this->controller[$kind.'.'.$type];
   }
   
   /**
    * Get validator object
    * 
    * @param array& $options
    * @return object
    */
   public function getValidator(array& $options = array())
   {
      if(isset($this->validator) && is_object($this->validator))
      {
         return $this->validator;
      }
      
      $_conf = $this->ConfigManager->getValidatorConfiguration($options);

      $classname = $_conf['classname'];
 
      $_conf['options'] = is_array($_conf['options']) ? array_merge($_conf['options'], $options) : $options;
 
      import('lib.validator.'.$classname);

      if (!class_exists($classname)) throw new Exception(__METHOD__.': Validator class "'.$classname.'" does not exist');

      $this->validator = call_user_func(array($classname, 'getInstance'), $_conf['options']);
      
      return $this->validator;
   }
   
   /**
    * Get event object
    * 
    * @param mixed $subject
    * @param string $name
    * @param array $parameters
    * @param array& $options
    * @return object
    */
   public function getEvent($subject, $name, $parameters = array(), array& $options = array())
   {
      if(empty($this->event))
      {
         $_conf = $this->ConfigManager->getEventConfiguration($options);

         $classname = $_conf['classname_event'];

         import('lib.event.'.$classname);

         if (!class_exists($classname)) throw new Exception(__METHOD__.': Event class "'.$classname.'" does not exist');

         $this->event = $classname;
      }

      return new $this->event($subject, $name, $parameters);
   }
   
   /**
    * Get event dispatcher
    * 
    * @param array& $options
    * @return object
    */
   public function getEventDispatcher(array& $options = array())
   {
      if(isset($this->event_dispatcher) && is_object($this->event_dispatcher))
      {
         return $this->event_dispatcher;
      }
      
      $_conf = $this->ConfigManager->getEventConfiguration($options);

      $classname = $_conf['classname_dispatcher'];

      import('lib.event.'.$classname);

      if (!class_exists($classname)) throw new Exception(__METHOD__.': EventDispatcher class "'.$classname.'" does not exist');

      $this->event_dispatcher = new $classname();
      
      return $this->event_dispatcher;
   }
   
   /**
    * Get modules manager
    * 
    * @param array& $options
    * @return object
    */
   public function getModulesManager(array& $options = array())
   {
      if(isset($this->modules_manager) && is_object($this->modules_manager))
      {
         return $this->modules_manager;
      }
      
      $_conf = $this->ConfigManager->getModulesConfiguration($options);

      $classname = $_conf['classname'];
 
      $_conf['options'] = is_array($_conf['options']) ? array_merge($_conf['options'], $options) : $options;
 
      import('lib.modules.'.$classname);

      if (!class_exists($classname)) throw new Exception(__METHOD__.': ModulesManager class "'.$classname.'" does not exist');

      $this->modules_manager = call_user_func(array($classname, 'getInstance'), $_conf['options']);
      
      return $this->modules_manager;
   }
   
   /**
    * Create and return new Pager object
    * 
    * @param string $kind
    * @param string $type
    * @param array& $options
    * @return object
    */
   public function createPager($kind, $type, array& $options = array())
   {
      $_conf = $this->ConfigManager->getPagerConfiguration($options);

      $classname = $_conf['classname'];
 
      if (isset($options['config']) && is_array($options['config']))
      {
         if (isset($_conf['options']) && is_array($_conf['options']))
         {
            $options['config'] = array_merge($_conf['options'], $options['config']);
         }
      }
      elseif (isset($_conf['options']) && is_array($_conf['options']))
      {
         $options['config'] = $_conf['options'];
      }
      
      import('lib.utility.'.$classname);

      if (!class_exists($classname)) throw new Exception(__METHOD__.': Pager class "'.$classname.'" does not exist');

      return new $classname($kind, $type, $options);
   }

   /**
    * Get Request object
    * 
    * @param array& $options
    * @return object
    */
   public function getRequest(array& $options = array())
   {
      if (isset($this->request) && is_object($this->request))
      {
         return $this->request;
      }
      
      $_conf = $this->ConfigManager->getRequestConfiguration($options);

      $classname = $_conf['classname'];

      import('lib.request.'.$classname);

      if (!class_exists($classname)) throw new Exception(__METHOD__.': Request class "'.$classname.'" does not exist');

      // Parameters
      if (isset($options['parameters']) && is_array($options['parameters']))
      {
         $_conf['parameters'] = is_array($_conf['parameters']) ? array_merge($_conf['parameters'], $options['parameters']) : $options['parameters'];
      }
      elseif (!isset($_conf['parameters']) || !is_array($_conf['parameters']))
      {
         $_conf['parameters'] = array();
      }
      
      // Options
      if (isset($options['options']) && is_array($options['options']))
      {
         $_conf['options'] = is_array($_conf['options']) ? array_merge($_conf['options'], $options['options']) : $options['options'];
      }
      elseif (!isset($_conf['options']) || !is_array($_conf['options']))
      {
         $_conf['options'] = array();
      }
      
      $this->request = call_user_func(array($classname, 'createInstance'), $_conf['parameters'], $_conf['options']);
      
      return $this->request;
   }
   
   /**
    * Get Response object
    * 
    * @param array& $options
    * @return object
    */
   public function getResponse(array& $options = array())
   {
      if (isset($this->response) && is_object($this->response))
      {
         return $this->response;
      }
      
      $_conf = $this->ConfigManager->getResponseConfiguration($options);

      $classname = $_conf['classname'];

      import('lib.response.'.$classname);

      if (!class_exists($classname)) throw new Exception(__METHOD__.': Response class "'.$classname.'" does not exist');

      // Options
      if (isset($options['options']) && is_array($options['options']))
      {
         $_conf['options'] = is_array($_conf['options']) ? array_merge($_conf['options'], $options['options']) : $options['options'];
      }
      elseif (!isset($_conf['options']) || !is_array($_conf['options']))
      {
         $_conf['options'] = array();
      }
      
      $this->response = call_user_func(array($classname, 'createInstance'), $_conf['options']);
      
      return $this->response;
   }
   
   /**
    * Get user object
    * 
    * @param string $authtype
    * @param string $key
    * @param array $options
    * @return object
    */
   public function getUser($authtype = '', $key = '', array $options = array())
   {
      if (is_object($this->user))
      {
         return $this->user;
      }
      
      switch ($authtype)
      {
         case 'MTAuth':
            // Get user class
            $classname = 'MTUser';
            
            import('lib.user.'.$classname);

            if (!class_exists($classname))
            {
               throw new Exception(__METHOD__.': user class "'.$classname.'" does not exist');
            }

            // Create User object
            try {
               $this->user = call_user_func(array($classname, 'createInstance'), $key);
            }
            catch (Exception $e)
            {
               if ($e->getCode() == 1) // Alredy exists
               {
                  $this->user = call_user_func(array($classname, 'getCurrent'));
               }
               else throw $e;
            }
            
            break;
         
         
         case 'LDAP':
            throw new Exception('Not supported autorization type "LDAP"');
            break;
         
         
         case 'Basic':
         default:
            // Get storage object
            if (!$this->storage)
            {
               $storage = 'SessionStorage';
               import('lib.storage.'.$storage);
                
               if (!class_exists($storage))
               {
                  throw new Exception(__METHOD__.': storage class "'.$storage.'" does not exist');
               }

               $this->storage = new $storage();
            }
            
            // Get user class
            $classname = 'BasicUser';
            import('lib.user.'.$classname);
            
            if (!class_exists($classname))
            {
               throw new Exception(__METHOD__.': user class "'.$classname.'" does not exist');
            }

            // Create User object
            try {
               $this->user = call_user_func(array($classname, 'createInstance'), $this->storage);
            }
            catch (Exception $e)
            {
               if ($e->getCode() == 1) // Alredy exists
               {
                  $this->user = call_user_func(array($classname, 'getCurrent'));
               }
               else throw $e;
            }

            // Login
            if (!$this->user->isAuthenticated() && !empty($key))
            {
               $params = explode('/', base64_decode($key));
                
               if (isset($params[1])) $this->user->login($params[0], $params[1]);
            }
      }
      
      // Check User object
      if (!is_a($this->user, 'BaseUser'))
      {
         throw new Exception(__METHOD__.': not supported user class "'.$classname.'"');
      }
      
      return $this->user;
   }
}
