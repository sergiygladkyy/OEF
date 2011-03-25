<?php

class Upload
{
   const DEFAULT_FORM_PREFIX = 'oefForms';
   
   protected static $instance = null;
   protected static $conf = array();
   
   protected $last_uploaded = array();
   
   /**
    * Create new instance
    *
    * @throws Exception
    * @param array $options
    * @return this
    */
   public static function createInstance(array $options = array())
   {
      if (is_object(self::$instance) && is_a(self::$instance, 'Upload'))
      {
         return self::$instance;
      }
      
      self::$instance = new self($options);
      
      return self::$instance;
   }

   /**
    * Get this instance
    *
    * @throws Exception
    * @return this
    */
   public static function getInstance()
   {
      if (is_null(self::$instance))
      {
         throw new Exception(__METHOD__.": Instance is not exists");
      }

      return self::$instance;
   }
   
   /**
    * Constructor
    * 
    * @param array& $options
    * @return void
    */
   protected function __construct(array& $options = array())
   {
      if (empty($options['upload_dir']))
      {
         throw new Exception(__METHOD__.": Unknow upload dir");
      }
      
      // Check upload dir
      if (!is_string($options['upload_dir']))
      {
         throw new Exception(__METHOD__.": Invalid upload dir");
      }
      
      if ($error = $this->checkDir($options['upload_dir']))
      {
         throw new Exception(__METHOD__.": ".$error);
      }
      
      self::$conf = $options;
      
      // Check form prefix
      if (!isset($options['form_prefix']))
      {
         self::$conf['form_prefix'] = self::DEFAULT_FORM_PREFIX;
      }
      elseif (!is_string($options['form_prefix']))
      {
         throw new Exception(__METHOD__.": Invalid form prefix");
      }
   }
   
   /**
    * Return original file name
    * 
    * @param string $kind - entity kind
    * @param string $type - entity type
    * @param string $attribute - file attribute name
    * @param array  $options
    * @return string or null
    */
   public function getName($kind, $type, $attribute, array $options = array())
   {
      if (isset($_FILES[self::$conf['form_prefix']]['name'][$kind][$type]['attributes'][$attribute]))
      {
         return (string) $_FILES[self::$conf['form_prefix']]['name'][$kind][$type]['attributes'][$attribute];
      }
      
      return null;
   }
   
   /**
    * Return MIME-type
    * 
    * @param string $kind - entity kind
    * @param string $type - entity type
    * @param string $attribute - file attribute name
    * @param array  $options
    * @return string or null
    */
   public function getType($kind, $type, $attribute, array $options = array())
   {
      if (isset($_FILES[self::$conf['form_prefix']]['type'][$kind][$type]['attributes'][$attribute]))
      {
         return (string) $_FILES[self::$conf['form_prefix']]['type'][$kind][$type]['attributes'][$attribute];
      }
      
      return null;
   }
   
   /**
    * Return tmp name
    * 
    * @param string $kind - entity kind
    * @param string $type - entity type
    * @param string $attribute - file attribute name
    * @param array  $options
    * @return string or null
    */
   public function getTmpName($kind, $type, $attribute, array $options = array())
   {
      if (isset($_FILES[self::$conf['form_prefix']]['tmp_name'][$kind][$type]['attributes'][$attribute]))
      {
         return (string) $_FILES[self::$conf['form_prefix']]['tmp_name'][$kind][$type]['attributes'][$attribute];
      }
      
      return null;
   }
   
   /**
    * Return error code
    * [
    *   UPLOAD_ERR_OK
    *     return 0;
    *   
    *   UPLOAD_ERR_INI_SIZE
    *     return 1; upload_max_filesize in php.ini.
    *   
    *   UPLOAD_ERR_FORM_SIZE
    *     return 2; MAX_FILE_SIZE in HTML-form.
    *   
    *   UPLOAD_ERR_PARTIAL
    *     return: 3;
    *   
    *   UPLOAD_ERR_NO_FILE
    *     return 4;
    * ]
    * @param string $kind - entity kind
    * @param string $type - entity type
    * @param string $attribute - file attribute name
    * @param array  $options
    * @return int or null
    */
   public function getError($kind, $type, $attribute, array $options = array())
   {
      if (isset($_FILES[self::$conf['form_prefix']]['error'][$kind][$type]['attributes'][$attribute]))
      {
         return (int) $_FILES[self::$conf['form_prefix']]['error'][$kind][$type]['attributes'][$attribute];
      }
      
      return null;
   }
   
   /**
    * Return file size
    * 
    * @param string $kind - entity kind
    * @param string $type - entity type
    * @param string $attribute - file attribute name
    * @param array  $options
    * @return int or null
    */
   public function getSize($kind, $type, $attribute, array $options = array())
   {
      if (isset($_FILES[self::$conf['form_prefix']]['size'][$kind][$type]['attributes'][$attribute]))
      {
         return (int) $_FILES[self::$conf['form_prefix']]['size'][$kind][$type]['attributes'][$attribute];
      }
      
      return null;
   }
   
