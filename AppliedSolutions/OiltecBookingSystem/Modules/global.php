<?php

/**
 * Learners functions
 *
 */
class Learners
{
   /**
    * Return list of Learners organizations for select box
    *
    * @return array
    */
   static public function getOrganizationsForSelect()
   {
      $odb   = Container::getInstance()->getODBManager();
      $query = "SELECT `_id`, `Description`, `_deleted` FROM catalogs.Counteragents WHERE `Type` = 1 ORDER BY `Description` ASC";
       
      if (null === ($res = $odb->executeQuery($query)))
      {
         throw new Exception('Database error');
      }
       
      $list = array();
       
      while ($row = $odb->fetchRow($res))
      {
         $list[] = array('value' => $row[0], 'text' => $row[1], 'deleted' => $row[2]);
      }
       
      return $list;
   }
   
   /**
    * Return false if learners organization with specified id is not exists
    * 
    * @param int $id
    * @return bool
    */
   static public function hasOrganization($id)
   {
      $odb   = Container::getInstance()->getODBManager();
      $query = "SELECT COUNT(*) AS `cnt` FROM catalogs.Counteragents WHERE `Type` = 1 AND `_id` = ".$id;
       
      if (null === ($row = $odb->loadAssoc($query)))
      {
         throw new Exception('Database error');
      }
       
      return $row['cnt'] > 0;
   }
}



/**
 * Instructors functions
 *
 */
class Instructors
{
   /**
    * Return list of Instructors organizations for select box
    *
    * @return array
    */
   static public function getOrganizationsForSelect()
   {
      $odb   = Container::getInstance()->getODBManager();
      $query = "SELECT `_id`, `Description`, `_deleted` FROM catalogs.Counteragents WHERE `Type` = 2 ORDER BY `Description` ASC";
       
      if (null === ($res = $odb->executeQuery($query)))
      {
         throw new Exception('Database error');
      }
       
      $list = array();
       
      while ($row = $odb->fetchRow($res))
      {
         $list[] = array('value' => $row[0], 'text' => $row[1], 'deleted' => $row[2]);
      }
       
      return $list;
   }
   
   /**
    * Return false if learners organization with specified id is not exists
    * 
    * @param int $id
    * @return bool
    */
   static public function hasOrganization($id)
   {
      $odb   = Container::getInstance()->getODBManager();
      $query = "SELECT COUNT(*) AS `cnt` FROM catalogs.Counteragents WHERE `Type` = 2 AND `_id` = ".$id;
       
      if (null === ($row = $odb->loadAssoc($query)))
      {
         throw new Exception('Database error');
      }
       
      return $row['cnt'] > 0;
   }
}



/**
 * Application form functions
 *
 */
class ApplicationForm
{
   /**
    * Return true if ApplicationForm document contains Course with specified params
    * 
    * @param int $appForm      - ApplicationForm id
    * @param int $course       - Course id
    * @param int $courseNumber - Course number
    * @return bool
    */
   static public function hasCourse($appForm, $course, $courseNumber = 0)
   {
      $cmodel = Container::getInstance()->getCModel('documents.ApplicationForm.tabulars', 'Courses');
      
      $criterion = "WHERE `Owner` = ".$appForm." AND `Course` = ".$course." AND `CourseNumber` = ".$courseNumber;
   
      if (null === ($courses = $cmodel->getEntities(null, array('criterion' => $criterion))) || isset($result['errors']))
      {
         throw new Exception('Database error');
      }
      
      return !empty($courses);
   }
}



/**
 * PO functions
 *
 */
class PO
{
   /**
    * Return true if PO document contains Course with specified params
    * 
    * @param int $PO           - PO id
    * @param int $course       - Course id
    * @param int $courseNumber - Course number
    * @return bool
    */
   static public function hasCourse($PO, $course, $courseNumber = 0)
   {
      $cmodel = Container::getInstance()->getCModel('documents.PO.tabulars', 'Orders');
      
      $criterion = "WHERE `Owner` = ".$PO." AND `Course` = ".$course." AND `CourseNumber` = ".$courseNumber;
   
      if (null === ($courses = $cmodel->getEntities(null, array('criterion' => $criterion))) || isset($result['errors']))
      {
         throw new Exception('Database error');
      }
      
      return !empty($courses);
   }
}
