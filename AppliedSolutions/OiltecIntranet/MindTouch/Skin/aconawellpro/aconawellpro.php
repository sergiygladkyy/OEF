<?php
/*
 * MindTouch Deki - enterprise collaboration and integration platform
 *  derived from MediaWiki (www.mediawiki.org)
 * Copyright (C) 2006-2009 MindTouch, Inc.
 * www.mindtouch.com  oss@mindtouch.com
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

/**
 * Base Template
 *
 * @todo document
 * @package MediaWiki
 * @subpackage Skins
 */

if( !defined( 'MINDTOUCH_DEKI' ) )
    die();

/** */
require_once('includes/SkinTemplate.php');

/**
 * Inherit main code from SkinTemplate, set the CSS and template filter.
 * @todo document
 * @package MediaWiki
 * @subpackage Skins
 */
class SkinAconaWellpro extends SkinTemplate {
    /**
     * @type const - Defines the number of custom HTML areas available
     */
    const HTML_AREAS = 0;

    /** Using BasePlus. */
    function initPage( &$out ) {
        SkinTemplate::initPage( $out );
        $this->skinname  = 'AconaWellpro';
        $this->stylename = 'standard';
        $this->template  = 'AconaWellproTemplate';
    }

    /**
     * (non-PHPdoc) customize display for dashboard header
     * @see includes/Skin#dashboardHeader($PageInfo)
     */
    function renderUserDashboardHeader($Article, $Title)
    {
	global $wgUser;

	$html = '';

	if (defined('PAGE_ALERTS')) {
		$enablePageAlerts = !$wgUser->isAnonymous() && $wgUser->canSubscribe();
		$html .= DekiPageAlertsPlugin::getPageAlertsButton($Article, $enablePageAlerts);
	}

	return $html;
    }
}
    
class AconaWellproTemplate extends QuickTemplate
{

    function setupDashboard()
    {
        // move page alerts into dashboard header
	$this->set('page.alerts', '');
    }

