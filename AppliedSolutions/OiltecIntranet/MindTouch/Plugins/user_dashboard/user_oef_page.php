<?php

/**
 * Base class for pages utilizing OEF Framework
 * 
 */
class UserOEFPage extends UserDashboardPage
{
   protected 
      $oefInitialized = false,
      $appName   = '',
      $container = null,
      $oefUser   = null
   ;
   
   
   /**
    * Set applied solution name
    * 
    * @param string $name
    * @return void
    */
   public function setAppliedSolutionName($name)
   {
      $this->appName = (string) $name;
   }
   
   /**
    * Get applied solution name
    * 
    * @return string
    */
   public function getAppliedSolutionName()
   {
      return $this->appName;
   }
   
   /**
    * Initialize OEF framework
    *
    * @return boolean
    */
   protected function initialize()
   {
      if ($this->oefInitialized) return true;
      
      $appliedSolutionName = $this->appName;

      if ($appliedSolutionName === false)
      {
         return false;
      }

      global $IP;

      $this->conf = ExternalConfig::$extconfig['installer'];
      $this->conf['IP'] = $IP;

      $framework = $IP.$this->conf['base_dir'].$this->conf['framework_dir'];

      if (!chdir($framework)) return false;

      $container_options = array(
         'base_dir' => $IP.$this->conf['base_dir'].$this->conf['applied_solutions_dir'].'/'.$appliedSolutionName
      );

      require_once('config/init.php');

      $this->container = Container::getInstance();
      
      $this->oefInitialized = true;

      return true;
   }
   
   /**
    * (non-PHPdoc)
    * @see deki/plugins/user_dashboard/UserDashboardPlugin#isVisible($ToUser)
    */
   public function isVisible($User)
   {
      if (!$this->oefInitialized)
      {
         try
         {
            if (!$this->initialize()) return false;
         }
         catch(Exception $e)
         {
            return false;
         }
      }
      
      if (defined('IS_SECURE'))
      {
         $this->oefUser = $this->container->getUser('MTAuth', DekiToken::get());

         if (!$this->oefUser->isAuthenticated())
         {
            return false;
         }
      }
      
      return true;
   }
   
   /**
    * (non-PHPdoc)
    * @see deki/plugins/user_dashboard/UserDashboardPage#getHtml()
    */
   public function getHtml()
   {
      global $wgUser;
      $sk = $wgUser->getSkin();

      $html = '';
       
      $Title = Title::newFromId($this->PageInfo->id);
      $Article = new Article($Title);

      $html .= $sk->renderUserDashboardHeader($Article, $Title);
      $html .= parent::getHtml();

      return $html;
   }
}
