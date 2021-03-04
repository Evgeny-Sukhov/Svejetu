/**
 * Grid pagination class
 */
var importExportPaginationClass = function(){
    var self = this;

    /**
     * Current page
     * @type {number}
     */
    this.current_page = 1;

    /**
     * Last page in the pagination grid
     * @type {number}
     */
    this.last_page = 0;

    /**
     * Type of the pagination: {export, import}
     * @type {string}
     */
    this.type = '';

    /**
     * Where is pagination has been launched: {frontend, backend}
     * @type {string}
     */
    this.launched_in = '';

    /**
     * Class initialization method
     */
    this.init = function () {
        var $paginationUl = $('.grid-pagination');
        self.type = $paginationUl.data('type');
        self.launched_in =  $paginationUl.data('from');

        self.getCurrentPage();
        self.getPagenationInfo();
    };

    /**
     * Register all neccessary handlers
     */
    this.registerHandlers = function () {
        $('#goto-first').click(function () {
            self.first();
        });
        $('#goto-previous').click(function () {
            self.previous();
        });
        $('#goto-next').click(function () {
            self.next()
        });
        $('#goto-last').click(function () {
            self.last();
        });

        $('form').find('#goto-page').keypress(function (e) {
            if (e.which == 13) {
                var $input = $('#goto-page');
                if (self.isValidValue($input)) {
                    self.changeUrl($input.val());
                }

                return false;
            }
        });

        if (self.type == 'export' || !self.type) {
            $(".row-checkbox input").live('change', function (e) {
                self.excludeFromList(e);
                $(this).parents("tr").toggleClass('disabled no_hover');
            });
        }
    };


    /**
     * Validate input field
     *
     * @param $checking_value {object} - Input element
     * @returns {boolean} - Does value is valid
     */
    this.isValidValue = function ($checking_value) {
        var input_value = $checking_value.val();
        return !!(input_value > 0 && input_value <= self.last_page);
    };

    /**
     * Get next chunk of the data
     */
    this.next = function () {
        if (self.current_page == self.last_page) {
            return;
        }

        var page = self.current_page + 1;

        self.enableBtn('#goto-next,#goto-last');
        self.changeUrl(page);
    };

    /**
     * Get previous chunk of the data
     */
    this.previous = function () {
        if (self.current_page == 1) {
            return;
        }

        var page = self.current_page - 1;
        self.changeUrl(page);
    };

    /**
     * Hide first element of the list
     */
    this.hideFirst = function () {
        $('table.import tr.body:first').hide();
    };

    /**
     * Prevent unnecessary action by disabling button
     *
     * @param btn {string} - Button selector
     **/
    this.disableBtn = function (btn) {
        $(btn)
            .addClass('btn-disabled')
            .addClass('disabled')
            .attr('disabled', true);
    };

    /**
     * Enable actions of the button
     * @param btn {string} - Button selector
     */
    this.enableBtn = function (btn) {
        $(btn)
            .removeClass('btn-disabled')
            .removeClass('disabled')
            .removeAttr('disabled');
    };

    /**
     * Go to the last page
     */
    this.last = function () {
        self.changeUrl(self.last_page);
    };

    /**
     * Getting all infomation about pagination grid
     */
    this.getPagenationInfo = function () {
        data = {
            item: 'eil_ajaxGetPaginationInfo',
            mode: 'eil_ajaxGetPaginationInfo',
            type: self.type
        };

        self.sendAjax(data, function (response) {
            if (response.status == 'OK') {
                self.last_page = response.total_pages;
                $('#goto-page').val(self.current_page);
                self.checkButtonState(self.current_page);
            }
        });
    };

    /**
     * Go to the first pagination item
     */
    this.first = function () {
        self.changeUrl(1);
    };

    /**
     * Change url and getting data
     *
     * @param page {int} - page number
     */
    this.changeUrl = function (page) {
        self.current_page = parseInt(page);
        var data = {
            item: 'eil_ajaxImportExportPagination',
            mode: 'eil_ajaxImportExportPagination',
            page: page,
            type: self.type
        };

        self.sendAjax(data, function (response) {
            if (response.status == 'OK') {
                self.clearGrid();
                $(response.html).insertAfter($('.import tr:last-child'));
                window.location.hash = page;
                $('#goto-page').val(page);

                if (page == 1 && self.type == 'import') {
                    self.hideFirst();
                }
                self.checkButtonState(page);

                if (self.type == 'export') {
                    self.exportDomChanged();
                }
            }
        });
    };

    /**
     * Export dom has been changed after pagination control click
     */
    this.exportDomChanged = function () {
        var $excludeInput = $('#exclude_from_list');
        var data = $excludeInput.val();
        var working_array = data.split(',');

        $('.row-checkbox input').each(function () {
            var value = $(this).data('id');
            var index = working_array.indexOf(value.toString());

            if (index === -1) {
                $(this).attr('checked', true);
            } else {
                $(this).parents("tr").addClass('disabled no_hover');
                $(this).attr('checked', false);
            }
        });

        if (self.launched_in == 'frontend') {
            self.exportDomChangedOnFrontEnd();
        }
    };

    /**
     * Remove all dynamic data from grid
     */
    this.clearGrid = function () {
        $('.import tr.body').remove();
    };

    /**
     * After Export Dom changed handler on the front-end
     */
    this.exportDomChangedOnFrontEnd = function () {
        if (typeof flynaxTpl.customInput === 'function') {
            flynaxTpl.customInput();
        }
    };

    /**
     * Getting current page number
     *
     * @returns page {int} - current page
     */
    this.getCurrentPage = function () {
        var page = parseInt(window.location.hash.replace('#', ''))
            ? parseInt(window.location.hash.replace('#', ''))
            : 1 ;
        self.current_page = page;

        return page;
    };

    /**
     * Sending ajax request
     *
     * @param data {object} - Sending data
     * @param callback {string} - Callback method, which will be calleds
     */
    this.sendAjax = function (data, callback) {
        $.post(rlConfig["ajax_url"], data,
            function(response){
                callback(response);
            }, 'json')
    };

    /**
     * In which state button is
     *
     * @param page {int} - Current page
     */
    this.checkButtonState = function (page) {
        self.enableBtn('#goto-next,#goto-last,#goto-first,#goto-previous');

        if (page == self.last_page) {
            self.disableBtn('#goto-next,#goto-last');
        }

        if (page == 1) {
            self.disableBtn('#goto-previous,#goto-first');
        }
    };

    /**
     * Managing exclude ID's in the input
     *
     * @param event - Evenet handler object
     */
    this.excludeFromList = function (event) {

        var exclude = $(event.target).data('id');
        self.toggleValueInList(exclude);
    };

    /**
     * Add or remove exclude ID from the list
     * @param value - ID of the excluded
     */
    this.toggleValueInList = function (value) {
        var $excludeInput = $('#exclude_from_list');
        var data = $excludeInput.val();
        var working_array = data.split(',');
        var index = working_array.indexOf(value.toString());
        (index === -1) ? working_array.push(value) : working_array.splice(index, 1);

        // sanitize data
        working_array = working_array.filter(Boolean);
        data = working_array.join(',');

        $excludeInput.val(data);
    };
};

var importExportPagination = new importExportPaginationClass();
importExportPagination.init();
