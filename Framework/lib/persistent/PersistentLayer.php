<?php

class PersistentLayer
{
   const m_config_dir    = 'config/';   
   const p_config_dir    = 'lib/persistent/config/';
   const i_config_dir    = 'config/internal/';
   const dictionary_path = 'config/dictionary.php';
   const config_map_path = 'config/internal/config_map.php';
   
   protected static $instance = null;

   protected
      $applied_solution_name = null,
      $applied_solution_dir  = null,
	  $config_dir      = null,
	  $dictionary_path = null,
      $config_map_path = null,
      $dictionary      = array();
   
   /**
    * Get PersistentLayer object
    *  
    * @param array $options
    * @return object PersistentLayer
    */
   public static function getInstance(array $options = array())
   {
      if(is_null(self::$instance))
      {
         self::$instance = new PersistentLayer($options);
      }

      return self::$instance;
   }

   /**
    * Constructor
    * 
    * @param array& $options
    * @return void
    */
   protected function __construct(array & $options = array())
   {
      $this->applied_solution_dir  = isset($options['AppliedSolutionDir'])  ? (string) $options['AppliedSolutionDir']  : null;
      $this->applied_solution_name = isset($options['AppliedSolutionName']) ? (string) $options['AppliedSolutionName'] : null;
	  
	  $this->initialize();
   }
   
   /**
    * Set current Applied Solution Name
    * 
    * @param string $name
    * @return void
    */
   public function setAppliedSolutionDir($dir)
   {
      $this->applied_solution_dir = (string) $dir;
	  
	  $this->initialize();
   }
   
   /**
    * Set current Applied Solution Name
    * 
    * @param string $name
    * @return void
    */
   public function setAppliedSolutionName($name)
   {
      $this->applied_solution_name = (string) $name;
	  
	  $this->initialize();
   }
   
   
   /**
    * Initialize
	*
	* @param array& $options
	* @return void
	*/
   protected function initialize(array& $options = array())
   {
      $base = $this->applied_solution_dir  ? $this->applied_solution_dir.'/'  : '';
      $apps = $this->applied_solution_name ? $this->applied_solution_name.'/' : '';
	  
	  $this->config_dir      = $base.$apps.self::i_config_dir;
	  $this->dictionary_path = $base.$apps.self::dictionary_path;
      $this->config_map_path = $base.$apps.self::config_map_path;
   }
   
   
   /**
    * Load dictionary
    * 
    * @param array $options
    * @return 
    */
   protected function loadDictionary(array& $options = array())
   {
      $this->dictionary = $this->loadConfigFromFile($this->dictionary_path);
      
      return $this->checkDictionary($this->dictionary, $options);
   }
   
   /**
    * Check dictionary file
    * 
    * @param array $config - !!! метод вносит изменения в передаваемый массив
    * @param array $options
    * @return array - errors
    */
   protected function checkDictionary(array& $config, array& $options = array())
   {
      $errors   = array();
      $valid    = array();
      $sections = array(
         'catalogs',
         'documents',
         'information_registry',
         'reports',
         'data_processors',
         'web_services',
         'security'
      );
      
      foreach ($sections as $kind)
      {
         $name = str_replace('_', ' ', ucfirst($kind));

         if (!isset($config[$kind]))
         {
            if ($kind == 'catalogs')
            {
               $errors['global'][] = $name.' configuration is wrong';
            }
            else $valid[$kind] = array();
         }
         elseif (!is_array($config[$kind]))
         {
            $errors['global'][] = $name.' configuration is wrong';
         }
         elseif (empty($config[$kind]))
         {
            $errors['global'] = $name.' configuration is empty';
         }
         else
         {
            $func = 'check'.str_replace(' ', '', ucwords(str_replace('_', ' ', $kind))).'Config';
             
            if (!$err = $this->$func($config[$kind]))
            {
               $valid[$kind] = $config[$kind];
            }
            else $errors[$kind] = $err;
         }
      }
      
      $config = $valid;
      unset($valid);
      
      return $errors;
   }
   
   /**
    * Check catalogs configuration array
    * 
    * @param array& $config - !!! метод вносит изменения в передаваемый массив
    * @return array - errors
    */
   protected function checkCatalogsConfig(array& $config)
   {
      $errors = array();
      $valid  = array();
      
      foreach ($config as $catalog => $conf)
      {
         if (!$this->checkName($catalog))
         {
            $errors[$catalog]['global'][] = 'Invalid name';
         }
         
         /* Check fields config */
         
         if (!isset($conf['fields']))
         {
            $valid[$catalog]['fields'] = array();
         }
         elseif (!is_array($conf['fields']))
         {
            $errors[$catalog]['global'][] = 'Fields configuration for catalog "'.$catalog.'" is wrong';
         }
         elseif (empty($conf['fields']))
         {
            $errors[$catalog]['global'][] = 'Fields configuration for catalog "'.$catalog.'" is empty';
         }
         else
         {
            if (!$err = $this->checkFieldsConfig('catalogs', $conf['fields']))
            {
               $valid[$catalog]['fields'] = $conf['fields'];
            }
            else $errors[$catalog]['fields'] = $err;
         }
         
         
         /* Check tabular sections config */
         
         if (!isset($conf['tabular_sections']))
         {
            $valid[$catalog]['tabular_sections'] = array();
         }
         elseif(!is_array($conf['tabular_sections']))
         {
            $errors[$catalog]['global'][] = 'Tabular sections configuration for catalog "'.$catalog.'" is wrong';
         }
         elseif (empty($conf['tabular_sections']))
         {
            $errors[$catalog]['global'][] = 'Tabular sections configuration for catalog "'.$catalog.'" is empty';
         }
         else
         {
            if (!$err = $this->checkTabularSectionsConfig($conf['tabular_sections']))
            {
               $valid[$catalog]['tabular_sections'] = $conf['tabular_sections'];
            }
            else $errors[$catalog]['tabular_sections'] = $err;
         }
         
         
         /* Check common config */
          
         if ($err = $this->checkCommonConfig('catalogs', $catalog, $conf))
         {
            $errors = isset($errors[$catalog]) && is_array($errors[$catalog]) ? array_merge($errors[$catalog], $err) : $err;
         }
         else
         {
            $valid[$catalog]['model'] = $conf['model'];
            $valid[$catalog]['controller'] = $conf['controller'];
         }
      }
      
      $config = $valid;
      unset($valid);
      
      return $errors;
   }
   
   /**
    * Check information registry configuration array
    * 
    * @param array& $config - !!! метод вносит изменения в передаваемый массив
    * @return array - errors
    */
   protected function checkInformationRegistryConfig(array& $config)
   {
      $errors = array();
      $valid  = array();
      
      foreach ($config as $registry => $conf)
      {
         $check_duplicate  = false;
         $check_periodical = false;
      
         if (!$this->checkName($registry))
         {
            $errors[$registry]['global'][] = 'Invalid name';
         }
         
         /* Check dimensions config */
         
         if (!isset($conf['dimensions']))
         {
            $valid[$registry]['dimensions'] = array();
         }
         elseif (!is_array($conf['dimensions']))
         {
            $errors[$registry]['global'][] = 'Dimensions configuration for information registry "'.$registry.'" is wrong';
         }
         elseif (empty($conf['dimensions']))
         {
            $errors[$registry]['global'][] = 'Dimensions configuration for information registry "'.$registry.'" is empty';
         }
         else
         {
            $check_duplicate = true;
            
            if (!$err = $this->checkFieldsConfig('information_registry', $conf['dimensions']))
            {
               $valid[$registry]['dimensions'] = $conf['dimensions'];
            }
            else $errors[$registry]['dimensions'] = $err;
         }
      
         /* Check periodical config */
         
         if (isset($conf['periodical']))
         {
            if (!is_string($conf['periodical']) || !in_array($conf['periodical'], $this->getAllowedPeriods()))
            {
               $errors[$registry]['global'][] = '"Periodical" configuration for information registry "'.$registry.'" is wrong';
            }
            else
            {
               $check_periodical = true;
               
               $valid[$registry]['periodical'] = $conf['periodical'];
            }
         }
         /*elseif (!isset($conf['dimensions']))
         {
            $errors[$registry]['global'][] = 'Configuration for information registry "'.$registry.'" is wrong. You must specify the dimensions and/or periodical.';
         }*/
         
         /* Check fields config */
          
         if (!isset($conf['fields']))
         {
            $valid[$registry]['fields'] = array();
         }
         elseif (!is_array($conf['fields']))
         {
            $errors[$registry]['global'][] = 'Fields configuration for information registry "'.$registry.'" is wrong';
         }
         elseif (empty($conf['fields']))
         {
            $errors[$registry]['global'][] = 'Fields configuration for information registry "'.$registry.'" is empty';
         }
         else
         {
            $duplicate = false;

            if ($check_duplicate && $duplicate = array_intersect_key($conf['dimensions'], $conf['fields']))
            {
               $duplicate = true;
               $errors[$registry]['global'][] = 'Dimensions and Fields use one namespace. Duplicate name in information registry "'.$registry.'": '.implode(", ", array_keys($duplicate)).'.';
            }

            if ($check_periodical && isset($conf['fields']['period']))
            {
               $duplicate = true;
               $errors[$registry]['global'][] = '"period" - is system field name. Rename your field "period".';
            }
            if (!$duplicate)
            {
               if (!$err = $this->checkFieldsConfig('information_registry', $conf['fields']))
               {
                  $valid[$registry]['fields'] = $conf['fields'];
               }
               else $errors[$registry]['fields'] = $err;
            }
         }
         
         /* Check Recorders config */
         
         if (!isset($conf['recorders']))
         {
            $valid[$registry]['recorders'] = array();
         }
         elseif (!is_array($conf['recorders']))
         {
            $errors[$registry]['global'][] = 'Recorders configuration for information registry "'.$registry.'" is wrong';
         }
         elseif (empty($conf['recorders']))
         {
            $errors[$registry]['global'][] = 'Recorders configuration for information registry "'.$registry.'" is empty';
         }
         else
         {
            if (!$err = $this->checkRecordesConfig($conf['recorders']))
            {
               $valid[$registry]['recorders'] = $conf['recorders'];
            }
            else $errors[$registry]['recorders'] = $err;
         }
         
         /* Check common config */
          
         if ($err = $this->checkCommonConfig('information_registry', $registry, $conf))
         {
            $errors = isset($errors[$registry]) && is_array($errors[$registry]) ? array_merge($errors[$registry], $err) : $err;
         }
         else
         {
            $valid[$registry]['model'] = $conf['model'];
            $valid[$registry]['controller'] = $conf['controller'];
         }
      }
      
      $config = $valid;
      unset($valid);
      
      return $errors;
   }
   
