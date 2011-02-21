<?php

if (defined('MINDTOUCH_DEKI')) {
   DekiPlugin::registerHook('Special:OEController', array('SpecialOEController', 'execute'));
}

class SpecialOEController extends SpecialPagePlugin
{
   protected
      $pageName  = 'OEController', // SpecialPage name
      $container = null,           // OEF factory object
      $user      = null;           // OEF User object
   
   
   /**
    * Main function for SpecialPage (see MindTouch docs)
    * 
    * @param string $pageName
    * @param string& $pageTitle
    * @param string& $html
    * @return void
    */
   public static function execute($pageName, &$pageTitle, &$html)
   {
      global $IP;
      
      if ($_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest')
      {
         self::redirectHome();
      }
      
      // Send response
      require_once($IP.'/includes/JSON.php');
      
      header('Content-Type: text/html; charset=utf-8');

      $res  = self::executeQuery($pageName, $pageTitle, $html);
      $JSON = new Services_JSON();

      echo $JSON->encode($res); exit;
   }
   
   /**
    * Execute user query
    * 
    * @param string $pageName
    * @param string& $pageTitle
    * @param string& $html
    * @return array
    */
   public static function executeQuery($pageName, &$pageTitle, &$html)
   {
      // Initialize OEF framework
      $special = new self($pageName, basename(__FILE__, '.php'));
      
      $special->initialize();
      
      // Check user
      if (defined('IS_SECURE'))
      {
         $special->user = $special->container->getUser('MTAuth', DekiToken::get());
         
         if (!$special->user->isAuthenticated())
         {
            return array('errors' => array('global' => 'You must be logged in'), 'status' => false);
         }
      }
      
      // Execute action
      if (empty($_POST['action']) || !method_exists($special, $_POST['action']))
      {
         return array('errors' => array('global' => 'Unknow action'), 'status' => false);
      }
      
      $method = $_POST['action'];

      return $special->$method();
   }
   
   
   /**
    * Initialize OEF framework
    * 
    * @return boolean
    */
   protected function initialize()
   {
      global $IP;
      
      $this->conf = ExternalConfig::$extconfig['installer'];
      $this->conf['IP'] = $IP;
      
      $framework = $IP.$this->conf['base_dir'].$this->conf['framework_dir'];
      
      if (!chdir($framework)) return false;
      
      $container_options = array(
         'base_dir' => $IP.$this->conf['base_dir'].$this->conf['applied_solutions_dir'].'/'.$this->conf['applied_solution_name']
      );
      
      require_once('config/init.php');
      
      $this->container = Container::getInstance();
      
      return true;
   }
   
   
   /************************************* Actions *********************************************/
      
   
   /**
    * Save Entity Form
    * 
    * @return array
    */
   protected function save()
   {
      if (empty($_POST['aeform']))
      {
         return array('errors' => array('global' => 'Invalid data'), 'status' => false);
      }

      $method = 'process'.(isset($_POST['form']) ? $_POST['form'] : 'Form');
      
      if (!method_exists($this, $method))
      {
         return array('errors' => array('global' => 'Invalid data'), 'status' => false);
      }
      
      $result = array();
      
      foreach ($_POST['aeform'] as $kind => $params)
      {
         if (!is_array($params) || empty($params))
         {
            $errors['global'] = 'Invalid data';
            continue;
         }
         
         foreach ($params as $type => $values)
         {
            if (!is_array($values) || empty($values))
            {
               $errors['global'] = 'Invalid data';
               continue;
            }
            
            $result[$kind][$type] = $this->$method($kind, $type, $values);
         }
      }
      
      return $result;
   }
   
   /**
    * Process simple form
    * 
    * @param string $kind
    * @param string $type
    * @param array $params
    * @return array
    */
   protected function processForm($kind, $type, array $params)
   {
      // Check attributes
      if (empty($params['attributes']) || !is_array($params['attributes']))
      {
         return array('status' => false, 'errors' => array('global' => 'Invalid data'));
      }
      
      $values = $params['attributes'];
      $action = isset($values['_id']) ? 'update' : 'create';
      
      // Check interactive permission
      if (defined('IS_SECURE'))
      {
         switch($kind)
         {
            case 'catalogs':
            case 'documents':
               if ($action == 'update')
               {
                   $access = $this->user->hasPermission($kind.'.'.$type.'.Edit');
               }
               else
               {
                  $access = $this->user->hasPermission($kind.'.'.$type.'.InteractiveInsert');
               }
               break;
               
            case 'information_registry':
            case 'AccumulationRegisters':
               $access = $this->user->hasPermission($kind.'.'.$type.'.Edit');
               break;
            
            default:
               $access = false;
         }
         
         if (!$access)
         {
            return array(
               'status' => false,
               'result' => array('msg' => 'Access denied'),
               'errors' => array()
            );
         }
      }
      
      $controller = $this->container->getController($kind, $type);
      
      return $controller->$action(Utility::escaper($values));
   }
   
   /**
    * Process object form with tabular sections
    * 
    * @param string $kind
    * @param string $type
    * @param array $params
    * @return array
    */
   protected function processObjectForm($kind, $type, array $params)
   {
      if (!in_array($kind, array('catalogs', 'documents')))
      {
         throw new Exception('"'.$kind.'" is not object type');
      }
      
      // Check attributes
      if (empty($params['attributes']) || !is_array($params['attributes']))
      {
         return array('status' => false, 'errors' => array('global' => 'Invalid data'));
      }
      
      $values = $params['attributes'];
      $action = isset($values['_id']) ? 'update' : 'create';
      
      // Check interactive permission
      if (defined('IS_SECURE'))
      {
         if ($action == 'update')
         {
            $access = $this->user->hasPermission($kind.'.'.$type.'.Edit');
         }
         else
         {
            $access = $this->user->hasPermission($kind.'.'.$type.'.InteractiveInsert');
         }
          
         if (!$access)
         {
            return array(
               'status' => false,
               'result' => array('msg' => 'Access denied'),
               'errors' => array()
            );
         }
      }
      
      // Save object
      $controller = $this->container->getController($kind, $type);
      $return     = $controller->$action(Utility::escaper($values));
      
      if (!$return['status']) return $return; 
      
      // Save tabular section
      $owner_id = $return['result']['_id'];
      
      if ($action != 'create') unset($return['result']['_id']);
      
      if (empty($params['tabulars']) || !is_array($params['tabulars']))
      {
         return $return;
      }
      
      $t_kind = $kind.'.'.$type.'.tabulars';
      
      foreach ($params['tabulars'] as $t_type => $params)
      {
         $return['tabulars'][$t_type] = $this->processTabularForm($t_kind, $t_type, $params, $owner_id);
      }
      
      return $return;
   }
   
   /**
    * Process tabular section form
    * 
    * @param string $kind
    * @param string $type
    * @param array $params
    * @param int $owner_id
    * @return array
    */
   protected function processTabularForm($kind, $type, array $params, $owner_id)
   {
      $result = array();
      $ids    = array();
      
      // Checkbox for batch actions
      if (isset($params['ids'])) unset($params['ids']);
      
      $controller = $this->container->getController($kind, $type);
      
      // Check values
      $params = Utility::escapeRecursive($params);
      
      // Delete
      if (isset($params['deleted']))
      {
         if (!empty($params['deleted']))
         {
            $options = array(
               'attributes' => array('%pkey', 'Owner'),
               'criterion'  => '`Owner` = %%Owner%% AND `%pkey` IN (%%pkey%%)'
            );
            $result['delete'] = $controller->delete(array('%pkey' => $params['deleted'], 'Owner' => $owner_id), $options);
         }
         
         unset($params['deleted']);
      }
      
      // Save all
      foreach ($params as $key => $values)
      {
         $values['Owner'] = $owner_id;
         
         $action = isset($values['_id']) ? 'update' : 'create';
                  
         $result[$key] = $controller->$action($values);
         
         if ($result[$key]['status'] && $action == 'update')
         {
            unset($result[$key]['result']['_id']);
         }
      }
      
      return $result;
   }
   
   /**
    * Process custom form
    * 
    * @param string $kind
    * @param string $type
    * @param array $params
    * @return array
    */
   protected function processCustomForm($kind, $type, array $params)
   {
      // Check attributes
      if (empty($params['attributes']) || empty($params['name']) || !is_array($params['attributes']))
      {
         return array('status' => false, 'errors' => array('global' => 'Invalid data'));
      }
      
      $values = $params['attributes'];
      $name   = $params['name']; 
      
      // Check interactive permission
      if (defined('IS_SECURE'))
      {
         switch($kind)
         {
            case 'catalogs':
            case 'documents':
               $access = $this->user->hasPermission($kind.'.'.$type.'.Edit') ||
                         $this->user->hasPermission($kind.'.'.$type.'.InteractiveInsert');
               break;
               
            case 'information_registry':
            case 'AccumulationRegisters':
               $access = $this->user->hasPermission($kind.'.'.$type.'.Edit');
               break;
            
            case 'reports':
            case 'data_processors':
               $access = $this->user->hasPermission($kind.'.'.$type.'.Use');
               break;
               
            default:
               $access = false;
         }
         
         if (!$access)
         {
            return array(
               'status' => false,
               'result' => array('msg' => 'Access denied'),
               'errors' => array()
            );
         }
      }
      
      $controller = $this->container->getController($kind, $type);
      
      return $controller->processCustomForm($name, Utility::escapeRecursive($values));
   }
   
   /**
    * Process constants form
    * 
    * @param string $kind
    * @param string $type
    * @param array $params
    * @return array
    */
   protected function updateConstants()
   {
      // Check form data
      if (empty($_POST['aeform']) || empty($_POST['aeform']['Constants']))
      {
         return array('status' => false, 'errors' => array('global' => 'Invalid data'));
      }
      
      $values = $_POST['aeform']['Constants'];
      
      if (empty($values['attributes']) || !is_array($values['attributes']))
      {
         return array('status' => false, 'errors' => array('global' => 'Invalid data'));
      }
      
      $values = $values['attributes'];
      
      // Check interactive permission
      if (defined('IS_SECURE') && !$this->user->isAdmin())
      {
         return array(
            'status' => false,
            'result' => array('msg' => 'Access denied'),
            'errors' => array()
         );
      }
      
      // Update Constants
      $controller = $this->container->getController('Constants', null);
      
      return $controller->update(Utility::escaper($values));
   }
   
   
   
   
   
   /**
    * Delete entity (Only for not object types) 
    * 
    * Only for 'information_registry', 'AccumulationRegisters'
    * 
    * @return array
    */
   protected function delete()
   {
      // Check data
      if (empty($_POST['aeform']))
      {
         return array('status' => false, 'errors' => array('global' => 'Invalid data'));
      }
      
      $values = $_POST['aeform'];
      $errors = array();
      
      if (empty($values['kind'])) $errors[] = 'Unknow entity kind';
      if (empty($values['type'])) $errors[] = 'Unknow entity type';
      if (empty($values['_id']))  $errors[] = 'Unknow entity id';
      
      if ($errors)
      {
         return array('status' => false, 'errors' => array('global' => implode('; ', $errors)));
      }
      
      if ($values['kind'] == 'catalogs' || $values['kind'] == 'documents')
      {
         return array('status' => false, 'errors' => array('global' => 'Not supported operation'));
      }
      
      // Check interactive permission
      if (defined('IS_SECURE') && !$this->user->hasPermission($values['kind'].'.'.$values['type'].'.Edit'))
      {
         return array(
            'status' => false,
            'result' => array('msg' => 'Access denied'),
            'errors' => array()
         );
      }
      
      // Delete
      $controller = $this->container->getController($values['kind'], $values['type']);
      
      return $controller->delete((int) $values['_id']);
   }
   
   /**
    * Mark for deletion (only for object types)
    * 
    * @return array
    */
   protected function markForDeletion()
   {
      // Check data
      if (empty($_POST['aeform']))
      {
         return array('status' => false, 'errors' => array('global' => 'Invalid data'));
      }
      
      $values = $_POST['aeform'];
      $errors = array();
      
      if (empty($values['kind'])) $errors[] = 'Unknow entity kind';
      if (empty($values['type'])) $errors[] = 'Unknow entity type';
      if (empty($values['_id']))  $errors[] = 'Unknow entity id';
      
      if ($errors)
      {
         return array('status' => false, 'errors' => array('global' => implode('; ', $errors)));
      }
      
      // Check interactive permission
      if (defined('IS_SECURE') && !$this->user->hasPermission($values['kind'].'.'.$values['type'].'.InteractiveMarkForDeletion'))
      {
         return array(
            'status' => false,
            'result' => array('msg' => 'Access denied'),
            'errors' => array()
         );
      }
      
      // Mark For Deletion object type entity
      $controller = $this->container->getController($values['kind'], $values['type']);
   
      if (!method_exists($controller, 'markForDeletion'))
      {
         return array('status' => false, 'errors' => array('global' => 'Not supported operation'));
      }
      
      return $controller->markForDeletion((int) $values['_id']);
   }
   
   /**
    * Unmark for deletion (only for object types)
    * 
    * @return array
    */
   protected function unmarkForDeletion()
   {
      // Check data
      if (empty($_POST['aeform']))
      {
         return array('status' => false, 'errors' => array('global' => 'Invalid data'));
      }
      
      $values = $_POST['aeform'];
      $errors = array();
      
      if (empty($values['kind'])) $errors[] = 'Unknow entity kind';
      if (empty($values['type'])) $errors[] = 'Unknow entity type';
      if (empty($values['_id']))  $errors[] = 'Unknow entity id';
      
      if ($errors)
      {
         return array('errors' => array('global' => implode('; ', $errors)), 'status' => false);
      }
      
      // Check interactive permission
      if (defined('IS_SECURE') && !$this->user->hasPermission($values['kind'].'.'.$values['type'].'.InteractiveUnmarkForDeletion'))
      {
         return array(
            'status' => false,
            'result' => array('msg' => 'Access denied'),
            'errors' => array()
         );
      }
      
      // Unmark for deletion object type entity
      $controller = $this->container->getController($values['kind'], $values['type']);
      
      if (!method_exists($controller, 'unmarkForDeletion'))
      {
         return array('status' => false, 'errors' => array('global' => 'Not supported operation'));
      }
      
      return $controller->unmarkForDeletion((int) $values['_id']);
   }
   
   
   
   
   /**
    * Post
    * 
    * @return array
    */
   protected function post()
   {
      return $this->changePost(true);
   }
   
   /**
    * Unpost
    * 
    * @return array
    */
   protected function unpost()
   {
      return $this->changePost(false);
   }
   
   /**
    * Change post state
    * 
    * @param boolean $post
    * @return array
    */
   protected function changePost($post)
   {
      // Check data
      if (empty($_POST['aeform']))
      {
         return array('status' => false, 'errors' => array('global' => 'Invalid data'));
      }
      
      $values = $_POST['aeform'];
      $errors = array();
      
      if (empty($values['kind'])) $errors[] = 'Unknow entity kind';
      if (empty($values['type'])) $errors[] = 'Unknow entity type';
      if (empty($values['_id']))  $errors[] = 'Unknow entity id';
      
      if ($errors)
      {
         return array('status' => false, 'errors' => array('global' => implode('; ', $errors)));
      }
      
      // Check interactive permission
      if (defined('IS_SECURE'))
      {
         if ($post)
         {
            $access = $this->user->hasPermission($values['kind'].'.'.$values['type'].'.InteractivePosting');
         }
         else
         {
            $access = $this->user->hasPermission($values['kind'].'.'.$values['type'].'.InteractiveUndoPosting');
         }
         
         if (!$access)
         {
            return array(
               'status' => false,
               'result' => array('msg' => 'Access denied'),
               'errors' => array()
            );
         }
      }
      
      // Change post
      $controller = $this->container->getController($values['kind'], $values['type']);
      
      $method = $post ? 'post' : 'unpost';
      
      if (!method_exists($controller, $method))
      {
         return array('status' => false, 'errors' => array('global' => 'Not supported operation'));
      }
      
      return $controller->$method((int) $values['_id']);
   }
   
   
   
   
   /**
    * Generate report
    * 
    * @return array
    */
   protected function generate()
   {
      // Check data
      if (empty($_POST['aeform']))
      {
         return array('status' => false, 'errors' => array('global' => 'Invalid data'));
      }
      
      $values = $_POST['aeform'];
      $errors = array();
      
      list($type, $params) = each($values);
      
      $headline = isset($params['attributes']) ? $params['attributes'] : array();
      
      if (empty($type)) $errors[] = 'Unknow report type';
      
      if ($errors)
      {
         return array('status' => false, 'errors' => array('global' => implode('; ', $errors)));
      }
      
      // Check interactive permission
      if (defined('IS_SECURE') && !$this->user->hasPermission('reports.'.$type.'.Use'))
      {
         return array(
            'status' => false,
            'result' => array('msg' => 'Unknow report'),
            'errors' => array()
         );
      }
      
      // Generate report
      $controller = $this->container->getController('reports', $type);
      
      return array('reports' => array($type => $controller->generate(Utility::escapeRecursive($headline))));
   }
   
   /**
    * Decode report item
    * 
    * @return array
    */
   protected function decode()
   {
      // Check data
      $values = $_POST['parameters'];
      $errors = array();
      
      list($type, $params) = each($values);
      
      if (empty($type)) $errors[] = 'Unknow report type';
      
      if ($errors)
      {
         return array('status' => false, 'errors' => array('global' => implode('; ', $errors)));
      }
      
      // Check interactive permission
      if (defined('IS_SECURE') && !$this->user->hasPermission('reports.'.$type.'.Use'))
      {
         return array(
            'status' => false,
            'result' => array('msg' => 'Unknow report'),
            'errors' => array()
         );
      }
      
      // Decode
      $controller = $this->container->getController('reports', $type);
      
      return $controller->decode(Utility::escapeRecursive($params));
   }
   
   
   
   
   /**
    * Data import
    * 
    * @return array
    */
   protected function import()
   {
      // Check data
      if (empty($_POST['aeform']))
      {
         return array('status' => false, 'errors' => array('global' => 'Invalid data'));
      }
      
      $values = $_POST['aeform'];
      $errors = array();
      
      list($type, $params) = each($values);
      
      $headline = isset($params['attributes']) ? $params['attributes'] : array();
      
      if (empty($type)) $errors[] = 'Unknow data processor type';
      
      if ($errors)
      {
         return array('status' => false, 'errors' => array('global' => implode('; ', $errors)));
      }
      
      // Check interactive permission
      if (defined('IS_SECURE') && !$this->user->hasPermission('data_processors.'.$type.'.Use'))
      {
         return array(
            'status' => false,
            'result' => array('msg' => 'Unknow data processor'),
            'errors' => array()
         );
      }
      
      // Import
      $controller = $this->container->getController('data_processors', $type);
      
      return array('data_processors' => array($type => $controller->import(Utility::escaper($headline))));
   }
   
   

   /**
    * Generate custom form
    * 
    * @return array
    */
   protected function generateForm()
   {
      // Check data
      if (empty($_REQUEST['uid']) || !is_string($_REQUEST['uid']))
      {
         return array('status' => false, 'errors' => array('global' => 'Invalid data'));
      }
      
      if (empty($_REQUEST['name']) || !is_string($_REQUEST['name']))
      {
         return array('status' => false, 'errors' => array('global' => 'Invalid data'));
      }
      
      list($kind, $type) = Utility::parseUID(Utility::escapeString($_REQUEST['uid']));
      $name = $_REQUEST['name'];
      
      // Check interactive permission
      if (defined('IS_SECURE'))
      {
         switch($kind)
         {
            case 'catalogs':
            case 'documents':
            case 'information_registry':
            case 'AccumulationRegisters':
               $access = $this->user->hasPermission($kind.'.'.$type.'.Edit');
               break;
            
            case 'reports':
            case 'data_processors':
               $access = $this->user->hasPermission($kind.'.'.$type.'.Use');
               break;
               
            default:
               $access = false;
         }
         
         if (!$access)
         {
            return array(
               'status' => false,
               'result' => array('msg' => 'Access denied'),
               'errors' => array()
            );
         }
      }
      
      $controller = $this->container->getController($kind, $type);
      
      return $controller->generateCustomForm(Utility::escapeString($name), Utility::escapeRecursive($_REQUEST['parameters']));
   }
   
   /**
    * Notify form event
    * 
    * @return array
    */
   protected function notifyFormEvent()
   {
      // Check data
      if (empty($_REQUEST['uid']) || !is_string($_REQUEST['uid']))
      {
         return array('status' => false, 'errors' => array('global' => 'Invalid data'));
      }
      
      if (empty($_REQUEST['formName']) || !is_string($_REQUEST['formName']))
      {
         return array('status' => false, 'errors' => array('global' => 'Invalid data'));
      }
      
      if (empty($_REQUEST['event']) || !is_string($_REQUEST['event']))
      {
         return array('status' => false, 'errors' => array('global' => 'Invalid data'));
      }
      
      list($kind, $type) = Utility::parseUID(Utility::escapeString($_REQUEST['uid']));
      
      // Check interactive permission
      if (defined('IS_SECURE'))
      {
         switch($kind)
         {
            case 'catalogs':
            case 'documents':
            case 'information_registry':
            case 'AccumulationRegisters':
               $access = $this->user->hasPermission($kind.'.'.$type.'.Edit');
               break;
            
            case 'reports':
            case 'data_processors':
               $access = $this->user->hasPermission($kind.'.'.$type.'.Use');
               break;
               
            default:
               $access = false;
         }
         
         if (!$access)
         {
            return array(
               'status' => false,
               'result' => array('msg' => 'Access denied'),
               'errors' => array()
            );
         }
      }
      
      // Process parameters
      $formName = Utility::escapeString($_REQUEST['formName']);
      $event    = Utility::escapeString($_REQUEST['event']);
      
      if (isset($_REQUEST['formData']) && is_string($_REQUEST['formData']))
      {
         $formData = array();
         parse_str(urldecode($_REQUEST['formData']), $formData);
         $formData = Utility::escapeRecursive($formData);
      }
      else $formData = array();
      
      if (isset($_REQUEST['parameters']))
      {
         if (is_array($_REQUEST['parameters']))
         {
            $parameters = Utility::escapeRecursive($_REQUEST['parameters']);
         }
         else $parameters = array(Utility::escapeString($_REQUEST['parameters']));
      }
      else $parameters = array();
      
      // Execute action
      $controller = $this->container->getController($kind, $type);
      
      return $controller->notifyFormEvent($formName, $event, $formData, $parameters);
   }
   
   /**
    * Get list of related entities for deletion form
    * 
    * @return array
    */
   protected function relatedForDeletion()
   {
      // Check data
      if (empty($_POST['aeform']) || !is_array($_POST['aeform']))
      {
         return array('errors' => array('global' => 'Invalid data'), 'status' => false);
      }
      
      // Execute action
      $controller = $this->container->getObjectDeletionController();
      
      $list = $controller->getListOfRelated(Utility::escapeRecursive($_POST['aeform']));
      
      if ($list === null)
      {
         return array('errors' => array('global' => 'Invalid data'), 'status' => false);
      }
      
      return array(
         'status' => true,
         'result' => array(
            'list' => $list
         ),
         'errors' => array()
      );
   }
}