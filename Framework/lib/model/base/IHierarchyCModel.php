<?php

interface IHierarchyCModel
{
   /**
    * Get Hierarchically
    * 
    * @param int $nodeId - parent id
    * @param array $options
    * @return array or null
    */
   public function getHierarchically($nodeId = null, array $options = array());
   
   /**
    * Get children nodes
    * 
    * @param int $nodeId - parent id
    * @param array $options
    * @return array or null
    */
   public function getChildren($nodeId, array $options = array());
   
   /**
    * Get parent node
    * 
    * @param int $nodeId - parent id
    * @param array $options
    * @return array or null
    */
   public function getParent($nodeId, array $options = array());
   
   /**
    * Get parents
    * 
    * @param int $nodeId - parent id
    * @param array $options
    * @return array or null
    */
   public function getParents($nodeId, array $options = array());
   
   /**
    * Get siblings
    * 
    * @param int $nodeId - parent id
    * @param array $options
    * @return array or null
    */
   public function getSiblings($nodeId, array $options = array());
}