   /**
    * Check documents configuration array
    * 
    * @param array& $config - !!! метод вносит изменения в передаваемый массив
    * @return array - errors
    */
   protected function checkDocumentsConfig(array& $config)
   {
      $errors = array();
      $valid  = array();
      
      foreach ($config as $document => $conf)
      {
         if (!$this->checkName($document))
         {
            $errors[$document]['global'][] = 'Invalid name';
         }
         
         /* Check fields config */
         
         if (!isset($conf['fields']))
         {
            $valid[$document]['fields'] = array();
         }
         elseif (!is_array($conf['fields']))
         {
            $errors[$document]['global'][] = 'Fields configuration for document "'.$document.'" is wrong';
         }
         elseif (empty($conf['fields']))
         {
            $errors[$document]['global'][] = 'Fields configuration for document "'.$document.'" is empty';
         }
         else
         {
            if (!$err = $this->checkFieldsConfig('documents', $conf['fields']))
            {
               $valid[$document]['fields'] = $conf['fields'];
            }
            else $errors[$document]['fields'] = $err;
         }
         
         /* Check Recorder for configuration */
         
         if (!isset($conf['recorder_for']))
         {
            $valid[$document]['recorder_for'] = array();
         }
         elseif (!is_array($conf['recorder_for']))
         {
            $errors[$document]['global'][] = '"Recorder for" configuration for document "'.$document.'" is wrong';
         }
         elseif (empty($conf['recorder_for']))
         {
            $errors[$document]['global'][] = '"Recorder for" configuration for document "'.$document.'" is empty';
         }
         else
         {
            if (!$err = $this->checkRecordesConfig($conf['recorder_for']))
            {
               $valid[$document]['recorder_for'] = $conf['recorder_for'];
            }
            else $errors[$document]['recorder_for'] = $err;
         }
         
         
         /* Check tabular sections config */
          
         if (!isset($conf['tabular_sections']))
         {
            $valid[$document]['tabular_sections'] = array();
         }
         elseif(!is_array($conf['tabular_sections']))
         {
            $errors[$document]['global'][] = 'Tabular sections configuration for document "'.$document.'" is wrong';
         }
         elseif (empty($conf['tabular_sections']))
         {
            $errors[$document]['global'][] = 'Tabular sections configuration for document "'.$document.'" is empty';
         }
         else
         {
            if (!$err = $this->checkTabularSectionsConfig($conf['tabular_sections']))
            {
               $valid[$document]['tabular_sections'] = $conf['tabular_sections'];
            }
            else $errors[$document]['tabular_sections'] = $err;
         }
          
          
         /* Check common config */
          
         if ($err = $this->checkCommonConfig('documents', $document, $conf))
         {
            $errors = isset($errors[$document]) && is_array($errors[$document]) ? array_merge($errors[$document], $err) : $err;
         }
         else
         {
            $valid[$document]['model'] = $conf['model'];
            $valid[$document]['controller'] = $conf['controller'];
         }
      }
      
      $config = $valid;
      unset($valid);
      
      return $errors;
   }
   
   /**
    * Check Tabular Sections configuration
    * 
    * @param array& $config - !!! метод вносит изменения в передаваемый массив
    * @return array - errors
    */
   protected function checkTabularSectionsConfig(array& $config)
   {
      $errors = array();
      $valid  = array();

      foreach ($config as $name => $conf)
      {
         if (!$this->checkName($name))
         {
            $errors['global'][] = 'Invalid name "'.$name.'"';
         }
         
         /* Check fields config */
         
         if (!isset($conf['fields']) || !is_array($conf['fields']))
         {
            $errors['global'][] = 'Fields configuration for Tabular Sections "'.$name.'" is wrong';
         }
         elseif (empty($conf['fields']))
         {
            $errors['global'][] = 'Fields configuration for Tabular Sections "'.$name.'" is empty';
         }
         else
         {
            if (!$err = $this->checkFieldsConfig('tabular_sections', $conf['fields']))
            {
               $valid[$name]['fields'] = $conf['fields'];
            }
            else $errors[$name]['fields'] = $err;
         }
         
         /* Check common config */
          
         if ($err = $this->checkCommonConfig('tabular_sections', $name, $conf))
         {
            $errors = isset($errors[$name]) && is_array($errors[$name]) ? array_merge($errors[$name], $err) : $err;
         }
         else
         {
            $valid[$name]['model'] = $conf['model'];
            $valid[$name]['controller'] = $conf['controller'];
         }
      }

      $config = $valid;
      unset($valid);

      return $errors;
   }
   
   /**
    * Check Reports configuration
    * 
    * @param array& $config - !!! метод вносит изменения в передаваемый массив
    * @return array - errors
    */
   protected function checkReportsConfig(array& $config)
   {
      $errors = array();
      $valid  = array();

      foreach ($config as $name => $conf)
      {
         if (!$this->checkName($name))
         {
            $errors['global'][] = 'Invalid name "'.$name.'"';
         }
         
         /* Check fields config */
         
         if (!isset($conf['fields']) || !is_array($conf['fields']))
         {
            $errors['global'][] = 'Fields configuration for Reports "'.$name.'" is wrong';
         }
         elseif (empty($conf['fields']))
         {
            $errors['global'][] = 'Fields configuration for Reports "'.$name.'" is empty';
         }
         else
         {
            if (!$err = $this->checkFieldsConfig('reports', $conf['fields']))
            {
               $valid[$name]['fields'] = $conf['fields'];
            }
            else $errors[$name]['fields'] = $err;
         }
         
         /* Check common config */
          
         if ($err = $this->checkCommonConfig('reports', $name, $conf))
         {
            $errors = isset($errors[$name]) && is_array($errors[$name]) ? array_merge($errors[$name], $err) : $err;
         }
         else
         {
            $valid[$name]['model'] = $conf['model'];
            $valid[$name]['controller'] = $conf['controller'];
         }
      }

      $config = $valid;
      unset($valid);

      return $errors;
   }
   
