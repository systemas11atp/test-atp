/* global $ */
$(document).ready(function () {
  /*  var $searchWidget = $('#search_widget');
    var $searchBox    = $searchWidget.find('input[type=text]');
    var searchURL     = $searchWidget.attr('data-search-controller-url');

    $.widget('prestashop.psBlockSearchAutocomplete', $.ui.autocomplete, {
        _renderItem: function (ul, product) {
            var wp_img_cvr = prestashop.urls.no_picture_image.bySize.small_default.url;
                if (typeof product.cover !== 'undefined' && product.cover !== null){
                    wp_img_cvr = product.cover.small.url;
                }
            return $("<li>")
                .append($("<a>")
                    .append($('<img>').attr('src',wp_img_cvr).addClass("search-img"))
                    .append($("<span>").html(product.category_name).addClass("category"))
                    .append($("<span>").html(product.name).addClass("search-product-name"))
                    .append($("<span>").html(product.price).addClass("search-price"))
                ).appendTo(ul)
            ;
        }
    });

    $searchBox.psBlockSearchAutocomplete({
        source: function (query, response) {
            $.post(searchURL, {
                s: query.term,
                resultsPerPage: 10
            }, null, 'json')
            .then(function (resp) {
                response(resp.products);
            })
            .fail(response);
        },
        select: function (event, ui) {
            var url = ui.item.url;
            window.location.href = url;
        },
    });*/
});