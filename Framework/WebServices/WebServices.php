<?php

require_once('config/init.php');
require_once('lib/routing/WebServicesRouter.php');

$container = Container::createInstance();

$request = $container->getRequest();
/*$router  = new WebServicesRouter();
$params  = $router->parseURI($request->getUri());

$request->addGetParameters($params);
*/
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
   $responce->setStatusCode('500');
   $responce->sendHttpHeaders();
   exit;
}

$controller->execute();