    /**
     * Template filter callback for Base skin.
     * Takes an associative array of data set from a SkinTemplate-based
     * class, and a wrapper for MediaWiki's localization database, and
     * outputs a formatted page.
     *
     * @access private
     */
    function execute() {
        global $wgLogo, $wgUser, $wgTitle, $wgRequest, $wgArticle, $wgOut, $editor, $wgScriptPath, $wgContLang, $wgMenus, $IP;
        global $wgFiestaCMSMode;
        $sk = $wgUser->getSkin();
        $isArticle = $editor || $wgArticle->getID() > 0 || $wgArticle->mTitle->isEmptyNamespace();
        
        $this->cmsmode = false;
        
        if (isset($wgFiestaCMSMode)) 
        {
            $this->cmsmode = isset($wgFiestaCMSMode) ? (bool) $wgFiestaCMSMode : false;  /*Toggle on/off Fiesta CMS mode*/
        } 
        else if (!is_null(wfGetConfig('ui/fiesta-cmsmode', null))) 
        {
            $this->cmsmode = wfGetConfig('ui/fiesta-cmsmode', null) === true;
        }
        
        // allow variable overrides
        DekiPlugin::executeHook(Hooks::SKIN_OVERRIDE_VARIABLES, array(&$this));

        echo('<?xml version="1.0" encoding="UTF-8"?>');
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php $this->text('lang') ?>" lang="<?php $this->text('lang') ?>" dir="<?php $this->text('dir')?>">
<head>
    <script type="text/javascript">var _starttime = new Date().getTime();</script>
    <meta http-equiv="Content-Type" content="<?php $this->text('mimetype') ?>; charset=<?php $this->text('charset') ?>" />
    <?php $this->html('headlinks') ?>
    <title><?php $this->text('pagetitle'); ?></title>
    
    <!-- default css -->
    <link rel="stylesheet" type="text/css" media="screen" href="<?php $this->html('pathtpl'); ?>/_reset.css"/>
    <?php $this->html('screencss'); ?>
    <?php $this->html('printcss'); ?>
    
    <!-- default scripting -->
    <?php $this->html('javascript'); ?>
    
    <!-- specific screen stylesheets-->
    <?php if (!Skin::isPrintPage()) { ?>
        <link rel="stylesheet" type="text/css" media="screen" href="<?php $this->html('pathskin'); ?>/css.php"/>
        <link rel="stylesheet" type="text/css" media="screen" href="<?php $this->html('pathtpl'); ?>/css/main.css" />
        <link rel="stylesheet" type="text/css" media="screen" href="<?php $this->html('pathtpl'); ?>/css/ie6.css" />
    <?php } else { ?>
        <link rel="stylesheet" type="text/css" media="screen" href="<?php $this->html('pathskin'); ?>/_content.css" />
        <link rel="stylesheet" type="text/css" media="screen" href="<?php $this->html('pathtpl'); ?>/css/main.css" />
        <link rel="stylesheet" type="text/css" media="screen" href="<?php $this->html('pathtpl'); ?>/css/ie6.css" />
        <link rel="stylesheet" type="text/css" media="screen" href="<?php $this->html('pathtpl'); ?>/_print.css" />
        <link rel="stylesheet" type="text/css" media="screen" href="<?php $this->html('pathcommon'); ?>/prince.content.css" />
    <?php } ?>

    <!-- specific print stylesheets -->
    <link rel="stylesheet" type="text/css" media="print" href="<?php $this->html('pathtpl'); ?>/_print.css" />
	
    <!-- IE6 & IE7 specific stuff -->
    <!--[if IE]><meta http-equiv="imagetoolbar" content="no" /><![endif]-->
    
    <?php $this->html('inlinejavascript'); ?>
    
    <!-- styles overwritten via control panel - load this css last -->    
    <?php $this->html('customhead'); ?> 
    
    <script type="text/javascript">
        /*
         * Manage main page orange menu items
         */
        function mainPageTopMenu(item, text, link)    {
            var divTopMenu = document.getElementById("submenu");
            var divElementCollection = null;

            // DOM3 = IE5, NS6
            if (divTopMenu != null)
                divElementCollection = divTopMenu.getElementsByTagName("div");

            if (divElementCollection == null)
                return;

            var i;
            for (i in divElementCollection)
            {
                var divElement = divElementCollection[i];
                if (divElementCollection[i].id == item)
                    divElement.innerHTML = "<a href=\"" + link + "\">" + text + "</a>";
            }
        }
        
        /*
         * Manage "picture block" sextions: header, text, button
         */
        function mainPageSetElemInnerHTML(element, innerHTML)    {
            var divElement = document.getElementById(element);

            if (divElement != null)
                divElement.innerHTML = innerHTML;
        }

        function mainPageBlockLeftHeader(text, link)    {
            var elemName = "main-pic-header-sb0-header";
            var innerHTML = "<h2><a href=\"" + link + "\">" + text + "</a></h2>";    
            mainPageSetElemInnerHTML(elemName, innerHTML);
        }

        function mainPageBlockMiddleHeader(text, link)    {
            var elemName = "main-pic-header-sb1-header";
            var innerHTML = "<h2><a href=\"" + link + "\">" + text + "</a></h2>";    
            mainPageSetElemInnerHTML(elemName, innerHTML);
        }

        function mainPageBlockRightHeader(text, link)    {
            var elemName = "main-pic-header-sb2-header";
            var innerHTML = "<h2><a href=\"" + link + "\">" + text + "</a></h2>";    
            mainPageSetElemInnerHTML(elemName, innerHTML);
        }

        function mainPageBlockLeftText(text)    {
            var elemName = "main-pic-header-sb0-text";
            var innerHTML = "<p>" + text + "</p>";    
            mainPageSetElemInnerHTML(elemName, innerHTML);
        }

        function mainPageBlockRightText(text)    {
            var elemName = "main-pic-header-sb2-text";
            var innerHTML = "<p>" + text + "</p>";    
            mainPageSetElemInnerHTML(elemName, innerHTML);
        }

        function mainPageBlockLeftButtonMore(link)    {
            var elemName = "main-pic-header-sb0-mbutton";
            var innerHTML = "<a href=\"" + link + "\"  class=\"more\">" +
                "<img src=\"<?php $this->html('pathtpl'); ?>/images/sb0-more.png\" alt=\"more\" /></a>";    
            mainPageSetElemInnerHTML(elemName, innerHTML);
        }

        function mainPageBlockRightButtonMore(link)    {
            var elemName = "main-pic-header-sb2-mbutton";
            var innerHTML = "<a href=\"" + link + "\"  class=\"more\">" +
                "<img src=\"<?php $this->html('pathtpl'); ?>/images/sb0-more.png\" alt=\"more\" /></a>";    
            mainPageSetElemInnerHTML(elemName, innerHTML);
        }
        function mainPageTopMenuBackground(path)
        {
            var divTopMenu = document.getElementById("submenu");
            divTopMenu.style.backgroundImage="url("+path+")";
        }
        function mainPageTopSubMenuBackground(path)
        {
            var divTopMenu = document.getElementById("tsubmenu");
            divTopMenu.style.backgroundImage="url("+path+")";
        }
        function mainPageBlockLeftHeaderBackground(path)
        {
            var divTopMenu = document.getElementById("main-pic-header-sb0-header");
            divTopMenu.style.backgroundImage="url("+path+")";
        }
        function mainPageMiddleHeaderBackground(path)
        {
            var divTopMenu = document.getElementById("sb b2-w");
            divTopMenu.style.backgroundImage="url("+path+")";
        }
        function mainPageBlockRightBackground(path)
        {
            var divTopMenu = document.getElementById("main-pic-right-header");
            divTopMenu.style.backgroundImage="url("+path+")";
        }
        function mainPageBlockLeftTextBackground(path)
        {
            var divTopMenu = document.getElementById("sb-b sb0-bottom");
            divTopMenu.style.backgroundImage="url("+path+")";
        }
        function mainPageBlockMiddleTextBackground(path)
        {
            var divTopMenu = document.getElementById("main-pic-header-sb1-header");
            divTopMenu.style.backgroundImage="url("+path+")";
        }
        

        function mainPageSetBackground(name)
        {
            var path = '<?php echo $sk->getSkinPath().'/../images/headers/' ?>' + name + '/';

            mainPageTopSubMenuBackground(path + 'tsubmenu-back.jpg');
            mainPageMiddleHeaderBackground(path + 'b2-back.jpg');
            mainPageBlockRightBackground(path + 'b2-img.jpg');
            mainPageBlockLeftTextBackground(path + 'sb0-back.png');
            mainPageBlockLeftHeaderBackground(path + 'sb0-img.jpg');
            mainPageBlockMiddleTextBackground(path + 'sb1-img.jpg');
        }
    </script>
</head>

<body<?php if (!$wgUser->isAnonymous()) { echo(' id="loggedin"'); } ?> class="<?php $this->html('pagetype'); ?> <?php $this->html('language');?>">

    <?php $this->html('pageheader'); ?>
	
    <div id="page">
        <!-- Header -->
        <div id="header">
            <a href="/" id="logo"><img src="<?php $this->html('pathtpl'); ?>/images/logo.png" alt="AconaWellPro" /></a>
            <div id="tmenu">
            <?php if (!$wgUser->isAnonymous())
            {
                // Recent changes and Help menu items are disabled for admin account but still available from the page footer.
                // This is done, because  Google Chrome and Apple Safari carries menu over the next line (if menu can not fit into
                // one line)
            ?>
                <a href="<?php $this->html('userpageurl')?>"><?php echo(wfMsg('Skin.Common.header-my-page')); ?></a>
                <?php if ($wgUser->canViewControlPanel()) { ?>
                <a href="<?php echo($sk->makeAdminUrl(''));?>"><?php echo(wfMsg('Admin.ControlPanel.page-title'));?></a>
                <?php } ?>	
                <?php if ($wgUser->canViewControlPanel() == false) { ?>
                    <a href="<?php echo($sk->makeSpecialUrl('Recentchanges'));?>"><?php echo(wfMsg('Page.RecentChanges.page-title'));?></a>
                <?php } ?>    
                <a href="#" onclick="return DWMenu.Position('menuTools', this, 0, 0);"><?php echo(wfMsg('Skin.Common.header-tools'));?></a>
            <?php
            }
            ?>
            </div>
			
            <div id="tsearch">
                <form action="/Special:Search">
                    <input type="text" class="search" name="search" id="searchInput" tabindex="1" value="Search" onfocus="if (this.value=='Search') this.value=''" onblur="if (this.value=='') this.value='Search'" />
                    <input type="hidden" value="fulltext" name="type" />
                    <input type="image" name="go" id="searchSubmit" class="ssubmit" src="<?php $this->html('pathtpl'); ?>/images/tsearch-submit.png" />
                </form>
            </div>
            <div id="tlogin">
                <?php $this->UserAuth(); ?>
            </div>
        </div>

        <?php
        if ($this->data['pagetype'] && strpos($this->data['pagetype'],'home')>0) {
        //
        // Index page
        //
        ?>
        <div id="tsubmenu" style="display:none;">
            <div id="submenu">
                <div id="pos1"></div>
                <div id="pos2"></div>
                <div id="pos3"></div>
                <div id="pos4"></div>
                <div id="pos5"></div>
                <div id="pos6"></div>
                <div id="pos7"></div>
                <div id="pos8"></div>
                <div id="pos9"></div>
                <div id="pos10"></div>
            </div>
        </div>
		
        <div id="pwrapper">
            <div id="leftSide">
                <h1><a href="/">About Acona Wellpro</a></h1>
                <?php $this->html('sitenavtext');  ?>
            </div>

            <div id="main-pic-header" style="float:left;width:751px;display:none;">
                <div class="sb sb0">
                    <div id="main-pic-header-sb0-header" class="sb-b sb0-top">
                    </div>
                    <div class="sb-b sb0-bottom" id="sb-b sb0-bottom">
                        <div id="main-pic-header-sb0-text"><p></p></div>
                        <div id="main-pic-header-sb0-mbutton"></div>
                        <!-- <a href="#" class="more"><img src="<?php $this->html('pathtpl'); ?>/images/sb0-more.png" alt="more" /></a> -->
                    </div>
                </div>
                <div class="sb b2-w" id="sb b2-w">
                    <div class="sb sb1" style="margin-top:79px;height:auto;">
                        <div id="main-pic-header-sb1-header" class="sb-b sb1-top">
                        </div>
                    </div>
                </div>
                <div class="b2" id="main-pic-right-header">
                    <div id="main-pic-header-sb2-header"><h2>&nbsp;</h2></div>
                    <div id="main-pic-header-sb2-text"><p></p></div>
                    <div id="main-pic-header-sb2-mbutton"></div>
                    <!-- <a href="#" class="more"><img src="<?php $this->html('pathtpl'); ?>/images/b2-more.png" alt="more" /></a> -->
                </div>
            </div>
		
            <div id="rightSide">
                <div style="padding: 5px; float: left; width: 743px;">
                    <?php $this->PageMenu(); ?>
                </div>
                <div id="pageInner" style="float:left;width:733px;">
                    <div id="page-body" style="padding:5px">
                        <?php $this->html('bodytext'); ?>
                    </div>
                </div>
            </div>

        </div>

        <?php
        // 
        // End of Index page (body)
        //
        } else {
	//
        // Landing page
        //
	?>

        <!-- Page wrapper -->
        <div id="pwrapper">
		
            <div id="leftSide">
                <h1><a href="/">About Acona Wellpro</a></h1>
                <?php $this->html('sitenavtext'); ?>
            </div>

            <div id="rightSide">
                <!-- Page content -->
                <div id="pageContent">
                    <!-- required print options -->
                    <?php $this->html('printoptions'); ?>

                    <!-- Page header: hierarhy and revision -->
                    <div class="page-header">
                        <table cellpadding="0" cellspacing="0" width="100%">
                            <tbody>
                            <tr>
                                <td colspan="2">
                                    <?php $this->PageMenu(); ?>
                                </td>
                            </tr>
                            <tr>
                                <td style="text-align:left">
                                    <?php if ($this->hasData('hierarchy') && $wgTitle->isEditable() && Skin::isViewPage()) { ?>
                                        <div id="pageHierarchy" class="hierarchy">
                                        <?php $this->html('hierarchy'); ?>
                                        </div>
                                    <?php } ?>
                                </td>
                                <td style="text-align:right">
                                    <div id="pageRevision" class="pageRevision">
                                        <!-- last modified -->
                                        <?php
                                        if (!$wgUser->isAnonymous())
                                        {
                                            echo($this->haveData('pagemodified'));
                                        }
                                        $this->PageAccess();
                                        ?>
                                        <!-- end last modified -->
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                        </table>
                    </div>
                    <!-- End of Page header: hierarhy and revision -->

                    <?php if (!Skin::isNewPage() && !Skin::isEditPage()) {?>
                    <div id="pageTitle" class="pageTitle hideforedit">
                        <?php
                        if (!$wgUser->isAnonymous())
                        {
                            if ($this->hasData('page.alerts')  && $this->cmsmode == false) :
                                $this->html('page.alerts');
                            endif;
                            if ($this->haveData('pageismoved'))
                            {
                                echo('<a class="pageMoved" href="'.
                                        $this->haveData('pagemovelocation').
                                        '">'.$this->haveData('pagemovemessage').
                                        '</a>');
                            }
                        }
                        ?>
                        <?php if ($this->hasData('page.rating.header')) : ?>
                            <div class="hideforedit">
                                <?php $this->html('page.rating.header'); ?>
                            </div>
                        <?php endif; ?>
                        <h1 id="title">
                            <span <?php if ($this->haveData('pageisrestricted')) { echo(' class="pageRestricted" '); }?>>
                                <?php $this->html('page.title'); ?>
                            </span>
                        </h1>
                    </div>
                    <?php } ?>
                    <div id="pageInner">
                        <?php $this->html('pagesubnav'); ?>

                        <div class="pageStatus">
                            <?php  echo(wfMessagePrint());  ?>
                        </div>

                        <div class="<?php $this->html('pagename'); ?>">
                            <div id="page-body">
                                <?php $this->html('bodytext'); ?>
                            </div>

                            <!-- Edit tags, page info -->

                            <?php if ($wgTitle->isEditable() && Skin::isViewPage() && !$wgUser->isAnonymous()) { ?>
                                <div id="pageInfo" class="pageInfo">
                                    <div id="pageTagsLabel" class="pageTags"><strong><?php echo(wfMsg('Skin.Common.tags'));?></strong></div>
                                    <div id="pageTags" class="pageTags">
                                        <?php $this->html('tagstext'); ?>
                                    </div>

                                    <?php if ($wgTitle->canEditNamespace()) { ?>
                                        <div id="pageILinksLabel" class="pageIncomingLinks">
                                            <span>
                                                <?php /* echo(wfMsg('Skin.Common.what-links-here')); */ ?>
                                            </span>
                                        </div>
                                        <div id="pageILinks" class="pageIncomingLinks"></div>
                                    <?php } ?>
                                </div>
                            <?php } ?>
                            <?php
                            if ($this->hasData('languages') && $wgTitle->isEditable()) {
                                echo('<div class="pageInfo languages"><strong>'.wfMsg('Article.Common.languages').'</strong>');
                                $this->html('languages');
                                echo('</div>');
                            }
                            ?>

                            <!-- End Edit tags, page info -->

                        </div>

                        <!-- Attachments -->

                        <?php if (Skin::isViewPage() && $wgTitle->isEditable() && !$wgUser->isAnonymous()) { ?>
                            <div class="b-attachments" id="attachments">

                                <!-- File attachments -->
                                <div id="file-section" class="file-section">
                                    <?php
                                    echo('<div class="filesheader">'
                                        .'<div class="filesformlink">'
                                        .$this->haveData('fileaddlink')
                                        .'</div>'
                                        .'<div class="filesheadertext selected">'
                                        .Skin::iconify('file')
                                        .' <span class="text">'
                                        .wfMsg('Skin.Common.header-files-count',
                                        $this->haveData('filedisplaycount'))
                                        .'</span></div>'
                                        .'</div>');
                                    ?>
                                    <?php $this->html('filestext');    ?>
                                </div>
                                <!-- End of File attachments -->

                                <!--Gallery -->
                                <?php if ($this->haveData('gallerytext')) { ?>
                                    <div id="gallery-section" class="gallery-section">
                                        <?php
                                        echo('<div class="filesheader">'
                                            .'<a name="attachImages"></a>'
                                            .'<div class="filesheadertext selected">'.Skin::iconify('gallery')
                                            .'<span class="text">Gallery</span></div>'.'</div>');
                                        ?>
                                        <?php $this->html('gallerytext');?>
                                    </div>
                                <?php } ?>
                                <!-- End of Gallery -->

                                <!--Comments -->
                                <div id="comments-section" class="comments-section">
                                    <?php
                                    echo('<div class="filesheader">'
                                        .'<a name="attachImages"></a>'
                                        .'<div class="filesheadertext selected">'.Skin::iconify('comments').' <span class="text">'.wfMsg('Skin.Ace.header-comments-count', '('.$this->haveData('commentcount').')').'</span></div>'
                                        .'</div>');
                                    ?>
                                    <?php $this->html('comments'); ?>
                                </div>
                                <!-- End of Comments -->

                            </div>
                        <?php } ?>

                        <!-- End of Attachments -->

                    </div>

                </div>
                <!-- End of Page content -->
            </div>
		
        </div>
        <!-- End of Page wrapper -->
       
        <?php } ?>
        <div id="footer">
            <div class="fb0">
                <a href="/" class="flogo">
                    <img src="<?php $this->html('pathtpl'); ?>/images/flogo.png" alt="acona wellpro" />
                </a>
                <address>Acona Wellpro AS, <br />
                    Laberget 24, P.B. 216, 4066 Stavanger, <br />
                    telefon +47 52 97 76 00, <br />
                    <a href="mailto:aw@aconawellpro.com">aw@aconawellpro.com</a>
                </address>
            </div>
            <div class="fb1">
                <a href="/">ABOUT ACONA WELLPRO</a>
            </div>
            <div class="fb2">
                <?php if (!$wgUser->isAnonymous())
                {
                ?>
                    <a href="<?php $this->html('userpageurl')?>"><?php echo(wfMsg('Skin.Common.header-my-page')); ?></a>
                    <a href="<?php echo($sk->makeSpecialUrl('Recentchanges'));?>"><?php echo(wfMsg('Page.RecentChanges.page-title'));?></a>
                    <a href="#" onclick="return DWMenu.Position('menuTools', this, 0, 0);"><?php echo(wfMsg('Skin.Common.header-tools'));?></a>
                <?php
                }
                ?>
            </div>
	</div>

    </div>

    <script type="text/javascript">var _endtime = new Date().getTime(); var _size = <?php echo(ob_get_length())?>;</script>

    <div onclick="DWMenu.Bubble=true;" class="menu" id="menuTools" style="display:none;">
    <ul><?php
        if (!$wgUser->isAnonymous()) { 
            $this->SiteTools('Watchedpages', 'Page.WatchedPages.page-title');
            $this->SiteTools('Contributions', 'Page.Contributions.page-title');
            $this->SiteTools('Preferences', 'Page.UserPreferences.page-title');
        }
        $this->SiteTools('ListRss', 'Page.ListRss.page-title');
        if (!$wgUser->isAnonymous()) 
            $this->SiteTools('Listusers', 'Page.Listusers.page-title');
        if (!$wgUser->isAnonymous()) 
            $this->SiteTools('ListTemplates', 'Page.ListTemplates.page-title');
        $this->SiteTools('Sitemap', 'Page.SiteMap.page-title');
        $this->SiteTools('Popularpages', 'Page.Popularpages.page-title');
        
        if (!$wgUser->isAnonymous()) { 
            echo sprintf('<li class="%s"><a href="%s" target="_blank" title="%s"><span></span>%s</a></li>', 
                'deki-desktop-suite', 
                'http://www.mindtouch.com/Products/Desktop_Suite', 
                wfMsg('Skin.Common.desktop-suite'), 
                wfMsg('Skin.Common.desktop-suite')
            );
        }
    ?></ul>    
</div>

    <div onclick="DWMenu.Bubble=true;" class="menu" id="menuPageOptions" style="display:none;">
    <ul><?php
        $this->PageMenuControl('edit', 'Skin.Common.edit-page');
        $this->PageMenuControl('add', 'Skin.Common.new-page');
        $this->PageMenuControl('restrict', 'Skin.Common.restrict-access');
        $this->PageMenuControl('attach', 'Skin.Common.attach-file');
        $this->PageMenuControl('move', 'Skin.Common.move-page');
        $this->PageMenuControl('delete', 'Skin.Common.delete-page');
        $this->PageMenuControl('print', 'Skin.Common.print-page');
        $this->PageMenuControl('tags', 'Skin.Common.tags-page');
        $this->PageMenuControl('email', 'Skin.Common.email-page');
        $this->PageMenuControl('properties', 'Skin.Common.page-properties');
        $this->PageMenuControl('talk', 'Skin.Common.page-talk');
        if ($wgTitle->userIsWatching()) { 
            $this->PageMenuControl('watch', 'Skin.Common.unwatch-page');
        }
        else {
            $this->PageMenuControl('watch', 'Skin.Common.watch-page');
        }
        ?>
    </ul>
    </div>

    <div onclick="DWMenu.Bubble=true;" class="menu" id="menuBacklink" style="display:none;">
        <?php $this->html('pagebacklinks'); ?>
    </div>

    <div onclick="DWMenu.Bubble=true;" class="menu" id="menuPageContent" style="display:none;">
        <?php $this->html('toc'); ?>
    </div>

    <?php $this->html('pagefooter'); ?>

</body>
<?php $this->html('customtail'); ?>
</html>


<?php
    }

