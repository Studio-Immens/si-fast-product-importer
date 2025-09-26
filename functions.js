
function FP_Open_Detail(input){
    jQuery('.FPDetailSection').slideToggle();
    jQuery('.FPBackGroundSection').show();

    jQuery('.FPDetailHead strong').text(jQuery(input).attr('fp_title'));

    if (jQuery(input).attr('fp_short_title') == '') { 
        jQuery('p[fp-block="short_title"]').parent().hide();
    } else{ 
        jQuery('p[fp-block="short_title"]').parent().show();
        jQuery('p[fp-block="short_title"]').text(jQuery(input).attr('fp_short_title')); 
    }
    if (jQuery(input).attr('fp_slang_title') == '') { 
        jQuery('p[fp-block="slang_title"]').parent().hide();
    } else{ 
        jQuery('p[fp-block="slang_title"]').parent().show(); 
        jQuery('p[fp-block="slang_title"]').text(jQuery(input).attr('fp_slang_title'));
    }
    if (jQuery(input).attr('fp_description') == '') { 
        jQuery('p[fp-block="description"]').parent().hide();
    } else{ 
        jQuery('p[fp-block="description"]').parent().show();
        jQuery('p[fp-block="description"]').text(jQuery(input).attr('fp_description')); 
    }
    if (jQuery(input).attr('fp_exerp') == '') { 
        jQuery('p[fp-block="exerpt"]').parent().hide();
    } else{ 
        jQuery('p[fp-block="exerpt"]').parent().show();
        jQuery('p[fp-block="exerpt"]').text(jQuery(input).attr('fp_exerp')); 
    }
    FP_Create_Detail_tax_cloud( input, 'fp_macro_cat' );
    FP_Create_Detail_tax_cloud( input, 'fp_categories' );
    FP_Create_Detail_tax_cloud( input, 'fp_tag' );
    FP_Create_Detail_tax_cloud( input, 'fp_ingredient' );

    FP_Create_Detail_tax_cloud( input, 'fp_allerg' );
    FP_Create_Detail_tax_cloud( input, 'fp_sticker' );
    FP_Create_Detail_tax_cloud( input, 'fp_temp' );

    jQuery('.FPDetailBodyImages').empty();
    var thumb = jQuery(input).attr('fp_img');
    jQuery('.FPDetailBodyImages').append( '<img src="'+thumb+'">' );

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
            if ( element != '' ) {
                jQuery('div[fp-block="'+key+'"]').append('<div class="PFCloud">'+element+'</div>');
            }
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
    var languages = jQuery(".FP_languages").val();
        if ( languages == null ){ languages = ''; }
    var orderby = jQuery(".FP_orderby").val();
        if ( orderby == null ){ orderby = ''; }
    var limit = jQuery(".FP_limit").val();
        if ( limit == null ){ limit = ''; }
    var offset = jQuery(".FP_offset").val();
        if ( offset == null ){ offset = ''; }
    var s = jQuery(".FP_keyword").val();
    console.log(s);
        if ( s == null ){ s = ''; }
    var url = 'https://flashproducts.studioimmens.com/wp-json/flash_products/v1/products?categories='+categories+'&s='+s+'&languages='+languages+'&orderby='+orderby+'&limit='+limit+'&offset='+offset;

    jQuery.get( url, function( data ) {
        console.log(data.message);
        console.log(data.result);

        var clone = jQuery('.FPdefaultCard').clone();
        jQuery(".FPContainer").empty();
        jQuery(".FPContainer").append(clone);
        
        jQuery(".FPfound").text(data.founds);

        FPloopProducts(data.result);
    });
}

function FPloopProducts( products ){

    jQuery(products).each(function(i,e){
        console.log(e);
        var macro_categories = '';
        var product_cat = '';
        var product_tag = '';

        var copy = jQuery('.FPdefaultCard').clone();
        copy.removeClass('FPdefaultCard');

        copy.find('.FPCardTitle').text( e.post_title );

        copy.attr('fp_title', e.post_title);
        copy.attr('fp_short_title', e.short_title);
        copy.attr('fp_slang_title', e.slang_title);

        copy.attr('fp_description', e.post_content);
        copy.attr('fp_exerp', e.post_excerpt);
        copy.attr('fp_ingredient', e.Ingredienti);
        copy.attr('fp_allerg', e.Allergeni);
        copy.attr('fp_sticker', e.Sticker);
        copy.attr('fp_temp', e.Temperature);

        if (e.macro_categories) {
            jQuery(e.macro_categories).each(function(ind,ele){
                macro_categories = macro_categories+','+ele.name;
            });
            copy.attr('fp_macro_cat', macro_categories);
        }
        if (e.product_cat) {
            jQuery(e.product_cat).each(function(ind,ele){
                product_cat = product_cat+','+ele.name;
            });
            copy.attr('fp_categories', product_cat);
        }
        if (e.product_tag) {
            jQuery(e.product_tag).each(function(ind,ele){
                product_tag = product_tag+','+ele.name;
            });
            copy.attr('fp_tag', product_tag);
        }

        if (e.thumbnail != '' && e.gallery.length !== 0 ) {
            copy.find('.FPCardHead img').attr('src', e.gallery[e.thumbnail].guid);
            copy.attr('fp_img', e.gallery[e.thumbnail].guid);
        }
        // copy.find('.FPCardHead img').attr('src', e.gallery[e.thumbnail].guid);
            // _product_attributes

        // copy.attr('fp_gallery', e.gallery);

        jQuery(".FPContainer").append(copy);
    });
}