   /**
    * Check Data Processors configuration
    * 
    * @param array& $config - !!! метод вносит изменения в передаваемый массив
    * @return array - errors
    */
   protected function checkDataProcessorsConfig(array& $config)
   {
      $errors = array();
      $valid  = array();

      foreach ($config as $type => $conf)
      {
         if (!$this->checkName($type))
         {
            $errors['global'][] = 'Invalid name "'.$type.'"';
         }
         
         /* Check fields config */
         
         if (!isset($conf['fields']) || !is_array($conf['fields']))
         {
            $errors['global'][] = 'Fields configuration for Data Processors "'.$type.'" is wrong';
         }
         elseif (empty($conf['fields']))
         {
            $errors['global'][] = 'Fields configuration for Data Processors "'.$type.'" is empty';
         }
         else
         {
            if (!$err = $this->checkFieldsConfig('data_processors', $conf['fields']))
            {
               $valid[$type]['fields'] = $conf['fields'];
            }
            else $errors[$type]['fields'] = $err;
         }
         
         /* Check common config */
          
         if ($err = $this->checkCommonConfig('data_processors', $type, $conf))
         {
            $errors = isset($errors[$type]) && is_array($errors[$type]) ? array_merge($errors[$type], $err) : $err;
         }
         else
         {
            $valid[$type]['model'] = $conf['model'];
            $valid[$type]['controller'] = $conf['controller'];
         }
      }

      $config = $valid;
      unset($valid);

      return $errors;
   }
   
   /**
    * Check Web services configuration
    * 
    * @param array& $config - !!! метод вносит изменения в передаваемый массив
    * @return array - errors
    */
   protected function checkWebServicesConfig(array& $config)
   {
      $errors = array();
      $valid  = array();

      foreach ($config as $name => $_conf)
      {
         if (!$this->checkName($name))
         {
            $errors['global'][] = 'Invalid Web-service name "'.$name.'"';
         }

         
         /* Check web service configuration */
         
         if (!is_array($_conf))
         {
            $errors[$name][] = 'Web-service "'.$name.'" configuration is wrong';
         }
         elseif (empty($_conf))
         {
            $errors[$name][] = 'Web-service "'.$name.'" configuration is empty';
         }
         elseif (!(isset($_conf['actions']) && is_array($_conf['actions'])))
         {
            $errors[$name][] = 'Actions configuration for Web-service "'.$name.'" is wrong';
         }
         elseif (empty($_conf['actions']))
         {
            $errors[$name][] = 'Actions configuration for Web-service "'.$name.'" is empty';
         }
         else
         {
            /* Check actions configuration */
             
            foreach ($_conf['actions'] as $action => $conf)
            {
               if (!$this->checkName($action))
               {
                  $errors[$name][] = 'Invalid action name "'.$action.'"';
               }
                
               /* Check fields config */
                
               if (!isset($conf['fields']))
               {
                  $valid[$name]['actions'][$action]['fields'] = array();
               }
               elseif (!is_array($conf['fields']))
               {
                  $errors[$name][] = 'Fields configuration for action "'.$action.'" is wrong';
               }
               elseif (empty($conf['fields']))
               {
                  $valid[$name]['actions'][$action]['fields'] = array();
               }
               else
               {
                  if (!$err = $this->checkFieldsConfig('web_services', $conf['fields']))
                  {
                     $valid[$name]['actions'][$action]['fields'] = $conf['fields'];
                  }
                  else $errors[$name]['actions'][$action]['fields'] = $err;
               }
            }
         }
         
         
         /* Check common config */

         if ($err = $this->checkCommonConfig('web_services', $name, $_conf))
         {
            $errors = isset($errors[$name]) && is_array($errors[$name]) ? array_merge($errors[$name], $err) : $err;
         }
         else
         {
            $valid[$name]['model'] = $_conf['model'];
            $valid[$name]['controller'] = $_conf['controller'];
         }
      }

      $config = $valid;
      unset($valid);

      return $errors;
   }
   
   /**
    * Check Security configuration
    * 
    * @param array& $config - !!! метод вносит изменения в передаваемый массив
    * @return array - errors
    */
   protected function checkSecurityConfig(array& $config)
   {
      ;
   }
   
   
   
   
   
   
   
   /**
    * Check fields configuration
    * 
    * @param string $kind - entity kind
    * @param array& $config - !!! метод вносит изменения в передаваемый массив
    * @return array - errors
    */
   protected function checkFieldsConfig($kind, array& $config)
   {
      $errors = array();
      $valid  = array();
      
      foreach ($config as $field => $conf)
      {
         if (!is_array($conf)) return array("'".$field."' field configuration is wrong");
         
         if (!$this->checkName($field))
         {
            $errors['global'][] = 'Invalid name';
         }
         
         // is link?
         if (isset($conf['reference']))
         {
            if (!$err = $this->checkReferenceConfig($conf))
            {
               $valid[$field] = $conf;
            }
            else $errors[$field] = $err;

            continue;
         }
         
         // is attribute
         if ($kind == 'catalogs' && $field == 'Code')
         {
            $valid[$field] = $conf;
            continue;
         }
         
         if (!$err = $this->checkAttributeConfig($kind, $conf))
         {
            $valid[$field] = $conf;
         }
         else $errors[$field] = $err;
      }
      
      $config = $valid;
      unset($valid);
      
      return $errors;
   }
   
   /**
    * Check reference configuration array
    * 
    * @param array& $config - !!! метод вносит изменения в передаваемый массив
    * @return array - errors
    */
   protected function checkReferenceConfig(array& $config)
   {
      $errors = array();
      $valid  = array();

      if (is_string($config['reference']))
      {
         $valid['reference'] = $config['reference'];
      }
      else $errors[] = 'Reference configuration is wrong';


      /* Precision */

      if (isset($config['precision']))
      {
         if (!is_array($config['precision']))
         {
            $errors[] = 'Precision configuration is wrong';
         }
         elseif (empty($config['precision']))
         {
            $errors[] = 'Precision configuration is empty';
         }
         else
         {
            if (!$err = $this->checkPrecisionConfig($config['precision'], 'reference'))
            {
               $valid['precision'] = $config['precision'];
            }
            else
            {
               $errors = array_merge($errors, $err);
            }
         }
      }

      $config = $valid;
      unset($valid);

      return $errors;
   }
   
   /**
    * Check attribute configuration array
    * 
    * @param string $kind - entity kind
    * @param array& $config - !!! метод вносит изменения в передаваемый массив
    * @return array - errors
    */
   protected function checkAttributeConfig($kind, array& $config)
   {
      $errors = array();
      $valid  = array();
      $allowed_types = $this->getAllowedInternalTypes();

      /* sql */
      
      if (!in_array($kind, $this->getNotStorage()))
      {
         if (!isset($config['sql']) || !is_array($config['sql']))
         {
            $errors[] = 'SQL configuration is wrong';
         }
         elseif (!isset($config['sql']['type']))
         {
            $errors[] = 'Not set SQL-type';
         }
         elseif (!is_string($config['sql']['type']))
         {
            $errors[] = 'SQL-type is wrong';
         }
         else
         {
            $valid['sql']['type'] = $config['sql']['type'];
         }
      }

      /* type */

      if (!isset($config['type']))
      {
         $errors[] = 'Not set internal-type';
      }
      elseif (!is_string($config['type']))
      {
         $errors[] = 'Internal-type is wrong';
      }
      else
      {
         $type = strtolower($config['type']);

         if (!in_array($type, $allowed_types))
         {
            $errors[] = 'Not supported internal-type';
            unset($type);
         }
         else
         {
            $valid['type'] = $config['type'];
         }
      }
       
      /* Precision */

      if (isset($config['precision']))
      {
         if (!is_array($config['precision']))
         {
            $errors[] = 'Precision configuration is wrong';
         }
         elseif (empty($config['precision']))
         {
            $errors[] = 'Precision configuration is empty';
         }
         elseif (!empty($type))
         {
            if (!$err = $this->checkPrecisionConfig($config['precision'], $type))
            {
               $valid['precision'] = $config['precision'];
            }
            else
            {
               $errors = array_merge($errors, $err);
            }
         }
      }
      
      $config = $valid;
      unset($valid);
      
      return $errors;
   }
   
   
   /**
    * Check precision configuration array
    * 
    * @param array& $config - !!! метод вносит изменения в передаваемый массив
    * @param string $type - field type
    * @return array - errors
    */
   protected function checkPrecisionConfig(array& $config, $type)
   {
      $errors = array();
      $valid  = array();
      $types  = $this->getAllowedPrecisionByTypes();
      
      foreach ($config as $precision => $value)
      {
         if (!isset($types[$precision]))
         {
            $errors[] = 'Not supported precision "'.$precision.'"';
            continue;
         }

         $allowed = $types[$precision]['allowed'];
         
         if (!($allowed == 'all' || in_array($type, $allowed)))
         {
            $errors[] = 'Not supported precision "'.$precision.'" for type "'.$type.'"';
            continue;
         }
         
         $func = 'is_'.$types[$precision]['type'];
         
         if (!$func($value))
         {
            $errors[] = 'Invalid value type for precision "'.$precision.'"';
            continue;
         }
         
         $valid[$precision] = $value;
      }
      
      $config = $valid;
      unset($valid);
      
      return $errors;
   }
   
