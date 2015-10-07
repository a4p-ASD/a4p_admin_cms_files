

[{*
 *	@author:	a4p ASD / Andreas Dorner
 *	@company:	apps4print / page one GmbH, Nürnberg, Germany
 *
 *
 *	@version:	1.0.1
 *	@date:		07.10.2015
 *
 *
 *	a4p_admin_cms_files.tpl
 *
 *	apps4print - a4p_admin_cms_files - CMS als Dateien exportieren und importieren
 *
 *}]

[{*---------------------------------------------------------------------------------------------*}]
[{*		apps4print																				*}]
[{*---------------------------------------------------------------------------------------------*}]


	[{include file="headitem.tpl" title="GENERAL_ADMIN_TITLE"|oxmultilangassign}]


	<h1 class="pageHead center">CMS-Seiten als Datei im-/exportieren</h1>


	[{*---------------------------------------------------------------------------------------------*}]
	[{*		Export																					*}]
	[{*---------------------------------------------------------------------------------------------*}]

	[{block name="a4p_admin_cms_files__form_export" }]

		<h1 class="pageHead">Export</h1>

		<form name="myedit" id="myedit" action="[{ $oViewConf->getSelfLink() }]" method="post">
			[{ $oViewConf->getHiddenSid() }]
			<input type="hidden" name="cl" value="[{ $oView->getClassName() }]">
			<input type="hidden" name="fnc" value="exportCMS">

			[{*
			<input type="hidden" name="oxid" value="-1">
			*}]
			export to: <input type="text" id="s_cms_files__export_dir__rel" name="s_cms_files__export_dir__rel" value="[{ $oView->get_cms_files_dir__rel() }]" size="30">


			<button type="submit">export</button>

		</form>

	[{/block}]




	[{*---------------------------------------------------------------------------------------------*}]
	[{*		Export  -   Status-Ausgabe																*}]
	[{*---------------------------------------------------------------------------------------------*}]

	[{block name="a4p_admin_cms_files__status_export" }]

		<hr>

		[{if isset( $a_ret__export.i_total ) && isset( $a_ret__export.i_exported ) }]

			[{ $a_ret__export.i_exported }] von [{ $a_ret__export.i_total }] exportiert nach<br>
			[{ $a_ret__export.s_cms_files_dir }]

		[{/if}]

	[{/block}]



	<hr>



	[{*---------------------------------------------------------------------------------------------*}]
	[{*		Import																					*}]
	[{*---------------------------------------------------------------------------------------------*}]

	[{block name="a4p_admin_cms_files__form_import" }]

		<h1 class="pageHead">Import</h1>

		<form name="a4p_admin_cms_files__form_export" id="a4p_admin_cms_files__form_export" action="[{ $oViewConf->getSelfLink() }]" method="post" target="basefrm">
        	[{ $oViewConf->getHiddenSid() }]
			<input type="hidden" name="cl" value="a4p_admin_cms_files">
			<input type="hidden" name="fnc" value="importCMS">

			import from: <input type="text" id="s_cms_files__import_dir__rel" name="s_cms_files__import_dir__rel" value="[{ $oView->get_cms_files_dir__rel() }]" size="30">

			[{assign var="sDate__curDay" value=$smarty.now|date_format:"%Y-%m-%d" }]

			<br>
			[{assign var="a_cms_folders" value=$oView->get_cms_folders() }]
			[{foreach from=$a_cms_folders key=i_key item=a_folder }]
			
				<input type="radio" name="s_cms_files__import_dir__rel" id="a_folder__[{ $i_key }]" value="[{ $a_folder.rel }]"[{if $s_selected_folder == $a_folder.rel }] checked="checked"[{/if}]>
				<label for="a_folder__[{ $i_key }]"[{if $a_folder.name|stristr:$sDate__curDay }] class="cur_day"[{/if}]>[{ $a_folder.name }]</label> ([{ $a_folder.contents}])<br>
				
			[{/foreach}]

			<hr>
			[{if isset( $s_selected_folder ) }]
				<input type="checkbox" name="b_update_cms" id="b_update_cms" value="1" checked="checked"><label for="b_update_cms">gefundene CMS aktualisieren</label>
			[{/if}]


		
			<button type="submit">import</button>
		
		</form>

	[{/block}]


	[{*---------------------------------------------------------------------------------------------*}]
	[{*		Import  -   Status-Ausgabe																*}]
	[{*---------------------------------------------------------------------------------------------*}]

	[{*
	[{block name="a4p_admin_cms_files__status_import" }]

		<hr>

		[{if isset( $a_ret__import ) }]

			[{ $a_ret__import }]

		[{/if}]

	[{/block}]
	*}]



	[{include file="bottomnaviitem.tpl"}]

	[{include file="bottomitem.tpl"}]


[{*---------------------------------------------------------------------------------------------*}]
[{*---------------------------------------------------------------------------------------------*}]
[{*---------------------------------------------------------------------------------------------*}]
