<?php
$DEKIWIKI_ROOT = 'C:/Program files/MindTouch/MindTouch/web/';
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
      'base_dir' => '/ext/AE',
      'root_templates' => '/Templates',
      'templates_map'  => '/templates_map.php',
      'api' => $DEKIWIKI_API
   )
);
?>