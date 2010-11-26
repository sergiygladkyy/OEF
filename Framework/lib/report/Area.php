<?php

class Area
{
   public $parameters = array();
   public $decode  = null;
   protected $grid = array();
   protected $size = array('C' => 0, 'R' => 0);
   
   public function __construct(array $grid)
   {
      if (!empty($grid))
      {
         $this->grid = $grid;
         $this->size['R'] = count($this->grid);
         $this->size['C'] = count($this->grid[1]) - 1;
      }
      //echo '<hr><pre>'.print_r($this->grid, true).'</pre>';
      //echo '<hr><pre>'.print_r($this->size, true).'</pre>';
   }
   
   /**
    * Fill this area
    * 
    * @param array $values
    * @return boolean
    */
   public function fill(array $values)
   {
      $this->parameters = array();
      
      foreach ($values as $name => $value)
      {
         $this->parameters[$name] = $value;
      }
      
      return true;
   }
   
   /**
    * Get area content
    * 
    * @return array
    */
   public function getContents()
   {
      $res = array(); 
      
      foreach ($this->grid as $row => $cols)
      {
         foreach ($cols as $col => $param)
         {
            $res[$row][$col] = $param;
            
            if (isset($param['in'])) continue;
            
            // Decode
            if (!empty($res[$row][$col]['decode']))
            {
               foreach ($res[$row][$col]['decode'] as $decode => &$value)
               {
                  if (isset($this->decode[$decode])) $value = $this->decode[$decode];
               }
            }
            
            if ($col == 0) continue;
            
            // Parameters
            foreach ($res[$row][$col]['parameters'] as $name)
            {
               if (!isset($this->parameters[$name])) continue;
               
               $search[]  = '['.$name.']';
               $replace[] = $this->parameters[$name];
            }
            
            if (!empty($search))
            {
               $res[$row][$col]['content'] = str_replace($search, $replace, $res[$row][$col]['content']);
            }
         }
      }
      
      return $res;
   }
   
   /**
    * Return area size
    * 
    * @return array
    */
   public function getSize()
   {
      return $this->size;
   }
}
