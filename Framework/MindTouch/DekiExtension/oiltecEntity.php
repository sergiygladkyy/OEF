<?php
  include "include/config.php";
  include "include/DekiExt.php";

  DekiExt(
    // Description
      "OILTEC Deki entity extension",

    // Metadata
      array(
          "description" => "Extension to access entities.",
          "copyright"   => "OILTEC 2010",
          "namespace"   => "entities"
      ),

      // List of Extension Functions
      array (
          "displayListForm(uid:str, params:map):map" => 'displayListForm',
          "displayEditForm(uid:str, id:num, params:map):map" => 'displayEditForm',
          "displayItemForm(uid:str, id:num, params:map):map" => 'displayItemForm',
          "displayReportForm(uid:str, params:map):map" => 'displayReportForm',
          "displayImportForm(uid:str, params:map):map" => 'displayImportForm',
          "displayConstantsForm(params:map):map" => 'displayConstantsForm',
          "displayDeletionForm(params:map):map"  => 'displayDeletionForm',
          "displayTreeList(uid:str, params:map):map" => 'displayTreeList',
          "getInternalConfiguration(kind:str, type:str):map" => 'getInternalConfiguration',
          "parseUID(uid:str):map" => 'parseUID',
          "getAppliedSolutionName():map" => 'getAppliedSolutionName',
          "getUploadDir(kind:str, type:str, attr:str):map" => 'getUploadDir',
          "executeQuery(query:str, params:map):map" => 'executeQuery',
          "generateForm(uid:str, name:str, params:map):map" => 'generateCustomForm',
          "GetFormattedDate(date:str, format:str):str" => 'getFormattedDate'
      )
  );

  // ------------------------------------------------------------------------

  $OEF_USER = null;

  /**
   * Initilaze OEF user object
   *
   * @return boolean
   */
  function initializeOEFUser()
  {
     global $OEF_USER;

     $env_info  = $_SERVER["HTTP_X_DEKISCRIPT_ENV"];

     $classname = 'MTUser';
     import('lib.user.'.$classname);

     if (!class_exists($classname))
     {
        return array('Initialize error');
     }

     if (!$env_info)
     {
        $OEF_USER = call_user_func(array($classname, 'createInstance'));
     }
     else
     {
        if (!preg_match('/(?<=,|\s)user\.id=["\']([\d]+)["\'](?=,|\s)/i', $env_info, $matches))
        {
           return array('Initialize error');
        }

        $OEF_USER = call_user_func(array($classname, 'createInstanceById'), $matches[1]);
     }

     return array();
  }

  /**
   * Initialize OE framework
   *
   * @return array - errors
   */
  function initialize($full = false)
  {
     $appliedSolutionName = getApplicationName();

     if($appliedSolutionName[0] === -1 )
     {
         return array( 'There is no page.path  in $_SERVER[HTTP_X_DEKISCRIPT_ENV] :',$appliedSolutionName[1]);
     }
     else if($appliedSolutionName[0] === -2 )
     {
         return array( 'There is no root_path like in page.path  :',$appliedSolutionName[1],$_SERVER['HTTP_X_DEKISCRIPT_ENV']);
     }

     $conf =& ExternalConfig::$extconfig['installer'];

     $framework = '.'.$conf['base_for_deki_ext'].$conf['framework_dir'];

     $container_options = array(
        'base_dir' => $conf['root'].$conf['base_dir'].$conf['applied_solutions_dir'].'/'.$appliedSolutionName[1]
     );

     if (!chdir($framework)) return array('Initialize error');
     
     try {
        if (!$full)
        {
           require_once('lib/utility/Loader.php');
           require_once('lib/utility/Utility.php');
           require_once('lib/container/Container.php');
           require_once('lib/controller/Constants/Constants.php');

           $container = Container::createInstance($container_options);

           // Security (duplicated piece of code of 123 config/init.php)
           $odb = $container->getODBManager();
           $res = $odb->loadAssoc('SELECT count(*) AS cnt FROM catalogs.SystemUsers');

           if (!isset($res['cnt'])) return array('Initialize error');

           if ($res['cnt'] > 0) define('IS_SECURE', true);
        }
        else
        {
           require_once('config/init.php');
        }
     }
     catch (Exception $e)
     {
        return array('Initialize error');
     }

     // Initialize user
     $errors = initializeOEFUser();

     if ($errors) return $errors;

     // Check current user
     if (defined('IS_SECURE'))
     {
        global $OEF_USER;

        if (!$OEF_USER->isAuthenticated())
        {
           return array('You must be logged in');
        }
     }

     return array();
  }


  /**
   * Retrieve data for ListForm
   *
   * @param string $uid
   * @param array $params
   * @return array
   */
  function displayListForm($uid, array $params = array())
  {
     // Initialize OEF
     $errors = initialize();

     if (!empty($errors)) return array('status' => false, 'errors' => $errors);

     list($kind, $type) = Utility::parseUID($uid);

     // Check interactive permission
     if (defined('IS_SECURE'))
     {
        global $OEF_USER;

        if (!$OEF_USER->hasPermission($kind.'.'.$type.'.Read'))
        {
           return array(
              'status' => false,
              'result' => array(),
              'errors' => array('Access denied')
           );
        }
     }

     // Retrieve data
     $options = empty($params['options']) ? array() : $params['options'];

     $controller = Container::getInstance()->getController($kind, $type, $options);

     if (!method_exists($controller, 'displayListForm')) return array('status' => false, 'errors' => array('Not supported operation'));

     $options['with_link_desc'] = true;

     if (is_a($controller, 'ObjectsController') && empty($params['show_marked_for_deletion']))
     {
        $options['criteria']['attributes'][] = '%deleted';
        $options['criteria']['values']['%deleted'] = 0;
        $options['criteria']['criterion'] = '%deleted = %%deleted%%';
     }
     
     if (!empty($params['ids']))
     {
        $options['criteria']['values']['%pkey'] = $params['ids'];
        $options['criteria']['attributes'][] = '%pkey';
        $options['criteria']['criterion'] = (empty($options['criteria']['criterion']) ? '' : $options['criteria']['criterion'].' AND ').'%pkey IN (%%pkey%%)';
     }
     
     if (!empty($params['sort']) && is_string($params['sort']))
     {
        $pattern = '/^(?:[\s]*(?:(?:`[^\s`\,]+`)|(?:[^\s`\,]+))[\s]*?(?:\sASC|\sDESC|)[\s]*?(?:,(?=[\s]*\S)|\z))+$/i';
        
        if (preg_match($pattern, $params['sort']))
        {
           $options['criteria']['criterion'] = (empty($options['criteria']['criterion']) ? '' : $options['criteria']['criterion'].' ').'ORDER BY '.$params['sort'];
        }
     }

     $page = empty($params['page']) ? 1 : $params['page'];

     $res  = $controller->displayListForm($page, $options);

     if (!empty($res['result']['pagination']))
     {
        $pagin =& $res['result']['pagination'];

        for ($i = $pagin['first']; $i <= $pagin['last']; $i++) $pagin['FOR_MT'][] = $i;
     }

     return $res;
  }

  /**
   * Retrieve data for EditForm
   *
   * @param string $uid
   * @param int $id
   * @param array $params
   * @return array
   */
  function displayEditForm($uid, $id = null, array $params = array())
  {
     // Initialize OEF
     $errors = initialize(true);

     if (!empty($errors)) return array('status' => false, 'errors' => $errors);

     list($kind, $type) = Utility::parseUID($uid);

     // Check interactive permission
     if (defined('IS_SECURE'))
     {
        global $OEF_USER;

        if (!$OEF_USER->hasPermission($kind.'.'.$type.'.Edit'))
        {
           return array(
              'status' => false,
              'result' => array(),
              'errors' => array('Access denied')
           );
        }
     }

     // Retrieve data
     if (!is_null($id) && (int) $id < 0) return array('status' => false, 'errors' => array('Invalid parameter id'));

     $options = empty($params['options']) ? array() : $params['options'];

     $controller = Container::getInstance()->getController($kind, $type, $options);

     if (!method_exists($controller, 'displayEditForm')) return array('status' => false, 'errors' => array('Not supported operation'));

     return $controller->displayEditForm($id, $options);
  }

  /**
   * Retrieve data for ItemForm
   *
   * @param string $uid
   * @param int $id
   * @param array $params
   * @return array
   */
  function displayItemForm($uid, $id, array $params = array())
  {
     // Initialize OEF
     $errors = initialize();

     if (!empty($errors)) return array('status' => false, 'errors' => $errors);

     list($kind, $type) = Utility::parseUID($uid);

     // Check interactive permission
     if (defined('IS_SECURE'))
     {
        global $OEF_USER;

        if (!$OEF_USER->hasPermission($kind.'.'.$type.'.Read'))
        {
           return array(
              'status' => false,
              'result' => array(),
              'errors' => array('Access denied')
           );
        }
     }

     // Retrieve data
     if (!is_null($id) && (int) $id < 0) return array('status' => false, 'errors' => array('Invalid parameter id'));

     $options = empty($params['options']) ? array() : $params['options'];

     $controller = Container::getInstance()->getController($kind, $type, $options);

     if (!method_exists($controller, 'displayItemForm')) return array('status' => false, 'errors' => array('Not supported operation'));

     $options['with_link_desc'] = true;

     return $controller->displayItemForm((int) $id, $options);
  }

  /**
   * Retrieve data for ReportForm
   *
   * @param string $uid
   * @param array $params
   * @return array
   */
  function displayReportForm($uid, array $params = array())
  {
     // Initialize OEF
     $errors = initialize(true);

     if (!empty($errors)) return array('status' => false, 'errors' => $errors);

     list($kind, $type) = Utility::parseUID($uid);

     // Check interactive permission
     if (defined('IS_SECURE'))
     {
        global $OEF_USER;

        if (!$OEF_USER->hasPermission($kind.'.'.$type.'.Use'))
        {
           return array(
              'status' => false,
              'result' => array(),
              'errors' => array('Access denied')
           );
        }
     }

     // Retrieve data
     $headline = empty($params['headline']) ? array() : $params['headline'];
     $options  = empty($params['options'])  ? array() : $params['options'];

     $controller = Container::getInstance()->getController($kind, $type, $options);

     if (!method_exists($controller, 'displayReportForm')) return array('status' => false, 'errors' => array('Not supported operation'));

     return $controller->displayReportForm($headline, $options);
  }

  /**
   * Retrieve data for ImportForm
   *
   * @param string $uid
   * @param array $params
   * @return array
   */
  function displayImportForm($uid, array $params = array())
  {
     // Initialize OEF
     $errors = initialize(true);

     if (!empty($errors)) return array('status' => false, 'errors' => $errors);

     list($kind, $type) = Utility::parseUID($uid);

     // Check interactive permission
     if (defined('IS_SECURE'))
     {
        global $OEF_USER;

        if (!$OEF_USER->hasPermission($kind.'.'.$type.'.Use'))
        {
           return array(
              'status' => false,
              'result' => array(),
              'errors' => array('Access denied')
           );
        }
     }

     // Retrieve data
     $options  = empty($params['options'])  ? array() : $params['options'];

     $controller = Container::getInstance()->getController($kind, $type, $options);

     if (!method_exists($controller, 'displayImportForm')) return array('status' => false, 'errors' => array('Not supported operation'));

     return $controller->displayImportForm($options);
  }

  /**
   * Retrieve data for ConstantsForm
   *
   * @param array $params
   * @return array
   */
  function displayConstantsForm($params = array())
  {
     // Initialize OEF
     $errors = initialize();

     if (!is_array($params)) $params = array();

     if (!empty($errors)) return array('status' => false, 'errors' => $errors);

     // Check interactive permission
     if (defined('IS_SECURE'))
     {
        global $OEF_USER;

        if (!$OEF_USER->isAdmin())
        {
           return array(
              'status' => false,
              'result' => array(),
              'errors' => array('Access denied')
           );
        }
     }

     // Retrieve data
     $options  = empty($params['options'])  ? array() : $params['options'];

     $controller = Container::getInstance()->getController('Constants', null, $options);

     if (!method_exists($controller, 'displayEditForm')) return array('status' => false, 'errors' => array('Not supported operation'));

     return $controller->displayEditForm($options);
  }

  /**
   * Retrieve data for DeleteMarkedForDeletion form
   *
   * @param array $params
   * @return array
   */
  function displayDeletionForm($params = array())
  {
     // Initialize OEF
     $errors = initialize();

     if (!is_array($params)) $params = array();

     if (!empty($errors)) return array('status' => false, 'errors' => $errors);

     // Check interactive permission
     if (defined('IS_SECURE'))
     {
        global $OEF_USER;

        if (!$OEF_USER->isAdmin())
        {
           return array(
              'status' => false,
              'result' => array(),
              'errors' => array('Access denied')
           );
        }
     }

     // Retrieve data
     $options  = empty($params['options'])  ? array() : $params['options'];

     $controller = Container::getInstance()->getObjectDeletionController($options);

     if (!method_exists($controller, 'displayDeletionForm')) return array('status' => false, 'errors' => array('Not supported operation'));

     return $controller->displayDeletionForm($options);
  }

  /**
   * Retrieve data for TreeListForm
   *
   * @param string $uid
   * @param array $params
   * @return array
   */
  function displayTreeList($uid, array $params = array())
  {
     // Initialize OEF
     $errors = initialize();

     if (!empty($errors)) return array('status' => false, 'errors' => $errors);

     list($kind, $type) = Utility::parseUID($uid);

     // Check interactive permission
     if (defined('IS_SECURE'))
     {
        global $OEF_USER;

        if (!$OEF_USER->hasPermission($kind.'.'.$type.'.Read'))
        {
           return array(
              'status' => false,
              'result' => array(),
              'errors' => array('Access denied')
           );
        }
     }

     // Retrieve data
     $options = empty($params['options']) ? array() : $params['options'];

     $controller = Container::getInstance()->getController($kind, $type, $options);

     if (!method_exists($controller, 'getChildren')) return array('status' => false, 'errors' => array('Not supported operation'));

     $options['with_link_desc'] = true;

     if (!empty($params['show_marked_for_deletion']))
     {
        $options['criteria']['values'] = empty($params['ids']) ? array() : $params['ids'];
     }
     else
     {
        $options['criteria']['attributes'][] = '%deleted';

        if (!empty($params['ids']))
        {
           $options['criteria']['values']['%pkey'] = $params['ids'];
           $options['criteria']['attributes'][] = '%pkey';
           $options['criteria']['criterion'] = '%deleted = 0 AND %pkey IN (%%pkey%%)';
        }
        else {
           $options['criteria']['values']['%deleted'] = 0;
           $options['criteria']['criterion'] = '%deleted = %%deleted%%';
        }
     }

     $res = $controller->getChildren(null, $options);

     return $res;
  }




  /**
   * Get internal configuration by kind
   *
   * @param string $kind
   * @param string $type
   * @return array
   */
  function getInternalConfiguration($kind, $type = false)
  {
     $errors = initialize();

     if (!empty($errors)) return array();

     if ($type == 'null') $type = null;

     return Container::getInstance()->getConfigManager()->getInternalConfigurationByKind($kind, $type);
  }

  /**
   * Parse entity UID
   *
   * @param string $uid
   * @return array
   */
  function parseUID($uid)
  {
     $errors = initialize();

     if (!empty($errors)) return array('status' => false, 'errors' => $errors);

     try
     {
        list($kind, $type) = Utility::parseUID($uid);

        $result = Utility::parseKindString($kind);
        $result['type'] = $type;
     }
     catch (Exception $e)
     {
        return array('status' => false, 'errors' => array('Invalid UID'));
     }

     return $result;
  }

  /**
   * Execute object query
   *
   * @param string $query
   * @param array $params
   * @return array
   */
  function executeQuery($query, array $params = array())
  {
     $errors = initialize();

     if (!empty($errors)) return array('status' => false, 'errors' => $errors);

     $options = empty($params['options']) ? array() : $params['options'];
     $db_opt  = isset($options['db']) ? $options['db'] : array();
     $qu_opt  = isset($options['query']) ? $options['query'] : array();

     $db = Container::getInstance()->getODBManager($db_opt);

     if (($data = $db->loadAssocList((string) $query, $qu_opt)) === null)
     {
        $status = false;
        $errors = array($db->getError());
     }
     else $status = true;

     return array('status' => $status, 'result' => $data, 'errors' => $errors);
  }

  /**
   * Generate custom form
   *
   * @param string $uid
   * @param string $name
   * @param array $params
   * @return array
   */
  function generateCustomForm($uid, $name, array $params = array())
  {
     // Initialize OEF
     $errors = initialize(true);

     if (!empty($errors)) return array('status' => false, 'errors' => $errors);

     list($kind, $type) = Utility::parseUID($uid);

     // Check interactive permission
     if (defined('IS_SECURE'))
     {
        global $OEF_USER;

        if ($kind == 'reports' || $kind == 'data_processors')
        {
           $perm = 'Use';
        }
        else $perm = 'Edit';

        if (!$OEF_USER->hasPermission($kind.'.'.$type.'.'.$perm))
        {
           return array(
              'status' => false,
              'result' => array(),
              'errors' => array('Access denied')
           );
        }
     }

     // Retrieve form
     $options = empty($params['options'])  ? array() : $params['options'];

     $controller = Container::getInstance()->getController($kind, $type, $options);

     if (!method_exists($controller, 'generateCustomForm'))
     {
        return array('status' => false, 'errors' => array('Not supported operation'));
     }

     return $controller->generateCustomForm($name, $options);
  }

  /**
   * Generates date string for display in templates
   *
   * @param string $date - "Y-m-d H:i:s"
   * @return string or null
   */
  function getFormattedDate($date, $format = null)
  {
     $dt = explode(' ', $date);
     $vals = explode('-', $dt[0]);
     $vals[0] = (int) $vals[0];
     $vals[1] = (int) $vals[1];
     $vals[2] = (int) $vals[2];

     if (empty($vals[0])) return null;

     if (!empty($dt[1])) $time = explode(':', $dt[1]);

     $vals[3] = isset($time[0]) ? (int) $time[0] : 0;
     $vals[4] = isset($time[1]) ? (int) $time[1] : 0;
     $vals[5] = isset($time[2]) ? (int) $time[2] : 0;

     $mt = mktime($vals[3], $vals[4], $vals[5], $vals[1] ? $vals[1] : 1, $vals[2] ? $vals[2] : 1, $vals[0]);

     if (empty($format))
     {
        if (empty($vals[1]))     $format = '%Y';
        elseif (empty($vals[2])) $format = '%b %Y';
        elseif (empty($dt[1]))   $format = '%d.%m.%y';
        else                     $format = '%d.%m.%y %H:%M:%S';
     }

     return strftime($format, $mt);
  }
  
  /**
   * Get current applied solution name
   * 
   * @return array
   */
  function getAppliedSolutionName()
  {
     $appName = getApplicationName();
     
     if ($appName[0] === -1 || $appName[0] === -2)
     {
         return array('status' => false, 'errors' => array('Initialize error'));
     }
     
     return array('status' => true, 'result' => $appName[1]);
  }
  
  /**
   * Return current applied solution name
   * 
   * @return array
   */
  function getApplicationName()
  {
     $pagePath='';
     foreach (split(',',$_SERVER['HTTP_X_DEKISCRIPT_ENV']) as  $value) {
        if (strpos($value,'page.path')!==false)
        {
           $pagePath = $value;
           break;
        }
     }
     if (strlen($pagePath)<=0)
        return array(-2,$_SERVER['HTTP_X_DEKISCRIPT_ENV']);
     $tmp = split('"',$pagePath);
     $pagePath = $tmp[1];

     $root_path = ExternalConfig::$extconfig['installer']['root_path'];
     foreach ($root_path as $key => $value)
     {
        if (strpos($pagePath,$key)!==false)
           return array(0,$value);
     }
     return array(-2,$pagePath);
  }
  
  /**
   * Return relative path to upload dir
   * 
   * @param string $kind
   * @param string $type
   * @param string $attr
   * @return string
   */
  function getUploadDir($kind = null, $type = null, $attr = null)
  {
     $errors = initialize();

     if (!empty($errors)) return array('status' => false, 'errors' => $errors);

     $dir = Utility::getUploadDir($kind, $type, $attr);

     return array(
        'status' => true,
        'result' => $dir
     );
  }
?>
