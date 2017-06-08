<?php

/**
 * 	@author:	a4p Filip Rut
 * 	@company:	apps4print / page one GmbH, Nürnberg, Germany
 *
 *
 * 	@version:	1.0.0
 * 	@date:		07.06.2017
 *
 *
 * 	a4p_admin_cms_files_for_translation.php
 *
 * 	apps4print - a4p_admin_cms_files_for_translation - CMS als Excel Dateien exportieren und importieren
 *
 */

class a4p_admin_cms_files_for_translation extends oxAdminView {

    protected $o_a4p_debug_log = null;
    protected $_sThisTemplate = "a4p_admin_cms_files_for_translation.tpl";

    public function __construct() {

        parent::__construct();

    }

    public function render() {

        parent::render();

        return $this->_sThisTemplate;

    }

    public function exportCMSforTranslation() {

        /* Include PHPExcel */
        //include_once $_SERVER['DOCUMENT_ROOT'] . '/Classes/PHPExcel.php';

        /* Include PHPExcel as phar */
/*
        $s_modulepath = oxRegistry::get( "oxViewConfig" )->getModulePath( "a4p_admin_cms_files" );
        #include_once 'phar://' . $s_modulepath . '/core/vendor/PHPExcel/PHPExcel_1.8.0.phar/PHPExcel.php';
		include "phar://" . __DIR__ . "/vendor/PHPExcel/PHPExcel_1.8.0.phar/PHPExcel.php";


        if ( !class_exists( "PHPExcel" ) ) {

            oxRegistry::get( "oxUtilsView" )->addErrorToDisplay( "Error: PHPExcel library missing. Please check error log." );

        } else {
*/
            // CMS-Ordner aus Übergabeparameter
            $s_custom_cms_files_dir__rel = oxRegistry::getConfig()->getRequestParameter( "s_cms_files__export_dir__rel" );

            $o_a4p_admin_cms_files__core = oxRegistry::get( "a4p_admin_cms_files__translation_excel" );

            // Set export language
            $o_a4p_admin_cms_files__core->set_export_language();

            // CMS-Ordner setzen
            $o_a4p_admin_cms_files__core->set_cms_files_translate_dir( $s_custom_cms_files_dir__rel );

            // Set export file name
            $o_a4p_admin_cms_files__core->set_xls_export_filename();


            if ( file_exists( $o_a4p_admin_cms_files__core->get_xls_export_file_abs_path() ) ) {

                oxRegistry::get( "oxUtilsView" )->addErrorToDisplay( "FILE EXISTS: '" . $o_a4p_admin_cms_files__core->get_xls_export_file_abs_path() . "' -> break<br>" );

            } else {

                // Export ausführen
                $a_ret = $o_a4p_admin_cms_files__core->export_cms_for_translation();

                $this->addTplParam( "a_ret__export", $a_ret );

            }

 #       }
    }

    public function importCMSTranslation() {

    }

    public function get_cms_files_translate_dir__rel() {

        $o_a4p_admin_cms_files__core = oxRegistry::get( "a4p_admin_cms_files__translation_excel" );

        $s_ret = $o_a4p_admin_cms_files__core->get_cms_files_translate_dir(false);

        $s_ret .= date( "Y-m-d" ) . DIRECTORY_SEPARATOR;

        return $s_ret;
    }

}
