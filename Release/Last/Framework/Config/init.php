<?php 

$base = '';

require_once($base.'lib/utility/Loader.php');
require_once($base.'lib/utility/Utility.php');
require_once($base.'lib/container/Container.php');
require_once($base.'lib/report/Mockup.php');
require_once($base.'lib/report/TabularDoc.php');

$container = Container::createInstance();

$modules = $container->getModulesManager();
$modules->loadGlobalModules();
$modules->loadModules('catalogs');
$modules->loadModules('documents');
$modules->loadModules('information_registry');
$modules->loadModules('reports');
$modules->loadModules('data_processors');
$modules->loadModules('web_services');
