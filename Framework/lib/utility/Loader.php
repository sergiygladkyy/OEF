<?php

if(!defined('DS')) define( 'DS', /*DIRECTORY_SEPARATOR*/'/' );

class Loader
{
   public static function import($fPath, $base = '')
   {
      static $included;

      if (!isset($included)) $included = array();

      if (!isset($included[$fPath]))
      {
         $path = $base.str_replace('.', DS, $fPath).'.php';

         if (!file_exists($path)) throw new Exception(__CLASS__.': The file '.$path.' does not exist');
         
         $included[$fPath] = include_once($path);
      }

      return $included[$fPath];
   }
}

function import($fPath, $base = '')
{
   return Loader::import($fPath, $base);
}