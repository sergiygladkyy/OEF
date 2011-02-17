<?php

class Pager
{
   protected $kind;
   protected $type;
   protected $criteria;
   protected $config = array();
   protected $offset = 0;
   
   private $amount    = null;
   private $container = null;
   
   /**
    * Construct
    * 
    * @param string $kind
    * @param string $type
    * @param array $options
    * [
    *    array(
    *      'config' => array(
    *        'max_per_page'       => [int] // Max item in page
    *        'max_item_in_scroll' => [int] // Max item in scroll line
    *      ),
    *      'criteria' => array(
    *        'attributes' => [array] // List of attributes for the current entity 
    *                                // belonging to a selection criterion (See 
    *                                // BaseEntitiesModel::generateWhere and 
    *                                // BaseEntitiesModel::generateWhereByCriteria).
    *        'values'    => [mixed]  // Values of attributes
    *        'criterion'  => [string] // Template for WHERE sentence
    *      )
    *    )
    * ]
    * @return void
    */
   public function __construct($kind, $type, array $options = array())
   {
      $this->kind = $kind;
      $this->type = $type;
      
      $this->container = Container::getInstance();
      
      $this->criteria['attributes'] = isset($options['criteria']['attributes']) ? $options['criteria']['attributes'] : null;
      $this->criteria['values']     = isset($options['criteria']['values']) ? $options['criteria']['values'] : null;
      
      if (!empty($options['criteria']['criterion']) && is_string($options['criteria']['criterion']))
      {
         $this->criteria['criterion'] = $options['criteria']['criterion'];

         $pattern = '/LIMIT[\s]+([0-9]+)[\s]*(?:(?:,[\s]*([0-9]+)[\s]*)|\z)$/i';
         
         if (preg_match_all($pattern, $this->criteria['criterion'], $matches, PREG_SET_ORDER))
         {
            $this->offset = $matches[0][1];
            
            $this->criteria['template'] = preg_replace($pattern, '', $this->criteria['criterion']);
         }
         else $this->criteria['template'] = $this->criteria['criterion'];
      }
      else $this->criteria['criterion'] = null;
      
      if (isset($options['config']['max_per_page']) && 
          is_numeric($options['config']['max_per_page']) && 
          0 < (int)  $options['config']['max_per_page']
      )
      {
         $this->config['max_per_page'] = $options['config']['max_per_page'];
      }
      else $this->config['max_per_page'] = 30;
      
      if (isset($options['config']['max_item_in_scroll']) && 
          is_numeric($options['config']['max_item_in_scroll']) &&
          0 < (int)  $options['config']['max_item_in_scroll']
      )
      {
         $this->config['max_item_in_scroll'] = $options['config']['max_item_in_scroll'];
      }
      else $this->config['max_item_in_scroll'] = 15;
   }
   
   /**
    * Obtain all the necessary parameters to display the specified page
    * 
    * @param int $page_numb - page number
    * @param array& $options
    * @return array or null
    */
   public function retrievePage($page_numb, array $options = array())
   {
      $c_opt = isset($options['container']) ? $options['container'] : array();
      
      $values =& $this->criteria['values']; 
      $m_opt['attributes'] =& $this->criteria['attributes'];
      $m_opt['criterion']  =& $this->criteria['criterion'];
      
      $cmodel = $this->container->getCModel($this->kind, $this->type, $c_opt);

      $this->amount = $amount = $cmodel->countEntities($values, $m_opt);
      
      if ($amount > 0)
      {
         $params = $this->config;
         
         if (!empty($options['scroll'])) $params = array_merge($params, $options['scroll']);
         
         if ($amount > $params['max_per_page'])
         {
            $pagination = $this->getScrollParameters(
               $amount,
               $params['max_per_page'],
               $page_numb,
               $params['max_item_in_scroll']
            );
            
            $current = $pagination['current'];
         }
         else $current = 1;
         
         if (!empty($options)) $m_opt = array_merge($options, $m_opt);
         
         $m_opt['criterion']  = (isset($this->criteria['template']) ? $this->criteria['template'] : '');
         $m_opt['criterion'] .= ' LIMIT '.($this->offset + ($current - 1) * $params['max_per_page']).', '.$params['max_per_page'];
         
         $list = $cmodel->getEntities($values, $m_opt);      
         
         if (is_null($list)) return null;
         
         if (!isset($list['list'])) $list = array('list' => $list);
         
         if (isset($pagination)) $list['pagination'] = $pagination; 
      }
      else $list = array('list' => array());
      
      return $list;
   }
   
