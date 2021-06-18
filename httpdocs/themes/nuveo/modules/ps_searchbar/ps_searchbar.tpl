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
  <!--<div class="col-xl-4 col-lg-12">
     <div class="gcse-search"></div>
  </div>  
  <script async src="https://cse.google.com/cse.js?cx=014313635730003193707:kuuxljthx-w"></script>-->
  <!-- Block search module TOP -->
<!--
<div id="search_widget" class="col-xl-4 col-sm-12 search-widget" data-search-controller-url="{$search_controller_url}">
	<form method="get" action="{$search_controller_url}">
		<input type="hidden" name="controller" value="search">
		<input type="text" name="s" value="{$search_string}" placeholder="{l s='Search our catalog' d='Shop.Theme.Catalog'}" aria-label="{l s='Search' d='Shop.Theme.Catalog'}">
		<button type="submit">
			<i class="fa fa-search"></i>
		</button>
	</form>
</div>
-->
<!-- /Block search module TOP -->

<!--INICIA seccion de busqueda google-->
<div class="col-xl-4 col-lg-12">
	<form method ="get" action="https://avanceytec.mx/content/74-busqueda" id="cse-search-box" style="margin:0px;">
		<div class = "gcse-search">
			<td class="sb">
				<input type="text" name="q" size="25" id="input_gsc"/>
			</td>
			<td class="sb">
				<div class="button_position">
					<input type="submit" name="sa" value="" class="boton" style="cursor:url('hand.cur'); left: 0%;" id="button_gsc"/>
					<svg width="13" height="13" viewBox="0 0 13 13" style="position: absolute; top: 50%; right: 50%; fill: #FFF; transform: translate(50%, -50%);width: 20px; height: 20px;">
						<title>buscar</title>
						<path d="m4.8495 7.8226c0.82666 0 1.5262-0.29146 2.0985-0.87438 0.57232-0.58292 0.86378-1.2877 0.87438-2.1144 0.010599-0.82666-0.28086-1.5262-0.87438-2.0985-0.59352-0.57232-1.293-0.86378-2.0985-0.87438-0.8055-0.010599-1.5103 0.28086-2.1144 0.87438-0.60414 0.59352-0.8956 1.293-0.87438 2.0985 0.021197 0.8055 0.31266 1.5103 0.87438 2.1144 0.56172 0.60414 1.2665 0.8956 2.1144 0.87438zm4.4695 0.2115 3.681 3.6819-1.259 1.284-3.6817-3.7 0.0019784-0.69479-0.090043-0.098846c-0.87973 0.76087-1.92 1.1413-3.1207 1.1413-1.3553 0-2.5025-0.46363-3.4417-1.3909s-1.4088-2.0686-1.4088-3.4239c0-1.3553 0.4696-2.4966 1.4088-3.4239 0.9392-0.92727 2.0864-1.3969 3.4417-1.4088 1.3553-0.011889 2.4906 0.45771 3.406 1.4088 0.9154 0.95107 1.379 2.0924 1.3909 3.4239 0 1.2126-0.38043 2.2588-1.1413 3.1385l0.098834 0.090049z"></path>
					</svg>
				</div>
			</td>
		</div>
	</form>
	<!--<script async src="https://cse.google.com/cse.js?cx=014313635730003193707:kuuxljthx-w"></script>-->
</div>
<!--TERMINA seccion de busqueda google-->