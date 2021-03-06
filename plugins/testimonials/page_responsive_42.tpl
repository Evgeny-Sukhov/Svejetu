<!-- testimonials page content | responsive 42 -->

{if $total >= 20}
	<a class="button add-testimonial" href="javascript:void(0)" rel="nofollow"><span></span></a>
{/if}

{if $testimonials}
	<div class="testimonials" id="testimonials_area">
		{include file=$smarty.const.RL_PLUGINS|cat:'testimonials'|cat:$smarty.const.RL_DS|cat:'dom.tpl'}
	</div>
	
	<script type="text/javascript">
	{literal}
	
	$(function(){
		var color = $('.testimonials div.hlight').css('background-color');
		$('.testimonials div.triangle').css('border-{/literal}{if $text_dir == 'right'}top{else}right{/if}{literal}-color', color);
	});
	
	{/literal}
	</script>
{else}
	<div class="text-notice" style="padding-bottom: 30px;">{$lang.testimonials_no_testimonials}</div>
{/if}

{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_header.tpl' id='add_testimonial' name=$lang.testimonials_add}

<form class="content-padding" method="POST" action="" name="testimonial-form">
	<div class="submit-cell">
		<div class="name">{$lang.your_name} <span class="red">*</span></div>
		<div class="field">
			<input type="text" size="32" class="wauto" maxlength="32" id="t-name" {if $account_info}value="{$account_info.Full_name}"{/if} />
		</div>
	</div>
	<div class="submit-cell">
		<div class="name">{$lang.your_email}</div>
		<div class="field">
			<input type="text" size="50" class="wauto" maxlength="100" id="t-email" />
		</div>
	</div>
	<div class="submit-cell">
		<div class="name">{$lang.testimonials_testimonial} <span class="red">*</span></div>
		<div class="field">
			<textarea id="t-testimonial" cols="" rows="6"></textarea>
		</div>
	</div>
	<div class="submit-cell">
		<div class="name">{$lang.security_code} <span class="red">*</span></div>
		<div class="field">{include file='captcha.tpl' no_caption=true}</div>
	</div>
	<div class="submit-cell buttons">
		<div class="name"></div>
		<div class="field"><input type="submit" name="finish" value="{$lang.send}" /></div>
	</div>
</form>

<script type="text/javascript">
{literal}

$(document).ready(function(){
	$('a.add-testimonial').click(function(){
		flynax.slideTo('#fs_add_testimonial');
	});
	
	if ( flynax.getHash() == 'add-testimonial' ) {
		flynax.slideTo('#fs_add_testimonial');
	}
	
	$('form[name="testimonial-form"]').submit(function(){
		xajax_addTestimonial(
			$(this).find('#t-name').val(),
			$(this).find('#t-email').val(),
			$(this).find('#t-testimonial').val(),
			$(this).find('#security_code').val()
		);
		$(this).find('input[type=submit]').val(lang['loading']);
		
		return false;
	});
});

{/literal}
</script>

{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_footer.tpl'}

<!-- testimonials page content | responsive 42 END -->