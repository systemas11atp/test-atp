/**
 * 2007-2016 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2016 PrestaShop SA
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

$(document).ready(function() {

    if ( $('body > main').hasClass( "animsition" ) ) {
    $(".animsition").animsition({
        inClass: 'fade-in',
        outClass: 'fade-out',
        inDuration: 600,
        outDuration: 600,
        linkElement: '.animsition-link',
        // e.g. linkElement: 'a:not([target="_blank"]):not([href^=#])'
        loading: false, // display loading icon or not
        loadingParentElement: 'body', //animsition wrapper element
        loadingClass: 'animsition-loading',
        timeout: true,
        timeoutCountdown: 2000,
        onLoadEvent: true,
        browser: [ 'animation-duration', '-webkit-animation-duration'],
        // "browser" option allows you to disable the "animsition" in case the css property in the array is not supported by your browser.
        // The default setting is to disable the "animsition" in a browser that does not support "animation-duration".
        overlay : false,
        overlayClass : 'animsition-overlay-slide',
        overlayParentElement : 'body',    
      });
    }
    
    // add submenu class
    $('#_desktop_top_menu ul#top-menu li').has('ul').children('a').addClass('submenu');

	// show search-bar 
	$("#header #mobile_search").click(function () {
		$("#search_widget").slideToggle();
	});

    /* product sliders */
    var owl = $("#content-wrapper .featured-products div.products.owl-carousel, #order-confirmation #content-wrapper div.products, #content-wrapper .special-products div.products.owl-carousel, #content-wrapper .new-products div.products.owl-carousel, .wp-crossseling div.products, .wp-categoryproducts div.products, #product .product-accessories div.products, .wp-category-slider div.products.owl-carousel"); 
        owl.owlCarousel({
        responsive:{
            0:{
                items:1,
                slideBy : 1
            },
            430:{
                items:2,
                slideBy : 2
            },
            992:{
                items:3,
                slideBy : 3
            },
            1600:{
                items:4,
                slideBy : 4
            }
        },
        margin: 20, 
        nav: true,
        lazyLoad: true,
        dots: false,
        navText: [],
    });
    

	bindGrid(); 

	function bindGrid()
	{   

    var default_view = 'grid'; // choose the view to show by default (grid/list)
    var body_attr = $('body').attr('id');
    // check the presence of the cookie, if not create "view" cookie with the default view value
    if($.cookie('view') == null ){
        $.cookie('view', default_view, { expires: 7, path: '/' });
    } 
    function get_grid() {
	        $('#list').removeClass('active');
	        $('#grid').addClass('active');
            $('.products.row article.product-miniature').not('#index .products.row article.product-miniature').removeClass('prod-box-list col-xs-12').addClass('prod-box-grid col-xs-12 col-sm-6 col-lg-4');
    } // end "get_grid" function
    function get_list() {
	        $('#grid').removeClass('active');
	        $('#list').addClass('active');
            $('.products.row article.product-miniature').not('#index .products.row article.product-miniature').removeClass('prod-box-grid col-xs-12 col-sm-6 col-lg-4').addClass('prod-box-list col-xs-12');
    } // end "get_list" function
    if($.cookie('view') == 'list') { 
	        // we dont use the "get_list" function here to avoid the animation
	        $('#grid').removeClass('active');
	        $('#list').addClass('active');
	        $('.products.row article.product-miniature').not('#index .products.row article.product-miniature').removeClass('prod-box-grid col-xs-12 col-sm-6 col-lg-4').addClass('prod-box-list col-xs-12');
    } 

 	if($.cookie('view') == 'grid') { 
	 		get_grid(); 
 	}

    $('body').on("click", '#list', function() {
    	$.cookie('view', 'list'); 
        get_list()
    });

    $('body').on("click", '#grid', function() {
    	$.cookie('view', 'grid'); 
        get_grid();
    });   
    
    }


});	

