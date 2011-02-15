<?php

require_once('lib/report/Grid.php');

class TabularDoc extends Grid
{
   protected $curCol  = 0;
   protected $curRow  = 0;
   protected $curHeight = 0;
   
   
   public function __construct()
   {
   }
   
   /**
    * Join area
    * 
    * @param Area $area
    * @return boolean
    */
   public function join(Area $area)
   {
      $content = $area->getContents();
      
      foreach ($content as $row => $cols)
      {
         $r = $this->curRow + $row; 
         
         foreach ($cols as $col => $param)
         {
            // Merge row attributes
            if ($col == 0)
            {
               if ($this->curCol != 0)
               {
                  $attrs =& $this->grid[$r][0]['attributes'];
                  
                  foreach ($param['attributes'] as $name => $value)
                  {
                     if (!isset($attrs[$name]))
                     {
                        $attrs[$name] = $value;
                     }
                     elseif (false === strpos($attrs[$name], $value))
                     {
                        $attrs[$name] .= ' '.$value;
                     }
                  }
                  
                  if (empty($param['decode'])) continue;
                  
                  if (!empty($this->grid[$r][0]['decode']) && is_array($this->grid[$r][0]['decode']))
                  {
                     $this->grid[$r][0]['decode'] = array_merge($param['decode'], $this->grid[$r][0]['decode']);
                  }
                  else $this->grid[$r][0]['decode'] = $param['decode'];
               }
               else
               {
                  $this->grid[$r][0]['attributes'] = $param['attributes'];
                  
                  if (empty($param['decode'])) continue;
                  
                  $this->grid[$r][0]['decode'] = $param['decode'];
               }
               
               continue;
            }
            
            // Add cells
            $c = $this->curCol + $col;
            if (isset($param['in']))
            {
               $this->grid[$r][$c]['in'] = array(
                  $param['in'][0] + $this->curCol,
                  $param['in'][1] + $this->curRow,
                  $param['in'][2] + $this->curCol,
                  $param['in'][3] + $this->curRow
               );
            }
            else $this->grid[$r][$c] = $param;
         }
      }
      
      $size = $area->getSize();
      
      $this->curCol += $size['C'];
      
      if ($this->curHeight < $size['R']) $this->curHeight = $size['R'];
      
      if ($this->size['C'] < $this->curCol) $this->size['C'] = $this->curCol;
      
      return true;
   }
   
   /**
    * Put area
    * 
    * @param Area $area
    * @return boolean
    */
   public function put(Area $area)
   {
      $this->size['R'] += $this->curHeight;
      $this->curRow += $this->curHeight;
      $this->curCol = 0;
      $this->curHeight = 0;
      
      return $this->join($area);
   }
   
   /**
    * Merge cells
    * 
    * @param string $coord - Cn.Rn : Cm.Rm
    * @return bool
    */
   public function mergeCells($coord)
   {
      $coord = self::parseCoordinate($coord);

      if (!$this->checkCoordinates($coord)) return false;

      $coord = $this->retrieveRectangleArea($coord);

      if (!isset($this->grid[$coord[1]][$coord[0]])) throw new Exception(__METHOD__.': Coordinates is wrong');

      $cell = $this->grid[$coord[1]][$coord[0]];

      for ($r = $coord[1]; $r <= $coord[3]; $r++)
      {
         for ($c = $coord[0]; $c <= $coord[2]; $c++)
         {
            $this->grid[$r][$c]['in'] = $coord;
         }
      }

      $cell['attributes']['colspan'] = $coord[2] - $coord[0] + 1;
      $cell['attributes']['rowspan'] = $coord[3] - $coord[1] + 1;

      $this->grid[$coord[1]][$coord[0]] = $cell;
      
      return true;
   }
   
   /**
    * Return HTML table with contents this document
    * 
    * @return string
    */
   public function show()
   {
      $html = "<table class=\"oef_report\">\n<tbody>";
      
      foreach ($this->grid as $row => $cols)
      {
         // Render row
         $attr = empty($cols[0]['attributes']) ? '' : $this->renderAttributes($cols[0]['attributes']);
         
         if (!empty($cols[0]['decode'])) $attr .= ' '.$this->renderDecodes($cols[0]['decode']);
         
         $html .= "\n<tr".$attr.'>';
         
         unset($cols[0]);
         
         // Render cells
         for ($c = 1; $c <= $this->size['C']; $c++)
         {
            if (!isset($cols[$c]))
            {
               $html .= "\n\t<td class=\"oe_report_none_border\" colspan=\"".($this->size['C']-$c+1).'">&nbsp;</td>';
               break;
            }
            
            $param =& $cols[$c];
            
            if (isset($param['in']))
            {
               $c = $param['in'][2];
               continue;
            }
            
            $attr = empty($param['attributes']) ? '' : $this->renderAttributes($param['attributes']);
            
            if (!empty($param['decode'])) $attr .= ' '.$this->renderDecodes($param['decode']);
            
            $html .= "\n\t<".$param['tag'].$attr.'>'.$param['content'].'</'.$param['tag'].'>';
         }
         
         $html .= "\n</tr>";
      }
      
      return $html."\n</tbody>\n</table>\n";
   }
   
   /**
    * Render tag attributes
    * 
    * @param array $attrs
    * @return string
    */
   protected function renderAttributes(array $attrs)
   {
      $res = '';
      
      foreach ($attrs as $name => $value) $res .= ' '.$name.'="'.$value.'"';
      
      return $res;
   }
   
   /**
    * Render decodes params
    * 
    * @param array $decodes
    * @return string
    */
   protected function renderDecodes(array $decodes)
   {
      return 'ondblclick="javascript: decode(event, '.htmlspecialchars(Utility::convertArrayToJSONString($decodes)).');"';
   }
}