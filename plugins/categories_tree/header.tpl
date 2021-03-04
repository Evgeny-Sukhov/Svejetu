<!-- category tree plugin header -->

<style type="text/css"> {literal}
.ctree-container img.plus-icon {
    background: url('{/literal}{$rlTplBase}{literal}img/gallery.png') 1px -1851px no-repeat;
}
.ctree-container li.ctree-sc > img.plus-icon {
    background: url('{/literal}{$rlTplBase}{literal}img/gallery.png') -2px -1825px no-repeat;
}
.ctree-container li.opened > img.plus-icon {
    background: url('{/literal}{$rlTplBase}{literal}img/gallery.png') -2px -1837px no-repeat;
}

{/literal}{if $smarty.const.RL_LANG_DIR == 'rtl'}{literal}
.ctree-container img.plus-icon {
    background: url('{/literal}{$rlTplBase}{literal}img/rtl/gallery.png') right -624px no-repeat;
}
{/literal}{/if}
</style>

<!-- category tree plugin header end -->