   /**
    * Check recorders configuration array (by information_registry)
    * 
    * @param array& $config - !!! метод вносит изменения в передаваемый массив
    * @return array - errors
    */
   protected function checkRecordesConfig($config)
   {
      $errors = array();
      $valid  = array();
      
      foreach ($config as $key => $document_type)
      {
         if (is_string($document_type))
         {
            $valid[] = $document_type;
         }
         else $errors[$key] = 'Configuration is wrong';
      }
      
      $config = $valid;
      unset($valid);
      
      return $errors;
   }
   
   /**
    * Check common configuration
    * 
    * @param string $kind
    * @param string $type
    * @param array& $config - !!! метод вносит изменения в передаваемый массив
    * @return array - errors
    */
   protected function checkCommonConfig($kind, $type, array& $conf)
   {
      $errors = array();
      $valid  = array();
      
      /* Check model config */
       
      if (!isset($conf['model']))
      {
         $valid = $this->getDefaultModel($kind);
      }
      elseif (!is_array($conf['model']))
      {
         $errors['global'][] = 'Model configuration for "'.$kind.'.'.$type.'" is wrong';
      }
      elseif (empty($conf['model']))
      {
         $errors['global'][] = 'Model configuration for "'.$kind.'.'.$type.'" is empty';
      }
      else
      {
         if ($err = $this->checkModelConfig($conf['model']))
         {
            $errors['model'] = $err;
         }
         else
         {
            $valid = $conf['model'];
         }
      }
      
      $conf['model'] = $valid;
      $valid = array();
      
       
      /* Check controller config */

      if (!isset($conf['controller']))
      {
         $valid = $this->getDefaultController($kind);
      }
      elseif (!is_array($conf['controller']))
      {
         $errors['global'][] = 'Controller configuration for "'.$kind.'.'.$type.'" is wrong';
      }
      elseif (empty($conf['controller']))
      {
         $errors['global'][] = 'Controller configuration for "'.$kind.'.'.$type.'" is empty';
      }
      else
      {
         if ($err = $this->checkControllerConfig($conf['controller']))
         {
            $errors['controller'] = $err;
         }
         else
         {
            $valid = $conf['controller'];
         }
      }
       
      $conf['controller'] = $valid;
      unset($valid);

      return $errors;
   }
   
   /**
    * Check model configuration array
    * 
    * @param array& $config - !!! метод вносит изменения в передаваемый массив
    * @return array - errors
    */
   protected function checkModelConfig(array& $config)
   {
      $errors = array();
      $valid  = array();
      
      if (!isset($config['modelclass']) && !isset($config['cmodelclass']))
      {
         $errors[] = 'Configuration is wrong';
      }
      
      if (isset($config['modelclass']))
      {
         if (!is_string($config['modelclass']) || empty($config['modelclass']))
         {
            $errors[] = 'Invalid model class name';
         }
         else $valid['modelclass'] = $config['modelclass'];
      }

      if (isset($config['cmodelclass']))
      {
         if (!is_string($config['cmodelclass']) || empty($config['cmodelclass']))
         {
            $errors[] = 'Invalid cmodel class name';
         }
         else $valid['cmodelclass'] = $config['cmodelclass'];
      }
      
      $config = $valid;
      unset($valid);
      
      return $errors;
   }
   
   /**
    * Check controller configuration array
    * 
    * @param array& $config - !!! метод вносит изменения в передаваемый массив
    * @param string $type - field type
    * @return array - errors
    */
   protected function checkControllerConfig(array& $config)
   {
      $errors = array();
      $valid  = array();
      
      if (!isset($config['classname']))
      {
         $errors[] = 'Configuration is wrong';
      }
      elseif (!is_string($config['classname']) || empty($config['classname']))
      {
         $errors[] = 'Invalid controller class name';
      }
      else $valid['classname'] = $config['classname'];
      
      $config = $valid;
      unset($valid);
      
      return $errors;
   }
   
   /**
    * Check objects and fields names
    * 
    * @param string $name
    * @return boolean
    */
   protected function checkName($name)
   {
      return (bool) preg_match('/^[A-Za-z_][A-Za-z0-9_]{1,}$/i', $name); 
   }
   
   
   
   
   
   
   
      
   
   /**
    * Return entity kinds that can't storage (not have table in db)
    * 
    * @return array
    */
   protected function getNotStorage()
   {
      return array('reports', 'data_processors', 'web_services');
   }
   
   /**
    * Return entity kinds that can have tabular sections
    * 
    * @return array
    */
   protected function getHaveTabulars()
   {
      return array('catalogs', 'documents');
   }
   
   /**
    * Get list allowed internal types
    * 
    * @return array
    */
   public function getAllowedInternalTypes()
   {
      if (!isset($this->_internal_types))
      {
         $this->_internal_types = $this->loadConfigFromFile(self::p_config_dir.'internal_types.php');
      }
      
      return $this->_internal_types;
   }
   
   /**
    * Get list allowed precision by types
    * 
    * @param string $precision
    * @return array or null
    */
   public function getAllowedPrecisionByTypes($precision = null)
   {
      if (!isset($this->_precision_by_types))
      {
         $this->_precision_by_types = $this->loadConfigFromFile(self::p_config_dir.'precision_by_types.php');
      }
      
      if (!is_null($precision))
      {
         return isset($this->_precision_by_types["$precision"]) ? $this->_precision_by_types["$precision"] : null;
      }
      
      return $this->_precision_by_types;
   }
   
   /**
    * Get list allowed periods
    * 
    * @return array or null
    */
   public function getAllowedPeriods()
   {
      if (!isset($this->_periods))
      {
         $this->_periods = $this->loadConfigFromFile(self::p_config_dir.'periods.php');
      }
      
      return $this->_periods;
   }
   
   /**
    * Get default model configuration
    * 
    * @param string $kind - entity kind
    * @return array
    */
   public function getDefaultModel($kind)
   {
      if (!isset($this->_default_models))
      {
         $this->_default_models = $this->loadConfigFromFile(self::p_config_dir.'default_models.php');
      }
      
      if (empty($this->_default_models[$kind]))
      {
         throw new Exception(__METHOD__.": Default model configuration is wrong");
      }
      
      return $this->_default_models[$kind];
   }
   
   /**
    * Get default controller configuration
    * 
    * @param string $kind - entity kind
    * @return array
    */
   public function getDefaultController($kind)
   {
      if (!isset($this->_default_controllers))
      {
         $this->_default_controllers = $this->loadConfigFromFile(self::p_config_dir.'default_controllers.php');
      }
      
      if (empty($this->_default_controllers[$kind]))
      {
         throw new Exception(__METHOD__.": Default controller configuration is wrong");
      }
      
      return $this->_default_controllers[$kind];
   }
   
