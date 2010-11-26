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
          "getInternalConfiguration(kind:str, type:str):map" => 'getInternalConfiguration',
          "parseUID(uid:str):map" => 'parseUID',
          "executeQuery(query:str, params:map):map" => 'executeQuery',
          "GetFormattedDate(date:str, format:str):str" => 'getFormattedDate'
      )
  );

  // ------------------------------------------------------------------------

  /**
   * Checking permissions using environment variables
   * 
   * @return boolean
   */ 
  function CheckPermissions()
  {
     $env = $_SERVER["HTTP_X_DEKISCRIPT_ENV"];
     
     if (!strlen($env)) return false;
     
     if (strstr($env, 'user.anonymous="false"')) return true;
        
     return false;
  }

  /**
   * Initialize AE Framework
   * 
   * @return array - errors
   */
  function initialize($full = false)
  {
     if (!CheckPermissions()) return array('You do not have permision to access this page');
     
     $framework = '.'.ExternalConfig::$extconfig['installer']['base_for_deki_ext'];
     
     if (!chdir($framework)) return array('Initialize entity extension error');
     
     if (!$full)
     {
        require_once('lib/utility/Loader.php');
        require_once('lib/utility/Utility.php');
        require_once('lib/container/Container.php');
     }
     else require_once('config/init.php');
     
     $conf =& ExternalConfig::$extconfig['installer'];
     $base_dir = $conf['root'].$conf['base_dir'].$conf['applied_solutions_dir'].'/'.$conf['applied_solution_name'];
     
     Container::createInstance(array('base_dir' => $base_dir));
     
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
     $errors = initialize();
     
     if (!empty($errors)) return array('status' => false, 'errors' => $errors);
      
     list($kind, $type) = Utility::parseUID($uid);
      
     $options = empty($params['options']) ? array() : $params['options'];
      
     $controller = Container::getInstance()->getController($kind, $type, $options);
     
     if (!method_exists($controller, 'displayListForm')) return array('status' => false, 'errors' => array('Not supported operation'));
     
     $options['with_link_desc'] = true;
     
     if (is_a($controller, 'ObjectsController'))
     {
        if (!empty($params['show_deleted']))
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
     if (isset($id) && (int) $id <= 0) return array('status' => false, 'errors' => array('Invalid parameter id'));
     
     $errors = initialize();
     
     if (!empty($errors)) return array('status' => false, 'errors' => $errors);

     list($kind, $type) = Utility::parseUID($uid);
     
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
     if (empty($id) || (int) $id <= 0) return array('status' => false, 'errors' => array('Invalid parameter id'));
     
     $errors = initialize();
     
     if (!empty($errors)) return array('status' => false, 'errors' => $errors);
     
     list($kind, $type) = Utility::parseUID($uid);

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
     $errors = initialize(true);
     
     if (!empty($errors)) return array('status' => false, 'errors' => $errors);

     list($kind, $type) = Utility::parseUID($uid);
     
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
     $errors = initialize(true);
     
     if (!empty($errors)) return array('status' => false, 'errors' => $errors);

     list($kind, $type) = Utility::parseUID($uid);
     
     $options  = empty($params['options'])  ? array() : $params['options'];
     
     $controller = Container::getInstance()->getController($kind, $type, $options);

     if (!method_exists($controller, 'displayImportForm')) return array('status' => false, 'errors' => array('Not supported operation'));
     
     return $controller->displayImportForm($options);
  }
  
  
  
  
  /**
   * Get internal configuration by kind
   * 
   * @param string $kind
   * @param string $type
   * @return array
   */
  function getInternalConfiguration($kind, $type = null)
  {
     $errors = initialize();
     
     if (!empty($errors)) return array();
     
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
     
     list($kind, $type) = Utility::parseUID($uid);

     $result = Utility::parseKindString($kind);
     $result['type'] = $type;
     
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
?>