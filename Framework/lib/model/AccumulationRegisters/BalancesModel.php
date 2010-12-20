<?php 

class BalancesModel
{
   protected $conf = null;
   
   public function __construct(array $configuration, array $options = array())
   {
      $this->conf = $configuration;
   }
   
   public function getTotals($from = null, $to = null)
   {
   }
   
   public function countTotals($from = null)
   {
   }
}
