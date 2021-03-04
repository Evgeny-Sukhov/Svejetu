
var monetizeClass = function () {
    /**
     * Intance of the monetize class
     * @type {monetizeClass}
     */
    var self = this;

    /**
     * @since 1.3.0
     * @type {string}
     */
    this.highlightClass = 'highlight';

    /**
     * @since 1.3.0
     * @type {gridObj}
     */
    this.activeGrid = {};

    /**
     * Events of the Monetize plugin
     */
    this.events = {
        /**
         * Event which is fire right after package removing process
         */
        afterPackageDelete: null
    };

    /**
     * Monetize plans switching
     */
    this.bp_plan_switcher = function () {

        var plan_elem = $("div.plans-container .plan input:checked").parents('li.plan');

        var plan_id = $(plan_elem).attr('id').split('_')[1];
        var is_free = $(plan_elem).data('isfree') ? true : false;

        var $getawayInputs = $('input[name="gateway"]');
        if ($getawayInputs.length > 0 && plan_id == -1) {
            $getawayInputs.attr('checked', false);
        }

        if (!is_free && plan_id != -1) {
            $("#credits-options").addClass('hide');
            $("#gateways").removeClass('hide');
        }

        $('.plans-container .plan').click(function(){
            var plan_container = $(this);

            var id = $(plan_container).attr('id').split('_')[1];
            var free = $(plan_container).data('isfree') ? true : false;

            if (id == -1 || free) {
                $('#gateways').addClass('hide');
                $('#credits-options')[free ? 'addClass' : 'removeClass']('hide');
            } else {
                $("#gateways").removeClass('hide');
                $("#credits-options").addClass('hide');
            }

            var $couponCodeBlock = $('#coupon_code');
            if ($couponCodeBlock.length > 0) {
                $couponCodeBlock.show();
                $('#coupon_code_name').val('');
            }

        });
    };

    /**
     * Hide all unnecessary data from single listing view
     *
     * @param selector - Selector
     */
    this.hideData = function (selector, is_escort) {

        $(selector).find(".before-nav").addClass('hide');
        $(selector).find('.nav-column > li:not(.bumped-up-full)').addClass('hide');
        $(selector).find('span.date').addClass('hide');
        $(selector).find('.price-tag').addClass('hide');
        $(selector).find('.rating-bar').addClass('hide');

        $(selector).find('.favorite').addClass('hide');
    };

    /**
     * Enable scroll bar on the choose monetize plans
     */
    this.enableScrollBar = function () {
        var $plansContainer = $('div.plans-container');
        if (typeof mCustomScrollbar !== 'undefined' && $plansContainer.find('ul.plans > li').length > 5) {
            $plansContainer.mCustomScrollbar('destroy').mCustomScrollbar({horizontalScroll: true});

            if (rlLangDir == 'rtl') {
                $plansContainer.mCustomScrollbar('scrollTo', 'right');
            }
        }
    };

    this.fixMonetizeBlock = function () {
        $('.monetize-block').find('article').removeClass('col-sm-3 col-sm-4').addClass('col-sm-12');
    };

    this.moveTabs = function () {
        var $monetizeBlocks = $("#area_bump_up,#area_highlight").detach();
        $monetizeBlocks.appendTo('.content-padding');
    };
    
    /**
     * Send ajax request to the request.ajax.php with provided data
     *
     * @since 1.3.0
     *
     * @param {object}   data     - Data which you want to send
     * @param {function} callback - Callable function which will be fired as an callback
     */
    this.sendAjax = function (data, callback) {
        $.post(rlConfig["ajax_url"], data,
            function (response) {
                callback(response);
            }, 'json')
    };
};

/**
 * @since 1.3.0
 */
