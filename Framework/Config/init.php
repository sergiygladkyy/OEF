<?php 

/* General */

$base = '';

require_once($base.'lib/utility/Loader.php');
require_once($base.'lib/utility/Utility.php');
require_once($base.'lib/container/Container.php');
require_once($base.'lib/report/Mockup.php');
require_once($base.'lib/report/TabularDoc.php');
require_once($base.'lib/controller/Constants/Constants.php');

if (!isset($container_options)) $container_options = array();

$container = Container::createInstance($container_options);

/* Security */

$odb = $container->getODBManager();
$res = $odb->loadAssoc('SELECT count(*) AS cnt FROM catalogs.SystemUsers');

if (!isset($res['cnt'])) throw new Exception('Initialize error');

if ($res['cnt'] > 0) define('IS_SECURE', true);


/* Modules */

$modules = $container->getModulesManager();
$modules->loadGlobalModules();
$modules->loadModules('catalogs');
$modules->loadModules('documents');
$modules->loadModules('information_registry');
$modules->loadModules('reports');
$modules->loadModules('data_processors');
$modules->loadModules('web_services');
$modules->loadModules('Constants');
