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
 <div id="_desktop_user_info">
  <div class="user-info">
    {if $logged}
      <a
        class="logout hidden-lg-down"
        href="{$logout_url}"
        rel="nofollow"
      >
        <i class="material-icons">&#xE7FF;</i>
        {l s='Sign out' d='Shop.Theme.Actions'}
      </a>
    <!--
      <a
        class="account"
        href="{$my_account_url}"
        title="{l s='View my customer account' d='Shop.Theme.Customeraccount'}"
        rel="nofollow"
      >
        <i class="material-icons hidden-lg-up logged">&#xE7FF;</i>
        <span class="hidden-lg-down">{$customerName}</span>
      </a>
    -->
    <div class="account user-info dropdown js-dropdown account" style="display: inline-block;">
      <span class="expand-more" data-toggle="dropdown" style="display: inline-block;">
        <i class="material-icons">&#xE7FF;</i>
        <span style="color:#FFF;">{$customerName}</span>
      </span>
      <ul class="dropdown-menu" id="dropDown-top">

       <li>
        <a class="account dropdown-item" id="identity-link" href="{$my_account_url}">
          <span class="link-item">
            <!--<i class="material-icons">&#xEAC8;</i>-->
            <!--<span>{l s='my account' d='Shop.Theme.Customeraccount'}</span>-->
            <span>Mi cuenta</span>
          </span>
        </a>
      </li>

      <li>
        <a class="account dropdown-item" id="identity-link" href="{$urls.pages.identity}">
          <span class="link-item">
            <!--<i class="material-icons">&#xEAC9;</i>-->
            <span>{l s='Information' d='Shop.Theme.Customeraccount'}</span>
          </span>
        </a>
      </li>

      {if $customer.addresses|count}
      <li>
        <a class="account dropdown-item" id="addresses-link" href="{$urls.pages.addresses}">
          <span class="link-item">
            <!--<i class="material-icons">&#xEB6F;</i>-->
            <span>{l s='Addresses' d='Shop.Theme.Customeraccount'}</span>
          </span>
        </a>
      </li>
      {else}
      <li>
        <a class="account dropdown-item" id="address-link" href="{$urls.pages.address}">
          <span class="link-item">
            <!--<i class="material-icons">&#xEB6F;</i>-->
            <span>{l s='Add first address' d='Shop.Theme.Customeraccount'}</span>
          </span>
        </a>
      </li>
      {/if}

      {if !$configuration.is_catalog}
      <li>
        <a class="account dropdown-item" id="history-link" href="{$urls.pages.history}">
          <span class="link-item">
            <!--<i class="material-icons">&#xEA61;</i>-->
            <span>{l s='Order history and details' d='Shop.Theme.Customeraccount'}</span>
          </span>
        </a>
      </li>
      {/if}

      {if !$configuration.is_catalog}
      <li>
        <a class="account dropdown-item" id="order-slips-link" href="{$urls.pages.order_slip}">
          <span class="link-item">
            <!--<i class="material-icons">&#xEC57;</i>-->
            <span>{l s='Credit slips' d='Shop.Theme.Customeraccount'}</span>
          </span>
        </a>
      </li>
      <li>
        <a class="account dropdown-item" id="order-alerts" href="{url entity='module' name='ps_emailalerts' controller='account'}">
          <span class="link-item">
            <!--<i class="material-icons">&#xECD0;</i>-->
            <span>{l s='My alerts' d='Modules.Emailalerts.Shop'}</span>
          </span>
        </a>
      </li>
      {/if}

      {if $configuration.voucher_enabled && !$configuration.is_catalog}
      <li>
        <a class="account dropdown-item" id="discounts-link" href="{$urls.pages.discount}">
          <span class="link-item">
            <!--<i class="material-icons">&#xECFA;</i>-->
            <span>{l s='Vouchers' d='Shop.Theme.Customeraccount'}</span>
          </span>
        </a>
      </li>
      {/if}

      {if $configuration.return_enabled && !$configuration.is_catalog}
      <li>
        <a class="account dropdown-item" id="returns-link" href="{$urls.pages.order_follow}">
          <span class="link-item">
            <!--<i class="material-icons">&#xECC7;</i>-->
            <span>{l s='Merchandise returns' d='Shop.Theme.Customeraccount'}</span>
          </span>
        </a>
      </li>
      {/if}
      <li>
        {block name='display_customer_account'}
        {hook h='displayCustomerAccountDropdown'}
        {/block}
      </li>
    </ul>
  </div>
  {else}
  <!--
  <a
  href="{$my_account_url}"
  title="{l s='Log in to your customer account' d='Shop.Theme.Customeraccount'}"
  rel="nofollow"
  >
  -->
  <!--<i class="material-icons">&#xe7fd;</i>
  <span class="hidden-lg-down">{l s='Sign in' d='Shop.Theme.Actions'}</span>
  -->
</a>
{/if}
</div>
</div>