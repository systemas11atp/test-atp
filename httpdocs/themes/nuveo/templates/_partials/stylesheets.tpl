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
{foreach $stylesheets.external as $stylesheet}
  <link rel="stylesheet" href="{$stylesheet.uri}" type="text/css" media="{$stylesheet.media}">
{/foreach}
	<link rel="preload" as="font" href="{$urls.css_url}MaterialIcons-Regular.woff2" type="font/woff2" crossorigin="anonymous">
	<link rel="preload" as="font" href="{$urls.css_url}font-awesome/fonts/fontawesome-webfont.woff2?v=4.7.0" type="font/woff2" crossorigin="anonymous">
	{if isset($wptheme.wp_google_link)}
		<link rel="preload" href="//{$wptheme.wp_google_link}" as="style">
		<link href="//{$wptheme.wp_google_link}" rel="stylesheet">
	{/if}
	{if isset($wptheme.wp_google_link2) && $wptheme.wp_google_link != $wptheme.wp_google_link2}
		<link rel="preload" href="//{$wptheme.wp_google_link2}" as="style">	
		<link href="//{$wptheme.wp_google_link2}" rel="stylesheet">
	{/if}

{foreach $stylesheets.inline as $stylesheet}
  <style>
    {$stylesheet.content}
  </style>
{/foreach}
