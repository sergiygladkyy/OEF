<?php

require_once('lib/utility/Utility.php');
require_once('lib/routing/WebServicesRouter.php');

$router = new WebServicesRouter();
$params = $router->parseURI($_SERVER['REQUEST_URI']);

if (!isset($params['solution']))
{
   header($_SERVER['SERVER_PROTOCOL'].' 400 Bad Request'); exit;
}

try 
{
   $container_options = array('base_dir' => '../AppliedSolutions/'.$params['solution']);

   require_once('config/init.php');

   $container = Container::getInstance();
}
catch (Exception $e)
{
   header($_SERVER['SERVER_PROTOCOL'].' 400 Bad Request'); exit;
}

$request = $container->getRequest();
$request->addGetParameters($params);

$options  = array('protocol' => $request->getHttpHeader('SERVER_PROTOCOL', ''));
$responce = $container->getResponce($options);
   
$kind = $request->getRequestParameter('kind');
$type = $request->getRequestParameter('type');

try
{
   $controller = $container->getController($kind, $type);
}
catch (Exception $e)
{
   // Internal Server Error
   $responce->setStatusCode('400');
   $responce->sendHttpHeaders();
   exit;
}

$controller->execute();
