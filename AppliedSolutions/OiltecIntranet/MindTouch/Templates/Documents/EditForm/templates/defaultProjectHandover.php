{{
   var uid    = args[0];
   var puid   = args[1];
   var data   = args[2];
   var root   = args[3] ?? 'Template:Entities';
   var prefix = args[4] ?? 'default';
   var fields = {};
   var field_type = {};
   var field_prec = {};
   var required   = [];
   var dynamic    = {};
   var references = [];
   var layout     = [];
   var kind     = '';
   var type     = puid.type;
   var item     = data.item is map ? data.item : {};
   var select   = data.select;
   var tabulars = data.tabulars;
   
   var _fields  = [];
   
   var tmpList = string.Split(uid,'.');
   var header = string.Remove(string.ToUpperFirst(tmpList[0]),string.Length(tmpList[0])-1,1)..' '..tmpList[1];
}}
<eval:if test="puid is nil">
  <ul class="ae_errors">
    <li class="ae_error">Unknow entity</li>
  </ul>
</eval:if>
<eval:else>
  <h3>{{header;}}</h3>
  {{
      if (#puid.main_kind != 0) {
         let kind = puid.main_kind..'.'..puid.main_type..'.'..puid.kind;
      }
      else {
         let kind = puid.kind;
      }
      var enctype = '';
      var name_prefix = 'aeform['..kind..']['..type..']';
      let field_type = entities.getInternalConfiguration(kind..'.field_type', type);
      let field_prec = entities.getInternalConfiguration(kind..'.field_prec', type);
      let fields     = entities.getInternalConfiguration(kind..'.fields', type);
      let required   = entities.getInternalConfiguration(kind..'.required', type);
      let dynamic    = entities.getInternalConfiguration(kind..'.dynamic', type);
      let references = entities.getInternalConfiguration(kind..'.references', type);
      let layout     = entities.getInternalConfiguration(kind..'.layout', type);
      
      var tab_s    = entities.getInternalConfiguration(kind..'.'..type..'.tabulars.tabulars');
      if (item._id > 0) {
         var header = 'Edit ';
         var hidden = '&lt;input type="hidden" name="'..name_prefix..'[attributes][_id]" value="'..item._id..'" /&gt;';
      }
      else {
         var header = 'New ';
         var hidden = '';
      }
      
      if (#map.select(field_type, "$.value=='file'") > 0)
      {
         let enctype = 'multipart/form-data';
      }
      
      var class  = string.replace(kind, '.', '_')..'_'..type;
      var js_uid = class;
  }}
  
<style type="text/css">
  #documents_ProjectHandover_item input[type=text] {
     width: 200px;
  }
  #documents_ProjectHandover_item select {
     width: 206px;
  }
  #documents_ProjectHandover_item .ae_tabular_section table{
     width: 701px !important;
  }
  .oe_td_bool {
     text-align: center;
  }
  .oe_td_comment input[type=text] {
     width: 98% !important;
  }
  .oe_even {
     background-color: #EFEFEF;
  }
  .hover:hover {
     background-color: #EEFFC9;
  }
  .normal {
     font-variant: normal !important;
     font-weight: normal !important;
  }
  .no_border {
     border-left: 1px solid white !important;
     border-right: 1px solid white !important;
  }
  .documents_ProjectHandover_tabulars_Misc tr,
  .documents_ProjectHandover_tabulars_Conditions tr {
     border-left: 1px solid #AAAAAA;
  }
  .documents_ProjectHandover_tabulars_Misc_Issue_header,
  .documents_ProjectHandover_tabulars_Conditions_Description_header {
     width: 240px;
  }
  
  #AcceptCondition .desc {
     padding: 4px 0 0 17px !important;
     margin: 0;
     font-size: 9px;
     line-height: 12px;
  }
  #AcceptCondition span {
     color: #222222;
  }
  #AcceptCondition input[type=radio] {
     padding: 5px;
     vertical-align: text-bottom;
  }
