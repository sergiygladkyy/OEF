<?php

require_once('lib/persistent/config/SystemConstants.php');

class PersistentLayer
{
   const m_config_dir    = 'config/';   
   const p_config_dir    = 'lib/persistent/config/';
   const i_config_dir    = 'config/internal/';
   const container_path  = 'config/container.php';
   const dictionary_path = 'config/dictionary.php';
   const config_map_path = 'config/internal/config_map.php';
   
   protected static $instance = null;

   protected
      $applied_solution_name = null,
      $applied_solution_dir  = null,
	  $config_dir      = null,
	  $container_path  = null,
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
      if (is_null(self::$instance))
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
	  $this->container_path  = $base.$apps.self::container_path;
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
      
      $this->addSystemEntities($this->dictionary, $options);
      
      return $this->checkDictionary($this->dictionary, $options);
   }
   
   /**
    * Add system entities
    * 
    * @param array& $dict - dictionary
    * @param array& $options
    * @return array
    */
   protected function addSystemEntities(array& $dict, array& $options = array())
   {
      $dict['catalogs']['SystemUsers'] = array(
         'fields' => array(
            'Code' => array(
               'precision' => array(
                  'max_length' => 8
               )
            ),
            'User' => array(
               'type' => 'string',
               'sql'  => array(
                  'type' => "varchar(128) NOT NULL default ''"
               ),
               'precision' => array(
                  'required' => true
               )
            ),
            'AuthType' => array(
               'type' => 'enum',
               'sql'  => array(
                  'type' => "ENUM('MTAuth', 'Basic', 'LDAP')"
               ),
               'precision' => array(
                  'in' => array(1 => 'MTAuth', 2 => 'Basic', 3 => 'LDAP'),
                  'required' => true
               )
            ),
            'Attributes' => array(
               'type' => 'string',
               'sql'  => array(
                  'type' => "varchar(512) NOT NULL default ''"
               )
            )
         ),
         
         'model' => array(
            'modelclass'  => 'SystemUserModel',
            'cmodelclass' => 'CatalogsModel'
         )
      );
      
      return $dict;
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
      $sections = $this->getAllowedKinds();
      
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
         
         
         /* Check Owners config */
         
         if (!isset($conf['Owners']))
         {
            $valid[$catalog]['Owners'] = array();
         }
         elseif (!is_array($conf['Owners']))
         {
            $errors[$catalog]['global'][] = 'Owners configuration for catalog "'.$catalog.'" is wrong';
         }
         elseif (empty($conf['Owners']))
         {
            $errors[$catalog]['global'][] = 'Owners configuration for catalog "'.$catalog.'" is empty';
         }
         else
         {
            if (!$err = $this->checkOwnersConfig('catalogs', $conf['Owners']))
            {
               $valid[$catalog]['Owners'] = $conf['Owners'];
            }
            else $errors[$catalog]['Owners'] = $err;
         }
         
         
         /* Check Hierarchy config */
         
         $hierarchy = false;
         
         if (!isset($conf['Hierarchy']))
         {
            $valid[$catalog]['Hierarchy'] = array();
         }
         elseif (!is_array($conf['Hierarchy']))
         {
            $errors[$catalog]['global'][] = 'Hierarchy configuration for catalog "'.$catalog.'" is wrong';
         }
         elseif (empty($conf['Hierarchy']))
         {
            $errors[$catalog]['global'][] = 'Hierarchy configuration for catalog "'.$catalog.'" is empty';
         }
         else
         {
            if (!$err = $this->checkHierarchyConfig('catalogs', $conf['Hierarchy']))
            {
               $valid[$catalog]['Hierarchy'] = $conf['Hierarchy'];
               
               if (!empty($conf['Hierarchy'])) $hierarchy = true;
            }
            else $errors[$catalog]['Hierarchy'] = $err;
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
         
         /* Check BasisFor configuration */
         
         if (!isset($conf['basis_for']))
         {
            $valid[$catalog]['basis_for'] = array();
         }
         elseif (!is_array($conf['basis_for']))
         {
            $errors[$catalog]['global'][] = '"Basis for" configuration for catalog "'.$catalog.'" is wrong';
         }
         elseif (empty($conf['basis_for']))
         {
            $errors[$catalog]['global'][] = '"Basis for" configuration for catalog "'.$catalog.'" is empty';
         }
         else
         {
            if (!$err = $this->checkBasisForConfig($conf['basis_for']))
            {
               $valid[$catalog]['basis_for'] = $conf['basis_for'];
            }
            else $errors[$catalog]['basis_for'] = $err;
         }
         
         
         /* Check common config */
          
         if ($err = $this->checkCommonConfig('catalogs', $catalog, $conf))
         {
            $errors = isset($errors[$catalog]) && is_array($errors[$catalog]) ? array_merge($errors[$catalog], $err) : $err;
         }
         else
         {
            $valid[$catalog]['model']      = $conf['model'];
            $valid[$catalog]['controller'] = $conf['controller'];
            $valid[$catalog]['Forms']      = $conf['Forms'];
            $valid[$catalog]['Templates']  = $conf['Templates'];
            $valid[$catalog]['Layout']     = $conf['Layout'];
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
      return $this->checkRegistersConfig($config, 'information_registry');
   }
   
   /**
    * Check Accumulation Registers configuration array
    * 
    * @param array& $config - !!! метод вносит изменения в передаваемый массив
    * @return array - errors
    */
   protected function checkAccumulationRegistersConfig(array& $config)
   {
      return $this->checkRegistersConfig($config, 'AccumulationRegisters');
   }
   
   /**
    * Check Registers configuration array
    * 
    * @param array& $config - !!! метод вносит изменения в передаваемый массив
    * @param string $kind
    * @return array - errors
    */
   protected function checkRegistersConfig(array& $config, $kind)
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
         
         if ($kind == 'AccumulationRegisters')
         {
            /* Check register type */
             
            if (!isset($conf['register_type']))
            {
               $errors[$registry]['global'][] = 'Not set register type configuration for Accumulation Register "'.$registry.'"';
            }
            elseif (!is_string($conf['register_type']) || !in_array($conf['register_type'], array('Balances', 'Turnovers')))
            {
               $errors[$registry]['global'][] = 'Register type configuration for Accumulation Register "'.$registry.'" is wrong';
            }
            else
            {
               $valid[$registry]['register_type'] = $conf['register_type'];
            }
         }
         
         /* Check dimensions config */
         
         if (!isset($conf['dimensions']))
         {
            $valid[$registry]['dimensions'] = array();
         }
         elseif (!is_array($conf['dimensions']))
         {
            $errors[$registry]['global'][] = 'Dimensions configuration for '.$kind.' "'.$registry.'" is wrong';
         }
         elseif (empty($conf['dimensions']))
         {
            $errors[$registry]['global'][] = 'Dimensions configuration for '.$kind.' "'.$registry.'" is empty';
         }
         else
         {
            $check_duplicate = true;
            
            if (!$err = $this->checkFieldsConfig($kind, $conf['dimensions']))
            {
               $valid[$registry]['dimensions'] = $conf['dimensions'];
            }
            else $errors[$registry]['dimensions'] = $err;
         }

         /* Check periodical config */

         if ($kind == 'AccumulationRegisters')
         {
            $valid[$registry]['periodical'] = 'second';
         }
         else
         {
            if (isset($conf['periodical']))
            {
               if (!is_string($conf['periodical']) || !in_array($conf['periodical'], $this->getAllowedPeriods()))
               {
                  $errors[$registry]['global'][] = '"Periodical" configuration for '.$kind.' "'.$registry.'" is wrong';
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
         }

         
         /* Check fields config */
          
         if (!isset($conf['fields']))
         {
            $valid[$registry]['fields'] = array();
         }
         elseif (!is_array($conf['fields']))
         {
            $errors[$registry]['global'][] = 'Fields configuration for '.$kind.' "'.$registry.'" is wrong';
         }
         elseif (empty($conf['fields']))
         {
            $errors[$registry]['global'][] = 'Fields configuration for '.$kind.' "'.$registry.'" is empty';
         }
         else
         {
            $duplicate = false;

            if ($check_duplicate && $duplicate = array_intersect_key($conf['dimensions'], $conf['fields']))
            {
               $duplicate = true;
               $errors[$registry]['global'][] = 'Dimensions and Fields use one namespace. Duplicate name in '.$kind.' "'.$registry.'": '.implode(", ", array_keys($duplicate)).'.';
            }

            if ($check_periodical && isset($conf['fields']['Period']))
            {
               $duplicate = true;
               $errors[$registry]['global'][] = '"Period" - is system field name. Rename your field "Period".';
            }
            if (!$duplicate)
            {
               if (!$err = $this->checkFieldsConfig($kind, $conf['fields']))
               {
                  $valid[$registry]['fields'] = $conf['fields'];
               }
               else $errors[$registry]['fields'] = $err;
            }
         }

         /* Check Recorders config */
         
         if (!isset($conf['recorders']))
         {
            if ($kind == 'AccumulationRegisters')
            {
               $errors[$registry]['global'][] = 'Not set recorders configuration for Accumulation Register "'.$registry.'"';
            }
            else $valid[$registry]['recorders'] = array();
         }
         elseif (!is_array($conf['recorders']))
         {
            $errors[$registry]['global'][] = 'Recorders configuration for '.$kind.' "'.$registry.'" is wrong';
         }
         elseif (empty($conf['recorders']))
         {
            $errors[$registry]['global'][] = 'Recorders configuration for '.$kind.' "'.$registry.'" is empty';
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
          
         if ($err = $this->checkCommonConfig($kind, $registry, $conf))
         {
            $errors = isset($errors[$registry]) && is_array($errors[$registry]) ? array_merge($errors[$registry], $err) : $err;
         }
         else
         {
            $valid[$registry]['model']      = $conf['model'];
            $valid[$registry]['controller'] = $conf['controller'];
            $valid[$registry]['Forms']      = $conf['Forms'];
            $valid[$registry]['Templates']  = $conf['Templates'];
            $valid[$registry]['Layout']     = $conf['Layout'];
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
         
         /* Check BasisFor configuration */
         
         if (!isset($conf['basis_for']))
         {
            $valid[$document]['basis_for'] = array();
         }
         elseif (!is_array($conf['basis_for']))
         {
            $errors[$document]['global'][] = '"Basis for" configuration for document "'.$document.'" is wrong';
         }
         elseif (empty($conf['basis_for']))
         {
            $errors[$document]['global'][] = '"Basis for" configuration for document "'.$document.'" is empty';
         }
         else
         {
            if (!$err = $this->checkBasisForConfig($conf['basis_for']))
            {
               $valid[$document]['basis_for'] = $conf['basis_for'];
            }
            else $errors[$document]['basis_for'] = $err;
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
            $valid[$document]['model']      = $conf['model'];
            $valid[$document]['controller'] = $conf['controller'];
            $valid[$document]['Forms']      = $conf['Forms'];
            $valid[$document]['Templates']  = $conf['Templates'];
            $valid[$document]['Layout']     = $conf['Layout'];
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
            $valid[$name]['model']      = $conf['model'];
            $valid[$name]['controller'] = $conf['controller'];
            $valid[$name]['Forms']      = $conf['Forms'];
            $valid[$name]['Templates']  = $conf['Templates'];
            $valid[$name]['Layout']     = $conf['Layout'];
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
            $valid[$name]['model']      = $conf['model'];
            $valid[$name]['controller'] = $conf['controller'];
            $valid[$name]['Forms']      = $conf['Forms'];
            $valid[$name]['Templates']  = $conf['Templates'];
            $valid[$name]['Layout']     = $conf['Layout'];
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
            $valid[$type]['model']      = $conf['model'];
            $valid[$type]['controller'] = $conf['controller'];
            $valid[$type]['Forms']      = $conf['Forms'];
            $valid[$type]['Templates']  = $conf['Templates'];
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
    * Check AccessRights configuration
    * 
    * @param array& $config - !!! метод вносит изменения в передаваемый массив
    * @return array - errors
    */
   protected function checkAccessRightsConfig(array& $config)
   {
      $errors = array();
      $valid  = array();

      $config['Admin'] = array();
      
      foreach ($config as $role => $conf)
      {
         if (!$this->checkName($role))
         {
            $errors['global'][] = 'Invalid name "'.$role.'"';
         }
         
         /* Check entities permission configuration */
         
         if (!isset($conf['entities']))
         {
            $valid[$role]['entities'] = array();
         }
         elseif (!is_array($conf['entities']))
         {
            $errors['global'][] = 'Entities configuration for Role "'.$role.'" is wrong';
         }
         elseif (empty($conf['entities']))
         {
            $valid[$role]['entities'] = array();
         }
         else
         {
            $kinds = $this->getAllowedKinds();
            
            foreach ($conf['entities'] as $kind => $_conf)
            {
               if (!in_array($kind, $kinds))
               {
                  $errors[$role]['entities'][] = 'Unknow kind "'.$kind.'"';
               }
               elseif (empty($_conf) || !is_array($_conf))
               {
                  $errors[$role]['entities'][$kind] = 'Global configuration for "'.$kind.'" is wrong';
               }
               else
               {
                  foreach ($_conf as $type => $permissions)
                  {
                     if (!$err = $this->checkPermissionConfig($kind, $permissions))
                     {
                        $valid[$role]['entities'][$kind][$type] = $permissions;
                     }
                     else $errors[$role]['entities'][$kind][$type] = $err;
                  }
               }
            }
         }
         
         /* Check global permission configuration */
          
         if (!isset($conf['global']))
         {
            $valid[$role]['global'] = array();
         }
         elseif (!is_array($conf['global']))
         {
            $errors['global'][] = 'Global configuration for Role "'.$role.'" is wrong';
         }
         elseif (empty($conf['global']))
         {
            $valid[$role]['global'] = array();
         }
         else
         {
            if (!$err = $this->checkPermissionConfig('global', $conf['global']))
            {
               $valid[$role]['global'] = $conf['global'];
            }
            else $errors[$role]['global'] = $err;
         }
      }

      $config = $valid;
      unset($valid);

      return $errors;
   }
   
   /**
    * Check permissions configuration
    * 
    * @param string $kind
    * @param array& $permissions
    * @return array
    */
   protected function checkPermissionConfig($kind, array& $permissions)
   {
      $errors  = array();
      $valid   = array();
      $allowed = $this->getAllowedPermissions($kind);
      $diff    = array();

      foreach ($permissions as $permission => $value)
      {
         if (!isset($allowed[$permission]))
         {
            $diff[] = $permission;
            continue;
         }
          
         $valid[$permission] = $value ? true : false;
      }

      if (!empty($diff))
      {
         $errors[] = 'Not allowed permissions: '.implode(', ', $diff).'.';
         $valid = array();
      }
       
      $config = $valid;
      unset($valid);

      return $errors;
   }
   
   /**
    * Check Roles configuration
    * 
    * @param array& $config - !!! метод вносит изменения в передаваемый массив
    * @return array - errors
    */
   protected function checkRolesConfig(array& $config)
   {
      $errors = array();
      $valid  = array();

      foreach ($config as $login => $_conf)
      {
         if (!$this->checkName($login))
         {
            $errors['global'][] = 'Invalid Login "'.$login.'"';
         }

         if (!is_array($_conf))
         {
            $errors[$login][] = 'User "'.$login.'" configuration is wrong';
         }
         elseif (empty($_conf))
         {
            $errors[$login][] = 'User "'.$login.'" configuration is empty';
         }
         else
         {
            /* Check password */
            
            if (!isset($_conf['password']))
            {
               $errors[$login]['password'][] = 'User "'.$login.'" password is empty';
            }
            elseif (!is_string($_conf['password']))
            {
               $errors[$login]['password'][] = 'Invalid user "'.$login.'" password';
            }
            else $valid[$login]['password'] = $_conf['password'];
            
            
            /* Check roles */
            
            if (empty($_conf['roles']))
            {
               $errors[$login]['roles'][] = 'Roles configuration for user "'.$login.'" is empty';
            }
            elseif (!is_array($_conf['roles']))
            {
               $errors[$login]['roles'][] = 'Roles configuration for user "'.$login.'" is wrong';
            }
            else
            {
               foreach ($_conf['roles'] as $key => $role)
               {
                  if (empty($role))
                  {
                     $errors[$login]['roles'][] = 'Role name is empty';
                  }
                  elseif (!(is_string($role) && $this->checkName($role)))
                  {
                     $errors[$login]['roles'][] = 'Invalid role name "'.$role.'"';
                  }
                  else
                  {
                     $valid[$login]['roles'][$key] = $role;
                  }
               }
            }
         }
      }

      $config = $valid;
      unset($valid);

      return $errors;
   }
   
   /**
    * Check Constants configuration array
    * 
    * @param array& $config - !!! метод вносит изменения в передаваемый массив
    * @return array - errors
    */
   protected function checkConstantsConfig(array& $config)
   {
      if ($errors = $this->checkFieldsConfig('Constants', $config))
      {
         $config = array();
      }
      
      return $errors;
   }
   
   
   
   
   
   
   
   
   
   /**
    * Check Owners configuration
    * 
    * @param string $kind - entity kind
    * @param array& $config - !!! метод вносит изменения в передаваемый массив
    * @return array - errors
    */
   protected function checkOwnersConfig($kind, array& $config)
   {
      $errors = array();
      $valid  = array();
      
      foreach ($config as $key => $catalogs_type)
      {
         if (is_string($catalogs_type))
         {
            $valid[] = $catalogs_type;
         }
         else $errors[$key] = 'Configuration is wrong';
      }
      
      $config = $valid;
      unset($valid);
      
      return $errors;
   }
   
   /**
    * Check Hierarchy configuration
    * 
    * @param string $kind - entity kind
    * @param array& $config - !!! метод вносит изменения в передаваемый массив
    * @return array - errors
    */
   protected function checkHierarchyConfig($kind, array& $config)
   {
      $errors = array();
      $valid  = array();
      $allowed_types = $this->getAllowedHierarchyTypes();

      /* type */

      if (!isset($config['type']))
      {
         $errors[] = 'Not set hierarchy type';
      }
      elseif (!is_string($config['type']))
      {
         $errors[] = 'hierarchy type is wrong';
      }
      else
      {
         $type = ucfirst($config['type']);

         if (false === ($code = array_search($type, $allowed_types)))
         {
            $errors[] = 'Not supported hierarchy type';
            unset($type);
         }
         else
         {
            $valid['type'] = $code;
         }
      }
       
      $config = $valid;
      unset($valid);
      
      return $errors;
   }
   
   /**
    * Check fields configuration
    * 
    * @param string $kind    - entity kind
    * @param array& $config  - !!! метод вносит изменения в передаваемый массив
    * @return array - errors
    */
   protected function checkFieldsConfig($kind, array& $config)
   {
      $errors  = array();
      $valid   = array();
      $options = ($kind == 'catalogs') ? array('hierarchy' => true) : array();
      
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
            if (!$err = $this->checkReferenceConfig($conf, $options))
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
         
         if (!$err = $this->checkAttributeConfig($kind, $conf, $options))
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
    * @param array& $config  - !!! метод вносит изменения в передаваемый массив
    * @param array& $options
    * @return array - errors
    */
   protected function checkReferenceConfig(array& $config, array& $options = array())
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
      
      /* Use */
      
      if (!empty($options['hierarchy']))
      {
         if (isset($config['use']))
         {
            if (!$err = $this->checkUseConfig($config['use']))
            {
               $valid['use'] = $config['use'];
            }
            else
            {
               $errors = array_merge($errors, $err);
            }
         }
         else $valid['use'] = 1;
      }

      $config = $valid;
      unset($valid);

      return $errors;
   }
   
   /**
    * Check attribute configuration array
    * 
    * @param string $kind    - entity kind
    * @param array& $config  - !!! метод вносит изменения в передаваемый массив
    * @param array& $options
    * @return array - errors
    */
   protected function checkAttributeConfig($kind, array& $config, array& $options = array())
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
      
      /* Use */
      
      if (!empty($options['hierarchy']))
      {
         if (isset($config['use']))
         {
            if (!$err = $this->checkUseConfig($config['use']))
            {
               $valid['use'] = $config['use'];
            }
            else
            {
               $errors = array_merge($errors, $err);
            }
         }
         else $valid['use'] = 1;
      }
      
      $config = $valid;
      unset($valid);
      
      return $errors;
   }
   
   /**
    * Check use configuration
    * 
    * @param string& $config
    * @return array - errors
    */
   protected function checkUseConfig(& $config)
   {
      $errors = array();
      $valid  = array();
      $uses   = $this->getAllowedUses();
      
      if (!is_string($config))
      {
         $errors[] = 'Use type is wrong';
      }
      else
      {
         $use = ucfirst($config);
         
         if (false === ($code = array_search($use, $uses)))
         {
            $errors[] = 'Not supported use type';
            unset($use);
         }
         else
         {
            $valid = $code;
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
    * Check basis_for configuration
    * 
    * @param array& $config - !!! метод вносит изменения в передаваемый массив
    * @return array - errors
    */
   protected function checkBasisForConfig($config)
   {
      $errors = array();
      $valid  = array();
      
      foreach ($config as $key => $uid)
      {
         if (is_string($uid))
         {
            $valid[] = $uid;
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
         if ($kind == 'catalogs')
         {
            if (!empty($conf['Owners']))
            {
               $mtype = 'slave';
            }
            
            if (!empty($conf['Hierarchy']))
            {
               $mtype = empty($mtype) ? 'hierarchy' : $mtype.'_and_hierarchy';
            }
         }
         
         if (empty($mtype)) $mtype = 'base';
         
         $valid = $this->getDefaultModel($kind, $mtype);
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
      $valid = array();

      
      /* Check Forms config */
      
      if (!isset($conf['Forms']))
      {
         $valid = array();
      }
      elseif (!is_array($conf['Forms']))
      {
         $errors['global'][] = 'Forms configuration for "'.$kind.'.'.$type.'" is wrong';
      }
      elseif (empty($conf['Forms']))
      {
         $errors['global'][] = 'Forms configuration for "'.$kind.'.'.$type.'" is empty';
      }
      else
      {
         if ($err = $this->checkFormsConfig($kind, $conf['Forms']))
         {
            $errors['Forms'] = $err;
         }
         else
         {
            $valid = $conf['Forms'];
         }
      }
       
      $conf['Forms'] = $valid;
      $valid = array();
      
      
      /* Check Templates config */
      
      if (!isset($conf['Templates']))
      {
         $valid = array();
      }
      elseif (!is_array($conf['Templates']))
      {
         $errors['global'][] = 'Templates configuration for "'.$kind.'.'.$type.'" is wrong';
      }
      elseif (empty($conf['Templates']))
      {
         $errors['global'][] = 'Templates configuration for "'.$kind.'.'.$type.'" is empty';
      }
      else
      {
         if ($err = $this->checkTemplatesConfig($conf['Templates']))
         {
            $errors['Templates'] = $err;
         }
         else
         {
            $valid = $conf['Templates'];
         }
      }
       
      $conf['Templates'] = $valid;
      unset($valid);
      
      
      /* Check Layout config */
      
      if (!isset($conf['Layout']))
      {
         $valid = array();
      }
      elseif (!is_array($conf['Layout']))
      {
         $errors['global'][] = 'Layout configuration for "'.$kind.'.'.$type.'" is wrong';
      }
      elseif (empty($conf['Layout']))
      {
         $errors['global'][] = 'Layout configuration for "'.$kind.'.'.$type.'" is empty';
      }
      else
      {
         if ($err = $this->checkTemplatesConfig($conf['Layout']))
         {
            $errors['Layout'] = $err;
         }
         else
         {
            $valid = $conf['Layout'];
         }
      }
       
      $conf['Layout'] = $valid;
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
    * Check Forms configuration array
    * 
    * @param string $kind   - entity kind
    * @param array& $config - !!! метод вносит изменения в передаваемый массив
    * @return array - errors
    */
   protected function checkFormsConfig($kind, array& $config)
   {
      $errors = array();
      $valid  = array();
      
      $ftypes = $this->getAllowedFormTypes($kind);
      
      foreach ($config as $ftype => $params)
      {
         $err = array();
         
         if (!in_array($ftype, $ftypes))
         {
            $err[] = 'Unknow form type '.$ftype;
         }
         elseif (!is_array($params))
         {
            $err[] = 'Invalid configuration for '.$ftype;
         }
         elseif ($ftype == 'Custom')
         {
            if (empty($params))
            {
               $err[] = 'Custom form configuration is empty';
            }
            elseif (!is_array($params))
            {
               $err[] = 'Custom form configuration is wrong';
            }
            else
            {
               foreach ($params as $fname)
               {
                  if (in_array($fname, $ftypes) || !$this->checkName($fname))
                  {
                     $err[] = 'Invalid custom form name "'.$form.'"';
                  }
               }
            }
         }
         
         if ($err)
         {
            $errors['global'] = empty($errors['global']) ? $err : array_merge($errors['global'], $err);
         }
         else
         {
            $valid[$ftype] = $params;
         }
      }

      $config = $valid;
      unset($valid);
      
      return $errors;
   }
   
   /**
    * Check Templates configuration array
    * 
    * @param array& $config - !!! метод вносит изменения в передаваемый массив
    * @return array - errors
    */
   protected function checkTemplatesConfig(array& $config)
   {
      $errors = array();
      $valid  = array();
      
      foreach ($config as $template)
      {
         if (!is_string($template))
         {
            $errors['global'][] = 'Template configuration is wrong';
         }
         elseif (!$this->checkName($template))
         {
            $errors['global'][] = 'Invalid Template name "'.$template.'"';
         }
         else
         {
            $valid[] = $template;
         }
      }

      $config = $valid;
      unset($valid);
      
      return $errors;
   }
   
   /**
    * Check Layout configuration array
    * 
    * @param array& $config - !!! метод вносит изменения в передаваемый массив
    * @return array - errors
    */
   protected function checkLayoutConfig(array& $config)
   {
      $errors = array();
      $valid  = array();
      
      foreach ($config as $template)
      {
         if (!is_string($template))
         {
            $errors['global'][] = 'Layout configuration is wrong';
         }
         elseif (!$this->checkName($template))
         {
            $errors['global'][] = 'Invalid Layout name "'.$template.'"';
         }
         else
         {
            $valid[] = $template;
         }
      }

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
    * Return allowed entity kinds
    * 
    * @return array
    */
   protected function getAllowedKinds()
   {
      return array(
         'catalogs',
         'documents',
         'information_registry',
         'AccumulationRegisters',
         'reports',
         'data_processors',
         'web_services',
         'AccessRights',
         'Roles',
         'Constants'
      );
   }
   
   /**
    * Return entity kinds that can't storage (not have table in db)
    * 
    * @return array
    */
   protected function getNotStorage()
   {
      return array('reports', 'data_processors', 'web_services', 'AccessRights', 'Roles');
   }
   
   /**
    * Return object entity kinds
    * 
    * @return array
    */
   protected function getObjectTypes()
   {
      return array('catalogs', 'documents');
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
    * Return list of allowed hierarchy types
    * 
    * @return array
    */
   protected function getAllowedHierarchyTypes()
   {
      return array(1 => 'Item', 2 => 'Folder and item');
   }
   
   /**
    * Return list of allowed attribute uses types
    * 
    * @return array
    */
   protected function getAllowedUses()
   {
      return array(1 => 'For item', 2 => 'For folder', 3 => 'For folder and item');
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
    * Get list allowed permissions
    * 
    * @param string $kind
    * @return array
    */
   public function getAllowedPermissions($kind = null)
   {
      if (!isset($this->_permissions))
      {
         $this->_permissions = $this->loadConfigFromFile(self::p_config_dir.'permissions.php');
      }
      
      if (!empty($kind)) return isset($this->_permissions[$kind]) ? $this->_permissions[$kind] : array();
      
      return $this->_permissions;
   }
   
   /**
    * Get default model configuration
    * 
    * @param string $kind  - entity kind
    * @param string $mtype - model type
    * @return array
    */
   public function getDefaultModel($kind, $mtype = 'base')
   {
      if (!isset($this->_default_models))
      {
         $this->_default_models = $this->loadConfigFromFile(self::p_config_dir.'default_models.php');
      }
      
      if (empty($this->_default_models[$kind][$mtype]))
      {
         throw new Exception(__METHOD__.": Default model configuration is wrong");
      }
      
      return $this->_default_models[$kind][$mtype];
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
    * Get allowed form types
    * 
    * @param string $kind - entity kind
    * @return array
    */
   public function getAllowedFormTypes($kind)
   {
      if (!isset($this->_allowed_form_types))
      {
         $this->_allowed_form_types = $this->loadConfigFromFile(self::p_config_dir.'form_types.php');
      }
      
      return empty($this->_allowed_form_types[$kind]) ? array() : $this->_allowed_form_types[$kind];
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
      $map['container'] = $this->container_path;
      
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
      $map['Constants']               = $this->saveInternalConfiguration($result['Constants'],               'Constants/');
      $map['security']                = $this->saveInternalConfiguration($result['AccessRights'],            'security/');
      $map['information_registry']    = $this->saveInternalConfiguration($result['information_registry'],    'information_registry/');
      $map['AccumulationRegisters']   = $this->saveInternalConfiguration($result['AccumulationRegisters'],   'AccumulationRegisters/');
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
      
      // Basis for and Input on basis
      if (!$errors)
      {
         $errors = $this->processBasis($internal, 'catalogs');
      }
      
      
      /* Information registry */
      
      $result = $this->generateRegistersInternalConfiguration('information_registry', $dictionary['information_registry'], $options);
      
      if (!isset($result['errors']))
      {
         $internal['information_registry'] = $result;
      }
      else $errors = array_merge($errors, $result['errors']);
      
      
      /* Accumulation Registers */
      
      $result = $this->generateRegistersInternalConfiguration('AccumulationRegisters', $dictionary['AccumulationRegisters'], $options);
      
      if (!isset($result['errors']))
      {
         $internal['AccumulationRegisters'] = $result;
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
      
      if (!$errors)
      {
         // Recorders and Recorder for
         foreach ($internal['documents']['recorders'] as $r_kind => $conf)
         {
            foreach ($conf as $r_type => $recorder)
            {
               if (isset($internal[$r_kind]['recorders'][$r_type]))
               {
                  $internal[$r_kind]['recorders'][$r_type] = array_merge($recorder, $internal[$r_kind]['recorders'][$r_type]);
                  $internal[$r_kind]['recorders'][$r_type] = array_unique($internal[$r_kind]['recorders'][$r_type]);
               }
               else $internal[$r_kind]['recorders'][$r_type] = $recorder;
            }
         }
         unset($internal['documents']['recorders']);
         
         $register_kinds = array(
            'information_registry',
            'AccumulationRegisters'
         );
         
         foreach ($register_kinds as $reg_kind)
         {
            foreach ($internal[$reg_kind]['recorder_for'] as $doc_type => $reg_types)
            {
               if (isset($internal['documents']['recorder_for'][$doc_type][$reg_kind]))
               {
                  $dr =& $internal['documents']['recorder_for'][$doc_type][$reg_kind];
                  $dr = array_merge($reg_types, $dr);
                  $dr = array_unique($dr);
               }
               else $internal['documents']['recorder_for'][$doc_type][$reg_kind] = $reg_types;
            }
            unset($internal[$reg_kind]['recorder_for']);
         }
         
         // Basis for and Input on basis
         $errors = $this->processBasis($internal, 'documents');
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
      
      
      /* AccessRights */
      
      $result = $this->generateAccessRightsInternalConfiguration($dictionary['AccessRights'], $options);
      
      if (!isset($result['errors']))
      {
         $internal['AccessRights'] = $result;
      }
      else $errors = array_merge($errors, $result['errors']);
      
      
      /* Constants */
      
      $result = $this->generateConstantsInternalConfiguration($dictionary['Constants'], $options);
      
      if (!isset($result['errors']))
      {
         $internal['Constants'] = $result;
      }
      else $errors = array_merge($errors, $result['errors']);
      
      
      return ($errors) ? array('errors' => $errors) : $internal; 
   }
   
   /**
    * Processing Basic for and Input on basic configurations
    * 
    * @param array& $internal - internal configuration
    * @param string $kind     - entity kind
    * @return array - errors
    */
   protected function processBasis(array& $internal, $kind)
   {
      foreach ($internal[$kind]['basis_for'] as $type => $r_kinds)
      {
         foreach ($r_kinds as $r_kind => $r_types)
         {
            foreach ($r_types as $r_type)
            {
               if (!isset($internal[$r_kind]['input_on_basis'][$r_type][$kind]))
               {
                  $internal[$r_kind]['input_on_basis'][$r_type][$kind] = array($type);
               }
               elseif (!in_array($type, $internal[$r_kind]['input_on_basis'][$r_type][$kind]))
               {
                  $internal[$r_kind]['input_on_basis'][$r_type][$kind][] = $type;
               }
            }
         }
      }
      
      return array();
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
         'dynamic'    => array(),
         'model'      => array(),
         'controller' => array(),
         'forms'      => array(),
         'forms_view' => array(),
         'templates'  => array(),
         'layout'     => array()
      );
      
      $is_obj_type = false;
      $_obj_types  = $this->getObjectTypes();
      
      if (in_array($kind, $_obj_types))
      {
         $is_obj_type = true;
         
         $result['basis_for'] = array();
         $result['input_on_basis'] = array();
         $result['files'] = array();
      }
      
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
         elseif ($kind == 'catalogs')
         {
            $result['owners']    = array();
            $result['hierarchy'] = array();
            $result['field_use'] = array();
         }
      }
      
      foreach ($entity_dictionary as $type => $params)
      {
         $result[$kind][] = $type;
         
         
         /* Owners and Hierarchy */
         
         if ($kind == 'catalogs')
         {
            // Owners
            foreach ($params['Owners'] as &$uid)
            {
               if (isset($this->dictionary['catalogs'][$uid]))
               {
                  $result['owners'][$type][] = $uid;
                  
                  $uid = ucfirst($uid);
                  
                  continue;
               }
               
               try
               {
                  list($o_kind, $o_type) = Utility::parseUID($uid);
                  
                  if ($o_kind != 'catalogs')
                  {
                     $errors[] = 'Invalid owner '.$uid.' for '.$kind.'.'.$type;
                  }
                  elseif (!isset($this->dictionary[$o_kind][$o_type]))
                  {
                     $errors[] = $uid.' not exists';
                  }
                  else
                  {
                     $result['owners'][$type][] = $o_type;
                     
                     $uid = ucfirst($o_type);
                  }
               }
               catch (Exception $e)
               {
                  $errors[] = 'Catalog "'.$type.'": invalid owner uid '.$uid;
               }
            }
            
            // Hierarchy
            $result['hierarchy'][$type] = $params['Hierarchy'];
         }
         
         
         /* Fields */
         
         $add_fields = array();
         
         if (in_array($kind, $this->getObjectTypes()))
         {
            $clength = empty($params['fields']['Code']['precision']['max_length']) ? 5 : $params['fields']['Code']['precision']['max_length'];
            $add_fields['Code'] = array(
               'type' => 'string',
               'use'  => 3,
               'sql'  => array(
                  'type' => "varchar(".$clength.") NOT NULL"
               ),
               'precision' => array(
                  'required'   => true,
                  'max_length' => $clength
               )
            );
            unset($params['fields']['Code']);
         }
         
         if ($kind == 'catalogs')
         {
            $add_fields['Description'] = array(
               'type' => 'string',
               'use'  => 3,
               'sql'  => array(
                  'type' => "varchar(255) NOT NULL"
               ),
               'precision' => array('required' => true)
            );
            unset($params['fields']['Description']);
            
            // Owner attributes
            if (!empty($params['Owners']))
            {
               sort($params['Owners']);
               array_unshift($params['Owners'], ' ');
               unset($params['Owners'][0]);
               
               $add_fields['OwnerType'] = array(
                  'type' => 'string',
                  'use'  => 3,
                  'sql'  => array(
                     'type' => "varchar(128) NOT NULL"
                  ),
                  'precision' => array(
                     'required' => true,
                     'in' => $params['Owners']
                  )
               );
               
               $add_fields['OwnerId'] = array(
                  'type' => 'int',
                  'use'  => 3,
                  'sql'  => array(
                     'type' => "int(11) NOT NULL default 0"
                  ),
                  'precision' => array(
                     'required' => true
                  )
               );
               
               unset(
                  $params['fields']['OwnerType'],
                  $params['fields']['OwnerId']
               );
            }
            
            // Hierarchy attributes
            if (!empty($params['Hierarchy']))
            {
               $add_fields['Parent'] = array(
                  'reference' => 'catalogs.'.$type,
                  'use'  => 3
               );
               
               unset($params['fields']['Parent']);
            }
         }
         elseif ($kind == 'documents')
         {
            $field = 'Date';
            $add_fields[$field] = array(
               'type' => 'datetime',
               'sql'  => array(
                  'type' => "DATETIME NOT NULL default '0000-00-00'"
               ),
               'precision' => array('required' => true)
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
            
            if (isset($res['field_use']))
            {
               $result['field_use'][$type] = $res['field_use'];
            }
            
            if ($is_obj_type)
            {
               $result['files'][$type] = $res['files'];
            }
            
            $result['fields'][$type]     = $res['fields'];
            $result['field_type'][$type] = $res['field_type'];
            $result['field_prec'][$type] = $res['field_prec'];
            $result['references'][$type] = $res['references'];
            $result['required'][$type]   = $res['required'];
            $result['dynamic'][$type]    = $res['dynamic'];
         }
         else $errors = array_merge($errors, $res['errors']);
         
         
         /* Recorder for */
         
         if ($kind == 'documents')
         {
            foreach ($params['recorder_for'] as $uid)
            {
               try
               {
                  list($r_kind, $r_type) = Utility::parseUID($uid);
                  
                  if (isset($this->dictionary[$r_kind][$r_type]))
                  {
                     $result['recorder_for'][$type][$r_kind][] = $r_type;
                     $result['recorders'][$r_kind][$r_type][]  = $type;
                  }
                  else $errors[] = $r_kind.' register "'.$r_type.'" not exists';
               }
               catch (Exception $e)
               {
                  $errors[] = 'Document "'.$type.'": invalid recorder uid "'.$uid.'"';
               }
            }
         }
         
         
         /* Basis for */
         
         if ($is_obj_type)
         {
            foreach ($params['basis_for'] as $uid)
            {
               try
               {
                  list($r_kind, $r_type) = Utility::parseUID($uid);
                  
                  if (!in_array($r_kind, $_obj_types))
                  {
                     $errors[] = 'Invalid kind'; 
                  }
                  elseif (!isset($this->dictionary[$r_kind][$r_type]))
                  {
                     $errors[] = $uid.' not exists';
                  }
                  else
                  {
                     $result['basis_for'][$type][$r_kind][] = $r_type;
                  }
               }
               catch (Exception $e)
               {
                  $errors[] = $kind.'.'.$type.': invalid basis for uid "'.$uid.'"';
               }
            }
         }
         
         /* Model */
         
         $result['model'][$type] = $params['model'];
         
         /* Controller */
         
         $result['controller'][$type] = $params['controller'];
         
         /* Templates */
         
         $result['templates'][$type] = $params['Templates'];
         
         /* Layout */
         
         $result['layout'][$type] = $params['Layout'];
         
         /* Forms */
         
         $res = $this->generateFormsInternalConfiguration($params['Forms'], $kind, $res, $options);
         
         if (!isset($res['errors']))
         {
            $result['forms'][$type]      = $res['forms'];
            $result['forms_view'][$type] = $res['forms_view'];
         }
         else $errors = array_merge($errors, $res['errors']);
         
         
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
      }
      
      return empty($errors) ? $result : array('errors' => $errors);
   }
   
   /**
    * Generate Regicters internal configuration
    * 
    * @param string $kind - registers kind
    * @param array& $dict - registers dictionary
    * @param array& $options
    * @return array - 'errors' => $errors or list <confName> => array()
    */
   protected function generateRegistersInternalConfiguration($kind, array& $dict, array& $options = array())
   {
      $errors = array();
      $result = array(
         "$kind"      => array(),
         'dimensions' => array(),
         'periodical' => array(),
         'fields'     => array(),
         'field_sql'  => array(),
         'field_type' => array(),
         'field_prec' => array(),
         'references' => array(),
         'required'   => array(),
         'dynamic'    => array(),
         'files'      => array(),
         'recorders'  => array(),
         'model'      => array(),
         'controller' => array(),
         'forms'      => array(),
         'forms_view' => array(),
         'templates'  => array(),
         'layout'     => array()
      );
      
      $result['recorder_for'] = array();
      
      if ($kind == 'AccumulationRegisters')
      {
         $result['register_type'] = array();
      }
      
      foreach ($dict as $registry => $params)
      {
         $result[$kind][] = $registry;
         
         if ($kind == 'AccumulationRegisters')
         {
            $result['register_type'][$registry] = $params['register_type'];
         }
         
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
            $field = 'Period';
            $per_field = array(
               $field => array(
                  'type' => 'datetime',
                  'sql'  => array(
                     'type' => "DATETIME NOT NULL default '0000-00-00 00:00:00'"
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
         
         $res = $this->generateFieldsInternalConfiguration($_fields, $kind, $options);
         
         unset($_fields);
         
         if (!isset($res['errors']))
         {
            $result['fields'][$registry]     = $res['fields'];
            $result['field_sql'][$registry]  = $res['field_sql'];
            $result['field_type'][$registry] = $res['field_type'];
            $result['field_prec'][$registry] = $res['field_prec'];
            $result['references'][$registry] = $res['references'];
            $result['required'][$registry]   = $res['required'];
            $result['dynamic'][$registry]    = $res['dynamic'];
            $result['files'][$registry]      = $res['files'];
         }
         else $errors = array_merge($errors, $res['errors']);
         
         
         /* Recorders */
         
         foreach ($params['recorders'] as $doc_type)
         {
            if (isset($this->dictionary['documents'][$doc_type]))
            {
               $result['recorders'][$registry][]    = $doc_type;
               $result['recorder_for'][$doc_type][] = $registry;
            }
            else $errors[] = $kind.' register "'.$registry.'": documents "'.$doc_type.'" not exists.';
         }
         
         
         /* Model */
         
         $result['model'][$registry] = $params['model'];
         
         /* Controller */
         
         $result['controller'][$registry] = $params['controller'];
         
         /* Templates */
         
         $result['templates'][$registry] = $params['Templates'];
         
         /* Layout */
         
         $result['layout'][$registry] = $params['Layout'];
         
         /* Forms */
         
         $res = $this->generateFormsInternalConfiguration($params['Forms'], $kind, $res, $options);
         
         if (!isset($res['errors']))
         {
            $result['forms'][$registry]      = $res['forms'];
            $result['forms_view'][$registry] = $res['forms_view'];
         }
         else $errors = array_merge($errors, $res['errors']);
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
         'dynamic'    => array(),
         'files'      => array(),
         'model'      => array(),
         'controller' => array(),
         'forms'      => array(),
         'forms_view' => array(),
         'templates'  => array(),
         'layout'     => array()
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
         
         $res = $this->generateFieldsInternalConfiguration($params['fields'], $kind.'.'.$type.'.tabulars', $options);
          
         if (!isset($res['errors']))
         {
            $result['fields'][$type][$tabular]     = $res['fields'];
            $result['field_sql'][$type][$tabular]  = $res['field_sql'];
            $result['field_type'][$type][$tabular] = $res['field_type'];
            $result['field_prec'][$type][$tabular] = $res['field_prec'];
            $result['references'][$type][$tabular] = $res['references'];
            $result['required'][$type][$tabular]   = $res['required'];
            $result['dynamic'][$type][$tabular]    = $res['dynamic'];
            $result['files'][$type][$tabular]      = $res['files'];
         }
         else $errors = array_merge($errors, $res['errors']);
         
         /* Model */
         
         $result['model'][$type][$tabular] = $params['model'];
         
         /* Controller */
         
         $result['controller'][$type][$tabular] = $params['controller'];
         
         /* Templates */
         
         $result['templates'][$type][$tabular] = $params['Templates'];
         
         /* Layout */
         
         $result['layout'][$type][$tabular] = $params['Layout'];
         
         /* Forms */
         
         $res = $this->generateFormsInternalConfiguration($params['Forms'], 'tabular_sections', $res, $options);
         
         if (!isset($res['errors']))
         {
            $result['forms'][$type][$tabular]      = $res['forms'];
            $result['forms_view'][$type][$tabular] = $res['forms_view'];
         }
         else $errors = array_merge($errors, $res['errors']);
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
    * Generate AccessRights internal configuration
    * 
    * @param array& $dict
    * @param array& $options
    * @return array - 'errors' => $errors or list <confName> => array()
    */
   protected function generateAccessRightsInternalConfiguration(array& $dict, array& $options = array())
   {
      $errors = array();
      $result = array(
         'roles'       => array(),
         'permissions' => array()
      );
      
      foreach ($dict as $role => $config)
      {
         $result['roles'][] = $role;
         
         /* Entities */
         
         
         foreach ($config['entities'] as $kind => $conf)
         {
            switch ($kind)
            {
               case 'catalogs':
               case 'documents':
                  
                  foreach ($conf as $type => $permissions)
                  {
                     // Read
                     if (empty($permissions['Read']))
                     {
                        continue;
                     }
                     // Edit
                     if (empty($permissions['Insert']))
                     {
                        if (empty($permissions['Update']))
                        {
                           unset(
                              $permissions['InteractiveInsert'],
                              $permissions['Edit']
                           );
                        }
                        else unset($permissions['InteractiveInsert']);
                     }
                     elseif (empty($permissions['Update']))
                     {
                        unset($permissions['Edit']);
                     }
                     // Delete
                     if (empty($permissions['Delete']))
                     {
                        unset(
                           $permissions['InteractiveDelete'],
                           $permissions['InteractiveMarkForDeletion'],
                           $permissions['InteractiveUnmarkForDeletion'],
                           $permissions['InteractiveDeleteMarked']
                        );
                     }
                     elseif (empty($permissions['InteractiveDelete']))
                     {
                        unset(
                           $permissions['InteractiveMarkForDeletion'],
                           $permissions['InteractiveUnmarkForDeletion'],
                           $permissions['InteractiveDeleteMarked']
                        );
                     }
                     elseif (empty($permissions['InteractiveDeleteMarked']))
                     {
                        unset(
                           $permissions['InteractiveMarkForDeletion'],
                           $permissions['InteractiveUnmarkForDeletion']
                        );
                     }
                     // View
                     if (empty($permissions['View']))
                     {
                        unset(
                           $permissions['Edit'],
                           $permissions['InteractiveInsert'],
                           $permissions['InteractiveDelete'],
                           $permissions['InteractiveMarkForDeletion'],
                           $permissions['InteractiveUnmarkForDeletion'],
                           $permissions['InteractiveDeleteMarked']
                        );
                     }
                     // Post
                     if ($kind == 'documents')
                     {
                        if (empty($permissions['Posting'])) unset($permissions['InteractivePosting']);
                        if (empty($permissions['UndoPosting'])) unset($permissions['InteractiveUndoPosting']);
                     }
                     
                     foreach ($permissions as $name => $value)
                     {
                        if ($value) $result['permissions'][$role][$kind.'.'.$type.'.'.$name] = true;
                     }
                  }
                  break;
                  
               
               case 'information_registry':
                  
                  foreach ($conf as $type => $permissions)
                  {
                     // Read
                     if (empty($permissions['Read']))
                     {
                        continue;
                     }
                     // Edit
                     if (empty($permissions['Update']))
                     {
                        unset($permissions['Edit']);
                     }
                     // View
                     if (empty($permissions['View']))
                     {
                        unset($permissions['Edit']);
                     }
                     
                     foreach ($permissions as $name => $value)
                     {
                        if ($value) $result['permissions'][$role][$kind.'.'.$type.'.'.$name] = true;
                     }
                  }
                  break;
               
               
               case 'reports':
               case 'data_processors':
                  
                  foreach ($conf as $type => $permissions)
                  {
                     if (empty($permissions['Use'])) continue;
                     
                     foreach ($permissions as $name => $value)
                     {
                        if ($value) $result['permissions'][$role][$kind.'.'.$type.'.'.$name] = true;
                     }
                  }
                  break;
               
               
               case 'web_services':
                  ;
                  break;
               
               
               default:
                  throw new Exception(__METHOD__.': Unknow entity kind "'.$kind.'"');
            }
         }
         
         /* Global */
         
         foreach ($config['global'] as $permission => $value)
         {
            if ($value) $result['permissions'][$role]['global.'.$permission] = true;
         }
      }
      
      return empty($errors) ? $result : array('errors' => $errors);
   }
   
   /**
    * Generate Constants internal configuration
    * 
    * @param array& $dict
    * @param array& $options
    * @return array - 'errors' => $errors or list <confName> => array()
    */
   protected function generateConstantsInternalConfiguration(array& $dict, array& $options = array())
   {
      $ret  = array();
      $type = null; 
      $res  = $this->generateFieldsInternalConfiguration($dict, 'Constants', $options);
      
      if (!isset($res['errors']))
      {
         $ret['fields'][$type]     = $res['fields'];
         $ret['field_sql'][$type]  = $res['field_sql'];
         $ret['field_type'][$type] = $res['field_type'];
         $ret['field_prec'][$type] = $res['field_prec'];
         $ret['references'][$type] = $res['references'];
         $ret['required'][$type]   = $res['required'];
         $ret['dynamic'][$type]    = $res['dynamic'];
         
         $ret['model'][$type]      = array('modelclass' => 'ConstantModel');
         $ret['controller'][$type] = array('classname'  => 'Constants');
      }
      else $ret = array('errors' => $res['errors']);
      
      return $ret;
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
         'required'   => array(),
         'dynamic'    => array(),
         'files'      => array()
      );
      
      if ($kind == 'catalogs')
      {
         $result['field_use'] = array();
      }
      
      foreach ($fields_dictionary as $name => $params)
      {
         $result['fields'][] = $name;

         if (isset($params['reference'])) // Link
         {
            // Link params
            if (!strpos($params['reference'], "."))
            {
               $_pos   = strpos($kind, '.');
               $r_kind = $_pos ? substr($kind, 0, $_pos) : $kind;
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
            // Required
            if (isset($params['precision']['required']))
            {
               if ($params['precision']['required']) $result['required'][] = $name;
               unset($params['precision']['required']); 
            }
            
            // Dynamic update
            if (isset($params['precision']['dynamic_update']))
            {
               if ($params['precision']['dynamic_update']) $result['dynamic'][] = $name;
               unset($params['precision']['dynamic_update']); 
            }
            
            // Other
            if (!empty($params['precision'])) $result['field_prec'][$name] = $params['precision'];
         }
         
         if (isset($params['type']) && $params['type'] == 'file')
         {
            $result['files'][$name] = $result['field_prec'][$name];
            $result['field_prec'][$name] = array();
         }
         
         
         /* Use */
         
         if (isset($result['field_use']))
         {
            if (isset($params['use']))
            {
               switch ($params['use'])
               {
                  case 1:
                     $result['field_use'][SystemConstants::USAGE_WITH_ITEM][] = $name;
                     break;
                  case 2:
                     $result['field_use'][SystemConstants::USAGE_WITH_FOLDER][] = $name;
                     break;
                  case 3:
                     $result['field_use'][SystemConstants::USAGE_WITH_ITEM][]   = $name;
                     $result['field_use'][SystemConstants::USAGE_WITH_FOLDER][] = $name;
                     break;
                     
                  default:
                     $errors[] = 'Invalid use type for attribute '.$name;
               }
            }
            else $errors[] = 'Unknow use type for attribute '.$name;
         }
      }

      return empty($errors) ? $result : array('errors' => $errors);
   }
   
   /**
    * Generate form internal configuration
    * 
    * @param array& $form_dictionary
    * @param string $ckind
    * @param array& $internal
    * @param array& $options
    * @return array
    */
   protected function generateFormsInternalConfiguration(array& $form_dictionary, $ckind, array& $internal, array& $options = array())
   {
      $errors = array();
      $result = array(
         'forms'      => array(),
         'forms_view' => array()
      );
      
      $allowed = $this->getAllowedFormTypes($ckind);
      
      foreach ($allowed as $ftype)
      {
         if ($ftype == 'Custom')
         {
            if (empty($form_dictionary[$ftype])) continue;
            
            $conf =& $form_dictionary[$ftype];
            
            foreach ($conf as $fname)
            {
               $result['forms'][] = $fname;
               $result['forms_view'][$fname] = array();
            }
            
            continue;
         }
         
         if (isset($form_dictionary[$ftype]))
         {
            $result['forms'][] = $ftype;
            
            $conf =& $form_dictionary[$ftype];
         }
         else $conf = array();
         
         if (empty($conf['columns']))
         {
            $conf['columns'] = $internal['fields'];
         }
         
         $result['forms_view'][$ftype] = $conf;
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
      $catalogs    =& $configuration['catalogs']['catalogs'];
      $iRegistries =& $configuration['information_registry']['information_registry'];
      $iRecorders  =& $configuration['information_registry']['recorders'];
      $aRegistries =& $configuration['AccumulationRegisters']['AccumulationRegisters'];
      $aRegType    =& $configuration['AccumulationRegisters']['register_type'];
      $aRecorders  =& $configuration['AccumulationRegisters']['recorders'];
      $documents   =& $configuration['documents']['documents'];
      $constants   =& $configuration['Constants'];
      
      
      if (!empty($options['dbprefix']))  $dbprefix = $options['dbprefix'];
      
      /* Catalogs */
      
      foreach ($catalogs as $catalog)
      {
         $db_map['catalogs'][$catalog]['table'] = $dbprefix.'CAT_'.$catalog;
         $db_map['catalogs'][$catalog]['pkey']  = '_id';
         $db_map['catalogs'][$catalog]['deleted'] = '_deleted';
         
         $_conf =& $configuration['catalogs']['hierarchy'];
         
         if (!empty($_conf[$catalog]) && $_conf[$catalog]['type'] == 2)
         {
            $db_map['catalogs'][$catalog]['folder'] = '_folder';
         }
         
         /* Tabular sections */
         
         $t_map = array();
         $tabulars =& $configuration['catalogs']['tabulars']['tabulars'];
         
         if (!empty($tabulars[$catalog]))
         {
            foreach ($tabulars[$catalog] as $tabular)
            {
               $t_map[$tabular]['table'] = $dbprefix.'CAT_'.$catalog.'_TS_'.$tabular;
               $t_map[$tabular]['pkey']  = '_id';
            }
         }
         
         $db_map['catalogs'][$catalog]['tabulars'] = $t_map;
      }
      
      /* Information registry */
      
      foreach ($iRegistries as $register)
      {
         $db_map['information_registry'][$register]['table'] = $dbprefix.'IR_'.$register;
         $db_map['information_registry'][$register]['pkey']  = '_id';
         
         // Recorders
         if (array_key_exists($register, $iRecorders))
         {
            $db_map['information_registry'][$register]['recorder_type'] = '_rec_type';
            $db_map['information_registry'][$register]['recorder_id'] = '_rec_id';
         }
      }
      
      /* AccumulationRegisters */
      
      foreach ($aRegistries as $register)
      {
         $db_map['AccumulationRegisters'][$register]['table']  = $dbprefix.'AR_'.$register;
         $db_map['AccumulationRegisters'][$register]['pkey']   = '_id';
         $db_map['AccumulationRegisters'][$register]['line']   = '_line';
         $db_map['AccumulationRegisters'][$register]['active'] = '_active';
         $db_map['AccumulationRegisters'][$register]['total']  = array();
         
         $total_map =& $db_map['AccumulationRegisters'][$register]['total'];
         
         $total_map['table'] = $dbprefix.'AR_'.$register.'_total';
         $total_map['pkey']  = '_id';
         
         // Register type
         if ($aRegType[$register] == 'Balances')
         {
            $db_map['AccumulationRegisters'][$register]['operation'] = '_operation';
         }
         
         // Recorders
         if (array_key_exists($register, $aRecorders))
         {
            $db_map['AccumulationRegisters'][$register]['recorder_type'] = '_rec_type';
            $db_map['AccumulationRegisters'][$register]['recorder_id'] = '_rec_id';
         }
         else throw new Exception('Internal configuration is wrong'); 
      }
      
      /* Documents */
      
      foreach ($documents as $document)
      {
         $db_map['documents'][$document]['table'] = $dbprefix.'DOC_'.$document;
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
               $t_map[$tabular]['table'] = $dbprefix.'DOC_'.$document.'_TS_'.$tabular;
               $t_map[$tabular]['pkey']  = '_id';
            }
         }
         
         $db_map['documents'][$document]['tabulars'] = $t_map;
      }
      
      /* Constants */
      
      if (!empty($constants))
      {
         $db_map['Constants'][null]['table'] = $dbprefix.'Constants';
         $db_map['Constants'][null]['pkey']  = '_id';
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
      $storage   = array_diff($this->getAllowedKinds(), $this->getNotStorage());
      $haveTab   = $this->getHaveTabulars();
      
      foreach ($storage as $kind)
      {
         $references =& $configuration[$kind]['references'];
         
         foreach ($references as $type => $fields)
         {
            foreach ($fields as $field => $params)
            {
               $relations[$params['kind']][$params['type']][$kind][$type]['attributes'][] = $field;
            }
         }
         
         if (!in_array($kind, $haveTab)) continue;
         
         // Tabular sections
         $references =& $configuration[$kind]['tabulars']['references'];
         
         foreach ($references as $type => $tabulars)
         {
            foreach ($tabulars as $tabular => $fields)
            {
               foreach ($fields as $field => $params)
               {
                  if ($field == 'Owner') continue;
                  
                  $relations[$params['kind']][$params['type']][$kind][$type]['tabulars'][$tabular][] = $field;
               }
            }
         }
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
      $kinds = array_diff($this->getAllowedKinds(), $this->getNotStorage());
      
      foreach ($kinds as $kind)
      {
         $query = array_merge($query, $this->generateSQLCreate($kind, $CManager, $dbcharset, $options));
      }
      
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
      if ($kind == 'AccumulationRegisters')
      {
         return $this->generateAccumulationRegistersSQLCreate($CManager, $dbcharset, $options);
      }
      elseif ($kind == 'Constants')
      {
         return $this->generateConstantsSQLCreate($CManager, $dbcharset, $options);
      }
      
      $db_map    = $CManager->getInternalConfiguration('db_map', false, $options);
      $fields    = $CManager->getInternalConfiguration($kind.'.fields', false, $options);
      $field_sql = $CManager->getInternalConfiguration($kind.'.field_sql', false, $options);
      
      $query  = array();
      $db_map =& $db_map[$kind];
      
      if ($kind != 'information_registry')
      {
         $tab_fields    = $CManager->getInternalConfiguration($kind.'.tabulars.fields', false, $options);
         $tab_field_sql = $CManager->getInternalConfiguration($kind.'.tabulars.field_sql', false, $options);
      }
      else
      {
         $periodical = $CManager->getInternalConfiguration($kind.'.periodical', false, $options);
         $demensions = $CManager->getInternalConfiguration($kind.'.dimensions', false, $options);
         $recorders  = $CManager->getInternalConfiguration($kind.'.recorders', false, $options);
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

         if (in_array($kind, $this->getObjectTypes()))
         {
            $uKey = ', UNIQUE KEY `Code` (`Code`)';
            
            if (isset($db_map[$type]['folder']))
            {
               $q .= ', `'.$db_map[$type]['folder'].'` tinyint(1) NOT NULL default 0';
            }
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
               
               //$uKey .= $db_map[$type]['recorder_type'].'`, `'.$db_map[$type]['recorder_id'].'`, `';
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
    * Generate AccumulationRegisters CREATE SQL-query to create entities tables
    * 
    * @param object $CManager
    * @param string $dbcharset
    * @param array& $options
    * @return array
    */
   protected function generateAccumulationRegistersSQLCreate($CManager, $dbcharset, array& $options = array())
   {
      $kind      = 'AccumulationRegisters';
      $db_map    = $CManager->getInternalConfiguration('db_map', $kind, $options);
      $fields    = $CManager->getInternalConfiguration($kind.'.fields', false, $options);
      $field_sql = $CManager->getInternalConfiguration($kind.'.field_sql', false, $options);
      
      $query  = array();
      
      $periodical    = $CManager->getInternalConfiguration($kind.'.periodical',    false, $options);
      $demensions    = $CManager->getInternalConfiguration($kind.'.dimensions',    false, $options);
      $recorders     = $CManager->getInternalConfiguration($kind.'.recorders',     false, $options);
      $register_type = $CManager->getInternalConfiguration($kind.'.register_type', false, $options);

      foreach ($fields as $type => $e_fields)
      {
         $table = $db_map[$type]['table'];
         $pKey  = $db_map[$type]['pkey'];
         $uKey  = '';
         
         $q  = 'CREATE TABLE IF NOT EXISTS `'.$table.'` (';
         $q .= '`'.$pKey.'` int(11) NOT NULL AUTO_INCREMENT';
         $q .= ', `'.$db_map[$type]['recorder_type'].'` varchar(255) NOT NULL default \'\'';
         $q .= ', `'.$db_map[$type]['recorder_id'].'` int(11) NOT NULL default 0';
         $q .= ', `'.$db_map[$type]['line'].'` int(11) NOT NULL default 0';
         $q .= ', `'.$db_map[$type]['active'].'` tinyint(1) NOT NULL default 0';
         
         if (isset($db_map[$type]['operation']))
         {
            $q .= ', `'.$db_map[$type]['operation'].'` tinyint(1) NOT NULL default 0';
         }
         
         /* Fields */
         
         foreach ($e_fields as $field)
         {
            $sql_def = isset($field_sql[$type][$field]) ? $field_sql[$type][$field] : 'int(11) NOT NULL';
            $q .= ', `'.$field.'` '.$sql_def;
         }

         // Keys
         if (isset($periodical[$type]))
         {
            $uKey .= '`'.$periodical[$type]['field'].'`';
         }
         
         $uKey .= isset($demensions[$type]) ? ',`'.implode("`, `", $demensions[$type]).'`' : '';
         
         if (strlen($uKey)) $uKey = ', UNIQUE KEY `demensions` ('.$uKey.')';
         
         $q .= ', PRIMARY KEY (`'.$pKey.'`)'.$uKey;
          
         $query[$table] = $q.') ENGINE=InnoDB DEFAULT CHARSET='.$dbcharset.' COLLATE='.$dbcharset.'_general_ci AUTO_INCREMENT=1';
          
         /* Total */
          
         $dbmap =& $db_map[$type]['total'];

         $table = $dbmap['table'];
         $pKey  = $dbmap['pkey'];
         
         $q  = 'CREATE TABLE IF NOT EXISTS `'.$table.'` (';
         $q .= '`'.$pKey.'` int(11) NOT NULL AUTO_INCREMENT';
         
         foreach ($e_fields as $field)
         {
            $sql_def = isset($field_sql[$type][$field]) ? $field_sql[$type][$field] : 'int(11) NOT NULL';
            $q .= ', `'.$field.'` '.$sql_def;
         }
         
         $q .= ', PRIMARY KEY (`'.$pKey.'`)'.$uKey;
         
         $query[$table] = $q.') ENGINE=InnoDB DEFAULT CHARSET='.$dbcharset.' COLLATE='.$dbcharset.'_general_ci AUTO_INCREMENT=1';
      }
      
      return $query;
   }
   
   /**
    * Generate Constants CREATE SQL-query to create entities tables
    * 
    * @param object $CManager
    * @param string $dbcharset
    * @param array& $options
    * @return array
    */
   protected function generateConstantsSQLCreate($CManager, $dbcharset, array& $options = array())
   {
      $kind      = 'Constants';
      $type      = null;
      $db_map    = $CManager->getInternalConfiguration('db_map', $kind, $options);
      $fields    = $CManager->getInternalConfiguration($kind.'.fields', $type, $options);
      $field_sql = $CManager->getInternalConfiguration($kind.'.field_sql', $type, $options);

      $query  = array();

      $table = $db_map[$type]['table'];
      $pKey  = $db_map[$type]['pkey'];

      $q  = 'CREATE TABLE IF NOT EXISTS `'.$table.'` (';
      $q .= '`'.$pKey.'` int(11) NOT NULL AUTO_INCREMENT';

      foreach ($fields as $field)
      {
         $q .= ', `'.$field.'` '.(isset($field_sql[$field]) ? $field_sql[$field] : 'int(11) NOT NULL');
      }

      $q .= ', PRIMARY KEY (`'.$pKey.'`)';

      $query[$table] = $q.') ENGINE=InnoDB DEFAULT CHARSET='.$dbcharset.' COLLATE='.$dbcharset.'_general_ci AUTO_INCREMENT=1';

      return $query;
   }
   
   
   
   
   /**
    * Add system users
    * 
    * @param array& $dict   - dictionary
    * @param array $allowed - allowed roles
    * @param array& $options
    * @return array - errors
    */
   protected function addSystemUsers(array& $dict, array $allowed, array& $options = array())
   {
      $errors    = array();
      $container = $this->getContainer($options);
      
      if (!empty($dict))
      {
         // Generate configuration
         $result = array(
            'users'      => array(),
            'attributes' => array()
         );
         $have_admin = false;
         
         foreach ($dict as $login => $conf)
         {
            $diff = array_diff($conf['roles'], $allowed);
             
            if (!empty($diff))
            {
               $errors[$login] = 'Unknow roles: '.implode(', ', $diff).'.';
               continue;
            }
            
            if (!$have_admin) $have_admin = in_array(SystemConstants::ADMIN_ROLE, $conf['roles']);
            
            $result['users'][] = $login;
            $result['attributes'][$login]['roles'] = $conf['roles'];
            $result['attributes'][$login]['password'] = $conf['password'];
         }

         if (!$have_admin) 
         {
            $errors[] = 'Not specified admanistrator (user with role "'.SystemConstants::ADMIN_ROLE.'")';
            
            return $errors;
         }
         elseif (!empty($errors)) return $errors;

         // Add records
         $conf    = $this->getConfigManager($options)->getInternalConfigurationByKind('catalogs.model', 'SystemUsers', $options);
         $m_opt   = array('replace' => true);
         $userIds = array();

         import('lib.model.catalogs.'.$conf['modelclass']);
         
         foreach ($result['users'] as $login)
         {
            // Insert/Update SystemUsers
            $user = new $conf['modelclass']();
            $err  = $user->fromArray(array(
               'User'       => $login,
               'AuthType'   => 'Basic',
               'Attributes' => serialize($result['attributes'][$login])
            ), $m_opt);
            
            if (!$err) $err = $user->save();
             
            if ($err)
            {
               $errors = array_merge($errors, $err);
               continue;
            }
             
            $userIds[] = $user->getId();
         }
      }
      
      // Remove old records
      if (!$errors)
      {
         $db    = $container->getDBManager();
         $dbmap = $container->getConfigManager()->getInternalConfiguration('db_map');
         $query = "DELETE FROM `".$dbmap['catalogs']['SystemUsers']['table']."` WHERE `AuthType`='Basic'";
         if (!empty($userIds))
         {
            $query .= " AND ".$dbmap['catalogs']['SystemUsers']['pkey']." NOT IN(".implode(", ",  $userIds).")";
         }
         
         if (null === $db->executeQuery($query))
         {
            $errors[] = $db->getError();
         }
      }
      
      return $errors;
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
            'web_services',
            'Constants'
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
      
      if (!empty($errors)) return $errors;
      
      // SystemUsers and AuthenticationRecords
      if (!empty($this->dictionary['Roles']))
      {
         $errors = $this->addSystemUsers($this->dictionary['Roles'], array_keys($this->dictionary['AccessRights']), $options);
      }
      
      return $errors;
   }
   
   /**
    * Update
    * 
    * @param array& $options
    * @return array errors
    */
   public function update(array& $options = array())
   {
      //echo '<pre>'; print_r($this->dictionary); echo '</pre>';
      /* Копируем старую конфигурацию в папку config/internal/backup */
      /* Генерируем новую */
      /* Сравниваем старую и новую, создаем таблицу отличий */
      /* Генерируем SQL для обновления */
      
      if (!$this->isInstalled()) return array('Entities not installed');
      
      // Load dictionary
      $errors = $this->loadDictionary($options);
      
      if (!empty($errors)) return $errors;
      
      // Update modules
      if (!$this->getContainer($options)->getModulesManager()->clearCache())
      {
         $errors['modules'][] = 'Modules not updated';
      }
      
      // Update access rights
      $result = $this->generateAccessRightsInternalConfiguration($this->dictionary['AccessRights']);
      
      if (isset($result['errors']))
      {
         return array_merge($errors, $result['errors']);
      }
      
      $this->saveInternalConfiguration($result, 'security/');
      
      // Update SystemUsers and AuthenticationRecords
      $errors = array_merge($errors, $this->addSystemUsers($this->dictionary['Roles'], array_keys($this->dictionary['AccessRights']), $options));
      
      return $errors;
   }
   
   /**
    * Remove
    * 
    * @param array& $options
    * @return array errors
    */
   public function remove(array& $options = array())
   {
      if (!$this->isInstalled()) return array('Entities not installed');
      
      $errors   = array();
      $CManager = $this->getConfigManager($options);
      
      /* remove all tables */
      
      $tables  = array();
      $db_map  = $CManager->getInternalConfiguration('db_map', false, $options);
      
      foreach ($db_map as $kind => $map)
      {
         foreach ($map as $config)
         {
            $tables[] = $config['table'];
            
            if (!empty($config['total']))
            {
               $tables[] = $config['total']['table'];
            }
            
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