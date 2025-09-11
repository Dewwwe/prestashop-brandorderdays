{*
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
*}

<div class="panel">
	<h3><i class="icon icon-calendar"></i> {l s='Brand Order Days' d='Modules.Brandorderdays.Admin'}</h3>
	<p>{l s='Configure which days products from specific brands cannot be ordered.' d='Modules.Brandorderdays.Admin'}
	</p>
</div>

<form method="post"
	  action="{$smarty.server.REQUEST_URI|escape:'htmlall':'UTF-8'}">
	<div class="panel">
		<h3>{l s='General Settings' d='Modules.Brandorderdays.Admin'}</h3>

		<div class="form-group">
			<label class="control-label col-lg-3">{l s='Enable Module' d='Modules.Brandorderdays.Admin'}</label>
			<div class="col-lg-9">
				<span class="switch prestashop-switch fixed-width-lg">
					<input type="radio"
						   name="BRANDORDERDAYS_LIVE_MODE"
						   id="BRANDORDERDAYS_LIVE_MODE_on"
						   value="1"
						   {if isset($BRANDORDERDAYS_LIVE_MODE) && $BRANDORDERDAYS_LIVE_MODE}checked="checked"
						   {/if}>
					<label for="BRANDORDERDAYS_LIVE_MODE_on">{l s='Yes' d='Admin.Global'}</label>
					<input type="radio"
						   name="BRANDORDERDAYS_LIVE_MODE"
						   id="BRANDORDERDAYS_LIVE_MODE_off"
						   value="0"
						   {if !isset($BRANDORDERDAYS_LIVE_MODE) || !$BRANDORDERDAYS_LIVE_MODE}checked="checked"
						   {/if}>
					<label for="BRANDORDERDAYS_LIVE_MODE_off">{l s='No' d='Admin.Global'}</label>
					<a class="slide-button btn"></a>
				</span>
				<p class="help-block">
					{l s='Enable or disable the module functionality' d='Modules.Brandorderdays.Admin'}</p>
			</div>
		</div>

		<div class="form-group">
			<label class="control-label col-lg-3">{l s='Timezone' d='Modules.Brandorderdays.Admin'}</label>
			<div class="col-lg-9">
				<select name="timezone"
						class="form-control">
					{if isset($timezones) && $timezones}
						{foreach from=$timezones item=timezone}
							<option value="{$timezone.id}"
									{if isset($config.timezone) && $config.timezone == $timezone.id}selected="selected"
									{/if}>
								{$timezone.name}
							</option>
						{/foreach}
					{/if}
				</select>
				<p class="help-block">
					{l s='Select the timezone to use for day calculations' d='Modules.Brandorderdays.Admin'}</p>
			</div>
		</div>

		<div class="form-group">
			<label class="control-label col-lg-3">{l s='General Message' d='Modules.Brandorderdays.Admin'}</label>
			<div class="col-lg-9">
				<textarea name="global_message"
						  class="form-control"
						  rows="3">{if isset($config.global_message)}{$config.global_message|escape:'htmlall':'UTF-8'}{/if}</textarea>
				<p class="help-block">
					{l s='Default message shown for restricted products' d='Modules.Brandorderdays.Admin'}</p>
			</div>
		</div>
	</div>

	<div class="panel">
		<h3>{l s='Brand Restrictions' d='Modules.Brandorderdays.Admin'}</h3>

		<div class="alert alert-info">
			{l s='Check the days when products from a specific brand CANNOT be ordered. When a day is checked, customers will not be able to add products from that brand to their cart on that day.' d='Modules.Brandorderdays.Admin'}
		</div>

		<div class="filter-toggle">
			<label for="show_only_configured">
				{l s='Show only brands with configured restrictions' d='Modules.Brandorderdays.Admin'}
			</label>
			<div class="switch-container">
				<span class="switch prestashop-switch fixed-width-lg">
					<input type="radio"
						   name="show_only_configured"
						   id="show_only_configured_on"
						   value="1"
						   {if $show_only_configured}checked="checked"
						   {/if}
						   onchange="window.location.href='{$current_url}&show_only_configured=1'">
					<label for="show_only_configured_on">{l s='Yes' d='Admin.Global'}</label>
					<input type="radio"
						   name="show_only_configured"
						   id="show_only_configured_off"
						   value="0"
						   {if !$show_only_configured}checked="checked"
						   {/if}
						   onchange="window.location.href='{$current_url}&show_only_configured=0'">
					<label for="show_only_configured_off">{l s='No' d='Admin.Global'}</label>
					<a class="slide-button btn"></a>
				</span>
			</div>
		</div>

		<div class="table-responsive">
			<!-- Rest of your table code remains the same -->

			<table class="table">
				<thead>
					<tr>
						<th>{l s='Brand' d='Modules.Brandorderdays.Admin'}</th>
						{if isset($days_of_week) && $days_of_week}
							{foreach from=$days_of_week key=day_key item=day_name}
								<th class="text-center">{$day_name}</th>
							{/foreach}
						{/if}
						<th>{l s='Custom Message' d='Modules.Brandorderdays.Admin'}</th>
					</tr>
				</thead>
				<tbody>
					{if isset($brands) && $brands}
						{foreach from=$brands item=brand}
							<tr>
								<td>{$brand.name}</td>
								{if isset($days_of_week) && $days_of_week}
									{foreach from=$days_of_week key=day_key item=day_name}
										<td class="text-center">
											<input type="checkbox"
												   name="brand_{$brand.id_manufacturer}_days[]"
												   value="{$day_key}"
												   {if isset($config.brands[$brand.id_manufacturer].restricted_days) && in_array($day_key, $config.brands[$brand.id_manufacturer].restricted_days)}checked="checked"
												   {/if} />
										</td>
									{/foreach}
								{/if}
								<td>
									<input type="text"
										   class="form-control"
										   name="brand_{$brand.id_manufacturer}_message"
										   value="{if isset($config.brands[$brand.id_manufacturer].custom_message)}{$config.brands[$brand.id_manufacturer].custom_message|escape:'htmlall':'UTF-8'}{/if}" />
								</td>
							</tr>
						{/foreach}
					{else}
						<tr>
							<td colspan="{count($days_of_week) + 2}"
								class="text-center">
								{l s='No brands found' d='Modules.Brandorderdays.Admin'}
							</td>
						</tr>
					{/if}
				</tbody>
			</table>
		</div>
	</div>

	<div class="panel-footer">
		<button type="submit"
				name="submitBrandorderdaysModule"
				class="btn btn-default pull-right">
			<i class="process-icon-save"></i> {l s='Save' d='Admin.Actions'}
		</button>
	</div>
</form>