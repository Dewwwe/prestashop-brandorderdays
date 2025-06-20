
<div class="alert alert-warning cart-restrictions">
    <h4>{l s='Some products in your cart cannot be ordered today' d='Modules.Brandorderdays.Shop'}</h4>
    <p>{$global_message|escape:'htmlall':'UTF-8'}</p>
    
    <ul class="restricted-products-list">
        {foreach from=$restricted_products item=product}
            <li>
                <strong>{$product.name|escape:'htmlall':'UTF-8'}</strong>
                {if isset($product.attributes_small)}
                    <span class="product-attributes">({$product.attributes_small|escape:'htmlall':'UTF-8'})</span>
                {/if}
            </li>
        {/foreach}
    </ul>
    
    <div class="cart-action-buttons">
        <a href="{$urls.pages.cart}?action=show" class="btn btn-primary">
            {l s='Modify my cart' d='Modules.Brandorderdays.Shop'}
        </a>
    </div>
</div>