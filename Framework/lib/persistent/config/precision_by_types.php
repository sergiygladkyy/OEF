<?php
/* types -  'bool', 'int', 'float', 'string', 'text', 'file', 'date', 'datetime',
 *          'time', 'timestamp', 'year', 'enum', 'password'
 *          
 *          + reference
 */
$_precision_by_types = array(
   'required' => array(
      'allowed' => 'all',
      'type'    => 'bool' 
   ),
   'in'  => array(
      'allowed' => 'all',
      'type'    => 'array' 
   ),
   'min' => array(
      'allowed' => array('int', 'float'),
      'type'    => 'numeric' 
   ),
   'max' => array(
      'allowed' => array('int', 'float'),
      'type'    => 'numeric' 
   ),
   'min_length' => array(
      'allowed' => array('int', 'float', 'string', 'text', 'password'),
      'type'    => 'numeric'
   ),
   'max_length' => array(
      'allowed' => array('int', 'float', 'string', 'text', 'password'),
      'type'    => 'numeric'
   ),
   'regexp' => array(
      'allowed' => array('string', 'text', 'password'),
      'type'    => 'string'
   ),
   'dynamic_update' => array(
      'allowed' => array('reference'),
      'type'    => 'bool'
   )
);