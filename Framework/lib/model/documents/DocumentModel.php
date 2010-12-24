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
      // Check permissions
      if (defined('IS_SECURE') && !$this->container->getUser()->hasPermission($this->kind.'.'.$this->type.'.Posting'))
      {
         return array('Access denied');
      }
      
      // Execute method
      if ($errors = $this->changePost(true, $options))
      {
         $this->changePost(false, $options);
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
      // Check permissions
      if (defined('IS_SECURE') && !$this->container->getUser()->hasPermission($this->kind.'.'.$this->type.'.UndoPosting'))
      {
         return array('Access denied');
      }
      
      // Execute method
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
      
      $event = $this->container->getEvent($this, $this->kind.'.'.$this->type.'.model'.($post ? '.onPost' : '.onUnpost'));
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
      // Check permissions
      if (defined('IS_SECURE'))
      { 
         if ($this->isNew)
         {
            $access = $this->container->getUser()->hasPermission($this->kind.'.'.$this->type.'.Insert');
         }
         else
         {
            $access = $this->container->getUser()->hasPermission($this->kind.'.'.$this->type.'.Update');
         }
         
         if (!$access)
         {
            return array('Access denied');
         }
      }
      
      // Execute method
      if ($this->isPosted()) return array('This document is posted. You must clear posting before saving.');
      
      return parent::save($options);
   }
   

   
   
   /************************** For control access rights **************************************/
   
   
   
   /**
    * Mark for deletion
    * (non-PHPdoc)
    * @see BaseObjectModel#delete($options)
    */
   public function delete(array& $options = array())
   {
      // Check permissions
      // None
      
      // Execute method
      return parent::delete($options);
   }
   
   /**
    * (non-PHPdoc)
    * @see BaseEntityModel#load($id, $options)
    */
   public function load($id, array& $options = array())
   {
      // Check permissions
      if (defined('IS_SECURE') && !$this->container->getUser()->hasPermission($this->kind.'.'.$this->type.'.Read'))
      {
         return false;
      }
      
      // Execute method
      return parent::load($id, $options);
   }
   
   /**
    * (non-PHPdoc)
    * @see BaseEntityModel#toArray($options)
    */
   public function toArray(array $options = array())
   {
      // Check permissions
      if (defined('IS_SECURE') && !$this->isNew && !$this->container->getUser()->hasPermission($this->kind.'.'.$this->type.'.Read'))
      {
         return array();
      }
      
      // Execute method
      return parent::toArray($options);
   }
}
