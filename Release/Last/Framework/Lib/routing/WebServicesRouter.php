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
      
      $this->parameters['application'] = isset($params[0]) ? $params[0] : null;
      $this->parameters['kind']        = isset($params[1]) ? $params[1] : null;
      $this->parameters['type']        = isset($params[2]) ? $params[2] : null;
      $this->parameters['action']      = isset($params[3]) ? 'get'.$params[3] : null;
      
      for ($i = 4; $i < $count; $i++)
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