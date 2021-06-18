{if !$configuration.is_catalog && $page.page_name != 'product'}
	{if $product.add_to_cart_url}
	  <form action="{$product.add_to_cart_url}" method="post" id="add-to-cart-or-refresh">
	  	  <input type="hidden" name="token" value="{$static_token}">
	      <input type="hidden" name="id_product" value="{$product.id}" id="product_page_product_id">
	      <input
	              type="number"
	              name="qty"
	              value="{$product.minimal_quantity}"
	              class="input-group form-control qty"
	              min="{$product.minimal_quantity}"
	      >
	      <button
	              class="btn btn-primary add-to-cart"
	              data-button-action="add-to-cart"
	              type="submit"
	              {if $product.availability == 'unavailable'}
	                  disabled
	              {/if}
	      >
	          <i class="material-icons shopping-cart">&#xE8CB;</i>
	          {l s='Add to cart' d='Shop.Theme.Actions'}
	      </button>
	  </form>
	{/if}
{/if}