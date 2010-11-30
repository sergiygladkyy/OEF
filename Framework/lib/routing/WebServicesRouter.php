<?php

require_once('lib/routing/Router.php');

class WebServicesRouter extends Router
{
   /**
    * (non-PHPdoc)
    * @see lib/routing/Router#processParse($uri)
    */
   protected function processParse($uri)
   {
      $parts = parse_url($uri);
      
      if (!empty($parts['query']))
      {
         parse_str($parts['query'], $this->parameters);
      }
      
      if (empty($parts['path'])) return $this->parameters;
      
      $params = explode('/', $parts['path']);
      $count  = count($params);
      
      for ($i = 0; $i < $count; $i++)
      {
         if ($params[$i] == 'webservices') break;
      }
      
      $this->parameters['solution'] = isset($params[(++$i)]) ? $params[$i] : null;
      $this->parameters['kind']     = isset($params[(++$i)]) ? $params[$i] : null;
      $this->parameters['type']     = isset($params[(++$i)]) ? $params[$i] : null;
      $this->parameters['action']   = isset($params[(++$i)]) ? 'get'.$params[$i] : null;
      
      for ($i++; $i < $count; $i++)
      {
         $attr = explode('=', $params[$i]);
         $this->parameters[$attr[0]] = isset($attr[1]) ? $attr[1] : true;
      }
      
      return $this->parameters;
   }
   
   /**
    * (non-PHPdoc)
    * @see lib/routing/Router#processGenerate($url)
    */
   protected function processGenerate($url)
   {}
}