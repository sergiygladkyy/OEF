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
   {//return json_encode($array);
      $flag = true;
      $str = "{";

      foreach($array as $key => $value)
      {
         // If key or value is object - return empty string
         if(is_object($key) || is_object($value)) return '';
          
         // Convert key
         /*if(!is_numeric($key))*/ $key = '"'.str_replace(array('\\', '"'), array('\\\\', '\"'), $key).'"';
         
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
            $value = '"'.str_replace(array('\\', '"'), array('\\\\', '\"'), $value).'"';
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
   
   /**
    * Set array value by path
    * 
    * @param array& $array
    * @param string $path  - key_1/key_2/../key_n
    * @param mixed  $value - new value
    * @return boolean
    */
   public static function setArrayValueByPath(array& $array, $path, $value)
   {
      if (empty($path) || !is_string($path)) return false;
      
      $keys = explode('/', $path);
      $val  =& $array;
      
      foreach ($keys as $key)
      {
         if (!is_array($val) || !isset($val[$key])) return false;

         $val =& $val[$key];
      }
      
      $val = $value;
      
      return true;
   }
   
   /**
    * Remove array value by path
    * 
    * @param array& $array
    * @param string $path    - key_1/key_2/../key_n
    * @return boolean
    */
   public static function removeArrayValueByPath(array& $array, $path)
   {
      if (empty($path) || !is_string($path)) return false;
      
      $keys  = explode('/', $path);
      $value =& $array;
      
      for ($i = 0, $max = count($keys)-1; $i < $max; $i++)
      {
         if (!is_array($value) || !isset($value[$keys[$i]])) return false;

         $value =& $value[$keys[$i]];
      }
      
      unset($value[$keys[$i]]);
      
      return true;
   }
   
   /**
    * Resize and save image
    * 
    * [
    *   $params = array(
    *     '<add_to_new_fname>' => array(
    *        'max_width'  => 150,
    *        'max_height' => 100
    *     ),
    *     ..............................
    *   )
    *   
    *   Saved as $new_fname.<add_to_new_fname>
    * ]
    * 
    * @param string $fname - file name
    * @param string $ext   - extension
    * @param string $dir
    * @param string $new_fname
    * @param array  $params
    * 
    * @return array - errors
    */
   public static function resizeAndSaveImage($fname, $ext, $dir, $new_fname, array $params)
   {
      if ($ext == "gif" || $ext == "GIF")
      {
         $ext = 'gif';
         $createFunc = 'imagecreatefromgif';
         $saveFunc   = 'imagegif';
      }
      elseif ($ext == "jpg" || $ext == "jpeg" || $ext == "JPG" || $ext == "JPEG")
      {
         $ext = 'jpeg';
         $createFunc = 'imagecreatefromjpeg';
         $saveFunc   = 'imagejpeg';
      }
      elseif ($ext == "png" || $ext == "PNG")
      {
         $ext = 'png';
         $createFunc = 'imagecreatefrompng';
         $saveFunc   = 'imagepng';
      }
      else
      {
         return array('Not supported image type');
      }
      
      // Retrieve original size
      $size = getimagesize($fname);
      $base = ($size[0] >= $size[1]) ? $size[0] : $size[1];
      
      // Create new image
      $img = $createFunc($fname);
      
      $errors = array();
      $result = array();
      
      // Create and save resized images
      foreach ($params as $name_pref => $r_size)
      {
         // Calculate new size
         $wk = isset($r_size['max_width'])  ? abs($r_size['max_width'])/$size[0]  : 0;
         $hk = isset($r_size['max_height']) ? abs($r_size['max_height'])/$size[1] : 0;
         
         if (!$wk && !$hk)
         {
            $errors[$name_pref] = 'Invalid image size parameters';
            break;
         }
         
         $k  = ($wk == 0) ? $hk : ($hk == 0) ? $wk : ($wk > $hk) ? $hk : $wk;
         
         $width  = $size[0]*$k;
         $height = $size[1]*$k;
         
         // Create new image
         $new_img = imagecreatetruecolor($width, $height);
         
         $fpath = $dir.'/'.$name_pref.$new_fname;
         
         // Resize
         if (ImageCopyResampled($new_img, $img, 0, 0, 0, 0, $width, $height, $size[0], $size[1]))
         {
            if (!$saveFunc($new_img, $fpath, 100))
            {
               $errors[$name_pref] = "Can't save image";
               break;
            }
         }
         else
         {
            $errors[$name_pref] = "Can't resize image";
            break;
         }
         
         $result[] = $fpath;
      }
      
      return array(
         'status' => $errors ? false : true,
         'result' => $result,
         'errors' => $errors
      );
   }
}
