<?php 

class ConfigManager
{
   protected static $instance = null;
   
   protected $map_path = 'config/internal/config_map.php';
   
   protected $map = null;
   
   protected $cache = array();
   
   protected $cache_by_kind = array();
   
   // Cache variables
   protected
      $_container = null,
      $_db_config = null;

   /**
    * Create new instance
    *
    * @param array $options
    * @return this
    */
   public static function createInstance(array $options)
   {
      if(is_null(self::$instance))
      {
         self::$instance = new ConfigManager($options);
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
    * Construct
    *
    * @throws Exception
    * @param array& $options
    * @return this
    */
   protected function __construct(array& $options = array())
   {
      if (!empty($options['map_path']) && is_string($options['map_path']))
      {
         $this->map_path = $options['map_path'];
      }
      elseif (!empty($options['base_dir']) && is_string($options['base_dir']))
      {
         $this->map_path = $options['base_dir'].'/'.$this->map_path;
      }
      
      if (!file_exists($this->map_path)) throw new Exception(__METHOD__.': Configuration map file is not exists');

      $this->map = Utility::loadArrayFromFile($this->map_path);
   }
   

   
   /**
    * Get configuration map
    * 
    * @param array& $options
    * @return array
    */
   public function getConfigMap(array& $options = array())
   {
      return $this->map;
   }
   
   
   /**
    * Get DB configuration
    * 
    * @param array& $options
    * @return array
    */
   public function getDBConfiguration(array& $options = array())
   {
      if (!isset($this->_db_config))
      {
         if (!isset($this->_container))
         {
            $this->_container = Utility::loadArrayFromFile($this->map['container'], $options);
         }
         
         $this->_db_config = Utility::loadArrayFromFile($this->map['db_settings'], $options);
         $this->_db_config = array_merge($this->_container['db'], $this->_db_config);
      }
      
      return $this->_db_config;
   }
   
   /**
    * Get internal configuration by name
    * 
    * @param $name - configuration name
    * @return array
    */
   public function getInternalConfiguration($name, $key = false, array& $options = array())
   {
      if (!isset($this->cache[$name]))
      {
         // retrieve file path
         $cache  = array();
         $path   =& $this->map;
         $params = explode(".", $name);
         
         while (list($k, $val) = each($params))
         {
            if (!isset($path[$val])) throw new Exception(__METHOD__.': "'.$name.'" configuration not exist');
            
            $path =& $path[$val];
            $cache[] = $val;
             
            if (!is_array($path)) break;
         }
         
         if (!is_string($path)) throw new Exception(__METHOD__.': "'.$name.'" configuration not exist');
         
         // valid name
         $name = implode(".", $cache);
         
         // retrieve configuration
         if (!isset($this->cache[$name]))
         {
            $this->cache[$name] = Utility::loadArrayFromFile($path, $options);
         }
      }
      
      if ($key !== false)
      {
         return isset($this->cache[$name][$key]) ? $this->cache[$name][$key] : array();
      }
      
      return $this->cache[$name];
   }
   
   /**
    * Get internal configuration by kind
    * [ 
    *   db_map
    *   catalogs.fields,   type[catalog]
    *   catalogs.tabulars.fields,   type[catalog]
    *   catalogs.<catalog_type>.tabulars.fields,   type[tabular]
    * ]      
    * @param $kind - entity kind
    * @param $type - entity type
    * @return array
    */
   public function getInternalConfigurationByKind($kind, $type = false, array& $options = array())
   {
      if (!isset($this->cache_by_kind[$kind]))
      {
         // retrieve configuration name
         $name = null;
         $key  = null;
         $pars = explode(".", $kind);
         
         switch (count($pars))
         {
            case 1:
               $name = $pars[0];
               break;
            case 2:
               $name = $pars[0].'.'.$pars[1];
               break;
            case 3:
               $name = $pars[0].'.'.$pars[1].'.'.$pars[2];
               break;
            case 4:
               $name = $pars[0].'.'.$pars[2].'.'.$pars[3];
               $key  = $pars[1];
               break;
            
            default:
               throw new Exception(__METHOD__.': unknow configuration "'.$kind.'"');
         }
         
         // retrieve configuration
         if (!isset($this->cache[$name]))
         {
            $this->getInternalConfiguration($name, null, $options);
         }
         
         // cache
         if ($key)
         {
            if (isset($this->cache[$name][$key]))
            {
               $this->cache_by_kind[$kind] =& $this->cache[$name][$key];
            }
            else $this->cache_by_kind[$kind] = array();
         }
         else $this->cache_by_kind[$kind] =& $this->cache[$name]; 
      }
      
      if ($type !== false)
      {
         return isset($this->cache_by_kind[$kind][$type]) ? $this->cache_by_kind[$kind][$type] : array();
      }
      
      return $this->cache_by_kind[$kind];
   }
   
   
   
   
   /**
    * Get configuration by name from container configuration
    * 
    * @param string $confname
    * @param array& $options
    * @return array
    */
   protected function getFromContainerConfiguration($confname, array& $options = array())
   {
      if (!isset($this->_container))
      {
         $this->_container = Utility::loadArrayFromFile($this->map['container'], $options);
      }

      return isset($this->_container[$confname]) && is_array($this->_container[$confname]) ? $this->_container[$confname] : array();
   }

   /**
    * Get Event configuration
    * 
    * @param array& $options
    * @return array
    */
   public function getEventConfiguration(array& $options = array())
   {
      return $this->getFromContainerConfiguration('event', $options);
   }
   
   /**
    * Get Modules configuration
    * 
    * @param array& $options
    * @return array
    */
   public function getModulesConfiguration(array& $options = array())
   {
      return $this->getFromContainerConfiguration('modules', $options);
   }
   
   /**
    * Get Validator configuration
    * 
    * @param array& $options
    * @return array
    */
   public function getValidatorConfiguration(array& $options = array())
   {
      return $this->getFromContainerConfiguration('validator', $options);
   }
   
   /**
    * Get Pager configuration
    * 
    * @param array& $options
    * @return array
    */
   public function getPagerConfiguration(array& $options = array())
   {
      return $this->getFromContainerConfiguration('pager', $options);
   }
   
   /**
    * Get Request configuration
    * 
    * @param array& $options
    * @return array
    */
   public function getRequestConfiguration(array& $options = array())
   {
      return $this->getFromContainerConfiguration('request', $options);
   }
   
   /**
    * Get Response configuration
    * 
    * @param array& $options
    * @return array
    */
   public function getResponseConfiguration(array& $options = array())
   {
      return $this->getFromContainerConfiguration('response', $options);
   }
   
   /**
    * Get Upload configuration
    * 
    * @param array& $options
    * @return array
    */
   public function getUploadConfiguration(array& $options = array())
   {
      return $this->getFromContainerConfiguration('upload', $options);
   }
}