    function SiteTools($key, $languageKey) {
        global $wgUser;
        $sk = $wgUser->getSkin();
        $t = Title::makeTitle( NS_SPECIAL, $key );
        $href = $sk->makeSpecialUrl($key);
        if ($key == 'Contributions') {
            $href = $t->getLocalURL('target=' . urlencode( $wgUser->getName()));
        }
        elseif ($key == 'ListTemplates') {
             $t = Title::makeTitle('', NS_TEMPLATE);  
             $href = $sk->makeNSUrl('', '', NS_TEMPLATE);
        }
        elseif ($key == 'Listusers') {
             $t = Title::makeTitle('', NS_USER);  
             $href = $sk->makeNSUrl('', '', NS_USER);
        }
        else {
            $href = $t->getLocalURL();   
        }
        echo("\t".'<li class="site'.ucfirst($key).'"><a href="'.$href.'" title="'. wfMsg($languageKey) .'"><span></span>'. wfMsg($languageKey) .'</a></li>'."\n");
    }

    function PageSiteSearch() {
        $textSearch = null;
        $textSearchConst = wfMsg('Page.Search.search');
        
        if (isset($this->data['search'])) {
            $textSearch = $this->data['search'];
        }
        if ($textSearch == null)
            $textSearch = $textSearchConst;
?>    
        <fieldset class="search">
             <form action="<?php $this->text('searchaction') ?>">
                <input tabindex="1" id="searchInput" class="inputText" name="search" type="text" value="<?php echo($textSearch); ?>" 
                    onfocus="if (this.value == '<? echo($textSearchConst); ?>') this.value = ''; "/>
                <input type="hidden" name="type" value="fulltext" />
                <input id="searchSubmit" type="submit" name="go" class="searchSubmit" value="GO" title="<?php echo wfMsg('Skin.Common.submit-find'); ?>" />
            </form>
        </fieldset>
<?php        
    }

