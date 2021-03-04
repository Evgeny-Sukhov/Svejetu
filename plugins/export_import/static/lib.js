
/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.8.1
 *	LISENSE: FL7YNR66E9FU - https://www.flynax.com/flynax-software-eula.html
 *	PRODUCT: Classified Ads Script
 *	DOMAIN : svejetu.me
 *	FILE   : LIB.JS
 *
 *	This script is a commercial software and any kind of using it must be
 *	coordinate with Flynax Owners Team and be agree to Flynax License Agreement
 *
 *	This block may not be removed from this file or any other files with out
 *	permission of Flynax respective owners.
 *
 *	Copyrights Flynax Classifieds Software | 2020
 *	https://www.flynax.com
 *
 ******************************************************************************/

/**
* clone array/object without reference
*
*/
var clone = function(obj){
    var newObj = (obj instanceof Array) ? [] : {};
    for (i in obj) {
        if (obj[i] && typeof obj[i] == 'object') {
            newObj[i] = clone(obj[i]);
        }
        else {
            newObj[i] = obj[i]
        }
    }
    return newObj;
};

$(document).ready(function(){
    $('#start_import').click(function(){
        $('#import_start_nav').fadeOut(function(){
            $('#eil_statistic').fadeIn();
            importExport.start();
        });
    });

    // Unbind default pagination events
    $('ul.pagination li.transit input').unbind('keypress');

    importExportPagination.registerHandlers();

    $('#import_plan_id').change(function () {
        importExport.checkPackage($(this).val())
    });

    $('.ei-selecting-file').change(function () {
        var fileName = $(this)[0].files[0].name;
        $(this).parent().find('.file-name').val(fileName);
    });
});

var eil_rowHandler = function(row){
    return false;
    
    var eq = row ? ':eq('+row+')' : '';
    $('input[name^=rows]'+eq+'').each(function(){
        if ( $(this).is(':checked') )
        {
            $(this).closest('tr.body').removeClass('disabled no_hover').attr('title', '');
        }
        else
        {
            $(this).closest('tr.body').addClass('disabled no_hover').attr('title', eil_listing_wont_imported);
        }
    });
}

var eil_colHandler = function(){
    $('input[name^=cols]').each(function(){
        var index = $(this).closest('tr.col-checkbox').find('input').index(this);
        index = (index * 2) + 2;
        if ( $(this).is(':checked') )
        {
            $('table.import tr td:nth-child('+index+')').removeClass('disabled no_hover');
            $(this).closest('td').attr('title', '');
            $('table.import tr.header td:nth-child('+index+')').attr('title', '');
        }
        else
        {
            $('table.import tr td:nth-child('+index+')').addClass('disabled no_hover');
            $(this).closest('td').attr('title', eil_column_wont_imported);
            $('table.import tr.header td:nth-child('+index+')').attr('title', eil_column_wont_imported);
            $('table.import tr.header td:nth-child('+index+') select option').attr('selected', false);
        }
    });
}

var eil_typeHandler = function(key, element){
    if ( key )
    {
        // $('select[name='+element+'] option:first').text(lang['loading']);
        // xajax_fetchOptions(key, element, 1);

        $.post(
            rlConfig["ajax_url"],
            { 
                mode: 'eil_fetchOptions',
                key: key,
                element: element
            },
            function(response){
                if(response.status == 'ok') {
                    if(response.html.category) {
                        $('select[name=' + element + ']').html(response.html.category);
                    }
                    if(response.html.form) {
                        $("#export_table").html(response.html.form);
                    }
                    if(response.js) {
                        eval(response.js);
                    }
                    if(response.customInput) {
                        flynaxTpl.customInput();
                    }
                }
            }, 'json');
    }
    else
    {
        var option = '<option value="">'+eil_select_listing_type+'</option>';
        $('select[name='+element+']').html(option);
    }
}

var eil_status = function(value){
    value = value ? value : $('select[name=import_status]').val();
    
//  if ( value == 'approval' ) {
//      $('#plans_tr').hide();
//  }
//  else {
//      $('#plans_tr').show();
//  }
}

/**
 * Prevent default submit using Enter key in the pagination input
 */
function preventDefaultSubmit(event)
{
    if($(".pagination .transit input[type='text']").is(':focus')) {
        event.preventDefault();
    }
}

