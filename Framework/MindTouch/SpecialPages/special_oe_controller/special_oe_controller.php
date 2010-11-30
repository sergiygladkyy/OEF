<?php

if (defined('MINDTOUCH_DEKI')) {
   DekiPlugin::registerHook('Special:OEController', array('SpecialOEController', 'execute'));
}

class SpecialOEController extends SpecialPagePlugin
{
   protected $pageName = 'OEController';
   
   public static function execute($pageName, &$pageTitle, &$html)
   {
      global $IP;
      
      require_once($IP.'/includes/JSON.php');
      
      if($_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest')
      {
         self::redirectHome();
      }

      header('Content-Type: text/html; charset=utf-8');

      $res  = self::executeQuery($pageName, $pageTitle, $html);
      $JSON = new Services_JSON();

      echo $JSON->encode($res); exit;
   }
   
   /**
    * Execute user query
    * 
    * @return array
    */
   public static function executeQuery($pageName, &$pageTitle, &$html)
   {
      $User = DekiUser::getCurrent();

      if ($User->isAnonymous())
      {
         return array('errors' => array('global' => 'Access denied'), 'status' => false);
      }

      if (empty($_POST['action']))
      {
         return array('errors' => array('global' => 'Unknow action "'.$_POST['action'].'"'), 'status' => false);
      }

      $special = new self($pageName, basename(__FILE__, '.php'));

      $method = $_POST['action'];
      
      if (!method_exists($special, $method))
      {
         return array('errors' => array('global' => 'Unknow action "'.$_POST['action'].'"'), 'status' => false);
      }

      $special->initialize();
      
      return $special->$method();
   }
   
   
   /**
    * Initialize AE Framework
    * 
    * @return bool
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
   
   /**
    * Save Entity Form
    * 
    * @return array
    */
   protected function save()
   {
      if (empty($_POST['aeform'])) return array('errors' => array('global' => 'Invalid data'), 'status' => false);

      $method = 'process'.(isset($_POST['form']) ? $_POST['form'] : 'Form');
      if (!method_exists($this, $method))
      {
         return array('errors' => array('global' => 'Unknow form "'.$_POST['form'].'"'), 'status' => false);
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
      $controller = $this->container->getController($kind, $type);
      
      $action = isset($values['_id']) ? 'update' : 'create';
      
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
         throw new Exception(__METHOD__.' "'.$kind.'" is not object type');
      }
      
      $controller = $this->container->getController($kind, $type);
      
      // Save object
      if (empty($params['attributes']) || !is_array($params['attributes']))
      {
         return array('errors' => array('global' => '"'.$kind.'.'.$type.'" object attributes is empty'), 'status' => false);
      }
      $values = $params['attributes'];
      $action = isset($values['_id']) ? 'update' : 'create';
      $return = $controller->$action(Utility::escaper($values));
      
      if (!$return['status']) return $return; 
      
      // Save tabular section
      $owner_id = $return['result']['_id'];
      if ($action != 'create') unset($return['result']['_id']);
      
      if (empty($params['tabulars']) || !is_array($params['tabulars'])) return $return;
      
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
               'attributes' => array('%pkey', 'owner'),
               'criterion'  => '`owner` = %%owner%% AND `%pkey` IN (%%pkey%%)'
            );
            $result['delete'] = $controller->delete(array('%pkey' => $params['deleted'], 'owner' => $owner_id), $options);
         }
         
