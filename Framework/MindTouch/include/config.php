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
      'templates_dir'  => '/Templates',
      'templates_map'  => '/Templates/templates_map.php',
      'root_path' => array(
          'Release2' => 'AWPAnalytics',
          'Release1' => 'Oiltec'
      )
   )
);
?>