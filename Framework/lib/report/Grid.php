<?php

abstract class Grid
{
   /**
    * $grid[<row>][<col>] = array(
    *    'tag'        => [string],
    *    'content'    => [string],
    *    'parameters' => [array],
    *    'attributes' => [array],
    *    'decode'     => [mixed] (optional - array, string, null or not set)
    * );
    * 
    * @var array
    */
   protected $grid = array();
   
   /**
    * Grid attributes
    * 
    * @var array
    */
   protected $attributes = array();
   
   /**
    * $area[name][coord] = array(C1, R1, C2, R2)
    *
    * @var array
    */
   protected $area = array();
   
   /**
    * STYLE tags content
    *  
    * @var array
    */
   protected $css   = array();
   
   protected $size  = array('C' => 0, 'R' => 0);
   protected $cache = array();
   
   
   /**
    * Get grid area by uid
    * [
    *   $id - '<name>', '<name_1> | <name_2>', 'Cn.Rn : Cm.Rm'
    * ]
    * @param string $id
    * @return array or null
    */
   public function getArea($id)
   {
      if (($pos = strpos($id, '|')) !== false)
      {
         $fname = trim(substr($id, 0, $pos));
         $lname = trim(substr($id, $pos + 1));
         
         $grid = $this->getAreaByName($fname, $lname);
      }
      elseif (strpos($id, '.') !== false || strpos($id, ':') !== false)
      {
         if (!$coord = self::parseCoordinate($id)) return null;
         
         $grid = $this->getAreaByCoordinate($coord);
      }
      else $grid = $this->getAreaByName($id);
      
      
      return $grid ? $grid : null;
   }
   
