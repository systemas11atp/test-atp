{**
 * 2007-2019 PrestaShop.
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
<div id="js-product-list-header">
    {if $listing.pagination.items_shown_from == 1}
        <div class="block-category hidden-lg-down">
            {if $category.image.large.url}
                <div class="category-cover">       
                    <img src="{$category.image.large.url}" alt="{if !empty($category.image.legend)}{$category.image.legend}{else}{$category.name}{/if}">        
                </div>
            {/if}

            <h1 class="h1">{$category.name}</h1>
            {if $category.description}
                <div id="category-description" class="text-muted">{$category.description nofilter}</div>        
            {/if}
        </div>
        <div class="text-sm-center hidden-md-up">
          <h1 class="h1">{$category.name}</h1>
        </div>
    {/if}

    <!-- Subcategories -->
    {if isset($subcategories) && (!empty($subcategories))}
        <div id="subcategories">
            <p class="subcategory-heading">{l s='Subcategories' d='Shop.Theme.Global'}</p>
            <div class="row">
                {foreach from=$subcategories item=subcategory}
                    <div class="col-xs-6 col-sm-4 col-lg-3">
                        <a class="subcat" href="{$link->getCategoryLink($subcategory.id_category, $subcategory.link_rewrite)}" title="{$subcategory.name}" class="img">
                            <span class="subcategory-image">
                                    {if $subcategory.id_image}
                                        <img class="replace-2x lazyload" data-src="{$link->getCatImageLink($subcategory.link_rewrite, $subcategory.id_image, 'small_default')}" alt="{$subcategory.name}"/>
                                    {else}
                                        <img class="replace-2x" src="{$img_cat_dir}{$lang_iso}-default-small_default.jpg" alt="{$subcategory.name}"/>
                                    {/if}
                            </span>
                            <span class="subcategory-name">{$subcategory.name|truncate:25:'...'}</span>
                        </a>
                    </div>
                {/foreach}
            </div>
        </div>
    {/if}

</div>

   