   /**
    * Load configuration from file
    * 
    * @throws Exception
    * @param string $path
    * @param array& $options
    * @return array
    */
   public function loadConfigFromFile($path, array & $options = array())
   {
      if (!file_exists($path)) throw new Exception(__METHOD__.': The file '.$path.' does not exist');

      include($path);
      
      if (!isset($_conf))
      {
         $start  = strrpos($path, '/') + 1;
         $length = strrpos($path, '.') - $start;
         $varName = '_'.substr($path, $start, $length); //basename($path, ".php");
         
         return (isset($$varName) && is_array($$varName)) ? $$varName : array();
      }
      
      return is_array($_conf) ? $_conf : array();
   }
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   /**
    * Create internal configuration
    * 
    * @param array& $dictionary
    * @param array& $options
    * @return array - errors
    */
   protected function createInternalConfiguration(array& $dictionary, array& $options = array())
   {
      // configuration map
      $map['container'] = self::m_config_dir.'container.php';
      
      // generate db_settings configuration
      $_conf = $this->loadConfigFromFile($map['container']);
      
      $db_conf['dbname']    = $_conf['db']['dbname'];
      $db_conf['dbprefix']  = $_conf['db']['dbprefix'];
      $db_conf['dbcharset'] = $_conf['db']['dbcharset'];
      
      unset($_conf);
      
      // generate other internal configuration
      $options = array_merge($options, array('dbprefix' => $db_conf['dbprefix']));
      $result  = $this->generateInternalConfiguration($dictionary, $options);
      
      if (isset($result['errors'])) return $result['errors'];
      
      $result['other']['db_settings'] = $db_conf;
      $result['other']['db_map']      = $this->generateDBMap($result, $options);;
      $result['other']['relations']   = $this->generateRelationsMap($result, $options);
      
      // save internal configuration (and generate configuration map)
      $map['information_registry']    = $this->saveInternalConfiguration($result['information_registry'],    'information_registry/');
      $map['reports']                 = $this->saveInternalConfiguration($result['reports'],                 'reports/');
      $map['data_processors']         = $this->saveInternalConfiguration($result['data_processors'],         'data_processors/');
      $map['catalogs']['tabulars']    = $this->saveInternalConfiguration($result['catalogs']['tabulars'],    'catalogs/tabulars/');
      $map['documents']['tabulars']   = $this->saveInternalConfiguration($result['documents']['tabulars'],   'documents/tabulars/');
      $map['web_services']['actions'] = $this->saveInternalConfiguration($result['web_services']['actions'], 'web_services/actions/');
      unset(
         $result['catalogs']['tabulars'],
         $result['documents']['tabulars'],
         $result['web_services']['actions']
      );
      $map['catalogs']     = array_merge($map['catalogs'],     $this->saveInternalConfiguration($result['catalogs'],     'catalogs/'));
      $map['documents']    = array_merge($map['documents'],    $this->saveInternalConfiguration($result['documents'],    'documents/'));
      $map['web_services'] = array_merge($map['web_services'], $this->saveInternalConfiguration($result['web_services'], 'web_services/'));
      
      $map = array_merge($map, $this->saveInternalConfiguration($result['other']));
      
      // save configuration map
      if (!file_put_contents($this->config_map_path, "<?php\n".'$_config_map = '.Utility::convertArrayToPHPString($map).';'))
      {
         throw new Exception(__METHOD__.': Can\'t save internal configuration map');
      }
      
      return array();
   }
   
   /**
    * Save internal configuration files
    * 
    * @param array& $files
    * @param string $dir
    * @return void
    */
   protected function saveInternalConfiguration(array& $files, $dir = '')
   {
      $map  = array();
      $path = $this->config_dir.$dir;
      
      if (!is_dir($path))
      {
         if (!mkdir($path, 0755, true)) throw new Exception(__METHOD__.': Can\'t create dir "'.$path.'"');
      }
      
      foreach ($files as $fname => $values)
      {
         $fpath = $path.$fname.'.php';
         
         if (!file_put_contents($fpath, "<?php\n".'$_'.$fname.' = '.Utility::convertArrayToPHPString($values).';'))
         {
            throw new Exception(__METHOD__.': Can\'t save internal "'.$fname.'" configuration');
         }
         
         $map[$fname] = $fpath;
      }
      
      return $map;
   }
   
   /**
    * Generate internal configuration
    * 
    * @param array& $dictionary
    * @param array& $options
    * @return array - 'errors' => $errors or list <fName> => array()
    */
   protected function generateInternalConfiguration(array& $dictionary, array& $options = array())
   {
      $errors   = array();
      $internal = array();
      
      $this->internal['possible_to_refer']['catalogs']  = array_keys($dictionary['catalogs']);
      $this->internal['possible_to_refer']['documents'] = array_keys($dictionary['documents']);
      
      /* Catalogs */
      
      $result = $this->generateCatalogsInternalConfiguration($dictionary['catalogs'], $options);
      
      if (!isset($result['errors']))
      {
         $internal['catalogs'] = $result;
      }
      else $errors = $result['errors'];
      
      
      /* Information registry */
      
      $result = $this->generateInfRegistryInternalConfiguration($dictionary['information_registry'], $options);
      
      if (!isset($result['errors']))
      {
         $internal['information_registry'] = $result;
      }
      else $errors = array_merge($errors, $result['errors']);
      
      
      /* Documents */
      
      $result = $this->generateDocumentsInternalConfiguration($dictionary['documents'], $options);
      
      if (!isset($result['errors']))
      {
         $internal['documents'] = $result;
      }
      else $errors = array_merge($errors, $result['errors']);
      
      unset($result);
      
      // Recorders and Recorder for
      if (!$errors)
      {
         foreach ($internal['documents']['recorders'] as $r_type => $recorder)
         {
            if (isset($internal['information_registry']['recorders'][$r_type]))
            {
               $internal['information_registry']['recorders'][$r_type] = array_merge($recorder, $internal['information_registry']['recorders'][$r_type]);
               $internal['information_registry']['recorders'][$r_type] = array_unique($internal['information_registry']['recorders'][$r_type]);
            }
            else $internal['information_registry']['recorders'][$r_type] = $recorder;
         }
         unset($internal['documents']['recorders']);
         
         foreach ($internal['information_registry']['recorder_for'] as $r_type => $recorder)
         {
            if (isset($internal['documents']['recorder_for'][$r_type]))
            {
               $internal['documents']['recorder_for'][$r_type] = array_merge($recorder, $internal['documents']['recorder_for'][$r_type]);
               $internal['documents']['recorder_for'][$r_type] = array_unique($internal['documents']['recorder_for'][$r_type]);
            }
            else $internal['documents']['recorder_for'][$r_type] = $recorder;
         }
         unset($internal['information_registry']['recorder_for']);
      }
      
      
      /* Reports */
      
      $result = $this->generateReportsInternalConfiguration($dictionary['reports'], $options);
      
      if (!isset($result['errors']))
      {
         $internal['reports'] = $result;
      }
      else $errors = array_merge($errors, $result['errors']);
      
      
      /* Data Processors */
      
      $result = $this->generateDataProcessorsInternalConfiguration($dictionary['data_processors'], $options);
      
      if (!isset($result['errors']))
      {
         $internal['data_processors'] = $result;
      }
      else $errors = array_merge($errors, $result['errors']);
      
      
      /* Web services */
      
      $result = $this->generateWebServicesInternalConfiguration($dictionary['web_services'], $options);
      
      if (!isset($result['errors']))
      {
         $internal['web_services'] = $result;
      }
      else $errors = array_merge($errors, $result['errors']);
      
      
      return ($errors) ? array('errors' => $errors) : $internal; 
   }
   
   /**
    * Generate catalogs internal configuration
    * 
    * @param array& $catalogs_dictionary
    * @param array& $options
    * @return array - 'errors' => $errors or list <fName> => array()
    */
   protected function generateCatalogsInternalConfiguration(array& $catalogs_dictionary, array& $options = array())
   {
      return $this->generateEntityInternalConfiguration($catalogs_dictionary, 'catalogs', $options);
   }
   
   /**
    * Generate documents internal configuration
    * 
    * @param array& $documents_dictionary
    * @param array& $options
    * @return array - 'errors' => $errors or list <confName> => array()
    */
   protected function generateDocumentsInternalConfiguration(array& $documents_dictionary, array& $options = array())
   {
      return $this->generateEntityInternalConfiguration($documents_dictionary, 'documents', $options);
   }
   
   /**
    * Generate reports internal configuration
    * 
    * @param array& $reports_dictionary
    * @param array& $options
    * @return array - 'errors' => $errors or list <confName> => array()
    */
   protected function generateReportsInternalConfiguration(array& $reports_dictionary, array& $options = array())
   {
      return $this->generateEntityInternalConfiguration($reports_dictionary, 'reports', $options);
   }
   
   /**
    * Generate data processors internal configuration
    * 
    * @param array& $data_processors_dictionary
    * @param array& $options
    * @return array - 'errors' => $errors or list <confName> => array()
    */
   protected function generateDataProcessorsInternalConfiguration(array& $data_processors_dictionary, array& $options = array())
   {
      return $this->generateEntityInternalConfiguration($data_processors_dictionary, 'data_processors', $options);
   }
   
