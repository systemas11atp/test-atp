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

<div class="block-contact col-lg-12 col-xl-3 links wrapper">
    <p class="h3 block-contact-title hidden-lg-down">{l s='Store information' d='Shop.Theme.Global'}</p>

      <div class="title clearfix hidden-xl-up" data-target="#footer_contact_list" data-toggle="collapse">
        <span class="h3">{l s='Store information' d='Shop.Theme.Global'}</span>
        <span class="float-xs-right">
          <span class="navbar-toggler collapse-icons">
            <i class="material-icons add">&#xE313;</i>
            <i class="material-icons remove">&#xE316;</i>
          </span>
        </span>
      </div>

      <ul class="collapse" id="footer_contact_list">
      <li class="address">
        <i class="material-icons">&#xE0C8;</i>
 {$contact_infos.address.formatted nofilter} 
      </li>
      {if $contact_infos.phone}
        <li>
        <i class="material-icons">&#xE0CD;</i>
        {* [1][/1] is for a HTML tag. *}
        <span>{l s='Call us: [1]%phone%[/1]'
          sprintf=[
          '[1]' => '<span>',
          '[/1]' => '</span>',
          '%phone%' => $contact_infos.phone
          ]
          d='Shop.Theme.Global'
        }</span>
        </li>
      {/if}
      {if $contact_infos.fax}
        <li>
        <i class="material-icons">&#xE0CD;</i>
        {* [1][/1] is for a HTML tag. *}
        <span>{l
          s='Fax: [1]%fax%[/1]'
          sprintf=[
            '[1]' => '<span>',
            '[/1]' => '</span>',
            '%fax%' => $contact_infos.fax
          ]
          d='Shop.Theme.Global'
        }</span>
        </li>
      {/if}
      {if $contact_infos.email && $display_email}
        <li class="email">
        <span>
        <i class="material-icons">&#xE0BE;</i>
        {* [1][/1] is for a HTML tag. *}
        {l
          s='Email us: [1]%email%[/1]'
          sprintf=[
            '[1]' => '<a href="mailto:'|cat:$contact_infos.email|cat:'" class="dropdown">',
            '[/1]' => '</a>',
            '%email%' => $contact_infos.email
          ]
          d='Shop.Theme.Global'
        }</span>
        </li>
      {/if}
      </ul>
</div>
 
