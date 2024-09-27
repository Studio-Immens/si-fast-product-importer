

function FP_Open_Detail(input){
    jQuery('.FPDetailSection').slideToggle();
    jQuery('.FPBackGroundSection').show();

    jQuery('.FPDetailHead strong').text(jQuery(input).attr('fp_title'));
    jQuery('p[fp-block="description"]').text(jQuery(input).attr('fp_description'));
    jQuery('p[fp-block="exerpt"]').text(jQuery(input).attr('fp_exerp'));

    FP_Create_Detail_tax_cloud( input, 'fp_macro_cat' );
    FP_Create_Detail_tax_cloud( input, 'fp_categories' );
    FP_Create_Detail_tax_cloud( input, 'fp_tag' );
    FP_Create_Detail_tax_cloud( input, 'fp_ingredient' );

    FP_Create_Detail_tax_cloud( input, 'fp_allerg' );
    FP_Create_Detail_tax_cloud( input, 'fp_sticker' );
    FP_Create_Detail_tax_cloud( input, 'fp_temp' );

    // jQuery('strong').text(jQuery(input).attr('fp_img'));
    // jQuery('strong').text(jQuery(input).attr('fp_gallery'));
}

function FP_Close_Detail(input){
    jQuery('.FPDetailSection').slideToggle();
    jQuery('.FPBackGroundSection').hide();
}

function FP_Create_Detail_tax_cloud( input, key ){
    jQuery('div[fp-block="'+key+'"]').empty();
    if (jQuery(input).attr(''+key+'') == '') {
        jQuery('div[fp-block="'+key+'"]').parent().hide();
    } else{
        jQuery('div[fp-block="'+key+'"]').parent().show();
        var fp_categories = jQuery(input).attr(''+key+'').split(',');
        fp_categories.forEach(element => {
            jQuery('div[fp-block="'+key+'"]').append('<div class="PFCloud">'+element+'</div>');
        });
    }
}

function FP_Import_product(input){
    // jQuery('.FPDetailSection').hide();
    // jQuery('.FPBackGroundSection').hide();
    console.log('FP_Import_product');
}













