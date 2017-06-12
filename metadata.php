<?php

/**
 *	@author:	a4p ASD / Andreas Dorner
 *	@company:	apps4print / page one GmbH, Nürnberg, Germany
 *
 *
 *	@version:	1.1.0
 *	@date:		30.08.2016
 *
 *
 *	metadata.php
 *
 *	apps4print - a4p_admin_cms_files - CMS als Dateien exportieren und importieren
 *
 */

// ------------------------------------------------------------------------------------------------
// apps4print
// ------------------------------------------------------------------------------------------------

$sMetadataVersion	= '1.1';

$aModule = array(
	'id'			=> 'a4p_admin_cms_files', 
	'title'			=> 'apps4print - a4p_admin_cms_files', 
	'description'	=> array(
		'de'									=> 'CMS als Dateien exportieren und importieren', 
		'en'									=> 'export and import cms as files' 
	), 
	'lang'			=> 'de',
	'thumbnail'		=> 'out/img/apps4print/a4p_logo.jpg', 
	'version'		=> '<a4p_VERSION> (1.1.0)', 
	'author'		=> 'apps4print', 
	'url'			=> 'http://www.apps4print.com', 
	'email'			=> 'support@apps4print.com', 
	'extend'	  	=> array(
	), 
	'files'			=> array(
		'a4p_admin_cms_files'					=> 'apps4print/a4p_admin_cms_files/controllers/admin/a4p_admin_cms_files.php', 
		'a4p_admin_cms_files_for_translation'	=> 'apps4print/a4p_admin_cms_files/controllers/admin/a4p_admin_cms_files_for_translation.php', 
		'a4p_admin_cms_files__core'				=> 'apps4print/a4p_admin_cms_files/core/a4p_admin_cms_files__core.php', 
		'a4p_admin_cms_files__translation_excel'	=> 'apps4print/a4p_admin_cms_files/core/a4p_admin_cms_files__translation_excel.php', 
		'a4p_admin_cms_files__tag_parser'		=> 'apps4print/a4p_admin_cms_files/core/a4p_admin_cms_files__tag_parser.php' 
	), 
	'blocks'		=> array(
		array( 'template' => 'headitem.tpl',	'block' => 'admin_headitem_inccss',	'file' => '/views/blocks/a4p_admin_headitem_inccss.tpl' )
	), 
	'settings'		=> array(
		array( 'group' => 'a4p_main',	'name' => 'a4p_admin_cms_files__aCmsBlacklist',		'type' => 'arr',  'value' => array('oxagb','oxstart') ), 
	), 
	'templates'		=> array(
		'a4p_admin_cms_files.tpl'					=> 'apps4print/a4p_admin_cms_files/views/admin/tpl/a4p_admin_cms_files.tpl', 
		'a4p_admin_cms_files_for_translation.tpl'	=> 'apps4print/a4p_admin_cms_files/views/admin/tpl/a4p_admin_cms_files_for_translation.tpl' 
	), 
	'events'		=> array(
	)
);

// ------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------
