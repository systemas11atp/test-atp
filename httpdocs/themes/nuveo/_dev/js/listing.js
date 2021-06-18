/**
 * 2007-2018 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
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
 * @copyright 2007-2018 PrestaShop SA
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */
import $ from 'jquery';
import prestashop from 'prestashop';
import 'velocity-animate';

import ProductMinitature from './components/product-miniature';

$(document).ready(() => {
  prestashop.on('clickQuickView', function (elm) {
    let data = {
      'action': 'quickview',
      'id_product': elm.dataset.idProduct,
      'id_product_attribute': elm.dataset.idProductAttribute,
    };
    $.post(prestashop.urls.pages.product, data, null, 'json').then(function (resp) {
      $('body').append(resp.quickview_html);
      let productModal = $(`#quickview-modal-${resp.product.id}-${resp.product.id_product_attribute}`);
      productModal.modal('show');      
      productConfig(productModal);
      productModal.on('shown.bs.modal', function () {
      var owl2 = $(".js-qv-mask ul.js-qv-product-images"); 
      owl2.owlCarousel({
        responsive:{
          0:{
              items:3,
              slideBy : 3
          },
          400:{
              items:3,
              slideBy : 3
          },            
          768:{
              items:3,
              slideBy : 3
          },
          992:{
              items:3,
              slideBy : 3
          }
        },
          margin: 10, 
          nav: true,     
          dots: false,
          navText: [],
          responsiveBaseElement: "#header .container"
        });
      });
      productModal.on('hidden.bs.modal', function () {
        productModal.remove();
      });
    }).fail((resp) => {
      prestashop.emit('handleError', {eventType: 'clickQuickView', resp: resp});
    });
  });

  var productConfig = (qv) => {
    const MAX_THUMBS = 4;
    var $arrows = $('.scroll-box-arrows');
    var $thumbnails = qv.find('.js-qv-product-images');
    $('.js-thumb').on('click', (event) => {
      if ($('.js-thumb').hasClass('selected')) {
        $('.js-thumb').removeClass('selected');
      }
      $(event.currentTarget).addClass('selected');
      $('.js-qv-product-cover').attr('src', $(event.target).data('image-large-src'));
    });
    if ($thumbnails.find('li').length <= MAX_THUMBS) {
      $arrows.hide();
    } else {
      $arrows.on('click', (event) => {
        if ($(event.target).hasClass('left')) {
          owl2.trigger('prev.owl.carousel');          
        } else if ($(event.target).hasClass('right')) {
          owl2.trigger('next.owl.carousel');
        }
      });
    }

    // prevent click on modal window
    $('body').on('click', '.modal-body .product-cover .zoomed', function (e) {
      e.preventDefault();
    });

    // add space for arrows
    if ($('.js-qv-product-images li').length > 2) {
      $('.js-qv-mask').addClass('scroll');
      // $('.scroll-box-arrows').addClass('scroll');        
    } else {
      $('.js-qv-mask').removeClass('scroll');
      // $('.scroll-box-arrows').removeClass('scroll');
    }
  



    qv.find('#quantity_wanted').TouchSpin({
      verticalbuttons: true,
      verticalupclass: 'material-icons touchspin-up',
      verticaldownclass: 'material-icons touchspin-down',
      buttondown_class: 'btn btn-touchspin js-touchspin',
      buttonup_class: 'btn btn-touchspin js-touchspin',
      min: 1,
      max: 1000000
    });
  };
  var move = (direction) => {
    const THUMB_MARGIN = 20;
    var $thumbnails = $('.js-qv-product-images');
    var thumbHeight = $('.js-qv-product-images li img').height() + THUMB_MARGIN;
    var currentPosition = $thumbnails.position().top;
    $thumbnails.velocity({
      translateY: (direction === 'up') ? currentPosition + thumbHeight : currentPosition - thumbHeight
    }, function () {
      if ($thumbnails.position().top >= 0) {
        $('.arrow-up').css('opacity', '.2');
      } else if ($thumbnails.position().top + $thumbnails.height() <= $('.js-qv-mask').height()) {
        $('.arrow-down').css('opacity', '.2');
      }
    });
  };
  $('body').on('click', '#search_filter_toggler', function () {
    $('#search_filters_wrapper').removeClass('hidden-lg-down');
    $('#content-wrapper').addClass('hidden-lg-down');
    $('#footer, #wpFooter').addClass('hidden-lg-down');
  });
  $('#search_filter_controls .clear').on('click', function () {
    $('#search_filters_wrapper').addClass('hidden-lg-down');
    $('#content-wrapper').removeClass('hidden-lg-down');
    $('#footer, #wpFooter').removeClass('hidden-lg-down');
  });
  $('#search_filter_controls .ok').on('click', function () {
    $('#search_filters_wrapper').addClass('hidden-lg-down');
    $('#content-wrapper').removeClass('hidden-lg-down');
    $('#footer, #wpFooter').removeClass('hidden-lg-down');
  });

  const parseSearchUrl = function (event) {
    if (event.target.dataset.searchUrl !== undefined) {
      return event.target.dataset.searchUrl;
    }

    if ($(event.target).parent()[0].dataset.searchUrl === undefined) {
      throw new Error('Can not parse search URL');
    }

    return $(event.target).parent()[0].dataset.searchUrl;
  };

  $('body').on('change', '#search_filters input[data-search-url]', function (event) {
    prestashop.emit('updateFacets', parseSearchUrl(event));
  });

  $('body').on('click', '.js-search-filters-clear-all', function (event) {
    prestashop.emit('updateFacets', parseSearchUrl(event));
  });

  $('body').on('click', '.js-search-link', function (event) {
    event.preventDefault();
    prestashop.emit('updateFacets', $(event.target).closest('a').get(0).href);
  });

  $('body').on('change', '#search_filters select', function (event) {
    const form = $(event.target).closest('form');
    prestashop.emit('updateFacets', '?' + form.serialize());
  });

  /* web-plus - scroll only after pagination */  
    /* const wpscrollpoint = document.getElementById("top-list-bar"); */
    $('body').on('click', '.page-list', function (event) {
      window.scrollTo(0, 0);
      /* wpscrollpoint.scrollIntoView({behavior: 'smooth', block: 'start', inline: 'start'}); */
    });

  prestashop.on('updateProductList', (data) => {
    updateProductListDOM(data);
    //window.scrollTo(0, 0);

    /* template mod */
    var body_attr = $('body').attr('id');
    function get_grid(){
          $('#list').removeClass('active');
          $('#grid').addClass('active');
          $('.products.row article.product-miniature').not('#index .products.row article.product-miniature').removeClass('prod-box-list col-xs-12').addClass('prod-box-grid col-xs-12 col-sm-6 col-lg-4');
    }
    
    if($.cookie('view') == 'list'){ 
          // we dont use the "get_list" function here to avoid the animation
          $('#grid').removeClass('active');
          $('#list').addClass('active');
          $('.products.row article.product-miniature').not('#index .products.row article.product-miniature').removeClass('prod-box-grid col-xs-12 col-sm-6 col-lg-4').addClass('prod-box-list col-xs-12');
    }

    if($.cookie('view') == 'grid'){ 
        get_grid(); 
    }

  });
});

function updateProductListDOM (data) {
  $('#search_filters').replaceWith(data.rendered_facets);
  $('#js-active-search-filters').replaceWith(data.rendered_active_filters);
  $('#js-product-list-top').replaceWith(data.rendered_products_top);
  $('#js-product-list').replaceWith(data.rendered_products);
  $('#js-product-list-bottom').replaceWith(data.rendered_products_bottom);
  if (data.rendered_products_header) {
      $('#js-product-list-header').replaceWith(data.rendered_products_header);
  }

  let productMinitature = new ProductMinitature();
  productMinitature.init();

}
