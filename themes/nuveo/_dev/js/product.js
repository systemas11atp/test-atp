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
import ProductSelect from './components/product-select';

$(document).ready(function () {
  createProductSpin();
  createInputFile();
  coverImage();
  imageScrollBox();

  prestashop.on('updatedProduct', function (event) {
    createInputFile();
    coverImage();
    if (event && event.product_minimal_quantity) {
      const minimalProductQuantity = parseInt(event.product_minimal_quantity, 10);
      const quantityInputSelector = '#quantity_wanted';
      let quantityInput = $(quantityInputSelector);

      // @see http://www.virtuosoft.eu/code/bootstrap-touchspin/ about Bootstrap TouchSpin
      quantityInput.trigger('touchspin.updatesettings', {min: minimalProductQuantity});
    }
    imageScrollBox();
    $($('.tabs .nav-link.active').attr('href')).addClass('active').removeClass('fade');
    $('.js-product-images-modal').replaceWith(event.product_images_modal);

    let productSelect  = new ProductSelect();
    productSelect.init();
  });

  function coverImage() {
    $('.js-thumb').on(
      'mouseover',
      (event) => {
        $('.js-modal-product-cover').attr('src',$(event.target).data('image-large-src'));
        $('.selected').removeClass('selected');
        $(event.target).addClass('selected');
        $('.js-qv-product-cover').prop('src', $(event.currentTarget).data('image-large-src'));
      }
    );
  }

  function imageScrollBox()
  {

    if ($('.js-qv-product-images li').length > 2) {
      $('.js-qv-mask').addClass('scroll');
      // $('.scroll-box-arrows').addClass('scroll');       
    } else {
      $('.js-qv-mask').removeClass('scroll');
      // $('.scroll-box-arrows').removeClass('scroll');
    }

  /* thumbnails slider */
  var owl2 = $(".js-qv-mask ul.js-qv-product-images"); 
     owl2.on({
     'initialized.owl.carousel': function () {
  }
}).owlCarousel({
 responsive:{
        0:{
            items:2,
            slideBy : 2
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

    /* Arrows on product page */
        $('.scroll-box-arrows .left').click(function () {
           owl2.trigger('prev.owl.carousel');
        });
        $('.scroll-box-arrows .right').click(function () {
          owl2.trigger('next.owl.carousel');
    }); 
  }

  function createInputFile()
  {
    $('.js-file-input').on('change', (event) => {
      let target, file;

      if ((target = $(event.currentTarget)[0]) && (file = target.files[0])) {
        $(target).prev().text(file.name);
      }
    });
  }

  function createProductSpin()
  {
    const $quantityInput = $('#quantity_wanted');

    $quantityInput.TouchSpin({
      verticalbuttons: true,
      verticalupclass: 'material-icons touchspin-up',
      verticaldownclass: 'material-icons touchspin-down',
      buttondown_class: 'btn btn-touchspin js-touchspin',
      buttonup_class: 'btn btn-touchspin js-touchspin',
      min: parseInt($quantityInput.attr('min'), 10),
      max: 1000000
    });

    $('body').on('change keyup', '#quantity_wanted', (e) => {
      $(e.currentTarget).trigger('touchspin.stopspin');
      prestashop.emit('updateProduct', {
          eventType: 'updatedProductQuantity',
          event: e
      });
    });
  }
});
