
subcats_container = $('#subcats-container');

$( ".cat-item-link" ).click(function(event) {
    // event.source().find('.header-subcats-list').css('display', 'block');
    event.preventDefault();
    event.stopPropagation();
    let url = location.protocol+'//'+location.hostname+'/'+$(event.currentTarget).attr('href')+'/';
    console.log(url);
    // $("#main_container").load(url);
    $.ajax({
        type: "GET",
        url: url,
        data: { },
        success: function(data){
            let elementID = '#main_container';
            let specificData =  $(data).find(elementID).html();
            $('#main_container').html(specificData);
        }
    });
    $('.header-cats.cats-item.active').removeClass('active');
    let button = $(event.currentTarget).parent();
    if ( !button.hasClass('active') ) {
        subcats_container.html('');
    }
    if (button.hasClass('inactive')) {
        button.removeClass('inactive');
    }

    // backButton = $('<a href="#" class="cats-back-button">&#8249;</a>');
    // $( ".cat-item.active" ).removeClass('active');
    button.addClass('active');
    // backButton.insertBefore($(event.currentTarget)).parent();
    // $(event.currentTarget).parent().find('.header-subcats-list').css('display', 'flex');

    let subcats = button.find('.header-subcats-list').clone();
    subcats_container.append(subcats);
    activateSubcats();


    // hideOthers();
});

function activateSubcats() {
    subcats_container.find('.header-subcats-item a').each(function(i, obj) {
        $(obj).click(function (event) {
            console.log('Yo')
            event.preventDefault();
            event.stopPropagation();

            // showCatsBack();
            loadContent($(obj));
        })
    });


}

function loadContent(button) {
    console.log(button);
    let url = 'https://www.svejetu.me/motors/cars/';

    $.ajax({
        type: "GET",
        url: url,
        data: { },
        success: function(data){
            let elementID = '#main_container';
            let specificData =  $(data).find(elementID);
            $('#main_container').html(specificData);
        }
    });
}

function showCatsBack() {


    $('.cat-item-link').each(function (i, obj) {
        // console.log($(obj).parent());
        let item = $(obj).parent();
        if ( !item.hasClass('active')) {
            let dimensions = item.data('size');
            console.log(dimensions);
            item.animate({
                height: dimensions.y+"px",
                width: dimensions.x+"px",

            }, {
                duration: 500,
                complete: function () {
                    $('.cats-item.active').removeClass('active');
                }
            });
        }
    });

}

function hideOthers() {
    $('.cat-item-link').each(function(i, obj) {
        var item = $(obj).parent();

        if ( !item.hasClass('active') ) {
            // console.log(item);
            item.addClass('inactive');
        }
    });
}