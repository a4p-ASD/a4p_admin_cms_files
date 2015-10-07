<?php

/**
 *	@author:	a4p ASD / Andreas Dorner
 *	@company:	apps4print / page one GmbH, Nürnberg, Germany
 *
 *
 *	@version:	1.0.1
 *	@date:		07.10.2015
 *
 *
 *	a4p_admin_cms_files.php
 *
 *	apps4print - a4p_admin_cms_files - CMS als Dateien exportieren und importieren
 *
 */

// ------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------

class a4p_admin_cms_files extends oxAdminView {
	
	// ------------------------------------------------------------------------------------------------
	// ------------------------------------------------------------------------------------------------
	

	protected $o_a4p_debug_log					= null;
	
	
	protected $_sThisTemplate					= "a4p_admin_cms_files.tpl";
	
	
	// ------------------------------------------------------------------------------------------------
	// ------------------------------------------------------------------------------------------------
	
	public function __construct() {


		// ------------------------------------------------------------------------------------------------
		// oxAdminView hat __construct()
		parent::__construct();


		// ------------------------------------------------------------------------------------------------
		// init a4p_debug_log
		//
		/*
		if ( oxRegistry::get( "oxViewConfig" )->isModuleActive( "a4p_debug_log" ) ) {

			$this->o_a4p_debug_log				= oxNew( "a4p_debug_log" );
			$this->o_a4p_debug_log->a4p_debug_log_init( true, __CLASS__ . ".txt", null );
		}
		//*/
		// ------------------------------------------------------------------------------------------------

		
	}
	
	// ------------------------------------------------------------------------------------------------
	
	public function render() {
		
		
		parent::render();


		return $this->_sThisTemplate;
	}

	// ------------------------------------------------------------------------------------------------
	
	public function exportCMS() {
		

		// ------------------------------------------------------------------------------------------------
		// CMS-Ordner aus Übergabeparameter
		$s_custom_cms_files_dir__rel			= oxRegistry::getConfig()->getRequestParameter( "s_cms_files__export_dir__rel" );
		
		
		
		$o_a4p_admin_cms_files__core			= oxRegistry::get( "a4p_admin_cms_files__core" );
		

		// CMS-Ordner setzen
		$o_a4p_admin_cms_files__core->set_cms_files_dir( $s_custom_cms_files_dir__rel );
		
		
		// Export ausführen
		$a_ret									= $o_a4p_admin_cms_files__core->export_cms();
		
		
		$this->addTplParam( "a_ret__export", $a_ret );
		

		// kein return
		#return $a_ret;

	}
	
	// ------------------------------------------------------------------------------------------------
	
	public function importCMS() {
		
		
		// ------------------------------------------------------------------------------------------------
		// CMS-Ordner aus Übergabeparameter
		$s_custom_cms_files_dir__rel			= oxRegistry::getConfig()->getRequestParameter( "s_cms_files__import_dir__rel" );

		$b_update_cms               			= oxRegistry::getConfig()->getRequestParameter( "b_update_cms" );


		$o_a4p_admin_cms_files__core			= oxRegistry::get( "a4p_admin_cms_files__core" );
		

		// CMS-Ordner setzen
		$o_a4p_admin_cms_files__core->set_cms_files_dir( $s_custom_cms_files_dir__rel );
		
		
		// Import ausführen
		$a_ret									= $o_a4p_admin_cms_files__core->import_cms( $b_update_cms );
		
		
		$this->addTplParam( "a_ret__import", $a_ret );

		if ( $s_custom_cms_files_dir__rel )
			$this->addTplParam( "s_selected_folder", $s_custom_cms_files_dir__rel );


		// kein return
		#return $a_ret;
	}
	
	// ------------------------------------------------------------------------------------------------
	
	public function get_cms_files_dir__rel() {
		
		
		$o_a4p_admin_cms_files__core			= oxRegistry::get( "a4p_admin_cms_files__core" );
		
		$s_ret									= $o_a4p_admin_cms_files__core->get_cms_files_dir( false );

		
		// ------------------------------------------------------------------------------------------------
		// Unterordner anhängen
		$s_ret									.= date( "Y-m-d_H-i" ) . DIRECTORY_SEPARATOR;
		

		return $s_ret;
	}
	
	// ------------------------------------------------------------------------------------------------
	
	public function get_cms_folders() {

		
		$o_a4p_admin_cms_files__core			= oxRegistry::get( "a4p_admin_cms_files__core" );

		// Pfad zurücksetzen
		$o_a4p_admin_cms_files__core->set_cms_files_dir( false, false, true );

		$a_ret									= $o_a4p_admin_cms_files__core->get_cms_folders();
				
		
		return $a_ret;
	}
	
	// ------------------------------------------------------------------------------------------------
	
}

// ------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------
