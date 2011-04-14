{{
   var uid     = args[0];
   var puid    = args[1];
   var root    = args[2];
   var current = args[3];
   var params  = args[4];
   var prefix  = args[5] ?? 'default';
   
   var dir = params.dir ?? '/';
}}
<div class="oe_oiltec_intranet_desktop">
  <img src="{{ dir..'/Resources/Desktop/oiltec_intranet_desktop.png' }}" width="695" height="1061" alt="Desktop" usemap="#OiltecIntranetDesktop">
  <map name="OiltecIntranetDesktop">
    <!-- Sales -->
    <area shape="poly" coords="74,46, 131,46, 154,70, 154,148, 74,148"   href="" onclick="openPopup(this, 'documents', 'Contract', 'EditForm'); return false;" alt="Contract">
    <area shape="poly" coords="204,46, 260,46, 284,70, 284,148, 204,148" href="" onclick="openPopup(this, 'documents', 'ProjectHandover', 'EditForm'); return false;" alt="Project Handover">
    <area shape="poly" coords="336,46, 392,46, 416,70, 416,148, 336,148" href="" onclick="openPopup(this, 'documents', 'Invoice', 'EditForm'); return false;" alt="Invoice">
    <area shape="poly" coords="558,68, 637,68, 660,90, 660,129, 558,129" href="{{ page.path..'/Catalogs_Counteragents' }}" alt="Counteragents">
    <!-- ProjectManager -->
    <area shape="poly" coords="132,271, 190,271, 214,294, 214,374, 132,374" href="" onclick="openPopup(this, 'documents', 'ProjectRegistration', 'EditForm'); return false;" alt="Project Registration">
    <area shape="poly" coords="270,271, 328,271, 350,294, 350,374, 270,374" href="" onclick="openPopup(this, 'documents', 'ProjectAssignment', 'EditForm'); return false;" alt="Project Assignment">
    <area shape="poly" coords="412,271, 470,271, 494,294, 494,374, 412,374" href="" onclick="openPopup(this, 'documents', 'ProjectClosure', 'EditForm'); return false;" alt="Project Closure">
    <area shape="poly" coords="558,298, 636,298, 660,321, 660,360, 558,360" href="{{ page.path..'/Catalogs_Projects' }}" alt="Projects">
    <area shape="rect" coords="216,422, 296,474" href="{{ page.path..'/Report_ResourceAllocation' }}" alt="Resource Allocation">
    <area shape="rect" coords="318,422, 396,474" href="{{ page.path..'/Report_ResourceLoad' }}" alt="Resource Load">
    <area shape="rect" coords="414,422, 494,474" href="{{ page.path..'/Report_TimeCards' }}" alt="Time Reporting">
    <!-- Employee -->
    <area shape="poly" coords="412,530, 471,530, 494,552, 494,632, 412,632" href="" onclick="openPopup(this, 'documents', 'TimeCard', 'EditForm'); return false;" alt="Time Card">
    <!-- HR -->
    <area shape="poly" coords="162,697, 222,697, 245,720, 245,799, 162,799" href="" onclick="openPopup(this, 'documents', 'RecruitingOrder', 'EditForm'); return false;" alt="Recruiting Order">
    <area shape="poly" coords="307,697, 366,697, 389,720, 389,799, 307,799" href="" onclick="openPopup(this, 'documents', 'VacationOrder', 'EditForm'); return false;" alt="Vacation Order">
    <area shape="poly" coords="448,697, 507,697, 530,720, 530,799, 448,799" href="" onclick="openPopup(this, 'documents', 'DismissalOrder', 'EditForm'); return false;" alt="Dismissal Order">
    <area shape="poly" coords="558,689, 637,689, 660,712, 660,752, 558,752" href="{{ page.path..'/Calendar' }}" alt="Calendar">
    <area shape="poly" coords="558,767, 637,767, 660,790, 660,830, 558,830" href="{{ page.path..'/Catalogs_Schedules' }}" alt="Schedules">
    <area shape="poly" coords="66,836, 145,836, 167,858, 167,898, 66,898"   href="{{ page.path..'/Catalogs_NaturalPersons' }}" alt="Natural Persons">
    <area shape="poly" coords="236,836, 314,836, 336,859, 336,898, 236,898" href="{{ page.path..'/Catalogs_Employees' }}" alt="Employees">
    <!-- Other Catalogs -->
    <area shape="poly" coords="72,964, 151,964, 174,986, 174,1025, 72,1025"   href="{{ page.path..'/Catalogs_OrganizationalUnits' }}" alt="Organizational Units">
    <area shape="poly" coords="194,964, 272,964, 294,986, 294,1025, 217,1025" href="{{ page.path..'/Catalogs_OrganizationalPositions' }}" alt="Organizational Positions">
    <area shape="poly" coords="308,964, 387,964, 410,986, 410,1025, 308,1025" href="{{ page.path..'/Catalogs_Nomenclature' }}" alt="Nomenclature">
    <area shape="poly" coords="434,964, 512,964, 536,986, 536,1025, 434,1025" href="{{ page.path..'/Catalogs_SubProjects' }}" alt="Subprojects">
  </map>
</div>
