<?php
/*
 * MindTouch Core - open source enterprise collaborative networking
 * Copyright (c) 2006-2010 MindTouch Inc.
 * www.mindtouch.com oss@mindtouch.com
 *
 * For community documentation and downloads visit www.opengarden.org;
 * please review the licensing section.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
 * http://www.gnu.org/copyleft/gpl.html
 */

if (defined('MINDTOUCH_DEKI')) :

DekiPlugin::registerHook(Hooks::SKIN_NAVIGATION_PANE, 'wfSkinNavigationPane');
DekiPlugin::registerHook(Hooks::MAIN_PROCESS_OUTPUT, 'wfSkinNavigationIncludes');

function wfSkinNavigationIncludes()
{
	$type = strtolower(wfGetConfig('ui/nav-type', 'compact'));
	switch ($type)
	{
		case 'none':
		
			// no navigation tree, nothing to do
			return;
		case 'expandable':
		
			// expandable AJAX tree
			DekiPlugin::includeCss('oiltec_nav_pane', 'expandablenav.css');
			DekiPlugin::includeJavascript('oiltec_nav_pane', 'expandablenav.js');
			break;
			
		case 'compact':
		default:
		
			// original compact tree
			DekiPlugin::includeJavascript('oiltec_nav_pane', 'rgbcolor.js');
			DekiPlugin::includeJavascript('oiltec_nav_pane', 'nav.js');
			DekiPlugin::includeCss('oiltec_nav_pane', 'oiltec_nav_pane.css');
			break;
	}
}

/*
 * @param $Title - currently requested title object
 * @param string &$html - markup to embed into the skin
 */
function wfSkinNavigationPane($Title, &$html)
{
	global $wgNavPaneEnabled, $wgNavPaneWidth, $wgUser;
	
	// set defaults
	$width   = isset($wgNavPaneWidth) ? $wgNavPaneWidth : 1600;
	$enabled = isset($wgNavPaneEnabled) ? $wgNavPaneEnabled : true;
	$type    = strtolower(wfGetConfig('ui/nav-type', 'compact'));
	$roots   = array_combine(ExternalConfig::$extconfig['installer']['root_path'], array_keys(ExternalConfig::$extconfig['installer']['root_path']));
	$appRoot = '';
	
	// check if nav is disabled
	if ($type === 'none') 
	{
		return '';
	}
	
	$pageTitle = $Title->isHomepage() ? 'home' : '='.$Title->getPrefixedText();
    
	// Check page title
	if (!$wgUser->isGroupMember('StandardMenu', true))
	{
	   foreach ($roots as $root => $app)
	   {
	      if (false !== ($pos = strpos($pageTitle, $root)))
	      {
	         if ($pos > 1) $pos--;
	         
	         $end = strlen($root) + $pos;
	         
	         if ($end == strlen($pageTitle) || $pageTitle{$end} == '/')
	         {
	            $pageTitle = substr($pageTitle, 0, $pos);
	            
	            $appRoot = $root;
	            
	            break;
	         }
	      }
	   }
	}
	
	// get the nav pane html... from the api... (sad)
	$Result = DekiPlug::getInstance()
		->At('site', 'nav', $pageTitle, 'full')
		->With('width', $width)
		->With('type', $type)
		->Get();
	
	if ($Result->isSuccess())
	{
	    $html =
		'<div id="siteNavTree">'.
			($appRoot ? _oefPrepareMenu($Title, $Result->getVal('body/tree'), $appRoot) : $Result->getVal('body/tree')).
		'</div>';
		
		if ($enabled)
		{
		   global $IP;

           require_once($IP.'/includes/JSON.php');
		   
           $JSON = new Services_JSON();

			// add the javascript
			// add the pane width via javascript
			$html .= '<script type="text/javascript">'.
			    'var _appSolutionRoot = '.$JSON->encode($roots).';'.
				'var navMaxWidth = '.(int)$width .';'.
				(($type === 'compact') ? 'YAHOO.util.Event.onAvailable("siteNavTree", ((typeof Deki.nav != "undefined") ? Deki.nav.init : null), Deki.nav, true);' : '').
			'</script>';
		}
	}
	else
	{
		$html = '<div id="siteNavTree"></div>';
	}
}

/**
 * Change standard menu
 * 
 * @param object $Title
 * @param string $html
 * @param string $appRoot
 * @return string
 */
function _oefPrepareMenu($Title, &$html, $appRoot)
{
   $conf = ExternalConfig::$extconfig['installer'];
   $path = 'path="'.$appRoot.'/"';
   
   if (false === ($pos = strpos($html, $path))) return $html;
   if (false === ($pos = strpos($html, '</div>', $pos))) return $html;
   
   $pos  += 6;
   $fname = $conf['root'].$conf['base_dir'].$conf['applied_solutions_dir'].'/'.$conf['root_path'][$appRoot].'/MindTouch/Menu/menu.xml';
   
   if (!is_readable($fname))
   {
      return $html;
   }
   
   $html = substr($html, 0, $pos).'<div id="oefSubMenu">'._oefGenerateSubMenu($fname).'</div>'.substr($html, $pos);
   
   return $html;
}

/**
 * Generate submenu
 * 
 * @param string $path - path to menu.xml
 * @return string
 */
function _oefGenerateSubMenu($path)
{
   require_once(dirname(__FILE__)."/navmenu.php");
   
   $menu = new NavMenu($path);
   $html = '';
   
   if ($menu->menuItems != NULL)
   {
      foreach ($menu->menuItems as $item1)
      {
         $html .= _oefShow1LevelVMenuItem($item1->title, $item1->url);
         
         foreach ($item1->menuItems as $item2)
         {
            $html .= _oefShow2LevelVMenuItem($item2->title, $item2->url);
         }
      }
   }
   
   return $html;
}

/*
function ShowFixedVMenuItem($title, $link)
{
   print("<div class=\"node dockedNode homeNode lastDocked parentClosed\">");
   print("<a title=\"$title\" href=\"$link\"><span>$title</span></a>");
   print("</div>\n");
}
*/

/**
 * Generate menu item
 * 
 * @param string $title
 * @param string $link
 * @return string
 */
function _oefShow1LevelVMenuItem($title, $link)
{
   // selected
   $html  = '<div class="node childNode sibling'.($_SERVER['REQUEST_URI'] == $link ? ' oef_selected' : '').'">';
   $html .= "<a title=\"$title\" href=\"$link\"><span>$title</span></a>";
   $html .= "</div>\n";
   
   return $html;
}

/**
 * Generate menu item
 * 
 * @param string $title
 * @param string $link
 * @return string
 */
function _oefShow2LevelVMenuItem($title, $link)
{
   $html  = '<div class="node childNode selectedChild'.($_SERVER['REQUEST_URI'] == $link ? ' oef_selected' : '').'">';
   $html .= "<a title=\"$title\" href=\"$link\"><span>$title</span></a>";
   $html .= "</div>\n";
   
   return $html;
}

endif;
