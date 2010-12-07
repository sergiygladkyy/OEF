<?php

if (defined('MINDTOUCH_DEKI'))
{
   DekiPlugin::registerHook('Special:OEInstall', array('SpecialOEInstall', 'execute'));
}


class SpecialOEInstall extends SpecialPagePlugin
{
   protected $pageName = 'OEInstall';
   
   protected $conf;

   public static function execute($pageName, &$pageTitle, &$html, &$subhtml)
   {
      $User = DekiUser::getCurrent();

      if (!$User->isAdmin())
      {
         self::redirectHome();
      }

      $Special = new self($pageName, basename(__FILE__, '.php'));
      $pageTitle = 'Installer';
      
      if (!$Special->initialize())
      {
         DekiMessage::error('Initialize error');
      }
      
      $html = $Special->output($html, $subhtml);
   }
   
   /**
    * Initialize OEF Framework
    * 
    * @return boolean
    */
   protected function initialize()
   {
      global $IP;
      
      $this->conf = ExternalConfig::$extconfig['installer'];
      $this->conf['IP'] = $IP;
      $this->conf['Container'] = array(
         'base_dir' => $IP.$this->conf['base_dir'].$this->conf['applied_solutions_dir'].'/'.$this->conf['applied_solution_name']
      );
      $this->conf['PersistentLayer'] = array(
         'AppliedSolutionDir'  => $IP.$this->conf['base_dir'].$this->conf['applied_solutions_dir'],
         'AppliedSolutionName' => $this->conf['applied_solution_name']
      );
      
      $framework = $IP.$this->conf['base_dir'].$this->conf['framework_dir'];
      
      if (!chdir($framework)) return false;
      
      require_once('lib/utility/Loader.php');
      require_once('lib/container/Container.php');
      require_once('lib/persistent/PersistentLayer.php');
      require_once('lib/utility/Utility.php');
             
      return true;
   }

   /**
    * Generate output html
    *
    * @param $html
    * @param $subhtml
    * @return string
    */
   protected function &output(&$html, &$subhtml)
   {
      $this->includeSpecialCss('special_oe_install.css');

      // process form
      $request = DekiRequest::getInstance();
      $action  = $request->getVal('actions', false);

      if ($action)
      {
         $this->executeAction($action);
      }

      // generate form view
      $base_url    = $this->getTitle()->getLocalUrl();
      $isInstalled = PersistentLayer::getInstance($this->conf['PersistentLayer'])->isInstalled();

      ob_start();
      include ($this->conf['IP'].$this->getWebPath().'form.tpl');
      $html .= ob_get_clean().'</form>';

      return $html;
   }
    
   /**
    * Execute action
    *
    * @param string $action
    * @return void
    */
   protected function executeAction($action)
   {
      switch ($action)
      {
         // Install
         case 'install':
            
            $perLay = PersistentLayer::getInstance($this->conf['PersistentLayer']);
            $errors = $perLay->install();
            $msg = 'Installed succesfully';
            
            break;
      
         // Remove
         case 'remove':
            
            $perLay = PersistentLayer::getInstance($this->conf['PersistentLayer']);
            $errors = $perLay->remove();
            $msg = 'Removed succesfully';
            
            break;
   
         // Update modules
         case 'update_modules':
            
            $container_options =& $this->conf['Container'];
            require_once('config/init.php');
            $container = Container::getInstance();
            
            $container->getModulesManager()->clearCache();
            $msg = 'Updated succesfully';
            
            break;
         
         // Update templates   
         case 'update_templates':
            
            try
            {
               $map = Utility::loadArrayFromFile('.'.$this->conf['templates_map']);
               
               foreach ($map as $template => $path)
               {
                  if (!is_null($path))
                  {
                     $path = '.'.$this->conf['templates_dir'].$path;

                     if (!file_exists($path))
                     {
                        $errors[] = 'Template '.$template.': file not exists';
                        continue;
                     }

                     $content = file_get_contents($path);
                  }
                  else $content = '<p>Folder</p>';
                  
                  if (!$this->postContent($template, $content))
                  {
                     $errors[] = 'Template '.$template.' not posted';
                  }
               }
               
               if (!$errors) $msg = 'Updated succesfully';
            }
            catch (Exception $e)
            {
               $errors[] = $e->getMessage();
            }
            
            break;
      }
      
      if ($errors)
      {
         DekiMessage::error('<pre>'.print_r($errors, true).'</pre>');
      }
      elseif ($msg)
      {
         DekiMessage::success($msg);
      }
   }
   
   /**
    * Create MT page or template
    * 
    * @param string $title
    * @param string $content
    * @return boolean
    */
   protected function postContent($title, $content = '')
   {
      $Plug = new MyPlug($this->conf['api']);
      
      $Plug->SetAuthToken($_REQUEST['authtoken']);
      
      $Plug = $Plug->At('deki', 'pages', '='.rawurlencode($title), 'contents');
      $Plug = $Plug->With('edittime', date("YmdHis"));
      
      $result = $Plug->Post($content);
      
      return $result->getStatus();
   }
}
