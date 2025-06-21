<?php
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
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Brandorderdays extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'brandorderdays';
        $this->tab = 'front_office_features';
        $this->version = '0.0.1';
        $this->author = 'dewwwe';
        $this->need_instance = 0;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->trans('Brand Order Days', [], 'Modules.Brandorderdays.Admin');
        $this->description = $this->trans('Restrict ordering of products from specific brands on certain days of the week while maintaining clear communication with customers.', [], 'Modules.Brandorderdays.Admin');

        $this->ps_versions_compliancy = array('min' => '1.7.6', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        Configuration::updateValue('BRANDORDERDAYS_LIVE_MODE', false);

        return parent::install()
            // register css/js front
            && $this->registerHook('displayHeader')
            // show banner
            && $this->registerHook('displayTop')
            && $this->registerHook('displayWrapperTop')
            // register css/js back
            && $this->registerHook('displayBackOfficeHeader')
            // add configuration to product list pages
            && $this->registerHook('actionPresentProductListing')
            // show message on product page
            && $this->registerHook('displayProductAdditionalInfo')
            // show message on product list page 
            && $this->registerHook('displayProductListReviews')
            // 
            && $this->registerHook('actionCartUpdateQuantityBefore')
            // show general message in cart
            && $this->registerHook('displayShoppingCart')
            // message next to products in cart
            && $this->registerHook('displayCartExtraProductActions')
            // check before order completion
            && $this->registerHook('actionValidateOrder')
            && $this->saveModuleConfig($this->getDefaultConfig())
            // Install the quick access tab in the back office
            && $this->installTab();
    }

    public function uninstall()
    {
        Configuration::deleteByName('BRANDORDERDAYS_LIVE_MODE');
        Configuration::deleteByName('BRANDORDERDAYS_CONFIG');

        // Uninstall tab
        $this->uninstallTab();

        return parent::uninstall();
    }

    /**
     * Create admin tab for quick access
     */
    private function installTab()
    {
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = 'AdminBrandOrderDays';
        $tab->name = array();

        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = $this->trans('Brand Ordering Restrictions', [], 'Modules.Brandorderdays.Admin', $lang['locale']);
        }

        // Find the position of Brands & Suppliers tab
        $id_parent = (int) Tab::getIdFromClassName('AdminCatalog');
        // $id_brands_tab = (int) Tab::getIdFromClassName('AdminParentManufacturers');

        $tab->id_parent = $id_parent;
        $tab->module = $this->name;
        $tab->position = Tab::getNewLastPosition($tab->id_parent);

        return $tab->add();
    }

    /**
     * Remove admin tab
     */
    private function uninstallTab()
    {
        $id_tab = (int) Tab::getIdFromClassName('AdminBrandOrderDays');

        if ($id_tab) {
            $tab = new Tab($id_tab);
            return $tab->delete();
        }

        return true;
    }

    /**
     * Use new translation system
     */
    public function isUsingNewTranslationSystem()
    {
        return true;
    }

    /**
     * Default configuration values
     */
    private function getDefaultConfig()
    {
        return [
            'timezone' => 'Europe/Paris',
            'global_message' => $this->trans('Some products are only available on specific days of the week.', [], 'Modules.Brandorderdays.Shop'),
            'brands' => [] // Empty by default, meaning no restrictions
        ];
    }

    /**
     * Get the module configuration
     */
    public function getModuleConfig()
    {
        $config = json_decode(Configuration::get('BRANDORDERDAYS_CONFIG'), true);

        if (!$config) {
            $config = $this->getDefaultConfig();
        }

        return $config;
    }

    /**
     * Save the module configuration
     */
    public function saveModuleConfig($config)
    {
        return Configuration::updateValue('BRANDORDERDAYS_CONFIG', json_encode($config));
    }

    /**
     * Render the configuration form
     */
    public function renderConfigurationForm()
    {
        // Prepare data for the configuration form
        $config = $this->getModuleConfig();

        // Get all brands (manufacturers)
        $brands = Manufacturer::getManufacturers();

        $days_of_week = [
            'monday' => $this->trans('Monday', [], 'Modules.Brandorderdays.Admin'),
            'tuesday' => $this->trans('Tuesday', [], 'Modules.Brandorderdays.Admin'),
            'wednesday' => $this->trans('Wednesday', [], 'Modules.Brandorderdays.Admin'),
            'thursday' => $this->trans('Thursday', [], 'Modules.Brandorderdays.Admin'),
            'friday' => $this->trans('Friday', [], 'Modules.Brandorderdays.Admin'),
            'saturday' => $this->trans('Saturday', [], 'Modules.Brandorderdays.Admin'),
            'sunday' => $this->trans('Sunday', [], 'Modules.Brandorderdays.Admin')
        ];

        // Get all timezones
        $timezones = [];
        foreach (DateTimeZone::listIdentifiers() as $timezone) {
            $timezones[] = [
                'id' => $timezone,
                'name' => $timezone
            ];
        }

        // Check if we should filter to show only configured brands
        $show_only_configured = (bool) Tools::getValue('show_only_configured', false);

        // Filter brands if needed
        $filtered_brands = [];
        foreach ($brands as $brand) {
            $id_brand = $brand['id_manufacturer'];
            if (!$show_only_configured || isset($config['brands'][$id_brand])) {
                $filtered_brands[] = $brand;
            }
        }

        // Determine the current URL based on context
        $current_url = '';
        if (Tools::getValue('controller') == 'AdminBrandOrderDays') {
            $current_url = $this->context->link->getAdminLink('AdminBrandOrderDays');
        } else {
            $current_url = $this->context->link->getAdminLink('AdminModules') . '&configure=' . $this->name;
        }

        $this->context->smarty->assign([
            'module_dir' => $this->_path,
            'brands' => $filtered_brands,
            'days_of_week' => $days_of_week,
            'timezones' => $timezones,
            'config' => $config,
            'BRANDORDERDAYS_LIVE_MODE' => Configuration::get('BRANDORDERDAYS_LIVE_MODE', false),
            'show_only_configured' => $show_only_configured,
            'current_url' => $current_url
        ]);

        return $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure.tpl');
    }

    /**
     * Process the configuration form
     */
    public function processConfigurationForm()
    {
        $output = '';

        // Process the standard configuration values
        $form_values = $this->getConfigFormValues();
        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }

        // Process the brand restrictions configuration
        $result = $this->processConfigForm();

        return $result;
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        $output = '';

        if ((bool) Tools::isSubmit('submitBrandorderdaysModule')) {
            $output .= $this->processConfigurationForm();
        }

        $output .= $this->renderConfigurationForm();

        return $output;
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        // Process the standard configuration values
        $form_values = $this->getConfigFormValues();
        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }

        // Process the brand restrictions configuration
        return $this->processConfigForm();
    }

    /**
     * Process the configuration form for brand restrictions
     */
    protected function processConfigForm()
    {
        $config = $this->getModuleConfig();

        // Update general settings
        $config['timezone'] = Tools::getValue('timezone');
        $config['global_message'] = Tools::getValue('global_message');

        // Process brand restrictions
        $config['brands'] = [];
        $brands = Manufacturer::getManufacturers();

        foreach ($brands as $brand) {
            $id_brand = $brand['id_manufacturer'];
            $restricted_days = Tools::getValue('brand_' . $id_brand . '_days', []);

            if (!empty($restricted_days)) {
                $config['brands'][$id_brand] = [
                    'restricted_days' => $restricted_days,
                    'custom_message' => Tools::getValue('brand_' . $id_brand . '_message', '')
                ];
            }
        }

        if ($this->saveModuleConfig($config)) {
            return $this->displayConfirmation($this->trans('Settings updated successfully.', [], 'Modules.Brandorderdays.Admin'));
        } else {
            return $this->displayError($this->trans('Error occurred during settings update.', [], 'Modules.Brandorderdays.Admin'));
        }
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array(
            'BRANDORDERDAYS_LIVE_MODE' => Configuration::get('BRANDORDERDAYS_LIVE_MODE', true),
        );
    }


    /**
     * Add the CSS & JavaScript files you want to be loaded in the BO.
     */
    public function hookDisplayBackOfficeHeader()
    {
        if (Tools::getValue('configure') == $this->name) {
            $this->context->controller->addJS($this->_path . 'views/js/back.js');
            $this->context->controller->addCSS($this->_path . 'views/css/back.css');
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        // Only load assets if the module is active
        if (Configuration::get('BRANDORDERDAYS_LIVE_MODE', false)) {
            $this->context->controller->addCSS($this->_path . '/views/css/front.css');
            $this->context->controller->addJS($this->_path . '/views/js/front.js');
        }
    }

    /**
     * Check if a product is restricted today
     */
    public function isProductRestrictedToday($id_product)
    {
        // If the module is not active, no products are restricted
        if (!Configuration::get('BRANDORDERDAYS_LIVE_MODE', false)) {
            return false;
        }

        static $restricted_products = null;

        // Initialize the cache if needed
        if ($restricted_products === null) {
            $restricted_products = [];
            $config = $this->getModuleConfig();

            // Set timezone for date calculations
            $previous_timezone = date_default_timezone_get();
            date_default_timezone_set($config['timezone']);

            // Get current day of week
            $current_day = strtolower(date('l'));

            // Get all brands with restrictions for today
            $restricted_brands = [];
            foreach ($config['brands'] as $id_brand => $brand_config) {
                if (in_array($current_day, $brand_config['restricted_days'])) {
                    $restricted_brands[] = (int) $id_brand;
                }
            }

            // If we have restricted brands, get their products
            if (!empty($restricted_brands)) {
                $products = Db::getInstance()->executeS('
                SELECT id_product 
                FROM ' . _DB_PREFIX_ . 'product 
                WHERE id_manufacturer IN (' . implode(',', $restricted_brands) . ')
            ');

                if ($products) {
                    foreach ($products as $product) {
                        $restricted_products[$product['id_product']] = true;
                    }
                }
            }

            // Restore original timezone
            date_default_timezone_set($previous_timezone);
        }

        return isset($restricted_products[$id_product]);
    }

    /**
     * Get restriction message for a product
     */
    public function getProductRestrictionMessage($id_product)
    {
        $config = $this->getModuleConfig();
        $product = new Product($id_product);
        $id_brand = $product->id_manufacturer;

        if (isset($config['brands'][$id_brand]['custom_message']) && !empty($config['brands'][$id_brand]['custom_message'])) {
            return $config['brands'][$id_brand]['custom_message'];
        }

        return $config['global_message'];
    }

    /**
     * Get all restricted products in a cart
     */
    public function getRestrictedProductsInCart($cart)
    {
        $restricted_products = [];

        foreach ($cart->getProducts() as $product) {
            if ($this->isProductRestrictedToday($product['id_product'])) {
                // Add the restriction message to the product data
                $product['restriction_message'] = $this->getProductRestrictionMessage($product['id_product']);
                $restricted_products[] = $product;
            }
        }

        return $restricted_products;
    }

    /**
     * Modify product data for listings
     */
    public function hookActionPresentProductListing(array $params)
    {
        // If the module is not active, don't modify anything
        if (!Configuration::get('BRANDORDERDAYS_LIVE_MODE', false)) {
            return;
        }

        $presentedProduct = &$params['presentedProduct'];

        // Access the product data directly
        $id_product = $presentedProduct['id_product'];

        if ($this->isProductRestrictedToday($id_product)) {
            // dump('presented product restricted: ');
            // dump($presentedProduct);

            // Instead of modifying add_to_cart_url directly, we'll add our own flag
            $presentedProduct['restricted_day'] = true;
            $presentedProduct['restriction_message'] = $this->getProductRestrictionMessage($id_product);
            // Disable add to cart button by removing the url to up the product quantity
            $presentedProduct['up_quantity_url'] = false;

            // dump($presentedProduct);
        }
    }

    /**
     * Display restriction message in product list
     */
    public function hookDisplayProductListReviews($params)
    {
        // If the module is not active, don't show any messages
        if (!Configuration::get('BRANDORDERDAYS_LIVE_MODE', false)) {
            return '';
        }


        $product = $params['product'];

        // dump('this is a product');

        if (isset($product['restricted_day']) && $product['restricted_day']) {
            $this->context->smarty->assign([
                'product' => $product
            ]);

            // dump('this product is restricted'); 

            return $this->display(__FILE__, 'views/templates/hook/product_list_override.tpl');
        }

        return '';
    }

    /**
     * Show restriction message on product page
     */
    public function hookDisplayProductAdditionalInfo($params)
    {
        // If the module is not active, don't show any messages
        if (!Configuration::get('BRANDORDERDAYS_LIVE_MODE', false)) {
            return '';
        }

        $product = $params['product'];

        if ($this->isProductRestrictedToday($product->id)) {
            $this->context->smarty->assign([
                'restriction_message' => $this->getProductRestrictionMessage($product->id)
            ]);

            return $this->display(__FILE__, 'views/templates/hook/product_restriction.tpl');
        }

        return '';
    }

    /**
     * Prevent adding restricted products to cart
     */
    public function hookActionCartUpdateQuantityBefore($params)
    {
        // If the module is not active, don't restrict anything
        if (!Configuration::get('BRANDORDERDAYS_LIVE_MODE', false)) {
            return;
        }

        if ($this->isProductRestrictedToday($params['product']->id)) {
            $this->context->controller->errors[] = $this->trans('This product cannot be ordered today.', [], 'Modules.Brandorderdays.Shop') . ' ' .
                $this->getProductRestrictionMessage($params['product']->id);

            // Prevent adding to cart by throwing an exception
            throw new PrestaShopException($this->trans('This product cannot be ordered today.', [], 'Modules.Brandorderdays.Shop'));
        }
    }

    /**
     * Show messages about unavailable products in cart
     */
    public function hookDisplayShoppingCart($params)
    {
        // If the module is not active, don't show any messages
        if (!Configuration::get('BRANDORDERDAYS_LIVE_MODE', false)) {
            return '';
        }

        $cart = $params['cart'];
        $restricted_products = $this->getRestrictedProductsInCart($cart);

        if (!empty($restricted_products)) {
            $this->context->smarty->assign([
                'restricted_products' => $restricted_products,
                'global_message' => $this->getModuleConfig()['global_message'],
                'static_token' => Tools::getToken(false),
                'urls' => [
                    'pages' => [
                        'cart' => $this->context->link->getPageLink('cart')
                    ]
                ]
            ]);

            return $this->display(__FILE__, 'views/templates/hook/cart_restrictions.tpl');
        }

        return '';
    }


    /**
     * Display a message next to restricted products in the cart
     */
    public function hookDisplayCartExtraProductActions($params)
    {
        // If the module is not active, don't show any message
        if (!Configuration::get('BRANDORDERDAYS_LIVE_MODE', false)) {
            return '';
        }

        // Get the product from the parameters
        $product = $params['product'];
        $id_product = $product['id_product'];

        // Check if this product is restricted today
        if ($this->isProductRestrictedToday($id_product)) {
            $this->context->smarty->assign([
                'restriction_message' => $this->getProductRestrictionMessage($id_product)
            ]);
            return $this->display(__FILE__, 'views/templates/hook/cart_product_restriction.tpl');
        }

        return '';
    }

    /**
     * Final validation before order completion
     */
    public function hookActionValidateOrder($params)
    {
        // If the module is not active, don't restrict anything
        if (!Configuration::get('BRANDORDERDAYS_LIVE_MODE', false)) {
            return;
        }

        $cart = $params['cart'];
        $restricted_products = $this->getRestrictedProductsInCart($cart);

        if (!empty($restricted_products)) {
            // Prevent order completion
            throw new PrestaShopException($this->trans('Your cart contains products that cannot be ordered today.', [], 'Modules.Brandorderdays.Shop'));
        }
    }

    /**
     * Display a banner when restricted products are on the page
     */
    public function hookDisplayTop($params)
    {
        return $this->getBanner($params);
    }
    public function hookDisplayWrapperTop($params)
    {
        return $this->getBanner($params);
    }

    protected function getBanner($params)
    {
        // If the module is not active, don't show any banner
        if (!Configuration::get('BRANDORDERDAYS_LIVE_MODE', false)) {
            return '';
        }

        $config = $this->getModuleConfig();

        // Check if we're on a page that might display products
        $controller = $this->context->controller->php_self;
        $product_controllers = ['category', 'product', 'search', 'manufacturer', 'supplier', 'index'];

        if (!in_array($controller, $product_controllers)) {
            return '';
        }

        // For product page, check if the current product is restricted
        if ($controller === 'product') {
            $id_product = (int) Tools::getValue('id_product');
            if ($id_product && $this->isProductRestrictedToday($id_product)) {
                $this->context->smarty->assign([
                    'restriction_message' => $config['global_message']
                ]);
                return $this->display(__FILE__, 'views/templates/hook/restriction_banner.tpl');
            }
            return '';
        }

        // For other pages, check if there are any restricted products from active brands

        // Set timezone for date calculations
        $previous_timezone = date_default_timezone_get();
        date_default_timezone_set($config['timezone']);

        // Get current day of week
        $current_day = strtolower(date('l'));

        // Get all brands with restrictions for today
        $restricted_brands = [];
        foreach ($config['brands'] as $id_brand => $brand_config) {
            if (in_array($current_day, $brand_config['restricted_days'])) {
                $restricted_brands[] = (int) $id_brand;
            }
        }

        // Restore original timezone
        date_default_timezone_set($previous_timezone);

        // If we have restricted brands, show the banner
        if (!empty($restricted_brands)) {
            $this->context->smarty->assign([
                'restriction_message' => $config['global_message']
            ]);
            return $this->display(__FILE__, 'views/templates/hook/restriction_banner.tpl');
        }

        return '';
    }

}