   /**
    * Generate entity (catalogs and documents) internal configuration
    * 
    * @param array& $entity_dictionary
    * @param string $kind
    * @param array& $options
    * @return array - 'errors' => $errors or list <confName> => array()
    */
   protected function generateEntityInternalConfiguration(array& $entity_dictionary, $kind, array& $options = array())
   {
      $errors = array();
      $result = array(
         "$kind"      => array(),
         'fields'     => array(),
         'field_type' => array(),
         'field_prec' => array(),
         'references' => array(),
         'required'   => array(),
         'model'      => array(),
         'controller' => array()
      );
      
      if (in_array($kind, $this->getHaveTabulars()))
      {
         $result['tabulars'] = array();
      }
      
      if (!in_array($kind, $this->getNotStorage()))
      {
         $result['field_sql'] = array();
         
         if ($kind == 'documents')
         { 
            $result['recorder_for'] = array();
            $result['recorders'] = array();
            
            if (empty($entity_dictionary))
            {
               // Create empty configuration for tabular sections
               $result['tabulars'] = $this->generateTabularInternalConfiguration($entity_dictionary, '', '');
            }
         }
      }
      
      foreach ($entity_dictionary as $type => $params)
      {
         $result[$kind][] = $type;
         
         /* Fields */
         
         $add_fields = array();
         
         if ($kind == 'catalogs')
         {
            $clength = empty($params['fields']['Code']['precision']['max_length']) ? 5 : $params['fields']['Code']['precision']['max_length'];
            $add_fields = array(
               'Code' => array(
                  'type' => 'string',
                  'sql'  => array(
                     'type' => "varchar(".$clength.") NOT NULL"
                  ),
                  'precision' => array(
                     'required'   => true,
                     'max_length' => $clength
                  )
               ),
               'Description' => array(
                  'type' => 'string',
                  'sql'  => array(
                     'type' => "varchar(255) NOT NULL"
                  ),
                  'precision' => array('required' => true)
               )
            );
            unset(
               $params['fields']['Code'],
               $params['fields']['Description']
            );
         }
         elseif ($kind == 'documents')
         {
            $field = 'Date';
            $add_fields = array(
               $field => array(
                  'type' => 'datetime',
                  'sql'  => array(
                     'type' => "DATETIME NOT NULL default '0000-00-00'"
                  ),
                  'precision' => array('required' => true)
               )
            );
            unset($params['fields'][$field]);   
         }
         
         $params['fields'] = array_merge($add_fields, $params['fields']);
         
         $res = $this->generateFieldsInternalConfiguration($params['fields'], $kind, $options);
         
         if (!isset($res['errors']))
         {
            if (isset($result['field_sql']))
            {
               $result['field_sql'][$type] = $res['field_sql'];
            }
            $result['fields'][$type]     = $res['fields'];
            $result['field_type'][$type] = $res['field_type'];
            $result['field_prec'][$type] = $res['field_prec'];
            $result['references'][$type] = $res['references'];
            $result['required'][$type]   = $res['required'];
         }
         else $errors = array_merge($errors, $res['errors']);
         
         
         /* Recorder for */
         
         if ($kind == 'documents')
         {
            foreach ($params['recorder_for'] as $r_type)
            {
               if (isset($this->dictionary['information_registry'][$r_type]))
               {
                  $result['recorder_for'][$type][] = $r_type;
                  $result['recorders'][$r_type][] = $type;
               }
               else $errors[] = 'Information registry "'.$r_type.'" not exists';
            }
         }
         
         
         /* Tabular sections */
         
         if (isset($result['tabulars']))
         {
            $res = $this->generateTabularInternalConfiguration($params['tabular_sections'], $kind, $type, $options);
             
            if (!isset($res['errors']))
            {
               $result['tabulars'] = array_merge_recursive($result['tabulars'], $res);
            }
            else $errors = array_merge($errors, $res['errors']);
         }
         
         
         /* Model */
         
         $result['model'][$type] = $params['model'];
         
         /* Controller */
         
         $result['controller'][$type] = $params['controller'];
      }
      
      return empty($errors) ? $result : array('errors' => $errors);
   }
   
   /**
    * Generate information registry internal configuration
    * 
    * @param array& $registry_dictionary
    * @param array& $options
    * @return array - 'errors' => $errors or list <confName> => array()
    */
   protected function generateInfRegistryInternalConfiguration(array& $registry_dictionary, array& $options = array())
   {
      $errors = array();
      $result = array(
         'information_registry' => array(),
         'dimensions' => array(),
         'periodical' => array(),
         'fields'     => array(),
         'field_sql'  => array(),
         'field_type' => array(),
         'field_prec' => array(),
         'references' => array(),
         'required'   => array(),
         'recorders'  => array(),
         'model'      => array(),
         'controller' => array()
      );
      
      $result['recorder_for'] = array();
      
      foreach ($registry_dictionary as $registry => $params)
      {
         $result['information_registry'][] = $registry;
         
         /* Dimensions */
         
         if (!empty($params['dimensions']))
         {
            $result['dimensions'][$registry] = array_keys($params['dimensions']);

            /*foreach ($params['dimensions'] as $field => $conf)
            {
               $params['dimensions'][$field]['precision']['required'] = true;
            }*/
         }
         
         /* Periodical */
         
         if (isset($params['periodical']))
         {
            $field = 'period';
            $per_field = array(
               $field => array(
                  'type' => 'datetime',
                  'sql'  => array(
                     'type' => "DATETIME NOT NULL default '0000-00-00'"
                  ),
                  'precision' => array('required' => true)
               )
            );
            
            $result['periodical'][$registry]['field']  = $field;
            $result['periodical'][$registry]['period'] = $params['periodical'];
         }
         else $per_field = array();
         
         /* Fields */
         
         $_fields = array_merge($params['dimensions'], $per_field, $params['fields']);
         
         $res = $this->generateFieldsInternalConfiguration($_fields, 'information_registry', $options);
         
         unset($_fields);
         
         if (!isset($res['errors']))
         {
            $result['fields'][$registry]     = $res['fields'];
            $result['field_sql'][$registry]  = $res['field_sql'];
            $result['field_type'][$registry] = $res['field_type'];
            $result['field_prec'][$registry] = $res['field_prec'];
            $result['references'][$registry] = $res['references'];
            $result['required'][$registry]   = $res['required'];
         }
         else $errors = array_merge($errors, $res['errors']);
         
         
         /* Recorders */
         
         foreach ($params['recorders'] as $type)
         {
            if (isset($this->dictionary['documents'][$type]))
            {
               $result['recorders'][$registry][] = $type;
               $result['recorder_for'][$type][]  = $registry;
            }
            else $errors[] = 'Information registry "'.$registry.'": documents "'.$type.'" not exists.';
         }
         
         
         /* Model */
         
         $result['model'][$registry] = $params['model'];
         
         
         /* Controller */
         
         $result['controller'][$registry] = $params['controller'];
      }
      
      return empty($errors) ? $result : array('errors' => $errors);
   }
   
   /**
    * Generate tabular internal configuration
    * 
    * @param array& $fields_dictionary
    * @param string $kind - entity kind [ catalogs | documents ]
    * @param string $type - entity name
    * @param array& $options
    * @return array - 'errors' => $errors or list <confName> => array()
    */
   protected function generateTabularInternalConfiguration(array& $tabular_dictionary, $kind, $type, array& $options = array())
   {
      $errors = array();
      $result = array(
         'tabulars'   => array(),
         'fields'     => array(),
         'field_sql'  => array(),
         'field_type' => array(),
         'field_prec' => array(),
         'references' => array(),
         'required'   => array(),
         'model'      => array(),
         'controller' => array()
      );
      
      foreach ($tabular_dictionary as $tabular => $params)
      {
         $result['tabulars'][$type][] = $tabular;
         
         /* Owner */
         
         $field = 'Owner';
         $add_fields = array(
            $field => array(
               'reference' => $type,
               'precision' => array('required' => true)
            )
         );
         
         $params['fields'] = array_merge($params['fields'], $add_fields);
         
         /* Fields */
         
         $res = $this->generateFieldsInternalConfiguration($params['fields'], $kind, $options);
          
         if (!isset($res['errors']))
         {
            $result['fields'][$type][$tabular]     = $res['fields'];
            $result['field_sql'][$type][$tabular]  = $res['field_sql'];
            $result['field_type'][$type][$tabular] = $res['field_type'];
            $result['field_prec'][$type][$tabular] = $res['field_prec'];
            $result['references'][$type][$tabular] = $res['references'];
            $result['required'][$type][$tabular]   = $res['required'];
         }
         else $errors = array_merge($errors, $res['errors']);
         
         /* Model */
         
         $result['model'][$type][$tabular] = $params['model'];
         
         /* Controller */
         
         $result['controller'][$type][$tabular] = $params['controller'];
      }
      
      return empty($errors) ? $result : array('errors' => $errors);
   }
   
