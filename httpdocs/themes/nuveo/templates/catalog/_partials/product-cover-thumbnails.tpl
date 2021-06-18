{**
 * 2007-2019 PrestaShop
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
 * @copyright 2007-2019 PrestaShop SA
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 *}
<div class="images-container">
  {block name='product_cover'}
    {if $product.default_image}
    <div class="product-cover">
      <div data-target="#product-modal">
        <div class="easyzoom easyzoom--overlay easyzoom--with-thumbnails">     
            <a class="zoomed" href="{$product.default_image.bySize.large_default.url}" data-toggle="modal">
              <img class="js-qv-product-cover" src="{$product.default_image.bySize.large_default.url}" alt="{$product.default_image.legend}" title="{$product.default_image.legend}" style="width:100%;">
            </a>
        </div>      
      </div>
    </div>
    {else}
      <div class="product-cover noimg">
        <img src="{$urls.no_picture_image.bySize.large_default.url}">
      </div>
    {/if}
    
  {/block}

  {block name='product_images'}
    <div class="wp-gallery {if $product.images|count < 2}hidden-xs-up{/if}">
      <div class="js-qv-mask mask">
        <ul class="product-images js-qv-product-images owl-carousel">
          {foreach from=$product.images item=image}
            <li class="thumb-container">
              <figure class="wpgallery-assoc">
                <!--href="{$image.bySize.large_default.url}"-->
                <a  data-size="{$image.bySize.large_default.width}x{$image.bySize.large_default.height}" title="{if $image.legend}{$image.legend}{else}{$product.name}{/if}" onmouseover="cambiarImagen({$image.id_image})" 
                id={$image.id_image} name={$image.id_image}>
                  <img
                    class="lazyload thumb js-thumb {if $image.id_image == $product.default_image.id_image} selected {/if}"
                    data-image-medium-src="{$image.bySize.medium_default.url}"
                    data-image-large-src="{$image.bySize.large_default.url}"
                    data-src="{$image.bySize.home_default.url}"
                    alt="{$image.legend}"
                    title="{$image.legend}"
                  >
                </a>
              </figure>
            </li>
          {/foreach}
        </ul>
      </div>
    </div>
  {/block}
  {hook h='displayAfterProductThumbs'}
</div>

