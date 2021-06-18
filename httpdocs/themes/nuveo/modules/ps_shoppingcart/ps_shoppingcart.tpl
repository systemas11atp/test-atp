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
 <div id="_desktop_cart" class="col-lg-4">
  <div class="blockcart cart-preview wp-expand {if $cart.products_count > 0}active{else}inactive{/if}" data-refresh-url="{$refresh_url}">
    <div class="header">
      {if $cart.products_count > 0}
        <a rel="nofollow" href="{$cart_url}">
      {/if}      
        <i class="fa fa-shopping-cart"></i>
        <span class="hidden-lg-down cart-label">{l s='Cart' d='Shop.Theme.Checkout'}</span>
        <span class="cart-products-count">{$cart.products_count}</span>
      {if $cart.products_count > 0}
        </a>
      {/if}

      <div class="card cart-summary {if $cart.products_count < 1}hidden-xs-up{/if}">
        {include file='checkout/_partials/cart-detailed.tpl' cart=$cart}
        {block name='cart_totals'}
          {include file='checkout/_partials/cart-detailed-totals-top.tpl' cart=$cart}
        {/block}
        
        <div class="checkout cart-detailed-actions card-block">
          <a rel="nofollow" href="{$cart_url}" class="btn btn-primary">{l s='Checkout' d='Shop.Theme.Actions'}</a>
        </div>      
      </div>
        
    </div>
  </div>
</div>
