<?php
/*
Plugin Name: List Stores by Category for Dokan
Plugin URI: https://wordpress.org/plugins/list-stores-category-dokan/
Description: Create stores list of Dokan stores based on store category
Version: 2.0.0
Author: Jason Herbert
Author URI: https://www.linkedin.com/in/jason-herbert-8817031a4
License: GPLv2 or later
Text Domain: list-stores-category-dokan
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
*/

if (!defined('ABSPATH')) {
	die;
}


define('SC_DOKANSC_URL', plugins_url('/', __FILE__));
define('SC_DOKANSC_PATH', plugin_dir_path(__FILE__));
define('SC_DOKANSC_PLUGIN_NAME', 'List Stores by Category for Dokan');
require_once(SC_DOKANSC_PATH . 'inc/class.sc-dependency-checker.php');




function my_plugin_enqueue_styles()
{
	wp_enqueue_style('my-plugin-style', plugins_url('/assets/style.css', __FILE__));
	wp_enqueue_script('font-awesome-kit', 'https://kit.fontawesome.com/e955595130.js', array(), null, true);
}
add_action('wp_enqueue_scripts', 'my_plugin_enqueue_styles');



class DokanStoreCategoryList
{
	function __construct()
	{
		// register shortcode
		add_shortcode('stores_list_by_category', array($this, 'create_stores_list_by_category_shortcode'));
	}

	function activate()
	{
		// generated shortcode
		// flush rewrite rules
		flush_rewrite_rules();
	}

	function deactivate()
	{
		flush_rewrite_rules();
	}

	function uninstall()
	{
	}

