{if $multi_format_keys}
<script>
    var mfFields = new Array();
    var mfFieldVals = new Array();
    lang['select'] = "{$lang.select}";
    lang['not_available'] = "{$lang.not_available}";
</script>
{/if}

<script>
    var mfGeoFields = new Array();
</script>

{if $blocks.geo_filter_box || $home_page_special_block.Key == 'geo_filter_box'}
<style>
{literal}
/*** GEO LOCATION BOX */
.gf-box.gf-has-levels ul.gf-current {
    padding-bottom: 10px;
}
.gf-box ul.gf-current > li {
    padding: 3px 0;
}
.gf-box ul.gf-current span {
    display: inline-block;
    margin: 0 5px 1px 3px;
}
.gf-box ul.gf-current span:before {
    content: '';
    display: block;
    width: 5px;
    height: 9px;
    border-style: solid;
    border-width: 0 2px 2px 0;
    transform: rotate(45deg);
}
body[dir=rtl] .gf-box ul.gf-current span {
    margin: 0 3px 1px 5px;
}

.special-block .gf-root {
    display: flex;
    flex-direction: column;
    width: 100%;
}
.special-block .gf-root .gf-box {
    flex: 1;
    overflow: hidden;
}
.special-block .multiField .clearfix {
    display: flex;
}
.special-block .gf-box .gf-container {
    max-height: none;
}

.gf-box .gf-container {
    max-height: 250px;
    overflow: hidden;
}
.gf-box .gf-container li > a {
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
    padding: 3px 0;
    display: inline-block;
    width: 100%;
}
@media screen and (max-width: 767px) {
    .gf-box .gf-container li > a {
        padding: 6px 0;
    }
}

.mf-autocomplete {
    padding-bottom: 15px;
    position: relative;
}
.mf-autocomplete-dropdown {
    width: 100%;
    height: auto;
    max-height: 185px;
    position: absolute;
    overflow-y: auto;
    background: white;
    z-index: 500;
    margin: 0 !important;
    box-shadow: 0px 3px 5px rgba(0,0,0, 0.2);
}
.mf-autocomplete-dropdown > a {
    display: block;
    padding: 9px 10px;
    margin: 0;
}
.mf-autocomplete-dropdown > a:hover,
.mf-autocomplete-dropdown > a.active {
    background: #eeeeee;
}

.gf-current a > img {
    background-image: url({/literal}{$rlTplBase}{literal}img/gallery.png);
}
@media only screen and (-webkit-min-device-pixel-ratio: 1.5),
only screen and (min--moz-device-pixel-ratio: 1.5),
only screen and (min-device-pixel-ratio: 1.5),
only screen and (min-resolution: 144dpi) {
    .gf-current a > img {
        background-image: url({/literal}{$rlTplBase}{literal}img/@2x/gallery2.png) !important;
    }
}
{/literal}
</style>
{/if}