var importExportClass = function(){
    var self = this;
    var item_width = width = percent = percent_value = 0;
    var window = false;
    var request;
    
    this.phrases = new Array();
    this.config = new Array();
    this.plans = new Array();
        
    this.import = function(index){
        /* import request */
        request = $.getJSON(self.config['rl_url_home'] +"plugins/export_import/import.php", {index: index}, function(response){
            index = response['from'];
            var percent = Math.ceil((response['from'] * 100) / response['count']);
            percent = percent > 100 ? 100 : percent;

            $('#processing').css('width', percent+'%');
            if ($("#line_color").length > 0) {
                $('#processing').css('background-color', $("#line_color").css('color'));
            }

            $('#loading_percent').html(percent+'%');
            
            if ( response['count'] > index )
            {
                var from = response['from'] + 1;
                var to = response['to'] + 1;
                to = response['count'] < to ? response['count'] : to;
                var import_current = from+'-'+to;
                $('#importing').html(import_current);

                self.import(index);
            }
            else
            {
                $('#eil_statistic').animate({opacity: 0});
                printMessage('notice', self.phrases['completed'].replace('{count}', response['count']));
                setTimeout(function(){
                    if ( rlConfig['mod_rewrite'] ) {
                        location.href=rlConfig['seo_url']+rlPageInfo['path']+'.html?reset';
                    }
                    else {
                        location.href=rlConfig['seo_url']+'?page='+rlPageInfo['path']+'&reset';
                    }
                }, 2000);
            }
        });
    }
    
    this.stop = function(){
        request.abort();
    }

    this.start = function(){
        self.import(0);
    }

    this.modeSwitcher = function(){
        $('a.eil_fullscreen').unbind('click').click(function(){
            var date = new Date();
            var button = '<a class="button eil_default" title="" href="javascript:void(0)"><span>'+ importExport.phrases['eil_default_view'] +'</span></a>';
            $('body > *:visible').addClass('tmp-hidden').hide();
            $('body').append('<div class="eil_fullscreen_area hide"><div class="eil_header"><table class="sTable"><tr><td class="lalign"><h1>'+ importExport.phrases['eil_import_table'] +'</h1></td><td class="ralign">'+ button +'</td></tr></table></div><div class="eil_body"></div><div id="footer" class="eil_footer"><table class="sTable"><tr><td class="lalign"><img alt="" src="'+ rlConfig['tpl_base'] +'img/logo.png" /></td><td class="ralign" valign="top"><span>&copy; '+date.getFullYear()+', '+importExport.phrases['powered_by']+' </span><a title="'+importExport.phrases['powered_by']+' '+importExport.phrases['copy_rights']+'" href="'+rlConfig['seo_url']+'">'+ importExport.phrases['copy_rights'] +'</a></td></tr></table></div></div>');
            $('body > div.eil_fullscreen_area > div.eil_body').append($('div.iel_table').parent());
            $('body > div.eil_fullscreen_area').fadeIn();
            
            $('a.eil_default').unbind('click').click(function(){
                $('body > div.eil_fullscreen_area').fadeOut(function(){
                    $('#controller_area').prepend($('body > div.eil_fullscreen_area > div.eil_body').children(':first'));
                    $('body > *.tmp-hidden').show().removeClass('tmp-hidden');
                    $(this).remove();
                });
            });
        });
    };

    this.plansHandler = function(){
        var plans = clone(this.plans);
        
        /* calculate plans using */
        $('table.import td.row-plan select').each(function(){
            var id = parseInt($(this).val());
            
            if ( id ) {
                var plan = plans[id];
                
                /* limited plans */
                if ( (plan['Limit'] > 0) || (plan['Package'] == 'package' && !plan['Advanced_mode']) ) {
                    plan['Listings_remains'] -= 1;
                }
                else if ( plan['Package'] == 'package' && plan['Advanced_mode'] ) {
                    if ( $(this).next().find('input:checked').val() == 'standard' ) {
                        plan['Standard_remains'] -= 1;
                    }
                    else if ( $(this).next().find('input:checked').val() == 'featured' ) {
                        plan['Featured_remains'] -= 1;
                    }
                }
            }
        });
        
        /* update plan details in dropdowns */
        $('table.import td.row-plan select').each(function(){
            var parent = this;
            
            $(this).find('option').each(function(){
                var id = parseInt($(this).val());
                
                if ( id ) {
                    var plan = plans[id];
                    
                    if ( plan['Limit'] > 0 || (plan['Package'] == 'package' && !plan['Advanced_mode']) ) {
                        var name = plan['name'] +' - '+ self.phrases[plan['Limit'] > 0 ? 'eil_free_listing' : 'eil_prepaid_package'];
                        var limit_field = plan['Limit'] > 0 ? 'Limit' : 'Listing_number';
                        
                        if ( $(this).is(':selected') ) {
                            $(this).html(name);
                        }
                        else {
                            if ( plan[limit_field] > 0 ) {
                                if ( plan['Listings_remains'] == 0 ) {
                                    $(this).html(name + ' ('+self.phrases['used_up']+')');
                                    $(this).attr('disabled', true);
                                }
                                else {
                                    $(this).html(name + ' ('+self.phrases['number_left'].replace('{number}', plan['Listings_remains'])+')');
                                    $(this).attr('disabled', false);
                                }
                            }
                            else {
                                $(this).html(name + ' ('+self.phrases['unlimited']+')');
                            }
                        }
                    }
                    else if ( plan['Package'] == 'package' ) {
                        var name = plan['name'] +' - '+ self.phrases['eil_prepaid_package'];
                        
                        $(this).html(name);
                        $(parent).next().hide();
                        
                        if ( plan['Advanced_mode'] ) {
                            var area = $(parent).next();
                            
                            if ( $(this).is(':selected') ) {
                                area.show();
                                
                                if ( $(area).find('input:not(:checked)').length == 2 ) {
                                    // standard info
                                    var standard_info = '';
                                    
                                    area.find('li:first input').attr('disabled', false);
                                    if ( plan['Standard_listings'] == 0 ) {
                                        standard_info = self.phrases['unlimited'];
                                    }
                                    else {
                                        if ( plan['Standard_remains'] > 0 ) {
                                            standard_info = self.phrases['number_left'].replace('{number}', plan['Standard_remains']);
                                        }
                                        else {
                                            standard_info = self.phrases['used_up'];
                                            area.find('li:first input').attr('disabled', true);
                                        }
                                    }
                                    area.find('li:first span').html('('+standard_info+')');
                                    
                                    // featured
                                    var featured_info = '';
                                    
                                    area.find('li:last input').attr('disabled', false);
                                    if ( plan['Featured_listings'] == 0 ) {
                                        featured_info = self.phrases['unlimited'];
                                    }
                                    else {
                                        if ( plan['Featured_remains'] > 0 ) {
                                            featured_info = self.phrases['number_left'].replace('{number}', plan['Featured_remains']);
                                        }
                                        else {
                                            featured_info = self.phrases['used_up'];
                                            area.find('li:last input').attr('disabled', true);
                                        }
                                    }
                                    area.find('li:last span').html('('+featured_info+')');
                                }
                                else if ( $(area).find('input:checked').length == 1 ) {
                                    area.find('span').html('');
                                }
                            }
                            else {
                                $(area).find('input').attr('checked', false);
                            }
                        }
                    }
                    else {
                        $(this).html(plan['name'] +' - '+ self.phrases['eil_free_listing']);
                    }
                }
            });
        });
    };

    /**
     * @since 3.6.0
     */
    this.sendAjax = function (data, callback) {
        if (typeof flUtilClass == 'function') {
            flUtil.ajax(data, function (response, status) {
                callback(response);
            });

            return true;
        }

        self._sendAjax(data, callback);
    };

    /**
     * @since 3.6.0
     */
    this._sendAjax = function (data, callback) {
        $.post(rlConfig["ajax_url"], data,
            function (response) {
                callback(response);
            }, 'json');
    };

    /**
     * @since 3.6.0
     *
     * @param {integer} listingPackageID
     */
    this.checkPackage = function (listingPackageID) {
        if (!listingPackageID) {
            return false;
        }

        var $informerBox = $('.import-package-informer');
        $('#max-import').val(0);

        var data = {
            'mode': 'eil_checkListingPackage',
            'package_id': listingPackageID
        };

        self.sendAjax(data, function (response) {
            if (response.status == 'OK') {
                var canBeImport = response.can_import ? response.can_import : 0;
                var isSimplePackage = response.type != 'package';
                var $informerBox = $('.import-package-informer');

                if (response.message) {
                    $informerBox.show();
                    $informerBox.find('.informer').html(response.message);

                    if (isSimplePackage) {
                        return;
                    }
                }

                var $maxImport = $('#max-import');
                if (!isSimplePackage) {
                    $maxImport.val(canBeImport);
                    return true;
                }

                $maxImport.removeAttr('value');

                if (isSimplePackage && response.is_free) {
                    $informerBox.hide();
                }
            }
        });
    }
};

var importExport = new importExportClass();