    function PageMenu() {
        global $wgUser;
?>
        <div id="pageNav">
            <table width="100%" cellpadding="0" cellspacing="0">
            <tbody>
                <tr>
                    <td>
                        <ul>
                            <li class="pageEdit"><?php $this->html('pageedit');?></li>
                            <li class="pageAdd"><?php $this->html('pageadd');?></li>
                            <li class="pageRestrict"><?php $this->html('pagerestrict');?></li>
                            <li class="pageAttach"><?php $this->html('pageattach');?></li>
                            <li class="pageMove"><?php $this->html('pagemove');?></li>
                            <li class="pageDelete"><?php $this->html('pagedelete');?></li>
                            <li class="pagePrint"><?php $this->html('pageprint');?></li>
                            <?php if (!$wgUser->isAnonymous()) { ?>
                                <li class="pageMore"><a href="#" onclick="return DWMenu.Position('menuPageOptions', this, 0, 0);"><span></span><?php echo(wfMsg('Skin.Common.more')); ?></a></li>
                            <?php } ?>
                        </ul>
                    </td>
                    <td style="text-align:right;width:146px;">
                        <ul>
                            <li class="pageToc"><?php $this->html('pagetoc');?></li>
                        </ul>
                   </td>
                </tr>
            </tbody>
            </table>
        </div>
    
<?php        
    }
    
