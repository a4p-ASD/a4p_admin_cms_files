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
 *	a4p_admin_cms_files__core.php
 *
 *	apps4print - a4p_admin_cms_files - CMS als Dateien exportieren und importieren
 *
 */

// ------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------

class a4p_admin_cms_files__core {
	
	// ------------------------------------------------------------------------------------------------
	// ------------------------------------------------------------------------------------------------

	
	protected $o_a4p_debug_log					= null;

	
	protected $s_cms_files_dir__abs				= null;
	
	protected $s_cms_files_dir__rel				= "data/cms/";
	
	
	// ------------------------------------------------------------------------------------------------
	// ------------------------------------------------------------------------------------------------
	
	public function __construct() {


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

	public function export_cms() {
		
		
		// ------------------------------------------------------------------------------------------------
		if ( $this->o_a4p_debug_log ) {
		#	$this->o_a4p_debug_log->_log( __CLASS__ . "::export_cms()", "null", __FILE__, __FUNCTION__, __LINE__ );
		}
		
		

		/* @var $o_oxcontent oxContent */
		$o_oxcontent							= oxNew( "oxContent" );
		
		
		// ------------------------------------------------------------------------------------------------
		// alle CMS als oxList
		/* @var $o_oxlist__cms oxList */
		$o_oxlist__cms							= oxNew( "oxList" );
		$o_oxlist__cms->init( $o_oxcontent->getClassName(), $o_oxcontent->getCoreTableName() );
		
		$sSql									= "SELECT * FROM " . $o_oxcontent->getCoreTableName();
		$sSql									.= " WHERE 1";
		$sSql									.= " ORDER BY oxloadid";
		
		$o_oxlist__cms->selectString( $sSql );
		
		
		// ------------------------------------------------------------------------------------------------
		// Ausgabe-Ordner setzen
		$this->set_cms_files_dir( false, true );
		// ------------------------------------------------------------------------------------------------
		
		
		
		$a_oxlist__cms							= $o_oxlist__cms->getArray();
		
		$i_cms__total							= count( $a_oxlist__cms );
		$i_cms__exported						= 0;
		
		// ------------------------------------------------------------------------------------------------
		// alle CMS als Datei exportieren
		foreach( $a_oxlist__cms as $s_oxcontents__oxid => $o_oxcontent ) {
			
			
			$b_ret__export						= $this->_save_cms_as_file( $o_oxcontent );
			
			if ( $b_ret__export )
				$i_cms__exported++;
			
		}
		// ------------------------------------------------------------------------------------------------
		
		
		// ------------------------------------------------------------------------------------------------
		// Rückgabe
		$a_ret									= array();
		$a_ret[ "i_total" ]						= $i_cms__total;
		$a_ret[ "i_exported" ]					= $i_cms__exported;
		$a_ret[ "s_cms_files_dir" ]				= $this->s_cms_files_dir__abs;
		
		return $a_ret;
	}

	// ------------------------------------------------------------------------------------------------

	/**
	 * @param bool|false $b_update_cms
	 */
	public function import_cms( $b_update_cms = false ) {
		

		// ------------------------------------------------------------------------------------------------
		if ( $this->o_a4p_debug_log ) {
		#	$this->o_a4p_debug_log->_log( __CLASS__ . "::import_cms()", "null", __FILE__, __FUNCTION__, __LINE__ );
		}
		
		
		
		// ------------------------------------------------------------------------------------------------
		// Ausgabe-Ordner setzen
		$this->set_cms_files_dir();
		// ------------------------------------------------------------------------------------------------
		
		
		// ------------------------------------------------------------------------------------------------
		// Dateien in Ausgabe-Ordner auslesen
		$a_cms_files							= $this->_read_cms_files_dir();
		// ------------------------------------------------------------------------------------------------
		

		// ------------------------------------------------------------------------------------------------
		// alle gefundenen Dateien prüfen/importieren
		foreach ( $a_cms_files as $i_key => $s_cms_file__abs ) {

			$i_ret                              = $this->_import_cms_from_file( $s_cms_file__abs, $b_update_cms );
		}
		// ------------------------------------------------------------------------------------------------
		
		
		return $i_ret;
	}	
	
	// ------------------------------------------------------------------------------------------------
	
	public function set_cms_files_dir( $s_custom_cms_files_dir__rel = false, $b_create = false, $b_force = false ) {
		
		
		// ------------------------------------------------------------------------------------------------
		if ( $this->o_a4p_debug_log ) {
		#	$this->o_a4p_debug_log->_log( __CLASS__ . "::set_cms_files_dir()", "null", __FILE__, __FUNCTION__, __LINE__ );
		#	$this->o_a4p_debug_log->_log( "\$s_custom_cms_files_dir__rel", $s_custom_cms_files_dir__rel, __FILE__, __FUNCTION__, __LINE__ );
		}
		
		
		
		// ------------------------------------------------------------------------------------------------
		// nur setzen, wenn noch nicht gesetzt wurde
		if ( is_null( $this->s_cms_files_dir__abs ) || $b_force ) {
		
		
			// Pfad abs
			$s_files_dir__abs					= oxRegistry::getConfig()->getConfigParam( "sShopDir" );
			
			
			// rel anhängen
			if ( $s_custom_cms_files_dir__rel )
				$s_files_dir__abs				.= $s_custom_cms_files_dir__rel;
			
			else
				$s_files_dir__abs				.= $this->s_cms_files_dir__rel;
			
			
			$this->s_cms_files_dir__abs			= $s_files_dir__abs;
			
		
			
			// ------------------------------------------------------------------------------------------------
			if ( $this->o_a4p_debug_log ) {
			#	$this->o_a4p_debug_log->_log( "\$this->s_cms_files_dir__abs", $this->s_cms_files_dir__abs, __FILE__, __FUNCTION__, __LINE__ );
			}


		}
		
		
		// ------------------------------------------------------------------------------------------------
		// Ordner anlegen, falls nicht existiert
		if ( !file_exists( $this->s_cms_files_dir__abs ) && $b_create )
			mkdir( $this->s_cms_files_dir__abs, 0777, true );
		// ------------------------------------------------------------------------------------------------
		
		
	}

	// ------------------------------------------------------------------------------------------------

	public function get_cms_files_dir( $b_abs = false ) {
		
		
		if ( is_null( $this->s_cms_files_dir__abs ) )
			$this->set_cms_files_dir();
		
		if ( $b_abs )
			return $this->s_cms_files_dir__abs;
		else
			return $this->s_cms_files_dir__rel;
	}

	// ------------------------------------------------------------------------------------------------

	public function get_cms_folders() {
		
		
		// ------------------------------------------------------------------------------------------------
		// Ausgabe-Ordner setzen
		$this->set_cms_files_dir();
		// ------------------------------------------------------------------------------------------------
				

		$a_cms_folders							= array();
		
		
		$dh										= opendir( $this->get_cms_files_dir( true ) );
		if ( $dh ) {
				
				
			$file								= readdir( $dh );
			while ( $file !== false ) {
		
				$s_cur_file__abs				= $this->s_cms_files_dir__abs . $file;
		
				if ( ( $file !== "." ) && ( $file !== ".." ) && is_dir( $s_cur_file__abs ) ) {
					
										
					#array_push( $a_cms_folders, $s_cur_file__abs );
					#$a_cms_folders[ $file ]		= $s_cur_file__abs;

					// ------------------------------------------------------------------------------------------------
					// als Array zurückgeben
					$a_folder					= array();
					$a_folder[ "name" ]			= $file;
					$a_folder[ "abs" ]			= $s_cur_file__abs;
					$a_folder[ "rel" ]			= $this->s_cms_files_dir__rel . $file;

					// ------------------------------------------------------------------------------------------------
					// Anzahl Dateien im Ordner
					$i_dircontents              = count( scandir( $s_cur_file__abs ) );
					if ( $i_dircontents )
						$i_dircontents          -= 2;
					if ( !$i_dircontents )
						$i_dircontents          = 0;
					$a_folder[ "contents" ]		= $i_dircontents;

					// ------------------------------------------------------------------------------------------------
					// Orderdatum als Array-Key zum sortieren 
					$i_folder_filemtime         = filemtime( $s_cur_file__abs );
					if ( isset( $a_cms_folders[ $i_folder_filemtime ] ) )
						array_push( $a_cms_folders, $a_folder );
					else
						$a_cms_folders[ $i_folder_filemtime ]       = $a_folder;

				}
		
		
				$file							= readdir( $dh );
			}

				
			closedir( $dh );
				
		} else {
			#echo "could not open dir";
			oxRegistry::get( "oxUtilsView" )->addErrorToDisplay( "could not open dir: '" . $this->get_cms_files_dir( true) . "'" );
		}
		
		
		// Sortieren
		ksort( $a_cms_folders );
		
		
		return $a_cms_folders;
	}

	// ------------------------------------------------------------------------------------------------
	
	protected function _save_cms_as_file( &$o_oxcontent ) {
		

		// ------------------------------------------------------------------------------------------------
		if ( $this->o_a4p_debug_log ) {
		#	$this->o_a4p_debug_log->_log( __CLASS__ . "::_save_cms_as_file()", "null", __FILE__, __FUNCTION__, __LINE__ );
		}
		
		
		$b_ret									= false;
		
		
		$s_cms_file__abs						= $this->s_cms_files_dir__abs;
		
		
		$s_cms_file__abs						.= $o_oxcontent->oxcontents__oxloadid->value . ".cms";
		
		
		if ( file_exists( $s_cms_file__abs ) ) {
			
			#echo "FILE EXISTS: '" . $s_cms_file__abs . "' -> break<br>";
			oxRegistry::get( "oxUtilsView" )->addErrorToDisplay( "FILE EXISTS: '" . $s_cms_file__abs . "' -> break<br>" );

		} else {
			
			$b_ret__save						= file_put_contents( $s_cms_file__abs, $o_oxcontent->oxcontents__oxcontent->value );
			
			if ( $b_ret__save !== false )
				$b_ret							= true;

		}
		
		return $b_ret;
	}
	
	// ------------------------------------------------------------------------------------------------
	
	protected function _read_cms_files_dir() {
		
		
		// ------------------------------------------------------------------------------------------------
		if ( $this->o_a4p_debug_log ) {
		#	$this->o_a4p_debug_log->_log( __CLASS__ . "::_read_cms_files_dir()", "null", __FILE__, __FUNCTION__, __LINE__ );
		}

		
		// ------------------------------------------------------------------------------------------------
		if ( $this->o_a4p_debug_log ) {
		#	$this->o_a4p_debug_log->_log( "opendir \$this->s_cms_files_dir__abs", $this->s_cms_files_dir__abs, __FILE__, __FUNCTION__, __LINE__ );
		}
		
		
		$a_cms_files							= array();
		
		
		$dh										= opendir( $this->s_cms_files_dir__abs );
		if ( $dh ) {
			
			
			$file								= readdir( $dh );

			while ( $file !== false ) {
				
				$s_cur_file__abs				= $this->s_cms_files_dir__abs . DIRECTORY_SEPARATOR . $file;

				// ------------------------------------------------------------------------------------------------
				if ( $this->o_a4p_debug_log ) {
				#	$this->o_a4p_debug_log->_log( "\$file", $file, __FILE__, __FUNCTION__, __LINE__ );
				#	$this->o_a4p_debug_log->_log( "is_file( \$s_cur_file__abs )", is_file( $s_cur_file__abs ), __FILE__, __FUNCTION__, __LINE__ );
				}

				if ( ( $file !== "." ) && ( $file !== ".." ) && is_file( $s_cur_file__abs ) ) {
					
					
					$a_pathinfo					= pathinfo( $s_cur_file__abs );
					
					if ( $a_pathinfo[ "extension" ] === "cms" );
						array_push( $a_cms_files, $s_cur_file__abs );
					
				}
				
				
				
				$file							= readdir( $dh );

				// ------------------------------------------------------------------------------------------------
				if ( $this->o_a4p_debug_log ) {
				#	$this->o_a4p_debug_log->_log( "\$file", $file, __FILE__, __FUNCTION__, __LINE__ );
				}

			}
			
			
			
			closedir( $dh );
			
		} else {
			oxRegistry::get( "oxUtilsView" )->addErrorToDisplay( "could not open dir: '" . $this->get_cms_files_dir( true) . "'" );
		}
		

		// ------------------------------------------------------------------------------------------------
		if ( $this->o_a4p_debug_log ) {
		#	$this->o_a4p_debug_log->_log( "\$a_cms_files", $a_cms_files, __FILE__, __FUNCTION__, __LINE__ );
		}
		
		
		return $a_cms_files;
	}
	
	// ------------------------------------------------------------------------------------------------
	
	protected function _import_cms_from_file( $s_cms_file__abs, $b_update_cms = false ) {
		
		
		// ------------------------------------------------------------------------------------------------
		if ( $this->o_a4p_debug_log ) {
		#	$this->o_a4p_debug_log->_log( __CLASS__ . "::_import_cms_from_file()", "null", __FILE__, __FUNCTION__, __LINE__ );
		#	$this->o_a4p_debug_log->_log( "\$s_cms_file__abs", $s_cms_file__abs, __FILE__, __FUNCTION__, __LINE__ );
		}


		$i_ret                                  = 0;

			
		$a_pathinfo								= pathinfo( $s_cms_file__abs );


		// ------------------------------------------------------------------------------------------------
		// CMS-Ident ist Dateiname
		$s_cms_ident							= $a_pathinfo[ "filename" ];
		
		// ------------------------------------------------------------------------------------------------
		if ( $this->o_a4p_debug_log ) {
		#	$this->o_a4p_debug_log->_log( "\$s_cms_ident", $s_cms_ident, __FILE__, __FUNCTION__, __LINE__ );
		}


		// ------------------------------------------------------------------------------------------------
		// prüfen ob es ein CMS mit dem ident des Dateinamen gibt
		
		/* @var o_oxContent oxContent */
		$o_oxContent							= oxNew( "oxContent" );
		
		$b_ret__load							= $o_oxContent->loadByIdent( $s_cms_ident );
		
		if ( $b_ret__load ) {


			// ------------------------------------------------------------------------------------------------
			// Datei auslesen
			$s_file_content				    	= file_get_contents( $s_cms_file__abs );

			// ------------------------------------------------------------------------------------------------
			if ( $this->o_a4p_debug_log ) {
			#	$this->o_a4p_debug_log->_log( "\$o_oxContent->oxcontents__oxcontent->value", $o_oxContent->oxcontents__oxcontent->value, __FILE__, __FUNCTION__, __LINE__ );
			#	$this->o_a4p_debug_log->_log( "\$s_file_content", $s_file_content, __FILE__, __FUNCTION__, __LINE__ );
			#	$this->o_a4p_debug_log->_log( "utf8_encode \$s_file_content", utf8_encode( $s_file_content ), __FILE__, __FUNCTION__, __LINE__ );
			#	$this->o_a4p_debug_log->_log( "utf8_decode \$s_file_content", utf8_decode( $s_file_content ), __FILE__, __FUNCTION__, __LINE__ );
			}

				
			// ------------------------------------------------------------------------------------------------
			// Update ausführen
			if ( $b_update_cms ) {

				
				// ------------------------------------------------------------------------------------------------
				// oxContent aktualisieren
				if ( $o_oxContent->oxcontents__oxcontent->value !== $s_file_content ) {


					#$s_file_content				= utf8_encode( $s_file_content );
					#$s_file_content				= utf8_decode( $s_file_content );

					// dsc-demo: kein utf8_*
					// drucksachencloud: kein utf8_*


					$a_update					= array();
					$a_update[ "oxcontent" ]	= $s_file_content;

					$o_oxContent->assign( $a_update );

					$o_oxContent->save();


					$i_ret                      = 2;

					oxRegistry::get( "oxUtilsView" )->addErrorToDisplay( "CMS '" . $s_cms_ident . "' updated" );

				}

			} else {

				// ------------------------------------------------------------------------------------------------
				// oxContent aktualisieren
				if ( $o_oxContent->oxcontents__oxcontent->value !== $s_file_content ) {


					// ------------------------------------------------------------------------------------------------
					if ( $this->o_a4p_debug_log ) {
					#	$this->o_a4p_debug_log->_log( "\$o_oxContent->oxcontents__oxcontent->value", $o_oxContent->oxcontents__oxcontent->value, __FILE__, __FUNCTION__, __LINE__ );
					#	$this->o_a4p_debug_log->_log( "\$s_file_content", $s_file_content, __FILE__, __FUNCTION__, __LINE__ );
					}

					oxRegistry::get( "oxUtilsView" )->addErrorToDisplay( "would update CMS '" . $s_cms_ident . "' from file '" . $s_cms_file__abs . "'" );
				}


				$i_ret                          = 1;
			}


		}
		
		return $i_ret;
	}
	
	// ------------------------------------------------------------------------------------------------
	
}

// ------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------
