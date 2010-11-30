<?php 

$base = '';

require_once($base.'lib/utility/Loader.php');
require_once($base.'lib/utility/Utility.php');
require_once($base.'lib/container/Container.php');
require_once($base.'lib/report/Mockup.php');
require_once($base.'lib/report/TabularDoc.php');

if (!isset($container_options)) $container_options = array();

$container = Container::createInstance($container_options);

$modules = $container->getModulesManager();
$modules->loadGlobalModules();
$modules->loadModules('catalogs');
$modules->loadModules('documents');
$modules->loadModules('information_registry');
$modules->loadModules('reports');
$modules->loadModules('data_processors');
$modules->loadModules('web_services');
