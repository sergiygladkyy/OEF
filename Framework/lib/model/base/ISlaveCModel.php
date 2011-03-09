<?php

interface ISlaveCModel
{
   /**
    * Get list of slave objects by owner
    * 
    * @throws Exception (unknow owner type)
    * @param string $type   - owner type
    * @param int    $id     - owner id
    * @param array& $options
    * @return array or null
    */
   public function getByOwner($type, $id, array $options = array());
   
   /**
    * Mark for deletion by owner
    * 
    * @param string $type   - owner type
    * @param int    $id     - owner id
    * @param array& $options
    * @return array - errors
    */
   public function markForDeletionByOwner($type, $id, array $options = array());
   
   /**
    * Delete by owner
    * 
    * @param string $type   - owner type
    * @param int    $id     - owner id
    * @param array& $options
    * @return array - errors
    */
   public function deleteByOwner($type, $id, array $options = array());
}