   /**
    * Generate Web-services internal configuration
    * 
    * @param array& $entity_dictionary
    * @param array& $options
    * @return array - 'errors' => $errors or list <confName> => array()
    */
   protected function generateWebServicesInternalConfiguration(array& $entity_dictionary, array& $options = array())
   {
      $kind   = 'web_services';
      $errors = array();
      $result = array(
         "$kind"      => array(),
         'actions'    => array(),
         'model'      => array(),
         'controller' => array()
      );
      
      foreach ($entity_dictionary as $type => $conf)
      {
         $result[$kind][] = $type;
         
         $actions = array(
            'actions'    => array(),
            'fields'     => array(),
            'field_type' => array(),
            'field_prec' => array(),
            'references' => array(),
            'required'   => array()
         );
         
         /* Actions */
         
         foreach ($conf['actions'] as $action => $params)
         {
            $actions['actions'][$type][] = $action;
            
            /* Fields */
            
            $res = $this->generateFieldsInternalConfiguration($params['fields'], $kind, $options);
             
            if (!isset($res['errors']))
            {
               $actions['fields'][$type][$action]     = $res['fields'];
               $actions['field_type'][$type][$action] = $res['field_type'];
               $actions['field_prec'][$type][$action] = $res['field_prec'];
               $actions['references'][$type][$action] = $res['references'];
               $actions['required'][$type][$action]   = $res['required'];
            }
            else $errors = array_merge($errors, $res['errors']);
         }
         
         if (empty($errors))
         {
            $result['actions'] = array_merge_recursive($result['actions'], $actions);
         }
         
         /* Model */
         
         $result['model'][$type] = $conf['model'];
         
         /* Controller */
         
         $result['controller'][$type] = $conf['controller'];
      }
      
      return empty($errors) ? $result : array('errors' => $errors);
   }
   
   
   
   
   
   
   
   
   
   /**
    * Generate fields internal configuration
    * 
    * @param array& $fields_dictionary
    * @param string $kind
    * @param array& $options
    * @return array - 'errors' => $errors or list <confName> => array()
    */
   protected function generateFieldsInternalConfiguration(array& $fields_dictionary, $kind, array& $options = array())
   {
      $errors = array();
      $result = array(
         'fields'     => array(),
         'field_sql'  => array(),
         'field_type' => array(),
         'field_prec' => array(),
         'references' => array(),
         'required'   => array()
      );
      
      foreach ($fields_dictionary as $name => $params)
      {
         $result['fields'][] = $name;

         if (isset($params['reference'])) // Link
         {
            // Link params
            if (!strpos($params['reference'], "."))
            {
               $r_kind = $kind;
               $r_type = $params['reference'];
            }
            else list($r_kind, $r_type) = explode(".", $params['reference']);
            
            // Check Link
            if (!isset($this->internal['possible_to_refer'][$r_kind]))
            {
               $errors[] = 'Invalid reference: "'.ucfirst($r_kind).'" can\'t reference to self.';
            }
            elseif (!in_array($r_type, $this->internal['possible_to_refer'][$r_kind]))
            {
               $errors[] = 'Invalid reference: "'.ucfirst($r_kind).'" with name "'.$r_type.'" not exists.';
            }
            
            $result['references'][$name] = array('kind' => $r_kind, 'type' => $r_type);
            $result['field_type'][$name] = 'reference';
         }
         else // Attribute
         {
            if (!in_array($kind, $this->getNotStorage()))
            {
               $result['field_sql'][$name] = $params['sql']['type'];
            }
            $result['field_type'][$name] = $params['type'];
         }

         /* Precision */
         
         if (isset($params['precision']))
         {
            if (isset($params['precision']['required']))
            {
               if ($params['precision']['required']) $result['required'][] = $name;
               unset($params['precision']['required']); 
            }
            
            if (!empty($params['precision'])) $result['field_prec'][$name] = $params['precision'];
         }
      }

      return empty($errors) ? $result : array('errors' => $errors);
   }

   
   /**
    * Generate db map
    * 
    * @param array& $configuration - internal configuration
    * @param array& $options
    * @return array db_map
    */
   protected function generateDBMap(array& $configuration, array& $options = array())
   {
      $db_map   = array();
      $dbprefix = '';
      $catalogs   =& $configuration['catalogs']['catalogs'];
      $registries =& $configuration['information_registry']['information_registry'];
      $periodical =& $configuration['information_registry']['periodical'];
      $documents  =& $configuration['documents']['documents'];
      $recorders  =& $configuration['information_registry']['recorders'];
      
      if (!empty($options['dbprefix']))  $dbprefix  = $options['dbprefix'];
      
      /* Catalogs */
      
      foreach ($catalogs as $catalog)
      {
         $db_map['catalogs'][$catalog]['table'] = $dbprefix.'catalogs_'.$catalog;
         $db_map['catalogs'][$catalog]['pkey']  = '_id';
         $db_map['catalogs'][$catalog]['deleted'] = '_deleted';
         
         /* Tabular sections */
         
         $t_map = array();
         $tabulars =& $configuration['catalogs']['tabulars']['tabulars'];
         
         if (!empty($tabulars[$catalog]))
         {
            foreach ($tabulars[$catalog] as $tabular)
            {
               $t_map[$tabular]['table'] = $dbprefix.'catalogs_'.$catalog.'_'.$tabular;
               $t_map[$tabular]['pkey']  = '_id';
            }
         }
         
         $db_map['catalogs'][$catalog]['tabulars'] = $t_map;
      }
      
      /* Information registry */
      
      foreach ($registries as $registry)
      {
         $db_map['information_registry'][$registry]['table'] = $dbprefix.'information_registry_'.$registry;
         $db_map['information_registry'][$registry]['pkey']  = '_id';
         //$db_map['information_registry'][$registry]['deleted'] = '_deleted';
         
         // Period
         /*if (array_key_exists($registry, $periodical))
         {
            $db_map['information_registry'][$registry]['period'] = '_period';
         }*/
         
         // Recorders
         if (array_key_exists($registry, $recorders))
         {
            $db_map['information_registry'][$registry]['recorder_type'] = '_rec_type';
            $db_map['information_registry'][$registry]['recorder_id'] = '_rec_id';
         }
      }
      
      /* Documents */
      
      foreach ($documents as $document)
      {
         $db_map['documents'][$document]['table'] = $dbprefix.'documents_'.$document;
         $db_map['documents'][$document]['pkey']  = '_id';
         $db_map['documents'][$document]['post']  = '_post';
         $db_map['documents'][$document]['deleted'] = '_deleted';
         
         /* Tabular sections */
         
         $t_map = array();
         $tabulars =& $configuration['documents']['tabulars']['tabulars'];
         
         if (!empty($tabulars[$document]))
         {
            foreach ($tabulars[$document] as $tabular)
            {
               $t_map[$tabular]['table'] = $dbprefix.'documents_'.$document.'_'.$tabular;
               $t_map[$tabular]['pkey']  = '_id';
            }
         }
         
         $db_map['documents'][$document]['tabulars'] = $t_map;
      }
      
      return $db_map;
   }
   
   /**
    * Generate relations map
    * 
    * @param array& $configuration - internal configuration
    * @param array& $options
    * @return array relations
    */
   protected function generateRelationsMap(array& $configuration, array& $options = array())
   {
      $relations = array();
      
      foreach (array('catalogs', 'documents', 'information_registry') as $kind)
      {
         // Objects and registries
         $references =& $configuration[$kind]['references'];
         foreach ($references as $type => $fields)
         {
            foreach ($fields as $field => $params)
            {
               $relations[$params['kind']][$params['type']][$kind][$type][] = $field;
            }
         }
         
         /*if ($kind == 'information_registry') continue;
         
         // Tabular sections
         $references =& $configuration[$kind]['tabulars']['references'];
         foreach ($references as $type => $tabulars)
         {
            foreach ($tabulars as $tabular => $fields)
            {
               foreach ($fields as $field => $params)
               {
                  $relations[$params['kind']][$params['type']][$kind.'.'.$type.'.tabulars'][$tabular][] = $field;
               }
            }
         }*/
      }
      
      return $relations;
   }
   
   
   
   
   
   
   
   
   
   /**
    * Get SQL-queries to table creation 
    * 
    * @param array& $options
    * @return array
    */
   protected function retrieveSQLToInstall(array& $options = array())
   {
      $CManager  = $this->getConfigManager($options);
      $db_conf   = $CManager->getDBConfiguration($options);
      $dbcharset = empty($db_conf['dbcharset']) ? 'utf8' : $db_conf['dbcharset'];
      
      $query = array();
      
      // Catalog
      $query = $this->generateSQLCreate('catalogs', $CManager, $dbcharset, $options);
      
      // Information registry
      $query = array_merge($query, $this->generateSQLCreate('information_registry', $CManager, $dbcharset, $options));
      
      // Documents
      $query = array_merge($query, $this->generateSQLCreate('documents', $CManager, $dbcharset, $options));
      
      return $query;
   }
   
