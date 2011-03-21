<?php

if (defined('MINDTOUCH_DEKI'))
{
   DekiPlugin::registerHook('Special:OEInstall', array('SpecialOEInstall', 'execute'));
}

require_once('ext/include/MyPlug.php');

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
      reset($this->conf['root_path']);
      $appliedSolutionFirstKey = key($this->conf['root_path']);

      $this->conf = ExternalConfig::$extconfig['installer'];
      $this->conf['IP'] = $IP;
      $this->conf['Container'] = array(
         'base_dir' => $IP.$this->conf['base_dir'].$this->conf['applied_solutions_dir'].'/'//.$this->conf['root_path'][$appliedSolutionFirstKey]
      );
      $this->conf['PersistentLayer'] = array(
         'AppliedSolutionDir'  => $IP.$this->conf['base_dir'].$this->conf['applied_solutions_dir'],
         'AppliedSolutionName' => ''//$this->conf['root_path'][$appliedSolutionFirstKey]
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
      $appliedSolutionName  = $request->getVal('appliedSolutionName', false);
      PersistentLayer::getInstance($this->conf['PersistentLayer'])->setAppliedSolutionName($appliedSolutionName);

      if ($action)
      {
         $this->executeAction($action,$appliedSolutionName);
      }

      // generate form view
      $base_url    = $this->getTitle()->getLocalUrl();
      $isInstalled = PersistentLayer::getInstance($this->conf['PersistentLayer'])->isInstalled();

      ob_start();
      //include ($this->conf['IP'].$this->getWebPath().'form.tpl');
      foreach ($this->conf['root_path'] as $key => $value) {
         PersistentLayer::getInstance($this->conf['PersistentLayer'])->setAppliedSolutionName($value);
         $isInstalled = PersistentLayer::getInstance($this->conf['PersistentLayer'])->isInstalled();
         include ($this->conf['IP'].$this->getWebPath().'form.tpl');

      }
      $html .= ob_get_clean().'</form>';

      return $html;
   }

   /**
    * Execute action
    *
    * @param string $action
    * @return void
    */
   protected function executeAction($action,$appliedSolutionName)
   {
      $errors = array();

      switch ($action)
      {
         // Install
         case 'install':

            $perLay = PersistentLayer::getInstance($this->conf['PersistentLayer']);
            $errors = $perLay->install();
            $msg = 'Installed succesfully';

            break;

         // Update
         case 'update':

            $perLay = PersistentLayer::getInstance($this->conf['PersistentLayer']);
            $errors = $perLay->update();
            $msg = 'Updated succesfully (only modules, AccessRights and Roles)';

            break;

         // Remove
         case 'remove':

            $perLay = PersistentLayer::getInstance($this->conf['PersistentLayer']);
            $errors = $perLay->remove();
            $msg = 'Removed succesfully';

            break;

         // Update modules
         case 'update_modules':

            $container_options = $this->conf['Container'];
            $container_options['base_dir'] .= $appliedSolutionName;
            
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
               $path=$this->conf['root'].$this->conf['base_dir'].'/Framework/Templates/OEF.php';
               $this->createTemplate('','',$path,false,$errors);

               foreach ($map as $template => $path)
               {
                   $this->createTemplate($template,$appliedSolutionName,$path,true,$errors);
               }
               $mapSolution = Utility::loadArrayFromFile($this->conf['root'].$this->conf['base_dir'].
                       $this->conf['applied_solutions_dir'].'/'.$appliedSolutionName.$this->conf['solution_templates_map']);
               foreach ($mapSolution as $template => $path)
               {
                   $path=$this->conf['root'].$this->conf['base_dir'].$this->conf['applied_solutions_dir'].'/'.$appliedSolutionName.'/MindTouch/Templates'.$path;
                   $this->createTemplate($template,$appliedSolutionName,$path,false,$errors);
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
    * Create template
    * @param string $template - template name (and path in MT)
    * @param string $appliedSolutionName - $applied solution name
    * @param bool $changePath - if true - path can be changed (used for relative path)
    * @param string $path - path to template
    * @param array $errors - reference to errors array
    */
   protected function createTemplate($template,$appliedSolutionName,$path,$changePath,&$errors)
   {
      if (!is_null($path))
      {
         if($changePath)
            $path = '.'.$this->conf['templates_dir'].$path;

         if (!file_exists($path))
         {
            $errors[] = 'Template '.$template.': file not exists'.$path;
            return;
         }

         $content = file_get_contents($path);
      }
      else $content = '<p>Folder</p>';
      $template = 'Template:OEF/'.$appliedSolutionName.'/'.$template;
      if (!$this->postContent($template, $content))
      {
         $errors[] = 'Template '.$template.' not posted';
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
