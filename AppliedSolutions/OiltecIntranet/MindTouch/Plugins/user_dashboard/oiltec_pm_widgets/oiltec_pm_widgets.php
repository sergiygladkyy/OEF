<?php

require_once('deki/plugins/user_dashboard/user_oef_page.php');

DekiPlugin::registerHook(Hooks::DATA_GET_USER_DASHBOARD_PLUGINS, array('UserOiltecPMWidgetsPage', 'getDashboardPluginsHook'));

/**
 * Project Manager Widgets
 *  
 */
class UserOiltecPMWidgetsPage extends UserOEFPage
{
   const OEF_APP_NAME = 'OiltecIntranet';
   
   protected $pluginFolder = 'oiltec_pm_widgets';
   
   /**
    * Process Hook
    * 
    * @param array& $plugins
    * @param object $User
    * @return void
    */
   public static function getDashboardPluginsHook(&$plugins, $User)
   {
      $Plugin = new self($User);
      $plugins[] = $Plugin;
   }
   
   /**
    * Constructor
    * 
    */
   public function __construct($User)
   {
      parent::__construct($User);
      
      $this->setAppliedSolutionName(self::OEF_APP_NAME);
   }
   
   /**
    * (non-PHPdoc)
    * @see deki/plugins/user_dashboard/UserDashboardPage#initPlugin()
    */
   public function initPlugin()
   {
      global $wgUser;

      if (!is_null($this->User) && $wgUser->getId() == $this->User->getId())
      {
         $this->displayTitle = 'PM';
      }
      else
      {
         $this->displayTitle = 'PM';
      }

      $path = 'Template:OEF/OiltecIntranet/Dashboard/PM';

      $this->pagePath = $path;

      parent::initPlugin();
   }

   /**
    * (non-PHPdoc)
    * @see deki/plugins/user_dashboard/UserDashboardPage#getPluginId()
    */
   public function getPluginId()
   {
      return 'PM';
   }

   /**
    * (non-PHPdoc)
    * @see deki/plugins/user_dashboard/UserOEFPage#isVisible($User)
    */
   public function isVisible($User)
   {
      if (!parent::isVisible($User)) return false;


      return MEmployees::currentIsPM();
   }
}
