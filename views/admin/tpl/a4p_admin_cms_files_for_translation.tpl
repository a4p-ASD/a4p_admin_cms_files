[{*
 *	@author:	a4p Filip Rut
 *	@company:	apps4print / page one GmbH, Nürnberg, Germany
 *
 *
 *	@version:	1.0.0
 *	@date:		07.06.2017
 *
 *
 *	a4p_admin_cms_files_for_translation.tpl
 *
 *	apps4print - a4p_admin_cms_files_for_translation - CMS als Excel Dateien exportieren und importieren
 *
 *}]

[{*---------------------------------------------------------------------------------------------*}]
[{*		apps4print																				*}]
[{*---------------------------------------------------------------------------------------------*}]

	[{include file="headitem.tpl" title="GENERAL_ADMIN_TITLE"|oxmultilangassign}]

	<h1 class="pageHead center">CMS-Seiten für Übersetzung als Excel-Datei im-/exportieren</h1>


	[{*---------------------------------------------------------------------------------------------*}]
	[{*		Export für Übersetzung																	*}]
	[{*---------------------------------------------------------------------------------------------*}]

	[{block name="a4p_admin_cms_files__form_export_for_translation"}]

		<h1 class="pageHead">Export for translation</h1>

		<form name="a4p_admin_cms_files__form_export_for_translation"
				id="a4p_admin_cms_files__form_export_for_translation"
				action="[{$oViewConf->getSelfLink()}]" method="post">

			[{$oViewConf->getHiddenSid()}]
			<input type="hidden" name="cl" value="[{$oView->getClassName()}]">
			<input type="hidden" name="fnc" value="exportCMSforTranslation">

			<div>

				Translation: "Deutsch" -> &nbsp;
				<select name="exportforlanguage" id="exportforlanguage">

					[{foreach from=$languages item=a_language}]
						[{if $a_language->oxid neq "de"}]
						<option value="[{$a_language->id}]">[{$a_language->name}]</option>
						[{/if}]
					[{/foreach}]

				</select>

			</div>
			<br />


			<input type="checkbox" name="b_filter_tags" id="b_filter_tags" value="1" checked="checked">
			<label for="b_filter_tags" class="link">Smarty+HTML-Tags filtern</label>
			<br />
			<br />


			export to:&nbsp;
			<input type="text" id="s_cms_files__export_dir__rel" name="s_cms_files__export_dir__rel" value="[{$oView->get_cms_files_translate_dir__rel()}]" size="40">

			<button type="submit">export</button>

		</form>

	[{/block}]




	[{*---------------------------------------------------------------------------------------------*}]
	[{*		Export	-	Status-Ausgabe																*}]
	[{*---------------------------------------------------------------------------------------------*}]

	[{block name="a4p_admin_cms_files__status_export"}]

		<hr>

		[{if isset( $a_ret__export.i_total ) && isset( $a_ret__export.i_exported )}]

			[{$a_ret__export.i_exported}] von [{$a_ret__export.i_total}] exportiert nach<br>
			[{$a_ret__export.s_cms_files_dir}]

		[{/if}]

	[{/block}]



	<hr>



	[{*---------------------------------------------------------------------------------------------*}]
	[{*		Import																					*}]
	[{*---------------------------------------------------------------------------------------------*}]




	[{include file="bottomnaviitem.tpl"}]

	[{include file="bottomitem.tpl"}]


[{*---------------------------------------------------------------------------------------------*}]
[{*---------------------------------------------------------------------------------------------*}]
[{*---------------------------------------------------------------------------------------------*}]
