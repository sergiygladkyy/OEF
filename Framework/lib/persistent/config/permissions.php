<?php

$_permissions = array(
   'catalogs' => array(
      'Read'   => false,
      'Insert' => false,
      'Update' => false,
      'Delete' => false,
      'View'   => false,
      'Edit'   => false,
      'InteractiveInsert' => false,
      'InteractiveDelete' => false,
      'InteractiveMarkForDeletion'   => false,
      'InteractiveUnmarkForDeletion' => false,
      'InteractiveDeleteMarked'      => false
   ),
   'documents' => array(
      'Read'   => false,
      'Insert' => false,
      'Update' => false,
      'Delete' => false,
      'Posting'     => false,
      'UndoPosting' => false,
      'View' => false,
      'Edit' => false,
      'InteractiveInsert' => false,
      'InteractiveDelete' => false,
      'InteractiveMarkForDeletion'   => false,
      'InteractiveUnmarkForDeletion' => false,
      'InteractiveDeleteMarked'      => false,
      'InteractivePosting'        => false,
      'InteractiveUndoPosting'    => false,
      'InteractiveChangeOfPosted' => false // very rare right, allowing to change the document without unposting
   ),
   'information_registry' => array(
      'Read'   => false,
      'Update' => false,
      'View' => false,
      'Edit' => false
   ),
   'reports' => array(
      'Use'  => false, // User can invoke the report for execution
      'View' => false // User can view the report's presense
   ),
   'data_processors' => array(
      'Use'  => false,
      'View' => false
   ),
   'web_services' => array(
   ),
   'global' => array(
      'UseRemoteCalls' => false
   )
);