         unset($params['deleted']);
      }
      
      // Save all
      foreach ($params as $key => $values)
      {
         $values['owner'] = $owner_id;
         
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
    * Delete entity
    * 
    * @return array
    */
   protected function delete()
   {
      if (empty($_POST['aeform'])) return array('errors' => array('global' => 'Invalid data'), 'status' => false);
      
      $values = $_POST['aeform'];
      $errors = array();
      
      if (empty($values['kind'])) $errors[] = 'Unknow entity kind';
      if (empty($values['type'])) $errors[] = 'Unknow entity type';
      if (empty($values['_id']))  $errors[] = 'Unknow entity id';
      
      if ($errors)
      {
         return array('errors' => array('global' => implode('; ', $errors)), 'status' => false);
      }
      
      $controller = $this->container->getController($values['kind'], $values['type']);
      
      return $controller->delete((int) $values['_id']);
   }
   
   /**
    * Restore entity
    * 
    * @return array
    */
   protected function restore()
   {
      if (empty($_POST['aeform'])) return array('errors' => array('global' => 'Invalid data'), 'status' => false);
      
      $values = $_POST['aeform'];
      $errors = array();
      
      if (empty($values['kind'])) $errors[] = 'Unknow entity kind';
      if (empty($values['type'])) $errors[] = 'Unknow entity type';
      if (empty($values['_id']))  $errors[] = 'Unknow entity id';
      
      if ($errors)
      {
         return array('errors' => array('global' => implode('; ', $errors)), 'status' => false);
      }
      
      $controller = $this->container->getController($values['kind'], $values['type']);
      
      if (!method_exists($controller, 'restore'))
      {
         return array('errors' => array('global' => '"'.$values['kind'].'.'.$values['type'].'" not supported restore'), 'status' => false);
      }
      
      return $controller->restore((int) $values['_id']);
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
      if (empty($_POST['aeform'])) return array('errors' => array('global' => 'Invalid data'), 'status' => false);
      
      $values = $_POST['aeform'];
      $errors = array();
      
      if (empty($values['kind'])) $errors[] = 'Unknow entity kind';
      if (empty($values['type'])) $errors[] = 'Unknow entity type';
      if (empty($values['_id']))  $errors[] = 'Unknow entity id';
      
      if ($errors)
      {
         return array('errors' => array('global' => $errors), 'status' => false);
      }
      
      $controller = $this->container->getController($values['kind'], $values['type']);
      
      $method = $post ? 'post' : 'unpost';
      
      if (!method_exists($controller, $method))
      {
         return array('errors' => array('global' => '"'.$values['kind'].'.'.$values['type'].'" not supported '.($post ? 'post' : 'unpost').' action'), 'status' => false);
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
      if (empty($_POST['aeform'])) return array('errors' => array('global' => 'Invalid data'), 'status' => false);
      
      $values = $_POST['aeform'];
      $errors = array();
      
      list($type, $params) = each($values);
      
      $headline = isset($params['attributes']) ? $params['attributes'] : array();
      
      if (empty($type)) $errors[] = 'Unknow entity type';
      
      if ($errors)
      {
         return array('errors' => array('global' => implode('; ', $errors)), 'status' => false);
      }
      
      $controller = $this->container->getController('reports', $type);
      
      return array('reports' => array($type => $controller->generate(Utility::escaper($headline))));
   }
   
   /**
    * Decode report item
    * 
    * @return array
    */
   protected function decode()
   {
      $values = $_POST['parameters'];
      $errors = array();
      
      list($type, $params) = each($values);
      
      if (empty($type)) $errors[] = 'Unknow entity type';
      
      if ($errors)
      {
         return array('errors' => array('global' => implode('; ', $errors)), 'status' => false);
      }
      
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
      if (empty($_POST['aeform'])) return array('errors' => array('global' => 'Invalid data'), 'status' => false);
      
      $values = $_POST['aeform'];
      $errors = array();
      
      list($type, $params) = each($values);
      
      $headline = isset($params['attributes']) ? $params['attributes'] : array();
      
      if (empty($type)) $errors[] = 'Unknow entity type';
      
      if ($errors)
      {
         return array('errors' => array('global' => implode('; ', $errors)), 'status' => false);
      }
      
      $controller = $this->container->getController('data_processors', $type);
      
      return array('data_processors' => array($type => $controller->import(Utility::escaper($headline))));
   }

}