</style>

  <div class="{{ class..'_message systemmsg' }}" style="display: none;">
    <div class="inner">
      <ul class="flashMsg">
        <li>&nbsp;</li>
      </ul>
    </div>
  </div>
  <div class="{{ class..'_actions ae_editform_actions' }}" style="{{ item._id &gt; 0 ? 'display: block;' : 'display: none;' }}">
    &nbsp;
  </div>
  <form method="post" action="#" class="ae_object_edit_form" id="{{ class..'_item' }}" enctype="{{ enctype }}">
    {{ web.html(hidden) }}
    <table>
    <tbody>
      <tr id="{{ class..'_post_flag' }}" style="{{ item._id &gt; 0 ? '' : 'display: none;' }}">
        <td class="{{ class..'_value ae_editform_field_value' }}" colspan="2" style="padding-top: 17px; padding-bottom: 15px;">
          <div class="{{ item._post &gt; 0 ? 'ae_field_posted' : 'ae_field_not_posted' }}">
            <span class="ae_field_posted_text" style="{{ item._post &gt; 0 ? 'display: block;' : 'display: none;' }}">This document is posted.</span>
            <span class="ae_field_not_posted_text" style="{{ item._post &gt; 0 ? 'display: none;' : 'display: block;' }}">This document is not posted.</span>
          </div>
        </td>
      </tr>
      <tr>
        <td colspan="2">
          <table>
          <thead>
            <tr>
              <th colspan="2">Project information</th>
            </tr>
          </thead>
          <tbody>
    {{
      let _fields = [
         'Code',
         'Date',
         'SalesManager',
         'ProjectManager',
         'TenderResonsible',
         'MainProject',
         'ProjectCode',
         'ProjectName',
         'Contract',
         'Customer',
         'CustomerMainContact',
         'SelligPrice',
         'MaterialsCost',
         'TotalIndirectLaborCost',
         'NumberOfHours',
         'GrossMargin',
         'AddedValuePerHour',
         'EstimatedStartDate',
         'EstimatedEndDate'
      ];
      
      var cnt = 0;
    }}
    <eval:foreach var="field" in="_fields">
      <tr>
        <td class="{{ class..'_name ae_editform_field_name'..(cnt%2 &gt; 0 ? ' oe_even' : '') }}">{{ string.ToUpperFirst(field); }}:</td>
        <td class="{{ class..'_value ae_editform_field_value'..(cnt%2 &gt; 0 ? ' oe_even' : '') }}">
          <ul class="{{ class..'_'..field..'_errors ae_editform_field_errors' }}" style="display: none;"><li>&nbsp;</li></ul>
          <pre class="script">
            var name   = name_prefix..'[attributes]['..field..']';
            var params = {
               select:    select[field],
               required:  list.contains(required, field),
               dynamic:   list.contains(dynamic, field),
               precision: field_prec[field]
            };
            
            if (references[field]) {
              let params ..= {reference: references[field]};
            }
            
            var template   = root..'/EditFormFields';
            var content    = wiki.template(template, [field_type[field], name, item[field], params, type, template, prefix]);
      
            if (string.contains(content, 'href="'..template..'"')) {
              let content = 'Template not found';
            }
          
            content;
          </pre>
        </td>
      </tr>
      {{ let cnt = cnt + 1 }}
    </eval:foreach>
    
          </tbody>
          </table>
        </td>
      </tr>
      <tr>
        <td colspan="2" style="padding-top: 23px;">
          <table style="border-top: 0 none; border-left: 0 none;">
          <thead>
            <tr>
              <th colspan="3" style="border-right: 0 none;">1. REVIEW OF CONTRACT</th>
            </tr>
            <tr style="border-left: 1px solid #AAAAAA;">
              <th style="width: 41%; font-weight: normal;">Responsible: Sales/Technical</th>
              <th style="width: 44px; font-weight: normal;">Yes/No</th>
              <th style="font-weight: normal;">Comments</th>
            </tr>
          </thead>
          <tbody>
    {{
      var field   = '';
      let _fields = [
         {
            fname:   'Do we have a contract or purchase order from customer?',
            field:   'HaveContract',
            comment: 'HaveContractComment',
            style:   'width: 99%; height: 35px;'
         },
         {
            fname:   'Is report format and documentation feedback agreed upon by Customer?',
            field:   'ReportFormatAgreed',
            comment: 'ReportFormatAgreedComment',
            style:   'width: 99%; height: 35px;'
         },
         {
            fname:   'Is payment schedule, guarantees and insurance clarified?',
            field:   'PaymentScheduleGuaranteesInsurance',
            comment: 'PaymentScheduleGuaranteesInsuranceComment',
            style:   'width: 99%; height: 35px;'
         },
         {
            fname:   'Does the contract have a penalty paragraph?',
            field:   'HavePenalty',
            comment: 'HavePenaltyComment',
            style:   'width: 99%; height: 35px;'
         },
         {
            fname:   'Is the delivery conditions clarified?',
            field:   'DeliveryConditions',
            comment: 'DeliveryConditionsComment',
            style:   'width: 99%; height: 35px;'
         },
         {
            fname:   'Is the demand for documentation clarified?',
            field:   'DemandForDocumentation',
            comment: 'DemandForDocumentationComment',
            style:   'width: 99%; height: 35px;'
         },
         {
            fname:   'Is there a total budget?',
            field:   'IsTotalBudget',
            comment: 'IsTotalBudgetComment',
            style:   'width: 99%; height: 35px;'
         },
         {
            fname:   'Is price strategy used?',
            field:   'PriceStrategyUsed',
            comment: 'PriceStrategyUsedComment',
            style:   'width: 99%; height: 35px;'
         }
      ];
    }}    
    <eval:foreach var="_conf" in="_fields">
      <tr style="border-left: 1px solid #AAAAAA;" class="hover">
        <td class="{{ class..'_name ae_editform_field_name' }}">{{ _conf.fname }}</td>
        {{ let field = _conf.field }}
        <td class="{{ class..'_value ae_editform_field_value oe_td_bool' }}">
          <ul class="{{ class..'_'..field..'_errors ae_editform_field_errors' }}" style="display: none;"><li>&nbsp;</li></ul>
          <pre class="script">
            var name   = name_prefix..'[attributes]['..field..']';
            var params = {
               select:    select[field],
               required:  list.contains(required, field),
               dynamic:   list.contains(dynamic, field),
               precision: field_prec[field]
            };
            
            if (references[field]) {
              let params ..= {reference: references[field]};
            }
            
            var template   = root..'/EditFormFields';
            var content    = wiki.template(template, [field_type[field], name, item[field], params, type, template, prefix]);
      
            if (string.contains(content, 'href="'..template..'"')) {
              let content = 'Template not found';
            }
          
            content;
          </pre>
        </td>
        {{ let field = _conf.comment }}
        <td class="{{ class..'_value ae_editform_field_value oe_td_comment' }}">
          <ul class="{{ class..'_'..field..'_errors ae_editform_field_errors' }}" style="display: none;"><li>&nbsp;</li></ul>
          <pre class="script">
            var name   = name_prefix..'[attributes]['..field..']';
            var params = {
               select:    select[field],
               required:  list.contains(required, field),
               dynamic:   list.contains(dynamic, field),
               precision: field_prec[field],
               attrs:     {style: _conf.style ?? ''},
               options:   {text: true}
            };
            
            if (references[field]) {
              let params ..= {reference: references[field]};
            }
            
            var template   = root..'/EditFormFields';
            var content    = wiki.template(template, [field_type[field], name, item[field], params, type, template, prefix]);
      
            if (string.contains(content, 'href="'..template..'"')) {
              let content = 'Template not found';
            }
          
            content;
          </pre>
        </td>
      </tr>
    </eval:foreach>
    
          </tbody>
          </table>
        </td>
      </tr>
      <tr>
        <td colspan="2" style="padding-top: 23px;">
          <table style="border-top: 0 none; border-left: 0 none;">
          <thead>
            <tr>
              <th colspan="3" style="border-right: 0 none;">2. OFFER/QUOTE REVIEW</th>
            </tr>
            <tr style="border-left: 1px solid #AAAAAA;">
              <th style="width: 41%; font-weight: normal;">Responsible: Sales/Technical</th>
              <th style="width: 44px; font-weight: normal;">Yes/No</th>
              <th style="font-weight: normal;">Comments</th>
            </tr>
          </thead>
          <tbody>
    {{
      var field   = '';
      let _fields = [
         {
            fname:   'Have all necessary descriptions, drawings, specifications etc. been received from the customer?',
            field:   'HaveAllDesc',
            comment: 'HaveAllDescComment',
            style:   'width: 99%; height: 35px;'
         },
         {
            fname:   'Is anything missing from Oiltec Solutions? (necessary resources, etc.)',
            field:   'AnythingMissing',
            comment: 'AnythingMissingComment',
            style:   'width: 99%; height: 35px;'
         },
         {
            fname:   'Is there a hardware delivery in the project?',
            field:   'HardwareDelivery',
            comment: 'HardwareDeliveryComment',
            style:   'width: 99%; height: 35px;'
         }
      ];
    }}    
    <eval:foreach var="_conf" in="_fields">
      <tr style="border-left: 1px solid #AAAAAA;" class="hover">
        <td class="{{ class..'_name ae_editform_field_name' }}">{{ _conf.fname }}</td>
        {{ let field = _conf.field }}
        <td class="{{ class..'_value ae_editform_field_value oe_td_bool' }}">
          <ul class="{{ class..'_'..field..'_errors ae_editform_field_errors' }}" style="display: none;"><li>&nbsp;</li></ul>
          <pre class="script">
            var name   = name_prefix..'[attributes]['..field..']';
            var params = {
               select:    select[field],
               required:  list.contains(required, field),
               dynamic:   list.contains(dynamic, field),
               precision: field_prec[field]
            };
            
            if (references[field]) {
              let params ..= {reference: references[field]};
            }
            
            var template   = root..'/EditFormFields';
            var content    = wiki.template(template, [field_type[field], name, item[field], params, type, template, prefix]);
      
            if (string.contains(content, 'href="'..template..'"')) {
              let content = 'Template not found';
            }
          
            content;
          </pre>
        </td>
        {{ let field = _conf.comment }}
        <td class="{{ class..'_value ae_editform_field_value oe_td_comment' }}">
          <ul class="{{ class..'_'..field..'_errors ae_editform_field_errors' }}" style="display: none;"><li>&nbsp;</li></ul>
          <pre class="script">
            var name   = name_prefix..'[attributes]['..field..']';
            var params = {
               select:    select[field],
               required:  list.contains(required, field),
               dynamic:   list.contains(dynamic, field),
               precision: field_prec[field],
               attrs:     {style: _conf.style ?? ''},
               options:   {text: true}
            };
            
            if (references[field]) {
              let params ..= {reference: references[field]};
            }
            
            var template   = root..'/EditFormFields';
            var content    = wiki.template(template, [field_type[field], name, item[field], params, type, template, prefix]);
      
            if (string.contains(content, 'href="'..template..'"')) {
              let content = 'Template not found';
            }
          
            content;
          </pre>
        </td>
      </tr>
    </eval:foreach>
    
          </tbody>
          </table>
        </td>
      </tr>
      <tr>
        <td colspan="2" style="padding-top: 23px;">
          <table style="border-top: 0 none; border-left: 0 none;">
          <thead>
            <tr>
              <th colspan="3" style="border-right: 0 none;">3. BID CLARIFICATIONS (BC)</th>
            </tr>
            <tr style="border-left: 1px solid #AAAAAA;">
              <th style="width: 41%; font-weight: normal;">Responsible: Sales/Technical</th>
              <th style="width: 44px; font-weight: normal;">Yes/No</th>
              <th style="font-weight: normal;">Comments</th>
            </tr>
          </thead>
          <tbody>
    {{
      var field   = '';
      let _fields = [
         {
            fname:   'Do we have the BC minutes of meeting?',
            field:   'HaveBCMinutesOfMeeting',
            comment: 'HaveBCMinutesOfMeetingComment',
            style:   'width: 99%; height: 35px;'
         },
         {
            fname:   'Is all BC correspondence archived?',
            field:   'AllBCCorrespondenceArhived',
            comment: 'AllBCCorrespondenceArhivedComment',
            style:   'width: 99%; height: 35px;'
         }
      ];
    }}    
    <eval:foreach var="_conf" in="_fields">
      <tr style="border-left: 1px solid #AAAAAA;" class="hover">
        <td class="{{ class..'_name ae_editform_field_name' }}">{{ _conf.fname }}</td>
        {{ let field = _conf.field }}
        <td class="{{ class..'_value ae_editform_field_value oe_td_bool' }}">
          <ul class="{{ class..'_'..field..'_errors ae_editform_field_errors' }}" style="display: none;"><li>&nbsp;</li></ul>
          <pre class="script">
            var name   = name_prefix..'[attributes]['..field..']';
            var params = {
               select:    select[field],
               required:  list.contains(required, field),
               dynamic:   list.contains(dynamic, field),
               precision: field_prec[field]
            };
            
            if (references[field]) {
              let params ..= {reference: references[field]};
            }
            
            var template   = root..'/EditFormFields';
            var content    = wiki.template(template, [field_type[field], name, item[field], params, type, template, prefix]);
      
            if (string.contains(content, 'href="'..template..'"')) {
              let content = 'Template not found';
            }
          
            content;
          </pre>
        </td>
        {{ let field = _conf.comment }}
        <td class="{{ class..'_value ae_editform_field_value oe_td_comment' }}">
          <ul class="{{ class..'_'..field..'_errors ae_editform_field_errors' }}" style="display: none;"><li>&nbsp;</li></ul>
          <pre class="script">
            var name   = name_prefix..'[attributes]['..field..']';
            var params = {
               select:    select[field],
               required:  list.contains(required, field),
               dynamic:   list.contains(dynamic, field),
               precision: field_prec[field],
               attrs:     {style: _conf.style ?? ''},
               options:   {text: true}
            };
            
            if (references[field]) {
              let params ..= {reference: references[field]};
            }
            
            var template   = root..'/EditFormFields';
            var content    = wiki.template(template, [field_type[field], name, item[field], params, type, template, prefix]);
      
            if (string.contains(content, 'href="'..template..'"')) {
              let content = 'Template not found';
            }
          
            content;
          </pre>
        </td>
      </tr>
    </eval:foreach>
    
          </tbody>
          </table>
        </td>
      </tr>
      <tr>
        <td colspan="2" style="padding-top: 23px;">
          <table style="border-top: 0 none; border-left: 0 none;">
          <thead>
            <tr>
              <th colspan="3" style="border-right: 0 none;">4. ECONOMY</th>
            </tr>
            <tr style="border-left: 1px solid #AAAAAA;">
              <th style="width: 41%; font-weight: normal;">Responsible: Sales</th>
              <th style="width: 44px; font-weight: normal;">Yes/No</th>
              <th style="font-weight: normal;">Comments</th>
            </tr>
          </thead>
          <tbody>
    {{
      var field   = '';
      let _fields = [
         {
            fname:   'Do the contract contain bank guarantees, advances or special insurances?',
            field:   'ContainBankGuarantees',
            comment: 'ContainBankGuaranteesComment',
            style:   'width: 99%; height: 35px;'
         },
         {
            fname:   'Is there a SACA sheet?',
            field:   'IsSACASheet',
            comment: 'IsSACASheetComment',
            style:   'width: 99%; height: 35px;'
         },
         {
            fname:   'What are the demands for reporting with regards to economy?',
            field:   'DemandsForReportingToEconomy',
            comment: 'DemandsForReportingToEconomyComment',
            style:   'width: 99%; height: 35px;'
         }
      ];
    }}    
    <eval:foreach var="_conf" in="_fields">
      <tr style="border-left: 1px solid #AAAAAA;" class="hover">
        <td class="{{ class..'_name ae_editform_field_name' }}">{{ _conf.fname }}</td>
        {{ let field = _conf.field }}
        <td class="{{ class..'_value ae_editform_field_value oe_td_bool' }}">
          <ul class="{{ class..'_'..field..'_errors ae_editform_field_errors' }}" style="display: none;"><li>&nbsp;</li></ul>
          <pre class="script">
            var name   = name_prefix..'[attributes]['..field..']';
            var params = {
               select:    select[field],
               required:  list.contains(required, field),
               dynamic:   list.contains(dynamic, field),
               precision: field_prec[field]
            };
            
            if (references[field]) {
              let params ..= {reference: references[field]};
            }
            
            var template   = root..'/EditFormFields';
            var content    = wiki.template(template, [field_type[field], name, item[field], params, type, template, prefix]);
      
            if (string.contains(content, 'href="'..template..'"')) {
              let content = 'Template not found';
            }
          
            content;
          </pre>
        </td>
        {{ let field = _conf.comment }}
        <td class="{{ class..'_value ae_editform_field_value oe_td_comment' }}">
          <ul class="{{ class..'_'..field..'_errors ae_editform_field_errors' }}" style="display: none;"><li>&nbsp;</li></ul>
          <pre class="script">
            var name   = name_prefix..'[attributes]['..field..']';
            var params = {
               select:    select[field],
               required:  list.contains(required, field),
               dynamic:   list.contains(dynamic, field),
               precision: field_prec[field],
               attrs:     {style: _conf.style ?? ''},
               options:   {text: true}
            };
            
            if (references[field]) {
              let params ..= {reference: references[field]};
            }
            
            var template   = root..'/EditFormFields';
            var content    = wiki.template(template, [field_type[field], name, item[field], params, type, template, prefix]);
      
            if (string.contains(content, 'href="'..template..'"')) {
              let content = 'Template not found';
            }
          
            content;
          </pre>
        </td>
      </tr>
    </eval:foreach>
    
          </tbody>
          </table>
        </td>
      </tr>
      <tr>
        <td colspan="2" style="padding-top: 23px;">
          <table style="border-top: 0 none; border-left: 0 none;">
          <thead>
            <tr>
              <th colspan="3" style="border-right: 0 none;">5. ORGANIZING</th>
            </tr>
            <tr style="border-left: 1px solid #AAAAAA;">
              <th style="width: 41%; font-weight: normal;">Responsible: Project manager</th>
              <th style="width: 44px; font-weight: normal;">Yes/No</th>
              <th style="font-weight: normal;">Comments</th>
            </tr>
          </thead>
          <tbody>
    {{
      var field   = '';
      let _fields = [
         {
            fname:   'Has the workforce been selected and approved?',
            field:   'HasWorkforce',
            comment: 'HasWorkforceComment',
            style:   'width: 99%; height: 35px;'
         },
         {
            fname:   'Will the project need new employments?',
            field:   'NeedNewEmployments',
            comment: 'NeedNewEmploymentsComment',
            style:   'width: 99%; height: 35px;'
         },
         {
            fname:   'Has resources in the manufacturing plant been allocated? (local or overseas)',
            field:   'HasResources',
            comment: 'HasResourcesComment',
            style:   'width: 99%; height: 35px;'
         }
      ];
    }}    
    <eval:foreach var="_conf" in="_fields">
      <tr style="border-left: 1px solid #AAAAAA;" class="hover">
        <td class="{{ class..'_name ae_editform_field_name' }}">{{ _conf.fname }}</td>
        {{ let field = _conf.field }}
        <td class="{{ class..'_value ae_editform_field_value oe_td_bool' }}">
          <ul class="{{ class..'_'..field..'_errors ae_editform_field_errors' }}" style="display: none;"><li>&nbsp;</li></ul>
          <pre class="script">
            var name   = name_prefix..'[attributes]['..field..']';
            var params = {
               select:    select[field],
               required:  list.contains(required, field),
               dynamic:   list.contains(dynamic, field),
               precision: field_prec[field]
            };
            
            if (references[field]) {
              let params ..= {reference: references[field]};
            }
            
            var template   = root..'/EditFormFields';
            var content    = wiki.template(template, [field_type[field], name, item[field], params, type, template, prefix]);
      
            if (string.contains(content, 'href="'..template..'"')) {
              let content = 'Template not found';
            }
          
            content;
          </pre>
        </td>
        {{ let field = _conf.comment }}
        <td class="{{ class..'_value ae_editform_field_value oe_td_comment' }}">
          <ul class="{{ class..'_'..field..'_errors ae_editform_field_errors' }}" style="display: none;"><li>&nbsp;</li></ul>
          <pre class="script">
            var name   = name_prefix..'[attributes]['..field..']';
            var params = {
               select:    select[field],
               required:  list.contains(required, field),
               dynamic:   list.contains(dynamic, field),
               precision: field_prec[field],
               attrs:     {style: _conf.style ?? ''},
               options:   {text: true}
            };
            
            if (references[field]) {
              let params ..= {reference: references[field]};
            }
            
            var template   = root..'/EditFormFields';
            var content    = wiki.template(template, [field_type[field], name, item[field], params, type, template, prefix]);
      
            if (string.contains(content, 'href="'..template..'"')) {
              let content = 'Template not found';
            }
          
            content;
          </pre>
        </td>
      </tr>
    </eval:foreach>
    
          </tbody>
          </table>
        </td>
      </tr>
      <tr>
        <td colspan="2" style="padding-top: 23px;">
          <table style="border-top: 0 none; border-left: 0 none;">
          <thead>
            <tr>
              <th colspan="3" style="border-right: 0 none;">7. TECHNICAL SOLUTION</th>
            </tr>
            <tr style="border-left: 1px solid #AAAAAA;">
              <th style="width: 41%; font-weight: normal;">Responsible: Project manager</th>
              <th style="width: 44px; font-weight: normal;">Yes/No</th>
              <th style="font-weight: normal;">Comments</th>
            </tr>
          </thead>
          <tbody>
    {{
      var field   = '';
      let _fields = [
         {
            fname:   'Has the techical solution been accepted by the Lead Engineer?',
            field:   'HasTechnicalSolution',
            comment: 'HasTechnicalSolutionComment',
            style:   'width: 99%; height: 35px;'
         },
         {
            fname:   'Is the project an internal development project?',
            field:   'IsInternalDevelopment',
            comment: 'IsInternalDevelopmentComment',
            style:   'width: 99%; height: 35px;'
         }
      ];
    }}    
    <eval:foreach var="_conf" in="_fields">
      <tr style="border-left: 1px solid #AAAAAA;" class="hover">
        <td class="{{ class..'_name ae_editform_field_name' }}">{{ _conf.fname }}</td>
        {{ let field = _conf.field }}
        <td class="{{ class..'_value ae_editform_field_value oe_td_bool' }}">
          <ul class="{{ class..'_'..field..'_errors ae_editform_field_errors' }}" style="display: none;"><li>&nbsp;</li></ul>
          <pre class="script">
            var name   = name_prefix..'[attributes]['..field..']';
            var params = {
               select:    select[field],
               required:  list.contains(required, field),
               dynamic:   list.contains(dynamic, field),
               precision: field_prec[field]
            };
            
            if (references[field]) {
              let params ..= {reference: references[field]};
            }
            
            var template   = root..'/EditFormFields';
            var content    = wiki.template(template, [field_type[field], name, item[field], params, type, template, prefix]);
      
            if (string.contains(content, 'href="'..template..'"')) {
              let content = 'Template not found';
            }
          
            content;
          </pre>
        </td>
        {{ let field = _conf.comment }}
        <td class="{{ class..'_value ae_editform_field_value oe_td_comment' }}">
          <ul class="{{ class..'_'..field..'_errors ae_editform_field_errors' }}" style="display: none;"><li>&nbsp;</li></ul>
          <pre class="script">
            var name   = name_prefix..'[attributes]['..field..']';
            var params = {
               select:    select[field],
               required:  list.contains(required, field),
               dynamic:   list.contains(dynamic, field),
               precision: field_prec[field],
               attrs:     {style: _conf.style ?? ''},
               options:   {text: true}
            };
            
            if (references[field]) {
              let params ..= {reference: references[field]};
            }
            
            var template   = root..'/EditFormFields';
            var content    = wiki.template(template, [field_type[field], name, item[field], params, type, template, prefix]);
      
            if (string.contains(content, 'href="'..template..'"')) {
              let content = 'Template not found';
            }
          
            content;
          </pre>
        </td>
      </tr>
    </eval:foreach>
    
          </tbody>
          </table>
        </td>
      </tr>
      <tr>
        <td colspan="2" style="padding-top: 23px;">
          <table style="border-top: 0 none; border-left: 0 none;">
          <thead>
            <tr>
              <th colspan="3" style="border-right: 0 none;">10. CRITICAL FACTORS</th>
            </tr>
            <tr style="border-left: 1px solid #AAAAAA;">
              <th style="width: 41%; font-weight: normal;">Responsible: Everybody</th>
              <th style="font-weight: normal;">Comments</th>
            </tr>
          </thead>
          <tbody>
    {{
      var field   = '';
      let _fields = [
         {
            field:   'CriticalFactors',
            comment: 'CriticalFactorsComment',
            style:   'width: 99%; height: 100px;'
         }
      ];
    }}    
    <eval:foreach var="_conf" in="_fields">
      <tr style="border-left: 1px solid #AAAAAA;" class="hover">
        {{ let field = _conf.field }}
        <td class="{{ class..'_value ae_editform_field_value oe_td_comment' }}">
          <ul class="{{ class..'_'..field..'_errors ae_editform_field_errors' }}" style="display: none;"><li>&nbsp;</li></ul>
          <pre class="script">
            var name   = name_prefix..'[attributes]['..field..']';
            var params = {
               select:    select[field],
               required:  list.contains(required, field),
               dynamic:   list.contains(dynamic, field),
               precision: field_prec[field],
               attrs:     {style: _conf.style ?? ''},
               options:   {text: true}
            };
            
            if (references[field]) {
              let params ..= {reference: references[field]};
            }
            
            var template   = root..'/EditFormFields';
            var content    = wiki.template(template, [field_type[field], name, item[field], params, type, template, prefix]);
      
            if (string.contains(content, 'href="'..template..'"')) {
              let content = 'Template not found';
            }
          
            content;
          </pre>
        </td>
        {{ let field = _conf.comment }}
        <td class="{{ class..'_value ae_editform_field_value oe_td_comment' }}">
          <ul class="{{ class..'_'..field..'_errors ae_editform_field_errors' }}" style="display: none;"><li>&nbsp;</li></ul>
          <pre class="script">
            var name   = name_prefix..'[attributes]['..field..']';
            var params = {
               select:    select[field],
               required:  list.contains(required, field),
               dynamic:   list.contains(dynamic, field),
               precision: field_prec[field],
               attrs:     {style: _conf.style ?? ''},
               options:   {text: true}
            };
            
            if (references[field]) {
              let params ..= {reference: references[field]};
            }
            
            var template   = root..'/EditFormFields';
            var content    = wiki.template(template, [field_type[field], name, item[field], params, type, template, prefix]);
      
            if (string.contains(content, 'href="'..template..'"')) {
              let content = 'Template not found';
            }
          
            content;
          </pre>
        </td>
      </tr>
    </eval:foreach>
    
          </tbody>
          </table>
        </td>
      </tr>
      <tr>
        <td colspan="2" style="padding-top: 23px;">
          <table style="border-top: 0 none; border-left: 0 none;">
          <thead>
            <tr>
              <th colspan="3" style="border-right: 0 none;">13. EXPERIENCE</th>
            </tr>
            <tr style="border-left: 1px solid #AAAAAA;">
              <th style="width: 41%; font-weight: normal;">Responsible: Project manager/Sales</th>
              <th style="width: 44px; font-weight: normal;">Yes/No</th>
              <th style="font-weight: normal;">Comments</th>
            </tr>
          </thead>
          <tbody>
    {{
      var field   = '';
      let _fields = [
         {
            fname:   'Is this a project with nearly replica-like conditions compared to previous projects of the same kind?',
            field:   'ConditionsComparedToPrevious',
            comment: 'ConditionsComparedToPreviousComment',
            style:   'width: 99%; height: 54px;'
         }
      ];
    }}
    <eval:foreach var="_conf" in="_fields">
      <tr style="border-left: 1px solid #AAAAAA;" class="hover">
        <td class="{{ class..'_name ae_editform_field_name' }}">{{ _conf.fname }}</td>
        {{ let field = _conf.field }}
        <td class="{{ class..'_value ae_editform_field_value oe_td_bool' }}">
          <ul class="{{ class..'_'..field..'_errors ae_editform_field_errors' }}" style="display: none;"><li>&nbsp;</li></ul>
          <pre class="script">
            var name   = name_prefix..'[attributes]['..field..']';
            var params = {
               select:    select[field],
               required:  list.contains(required, field),
               dynamic:   list.contains(dynamic, field),
               precision: field_prec[field]
            };
            
            if (references[field]) {
              let params ..= {reference: references[field]};
            }
            
            var template   = root..'/EditFormFields';
            var content    = wiki.template(template, [field_type[field], name, item[field], params, type, template, prefix]);
      
            if (string.contains(content, 'href="'..template..'"')) {
              let content = 'Template not found';
            }
          
            content;
          </pre>
        </td>
        {{ let field = _conf.comment }}
        <td class="{{ class..'_value ae_editform_field_value oe_td_comment' }}">
          <ul class="{{ class..'_'..field..'_errors ae_editform_field_errors' }}" style="display: none;"><li>&nbsp;</li></ul>
          <pre class="script">
            var name   = name_prefix..'[attributes]['..field..']';
            var params = {
               select:    select[field],
               required:  list.contains(required, field),
               dynamic:   list.contains(dynamic, field),
               precision: field_prec[field],
               attrs:     {style: _conf.style ?? ''},
               options:   {text: true}
            };
            
            if (references[field]) {
              let params ..= {reference: references[field]};
            }
            
            var template   = root..'/EditFormFields';
            var content    = wiki.template(template, [field_type[field], name, item[field], params, type, template, prefix]);
      
            if (string.contains(content, 'href="'..template..'"')) {
              let content = 'Template not found';
            }
          
            content;
          </pre>
        </td>
      </tr>
    </eval:foreach>
    
          </tbody>
          </table>
        </td>
      </tr>
      {{ var tabular = 'Misc'; let tab_s = list.select(tab_s, "$ != 'Misc'"); }}
      <tr>
        <td colspan="2" style="padding-top: 23px;">
          {{
             var template = root..'/Tabulars/EditForm';
             var tpl_params = [
               uid,
               {main_kind: puid.kind, main_type: puid.type, kind: 'tabulars', type: tabular},
               tabulars[tabular],
               root,
               template,
               {name_prefix: name_prefix..'[tabulars]'},
               prefix
             ];
             var content  = wiki.template(template, tpl_params);
      
             if (string.contains(content, 'href="'..template..'"'))
             {
                let content = 'Template not found';
             }
          
             content;
          }}
        </td>
      </tr>
      <tr>
        <td colspan="2" style="padding-top: 23px;">
          <table id="AcceptCondition" style="border-top: 0 none; border-left: 0 none;">
          <thead>
            <tr>
              <th colspan="2" style="border-right: 0 none;">
                15. PROJECT MANAGERS DECLARATION OF ACCEPTANCE
              </th>
            </tr>
          </thead>
          <tbody>
          {{
             var field   = 'AcceptCondition';
             var name    = name_prefix..'[attributes]['..field..']';
             
             if (item[field])
             {
                if (item[field] == 'NotSatisfyingNotAccept')
                {
                   var checked = 3;
                }
                else if (item[field] == 'PartialSatisfyingAccept')
                {
                   var checked = 2;
                }
                else
                {
                   var checked = 1;
                }
             }
             else var checked = 1;
             
             let _fields = [
                {
                   value: 1,
                   text:  'SatisfyingAccept',
                   desc:  'The project is satisfying and I accept the responsibility for carrying out the project.'
                },
                {
                   value: 2,
                   text:  'PartialSatisfyingAccept',
                   desc:  'The project is not completely satisfying, but I accept the responsibility for carrying out the project under the conditions specified below.'
                },
                {
                   value: 3,
                   text:  'NotSatisfyingNotAccept',
                   desc:  'The project is not satisfying because of the causes listed below and I cannot accept the responsibility for carrying out this project.'
                }
             ];
          }}
          <tr style="border-left: 1px solid #AAAAAA;">
            <td style="width: 2px; padding: 2px 8px 0 8px !important;" colspan="2">
              <ul class="{{ class..'_'..field..'_errors ae_editform_field_errors' }}" style="display: none; padding-top: 9px;"><li>&nbsp;</li></ul>
            </td>
          </tr>
          <eval:foreach var="_conf" in="_fields">
            <tr style="border-left: 1px solid #AAAAAA;" class="hover">
              <td style="width: 400px; border-right: 0 none !important;">
                {{
                   var input = '&lt;input type="radio" name="'..name..'" value="'.._conf.value..'"';
                   
                   if (checked == _conf.value)
                   {
                      let input ..= ' checked';
                   }
                   
                   let input ..= '&gt;';
                   
                   web.html(input);
                }}
                <span>{{ _conf.text }}</span>
                <p class="desc">{{ _conf.desc }}</p>
              </td>
              <td>{{ ' ' }}</td>
            </tr>
          </eval:foreach>
          
          </tbody>
          </table>
        </td>
      </tr>
    <eval:foreach var="tabular" in="tab_s">
      <tr>
        <td colspan="2" style="padding-top: 23px;">
          {{
             var template = root..'/Tabulars/EditForm';
             var tpl_params = [
               uid,
               {main_kind: puid.kind, main_type: puid.type, kind: 'tabulars', type: tabular},
               tabulars[tabular],
               root,
               template,
               {name_prefix: name_prefix..'[tabulars]'},
               prefix
             ];
             var content  = wiki.template(template, tpl_params);
      
             if (string.contains(content, 'href="'..template..'"'))
             {
                let content = 'Template not found';
             }
          
             content;
          }}
        </td>
      </tr>
    </eval:foreach>
      <tr>
        <td class="ae_submit" colspan="2" style="padding-top: 27px; padding-bottom: 8px;">
          {{ &lt;input type="button" value="Save and Close" class="ae_command" command="save_and_close" /&gt;&nbsp; }}
          {{ &lt;input type="button" value="Save" class="ae_command" command="save" /&gt;&nbsp; }}
          {{ &lt;input type="button" value="Close" class="ae_command" command="cancel" /&gt; }}
        </td>
      </tr>
    </tbody>
    </table>
  </form>
  {{ &lt;script type="text/javascript"&gt;" ae_name_prefix['"..js_uid.."'] = '"..name_prefix.."[attributes]';"&lt;/script&gt; }}
  <eval:if test="item._id &gt; 0">
    {{ &lt;script type="text/javascript"&gt;" generateActionsMenu('."..class.."_actions', '"..kind.."', '"..type.."', "..item._id..", {print: "..(#layout > 0 ? '\''..Json.Emit(layout)..'\'' : 'false').."});"&lt;/script&gt; }}
  </eval:if>
  <eval:if test="item._post &gt; 0">
    {{ 
       &lt;script type="text/javascript"&gt;"
         disabledForm('#"..class.."_item');
         displayMessage('"..class.."', 'To edit the document you must &lt;a href=\"#\" onclick=\"javascript:clearPosting(\\\'"..kind.."\\\', \\\'"..type.."\\\', "..item._id..", \\\'"..class.."\\\'); return false;\"&gt;clear posting&lt;/a&gt;', 2);
       "&lt;/script&gt;
    }}
  </eval:if>
</eval:else>
