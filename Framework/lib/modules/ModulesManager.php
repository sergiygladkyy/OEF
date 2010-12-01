<?php

class ModulesManager
{
   protected static $EVENTS = array(
      'catalogs' => array(
         'onGenerateCode',
         'onBeforeAddingRecord'
      ),
      'documents' => array(
         'onPost',
         'onUnpost',
         'onBeforeAddingRecord'
      ),
      'reports' => array(
         'onGenerate',
         'onDecode'
      ),
      'data_processors'     => array('onImport'),
      'information_regisry' => array('onBeforeAddingRecord')
   );
   
   protected static $modules_dir = null;
   protected static $cache_dir   = null;
   protected static $instance    = null;
   
   protected $map = array();
   
   /**
    * Get this object
    * 
    * @param array $options
    * @return object
    */
   public static function getInstance(array $options = array())
   {
      if(is_null(self::$instance))
      {
         self::$instance = new self($options);
      }

      return self::$instance;
   }
   
   protected function __construct(array& $options = array())
   {
      self::$modules_dir = isset($options['modules_dir']) ? $options['modules_dir'] : 'modules/';
      self::$cache_dir   = isset($options['cache_dir'])   ? $options['cache_dir']   : 'cache/';
      
      $this->container = Container::getInstance($options);
      $CManager  = $this->container->getConfigManager($options);
      try {
         $this->map = $CManager->getInternalConfiguration('modules');
      }
      catch(Exception $e)
      {
         $this->map = array();
      }
   }
   
   /**
    * Create empty modules if not exists
    * 
    * @throws Exception
    * @param mixed $kinds - string or array
    * @param array& $options
    * @return array - map for internal configuration
    */
   public function createModules($kinds, array& $options = array())
   {
      if (!is_array($kinds)) $kinds = array($kinds);
      
      $CManager = $this->container->getConfigManager($options);
      
      foreach ($kinds as $kind)
      {
         $entities = $CManager->getInternalConfiguration($kind.'.'.$kind);
         $basepath = self::$modules_dir.$kind.'/';
         $method   = 'generate'.str_replace(' ', '', ucwords(str_replace('_', ' ', $kind))).'Content';
         
         $this->map[$kind] = array();
      
         if (!method_exists($this, $method)) $method = false;
         
         // Create modules
         foreach ($entities as $type)
         {
            $dir  = $basepath.$type.'/';
            $file = $dir.'module.php';
             
            if (!file_exists($file))
            {
               if (!is_dir($dir))
               {
                  if (!mkdir($dir, 0755, true)) throw new Exception(__METHOD__.': Can\'t create dir "'.$dir.'"');
               }
            //}   

            $content = $method ? $this->$method($type, $options) : '';
            
            file_put_contents($file, $content);
            }
            $this->map[$kind][$type] = $file;
         }
      }

      return array('modules' => $this->map);
   }
   
   /**
    * Remove all modules
    * 
    * @param array& $options
    * @return array - errors
    */
   public function removeModules(array& $options = array())
   {
      $errors = array();
      
      foreach ($this->map as $kind => $map)
      {
         foreach ($map as $type => $file)
         {
            if (file_exists($file) && filesize($file) == 0)
            {
               if (!unlink($file)) $errors[] = 'Can\'t delete module file "'.$file.'"';
            }
         }
      }
      
      return $errors;
   }
   
   /**
    * Load modules by kind
    * 
    * @param string $kind
    * @param array $options
    * @return array - errors
    */
   public function loadModules($kind, array $options = array())
   {
      $errors = array();
      
      if (!isset($this->map[$kind])) throw new Exception(__METHOD__.': Unknow kind "'.$kind.'"');
      
      foreach ($this->map[$kind] as $type => $file)
      {
         if (!$this->loadModule($kind, $type)) $errors[] = 'Module for "'.$kind.'.'.$type.'" not loaded';
      }
      
      return $errors;
   }
   
