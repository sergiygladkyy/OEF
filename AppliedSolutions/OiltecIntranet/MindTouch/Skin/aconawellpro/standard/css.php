<?php 
/*
 * MindTouch Deki - enterprise collaboration and integration platform
 *  derived from MediaWiki (www.mediawiki.org)
 * Copyright (C) 2006 MindTouch, Inc.
 * www.mindtouch.com  oss@mindtouch.com
 *
 * For community documentation and downloads visit www.opengarden.org;
 *  please review the licensing section.
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

// necessary for LocalSettings.php
define('MEDIAWIKI', true); 

// chdir() will attempt to load LocalSettings.php magically;
// if this fails, you will need to explicitly set the path
chdir($_SERVER['DOCUMENT_ROOT']);
require_once('includes/Defines.php');
require_once('LocalSettings.php');
require_once($IP . '/includes/libraries/ui_handlers.php');

$CSS = new CssHandler(__FILE__);

// add a template css file (located in the template directory)
$CSS->addTemplate('common.css');
$CSS->addTemplate('special.css');

$CSS->addSkin('_content.css');
//$CSS->addSkin('standard.css');

// create the cache file
$CSS->process();

?>