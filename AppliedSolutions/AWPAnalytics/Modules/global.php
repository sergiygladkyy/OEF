<?php

class MGlobal
{
   public static function dateToTimeStamp($date, $day = 0)
   {
      $dt = explode(' ', $date);
      $vals = explode('-', $dt[0]);
      $vals[0] = (int) $vals[0];
      $vals[1] = isset($vals[1]) ? (int) $vals[1] : 1;
      $vals[2] = isset($vals[2]) ? $vals[2] + $day : 1;
       
      if (empty($vals[0])) return null;
       
      if (!empty($dt[1])) $time = explode(':', $dt[1]);

      $vals[3] = /*isset($time[0]) ? (int) $time[0] : */0;
      $vals[4] = /*isset($time[1]) ? (int) $time[1] : */0;
      $vals[5] = /*isset($time[2]) ? (int) $time[2] : */0;
       
      $mt = mktime($vals[3], $vals[4], $vals[5], $vals[1], $vals[2], $vals[0]);
       
      return $mt;
   }
   
   /**
    * Generates date string for display in templates
    *
    * @param string $date - "Y-m-d H:i:s"
    * @return string or null
    */
   public static function getFormattedDate($date, $format = null)
   {
      $dt = explode(' ', $date);
      $vals = explode('-', $dt[0]);
      $vals[0] = (int) $vals[0];
      $vals[1] = (int) $vals[1];
      $vals[2] = (int) $vals[2];
       
      if (empty($vals[0])) return null;
       
      if (!empty($dt[1])) $time = explode(':', $dt[1]);

      $vals[3] = isset($time[0]) ? (int) $time[0] : 0;
      $vals[4] = isset($time[1]) ? (int) $time[1] : 0;
      $vals[5] = isset($time[2]) ? (int) $time[2] : 0;
       
      $mt = mktime($vals[3], $vals[4], $vals[5], $vals[1] ? $vals[1] : 1, $vals[2] ? $vals[2] : 1, $vals[0]);

      if (empty($format))
      {
         if (empty($vals[1]))     $format = '%Y';
         elseif (empty($vals[2])) $format = '%b %Y';
         elseif (empty($dt[1]))   $format = '%d.%m.%y';
         else                     $format = '%d.%m.%y %H:%M:%S';
      }

      return strftime($format, $mt);
   }
}