<?php

class ModulesManager
{
   protected static
      $model_events = array(
         'catalogs' => array(
            'onGenerateCode',
            'onBeforeAddingRecord',
            'onInputOnBasis',
            'onPrint'
         ),
         'documents' => array(
            'onPost',
            'onUnpost',
            'onGenerateCode',
            'onBeforeAddingRecord',
            'onInputOnBasis',
            'onPrint'
         ),
         'reports' => array(
            'onGenerate',
            'onDecode',
            'onPrint'
         ),
         'data_processors' => array('onImport'),
         'information_registry'  => array('onBeforeAddingRecord', 'onPrint'),
         'AccumulationRegisters' => array('onBeforeAddingRecord', 'onPrint')
      ),
      $forms_events = array(
         'catalogs'  => array('onGenerate', 'onProcess', 'onFormUpdateRequest', 'onBeforeOpening'),
         'documents' => array('onGenerate', 'onProcess', 'onFormUpdateRequest', 'onBeforeOpening'),
         'reports'   => array('onGenerate', 'onProcess', 'onFormUpdateRequest', 'onBeforeOpening'),
         'data_processors' => array('onGenerate', 'onProcess', 'onFormUpdateRequest', 'onBeforeOpening'),
         'information_registry'  => array('onGenerate', 'onProcess', 'onFormUpdateRequest', 'onBeforeOpening'),
         'AccumulationRegisters' => array('onGenerate', 'onProcess', 'onFormUpdateRequest', 'onBeforeOpening')
      );
   
   protected static
      $object_types = array('catalogs', 'documents'),
      $instance     = null,
      $modules_dir  = null,
      $cache_dir    = null,
      $template_dir = null,
      $layout_dir = null;
   
   protected
      $container = null, 
      $map = array();
   
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
   
   /**
    * Constructor
    * 
    * @param array& $options
    * @return void
    */
   protected function __construct(array& $options = array())
   {
      self::$modules_dir  = isset($options['modules_dir'])  ? $options['modules_dir']  : 'modules/';
      self::$cache_dir    = isset($options['cache_dir'])    ? $options['cache_dir']    : 'cache/';
      self::$template_dir = isset($options['template_dir']) ? $options['template_dir'] : 'templates/';
      self::$layout_dir   = isset($options['layout_dir'])   ? $options['layout_dir']   : 'layout/';
      
      $this->container = Container::getInstance($options);
      $CManager = $this->container->getConfigManager($options);
      
      try
      {
         $this->map = $CManager->getInternalConfiguration('modules');
      }
      catch(Exception $e)
      {
         $this->map = array();
      }
   }
   
   /**
    * Create all modules with templates
    * 
    * @throws Exception
    * @param mixed $kinds - string or array
    * @param array& $options
    * @return array - map for internal configuration
    */
   public function createModules($kinds, array& $options = array())
   {
      if (!is_array($kinds)) $kinds = array($kinds);
      
      if (false !== ($key = array_search('Constants', $kinds)))
      {
         $this->map = $this->createConstantsModule($options);
         unset($kinds[$key]);
      }
      
      $this->map = array_merge_recursive($this->map, $this->createEntitiesModules($kinds, $options));
      
      if (false !== ($key = array_search('web_services', $kinds)))
      {
         unset($kinds[$key]);
      }
      
      $this->map = array_merge_recursive($this->map, $this->createFormsModules($kinds, $options));
      $this->map = array_merge_recursive($this->map, $this->createTemplates($kinds, $options));
      $this->map = array_merge_recursive($this->map, $this->createLayout($kinds, $options));
      
      return array('modules' => $this->map);
   }
   
   /**
    * Create empty entities modules if not exists
    * 
    * @throws Exception
    * @param array $kinds
    * @param array& $options
    * @return array - map
    */
   public function createEntitiesModules(array $kinds, array& $options = array())
   {
      $CManager = $this->container->getConfigManager($options);
      
      $map = array();
      
      foreach ($kinds as $kind)
      {
         $entities = $CManager->getInternalConfiguration($kind.'.'.$kind);
         $basepath = self::$modules_dir.$kind.'/';
         $method   = 'generate'.str_replace(' ', '', ucwords(str_replace('_', ' ', $kind))).'Content';
         
         $map[$kind] = array();
      
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
            $map[$kind][$type]['model']['module'] = $file;
         }
      }

