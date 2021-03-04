/**
 * Reference number plugin class
 */
var refNumberClass = function () {
    /**
     * Current class object
     * @type {refNumberClass}
     */
    var self = this;

    /**
     * Init method of the class
     */
    this.init = function () {
        $('#ref_rebuild').click(function () {
            self.apRefresh(0);
        });
    };

    /**
     * Refresh Reference numbers of the all listings (Recursive method)
     *
     * @param {number} start - Starting pointer of the updating process
     */
    this.apRefresh = function (start) {
        var data = {
            item: 'refRefresh',
            start: start
        };
        var $refreshBtn = $('#ref_rebuild');
        $refreshBtn.val(lang['loading']);

        self.sendAjax(data, function (response) {
            if (response && (response.status || response.message)) {
                if (response.status == 'OK') {
                    if (response.message) {
                        printMessage('notice', response.message);
                    }

                    if (response.next_limit) {
                        return self.apRefresh(response.next_limit);
                    }

                    $refreshBtn.val(refLang['rebuild']);
                    return true;
                }

                printMessage('error', response.message);
                return false;
            }
        });
    };

    /**
     * Search listing by Ref number
     *
     * @param {string} val - Looking reference string
     */
    this.refSearch = function (val) {
        var data = {
            item: 'refSearch',
            ref : val,
            lang: rlLang
        };

        self.sendAjax(data, function (response) {
            if (response && (response.status || response.message)) {
                if (response.status == 'OK' && response.redirect) {
                    window.location.replace(response.redirect);
                    return true;
                }

                printMessage('error', response.message);
                return false;
            }
        });
    };

    /**
     * Sending ajax request
     *
     * @param data {object} - Sending data
     * @param callback {string} - Callback method, which will be called
     */
    this.sendAjax = function (data, callback) {
        $.post(rlConfig["ajax_url"], data,
            function (response) {
                callback(response);
            }, 'json')
    }
};