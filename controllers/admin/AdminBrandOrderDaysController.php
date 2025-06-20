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
 * @author PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2025 PrestaShop SA
 * @license http://opensource.org/licenses/afl-3.0.php Academic Free License (AFL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class AdminBrandOrderDaysController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        $this->module = Module::getInstanceByName('brandorderdays');

        parent::__construct();

        $this->meta_title = $this->l('Brand Ordering Restrictions');
    }

    public function initContent()
    {
        parent::initContent();

        // Load the module explicitly for better linting
        /** @var Brandorderdays $module */
        $module = $this->module;

        // Use the module's getContent method to get the configuration content
        $output = $module->renderConfigurationForm();

        $this->content = $output;
        $this->context->smarty->assign('content', $this->content);
    }

    public function postProcess()
    {
        // Load the module explicitly for better linting
        /** @var Brandorderdays $module */
        $module = $this->module;

        // Let the module handle the form submission
        if (Tools::isSubmit('submitBrandorderdaysModule')) {
            $result = $module->processConfigurationForm();

            // Extract confirmation or error message from the result
            if (strpos($result, 'alert alert-success') !== false) {
                $this->confirmations[] = $this->l('Settings updated successfully.');
            } elseif (strpos($result, 'alert alert-danger') !== false) {
                $this->errors[] = $this->l('Error occurred during settings update.');
            }
        }

        parent::postProcess();
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);

        $this->addCSS($this->module->getPathUri() . 'views/css/back.css');
        $this->addJS($this->module->getPathUri() . 'views/js/back.js');
    }
}
