<?php
$DEKIWIKI_ROOT = 'C:/Program files/MindTouch/MindTouch/web';
$DEKIWIKI_API = 'http://mars/@api';
$dbHost = "localhost";
$dbUser = "root";
$dbPassword = "3Parere3";
$dbName = "wikidb";
$stripCSV = "stripped.txt";

// to avoid global cleanup
class ExternalConfig {
   public static $extconfig;
}

ExternalConfig::$extconfig = array (
   'installer' => array(
      'api'  => $DEKIWIKI_API,
      'root' => $DEKIWIKI_ROOT,
      'base_dir' => '/ext/OEF',
      'base_for_deki_ext' => '/OEF',
      'framework_dir' => '/Framework',
      'templates_dir'  => '/MindTouch/Templates',
      'templates_map'  => '/MindTouch/Templates/templates_map.php',
      'applied_solutions_dir'  => '/AppliedSolutions',
      'solution_templates_map' => '/MindTouch/Templates/templates_map.php',
      'root_path' => array(
          'AWPAnalytics'   => 'AWPAnalytics',
          'OiltecIntranet' => 'OiltecIntranet'
      )
   )
);
?>