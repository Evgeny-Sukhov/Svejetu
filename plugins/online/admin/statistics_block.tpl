<!-- online statistics block -->

{literal}
<script>

function aBlockInit()
{
    var onlineLoad = false;
    var key = 'online_block';
    var func = 'xajax_adminStatistics()';

    if ($('.block div[lang=' + key + ']').is(':visible')) {
        eval(func);
    }
    else {
        $('.block div[lang=' + key + ']').prev().find('div.collapse').click(function() {
            if (!onlineLoad) {
                eval(func);
                onlineLoad = true;
            }
        });
    }

    $('input#apsblock\\\:' + key).click(function() {
        if (!onlineLoad && $(this).attr('checked') && $('.block div[lang=' + key + ']').is(':visible')) {
            eval(func);
            onlineLoad = true;
        }
    });
}
aBlockInit();

</script>
{/literal}

<!-- online statistics block end -->
