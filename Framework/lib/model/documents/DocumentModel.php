<?php

require_once('lib/model/base/BaseObjectModel.php');

class DocumentModel extends BaseObjectModel
{
   const kind = 'documents';
   
   public function __construct($type, array& $options = array())
   {
      parent::__construct(self::kind, $type, $options);
   }
   
   /**
    * Post document
    * 
    * @param array& $options
    * @return array - errors
    */
   public function post(array& $options = array())
   {
      if ($errors = $this->changePost(true, $options))
      {
         $this->unpost($options);
      }
      
      return $errors;
   }
   
   /**
    * Unpost document
    * 
    * @param array& $options
    * @return array - errors
    */
   public function unpost(array& $options = array())
   {
      return $this->changePost(false, $options);
   }
   
   /**
    * Check posted flag
    * 
    * @return boolean (true - if document posted)
    */
   public function isPosted()
   {
      if ($this->isNew) return false;
      
      return !empty($this->attributes[$this->conf['db_map']['post']]);
   }
   
   /**
    * Change post state
    * 
    * @param bool $post - post flag
    * @param array& $options
    * @return array - errors
    */
   protected function changePost($post, array& $options = array())
   {
      if ($this->isNew) return array('You must save the document');
      
      if ($this->isPosted() && $post) return array('Document already posted');
      
      $event = $this->container->getEvent($this, $this->kind.'.'.$this->type.($post ? '.onPost' : '.onUnpost'));
      $event->setReturnValue(true);
      try
      {
         $this->container->getEventDispatcher()->notify($event);
      }
      catch(Exception $e)
      {
         return array($e->getMessage());
      }
      
      if (!$event->getReturnValue()) return array('Document not '.($post ? 'posted' : 'unposted').'. Module error');
      
      $db    =  $this->container->getDBManager($options);
      $dbmap =& $this->conf['db_map'];
      $query =  "UPDATE `".$dbmap['table']."` SET `".$dbmap['post']."` = ".($post ? 1 : 0)." WHERE `".$dbmap['pkey']."`=".$this->id;
       
      if (!$db->executeQuery($query))
      {
         return array($db->getError());
      }
      
      return array();
   }
   
   /**
    * (non-PHPdoc)
    * @see lib/model/base/BaseEntityModel#save($options)
    */
   public function save(array& $options = array())
   {
      if ($this->isPosted()) return array('This document is posted. You must clear posting before saving.');
      
      return parent::save($options);
   }
}
