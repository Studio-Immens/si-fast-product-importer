

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






function FP_search_product(){
    jQuery('.FPDetailSection').hide();
    jQuery('.FPBackGroundSection').hide();
    console.log('FP_search_product');

    var categories = jQuery(".FP_categories").val();
        if ( categories == null ){ categories = ''; }
    var s = jQuery(".FP_keyword").val();
        if ( s == null ){ s = ''; }
    var url = 'https://flashproducts.innovazioneweb.com/wp-json/flash_products/v1/products?categories='+categories+'&s='+s;

    jQuery.get( url, function( data ) {
        console.log(data.message);
        console.log(data.result);

        var clone = jQuery('.FPdefaultCard').clone();
        jQuery(".FPContainer").empty();
        jQuery(".FPContainer").append(clone);

        FPloopProducts(data.result);
    });
}

function FPloopProducts( products ){

    jQuery(products).each(function(i,e){
        console.log(e);

        var copy = jQuery('.FPdefaultCard').clone();
        copy.removeClass('FPdefaultCard');

        copy.find('.FPCardTitle').text( e.post_title );

        copy.attr('fp_title', e.post_title);
        copy.attr('fp_short_title', e.short_title);
        copy.attr('fp_slang_title', e.slang_title);

        copy.attr('fp_description', e.post_content);
        copy.attr('fp_exerp', e.post_excerpt);
        copy.attr('fp_categories', e.product_cat);
        copy.attr('fp_tag', e.product_tag);
        copy.attr('fp_ingredient', e.Ingredienti);
        copy.attr('fp_macro_cat', e.macro_categories);
        copy.attr('fp_allerg', e.Allergeni);
        copy.attr('fp_sticker', e.Sticker);
        copy.attr('fp_temp', e.Temperature);

            // _product_attributes

        // copy.attr('fp_img', element.post_name);
        // copy.attr('fp_gallery', element.post_name);

        jQuery(".FPContainer").append(copy);
    });
}




