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
{block name='product_miniature_item'}
<article class="product-miniature js-product-miniature prod-box-grid" data-id-product="{$product.id_product}" data-id-product-attribute="{$product.id_product_attribute}">
  <div class="thumbnail-container">
   
   <div class="left-block">
   
    {block name='product_thumbnail'}
      {if $product.default_image}
      <a href="{$product.url}" class="thumbnail product-thumbnail">
        <img
          data-src="{$product.default_image.bySize.home_default.url}"
          alt="{if !empty($product.default_image.legend)}{$product.default_image.legend}{else}{$product.name|truncate:60:'...'}{/if}"
          data-full-size-image-url="{$product.default_image.large.url}"
          class="owl-lazy"
        />
      </a>
      {else}
          <a href="{$product.url}" class="thumbnail product-thumbnail">
            <img src="{$urls.no_picture_image.bySize.home_default.url}" />
          </a>
      {/if}
    {/block}
    
           
    {include file='catalog/_partials/product-flags.tpl'}

      <div class="highlighted-informations{if !$product.main_variants} no-variants{/if} hidden-lg-down">
      {block name='quick_view'}
      <a href="#" class="quick-view" data-link-action="quickview">
        <i class="fa fa-search"></i> {l s='Quick view' d='Shop.Theme.Actions'}
      </a>  
      {/block}

      {block name='product_variants'}
        {if $product.main_variants}
          {include file='catalog/_partials/variant-links.tpl' variants=$product.main_variants}
        {/if}
      {/block}

    </div>
                
    
    </div>
    
    
    <div class="center-block">      
       
      <div class="product-description">

        {block name='product_reviews'}
          {hook h='displayProductListReviews' product=$product}
        {/block}

        {block name='product_name'}
        <h2 class="h3 product-title"><a href="{$product.url}">{$product.name|truncate:60:'...'}</a></h2>
      {/block}

        <div class="product-detail">
          <p>{$product.description_short|strip_tags:'UTF-8'|truncate:100:'...'}</p>
        </div>
    

    </div>

    </div>
   
   <div class="right-block">

      {block name='product_price_and_shipping'}
        {if $product.show_price}
          <div class="product-price-and-shipping">
            {if $product.has_discount}
              {hook h='displayProductPriceBlock' product=$product type="old_price"}

              <!--<span class="regular-price" aria-label="{l s='Regular price' d='Shop.Theme.Catalog'}">{$product.regular_price}</span>-->
              {assign var='priceVar' value=$product.price}
              {$priceVar = $priceVar|replace:'$':''}
              {$priceVar = $priceVar|replace:',':''}
              {$priceVar = $priceVar*1.02}
              {$priceVar = ($priceVar)|number_format:2:'.':','}
              <span class="regular-price" aria-label="{l s='Regular price' d='Shop.Theme.Catalog'}">${$priceVar}</span>
              {if $product.discount_type === 'percentage'}
                  <!--<span class="discount-percentage discount-product">{$product.discount_percentage}</span>-->
                  <span class="discount-percentage discount-product">2%</span>
                {elseif $product.discount_type === 'amount'}
                  <span class="discount-amount discount-product">{$product.discount_amount_to_display}</span>
              {/if}
            {/if}

            {hook h='displayProductPriceBlock' product=$product type="before_price"}

            <span class="price {if $product.has_discount}reduction{/if}" aria-label="{l s='Price' d='Shop.Theme.Catalog'}">{$product.price} <b style="font-size:0.5rem;">MXN</b></span>

            {hook h='displayProductPriceBlock' product=$product type='unit_price'}

            {hook h='displayProductPriceBlock' product=$product type='weight'}
          </div>
        {/if}
      {/block}

    {block name='product_availability'}
    {if $product.availability_message}
      <div id="product-availability">
        {if $product.show_availability && $product.availability_message}
          <span class="{if $product.availability == 'available'}product-available{elseif $product.availability == 'last_remaining_items'}product-last-items{else}product-unavailable{/if}">
            {$product.availability_message}
            </span>
        {/if}
      </div>
    {/if}      
    {/block} 



    {block name='product_actions'}    
    <div class="product-actions">
      <a class="btn btn-primary view" href="{$product.url}">{l s='View Detail' d='Shop.Theme.Actions'}</a>
      {include file="catalog/_partials/miniatures/cat-add-to-cart.tpl"}
    </div>  
    {/block} 

     
  
  </div>    
  </div>

</article>
{/block}
