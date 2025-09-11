/**
* 2007-2025 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
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
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2025 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*
* Don't forget to prefix your containers with your own identifier
* to avoid any conflicts with others containers.
*/

$(document).ready(function() {
    // Add disabled class to add-to-cart buttons for restricted products
    $('.product-miniature.restricted-day .add-to-cart').addClass('disabled');
    
    // Prevent click on restricted products' add-to-cart buttons
    $(document).on('click', '.product-miniature.restricted-day .add-to-cart', function(e) {
        e.preventDefault();
        e.stopPropagation();
        return false;
    });
    
    // Disable checkout button if restricted products are in cart
    function disableCheckoutIfRestricted() {
        if ($('[data-cart-has-restricted-products="true"]').length > 0) {
            // Specific selectors for checkout buttons only
            var checkoutSelectors = [
                'a[href*="/commande"]',
                'a[href*="/order"]', 
                '.cart-detailed-actions a.btn-primary',
                '.checkout a.btn-primary',
                'a:contains("Commander"):not(:contains("ajouter"))',
                '.btn:contains("Commander"):not(:contains("ajouter"))'
            ];
            
            checkoutSelectors.forEach(function(selector) {
                $(selector).each(function() {
                    var $btn = $(this);
                    var btnText = $btn.text().toLowerCase();
                    var href = $btn.attr('href') || '';
                    
                    // Check if it's a checkout button and not excluded
                    var isCheckoutButton = (
                        (btnText.includes('commander') && !btnText.includes('ajouter')) || 
                        href.includes('/commande') || 
                        href.includes('/order')
                    );
                    
                    var shouldExclude = (
                        btnText.includes('ajouter') || 
                        btnText.includes('remove') || 
                        btnText.includes('supprimer') ||
                        $btn.closest('.quantity').length > 0 ||
                        $btn.id === 'remove-all-restricted'
                    );
                    
                    if (isCheckoutButton && !shouldExclude) {
                        $btn.addClass('disabled brand-restricted')
                            .attr('disabled', true)
                            .css({
                                'opacity': '0.5',
                                'cursor': 'not-allowed',
                                'pointer-events': 'none'
                            })
                            .attr('title', 'Please remove restricted products before proceeding to checkout');
                        
                        if ($btn.is('a')) {
                            $btn.data('original-href', href).removeAttr('href');
                        }
                    }
                });
            });
        }
    }
    
    // Run on page load
    disableCheckoutIfRestricted();
    
    // Re-run when cart is updated (for AJAX cart updates)
    $(document).on('updateCart', disableCheckoutIfRestricted);
    
    // Prevent clicks on checkout elements with fallback
    $(document).on('click', '.brand-restricted, a[href*="/commande"], a[href*="/order"], .checkout-btn', function(e) {
        if ($('[data-cart-has-restricted-products="true"]').length > 0) {
            e.preventDefault();
            e.stopPropagation();
            alert('Please remove the restricted products from your cart before proceeding to checkout.');
            return false;
        }
    });
});