   /**
    * Load module
    * 
    * @param string $kind
    * @param string $type
    * @return boolean
    */
   protected function loadModule($kind, $type)
   {
      // Check cache
      $cache = self::$cache_dir.$kind.'/'.$type.'/';
      $fname = $cache.'module.php';
      
      if (file_exists($fname))
      {
         require_once($fname);
         return true;
      }
      
      $events = isset(self::$EVENTS[$kind]) ? self::$EVENTS[$kind] : array();
      
      // Check module template
      if (empty($this->map[$kind][$type])) return false;
      
      $file = $this->map[$kind][$type];
      
      if (!file_exists($file)) return false;
      
      // Generate module
      $code = '';
      if ($content = file_get_contents($file))
      {
         $pattern[] = '/^[\s]*<\?[\S]*[\s]*/i';
         $pattern[] = '/[\s]*\?>[\s]*$/i';
         $content = preg_replace($pattern, array('', ''), $content);
         
         $pattern = '/(?<=[\s\n]|)function[\s]+([A-Za-z_][A-Za-z0-9_]*)(?=[\s]*?\([^)]*\)[\s\n]*{)/s';
         $res = preg_match_all($pattern, $content, $matches);
         
         if ($res)
         {
            if (!($content = preg_replace('/(?<=[\s\n]|)function(?=[\s]+[A-Za-z_][A-Za-z0-9_]*[\s]*?\([^)]*\)[\s\n]*{)/s', 'public static function', $content)))
            {
               return false;
            }
            
            $classname = ucfirst($kind).ucfirst($type);
            
            foreach ($events as $event)
            {
               if (in_array($event, $matches[1]))
               {
                  $code .= "\$dispatcher->connect('".$kind.'.'.$type.'.'.$event."', array('".$classname."', '".$event."'));\n\n";
               }
            }
            
            if (!empty($code))
            {
               $code = "<?php\n\n\$dispatcher = Container::getInstance()->getEventDispatcher();\n\n".$code;
            }
            else $code = "<?php\n\n";
            
            $code .= "class ".$classname."\n{\n   ".str_replace("\n", "\n   ", $content)."\n}";
            
         }
         elseif ($res === false)
         {
            return false;
         }
      }
      
      // Save in cache 
      if (!is_dir($cache))
      {
         if (!mkdir($cache, 0755, true)) throw new Exception(__METHOD__.': Can\'t create dir "'.$cache.'"');
      }
      
      file_put_contents($fname, $code);
      
      require_once($fname);
      
      return true;
   }
   
   /**
    * Clear cache
    * 
    * @param $options
    * @return boolean
    */
   public function clearCache($dir = null)
   {
      $ret = true;
      
      if (is_null($dir)) $dir = self::$cache_dir;
      
      if (is_dir($dir) && $handle = opendir($dir))
      {
         while (false !== ($file = readdir($handle)))
         { 
            if ($file != "." && $file != ".." && $file != ".svn")
            { 
               if (is_dir($dir.$file))
               {
                  $ret = ($ret && $this->clearCache($dir.$file.'/'));
               }
               else $ret = ($ret && unlink($dir.$file));
            } 
         }
         closedir($handle);
         
         if ($ret) rmdir($dir);
      }
      
      return $ret;
   }
   
   /**
    * Load global modules
    * 
    * @throws Exception
    * @return boolean
    */
   public function loadGlobalModules()
   {
      $cache = self::$cache_dir;
      $fname = 'global.php';

      if (!file_exists($cache.$fname))
      {
         if (!is_dir($cache))
         {
            if (!mkdir($cache, 0755, true)) throw new Exception(__METHOD__.': Can\'t create dir "'.$cache.'"');
         }

         if (file_exists(self::$modules_dir.$fname))
         {
             if (!copy(self::$modules_dir.$fname, $cache.$fname) && !file_exists($cache.$fname))
             {
                throw new Exception(__METHOD__.': Can\'t load global module "'.$fname.'"');
             }
         }
         else file_put_contents($cache.$fname, '');
      }

      require_once($cache.$fname);

      return true;
   }
   
   /**
    * Generate content for Web-service module
    * 
    * @param string $type
    * @param array& $options
    * @return string
    */
   protected function generateWebServicesContent($type, array& $options = array())
   {
      $CManager = Container::getInstance()->getConfigManager($options);
      $actions  = $CManager->getInternalConfiguration('web_services.actions.actions', $type);
      $content  = '';
      
      foreach ($actions as $action)
      {
         $content .= <<<CONTENT
/**
 * Web-service action "$action"
 * 
 * @param string \$attributes
 * @return array
 */
function {$action}(array \$attributes)
{
   return array();
}

CONTENT;
;
      }
      
      return "<?php\n".$content.'?>';
   }
}
