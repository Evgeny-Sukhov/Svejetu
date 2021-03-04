
/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.8.1
 *	LISENSE: FL7YNR66E9FU - https://www.flynax.com/flynax-software-eula.html
 *	PRODUCT: Classified Ads Script
 *	DOMAIN : svejetu.me
 *	FILE   : LIB_ADMIN.JS
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

var import_in_progress = false;

$(document).ready(function(){
	$('#start_import').click(function(){
		importExport.start();
		$('#start_import').fadeOut();
	});
    $(window).bind('beforeunload', function() {
        if (import_in_progress) {
            return lang['eil_beforeunload_hint'];
        }
    });

    importExportPagination.registerHandlers();
});

var eil_colHandler = function(){
	$('input[name^=cols]').each(function(){
		var index = $(this).closest('tr.col-checkbox').find('input').index(this) + 2;

		if ( $(this).is(':checked') )
		{
			$('table.import tr td:nth-child('+index+')').removeClass('disabled no_hover');
			$(this).closest('td').attr('title', '');
			$('table.import tr.header td:nth-child('+index+')').attr('title', '');
		}
		else
		{
			$(this).attr('checked', false);
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
        $('select[name='+element+'] option:first').text(lang['loading']);
        $.post(rlConfig["ajax_url"],{ item: 'eil_fetchOptions', key: key, element: element },
        function(response){
            if(response.status == 'ok') {
                if(response.html.category) {
                    $('select[name=' + key + ']').html(response.html.category);
                }
                if(response.html.form) {
                    $("#export_table").html(response.html.form);
                }
                if(response.js) {
                    eval(response.js);
                }
            }
        }, 'json')
    }
    else
    {
        var option = '<option value="">'+eil_select_listing_type+'</option>';
        $('select[name='+element+']').html(option);
    }
}

var importExportClass = function(){
	var self = this;
	var item_width = width = percent = percent_value = 0;
	var window = false;
	var request;

	this.phrases = new Array();
	this.config = new Array();

	this.import = function(index){
		/* show window */
		if ( index == 0 )
		{
			if ( !window )
			{
				window = new Ext.Window({
					applyTo: 'statistic',
					layout: 'fit',
					width: 447,
					height: 120,
					closeAction: 'hide',
					plain: true
			    });

			    window.addListener('hide', function(){
	            	self.stop();
	            });
			}

			window.show();
		}

	    /* import request */
	    request = $.getJSON("../plugins/export_import/admin/import.php", {index: index}, function(response){
			index = response['from'];
			var percent = Math.ceil((response['from'] * 100) / response['count']);
			percent = percent > 100 ? 100 : percent;

			$('#processing').css('width', percent+'%');
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
				$('#import_start_nav').slideUp();
				printMessage('notice', self.phrases['completed'].replace('{count}', response['count']));
				setTimeout(function(){
					window.hide();
					listingsGrid.init();
					grid.push(listingsGrid.grid);

					// actions listener
					listingsGrid.actionButton.addListener('click', function(){
						var sel_obj = listingsGrid.checkboxColumn.getSelections();
						var action = listingsGrid.actionsDropDown.getValue();

						if (!action) {
							return false;
						}

						for (var i = 0; i < sel_obj.length; i++) {
							listingsGrid.ids += sel_obj[i].id;

							if (sel_obj.length != i+1) {
								listingsGrid.ids += '|';
							}
						}

						switch (action) {
							case 'delete':
								Ext.MessageBox.confirm('Confirm', lang['ext_notice_'+delete_mod], function(btn){
									if (btn == 'yes') {
										xajax_massActions(listingsGrid.ids, action);
										listingsGrid.store.reload();
									}
								});

								break;

							default:
								$('#make_featured,#move_area').fadeOut('fast');
								xajax_massActions(listingsGrid.ids, action);
								listingsGrid.store.reload();

								break;
						}

						listingsGrid.checkboxColumn.clearSelections();
						listingsGrid.actionsDropDown.setVisible(false);
						listingsGrid.actionButton.setVisible(false);
					});

					listingsGrid.grid.addListener('afteredit', function(editEvent){
						if (editEvent.field == 'Plan_ID') {
							listingsGrid.reload();
						}
					});
				}, 2000);
			}
		});
	}

	this.stop = function(){
		import_in_progress = false;
		request.abort();
	}

	this.start = function(){
		import_in_progress = true;
		self.import(0);
	}
};

var importExport = new importExportClass();
