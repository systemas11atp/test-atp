{**
 * 2007-2016 PrestaShop
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
 * @copyright 2007-2016 PrestaShop SA
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 *}

 <div class="product-item">
   <div class="left-block">
    {block name='product_thumbnail'}
    {if $product.default_image}
    <a href="{$product.url}" class="thumbnail product-thumbnail rc ratio1_1">
      <img
      data-src="{$product.default_image.bySize.cart_default.url}"
      alt="{if !empty($product.default_image.legend)}{$product.default_image.legend}{else}{$product.name|truncate:60:'...'}{/if}"
      data-full-size-image-url="{$product.default_image.large.url}"
      class="lazyload"
      />
    </a>
    {else}
    <a href="{$product.url}" class="thumbnail product-thumbnail">
      <img src="{$urls.no_picture_image.bySize.cart_default.url}" />
    </a>
    {/if}
    {/block}
  </div>

  <div class="right-block">
    <div class="product-description">
      {block name='product_name'}
      <h2 class="h3 product-title"><a href="{$product.url}">{$product.name|truncate:60:'...'}</a></h2>
      {/block}

      {block name='product_price_and_shipping'}
      {if $product.show_price}
      <div class="product-price-and-shipping">
        {if $product.has_discount}
        {hook h='displayProductPriceBlock' product=$product type="old_price"}
        {assign var='priceVar' value=$product.price}
        {$priceVar = $priceVar|replace:'$':''}
        {$priceVar = $priceVar|replace:',':''}
        {$priceVar = $priceVar*1.02}
        {$priceVar = ($priceVar)|number_format:2:'.':','}
        <span class="regular-price" aria-label="{l s='Regular price' d='Shop.Theme.Catalog'}">${$priceVar}</span>
        <!--<span class="regular-price" aria-label="{l s='Regular price' d='Shop.Theme.Catalog'}">{$product.regular_price}</span>-->
        {if $product.discount_type === 'percentage'}
        <!--<span class="discount-percentage">{$product.discount_percentage}</span>-->
        <span class="discount-percentage">2%</span>
        {elseif $product.discount_type === 'amount'}
        <span class="discount-amount">{$product.discount_amount_to_display}</span>
        {/if}
        {/if}

        {hook h='displayProductPriceBlock' product=$product type="before_price"}

        <span class="price">{$product.price}</span>

        {hook h='displayProductPriceBlock' product=$product type='unit_price'}

        {hook h='displayProductPriceBlock' product=$product type='weight'}
      </div>
      {/if}
      {/block}
    </div>
    
  </div>    
</div>