   /**
    * Get area by name
    * 
    * @param string $fname
    * @param string $lname
    * @return array or null - grid
    */
   protected function getAreaByName($fname, $lname = null)
   {
      // Check cache
      $cache = $fname.($lname ? $lname : '');
      
      if (isset($this->cache[$cache])) return $this->cache[$cache];
      
      if (!isset($this->area[$fname])) return null;
      
      // Get coordinates
      if ($lname)
      {
         if (!isset($this->area[$lname])) return null;

         $fcoord =& $this->area[$fname]['coord'];
         $lcoord =& $this->area[$lname]['coord'];

         $coord[0] = $fcoord[0] > $lcoord[0] ? $fcoord[0] : $lcoord[0];
         $coord[1] = $fcoord[1] > $lcoord[1] ? $fcoord[1] : $lcoord[1];
         $coord[2] = ($fcoord[2] != 0 && $fcoord[2] > $lcoord[2]) ? $fcoord[2] : $lcoord[2];
         $coord[3] = ($fcoord[3] != 0 && $fcoord[3] > $lcoord[3]) ? $fcoord[3] : $lcoord[3];
      }
      else
      {
         $coord = $this->area[$fname]['coord'];
      }
      
      // Save in cache
      $this->cache[$cache] = $this->getAreaByCoordinate($coord);

      return $this->cache[$cache];
   }
   
   
   /**
    * Get area by coordinate
    * 
    * @param array $coord
    * @return array or null - grid
    */
   protected function getAreaByCoordinate(array $coord)
   {
      // Check coordinates
      if (!$this->checkCoordinates($coord)) return null;
      
      // Check cache
      $cache = implode(',', $coord);
      
      if (isset($this->cache[$cache])) return $this->cache[$cache];
      
      if ($coord === array(1, 1, $this->size['C'], $this->size['R']))
      {
         $this->cache[$cache] = $this->grid;
         
         return $this->cache[$cache];
      }
      
      // Retrieve coordinates rectangle area
      $coord = $this->retrieveRectangleArea($coord);
      
      // Retrieve area
      $grid = array();
      $_r = 0;
      
      for ($r = $coord[1]; $r <= $coord[3]; $r++)
      {
         $grid[++$_r][0] = $this->grid[$r][0];
         $_c = 0;
         
         for ($c = $coord[0]; $c <= $coord[2]; $c++)
         {
            if (isset($this->grid[$r][$c]['in']))
            {
               $in =& $this->grid[$r][$c]['in'];
               $grid[$_r][++$_c]['in'] = array($in[0]-$coord[0]+1, $in[1]-$coord[1]+1, $in[2]-$coord[0]+1, $in[3]-$coord[1]+1);
            }
            else $grid[$_r][++$_c] = $this->grid[$r][$c];
         }
      }
      
      // Save in cache
      $this->cache[$cache] = $grid;
      
      return $this->cache[$cache];
   }
   
   
   /**
    * Parse coordinate string
    * [
    *   Cn.Rn:Cm.Rm   Cn.Rn:   :Cm.Rm   Cn.Rn   Cn   Rn   Cn:Cm   Rn:Rm
    * ]
    * @param string $str
    * @return array or null
    */  
   public static function parseCoordinate($str)
   {
      $str = trim($str);
      
      if (!$length = strlen($str)) return null;
      
      if (($pos = strpos($str, ':')) !== false)
      {
         if ($pos == 0) // :Cm.Rm
         {
            $coords = array(null, trim(substr($str, 1)));
         } 
         elseif ($pos == $length - 1) // Cn.Rn:
         {
            $coords = array(trim(substr($str, 0, $pos)), null);
         }
         else // Cn.Rn:Cm.Rm   Cn:Cm   Rn:Rm
         {
            $coords = array(trim(substr($str, 0, $pos)), trim(substr($str, $pos + 1)));
         }
      }
      else $coords = array($str, false);
      
      for ($i = 0; $i < 2; $i++)
      {
         if (is_null($coords[$i]))
         {
            $res[2*$i]   = 0;
            $res[2*$i+1] = 0;
            continue;
         }
         elseif ($coords[$i] === false)
         {
            $res[2*$i]   = $res[2*($i-1)];
            $res[2*$i+1] = $res[2*($i-1)+1];
            continue;
         }
         
         if (preg_match('/^[Cc]([0-9]+)[\s]*\.[\s]*[Rr]([0-9]+)$/', $coords[$i], $matches))
         {
            $res[2*$i]   = (int) $matches[1];
            $res[2*$i+1] = (int) $matches[2];
         }
         elseif (preg_match('/^(?:[Cc]([0-9]+)|[Rr]([0-9]+))$/', $coords[$i], $matches))
         {
            $res[2*$i]   = empty($matches[1]) ? 0 : (int) $matches[1];
            $res[2*$i+1] = empty($matches[2]) ? 0 : (int) $matches[2];
         }
         else return null;  
      }
      
      return $res;
   }
   
   /**
    * Check coordinates for this grid
    * 
    * @param array& $coord
    * @return bool 
    */
   protected function checkCoordinates(array& $coord)
   {
      if ($coord[0] > $this->size['C'] || $coord[1] > $this->size['R']) return false;
      
      if ($coord[0] == 0) $coord[0] = 1;
      if ($coord[1] == 0) $coord[1] = 1;
      if ($coord[2] == 0 || $coord[2] > $this->size['C']) $coord[2] = $this->size['C'];
      if ($coord[3] == 0 || $coord[3] > $this->size['R']) $coord[3] = $this->size['R'];
      
      if ($coord[0] > $coord[2] || $coord[1] > $coord[3]) return false;
      
      return true;
   }
   