monetizeClass.prototype = {
    /**
     * Highlight listings on the page
     */
    highlightListings: function () {
        var self = this;
        $('article.item').each(function (index, item) {
            var isHighlightHookExists = $(item).find('i.highlight').length;
            if (isHighlightHookExists) {
                $(item).addClass(self.highlightClass);
            }
        });
    },
    /**
     * Handle provided plan deleting, should it just delete or reassign to another one
     *
     * @param {number} planID - Monetizer plan ID
     * @param {string} planName - Monetizer plan Name
     * @param {string} type - Monetizer plan type: highlight, bumpup
     * @param {function} callback - Callback function
     */
    handlePlanDelete: function (planID, planName, type, callback) {
        var self = this;
        self.events.afterPackageDelete = callback;

        var reassignPlanUi = new ReassignPlanUIClass(planID, planName);
        reassignPlanUi.clearUI().init();

        this.getPlanUsingUsers(planID, type, function (users) {
            var usersCount = users.length;
            if (usersCount) {
                if (!$('#plans-reassign-block').is(':visible')) {
                    show('plans-reassign-block', '#action-block div');
                }

                var text = monetizeLang['m_plan_has_assigned_to'];
                reassignPlanUi.$assignInfoBlock
                .html(text.replace('[users_count]', usersCount)
                .replace('[monetize_plan]', planName));

                return;
            }

            return self.deletePlan(planID, type);
        });

        reassignPlanUi.$assignRadioOptions.change(function () {
            var submitActionType = parseInt($(this).val()) ? 'fetchPlans' : 'reassignPlan';
            reassignPlanUi.$submitButton.attr('data-type', submitActionType);
        });

        self.fetchPlansByType(reassignPlanUi.planTypes.highlight, planID, function (response) {
            if (response.plans.length) {
                reassignPlanUi.isPlanExists = true;
                reassignPlanUi.reassignOptions.$reassign.removeClass('hide');

                response.plans.forEach(function (plan) {
                    if (plan.ID == reassignPlanUi.removingPlan.id) {
                        return;
                    }

                    reassignPlanUi.$reassignPlanRow.find('select').append(
                        $('<option>', {
                            value: plan.ID,
                            text: plan.name,
                        })
                    );
                });
            }
        });

        reassignPlanUi.$submitButton.off('click').click(function () {
            var submitType = $(this).attr('data-type');
            if (!submitType) {
                return false;
            }

            if (parseInt(reassignPlanUi.$assignRadioOptions.filter(':checked').val())) {
                var toPlan = reassignPlanUi.$reassignPlanRow.find('select').val();
                self.reassignPlan(reassignPlanUi.removingPlan.id, toPlan, function (response) {
                    if (response.status == 'OK') {
                        self.deletePlan(planID, type);
                    }
                });
                return;
            }

            self.deletePlan(planID, type);
            return;
        });
    },
    /**
     * Get users list which are using provided monetizer plan
     *
     * @param {number} planID - Monetize plan ID
     * @param {string} type - Monetize plan type: highlight or bumpup
     * @param {function} callback
     */
    getPlanUsingUsers: function (planID, type, callback) {
        var data = {
            item: 'monetize_checkMonetizePlanUsage',
            type: type,
            plan_id: planID
        };

        this.sendAjax(data, function (response) {
            var users = [];
            if (response.users) {
                users = response.users;
            }

            return callback(users);
        });
    },
    /**
     * Get all plans by type: highlight or bumpup
     *
     * @param  {string}   planType - Monetizer plan type
     * @param  {number}   exclude  - Plan ID which you want to exclude from the result
     * @param  {function} callback
     *
     * @return {array} - List of the plans
     */
    fetchPlansByType: function (planType, exclude, callback) {
        if (!planType) {
            return [];
        }


        var data = {
            item: 'monetize_getPlans',
            type: planType,
            exclude: exclude
        };

        MonetizePluginUtils.sendAjax(data, function (response) {
            callback(response);
        });
    },
    /**
     * Reassign provided plan to another
     *
     * @param {number}   from - Monetize plan ID which you want to reassign
     * @param {number}   to   - Monetize plan ID to which you want to assign old plan
     * @param {function} callback
     */
    reassignPlan: function (from, to, callback) {
        var data = {
            item: 'monetize_reassignPlan',
            from: from,
            to: to
        };

        MonetizePluginUtils.sendAjax(data, function (response) {
            callback(response);
        });
    },
    /**
     * Delete provided monetizer plan
     *
     * @param {number}   planID   - Monetizer plan ID which you want to remove from DB
     * @param {string}   type     - Type of the Monetizer plan: highlight or bump up
     * @param {function} callback
     *
     * @return {boolean}
     */
    deletePlan: function (planID, type, callback) {
        var self = this;
        if (!planID) {
            return false;
        }

        var data = {
            item: type === 'highlight' ? 'deleteHighlightPlan' : 'deleteBumpUpPlan',
            id: planID
        };

        MonetizePluginUtils.sendAjax(data, function (response) {
            if ($('#plans-reassign-block').is(':visible')) {
                show('plans-reassign-block', '#action-block div');
            }

            var ui = new ReassignPlanUIClass();
            ui.clearUI();

            if (typeof callback === 'function') {
                return callback(response);
            }

            if (typeof self.events.afterPackageDelete === 'function') {
                return self.events.afterPackageDelete(response);
            }
        });
    }
};