   /**
    * Generate CREATE SQL-query to create entities tables
    * 
    * @param string $kind
    * @param object $CManager
    * @param string $dbcharset
    * @param array& $options
    * @return array
    */
   protected function generateSQLCreate($kind, $CManager, $dbcharset, array& $options = array())
   {
      $db_map    = $CManager->getInternalConfiguration('db_map', null, $options);
      $fields    = $CManager->getInternalConfiguration($kind.'.fields', null, $options);
      $field_sql = $CManager->getInternalConfiguration($kind.'.field_sql', null, $options);
      
      $query  = array();
      $db_map =& $db_map[$kind];
      
      if ($kind != 'information_registry')
      {
         $tab_fields    = $CManager->getInternalConfiguration($kind.'.tabulars.fields', null, $options);
         $tab_field_sql = $CManager->getInternalConfiguration($kind.'.tabulars.field_sql', null, $options);
      }
      else
      {
         $periodical = $CManager->getInternalConfiguration($kind.'.periodical', null, $options);
         $demensions = $CManager->getInternalConfiguration($kind.'.dimensions', null, $options);
         $recorders  = $CManager->getInternalConfiguration($kind.'.recorders', null, $options);
      }

      foreach ($fields as $type => $e_fields)
      {
         $table = $db_map[$type]['table'];
         $pKey  = $db_map[$type]['pkey'];
         $uKey  = '';
         
         $q  = 'CREATE TABLE IF NOT EXISTS `'.$table.'` (';
         $q .= '`'.$pKey.'` int(11) NOT NULL AUTO_INCREMENT';
          
         /* Fields */
         foreach ($e_fields as $field)
         {
            $sql_def = isset($field_sql[$type][$field]) ? $field_sql[$type][$field] : 'int(11) NOT NULL';
            $q .= ', `'.$field.'` '.$sql_def;
         }

         if ($kind == 'catalogs')
         {
            $uKey = ', UNIQUE KEY `Code` (`Code`)';
         }
         elseif ($kind == 'documents')
         {
            $q .= ', `'.$db_map[$type]['post'].'` tinyint(1) NOT NULL default 0';
         }
         elseif ($kind == 'information_registry')
         {
            $uKey = '';
            
            if (isset($recorders[$type]))
            {
               $q .= ', `'.$db_map[$type]['recorder_type'].'` varchar(255) NOT NULL default \'\'';
               $q .= ', `'.$db_map[$type]['recorder_id'].'` int(11) NOT NULL default 0';
               
               $uKey .= $db_map[$type]['recorder_type'].'`, `'.$db_map[$type]['recorder_id'].'`, `';
            }
            
            $uKey .= isset($demensions[$type]) ? implode("`, `", $demensions[$type]) : '';
            
            if (isset($periodical[$type]))
            {
               $uKey .= (isset($demensions[$type]) ? '`, `' : '').$periodical[$type]['field'];
            }
            
            if (strlen($uKey)) $uKey = ', UNIQUE KEY `demensions` (`'.$uKey.'`)';
         }
         
         if (isset($db_map[$type]['deleted']))
         {
            $q .= ', `'.$db_map[$type]['deleted'].'` tinyint(1) NOT NULL default 0';
         }
         
         $q .= ', PRIMARY KEY (`'.$pKey.'`)'.$uKey;
          
         $query[$table] = $q.') ENGINE=InnoDB DEFAULT CHARSET='.$dbcharset.' COLLATE='.$dbcharset.'_general_ci AUTO_INCREMENT=1';
          
         /* Tabular sections */
          
         if ($kind != 'information_registry' && !empty($tab_fields[$type]))
         {
            $dbmap =& $db_map[$type]['tabulars'];
            
            foreach ($tab_fields[$type] as $name => $n_fields)
            {
               $table = $dbmap[$name]['table'];
               $pKey  = $dbmap[$name]['pkey'];
               
               $q  = 'CREATE TABLE IF NOT EXISTS `'.$table.'` (';
               $q .= '`'.$pKey.'` int(11) NOT NULL AUTO_INCREMENT';
               
               /* Fields */
               foreach ($n_fields as $field)
               {
                  $sql_def = isset($tab_field_sql[$type][$name][$field]) ? $tab_field_sql[$type][$name][$field] : 'int(11) NOT NULL';
                  $q .= ', `'.$field.'` '.$sql_def;
               }

               $q .= ', PRIMARY KEY (`'.$pKey.'`)';

               $query[$table] = $q.') ENGINE=InnoDB DEFAULT CHARSET='.$dbcharset.' COLLATE='.$dbcharset.'_general_ci AUTO_INCREMENT=1';
            }
         }
      }

      return $query;
   }
   
   
   
   
   
   
   
   /**
    * Return true if db and internal configuration already exist
    * 
    * @return bool
    */
   public function isInstalled()
   {
      return file_exists($this->config_map_path);
   }
   
   /**
    * Get Container object
    * 
    * @throws Exception
    * @param array& $options
    * @return array
    */
   protected function getContainer(array& $options = array())
   {
      if (isset($this->Container) && is_a($this->Container, 'Container')) return $this->Container;
      
      $options = array_merge(array('map_path' => $this->config_map_path), $options);
      
      $this->Container = Container::createInstance($options);
      
      return $this->Container;
   }
   
   /**
    * Get configuration manager
    * 
    * @param $options
    * @return array
    */
   protected function getConfigManager(array& $options = array())
   {
      if (isset($this->CManager) && is_a($this->CManager, 'ConfigManager')) return $this->CManager;
      
      $options = array_merge(array('map_path' => $this->config_map_path), $options);
      
      return $this->getContainer($options)->getConfigManager($options);
   }
   
   /**
    * Install catalogs
    * 
    * @param array& $options
    * @return array
    */
   public function install(array& $options = array())
   {
      if ($this->isInstalled()) return array('Entities already installed');
      
      // load dictionary
      $errors = $this->loadDictionary($options);
      
      if (!empty($errors)) return $errors;
      
      // create internal config
      $errors = $this->createInternalConfiguration($this->dictionary);
      
      if (!empty($errors)) return $errors;
      
      // create modules
      $modules = $this->getContainer($options)->getModulesManager();
      $conf = $modules->createModules(
         array(
            'catalogs', 
            'documents',
            'information_registry',
            'reports', 
            'data_processors',
            'web_services'
         ),
         $options
      );
      
      $map = $this->saveInternalConfiguration($conf);
      $map = array_merge(Utility::loadArrayFromFile($this->config_map_path), $map);
      
      if (!file_put_contents($this->config_map_path, "<?php\n".'$_config_map = '.Utility::convertArrayToPHPString($map).';'))
      {
         throw new Exception(__METHOD__.': Can\'t save internal configuration map');
      }
      
      // table creation
      $db = $this->getContainer($options)->getDBManager($options);
      $queries = $this->retrieveSQLToInstall($options);
      
      foreach ($queries as $query)
      {
         if (!$db->executeQuery($query))
         {
            $errors[] = $db->getError();
         }
      }
      
      return $errors;
   }
   
   public function update(array& $options = array())
   {
      //echo '<pre>'; print_r($this->dictionary); echo '</pre>';
      /* Копируем старую конфигурацию в папку config/internal/backup */
      /* Генерируем новую */
      /* Сравниваем старую и новую, создаем таблицу отличий */
      /* Генерируем SQL для обновления */
   }
   
   public function remove(array& $options = array())
   {
      if (!$this->isInstalled()) return array('Entities not installed');
      
      $errors   = array();
      $CManager = $this->getConfigManager($options);
      
      /* remove all tables */
      
      $tables  = array();
      $db_map  = $CManager->getInternalConfiguration('db_map', null, $options);
      
      foreach ($db_map as $map)
      {
         foreach ($map as $config)
         {
            $tables[] = $config['table'];
             
            if (empty($config['tabulars'])) continue;
             
            foreach ($config['tabulars'] as $conf)
            {
               $tables[] = $conf['table'];
            }
         }
      }
      
      $query  = 'DROP TABLE IF EXISTS '.implode(", ", $tables);
      $db     = $this->getContainer($options)->getDBManager($options);
      
      if (!$db->executeQuery($query))
      {
         return array($db->getError());
      }
      
      /* remove all modules */
      
      $modules = $this->getContainer($options)->getModulesManager();
      
      if (!$modules->clearCache()) return array('Can\'t clear modules cache');
      
      $errors = $modules->removeModules($options);
         
      if (!empty($errors)) return $errors;
      
      /* remove internal configuration */
      
      $conf_map = $CManager->getConfigMap($options);
      unset($conf_map['container']);
      
      $paths   = Utility::retrieveTreeLeaves($conf_map);
      $paths[] = $this->config_map_path;
      
      foreach ($paths as $path)
      {
         if (!file_exists($path)) continue;
         
         if (!unlink($path)) $errors[] = 'Can\'t delete configuration file "'.$path.'"';
      }
      
      return $errors;
   }

}