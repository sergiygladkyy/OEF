<?php 

require_once('lib/report/Grid.php');
require_once('lib/report/Area.php');

class Mockup extends Grid
{
   public function __construct($filepath)
   {
      $this->parseMockup($filepath);
      
      //echo '<hr><pre>'.print_r($this->area, true).'</pre>';
      //echo '<hr><pre>'.print_r($this->grid, true).'</pre>';
      //echo '<hr><pre>'.print_r($this->size, true).'</pre>';
   }
   
   /**
    * Parse mockup
    * 
    * @param string $filepath
    * @return void
    */
   protected function parseMockup($filepath)
   {
      $sxml = simplexml_load_file($filepath);
      $xml  = $sxml->body->table[0];
      
      $index = 0;
      $current_col = 0;

      foreach ($xml->xpath('colgroup') as $colgroup)
      {
         $index++;
         
         foreach ($colgroup->children() as $col)
         {
            $current_col++;
            
            if (!empty($col['area']))
            {
               $end = $current_col + (isset($col['span']) ? $col['span']: 1) - 1;
               
               $this->addArea($col['area'], array($current_col, 0, $end, 0));
            }
         }
      }

      $this->size['C'] = $current_col;
      
      $index = 0;
      $current_row = 0;

      foreach ($xml->xpath('tr') as $tr)
      {
         /* Row params */
         
         $current_row++;

         if (!empty($tr['area']))
         {
            $this->addArea($tr['area'], array(0, $current_row, 0, $current_row));
         }
         
         $this->grid[$current_row][0] = array();
         $row =& $this->grid[$current_row][0];
         $row['attributes'] = $this->getAttributes($tr);
         
         // Decode
         if (isset($row['attributes']['decode']))
         {
            $row['decode'] = $this->getDecodes($row['attributes']['decode']);
            unset($row['attributes']['decode']);
         }
         
         /* Cell params */
         
         $current_col = 0;
         
         foreach ($tr->children() as $td)
         {
            $current_col++;
            
            // Skip the filled 'rowspan' cells
            while (isset($this->grid[$current_row][$current_col])) $current_col++;
            
            // Params
            $content = $this->getContent($td);
            $this->grid[$current_row][$current_col] = array();
            $cell =& $this->grid[$current_row][$current_col];
            $cell['tag']        = $td->getName(); 
            $cell['content']    = $content;
            $cell['parameters'] = $content ? $this->getParameters($content) : array();
            $cell['attributes'] = $this->getAttributes($td);
            
            // Decode
            if (isset($cell['attributes']['decode']))
            {
               $cell['decode'] = $this->getDecodes($cell['attributes']['decode']);
               unset($cell['attributes']['decode']);
            }
            
            // Current cells Span
            $width  = empty($td['colspan']) ? 0 : $td['colspan'] - 1;
            $height = empty($td['rowspan']) ? 0 : $td['rowspan'] - 1;

            // Area
            if (!empty($td['area']))
            {
               $this->addArea($td['area'], array($current_col, $current_row, $current_col + $width, $current_row + $height));
            }
            
            // Fill Span cells
            if (!$height && !$width) continue;
            
            $end_col = $current_col + $width;
            $end_row = $current_row + $height;
            
            for ($r = $current_row + 1; $r <= $end_row; $r++)
            {
               for ($c = $current_col; $c <= $end_col; $c++)
               {
                  $this->grid[$r][$c]['in'] = array($current_col, $current_row, $end_col, $end_row);
               }
            }

            if (!$width) continue;
            
            for ($c = $current_col + 1; $c <= $end_col; $c++)
            {
               $this->grid[$current_row][$c]['in'] = array($current_col, $current_row, $end_col, $end_row);
            }
            
            $current_col = $end_col;
         }
         
         if ($current_col > $this->size['C']) $this->size['C'] = $current_col;
      }
      
      $this->size['R'] = $current_row;
      
      if (!empty($this->area)) $this->normalizeArea($this->area);
   }
   
   /**
    * Add area info
    * 
    * @param string $names
    * @param array $coord
    * @return boolean
    */
   protected function addArea($names, array $coord)
   {
      if (!preg_match_all('/(?<=[\s]|\A)[A-Za-z_][A-Za-z_0-9]*(?=[\s]|\z)/i', $names, $matches))
      {
         return false;
      }
      
      foreach ($matches[0] as $name)
      {
         $this->area[$name]['coord'][] = $coord;
      }
      
      return true;
   }
   
   /**
    * Get element content
    * 
    * @param SimpleXMLElement $el
    * @return string or null
    */
   protected function getContent(SimpleXMLElement $el)
   {
      $dom_sxe = dom_import_simplexml($el);
      
      if (!$dom_sxe) return null;

      $dom = new DOMDocument();
      $dom_sxe = $dom->importNode($dom_sxe, true);
      $dom_sxe = $dom->appendChild($dom_sxe);

      if (!$content = $dom->saveHTML()) return null;
      
      $start   = strpos($content, '>') + 1;
      $length  = strrpos($content, '<') - $start;
      
      return substr($content, $start, $length);
   }
   
   /**
    * Get contents variables
    * 
    * @param string $content
    * @return array
    */
   protected function getParameters($content)
   {
      if (!preg_match_all('/(?<=\[)[A-Za-z_][A-Za-z_0-9]*?(?=\])/i', $content, $matches)) return array();
      
      return $matches[0];
   }
   
   /**
    * Get attributes list
    * 
    * @param SimpleXMLElement $el
    * @return array
    */
   protected function getAttributes(SimpleXMLElement $el)
   {
      $res = array();

      foreach ($el->attributes() as $name => $value) $res[$name] = (string) $value;
      
      return $res;
   }
   
   /**
    * Return decodes array 
    * 
    * @param string $decodes - 'dec_1 dec_2 ..'
    * @return unknown_type
    */
   protected function getDecodes($decodes)
   {
      $decodes = trim($decodes);
      
      if (empty($decodes)) return array();
      
      if (!preg_match_all('/[\S]+/i', $decodes, $matches)) return array();
      
      $res = array();
      
      foreach ($matches[0] as $decode)
      {
         $res[$decode] = null;
      }
      
      return $res; 
   }
   
   /**
    * Normalize Area
    * 
    * @param array& $area
    * @return array
    */
   protected function normalizeArea(array& $area)
   {
      foreach ($area as $name => &$params)
      {
         if (!is_array($params['coord'][0])) continue;
         
         $coord = $params['coord'][0];
         $cnt = count($params['coord']);
         
         for ($i = 1; $i < $cnt; $i++)
         {
            $_coord =& $params['coord'][$i];
            
            if ($coord[0] > $_coord[0]) $coord[0] = $_coord[0];
            if ($coord[1] > $_coord[1]) $coord[1] = $_coord[1];
            if ($coord[2] < $_coord[2]) $coord[2] = $_coord[2];
            if ($coord[3] < $_coord[3]) $coord[3] = $_coord[3];
         }
         
         $params['coord'] = $coord;
      }
      
      return $area;
   }
   
   /**
    * (non-PHPdoc)
    * @see lib/report/Grid#getArea($id)
    */
   public function getArea($id)
   {
      $grid = parent::getArea($id);
      
      return is_null($grid) ? null : new Area($grid);
   }
}