	// function that runs when shortcode is called
	function create_stores_list_by_category_shortcode($attr)
	{

		$args = shortcode_atts(array(

			'name' => 'Uncategorized',

		), $attr);

		$defaults = array(
			'role__in'   => array('seller', 'administrator'),
			'number'     => -1,
			'offset'     => 0,
			'orderby'    => 'registered',
			'order'      => 'ASC',
			'status'     => 'approved',
			'featured'   => '', // yes or no
			'meta_query' => array(),
		);


		$sellers = dokan_get_sellers($defaults);
?>

		<div id="dokan-seller-listing-wrap" class="grid-view">
			<div class="seller-listing-content">
				<ul class="dokan-seller-wrap">


					<?php

					foreach ($sellers['users'] as $seller) {

						$vendor            = dokan()->vendor->get($seller->ID);
						$store_banner_id   = $vendor->get_banner_id();
						$store_name        = $vendor->get_shop_name();
						$store_url         = $vendor->get_shop_url();
						$store_rating      = $vendor->get_rating();
						$is_store_featured = $vendor->is_featured();
						$store_phone       = $vendor->get_phone();
						$store_info        = dokan_get_store_info($seller->ID);
						$store_address     = dokan_get_seller_short_address($seller->ID);
						$image_size = 'full'; // ou toute autre taille d'image que vous souhaitez utiliser, comme 'medium', 'thumbnail', etc.
						$store_banner_url  = $store_banner_id ? wp_get_attachment_image_src($store_banner_id, $image_size ?? 'full') : DOKAN_PLUGIN_ASSEST . '/images/default-store-banner.png';
						$store_cat = dokan()->vendor->get($seller->ID)->get_info_part('categories');

						for ($i = 0; $i < count($store_cat); $i++) {
							if (strtolower($store_cat[$i]->name) == strtolower($args['name'])) : ?>

								<li class="dokan-single-seller woocommerce coloum-<?php echo esc_attr($per_row); ?> <?php echo (!$store_banner_id) ? 'no-banner-img' : ''; ?>">
									<div class="store-wrapper">
										<div class="store-header">
											<div class="store-banner">
												<a href="<?php echo esc_url($store_url); ?>">
													<img src="<?php echo is_array($store_banner_url) ? esc_attr($store_banner_url[0]) : esc_attr($store_banner_url); ?>">
												</a>
											</div>
										</div>
										<a href="<?php echo esc_url($store_url); ?>" title="<?php esc_attr_e('Visit Store', 'dokan-lite'); ?>">

											<div class="store-content <?php echo !$store_banner_id ? esc_attr('default-store-banner') : '' ?>">
												<div class="store-data-container">
													<div class="featured-favourite">
														<?php if ($is_store_featured) : ?>
															<div class="featured-label"><?php esc_html_e('Featured', 'dokan-lite'); ?></div>
														<?php endif ?>

														<?php do_action('dokan_seller_listing_after_featured', $seller, $store_info); ?>
													</div>

													<?php
													$dokan_store_time_enabled = isset($store_info['dokan_store_time_enabled']) ? $store_info['dokan_store_time_enabled'] : '';
													$dokan_store_time_enabled = isset($store_info['dokan_store_time_enabled']) ? $store_info['dokan_store_time_enabled'] : '';
													$store_open_notice        = isset($store_info['dokan_store_open_notice']) && !empty($store_info['dokan_store_open_notice']) ? $store_info['dokan_store_open_notice'] : __('Store Open', 'dokan-lite');
													$store_closed_notice      = isset($store_info['dokan_store_close_notice']) && !empty($store_info['dokan_store_close_notice']) ? $store_info['dokan_store_close_notice'] : __('Store Closed', 'dokan-lite');
													$show_store_open_close    = dokan_get_option('store_open_close', 'dokan_appearance', 'on');
													if ('on' === $show_store_open_close && 'yes' === $dokan_store_time_enabled) : ?>
														<?php if (dokan_is_store_open($seller->ID)) { ?>
															<span class="dokan-store-is-open-close-status dokan-store-is-open-status" title="<?php esc_attr_e($store_open_notice); ?>"><?php esc_html_e('Open', 'dokan-lite'); ?></span>
														<?php } else { ?>
															<span class="dokan-store-is-open-close-status dokan-store-is-closed-status" title="<?php esc_attr_e($store_closed_notice); ?>"><?php esc_html_e('Closed', 'dokan-lite'); ?></span>
														<?php } ?>
													<?php endif ?>

													<div class="store-data <?php echo esc_attr($store_open_is_on); ?>">
														<h2 class="storetitle_homepage"><?php echo esc_html($store_name); ?></h2>

														<?php if (!empty($store_rating['count'])) : ?>
															<div class="dokan-seller-rating" title="<?php echo sprintf(esc_attr__('Rated %s out of 5', 'dokan-lite'), esc_attr($store_rating['rating'])) ?>">
																<?php echo wp_kses_post(dokan_generate_ratings($store_rating['rating'], 5)); ?>
																<p class="rating">
																	<?php echo esc_html(sprintf(__('%s out of 5', 'dokan-lite'), $store_rating['rating'])); ?>
																</p>
															</div>
														<?php endif ?>

														<?php if (!dokan_is_vendor_info_hidden('address') && $store_address) : ?>
															<?php
															$allowed_tags = array(
																'span' => array(
																	'class' => array(),
																),
																'br' => array()
															);
															?>
															<p class="store-address"><?php echo wp_kses($store_address, $allowed_tags); ?></p>
														<?php endif ?>

														<?php if (!dokan_is_vendor_info_hidden('phone') && $store_phone) { ?>
															<p class="store-phone">
																<i class="fa fa-phone" aria-hidden="true"></i> <?php echo esc_html($store_phone); ?>
															</p>
														<?php } ?>

														<?php do_action('dokan_seller_listing_after_store_data', $seller, $store_info); ?>
													</div>
												</div>
											</div>

											<div class="store-footer">
												<div class="seller-avatar">
													<img src="<?php echo esc_url($vendor->get_avatar()) ?>" alt="<?php echo esc_attr($vendor->get_shop_name()) ?>" size="150">
												</div>
												<div class="showstore">
													<i class="fa-solid fa-arrow-right dokan-btn-theme dokan-btn-round" style="/*! display: inline; *//*! block-size: ; */"></i>
													<p style="margin-bottom: 0px !important;">Discover <?php echo esc_html($store_name); ?></p>
												</div>
											</div>
									</div></a>
								</li>
					<?php endif;
						}
					} ?>
				</ul>
			</div>
		</div>
<?php

		return;
	}
}

$dependency = new SC_Dependency_Checker();
if ($dependency->check()) {
	if (class_exists('DokanStoreCategoryList')) {
		$dokanCarousel = new DokanStoreCategoryList();
	}
}
