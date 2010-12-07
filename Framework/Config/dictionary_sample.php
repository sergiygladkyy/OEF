<?php

$_dictionary = array(
   'entities' => array(
      '<entity_name>' => array(
         /*
          * Описание полей сущности. Полями могут быть ссылки и атрибуты. В формах, по умолчанию,
          * поля отображаются в порядке следования в этом описании. Для того, чтобы изменить этот
          * порядок, нужно будет переопределить конфигурацию вида, либо template. 
          */
         'fields' => array(
            '<field_name>' => array(
               /*
                * Один из внутренних типов.
                */
               'type' => 'string',
               
               /*
                * Конфигурация для генерации SQL-запроса, добавляющего таблицу в БД
                */
               'sql' => array(
                  'type' => "varchar(255) NOT NULL default '<def_value>'",
                  /*
                   * Здесь могут быть добавлены любые параметры (опции), связанные с
                   * генерацией sql-запроса добавляющего таблицу
                   */
               ),
               
               /*
                * Конфигурация для работы со значением в PHP
                */
               'credentials' => array(     /* ограничения */
                  'min_length' => 5,
                  'max_length' => 255,
                  'require'    => true,                       // Обязательный (непустой) параметр [ true | false ] - форма
                                                              // редактирования не может передать пустую строку
                  'regexp'     => '/^[a-zA-Z0-9_\s]{5,255}$/' // Проверка на соответствие регулярному выражению
                //...........................................
               ),

             //.................................................................................
               /* Здесь могут быть добавлены любые параметры (опции), описывающие данный атрибут */
            ),
          //................................................................................................................
            'quantity' => array(
               'type' => 'int',
               'sql'  => array(
                  'type' => "int(11) NOT NULL default 0"
               ),
               'credentials' => array(
                  'min' => 5,                   // Не меньше
                  'max' => 255,                 // Не больше
                  'in'  => array(1, 7, 15, 77), // Может принимать только указанные значения
                  'require' => true             // Форма редактирования не может передать пустую строку
                //..............................
               ),
            ),
            
            /*
             * Ссылка на другую сущность. Определение атрибута отличается тем, что в нем не может
             * быть указан параметр 'reference'. В его описании (атрибута) другая структура вложеных
             * секций. Ссылка определяет связь 1:М (много сущностей описываемого типа, могут быть связаны
             * с одной сущность, на которую указывает 'reference'). В SQL, к таблице добавляется внешний
             * ключ, который будет хранить id указываемой в 'reference' сущности.
             */
            'locations' => array(
               'reference' => '<entitiy_name>'
            )
         ),
         
         /*
          * Табличные части
          */
         'tabular_sections' => array(
            '<tabular_section_name>' => array(
               'fields' => array(
                  /*
                   * Как у entities. В формах, по умолчанию, поля отображаются в порядке следования в 
                   * этой секции отдельным блоком.
                   */
               )
            ), 
         ),
         
         /*
          * Модель
          */
         'model' => array(
            'modelclass'  => 'Entity',
            'cmodelclass' => 'Entities'
            // Если классов несколько - здесь добавить еще один параметр
          //......................... // Дополнительные параметры модели
         )
      ),
    //................................................................................................................
   ),

   'information_registry' => array(
      '<registry_name>' => array(
         'dimensions' => array(
            '<field_name>' => array(
               /*
                * Здесь описание поля как в entities->fields-><field_name>
                */
            ),
          //...................................
         ),
         /*
          * 'dimensions' - unique key. 'period' включается в этот unique key.
          *  Этот параметр опционален (может не указываться).
          */
         'periodical' => '<period_length>', // [ second | day | month | quarter | year ]
         'fields' => array(
            /*
             * Как у entities. В формах, по умолчанию, поля отображаются в порядке следования в 
             * этой секции. Поля dimensions отображаются вначале, в том порядке, в котором они
             * определены в секции 'dimensions'. Если указан 'period', то в формах, он отображается
             * после dimensions, перед fields.
             */
         )
      ),
    //................................................................................................................ 
   ),
   
   ///////////////////
   // Reports Section
   ///////////////////
   'reports' => array(
      'ProjectManHours' => array(
         'fields' => array(
            'Date' => array(
               'type' => 'date',
               'sql'  => array(
                  'type' => "DATE NOT NULL default '0000-00-00'"
               ),
               'precision' => array(
                  'required' => true
               )
            )
         )
      ),
      'ProjectResources' => array(
         'fields' => array(
            'Date' => array(
               'type' => 'date',
               'sql'  => array(
                  'type' => "DATE NOT NULL default '0000-00-00'"
               ),
               'precision' => array(
                  'required' => true
               )
            ),
            'Project' => array(
               'reference' => 'catalogs.Projects',
               'precision' => array(
                  'required' => true
               )
            )
         )
      )
   ),
   
   ///////////////////////////
   // Data processors Section
   ///////////////////////////
   'data_processors' => array(
      'NavCsvImport' => array(
         'fields' => array(
            'Server' => array(
               'type' => 'string',
               'precision' => array(
                  'required' => true
               )
            ),
            'FileName' => array(
               'type' => 'string',
               'precision' => array(
                  'required' => true
               )
            ),
            'UserName' => array(
               'type' => 'string',
               'precision' => array(
                  'required' => true
               )
            ),
            'Password' => array(
               'type' => 'password',
               'precision' => array(
                  'required' => true
               )
            )
         )
      )
   ),
   
   ////////////////
   // Web services
   ////////////////
   'web_services' => array(
      '<web_service_name>' => array(
         'actions' => array(
            '<method_name>' => array(
               'fields' => array()
            ),
            'getProjectMembers' => array(
               'fields' => array(
                  'Project' => array(
                     'reference' => 'catalogs.Projects',
                     'precision' => array(
                        'required' => true
                     )
                  ),
                  'Date' => array(
                     'type' => 'date'
                  )
               )
            )
         )
      )
   ),
   
   ////////////
   // Security
   ////////////
   'security' => array(
      '<Role>' => array(
         'entities' => array(
            '<entity_kind>' => array(
               '<entity_type>' => array(
                  '<permissions>' => array(
                     'permission_1' => true,
                     'permission_2' => false
                  )               
               )
            )
         ),
         'global' =>array(
            'UseRemoteCalls' => true
         )
      )
   )
);


