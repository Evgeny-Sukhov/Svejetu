
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

var CategoryFilterClass = function () {
    'use strict';

    /**
     * Reference to self object
     */
    var self = this;

    /**
     * Show/hide other sub-categories
     */
    this.moreFilters = function () {
        $('div.filter-area ul').each(function () {
            if ($(this).find('li.hide').length > 0) {
                $(this).next().after('<ul class="hide"></ul>');
                $(this).next().next().append($(this).find('li.hide').show());
            }
        });

        $('div.filter-area a.more').click(function () {
            var pos, subCategories, tmp, width, offset;

            $('div.other_filters_tmp').remove();

            pos           = $(this).offset();
            subCategories = $(this).next().html();
            tmp           = '<div class="other_filters_tmp side_block">';
            tmp           += '<div class="block_bg hlight"><ul></ul></div></div>';

            $('body').append(tmp);
            $('div.other_filters_tmp div ul').html(subCategories);

            width  = $(this).width() + 5;
            offset = rlLangDir === 'ltr' ? pos.left + width : pos.left - $('div.other_filters_tmp').width();

            $('div.other_filters_tmp').css({top: pos.top, left: offset, display: 'block'});
        });

        $(document).click(function (event) {
            if ($(event.target).closest('.other_filters_tmp').length <= 0
                && $(event.target).attr('class') !== 'dark_12 more'
            ) {
                $('div.other_filters_tmp').remove();
            }
        });
    };

    /**
     * Checkbox call method
     */
    this.checkbox = function (obj, empty) {
        obj.parent().find('ul li input').click(function () {
            self.checkboxAction(obj, empty);
        });

        this.checkboxAction(obj, empty);
    };

    /**
     * Checkbox action method
     */
    this.checkboxAction = function (obj, empty) {
        var values = [], href;
        obj.parent().find('ul li').each(function () {
            if ($(this).find('input').is(':checked')) {
                values.push($(this).find('input').val());
            }
        });

        if (values.length > 0) {
            href = obj.find('a:first').attr('accesskey');
            href = href.replace('[replace]', values.join(','));

            obj.find('a:first').attr('href', href);
            obj.find('span').hide();
            obj.removeClass('dark single');
            obj.find('a:first').show();

            if (empty) {
                obj.find('a:last').hide();
                obj.find('span').html(lang.cf_apply_filter);
            }
        } else {
            obj.find('a:first').hide();
            obj.addClass('dark single');
            obj.find('span').show();

            if (empty) {
                obj.find('a:last').show();
                obj.removeClass('single');
                obj.find('span').html(lang.cf_remove_filter);
            }
        }
    };

    /**
     * Handler for enabling/disabling apply button in search (text) fields
     * @param {object} obj    - Dom container with fields
     * @param {bool}   values - Detect selected values
     */
    this.textFields = function (obj, values) {
        var $applyButton, $inactiveButton, $fromField, $toField, $currencyField;

        $applyButton    = obj.find('a:first');
        $inactiveButton = !values ? obj.find('span') : obj.find('a.cf-remove');
        $fromField      = obj.closest('.submit-cell').find('input[name="from"]');
        $toField        = obj.closest('.submit-cell').find('input[name="to"]');
        $currencyField  = obj.closest('.submit-cell').find('select[name="currency"]');

        if (!values) {
            $applyButton.hide();
            $inactiveButton.show();
        }

        $applyButton.click(function () {
            var min, max, url, currency;

            min = parseFloat($fromField.val());
            max = parseFloat($toField.val());

            if (min >= 0 && max > 0) {
                url      = $applyButton.attr('accesskey').replace('[replace]', min + '-' + max);
                url      += rlConfig.mod_rewrite ? '/' : '';
                currency = $currencyField.length ? $currencyField.find('option:selected').val() : '';

                if (currency && currency !== '0') {
                    if (rlConfig.mod_rewrite) {
                        url += 'currency:' + currency + '/';
                    } else {
                        url += '&cf-currency:' + currency;
                    }
                }

                window.location.href = url;
            }
        });

        obj.closest('.submit-cell').find('input').keyup(function () {
            var min, max;

            min = parseFloat($fromField.val());
            max = parseFloat($toField.val());

            $inactiveButton[min >= 0 && max > 0 && max > min ? 'hide' : 'show']();
            $applyButton[min >= 0 && max > 0 && max > min ? 'show' : 'hide']();
        });
    };

    /**
     * Slider constructor
     * @since  2.7.0
     * @param  {object} options - Properties for building of slider
     *                          - Required: [key, from, to, step, minExist, maxExist, countsData]
     */
    this.slider = function (options) {
        var $field, $filterBlock, $counter, $applyButton, $emptyBlock;

        options.key = options.key.replace('-', '_');

        if (!options.key || !options.countsData) {
            console.log("Filter error: slider doesn't have required data.");
        } else {
            $field = $('input[name=slider_' + options.key + ']');

            if (!$field.length) {
                console.log("Filter error: input for slider doesn't exist.");
            }
        }

        $filterBlock = $('div#cf_link_' + options.key);
        $counter     = $filterBlock.find('span.counter');
        $applyButton = $filterBlock.find('a');
        $emptyBlock  = $filterBlock.find('span.empty').hide();

        if (rlConfig['mod_rewrite']) {
            options.pattern = new RegExp(options.key.replace('_', '-') + '[\:]([^/]+)');
        } else {
            options.pattern = new RegExp('cf-' + options.key.replace('_', '-') + '[\=]([^/]+)');
        }

        $field.slider({
            from         : options.from,
            to           : options.to,
            step         : options.step,
            skin         : 'round_plastic',
            raund        : 0,
            limits       : true,
            format       : options.format || {},
            dimension    : options.dimension || '',
            onstatechange: function (value) {
                var data = value.split(';'), start, finish, total = 0, i, count, price, sign, countValue, replace;

                start  = Number(data[0]);
                finish = Number(data[1]);

                // Count total count by selected range
                for (i = 0; i <= options.countsData.length; i++) {
                    if (options.countsData[i]) {
                        count = Number(options.countsData[i][0]);
                        price = Number(options.countsData[i][1]);

                        if (price >= start && price <= finish && count) {
                            total += count;
                        }
                    }
                }

                // Add real count
                countValue = '(' + total + ')';

                // Update total count in filter box
                if (total > 0) {
                    // Add "plus" to span with counter
                    if ((options.minExist !== -1 && options.maxExist !== -1)
                        && (start < options.minExist || finish > options.maxExist)
                    ) {
                        countValue = '(' + total + '+)';
                    }

                    $counter.html(countValue);

                    sign    = rlConfig['mod_rewrite'] ? ':' : '=';
                    replace = !rlConfig['mod_rewrite'] ? 'cf-' : '';
                    replace += options.key.replace('_', '-') + sign + start + '-' + finish;

                    $applyButton.attr('href', $applyButton.attr('href').replace(options.pattern, replace));

                    if (!$applyButton.is(':visible')) {
                        $emptyBlock.hide();
                        $applyButton.show();
                    }
                } else {
                    $counter.html(countValue);

                    if ($applyButton.is(':visible')) {
                        $applyButton.hide();
                        $emptyBlock.html(lang.cf_apply_filter).show();
                    }
                }
            }
        });
    };
};

var categoryFilter = new CategoryFilterClass();
