<!-- categorires carousel -->

{if $category_menu}
    {assign var='category_parent_ids' value=','|explode:$category.Parent_IDs}

    <section class="categories__block">
        <div class="categories__container point1">
            <div class="owl-carousel categories__carousel owl-hidden content-padding row">
                {foreach from=$category_menu item='item' key='iteration'}
                    {if $item.isListingType}
                        {assign var='title_key' value='pages+title+lt_'|cat:$item.Type}
                    {else}
                        {assign var='title_key' value='categories+title+'|cat:$item.Key}
                    {/if}

                    <div class="col-md-4 col-lg-3 categories__item">
                        <a href="{$item.href}"
                           class="categories__link d-flex align-items-center"
                           data-iteration="{$iteration}"
                           data-id="{if $item.isListingType}0{else}{$item.ID}{/if}"
                           data-has-subcategories="{if $item.isListingType || $item.ID|in_array:$category_menu_parents}1{else}0{/if}"
                           data-listing-type="{$item.Type}"
                           {if $lang.$title_key}title="{$lang.$title_key}"{/if}>
                            {if $item.icon && is_readable($item.icon)}
                                <span class="categories__item-icon">{fetch file=$item.icon}</span>
                            {/if}
                            <span class="text-overflow">{$item.Name}</span>
                        </a>
                    </div>
                {/foreach}
            </div>
        </div>
    </section>

    <script id="categorires-menu-box" type="text/x-jsrender">
        <div class="col-md-12 hide" id="cc_box_[%:box_id%]">
            <div class="sub-categories__container relative">
                <span class="sub-categories__arrow"></span>
                <header><a href="[%:href%]">[%:caption%]</a></header>
                <div class="sub-categories__body">{$lang.loading}</div>
            </div>
        </div>
    </script>

    <script id="categorires-menu-item" type="text/x-jsrender">
        <div class="sub-categories__item">
            <span class="sub-categories__parent"><a href="[%:link%]">[%:name%][%if show_count %]<span>[%:Count%][%/if%]</span></a></span>

            [%for sub_categories%]
                <span><a href="[%:link%]">[%:name%]</a></span>
            [%/for%]
        </div>
    </script>

    <script class="fl-js-dynamic">
    lang['view_all_listings_in_category'] = '{$lang.view_all_listings_in_category}';
    lang['listing_type_no_categories'] = '{$lang.listing_type_no_categories}';
    {literal}

    flUtil.loadScript(rlConfig['libs_url'] + 'javascript/jsRender.js', function(){
        var extra_large_desktop = false;
        var $parent = $('.categories__carousel');

        var getPerLine = function(){
            if (media_query == 'desktop') {
                return extra_large_desktop ? 5 : 4;
            } else if (media_query == 'tablet') {
                return 3;
            }
        }

        var openBox = function($link, $box){
            $parent.find('.col-md-12:not(.hide)').addClass('hide');

            if ($link.hasClass('categories__link-active')) {
                $link.removeClass('categories__link-active');
            } else {
                $parent.find('.categories__link-active').removeClass('categories__link-active');
                $box.removeClass('hide');
                $link.addClass('categories__link-active');
            }
        }

        var setPosition = function(){
            var $link = $parent.find('.categories__link-active');

            if (!$link.length) {
                return;
            }

            var $box = $parent.find('#cc_box_' + $link.data('iteration'));
            var $items = $parent.find('> .categories__item');

            var per_line = getPerLine();
            var index = $items.index($link.parent()) + 1;
            var on_line = Math.ceil(index / 4);
            var box_position = (on_line * per_line) + 1;
            var position = 1;

            // Set order
            $items.css('order', 'unset');
            $box.css('order', box_position);

            $items.each(function(){
                if (position == box_position) {
                    position++;
                }

                $(this).css('order', position);
                position++;
            });

            // Set arrow position
            $arrow = $('.sub-categories__arrow');
            var left = $link.parent().position().left;
            left += $link.find('span:last').width() / 2;
            left += 10;
            $arrow.css('transform', 'translateX(' + left + 'px)')
        }

        $('.categories__link').click(function(){
            var $link = $(this);
            var box_id = $link.data('iteration');

            if (!$link.data('has-subcategories') || media_query == 'mobile') {
                return true;
            }

            if (!$parent.find('#cc_box_' + box_id).length) {
                $(this).trigger('mouseenter');
            }

            openBox($link, $parent.find('#cc_box_' + box_id));
            setPosition();

            return false;
        }).mouseenter(function(){
            var $link = $(this);
            var name = $link.find('> span:last').text();
            var href = $link.attr('href');
            var box_id = $link.data('iteration');

            if (!$link.data('has-subcategories') || media_query == 'mobile') {
                return true;
            }

            if (!$parent.find('#cc_box_' + box_id).length) {
                // Create box
                var box_data = {
                    caption: lang['view_all_listings_in_category'].replace('{name}', '<span>' + name + '</span>'),
                    href: href,
                    box_id: box_id
                };
                var html = $('#categorires-menu-box').render(box_data);
                $parent.append(html);

                var $box = $parent.find('#cc_box_' + box_id);

                // Get box categories
                var data = {
                    mode: 'novaGetCategories',
                    lang: rlLang,
                    parent_id: $link.data('id'),
                    type: $link.data('listing-type')
                };
                flUtil.ajax(data, function(response, status){
                    if (status == 'success' && response.status == 'OK') {
                        if (response.results.length) {
                            var has_subcategories = false;
                            for (var i in response.results) {
                                if (parseInt(response.results[i].sub_categories_calc) > 0) {
                                    has_subcategories = true;
                                    break;
                                }
                            }

                            $box.find('.sub-categories__body')
                                .empty()
                                .append(
                                    $('#categorires-menu-item').render(response.results)
                                );

                            if (!has_subcategories) {
                                $box.find('.sub-categories__parent').removeClass('sub-categories__parent');
                            }
                        } else {
                            $box.find('.sub-categories__body')
                                .empty()
                                .append(lang['listing_type_no_categories'])
                        }
                    } else {
                        printMessage('error', lang['system_error']);
                    }
                });
            }
        });

        enquire.register('screen and (max-width: 767px)', {
            match: function(){
                flUtil.loadScript(rlConfig['tpl_base']  + 'js/owl.carousel.min.js', function(){
                    $('.owl-carousel').owlCarousel({
                        loop: true,
                        rtl: rlLangDir == 'rtl' ? true : false,
                        margin: 0,
                        nav: false,
                        dots: false,
                        navText: [],
                        navElement: 'div',
                        responsive: {
                           0: {
                               items: 2
                           },
                           380: {
                               items: 2.3
                           },
                           480: {
                               items: 3.3
                           }
                       }
                    });

                    $('.owl-carousel .item').removeClass(function (index, className) {
                        return (className.match (/(^|\s)col-\S+/g) || []).join(' ');
                    });

                    $('.owl-hidden').removeClass('owl-hidden');
                });
            },
            unmatch: function(){
                $('.owl-carousel').owlCarousel('destroy');
            }
        }).register('screen and (min-width: 1440px)', {
            match: function(){
                extra_large_desktop = true;
                setPosition();
            },
            unmatch: function(){
                extra_large_desktop = false;
                setPosition();
            }
        });
    });

    {/literal}
    </script>
{/if}

<!-- categorires carousel -->