      return $map;
   }
   
   /**
    * Create empty forms modules
    * 
    * @throws Exception
    * @param array $kinds
    * @param array& $options
    * @return array - modules map
    */
   protected function createFormsModules(array $kinds, array& $options = array())
   {
      $CManager = $this->container->getConfigManager($options);
      
      $map = array();
      
      foreach ($kinds as $kind)
      {
         $entities = $CManager->getInternalConfiguration($kind.'.'.$kind);
         $forms    = $CManager->getInternalConfiguration($kind.'.forms');
         $basepath = self::$modules_dir.$kind.'/';
         
         $map[$kind] = array();
         
         // Create modules
         foreach ($entities as $type)
         {
            $dir = $basepath.$type.'/forms/';
            
            if (!is_dir($dir))
            {
               if (!mkdir($dir, 0755, true)) throw new Exception(__METHOD__.': Can\'t create dir "'.$dir.'"');
            }
            
            foreach ($forms[$type] as $name)
            {
               $file = $dir.$name.'.php';
                
               if (!file_exists($file))
               {
                  file_put_contents($file, '');
               }
               
               $map[$kind][$type]['forms'][$name] = $file;
            }
         }
      }
      
      return $map;
   }
   
   /**
    * Create empty templates
    * 
    * @throws Exception
    * @param array $kinds
    * @param array& $options
    * @return array - modules map
    */
   protected function createTemplates(array $kinds, array& $options = array())
   {
      if (!is_array($kinds)) $kinds = array($kinds);
      
      $CManager = $this->container->getConfigManager($options);
      
      $map = array();
      
      foreach ($kinds as $kind)
      {
         $entities  = $CManager->getInternalConfiguration($kind.'.'.$kind);
         $templates = $CManager->getInternalConfiguration($kind.'.templates');
         $basepath  = self::$template_dir.$kind.'/';
         
         $map[$kind] = array();
         
         // Create templates
         foreach ($entities as $type)
         {
            $dir = $basepath.$type.'/';
            
            if (!is_dir($dir))
            {
               if (!mkdir($dir, 0755, true)) throw new Exception(__METHOD__.': Can\'t create dir "'.$dir.'"');
            }
            
            foreach ($templates[$type] as $name)
            {
               $file = $dir.$name.'.php';
                
               if (!file_exists($file))
               {
                  file_put_contents($file, '');
               }
               
               $map[$kind][$type]['templates'][$name] = $file;
            }
         }
      }

      return $map;
   }
   
   /**
    * Create empty layouts
    * 
    * @throws Exception
    * @param array $kinds
    * @param array& $options
    * @return array - modules map
    */
   protected function createLayout(array $kinds, array& $options = array())
   {
      if (!is_array($kinds)) $kinds = array($kinds);
      
      $CManager = $this->container->getConfigManager($options);
      
      $map = array();
      
      foreach ($kinds as $kind)
      {
         $entities  = $CManager->getInternalConfiguration($kind.'.'.$kind);
         $templates = $CManager->getInternalConfiguration($kind.'.layout');
         $basepath  = self::$layout_dir.$kind.'/';
         
         $map[$kind] = array();
         
         // Create templates
         foreach ($entities as $type)
         {
            $dir = $basepath.$type.'/';
            
            if (!is_dir($dir))
            {
               if (!mkdir($dir, 0755, true)) throw new Exception(__METHOD__.': Can\'t create dir "'.$dir.'"');
            }
            
            foreach ($templates[$type] as $name)
            {
               $file = $dir.$name.'.php';
                
               if (!file_exists($file))
               {
                  file_put_contents($file, '');
               }
               
               $map[$kind][$type]['layout'][$name] = $file;
            }
         }
      }

      return $map;
   }
   
   /**
    * Create empty constants module
    * 
    * @throws Exception
    * @param array& $options
    * @return array - modules map
    */
   protected function createConstantsModule(array& $options = array())
   {
      $CManager = $this->container->getConfigManager($options);
      
      $kind = 'Constants';
      $type = $kind;
      $map  = array();
      $dir  = self::$modules_dir.$kind.'/';
      
      if (!is_dir($dir))
      {
         if (!mkdir($dir, 0755, true)) throw new Exception(__METHOD__.': Can\'t create dir "'.$dir.'"');
      }

      $file = $dir.'module.php';

      if (!file_exists($file))
      {
         file_put_contents($file, '');
      }
       
      $map[$kind][$type]['model']['module'] = $file;

      return $map;
   }
   
   /**
    * Remove all modules with templates
    * 
    * @param array& $options
    * @return array - errors
    */
   public function removeModules(array& $options = array())
   {
      $errors = array();
      
      foreach ($this->map as $kind => $map)
      {
         foreach ($map as $type => $modules)
         {
            foreach ($modules as $module_type => $paths)
            {
               foreach ($paths as $name => $file)
               {
                  if (file_exists($file) && filesize($file) == 0)
                  {
                     if (!unlink($file)) $errors[] = 'Can\'t delete '.$module_type.' file "'.$file.'"';
                  }
               }
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
      
      if ($kind == 'Constants')
      {
         if (!$this->loadConstantModule())
         {
            $errors[] = 'Constants module not loaded';
         }
         
         return $errors;
      }
      
      foreach ($this->map[$kind] as $type => $modules)
      {
         foreach ($modules as $module_type => $paths)
         {
            if ($module_type == 'templates' || $module_type == 'layout') continue;
            
            foreach ($paths as $name => $path)
            {
               if (!$this->loadModule($kind, $type, $module_type, $name))
               {
                  $errors[] = 'Module '.$name.' for '.$kind.'.'.$type.' not loaded';
               }
            }
         }
      }
      
      return $errors;
   }
   
   /**
    * Load module
    * 
    * @param string $kind
    * @param string $type
    * @param string $module_type
    * @param string $module_name
    * @return boolean
    */
   protected function loadModule($kind, $type, $module_type, $module_name)
   {
      // Check cache
      $cache = self::$cache_dir.$kind.'/'.$type.'/'.$module_type.'/';
      $fname = $cache.$module_name.'.php';
      
      if (file_exists($fname))
      {
         require_once($fname);
         return true;
      }
      
      $ev_inf = $module_type.'_events';
      $events  = isset(self::${$ev_inf}[$kind]) ? self::${$ev_inf}[$kind] : array();
      
      // Check module template
      if (empty($this->map[$kind][$type][$module_type][$module_name])) return false;
      
      $file = $this->map[$kind][$type][$module_type][$module_name];
      
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
         
            $classname = ucfirst($kind).ucfirst($type).ucfirst($module_type).ucfirst($module_name);
            
            foreach ($events as $event)
            {
               if (in_array($event, $matches[1]))
               {
                  $event_id = ($module_type == 'forms') ? $module_type.'.'.$module_name : $module_type;
                  
                  $code .= "\$dispatcher->connect('".$kind.'.'.$type.'.'.$event_id.'.'.$event."', array('".$classname."', '".$event."'));\n\n";
               }
            }
            
            if ($module_type == 'forms')
            {
               $code .= "\$dispatcher->connect('".$kind.'.'.$type.'.'.$module_type.'.'.$module_name.".onCustomEvent', array('".$classname."', 'onCustomEvent'));\n\n";
            }
            
            // Add tabular events
            if (in_array($kind, self::$object_types))
            {
               $CManager = $this->container->getConfigManager();
      
               $tabulars = $CManager->getInternalConfiguration($kind.'.tabulars.tabulars', $type);
               
               foreach ($tabulars as $ttype)
               {
                  $func = 'onBeforeAdding'.$ttype.'Record';
                  
                  if (in_array($func, $matches[1]))
                  {
                     $code .= "\$dispatcher->connect('".$kind.'.'.$type.'.tabulars.'.$ttype.".model.onBeforeAddingRecord', array('".$classname."', '".$func."'));\n\n";
                  }
               }
            }
            
            if (!empty($code))
            {
               $code = "<?php\n\n\$dispatcher = Container::getInstance()->getEventDispatcher();\n\n".$code;
            }
            else $code = "<?php\n\n";
            
            $code .= "class ".$classname."\n{\n   ";
            $code .= "protected static \$templates_dir = '".self::$template_dir.$kind.'/'.$type."/';\n   \n   ";
            $code .= "protected static \$layout_dir    = '".self::$layout_dir.  $kind.'/'.$type."/';\n   \n   ";
            
            if ($module_type == 'forms' && !in_array('onCustomEvent', $matches[1]))
            {
               $code .= <<<OnCustomEvent
/**
    * Process custom event
    * 
    * @param object \$event
    * @return void
    */
   public static function onCustomEvent(\$event)
   {
      \$params = \$event['parameters'];
      
      if (!isset(\$params['eventName']) || !is_callable('self', \$params['eventName']))
      {
         return;
      }
      
      call_user_func(array('self', \$params['eventName']), \$event);
   }
   
   
OnCustomEvent;
;
            }
            
            $code .= str_replace("\n", "\n   ", $content)."\n}";
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
   
   
   /**
    * Load Constants module
    * 
    * @return array - errors
    */
   protected function loadConstantModule(array $options = array())
   {
      $kind = 'Constants';
      $type = $kind;
      $module_type = 'model';
      $module_name = 'module';
      
      // Check cache
      $cache = self::$cache_dir.$kind.'/'.$module_type.'/';
      $fname = $cache.$module_name.'.php';
      
      if (file_exists($fname))
      {
         require_once($fname);
         return true;
      }
      
      $CManager = $this->container->getConfigManager($options);
      
      $fields = $CManager->getInternalConfiguration($kind.'.fields');
      $events = array();
      
      foreach ($fields as $field)
      {
         $events[] = 'onUpdate'.$field;
      }
      
      // Check module template
      if (empty($this->map[$kind][$type][$module_type][$module_name])) return false;
      
      $file = $this->map[$kind][$type][$module_type][$module_name];
      
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
         
            $classname = ucfirst($kind).ucfirst($type).ucfirst($module_type).ucfirst($module_name);
            
            foreach ($events as $event)
            {
               if (in_array($event, $matches[1]))
               {
                  $event_id = ($module_type == 'forms') ? $module_type.'.'.$module_name : $module_type;
                  
                  $code .= "\$dispatcher->connect('".$kind.'.'.$event_id.'.'.$event."', array('".$classname."', '".$event."'));\n\n";
               }
            }
            
            if (!empty($code))
            {
               $code = "<?php\n\n\$dispatcher = Container::getInstance()->getEventDispatcher();\n\n".$code;
            }
            else $code = "<?php\n\n";
            
            $code .= "class ".$classname."\n{\n   ";
            //$code .= "protected static \$templates_dir = '".self::$template_dir.$kind.'/'.$type."/';\n   \n   ";
            $code .= str_replace("\n", "\n   ", $content)."\n}";
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
}
