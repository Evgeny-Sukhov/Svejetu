<!-- excel export/ import | RESPONSIVE -->
{assign var='sPost' value=$smarty.post}
<script>
	var rlUrlHome = "{$rlBase}";
</script>
<!-- tabs -->
{if !$iel_action}
	<ul class="tabs">
		{foreach from=$tabs item='tab' name='tabF'}{strip}
			<li class="{if ($sPost.action == 'export_condition' || isset($smarty.get.refine)) && $tab.key == 'export'}active {elseif ($sPost.action != 'export_condition' && !isset($smarty.get.refine) ) && $tab.key == 'import'}active {/if}" id="tab_{$tab.key}">
				<a href="#{$tab.key}" data-target="{$tab.key}">{$tab.name}</a>
			</li>
		{/strip}{/foreach}
	</ul>
{/if}
<!-- tabs end -->

<div class="content-padding eil">
	<a href="#" id="line_color" class="hide"></a>
	{if !$iel_action}
		<!-- import/export forms -->
		<div id="area_import" class="tab_area{if $sPost.action == 'export_condition' || isset($smarty.get.refine)} hide{/if}">
			{if !$prevent_import_export && !$prevent_import}
				<form action="{$rlBase}{if $config.mod_rewrite}{$pageInfo.Path}.html{else}?page={$pageInfo.Path}{/if}" method="post" enctype="multipart/form-data">
					<input type="hidden" name="action" value="import_file" />

					<div class="submit-cell">
						<div class="name">{$lang.eil_file_for_import}</div>
						<div class="field single-field">
							<div class="file-input">
								<input type="file" class="file ei-selecting-file" name="file" />
								<input type="text" class="file-name" name="" />
								<span>{$lang.choose}</span>
							</div>

							<div style="padding: 2px 0 10px;">{$lang.eil_file_for_import_desc}</div>
						</div>
					</div>

					<div class="submit-cell">
						<div class="name">{$lang.eil_pictures_archive}</div>
						<div class="field single-field">
							<div class="file-input">
								<input type="file" class="file ei-selecting-file" name="archive" />
								<input type="text" class="file-name" name="" />
								<span>{$lang.choose}</span>
							</div>

							<div style="padding: 2px 0 10px;">{$lang.eil_max_file_size} <b>{$max_file_size} MB</b></div>
						</div>
					</div>

					<div class="submit-cell">
						<div class="name"></div>
						<div class="field"><input name="" type="submit" value="{$lang.upload}" /></div>
					</div>
				</form>
			{else}
				{if $prevent_import_export}
					{$lang.iel_cantimport}
				{else}
					{$lang.iel_account_type_cant_import}
				{/if}
			{/if}
		</div>

		<!-- export conditions -->
		<div id="area_export" class="tab_area{if $sPost.action != 'export_condition' && !isset($smarty.get.refine)} hide{/if}">
		{if !$prevent_export}
		<form action="{$rlBase}{if $config.mod_rewrite}{$pageInfo.Path}.html{else}?page={$pageInfo.Path}{/if}" method="post" enctype="multipart/form-data">
				<input type="hidden" name="action" value="export_condition" />
				<input type="hidden" name="from_post" value="1" />

				<div class="submit-cell">
					<div class="name">{$lang.eil_file_format} <span class="red">*</span></div>
					<div class="field single-field">
						<select name="export_format">
							<option value="">{$lang.select}</option>
							<option {if $sPost.export_format == 'xls'}selected="selected"{/if} value="xls">{$lang.eil_xls}</option>
							<option {if $sPost.export_format == 'csv'}selected="selected"{/if} value="csv">{$lang.eil_csv}</option>
						</select>
					</div>
				</div>

				<div class="submit-cell">
					<div class="name">{$lang.listing_type} <span class="red">*</span></div>
					<div class="field single-field">
						<select name="export_listing_type">
							<option value="">{$lang.select}</option>
							{foreach from=$listing_types item='l_type'}
								{if $l_type.Key|in_array:$account_info.Abilities}
									<option {if $sPost.export_listing_type == $l_type.Key}selected="selected"{/if} value="{$l_type.Key}">{$l_type.name}</option>
								{/if}
							{/foreach}
						</select>
					</div>
				</div>

				<div class="submit-cell">
					<div class="name">{$lang.category}</div>
					<div class="field single-field">
						<select name="export_category_id">
							<option value="">{if $categories}{$lang.select}{else}{$lang.eil_select_listing_type}{/if}</option>
							{if $categories}
								{foreach from=$categories item='category'}
									<option {if $category.Level == 0}class="highlight_opt"{/if} {if $category.margin}style="margin-left: {$category.margin}px;"{/if} value="{$category.ID}" {if $sPost.export_category_id == $category.ID}selected="selected"{/if}>{$lang[$category.pName]}</option>
								{/foreach}
							{/if}
						</select>
					</div>
				</div>

				<div class="submit-cell">
					<div class="name">{$lang.posted_date}</div>
					<div class="field">
						<input name="export_date_from" placeholder="{$lang.from}" type="text" value="{$sPost.export_date_from}" size="12" maxlength="10" id="date_from" />
						<input name="export_date_to" placeholder="{$lang.to}" type="text" value="{$sPost.export_date_to}" size="12" maxlength="10" id="date_to" />
					</div>
				</div>

				<div id="export_table" style="margin-top: 10px;">
					{if $fields}
						{include file=$smarty.const.RL_PLUGINS|cat:'export_import'|cat:$smarty.const.RL_DS|cat:'search.tpl'}
					{/if}
				</div>

				<div class="submit-cell buttons">
					<div class="name"></div>
					<div class="field"><input type="submit" name="" value="{$lang.eil_export}" /></div>
				</div>
			</form>

			<script class="fl-js-dynamic">//<![CDATA[
				var eil_select_listing_type = "{$lang.eil_select_listing_type}";
				{literal}

				$(document).ready(function(){
					$('select[name=export_listing_type]').change(function(){
						eil_typeHandler($(this).val(), 'export_category_id');
					});

					$(function(){
						$('#date_from').datepicker({showOn: 'both', buttonImage: '{/literal}{$rlTplBase}{literal}img/blank.gif', buttonText: '{/literal}{$lang.dp_choose_date}{literal}', buttonImageOnly: true, dateFormat: 'yy-mm-dd'}).datepicker($.datepicker.regional['{/literal}{$smarty.const.RL_LANG_CODE}{literal}']);
						$('#date_to').datepicker({showOn: 'both', buttonImage: '{/literal}{$rlTplBase}{literal}img/blank.gif', buttonText: '{/literal}{$lang.dp_choose_date}{literal}', buttonImageOnly: true, dateFormat: 'yy-mm-dd'}).datepicker($.datepicker.regional['{/literal}{$smarty.const.RL_LANG_CODE}{literal}']);
					});
				});

				{/literal}
				//]]>
			</script>
		{else}
			{$lang.iel_account_type_cant_import}
		{/if}
		</div>

		<!-- export conditions end -->

	{elseif $iel_action == 'import-table'}

		<!-- import table -->
		<div class="iel_table">
			<form id="importing-data" action="{$rlBase}{if $config.mod_rewrite}{$pageInfo.Path}/import-table{if $smarty.get.pg}/index{$smarty.get.pg}{/if}.html{else}?page={$pageInfo.Path}&amp;action=import-table{/if}" method="post" enctype="multipart/form-data">
				<input type="hidden" name="action" value="import_form" />
				<input type="hidden" name="from_post" value="1" />

				<div class="submit-cell listing-container">
					<div class="field">
						<table class="import list">
							<tr class="col-checkbox no_hover">
								<td class="divider"></td>
								{foreach from=$sPost.data.0 item='checkbox' name='checkboxF'}
									<td>
										{assign var='iter_checkbox' value=$smarty.foreach.checkboxF.iteration-1}
										<label><input class="multiline" {if isset($sPost.data) && $sPost.cols[$iter_checkbox]}checked="checked"{elseif !isset($sPost.data)}checked="checked"{/if} value="1" type="checkbox"  name="cols[{$iter_checkbox}]" /></label>
									</td>
									{if !$smarty.foreach.checkboxF.last}<td class="divider"></td>{/if}
								{/foreach}
							</tr>
							<tr class="header no_hover">
								<td class="row-checkbox no-style"></td>
								{foreach from=$sPost.data.0 item='col' name='fieldF'}
									<td>
										{assign var='iter_field' value=$smarty.foreach.fieldF.iteration-1}
										<div>
											<span class="caption" title="{$col}">{$col}</span>
											<select name="field[{$iter_field}]">
												<option value="">{$lang.eil_select_field}</option>
												<optgroup label="{$lang.eil_system_fields}">
													{foreach from=$system_fields item='field'}
														<option {if $sPost.field.$iter_field == $field.Key}selected="selected"{/if} value="{$field.Key}">{$field.name}</option>
													{/foreach}
												</optgroup>
												<optgroup label="{$lang.eil_listing_fields}">
													{foreach from=$listing_fields item='field'}
														<option {if $sPost.field.$iter_field == $field.Key}selected="selected"{/if} value="{$field.Key}">{$field.name}</option>
													{/foreach}
												</optgroup>
											</select>
										</div>
									</td>
									{if !$smarty.foreach.fieldF.last}<td class="divider"></td>{/if}
								{/foreach}
							</tr>
							{section name=rowF loop=$sPost.data start=$grid.start max=$grid.limit+1}
								{assign var='iter_row' value=$smarty.foreach.rowF.iteration-1}
								<tr class="body">
									<td class="row-checkbox">
									</td>
									{foreach from=$sPost.data[rowF] item='col' name='colF'}
										<td class="eil-field{if $col|@strlen > 94 || $col|@strlen != $col|regex_replace:'/[\r\t\n]/':''|@strlen} scroll{/if}">
											<div class="hborder">
												{$col|escape:'quotes'}
											</div>
										</td>
										{if !$smarty.foreach.colF.last}<td class="divider"></td>{/if}
									{/foreach}
								</tr>
							{/section}
						</table>
					</div>
				</div>
				{if $grid.total_pages > 1}
                    <div class="ralign">
					   {include file=$smarty.const.RL_PLUGINS|cat:'export_import'|cat:$smarty.const.RL_DS|cat:'pagination.tpl' type="import"}
                    </div>
				{/if}
				<div class="submit-cell options-start">
					<div class="name">{$lang.listing_type} <span class="red">*</span></div>
					<div class="field">
						<select class="w200" name="import_listing_type">
							<option value="">{$lang.select}</option>
							{foreach from=$listing_types item='l_type'}
								{if $l_type.Key|in_array:$account_info.Abilities}
									<option {if $sPost.import_listing_type == $l_type.Key}selected="selected"{/if} value="{$l_type.Key}">{$l_type.name}</option>
								{/if}
							{/foreach}
						</select>
					</div>
				</div>

				<div class="submit-cell">
					<div class="name">{$lang.eil_default_category} <span class="red">*</span></div>
					<div class="field">
						<select class="w200" name="import_category_id">
							<option value="">{if $categories}{$lang.select}{else}{$lang.eil_select_listing_type}{/if}</option>
							{if $categories}
								{foreach from=$categories item='category'}
									<option {if $category.Level == 0}class="highlight_option"{/if} {if $category.margin}style="margin-left: {$category.margin}px;"{/if} value="{$category.ID}" {if $sPost.import_category_id == $category.ID}selected="selected"{/if}>{$lang[$category.pName]}</option>
								{/foreach}
							{/if}
						</select>
					</div>
				</div>

				<div class="submit-cell">
					<div class="name">{$lang.eil_default_status} <span class="red">*</span></div>
					<div class="field">
						<select class="w200" name="import_status">
							<option value="">{$lang.select}</option>
							<option value="active" {if $sPost.import_status == 'active'}selected="selected"{/if}>{$lang.active}</option>
							<option value="approval" {if $sPost.import_status == 'approval'}selected="selected"{/if}>{$lang.approval}</option>
						</select>
					</div>
				</div>
				{if !$block_plan_import}
                    {assign var="max_import" value="max-import"}

					<div class="submit-cell import-package-informer {if !$smarty.session.eil_import_grid_message}hide{/if}">
						<input type="hidden" id="max-import" name="max-import" value="{if $sPost.$max_import}{$sPost.$max_import}{/if}">
						<div class="name"></div>
						<div class="field informer">{$smarty.session.eil_import_grid_message}</div>
					</div>
					<div class="submit-cell">
						<div class="name">{$lang.eil_default_plan} <span class="red">*</span></div>
						<div class="field">
							<select id="import_plan_id" class="w200" name="import_plan_id">
								<option value="">{$lang.select}</option>
								{if $plans}
									{foreach from=$plans item='plan'}
										{assign var='plan_type' value=$plan.Type|cat:'_plan'}
										<option value="{$plan.ID}" {if $sPost.import_plan_id == $plan.ID}selected="selected"{/if}>{$plan.name} ({$lang.$plan_type}) - {if $plan.Price}{$config.system_currency}{$plan.Price}{else}{$lang.free}{/if}{if $plan.is_bought} ({$lang.eil_owned}){/if}</option>
									{/foreach}
								{/if}
							</select>
						</div>
					</div>
				{/if}

				<div class="submit-cell">
					<div class="name">{$lang.eil_per_run}</div>
					<div class="field">
						<select style="width: 60px;" name="import_per_run">
							{if $plans}
								{foreach from=$per_run item='run'}
									<option value="{$run}" {if $sPost.import_per_run == $run}selected="selected"{/if}>{$run}</option>
								{/foreach}
							{/if}
						</select>
						<span class="field_description">{$lang.eil_per_run_desc}</span>
					</div>
				</div>

				<div class="submit-cell buttons">
					<div class="name"></div>
					<div class="field"><input type="submit" name="" value="{$lang.eil_import}" /></div>
				</div>
			</form>

			<div class="import_note">
				<div>{$lang.eil_pictures_by_url_note}</div>
				<div>{$lang.eil_youtube_video_field_note}</div>
			</div>
		</div>
		<!-- import table end -->

		<script class="fl-js-dynamic">//<![CDATA[
			importExport.phrases['eil_default_view'] = "{$lang.eil_default_view}";
			importExport.phrases['eil_import_table'] = "{$lang.eil_import_table}";
			importExport.phrases['eil_free_listing'] = "{$lang.eil_free_listing}";
			importExport.phrases['eil_prepaid_package'] = "{$lang.eil_prepaid_package}";
			importExport.phrases['used_up'] = "{$lang.used_up}";
			importExport.phrases['powered_by'] = "{$lang.powered_by|escape:'quotes'}";
			importExport.phrases['copy_rights'] = "{$lang.copy_rights|escape:'quotes'}";
			importExport.phrases['unlimited'] = "{$lang.unlimited}";
			importExport.phrases['number_left'] = "{$lang.number_left}";
			tplSetting = '{$tpl_settings}';

			{foreach from=$user_plans item='user_plan'}
			importExport.plans[{$user_plan.ID}] = new Array();
			{foreach from=$user_plan item='plan_value' key='plan_field'}
			importExport.plans[{$user_plan.ID}]['{$plan_field}'] = {if is_numeric($plan_value)}{$plan_value}{else}'{$plan_value}'{/if};
			{/foreach}
			{/foreach}

			var eil_listing_wont_imported = "{$lang.eil_listing_wont_imported}";
			var eil_column_wont_imported = "{$lang.eil_column_wont_imported}";
			var eil_select_listing_type = "{$lang.eil_select_listing_type}";

			var allow_match = {if $smarty.post.from_post || isset($smarty.get.edit)}false{else}true{/if};
			var matched_fields = 0;
			var listings_count = {$sPost.data|@count};
			var hide_first = {if $sPost.hide_first}true{else}false{/if};
			{literal}

			$(document).ready(function(){
				eil_rowHandler();
				eil_colHandler();
				eil_status();
				importExport.modeSwitcher();
				importExport.plansHandler();

				$('input[name^=rows]').click(function(){
					var index = $('table.import tr.body td.row-checkbox input').index(this);
					eil_rowHandler(index);
				});
				$('input[name^=cols]').click(function(){
					eil_colHandler();
				});

				$('select[name=import_listing_type]').change(function(){
					eil_typeHandler($(this).val(), 'import_category_id');
				});

				$('select[name=import_status]').change(function(){
					eil_status($(this).val());
				});

				/* match fields handler */
				$('table.import tr.header td:not(.divider) div').each(function(){
					var field = $(this).find('span.caption').text();
					if ( !$(this).find('select').val() && field )
					{
						$(this).find('select optgroup option').each(function () {
							var pattern = new RegExp('^' + field + '?', 'i');
							if (pattern.test($(this).text())) {
								if (allow_match) {
									$(this).attr('selected', true);
								}
								matched_fields++;
								return false;
							}
						});
						if ( !$(this).find('select').val() )
						{
							$(this).find('select').addClass('error');
						}
					}
				});

				if ( (matched_fields > 2 || hide_first) && listings_count > 1 )
				{
					$('table.import tr.body:first').hide();
					$('table.import').after('<input type="hidden" name="hide_first" value="1" />');
				}

				$('table.import select.error').click(function(){
					$(this).removeClass('error');
				});
			});

			{/literal}
			//]]>
		</script>
	{elseif $iel_action == 'import-preview'}

		<!-- importing -->
		<script class="fl-js-dynamic">//<![CDATA[[
			importExport.phrases['completed'] = "{$lang.eil_completed}";
			importExport.config['per_run'] = {$import_details.1.value};
			importExport.config['rl_url_home'] = "{$smarty.const.RL_URL_HOME}";
			//]]>
		</script>

		<table class="table">
			{foreach from=$import_details item='item' name='itemF'}
				<tr>
					<td class="name">{$item.name}:</td>
					<td class="value{if $smarty.foreach.itemF.first} first{/if}">{$item.value}</td>
				</tr>
			{/foreach}
		</table>

		<div id="import_start_nav">
			<table class="table" style="margin-top: 15px;">
				<tr>
					<td class="name" style="padding-top: 9px;">
						<a class="dark_12" href="{$rlBase}{if $config.mod_rewrite}{$pageInfo.Path}/import-table.html?edit{else}?page={$pageInfo.Path}&amp;action=import-table&amp;edit{/if}">&larr;{$lang.eil_back_to_import_form}</a>
					</td>
					<td class="value">
						<input id="start_import" type="button" value="{$lang.eil_start}" />
					</td>
				</tr>
			</table>
		</div>

		<div id="eil_statistic" class="hide">
			<div class="caption" style="padding-top: 30px">{$lang.eil_importing_caption}</div>
			<table class="table">
				<tr>
					<td class="name">
						{$lang.eil_total_listings}:
					</td>
					<td class="value">{$import_details.0.value}</td>
				</tr>
			</table>

			<div id="dom_area">
				<table class="table">
					<tr>
						<td class="name">
							{$lang.eil_importing}:
						</td>
						<td class="value" id="importing">1-{if $import_details.1.value > $import_details.0.value}{$import_details.0.value}{else}{$import_details.1.value}{/if}</td>
					</tr>
				</table>
			</div>

			<div class="progress hborder">
				<div>
					<div id="processing" class="highlight_dark search">
						<div id="loading_percent">0%</div>
					</div>
				</div>
			</div>
		</div>

		<!-- importing -->

	{elseif $iel_action == 'export-table'}

		<form action="{$rlBase}{if $config.mod_rewrite}{$pageInfo.Path}/export-table.html{else}?page={$pageInfo.Path}&amp;action=export-table{/if}" method="post" enctype="multipart/form-data">
			<input type="hidden" name="action" value="export_table" />
			<input type="hidden" name="from_post" value="1" />
			<input type="hidden" name="exclude_from_list" id="exclude_from_list" />

			<div class="form-buttons" style="padding: 0 0 30px;">
                <a href="{$rlBase}{if $config.mod_rewrite}{$pageInfo.Path}.html?refine{else}?page={$pageInfo.Path}&amp;refine{/if}">&larr;{$lang.eil_back_to_export_criteria}</a>
                <input type="submit" name="submit" value="{$lang.save}" />
            </div>

			<div class="iel_table">
				<table class="import export list">
					<tr class="col-checkbox no_hover">
						<td></td>
						{foreach from=$fields item='checkbox' name='checkboxF'}
							<td>
								{assign var='iter_checkbox' value=$smarty.foreach.checkboxF.iteration-1}
								<label><input class="multiline" {if isset($sPost.from_post) && $sPost.cols[$iter_checkbox]}checked="checked"{elseif !isset($sPost.from_post)}checked="checked"{/if} value="1" type="checkbox" name="cols[{$iter_checkbox}]" /></label>
							</td>
							{if !$smarty.foreach.checkboxF.last}
								<td></td>
							{/if}
						{/foreach}
					</tr>
					<tr class="header no_hover">
						<td class="no-style"></td>
						{foreach from=$fields item='field' name='fieldF'}
							<td><div>{$lang[$field.pName]}</div></td>
							{if !$smarty.foreach.fieldF.last}
								<td class="divider"></td>
							{/if}
						{/foreach}
					</tr>

					{foreach from=$export_listings item='listing' name='rowF'}
						{assign var='iter_row' value=$smarty.foreach.rowF.iteration-1}
						<tr class="body">
							<td class="row-checkbox">
								<label><input {if isset($sPost.from_post) && $sPost.rows[$iter_row]}checked="checked"{elseif !isset($sPost.from_post)}checked="checked"{/if} type="checkbox" name="rows[{$iter_row}]" data-id="{$listing.ID}" value="1" /></label>
							</td>
							{foreach from=$fields item='field' name='fieldF'}
								<td class="eil-field{if $listing[$field.Key]|@strlen > 65 || $listing[$field.Key]|@strlen != $listing[$field.Key]|regex_replace:'/[\r\t\n]/':''|@strlen} scroll{elseif $listing[$field.Key]|regex_replace:'/[\r\t\n]/':''|@strlen < 20} inline-nowrap{/if}">
                                    <div class="hborder">
                                        {$listing[$field.Key]}
                                    </div>
                                </td>
								{if !$smarty.foreach.fieldF.last}
									<td class="divider"></td>
								{/if}
							{/foreach}
						</tr>
					{/foreach}
				</table>
			</div>

			{if $grid.total_pages > 1}
				<div class="two-inline clearfix">
					<div class="name"></div>
					<div class="field">
						{include file=$smarty.const.RL_PLUGINS|cat:'export_import'|cat:$smarty.const.RL_DS|cat:'pagination.tpl' type="export"}
					</div>
				</div>
			{/if}

			<div class="form-buttons">
                <a href="{$rlBase}{if $config.mod_rewrite}{$pageInfo.Path}.html?refine{else}?page={$pageInfo.Path}&amp;refine{/if}">&larr;{$lang.eil_back_to_export_criteria}</a>
                <input type="submit" name="submit" value="{$lang.save}" />
			</div>
		</form>
		<!-- export listings table -->

		<script class="fl-js-dynamic">
			var eil_listing_wont_imported = "{$lang.eil_listing_wont_exported}";
			var eil_column_wont_imported = "{$lang.eil_column_wont_exported}";
			importExport.phrases['eil_default_view'] = "{$lang.eil_default_view}";
			importExport.phrases['eil_import_table'] = "{$lang.eil_export_listings}";
			importExport.phrases['powered_by'] = "{$lang.powered_by|escape:'quotes'}";
			importExport.phrases['copy_rights'] = "{$lang.copy_rights|escape:'quotes'}";
			{literal}

			$(document).ready(function(){
				eil_rowHandler();
				eil_colHandler();
				importExport.modeSwitcher();

				$('input[name^=rows]').click(function(){
					var index = $('table.import tr.body td.row-checkbox input').index(this);
					eil_rowHandler(index);
				});
				$('input[name^=cols]').click(function(){
					eil_colHandler();
				});

				$('input[name^=cols]').each(function(){
					var index = $(this).closest('tr.col-checkbox').find('input').index(this);
					index = (index * 2) + 2;

					var count = 0;
					var empty = 0;
					$('table.import tr.body td:nth-child('+index+') div').each(function(){
						empty += trim($(this).html()) == '' ? 1 : 0;
						count ++;
					});

					if ( count == empty )
					{
						$(this).trigger('click');
					}
				});
			});

			{/literal}
		</script>

	{/if}

</div>

<!-- excel export/ import end -->