// initialize object
var monetizer = new monetizeClass();
monetizer.enableScrollBar();

/**
 * @since 1.3.0
 */
var ReassignPlanUIClass = function (planID, planName) {
    /**
     * Main container of the plan reassign block
     * @type {jQuery}
     */
    this.$mainContainer = $('#plans-reassign-block');

    /**
     * Submit button of the plan reassign block
     * @type {jQuery}
     */
    this.$submitButton = this.$mainContainer.find('.reassign-credits-button');

    /**
     * Another plan selector element
     * @type {jQuery}
     */
    this.$reassignPlanRow = $('#reassign-to');

    /**
     * Each reassign option row
     */
    this.reassignOptions = {
        $deleteComplitely: $('.monetize-delete-row'),
        $reassign: $('.monetize-reassign-row')
    };

    /**
     * Information line of the plan reassign block
     * @type {jQuery}
     */
    this.$assignInfoBlock = $('.assign-info-block');

    /**
     * Deleting method radio button: complete removing or removing with reassign
     * @type {jQuery}
     */
    this.$assignRadioOptions = $('.assign-main-block').find('input[name="monetize-reassing"]');

    /**
     * Monetizer plugin plans type: highlight and bumpup
     * @type {Object}
     */
    this.planTypes = {highlight: 'highlight', bumpup: 'bumpup'};

    /**
     * Information of the plan which will be removed soon
     * @type {Object}
     */
    this.removingPlan = {name: planName, id: planID};
};

/**
 * @since 1.3.0
 */
ReassignPlanUIClass.prototype = {
    /**
     * Class initialization method
     */
    init: function () {
        this.listenEvents();
    },
    /**
     * Enable all event listeners of the plan reassin block
     */
    listenEvents: function () {
        var self = this;
        self.$assignRadioOptions.change(function () {
            var submitActionType = 'fetchPlans';
            !self.$reassignPlanRow.hasClass('hide') ? self.$reassignPlanRow.slideUp().addClass('hide') : '';

            if (parseInt($(this).val())) {
                submitActionType = 'reassignPlan';
                self.$reassignPlanRow.slideDown().removeClass('hide');
            }

            self.$submitButton.attr('data-type', submitActionType);
        });
    },
    /**
     * Clear ui of the plan reassign
     */
    clearUI: function () {
        this.$submitButton.removeAttr('data-type');
        this.$submitButton.off('click');
        this.$assignRadioOptions.off('change');
        this.$reassignPlanRow.addClass('hide');
        this.$reassignPlanRow.find('select').html('');

        $('#monetize-delete-plan').attr('checked', true);

        return this;
    },
};

/**
 * @since 1.3.0
 */
