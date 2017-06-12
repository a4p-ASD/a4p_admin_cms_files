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
 *	a4p_admin_cms_files__translation_excel.php
 *
 *	apps4print - a4p_admin_cms_files - CMS als Dateien exportieren und importieren
 *
 */

// ------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------

class a4p_admin_cms_files__translation_excel {

	// ------------------------------------------------------------------------------------------------
	// ------------------------------------------------------------------------------------------------


	protected $o_a4p_debug_log					= null;


	protected $s_cms_files_dir__abs				= null;

	protected $s_cms_files_dir__rel				= "data/cms/";



	protected $s_cms_files_translate_dir__rel = "data/translations/cms/";

	protected $i_export_language_id = null;

	protected $s_export_language_abbr = null;

	protected $objPHPExcel = null;

	protected $s_xls_export_file__abs = null;


	// ------------------------------------------------------------------------------------------------
	// ------------------------------------------------------------------------------------------------

	public function __construct() {


		ini_set( "max_execution_time", 0 );


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

	public function export_cms_for_translation() {


		/* @var $o_oxcontent oxContent */
		$o_oxcontent							= oxNew( "oxContent" );


		// ------------------------------------------------------------------------------------------------
		// CMS-Blacklist aus Modul-Setting
		$a_cms_blacklist						= oxRegistry::getConfig()->getConfigParam( "a4p_admin_cms_files__aCmsBlacklist" );
		// ------------------------------------------------------------------------------------------------



		// ------------------------------------------------------------------------------------------------
		// alle CMS als oxList
		/* @var $o_oxlist__cms oxList */
		$o_oxlist__cms							= oxNew( "oxList" );
		$o_oxlist__cms->init( $o_oxcontent->getClassName(), $o_oxcontent->getCoreTableName() );

		$sSql									= "SELECT * FROM " . $o_oxcontent->getCoreTableName();
		$sSql									.= " WHERE 1";
		$sSql									.= " AND oxactive = 1";
		
		if ( count( $a_cms_blacklist ) ) {
		
			$sSql								.= " AND NOT oxloadid IN ( '" . implode( "', '", $a_cms_blacklist ) . "' )";
		}

		$sSql									.= " ORDER BY oxloadid";

		$o_oxlist__cms->selectString( $sSql );


		// ------------------------------------------------------------------------------------------------
		// Ausgabe-Ordner setzen
		$this->set_cms_files_translate_dir( false, true );
		// ------------------------------------------------------------------------------------------------



		$a_oxlist__cms							= $o_oxlist__cms->getArray();

		$i_cms__total							= count( $a_oxlist__cms );
		$i_cms__exported						= 0;

		// ------------------------------------------------------------------------------------------------
		// alle CMS als Datei exportieren
		foreach( $a_oxlist__cms as $s_oxcontents__oxid => $o_oxcontent ) {


			if( $i_cms__exported == 0 ) {

				$this->_init_xls_export();
				//$this->set_xls_export_filename();

			}

			$b_ret__export = $this->_save_cms_for_translation( $o_oxcontent, $i_cms__exported+2 );

			if( $i_cms__exported + 1 == $i_cms__total ) {

				$this->_finalize_xls_export();

			}


			if ( $b_ret__export ) {
				$i_cms__exported++;
			}

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
	public function import_cms( $b_update_cms = false, $b_skip_update_inactive_cms = false, $b_create_cms = false ) {


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

			$i_ret								= $this->_import_cms_from_file( $s_cms_file__abs, $b_update_cms, $b_skip_update_inactive_cms, $b_create_cms );
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

	public function set_cms_files_translate_dir( $s_custom_cms_files_dir__rel = false, $b_create = false, $b_force = false ) {


		// ------------------------------------------------------------------------------------------------
		if ( $this->o_a4p_debug_log ) {
		#	$this->o_a4p_debug_log->_log( __CLASS__ . "::set_cms_files_dir()", "null", __FILE__, __FUNCTION__, __LINE__ );
		#	$this->o_a4p_debug_log->_log( "\$s_custom_cms_files_dir__rel", $s_custom_cms_files_dir__rel, __FILE__, __FUNCTION__, __LINE__ );
		}



		// ------------------------------------------------------------------------------------------------
		// nur setzen, wenn noch nicht gesetzt wurde
		if ( is_null( $this->s_cms_files_dir__abs ) || $b_force ) {


			// Pfad abs
			$s_files_dir__abs = oxRegistry::getConfig()->getConfigParam( "sShopDir" );


			// rel anhängen
			if ( $s_custom_cms_files_dir__rel ) {
				$s_files_dir__abs .= $s_custom_cms_files_dir__rel;
						}
			else {
				$s_files_dir__abs .= $this->s_cms_files_translate_dir__rel;

								// add lang abbr
								//$s_files_dir__abs .= $this->s_export_language_abbr . DIRECTORY_SEPARATOR;

								// add date
								$s_files_dir__abs .= date( "Y-m-d" ) . DIRECTORY_SEPARATOR;
						}

						if( substr( $s_files_dir__abs, -1) != "/" && substr( $s_files_dir__abs, -1) != "\\" ) {
								$s_files_dir__abs .= DIRECTORY_SEPARATOR;
						}


			$this->s_cms_files_dir__abs = $s_files_dir__abs;



			// ------------------------------------------------------------------------------------------------
			if ( $this->o_a4p_debug_log ) {
			#	$this->o_a4p_debug_log->_log( "\$this->s_cms_files_dir__abs", $this->s_cms_files_dir__abs, __FILE__, __FUNCTION__, __LINE__ );
			}


		}


		// ------------------------------------------------------------------------------------------------
		// Ordner anlegen, falls nicht existiert
		if ( !file_exists( $this->s_cms_files_dir__abs ) && $b_create ) {
			mkdir( $this->s_cms_files_dir__abs, 0777, true );
				}
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

	public function get_cms_files_translate_dir( $b_abs = false ) {


		if ( is_null( $this->s_cms_files_dir__abs ) )
			$this->set_cms_files_translate_dir();

		if ( $b_abs )
			return $this->s_cms_files_dir__abs;
		else
			return $this->s_cms_files_translate_dir__rel;
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
					$i_dircontents				= count( scandir( $s_cur_file__abs ) );
					if ( $i_dircontents )
						$i_dircontents			-= 2;
					if ( !$i_dircontents )
						$i_dircontents			= 0;
					$a_folder[ "contents" ]		= $i_dircontents;

					// ------------------------------------------------------------------------------------------------
					// Orderdatum als Array-Key zum sortieren
					$i_folder_filemtime		 = filemtime( $s_cur_file__abs );
					if ( isset( $a_cms_folders[ $i_folder_filemtime ] ) )
						array_push( $a_cms_folders, $a_folder );
					else
						$a_cms_folders[ $i_folder_filemtime ]		= $a_folder;

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

	protected function _import_cms_from_file( $s_cms_file__abs, $b_update_cms = false, $b_skip_update_inactive_cms = false, $b_create_cms = false ) {


		// ------------------------------------------------------------------------------------------------
		if ( $this->o_a4p_debug_log ) {
		#	$this->o_a4p_debug_log->_log( __CLASS__ . "::_import_cms_from_file()", "null", __FILE__, __FUNCTION__, __LINE__ );
		#	$this->o_a4p_debug_log->_log( "\$s_cms_file__abs", $s_cms_file__abs, __FILE__, __FUNCTION__, __LINE__ );
		}


		$i_ret									= 0;


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
			$s_file_content						= file_get_contents( $s_cms_file__abs );

			// ------------------------------------------------------------------------------------------------
			if ( $this->o_a4p_debug_log ) {
			#	$this->o_a4p_debug_log->_log( "\$o_oxContent->oxcontents__oxcontent->value", $o_oxContent->oxcontents__oxcontent->value, __FILE__, __FUNCTION__, __LINE__ );
			#	$this->o_a4p_debug_log->_log( "\$s_file_content", $s_file_content, __FILE__, __FUNCTION__, __LINE__ );
			#	$this->o_a4p_debug_log->_log( "utf8_encode \$s_file_content", utf8_encode( $s_file_content ), __FILE__, __FUNCTION__, __LINE__ );
			#	$this->o_a4p_debug_log->_log( "utf8_decode \$s_file_content", utf8_decode( $s_file_content ), __FILE__, __FUNCTION__, __LINE__ );
			}


			// ------------------------------------------------------------------------------------------------
			// inaktive ggf. auslassen
			if ( $b_skip_update_inactive_cms && ( $o_oxContent->oxcontents__oxactive->value !== "1" ) )
				$b_update_cms					= false;


			// ------------------------------------------------------------------------------------------------
			// Update ausführen
			if ( $b_update_cms ) {


				// ------------------------------------------------------------------------------------------------
				// oxContent aktualisieren
				if ( $o_oxContent->oxcontents__oxcontent->value !== $s_file_content ) {


					// ------------------------------------------------------------------------------------------------
					// Umlaute in ANSI-Files: Text ab erstem Umlaut abgeschnitten! (encoding: ASCII / false)

					$s_file_content__encoding	= mb_detect_encoding( $s_file_content );
					if ( $s_file_content__encoding !== "UTF-8" )
						$s_file_content			= mb_convert_encoding( $s_file_content, "UTF-8" );


					$s_content_backup			= $o_oxContent->oxcontents__oxcontent->value;


					// ------------------------------------------------------------------------------------------------
					// Update
					$a_update					= array();
					$a_update[ "oxcontent" ]	= $s_file_content;
					$o_oxContent->assign( $a_update );
					$o_oxContent->save();


					// ------------------------------------------------------------------------------------------------
					// check und ggf. restore
					$o_oxContent				= null;
					$o_oxContent				= oxNew( "oxContent" );
					$o_oxContent->loadByIdent( $s_cms_ident );
					$s_oxcontent__updated		= $o_oxContent->oxcontents__oxcontent->value;

					if ( $s_oxcontent__updated !== $s_file_content ) {

						$a_update					= array();
						$a_update[ "oxcontent" ]	= $s_content_backup;
						$o_oxContent->assign( $a_update );
						$o_oxContent->save();


						oxRegistry::get( "oxUtilsView" )->addErrorToDisplay( "--- CMS '" . $s_cms_ident . "' update failed -> restored!" );

					} else {

						$i_ret					= 2;

						oxRegistry::get( "oxUtilsView" )->addErrorToDisplay( "CMS '" . $s_cms_ident . "' updated" );
					}


				}

			} else {

				// ------------------------------------------------------------------------------------------------
				// nur Ausgabe
				if ( $o_oxContent->oxcontents__oxcontent->value !== $s_file_content ) {


					// ------------------------------------------------------------------------------------------------
					// inaktive ggf. auslassen
					if ( ! ( $b_skip_update_inactive_cms && ( $o_oxContent->oxcontents__oxactive->value !== "1" ) ) ) {

						oxRegistry::get( "oxUtilsView" )->addErrorToDisplay( "would update CMS '" . $s_cms_ident . "' from file '" . $s_cms_file__abs . "'" );

					}

				}


				$i_ret							= 1;
			}


		} else if ( $b_create_cms ) {


			// ------------------------------------------------------------------------------------------------
			// Update ausführen
			if ( $b_update_cms ) {


				// ------------------------------------------------------------------------------------------------
				// Datei auslesen
				$s_file_content					= file_get_contents( $s_cms_file__abs );


				// ------------------------------------------------------------------------------------------------
				// Umlaute in ANSI-Files: Text ab erstem Umlaut abgeschnitten! (encoding: ASCII / false)

				$s_file_content__encoding		= mb_detect_encoding( $s_file_content );
				if ( $s_file_content__encoding !== "UTF-8" )
					$s_file_content				= mb_convert_encoding( $s_file_content, "UTF-8" );


				// ------------------------------------------------------------------------------------------------
				// Update
				$a_update						= array();
				$a_update[ "oxloadid" ]			= $s_cms_ident;
				$a_update[ "oxtitle" ]			= $s_cms_ident;
				$a_update[ "oxcontent" ]		= $s_file_content;
				$o_oxContent->assign( $a_update );
				$o_oxContent->save();

				oxRegistry::get( "oxUtilsView" )->addErrorToDisplay( "CMS '" . $s_cms_ident . "' created" );

			} else {

				// ------------------------------------------------------------------------------------------------
				// nur Ausgabe
				oxRegistry::get( "oxUtilsView" )->addErrorToDisplay( "would create CMS '" . $s_cms_ident . "' from file '" . $s_cms_file__abs . "'" );

			}


		}


		return $i_ret;
	}

	// ------------------------------------------------------------------------------------------------

	protected function _save_cms_for_translation( &$o_oxcontent, $row_nr ) {

		// ------------------------------------------------------------------------------------------------
		if ( $this->o_a4p_debug_log ) {
		#	$this->o_a4p_debug_log->_log( __CLASS__ . "::_save_cms_as_file()", "null", __FILE__, __FUNCTION__, __LINE__ );
		}

		$b_ret = false;
		$s_cms_ident = $o_oxcontent->oxcontents__oxloadid->value;

		$b_ret__save = $this->_save_cms_to_xls( $s_cms_ident, $o_oxcontent, $row_nr );

		if ( $b_ret__save !== false ) {
			$b_ret = true;
		}

		return $b_ret;
	}

	// ------------------------------------------------------------------------------------------------

	protected function _save_cms_to_xls( &$s_cms_ident, &$o_oxcontent, &$row_nr ) {

		// Inhalt in Deutsch
		$o_cms_data_1 = $this->_getCmsContentForLang( $s_cms_ident, 0 );
		$o_cms_content_1_parsed = $this->_filterTags( $o_cms_data_1->content );

		// Inhalt in Zielsprache
		$o_cms_data_2 = $this->_getCmsContentForLang( $s_cms_ident, $this->i_export_language_id );
		$o_cms_content_2_parsed = $this->_filterTags( $o_cms_data_2->content );


		$this->objPHPExcel->getActiveSheet()
					->setCellValueByColumnAndRow( 0, $row_nr, $o_oxcontent->oxcontents__oxloadid->value )
					//->setCellValueByColumnAndRow( 1, $row_nr, $o_oxcontent->oxcontents__oxtitle->value )
					->setCellValueByColumnAndRow( 1, $row_nr, $o_cms_data_1->title )
					//->setCellValueByColumnAndRow( 2, $row_nr, $o_oxcontent->{oxcontents__oxtitle_.$this->i_export_language_id}->value )
					->setCellValueByColumnAndRow( 2, $row_nr, $o_cms_data_2->title )
					->setCellValueByColumnAndRow( 3, $row_nr, $o_cms_content_1_parsed->parsedText )
					->setCellValueByColumnAndRow( 4, $row_nr, $o_cms_content_2_parsed->parsedText );

	}

	// ------------------------------------------------------------------------------------------------

	protected function _init_xls_export() {

		include "phar://" . __DIR__ . "/vendor/PHPExcel/PHPExcel_1.8.0.phar/PHPExcel.php";

		$this->objPHPExcel = new PHPExcel();

		$this->_xls_insert_header_row( $this->objPHPExcel );

	}

	// ------------------------------------------------------------------------------------------------

	protected function _finalize_xls_export() {

		/**
		// activate sheet protection
		$this->objPHPExcel->getActiveSheet()->getProtection()->setSheet(true);
		$this->objPHPExcel->getActiveSheet()->getProtection()->setFormatCells(true);
		$this->objPHPExcel->getActiveSheet()->getProtection()->setFormatColumns(true);
		$this->objPHPExcel->getActiveSheet()->getProtection()->setInsertRows(true);

		// get highest data row
		$s_highestdatarow = $this->objPHPExcel->getActiveSheet()->getHighestDataRow();

		// lock
		$this->objPHPExcel->getActiveSheet()
				->getStyle("C1:C$s_highestdatarow")
				->getProtection()
				->setLocked( PHPExcel_Style_Protection::PROTECTION_UNPROTECTED );
		 **/

		/* Set style defaults */
		$this->objPHPExcel->getDefaultStyle()->getFont()
				->setName('Arial')
				->setSize(9);

		// save xlsx file
		$this->_xls_save_file( $this->s_cms_files_dir__abs . "/" . $this->s_xls_export_file__abs, $this->objPHPExcel );

	}


	// ------------------------------------------------------------------------------------------------

	protected function _xls_insert_header_row() {

/*
		$this->objPHPExcel->getActiveSheet()
					->setCellValue( 'A1', 'Id' )
					->setCellValue( 'B1', 'Title DE' )
					->setCellValue( 'C1', 'Title ' . strtoupper( $this->s_export_language_abbr ) )
					->setCellValue( 'D1', 'Content DE' )
					->setCellValue( 'E1', 'Content ' . strtoupper( $this->s_export_language_abbr ) );
*/

		$this->objPHPExcel->getActiveSheet()
					->setCellValue( 'A1', 'Id' )
					->setCellValue( 'B1', 'Title de' )
					->setCellValue( 'C1', 'Title ' . $this->s_export_language_abbr )
					->setCellValue( 'D1', 'Content de' )
					->setCellValue( 'E1', 'Content ' . $this->s_export_language_abbr );

	}

	// ------------------------------------------------------------------------------------------------

	protected function _xls_save_file( $s_xls_file__abs ) {

		$objWriter = PHPExcel_IOFactory::createWriter( $this->objPHPExcel, 'Excel2007' );

		$objWriter->save( $s_xls_file__abs );

	}

	// ------------------------------------------------------------------------------------------------

	public function set_export_language() {

		if( $this->i_export_language_id === null ) {

			$this->i_export_language_id = intval( oxRegistry::getConfig()->getRequestParameter( "exportforlanguage" ) );

		}

		if( $this->s_export_language_abbr === null ) {

			$this->s_export_language_abbr = oxRegistry::getLang()->getLanguageAbbr( $this->i_export_language_id );

		}

	}

	// ------------------------------------------------------------------------------------------------

	public function set_xls_export_filename() {

		if( $this->s_export_language_abbr !== null ) {

			$this->s_xls_export_file__abs = "translate_cms_files__de-" . $this->s_export_language_abbr . ".xlsx";

		}

	}


	// ------------------------------------------------------------------------------------------------

	public function get_xls_export_file_abs_path() {

		return $this->s_cms_files_dir__abs . "/" . $this->s_xls_export_file__abs;

	}


	// ------------------------------------------------------------------------------------------------

	protected function _getCmsContentForLang( &$s_cms_ident, $i_lang ) {

			$o_oxcontent = oxNew( "oxContent" );
			$o_oxcontent->setLanguage( $i_lang );
			$o_oxcontent->loadByIdent( $s_cms_ident );

			$o_ret = new stdClass();
			$o_ret->content = $o_oxcontent->getFieldData( "oxcontent" );
			$o_ret->title = $o_oxcontent->getFieldData( "oxtitle" );

			return $o_ret;

	}

	// ------------------------------------------------------------------------------------------------

	protected function _filterTags( $s_content ) {

		return oxRegistry::get( "a4p_admin_cms_files__tag_parser" )->filterTags( $s_content );

	}

	// ------------------------------------------------------------------------------------------------

}

// ------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------