   /**
    * Retrieve coordinates rectangle Area
    * 
    * @param array $coord
    * @return array
    */
   protected function retrieveRectangleArea(array $coord)
   {
      while (true)
      {
         $in = $coord;

         // Without last row and col
         for ($r = $coord[1]; $r < $coord[3]; $r++)
         {
            for ($c = $coord[0]; $c < $coord[2]; $c++)
            {
               if (!isset($this->grid[$r][$c]['in'])) continue;

               $_in =& $this->grid[$r][$c]['in'];

               if ($in[0] > $_in[0]) $in[0] = $_in[0];
               if ($in[1] > $_in[1]) $in[1] = $_in[1];
               if ($in[2] < $_in[2]) $in[2] = $_in[2];
               if ($in[3] < $_in[3]) $in[3] = $_in[3];
            }
         }
         if ($in === array(1, 1, $this->size['C'], $this->size['R']))
         {
            $coord = $in;
            break;
         }
         
         if ($coord[2] == 0 || $coord[2] > $this->size['C']) $coord[2] = $this->size['C'];
         if ($coord[3] == 0 || $coord[3] > $this->size['R']) $coord[3] = $this->size['R'];
         
         // Last row
         $c = $coord[2];
         for ($r = $coord[1]; $r <= $coord[3]; $r++)
         {
            if (isset($this->grid[$r][$c]['in']))
            {
               $_in =& $this->grid[$r][$c]['in'];

               if ($in[1] > $_in[1]) $in[1] = $_in[1];
               if ($in[2] < $_in[2]) $in[2] = $_in[2];
               if ($in[3] < $_in[3]) $in[3] = $_in[3];

               $r = $_in[3];
               
               continue;
            }
            
            $colspan = isset($this->grid[$r][$c]['attributes']['colspan']) ? $this->grid[$r][$c]['attributes']['colspan'] - 1 : 0;
            
            if ($colspan > 0 && $in[2] < $c + $colspan) $in[2] = $c + $colspan;
         }
         
         // Last col
         $r = $coord[3];
         for ($c = $coord[0]; $c <= $coord[2]; $c++)
         {
            if (isset($this->grid[$r][$c]['in']))
            {
               $_in =& $this->grid[$r][$c]['in'];

               if ($in[0] > $_in[0]) $in[0] = $_in[0];
               if ($in[2] < $_in[2]) $in[2] = $_in[2];
               if ($in[3] < $_in[3]) $in[3] = $_in[3];
               
               $c = $_in[2];
               
               continue;
            }
            
            $rowspan = isset($this->grid[$r][$c]['attributes']['rowspan']) ? $this->grid[$r][$c]['attributes']['rowspan'] - 1 : 0;
            
            if ($rowspan > 0 && $in[3] < $r + $rowspan) $in[3] = $r + $rowspan;
         }
         
         if ($coord === $in) break;
         
         $coord = $in;
      }
      
      return $coord;
   }
   
   /**
    * Clear area cells from grid
    * 
    * @param array $coord
    * @return boolean
    */
   protected function clearArea(array $coord)
   {
      for ($r = $coord[1]; $r <= $coord[3]; $r++)
      {
         for ($c = $coord[0]; $c <= $coord[2]; $c++)
         {
            if (isset($this->grid[$r][$c])) unset($this->grid[$r][$c]);
         }
      }
      
      return true;
   }
   
   /**
    * Get grid attributes
    * 
    * @return array
    */
   public function getGridAttributes()
   {
      return $this->attributes;
   }
   
   /**
    * Set grid attributes
    * 
    * @param array $attrs
    * @return boolean
    */
   public function setGridAttributes(array $attrs)
   {
      $this->attributes = $attrs;
      
      return true;
   }
   
   /**
    * Get CSS style
    * 
    * @return array
    */
   public function getCSS()
   {
      return $this->css;
   }
   
   /**
    * Add css
    * 
    * @param mixed $css - array or string
    * @return boolean
    */
   public function addCSS($css)
   {
      if (empty($css)) return true;
   
      if (is_string($css))
      {
         $this->css[] = $css;
         
         return true;
      }
      
      if (!is_array($css)) return false;
      
      $ret = true;
      
      foreach ($css as $content)
      {
         if (is_string($content))
         {
            $this->css[] = $content;
         }
         else $ret = false;
      }
      
      return $ret;
   }
}