   /**
    * Save files
    * 
    * @param string $kind - entity kind
    * @param string $type - entity type
    * @param string $attribute - file attribute name
    * @param array  $options
    * @return string or null
    */
   public function saveUploadedFile($kind, $type, $attribute, array $options = array())
   {
      $this->last_uploaded = array();
      
      $dir = $this->getDirName($kind, $type, $attribute, $options);
      
      $settings = isset($options['settings']) && is_array($options['settings']) ? $options['settings'] : array();
      
      // Validation
      if (null == ($file['tmp_name'] = $this->getTmpName($kind, $type, $attribute, $options)))
      {
         return null;
      }
      
      if (!is_uploaded_file($file['tmp_name']))
      {
         throw new Exception('Invalid file');
      }
      
      if (isset($settings['max_file_size']) && filesize($file['tmp_name']) > $settings['max_file_size'])
      {
         throw new Exception('File size must not exceed '.$settings['max_file_size']);
      }
      
      $file['name'] = $this->getName($kind, $type, $attribute, $options);
      
      $ext = substr($file['name'], 1 + strrpos($file['name'], "."));
      
      if (isset($settings['allowed_exts']) && !in_array($ext, $settings['allowed_exts']))
      {
         throw new Exception('Invalid file type. File must be only: '.implode(', ', $settings['allowed_exts']));
      }
      
      
      // Save file
      $fname = $this->generateFilename($file['name']);
      
      if (!isset($settings['image']) || empty($settings['image']))
      {
         if (!copy($file['tmp_name'], $dir.$fname))
         {
            throw new Exception('Can\'t save file');
         }
         
         $this->last_uploaded = array($dir.$fname);
      }
      else
      {
         $params = $settings['image'];
         
         if (isset($params['max_width']) || isset($params['max_height']))
         {
            if (isset($params['max_width']))
            {
               $params['']['max_width'] = $params['max_width'];
               unset($params['max_width']);
            }
             
            if (isset($params['max_height']))
            {
               $params['']['max_height'] = $params['max_height'];
               unset($params['max_height']);
            }
            
            $not_copy = true;
         }
         
         $result = Utility::resizeAndSaveImage($file['tmp_name'], $ext, $dir, $fname, $params);
         
         if (!$not_copy)
         {
            if (!copy($file['tmp_name'], $dir.$fname))
            {
               throw new Exception('Can\'t save file');
            }
            
            $result['result'][] = $dir.$fname;
         }
         
         $this->last_uploaded = $result['result'];
         
         if (!$result['status'])
         {
            throw new Exception(implode(' ', $result['errors']), 1);
         }
      }
      
      return $fname;
   }
   
   /**
    * Generate, check and return dir name for current entity attribute
    * 
    * @throws Exception
    * @param string $kind - entity kind
    * @param string $type - entity type
    * @param string $attribute - file attribute name
    * @param array  $options
    * @return string
    */
   protected function getDirName($kind, $type, $attribute, array& $options = array())
   {
      //$dir = self::$conf['upload_dir'].$kind.'/'.$type.'/'.$attribute.'/';
      $dir = Utility::getAbsolutePathToUploadDir($kind, $type, $attribute);
      
      if ($error = $this->checkDir($dir, $options))
      {
         throw new Exception($error);
      }
      
      return $dir;
   }
   
   /**
    * Generate random file name
    * 
    * @param string $fname - original file name
    * @return string
    */
   protected function generateFilename($fname)
   {
      $ext = substr($fname, 1 + strrpos($fname, "."));
      
      return sha1(date('Y-m-d H:i:s').rand(11111, 99999).$fname).'.'.$ext;
   }
   
   /**
    * Check dir
    * 
    * @param string $dir
    * @param array  $options
    * @return string - errors
    */
   protected function checkDir($dir, array& $options = array())
   {
      $error = '';
      
      if (!file_exists($dir))
      {
         if (!mkdir($dir, 0777, true))
         {
            $error = "Can't create dir ".$dir;
         }
      }
      elseif (!is_dir($dir))
      {
         $error = $dir." is not a dir";
      }
      elseif (!is_writable($dir))
      {
         $error = "Dir ".$dir." is not writeable";
      }
      
      return $error;
   }

   /**
    * Remove last uploaded files
    * 
    * @return array - errors
    */
   public function removeLastUploaded()
   {
      $errors = array();
      
      foreach ($this->last_uploaded as $file)
      {
         if (!file_exists($file)) continue;
         
         if (!unlink($file)) $errors[] = 'Can\'t delete file "'.$file.'"';
      }
      
      return $errors;
   }
}
