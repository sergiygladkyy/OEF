<?php

require_once('lib/utility/Utility.php');
require_once('lib/routing/WebServicesRouter.php');

// Routing
$router = new WebServicesRouter();
$params = $router->parseURI($_SERVER['REQUEST_URI']);

if (!isset($params['solution']))
{
   header($_SERVER['SERVER_PROTOCOL'].' 400 Bad Request'); echo '400 Bad Request'; exit;
}

// Init OEF
try 
{
   $container_options = array('base_dir' => $_SERVER['DOCUMENT_ROOT'].'/ext/OEF/AppliedSolutions/'.$params['solution']);

   require_once('config/init.php');

   $container = Container::getInstance();
}
catch (Exception $e)
{
   header($_SERVER['SERVER_PROTOCOL'].' 400 Bad Request'); echo '400 Bad Request'; exit;
}

// Init Request
$request = $container->getRequest();
$request->addGetParameters($params);

// Init Responce
$options  = array('protocol' => $request->getHttpHeader('SERVER_PROTOCOL', ''));
$response = $container->getResponse($options);

// Init User
$authinfo = $request->getHttpHeader('OEF-Autorization');
$authtype = 'MTAuth';
$authkey  = '';

if (preg_match('/^[\s]*([\S]+)[\s]*([\S]*)[\s]*$/i', $authinfo, $matches))
{
   $authtype = $matches[1];
   $authkey  = $matches[2];
}

try
{
   $user = $container->getUser($authtype, $authkey);
   
   if (!$user->isAuthenticated())
   {
      $response->setStatusCode('401');
      $response->setContent('401 Unauthorized');
      $response->send();
      exit;
   }
}
catch (Exception $e)
{
   $response->setStatusCode('400');
   $response->setContent($e->getMessage());
   $response->send();
   exit;
}

// Process request
$kind = $request->getRequestParameter('kind');
$type = $request->getRequestParameter('type');

try
{
   $controller = $container->getController($kind, $type);
}
catch (Exception $e)
{
   // Internal Server Error
   $response->setStatusCode('400');
   $response->setContent('400 Bad Request');
   $response->send();
   exit;
}

$controller->execute();
