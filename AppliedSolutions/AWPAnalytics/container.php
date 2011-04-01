<?php 

$_conf = array(
   /* DB configuration */
   'db' => array(
      'dbserver'   => "localhost",
      'dbusername' => "wikiuser",
      'dbpass'     => "mQ3cRe4lGTMo6QPx",
      'dbname'     => "wikidb",
      'dbprefix'   => "AWPAnalytics_",
      'dbcharset'  => "utf8", // cp1251
      
      'classname'  => 'DBMysql',
      'oclassname' => 'ODBMysql',
      'options'    => array()    // Not required. Use for extensions
   ),
   
   /* Events configuration */
   'event' => array(
      'classname_event' => 'sfEvent',
      'classname_dispatcher' => 'sfEventDispatcher'
   ),
   
   /* Modules configuration */
   'modules' => array(
      'classname' => 'ModulesManager',
      'options'   => array(
         'modules_dir'  => '../AppliedSolutions/AWPAnalytics/Modules/',
         'cache_dir'    => '../AppliedSolutions/AWPAnalytics/Cache/',
         'template_dir' => '../AppliedSolutions/AWPAnalytics/Templates/',
         'layout_dir'   => '../AppliedSolutions/AWPAnalytics/Layout/'
      )
   ),
   
   /* Validation */
   'validator' => array(
      'classname' => 'Validator',
      'options'   => array()
   ),
   
   /* Pager */
   'pager' => array(
      'classname' => 'Pager',
      'options'   => array(
         'max_per_page' => 20,
         'max_item_in_scroll' => 15
      )
   ),
   
   /* Request */
   'request' => array(
      'classname' => 'Request',
      'options'   => array()
   ),
   
   /* Response */
   'response' => array(
      'classname' => 'Response',
      'options'   => array(
         //'protocol' => 'HTTP/1.1'
      )
   ),
   
   /* Upload */
   'upload' => array(
      'classname' => 'Upload',
      'options'   => array(
         'upload_dir'  => '../AppliedSolutions/AWPAnalytics/Upload/',
         'form_prefix' => 'aeform'
      )
   )
);