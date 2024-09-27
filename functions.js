



function FP_Open_Detail(input){
    jQuery('.FPDetailSection').slideToggle();
    jQuery('.FPBackGroundSection').show();

    jQuery('.FPDetailHead strong').text(jQuery(input).attr('fp_title'));
    jQuery('p[fp-block="description"]').text(jQuery(input).attr('fp_description'));
    jQuery('p[fp-block="exerpt"]').text(jQuery(input).attr('fp_exerp'));

    jQuery('div[fp-block="categories"]').empty();
    if (jQuery(input).attr('fp_categories') == '') {
        jQuery('div[fp-block="categories"]').hide();
    } else{
        jQuery('div[fp-block="categories"]').show();
        var fp_categories = jQuery(input).attr('fp_categories').split(',');
        fp_categories.forEach(element => {
            jQuery('div[fp-block="categories"]').append('<div class="PFCloud">'+element+'</div>');
        });
    }

    // jQuery('strong').text(jQuery(input).attr('fp_tag'));
    // jQuery('strong').text(jQuery(input).attr('fp_ingredient'));
    // jQuery('strong').text(jQuery(input).attr('fp_macro_cat'));
    // jQuery('strong').text(jQuery(input).attr('fp_allerg'));
    // jQuery('strong').text(jQuery(input).attr('fp_sticker'));
    // jQuery('strong').text(jQuery(input).attr('fp_temp'));

    // jQuery('strong').text(jQuery(input).attr('fp_img'));
    // jQuery('strong').text(jQuery(input).attr('fp_gallery'));
}

function FP_Close_Detail(input){
    jQuery('.FPDetailSection').slideToggle();
    jQuery('.FPBackGroundSection').hide();
}

















