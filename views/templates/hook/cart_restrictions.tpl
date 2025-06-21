<div class="alert alert-warning cart-restrictions">
  <h4>{l s='Some products in your cart cannot be ordered today' d='Modules.Brandorderdays.Shop'}</h4>
  <p>{$global_message|escape:'htmlall':'UTF-8'}</p>
  
  <ul class="restricted-products-list">
    {foreach from=$restricted_products item=product}
      <li>
        <strong>{$product.name|escape:'htmlall':'UTF-8'}</strong>
        {if isset($product.cart_quantity)}
          <span class="product-attributes">({$product.cart_quantity|escape:'htmlall':'UTF-8'})</span>
        {/if}
        <a href="{$urls.pages.cart}?delete=1&id_product={$product.id_product}&id_product_attribute={$product.id_product_attribute|default:'0'}&token={$static_token}" 
           class="remove-from-cart"
           rel="nofollow"
           title="{l s='Remove this product from my cart' d='Modules.Brandorderdays.Shop'}">
          <i class="material-icons">delete</i>
        </a>
      </li>
    {/foreach}
  </ul>
  
  <div class="cart-action-buttons">
    <button id="remove-all-restricted" class="btn btn-primary">
      {l s='Remove all restricted products' d='Modules.Brandorderdays.Shop'}
    </button>
  </div>

  <script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function() {
      // Get the remove all button
      var removeAllBtn = document.getElementById('remove-all-restricted');
      
      // Add click event
      removeAllBtn.addEventListener('click', function(e) {
        e.preventDefault();
        
        // Show loading state
        removeAllBtn.disabled = true;
        removeAllBtn.innerHTML = '<i class="material-icons">hourglass_empty</i> {l s='Removing...' d='Modules.Brandorderdays.Shop'}';
        
        // Array of products to remove
        var productsToRemove = [
          {foreach from=$restricted_products item=product}
            {
              id_product: {$product.id_product},
              id_product_attribute: {$product.id_product_attribute|default:'0'}
            }{if !$product@last},{/if}
          {/foreach}
        ];
        
        // Function to remove products one by one
        function removeNextProduct(index) {
          if (index >= productsToRemove.length) {
            // All products removed, reload the page
            window.location.reload();
            return;
          }
          
          var product = productsToRemove[index];
          
          // Create the URL for removal
          var removeUrl = '{$urls.pages.cart}?delete=1&id_product=' + 
                          product.id_product + 
                          '&id_product_attribute=' + 
                          product.id_product_attribute + 
                          '&token={$static_token}';
          
          // Make AJAX request to remove the product
          fetch(removeUrl, {
            method: 'GET',
            headers: {
              'X-Requested-With': 'XMLHttpRequest'
            }
          })
          .then(function(response) {
            // Move to the next product regardless of success
            removeNextProduct(index + 1);
          })
          .catch(function(error) {
            console.error('Error removing product:', error);
            // Try to continue with next product anyway
            removeNextProduct(index + 1);
          });
        }
        
        // Start removing products
        removeNextProduct(0);
      });
    });
  </script>
</div>
