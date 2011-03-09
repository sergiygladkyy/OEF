<?php

$_default_models = array(
   'catalogs' => array(
      'base' => array(
         'modelclass'  => 'CatalogModel',
         'cmodelclass' => 'CatalogsModel'
      ),
      'slave' => array(
         'modelclass'  => 'CatalogModel',
         'cmodelclass' => 'CatalogsSlaveModel'
      ),
      'hierarchy' => array(
         'modelclass'  => 'CatalogModel',
         'cmodelclass' => 'CatalogsHierarchyModel'
      ),
      'slave_and_hierarchy' => array(
         'modelclass'  => 'CatalogModel',
         'cmodelclass' => 'CatalogsSlaveAndHierarchyModel'
      )
   ),
   
   'information_registry' => array(
      'base' => array(
         'modelclass'  => 'InfRegistryModel',
         'cmodelclass' => 'InfRegistriesModel'
      )
   ),
   
   'AccumulationRegisters' => array(
      'base' => array(
         'modelclass'  => 'AccumulationRegisterModel',
         'cmodelclass' => 'AccumulationRegistersModel'
      )
   ),
   
   'tabular_sections' => array(
      'base' => array(
         'modelclass'  => 'TabularModel',
         'cmodelclass' => 'TabularsModel'
      )
   ),
   
   'documents' => array(
      'base' => array(
         'modelclass'  => 'DocumentModel',
         'cmodelclass' => 'DocumentsModel'
      )
   ),
   
   'reports' => array(
      'base' => array(
         'modelclass' => 'ReportModel'
      )
   ),
   
   'data_processors' => array(
      'base' => array(
         'modelclass' => 'DataProcessorModel'
      )
   ),
   
   'web_services' => array(
      'base' => array(   
         'modelclass' => 'WebServiceModel'
      )
   )
);