   /**
    * Return the number of entries received for the generation of the current page, 
    * the relevant current criterion.
    * 
    * @return int or null
    */
   public function getAmountPage()
   {
      return $this->amount;
   }
   
   /**
    * Get the parameters for pager
    *
    * @param $amount             - amount items
    * @param $max_per_page       - amount of items per page
    * @param $page_numb          - current page number
    * @param $max_item_in_scroll - max item in sroll line
    *
    * @return array
    */
   protected function getScrollParameters($amount, $max_per_page, $page_numb, $max_item_in_scroll)
   {
      $amount_page = ceil($amount/$max_per_page);

      // Validate the page number
      if (!empty($page_numb) && is_numeric($page_numb) && ($page_numb <= $amount_page) && ($page_numb > 0))
      {
         $scr_par['current'] = (int) $page_numb;
      }
      else
      {
         $scr_par['current'] = 1;
      }

      /* Scroll parameters */

      // Calculate first and last page number to scroll line
      $aver_view_numb = ceil($max_item_in_scroll / 2);
      
      if ($amount_page <= $max_item_in_scroll)
      {
         $scr_par['first'] = 1;
         $scr_par['last']  = $amount_page;
      }
      elseif ($scr_par['current'] <= $aver_view_numb) // 1...n > или 1...n
      {
         if (($amount_page - $scr_par['current']) < $aver_view_numb) // 1...n
         {
            $scr_par['first'] = 1;
            $scr_par['last']  = $amount_page;
         }
         else // 1...n >
         {
            $scr_par['first'] = 1;
            $scr_par['last']  = $max_item_in_scroll;
         }
      }
      else // < n...m или < n...m >
      {
         if (($amount_page - $scr_par['current']) < $aver_view_numb) // < n...m
         {
            $scr_par['first'] = $amount_page - $max_item_in_scroll + 1;
            $scr_par['last']  = $amount_page;
         }
         else // < n...m >
         {
            if (($max_item_in_scroll % 2) == 0)
            {
               $scr_par['first'] = $scr_par['current'] - $aver_view_numb + 1;
               $scr_par['last']  = $scr_par['current'] + $aver_view_numb;
            }
            else
            {
               $scr_par['first'] = $scr_par['current'] - $aver_view_numb + 1;
               $scr_par['last']  = $scr_par['current'] + $aver_view_numb - 1;
            }
         }
      }

      // Flag of adding scrolling shooter
      if ($scr_par['current'] == 1) // End
      {
         $scr_par['shooter'] = 2;
      }
      elseif ($scr_par['current'] == $amount_page) // Begin
      {
         $scr_par['shooter'] = 1;
      }
      else $scr_par['shooter'] = 0; // Both

      return $scr_par;
   }

    
   /**
    * Generate scrolling line
    *
    * @param $beg
    * @param $end
    * @param $shooter
    * @param $href
    * @param $cur_page
    * @param $el
    *
    * @return string
    */
   /*protected function generateScrolling($beg, $end, $shooter, $href, $cur_page, $el = '')
   {
      $scr_line = '';
      
      for ($i = $beg; $i < $cur_page; $i++)
      {
         $scr_line .= "<a href=\"{$href}&page={$i}{$el}\" class=\"pagination\">{$i}</a>&nbsp;";
      }
      
      $scr_line .= "<span class=\"pagination\">{$i}</span>&nbsp;";
      
      for ($j = $i + 1; $j <= $end; $j++)
      {
         $scr_line .= "<a href=\"{$href}&page={$j}{$el}\" class=\"pagination\">{$j}</a>&nbsp;";
      }

      $left_sh  = "<a href=\"{$href}&page=".($cur_page - 1).$el."\" class=\"pagination\"><</a>&nbsp;";
      $right_sh = "<a href=\"{$href}&page=".($cur_page + 1).$el."\" class=\"pagination\">></a>";

      if ($shooter === 0)
      {
         return $left_sh.$scr_line.$right_sh;
      }
      elseif ($shooter === 1)
      {
         return $left_sh.$scr_line;
      }
      elseif ($shooter === 2)
      {
         return $scr_line.$right_sh;
      }
   }*/
}
