<!-- languages selector -->

{if $languages|@count > 1}
	<span class="circle" id="lang-selector">
		<span class="default" accesskey="{$smarty.const.RL_LANG_CODE|ucfirst}">{$languages[$smarty.const.RL_LANG_CODE].Code}</span>
		<span class="content hide">
			<ul class="lang-selector">
				{foreach from=$languages item='lang_item'}
{*					{if $lang_item.Code|lower == $smarty.const.RL_LANG_CODE|lower}{continue}{/if}*}

					<li>
						<a class="font2" data-code="{$lang_item.Code|lower}" title="{$lang_item.name}" href="/{$lang_item.Code}">{$lang_item.name}</a>
					</li>
				{/foreach}
			</ul>
		</span>
	</span>
{/if}

<!-- languages selector end -->