var monetizeCreditAssignClass = function () {
    /**
     * Current class instance
     * @type {monetizeCreditAssignClass}
     */
    var self = this;

    /**
     * Does credits input changed
     * @type {boolean}
     */
    this.creditsChanged = false;

    /**
     * Highlight credits which will be assign after pressing "Assign" button
     * @type {number}
     */
    this.highlightCredits = 0;

    /**
     * Bump up credits which will be assign after pressing "Assign" button
     * @type {number}
     */
    this.bumpUpCredits = 0;

    /**
     * Instance of the MonetizeClass
     * @type {monetizeClass}
     */
    this.monetizerClass = new monetizeClass();

    /**
     * Credits assigning whole block container
     * @type {jQuery|HTMLElement}
     */
    this.$blockContainer = $('#credits-assign-block');

    /**
     * Credits assigning informer block. Using for showing preloader
     */
    this.$informBlock = this.$blockContainer.find('.block-message');

    /**
     * Assign credits button element
     * @type {jQuery|HTMLElement}
     */
    this.$assignButton = $('#assign-btn');

    /**
     * Highlight assigning row container
     * @type {jQuery|HTMLElement}
     */
    this.$highlightInput = $('.higlight-input');

    /**
     * Bump up assigning row container
     * @type {jQuery|HTMLElement}
     */
    this.$bumpupInput = $('.bumpup-input');

    /**
     * Username selector element
     * @type {jQuery|HTMLElement}
     */
    this.$usernameInput = $('#account');

    /**
     * All options/elements of the highlight assigning credits row
     * @type {{ is_blocked: boolean, $self: jQuery|HTMLElement, $input: jQuery|HTMLElement,
     *          $selector: jQuery|HTMLElement, $messageBox: jQuery|HTMLElement }}
     */
    this.highlightCreditsRow = {
        is_blocked: false,
        $self: self.$highlightInput,
        $input: self.$highlightInput.find('.assign-input'),
        $selector: self.$highlightInput.find('select'),
        $messageBox: self.$highlightInput.find('span')
    };

    /**
     * All options/elements of the bump up assigning credits row
     * @type {{ is_blocked: boolean, $self: jQuery|HTMLElement, $input: jQuery|HTMLElement,
     *          $selector: jQuery|HTMLElement, $messageBox: jQuery|HTMLElement }}
     */
    this.bumpUpCreditsRow = {
        is_blocked: false,
        $self: self.$bumpupInput,
        $input: self.$bumpupInput.find('.assign-input'),
        $selector: self.$bumpupInput.find('select'),
        $messageBox: self.$bumpupInput.find('span')
    };

    /**
     * Initial method
     */
    this.init = function () {
        var username = '';

        self.highlightCreditsRow.$input.on('input', function () {
            self.creditInputChanged();
        });

        self.bumpUpCreditsRow.$input.on('input', function () {
            self.creditInputChanged();
        });

        self.highlightCreditsRow.$selector.change(function () {
            self.onHighlightSelecChanged(this);
        });

        self.bumpUpCreditsRow.$selector.change(function () {
            self.onBumpupSelectChanged(this);
        });

        self.$assignButton.click(function () {
            self.assignCredits();
        });

        self.$usernameInput.on('input', function () {
            if (!$(this).val()) {
                self.$assignButton.attr('disabled', 'disabled')
            }
        });

        self.$usernameInput.rlAutoComplete({
            add_id: true,
            add_type: true,
            afterload: function (response) {
                if (response.Username) {
                    username = response.Username;
                    self.showInformBlock();
                    self.getInfoByUserName(username);
                }
            }
        });

        if (monetizeConfig['less_than_460']) {
            $(document).on('click', '#ac_interface div', function () {
                var username = $(this).html().replace(/<b>/i, '').replace(/<\/b>/i, '');

                self.showInformBlock();
                self.getInfoByUserName(username);
            });
        }
    };

    /**
     * Event run after highlight selector change
     * @param element
     */
    this.onHighlightSelecChanged = function (element) {
        var plan = $(element).val();
        var username = self.$usernameInput.val();
        var buttonText = self.$assignButton.val();

        var data = {
            item: 'monetize_getHighlightCredits',
            plan: plan,
            username: username
        };

        self.$assignButton.attr('disabled', 'disabled').val(lang['loading']);

        self.monetizerClass.sendAjax(data, function (response) {
            self.$assignButton.removeAttr('disabled').val(buttonText);

            if (response.status == 'OK') {
                var creditsInPlan = response.credits;
                self.highlightCreditsRow.$input.val(creditsInPlan);
                self.highlightCredits = creditsInPlan;
            }
        });
    };

    /**
     * Event run after bumpupPlans selector change
     * @param element - Select element itself
     */
    this.onBumpupSelectChanged = function (element) {
        var plan = $(element).val();
        var username = self.$usernameInput.val();
        var buttonText = self.$assignButton.val();

        var data = {
            item: 'monetize_getBumpUpCredits',
            plan: plan,
            username: username
        };

        self.$assignButton.attr('disabled', 'disabled').val(lang['loading']);

        self.monetizerClass.sendAjax(data, function (response) {
            self.$assignButton.removeAttr('disabled').val(buttonText);

            if (response.status == 'OK') {
                var creditsInPlan = response.credits;
                self.bumpUpCreditsRow.$input.val(creditsInPlan);
                self.bumpUpCredits = creditsInPlan;
            }
        });
    };

    /**
     * Assign amount of the credits to user
     */
    this.assignCredits = function () {
        var username = self.$usernameInput.val();
        var buttonText = self.$assignButton.val();

        self.$assignButton.attr('disabled', 'disabled').val(lang['loading']);

        var data = {
            item: 'monetize_ajaxAssignCredits',
            username: username,
            bumpup_plan: self.bumpUpCreditsRow.$selector.val(),
            bumpup_credits: self.bumpUpCreditsRow.$input.val(),
            highlight_plan: self.highlightCreditsRow.$selector.val(),
            highlight_credits: self.highlightCreditsRow.$input.val()
        };

        self.monetizerClass.sendAjax(data, function (response) {
            self.$assignButton.val(buttonText).removeAttr('disabled');

            if (response.status == 'OK') {
                printMessage('notice', monetizeLang['m_credits_assigned']);
                show('credits-assign-block');

                setTimeout(function () {
                    self.highlightCreditsRow.$self.addClass('hide');
                    self.bumpUpCreditsRow.$self.addClass('hide');
                    self.$usernameInput.val('');
                    self.$assignButton.attr('disabled', 'disabled');
                    self.bumpUpCreditsRow.$selector.html('');
                    self.highlightCreditsRow.$selector.html('');
                }, 400);

                return true;
            }

            printMessage('error', monetizeLang['m_something_went_wrong']);
            return false;
        });
    };

    /**
     * Event run after changing any credits assigning input change
     */
    this.creditInputChanged = function () {
        var nothingChanged = self.bumpUpCreditsRow.$input.val() == self.bumpUpCredits
            && self.highlightCreditsRow.$input.val() == self.highlightCredits;
        self.setCreditsChanged(!nothingChanged);
    };

    /**
     * Setter of the creditsChanged property
     * @param {boolean} value
     */
    this.setCreditsChanged = function (value) {
        self.creditsChanged = value;
        self.creditsChanged
            ? self.$assignButton.removeAttr('disabled')
            : self.$assignButton.attr('disabled', 'disabled');
    };

    /**
     * Setter of the creditsChanged property
     * @returns {boolean}
     */
    this.getCreditsChanged = function () {
        return self.creditsChanged;
    };

    /**
     * Getting plans and already using credits for them
     * @param {string} username
     */
    this.getInfoByUserName = function (username) {
        self.$assignButton.attr('disabled', 'disabled');

        var data = {
            item: 'monetize_getMonetizePlanUsingInfo',
            username: username
        };

        self.monetizerClass.sendAjax(data, function (response) {
            if (response.status == 'OK') {
                self.$bumpupInput.removeClass('hide');
                self.$highlightInput.removeClass('hide');

                self.blockBumpUpRow();
                if (response.credits_info.bump_up) {
                    self.bumpUpCredits = parseInt(response.credits_info.bump_up.total_credits);

                    var bumpupPlans = response.credits_info.bump_up.plans;
                    if (bumpupPlans.length > 0) {
                        self.bumpUpCredits = bumpupPlans[0].credits;
                        bumpupPlans.forEach(function (value, index) {
                            self.bumpUpCreditsRow.$selector.append($('<option/>', {
                                value: value.ID,
                                text: value.name
                            }))
                        });
                        self.unblockBumpUpRow();
                    }

                    self.bumpUpCreditsRow.$input.val(self.bumpUpCredits);
                }

                if (response.credits_info.highlight) {
                    self.blockHighlightRow();
                    if (response.credits_info.highlight.plans.length > 0) {
                        var highlightPlans = response.credits_info.highlight.plans;
                        self.highlightCredits = highlightPlans[0].credits;

                        highlightPlans.forEach(function (value, index) {
                            self.highlightCreditsRow.$selector.append($('<option/>', {
                                value: value.ID,
                                text: value.name
                            }))
                        });

                        self.highlightCreditsRow.$input.val(self.highlightCredits);
                        self.unblockHighlightRow();
                    }
                }

                self.$assignButton.removeAttr('disabled');
            }

            self.hideInformBlock();
        })
    };

    /**
     * Making whole row of provided type an inactive for users
     * @param {string} type - What row do you want to make an inactive: {highlight, bumpup}
     */
    this.blockRow = function (type) {
        var object = type == 'highlight' ? 'highlightCreditsRow' : 'bumpUpCreditsRow';
        var message = type == 'highlight'
            ? monetizeLang['m_cant_assign_highlight_credits']
            : monetizeLang['m_cant_assign_bumpup_credits'];

        self[object].is_blocked = true;
        self[object].$selector.hide();
        self[object].$messageBox.addClass('settings_desc').html(message);
        self[object].$input.attr('disabled', 'disabled').addClass('disabled').val('');
    };

    /**
     * Making whole row of provided type active for the users
     * @param {string} type - What row do you want to make available: {highlight, bumpup}
     */
    this.unblockRow = function (type) {
        var object = type == 'highlight' ? 'highlightCreditsRow' : 'bumpUpCreditsRow';
        var message = type == 'highlight' ? monetizeLang['m_select_highlight_plan'] : monetizeLang['select_bump_up'];

        self[object].is_blocked = false;
        self[object].$selector.show();
        self[object].$messageBox.removeClass('settings_desc').html(message).text();
        self[object].$input.removeAttr('disabled').removeClass('disabled');
    };

    /**
     * Show preloader block with provided message
     * @param {string} message
     */
    this.showInformBlock = function (message) {
        message = message ? message : lang['loading'];
        self.$informBlock.text(message).show();
    };

    /**
     * Hide preloader block
     */
    this.hideInformBlock = function () {
        self.$informBlock.hide();
    };

    /**
     * Blocking whole row of assigning highlight plans
     * Helper of the blockRow method
     */
    this.blockHighlightRow = function () {
        self.blockRow('highlight');
    };

    /**
     * Making whole row of assigning highlight plans available again
     * Helper of the unblockRow method
     */
    this.unblockHighlightRow = function () {
        self.unblockRow('highlight');
    };

    /**
     * Blocking whole row of assigning bump up plans
     * Helper of the blockRow method
     */
    this.blockBumpUpRow = function () {
        self.blockRow('bumpup');
    };

    /**
     * Making whole row of assigning bump up plans available again
     * Helper of the unblockRow method
     */
    this.unblockBumpUpRow = function () {
        self.unblockRow('bumpup');
    };
};

/**
 * Public available utils of the monetize plugin which are available without creating an instance of the class
 * @since 1.3.0
 */
var MonetizePluginUtils = function () {
    /**
     * Send ajax request to the request.ajax.php with provided data
     *
     * @since 1.3.0
     *
     * @param {object}   data     - Data which you want to send
     * @param {function} callback - Callable function which will be fired as an callback
     */
    sendAjax = function (data, callback) {
        $.post(rlConfig['ajax_url'], data,
            function (response) {
                callback(response);
            }, 'json');
    };

    return {
        sendAjax: function (data, callback) {
            sendAjax(data, callback);
        }
    };
}();

