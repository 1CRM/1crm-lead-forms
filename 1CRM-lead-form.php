<?php
/**
 * Plugin Name: 1CRM Lead Forms
 * Plugin URI: http://URI_Of_Page_Describing_Plugin_and_Updates
 * Description: Easy form generation for 1CRM lead capture
 * Version: 1.0
 * Author: 1CRM Corp.
 * License: MY_OWN
 */

define( 'OCRMLF_VERSION', '1.0' );
define('OCRMLF_PLIGIN_DIR', dirname(__FILE__));
define ('OCRMLF_INCLUDES_DIR', OCRMLF_PLIGIN_DIR . '/includes');
define ('OCRMLF_ADMIN_DIR', OCRMLF_PLIGIN_DIR . '/admin');

define('OCRMLF_PLUGIN_URL', untrailingslashit(plugins_url('', __FILE__ )));

define ('OCRMLF_TEXTDOMAIN', 'onecrm_lead_forms');

include OCRMLF_PLIGIN_DIR . '/hooks.php';

