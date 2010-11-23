<?php

class Utility
{
   /**
    * Load array from file
    * 
    * @throws Exception
    * @param string $path
    * @param array& $options
    * @return array
    */
   public static function loadArrayFromFile($path, array & $options = array())
   {
      if (!file_exists($path)) throw new Exception(__METHOD__.': The file '.$path.' does not exist');

      include($path);
      
      if (!isset($_conf))
      {
         $start  = strrpos($path, '/') + 1;
         $length = strrpos($path, '.') - $start;
         $varName = '_'.substr($path, $start, $length); //basename($path, ".php");
         
         return (isset($$varName) && is_array($$varName)) ? $$varName : array();
      }
      
      return is_array($_conf) ? $_conf : array();
   }
   
   /**
    * Convert PHP array to PHP string
    * 
    * @param array $array
    * @param int $level
    * @return string
    */
   public static function convertArrayToPHPString(array& $array, $level = 0)
   {
      $flag = true;
      $str = "array(";
      
      foreach ($array as $key => $value)
      {
         if (is_object($key) || is_object($value)) return '';
         
         if (!is_numeric($key)) $key = "'".str_replace(array('\\', "'"), array('\\\\', "\'"), $key)."'";
         
         if (is_null($value))
         {
            $value = 'null';
         }
         elseif (is_array($value))
         {
            $value = self::convertArrayToPHPString($value, $level+1);
         }
         elseif (is_bool($value))
         {
            $value = $value ? 'true' : 'false';
         }
         elseif (!is_numeric($value))
         {
            $value = "'".str_replace(array('\\', "'"), array('\\\\', "\'"), $value)."'";
         }
         
         if ($flag) $flag = false;
         else $str .= ",";
         
         $str .= "\n".str_repeat(' ', ($level+1)*3).$key." => ".$value;
      }
      
      return $str."\n".str_repeat(' ', $level*3).")";
   }

   /**
    * Convert PHP array to JSON string
    * 
    * @param array $array
    * @param int $level
    * @return string
    */
   public static function convertArrayToJSONString($array, $level = 0)
   {
      $flag = true;
      $str = "{";

      foreach($array as $key => $value)
      {
         // If key or value is object - return empty string
         if(is_object($key) || is_object($value)) return '';
          
         // Convert key
         if(!is_numeric($key)) $key = "'".str_replace(array('\\', "'"), array('\\\\', "\'"), $key)."'";
          
         // Convert value
         if (is_null($value))
         {
            $value = 'null';
         }
         elseif(is_array($value))
         {
            $value = self::convertArrayToJSONString($value, $level+1);
         }
         elseif(is_bool($value))
         {
            $value = $value ? 'true' : 'false';
         }
         elseif(!is_numeric($value))
         {
            $value = "'".str_replace(array('\\', "'"), array('\\\\', "\'"), $value)."'";
         }
          
         if($flag) $flag = false;
         else $str .= ",";
          
         $str .= "\n".str_repeat(' ', ($level+1)*3).$key.": ".$value;
      }

      return $str."\n".str_repeat(' ', $level*3)."}";
   }
   
   /**
    * Retrieve all leaves in tree
    * 
    * @param array& $tree
    * @param array& $result
    * @return array&
    */
   public static function &retrieveTreeLeaves(array& $tree, array& $result = array())
   {
      foreach ($tree as $name => $node)
      {
         if (is_array($node))
         {
            self::retrieveTreeLeaves($node, $result);
            continue;
         }
         
         $result[] = $node;
      }
      
      return $result;
   }
   
   /**
    * Parse kind string
    * 
    * return array(
    *   'main_kind'
    *   'main_type'
    *   'kind'
    * )
    * 
    * @param $kind - entity kind
    * @return array
    */
   public static function parseKindString($kind)
   {
      $result = array(
        'main_kind' => null,
        'main_type' => null,
        'kind'      => null
      );
      
      $params = explode(".", $kind);
       
      switch (count($params))
      {
         case 1:
            $result['kind'] = $kind;
            break;
         case 3:
            $result['main_kind'] = $params[0];
            $result['main_type'] = $params[1];
            $result['kind'] = $params[2];
            break;
             
         default:
            throw new Exception(__METHOD__.': unknow kind "'.$kind.'"');
      }
      
      return $result;
   }
   
   /**
    * Parse UID
    * 
    * @param string $uid
    * @return array
    */
   public static function parseUID($uid)
   {
      $mathches = array();
      
      preg_match('/^([^\s]+?)\.([^.\s]*)$/', $uid, $mathches);
      
      if (empty($mathches[2])) throw new Exception(__METHOD__.': Invalid UID "'.$uid.'"');
      
      return array($mathches[1], $mathches[2]);
   }
   

   /**
    * Escape special chars for SQL Query
    *
    * @param string $string
    * @return string
    */
   public static function escapeString($string)
   {
      if (get_magic_quotes_gpc()) $string = stripslashes($string);

      $string = trim($string);
      //$string = addslashes($string);
      //strip_tags($string);
      $string = str_replace("\\", "\\\\",   $string);
      $string = str_replace("'",  "&#039;", $string);
      $string = str_replace("<",  "&lt;",   $string);
      $string = str_replace(">",  "&gt;",   $string);
      $string = str_replace("\"", "&quot;", $string);
      //$string = htmlspecialchars($string, ENT_QUOTES);

      return $string;
   }
    
   /**
    * Escape special chars in all strings for SQL Query
    * 
    * @param array $values
    * @return array
    */
   public static function escaper(array $values)
   {
      foreach ($values as $key => $value) $values[$key] = self::escapeString($value);
       
      return $values;
   }
   
   /**
    * Recursive escape special chars in all strings for SQL Query
    * 
    * @param array $values
    * @return array
    */
   public static function escapeRecursive(array $values)
   {
      foreach ($values as $key => $value)
      {
         if (is_array($value))
         {
            $values[$key] = self::escapeRecursive($value);
         }
         else $values[$key] = self::escapeString($value);
      }
       
      return $values;
   }
   
   /**
    * Check name
    * 
    * @param string $name
    * @return string or null
    */
   public static function checkName($name)
   {
      if (!preg_match('/^[\s]*([A-Za-z_][A-Za-z_0-9]*)[\s]*$/i', $name, $matches)) return null;
      
      return $matches[1];
   }
   
   /**
    * Get array value by path
    * 
    * @param array& $array
    * @param string $path    - key_1/key_2/../key_n
    * @param mixed  $default - default value
    * @return mixed
    */
   public static function getArrayValueByPath(array& $array, $path, $default = null)
   {
      if (empty($path) || !is_string($path)) return $default;
      
      $keys  = explode('/', $path);
      $value =& $array;
      
      foreach ($keys as $key)
      {
         if (!is_array($value) || !isset($value[$key])) return $default;

         $value =& $value[$key];
      }
      
      return $value;
   }
}