    function PageMenuControl($key, $languageKey) {
        $pkey = 'page'.$key;
        $href = $this->haveHref($pkey);
        $onclick = 'menuOff(\'menuPageOptions\');'.$this->haveOnClick($pkey);
        $class = $this->haveCSSClass($pkey);
        echo("\t".'<li class="page'.ucfirst($key).' '.$class.'"><a href="'.$href.'"'.($class != '' ? ' class="'.$class.'"': '').' onclick="'.$onclick.'" title="'.wfMsg($languageKey).'"><span></span>'.wfMsg($languageKey).'</a></li>'."\n");
    }

    function UserAuth() {
        global $wgUser;
    ?>
        <div>
             <?php 
                if (!$wgUser->isAnonymous()) { 
                    echo('Logged in as:<br />');
					echo('<a href="'.$this->haveData('userpageurl').'" class="userName">');
					$this->text('username');
					echo('</a> [');
					echo('<a href="'.$this->haveData('logouturl').'" class="userName">'.wfMsg('Page.UserLogout.page-title').'</a>]<br />');
					echo('<a href="/User_Profile">User profile</a>'); // | ');
					//echo('<a href="'.$this->haveData('userpageurl').'">Settings</a>');
                    // Temporary disabled "| Settings" because we don't have settings now. 
				} else { 
                    if ($this->hasData('registerurl'))
                        echo('<a href="'.$this->haveData('registerurl').'" class="userRegister">'.wfMsg('Page.UserRegistration.page-title').'</a> ');
                    echo(' <a href="'.$this->haveData('loginurl').'" class="userLogin">'.wfMsg('Page.UserLogin.page-title').'</a>');
				}
            ?>
        </div>
    <?php    
    }
    
    function PageAccess()
    {
        global $wgUser, $wgArticle;
        
        $Security = new DekiResult($wgArticle->getSecurity());
            
        if ($wgArticle->userCanRestrict())
        {
                // Description:
                // array(
                // 'Public' => wfMsg('Page.PageRestrictions.desc-public'), 
                // 'Semi-Public' => wfMsg('Page.PageRestrictions.desc-semipublic'),
                // 'Private' => wfMsg('Page.PageRestrictions.desc-private')
                // ) 
                echo("<div class=\"page-restrict-access\">\n");
                echo("Page access restriction: "."<span class=\"".$Security->getVal('body/security/permissions.page/restriction/#text', 'Public')."\">".
                    $Security->getVal('body/security/permissions.page/restriction/#text', 'Public')."</span>\n");            
                echo("</div><br/>\n");
        }
    }
}
?>
