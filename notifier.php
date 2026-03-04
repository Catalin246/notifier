<?php
/**
 * Plugin Name: Notifier
 * Description: Lightweight notification engine inspired by Better Notifications for WP with custom trigger support.
 * Version: 1.0.0
 * Author: Venture Capital Team
 */

if (!defined('ABSPATH')) {
	exit;
}

define('NOTIFIER_VERSION', '0.3.0');
define('NOTIFIER_FILE', __FILE__);
define('NOTIFIER_DIR', plugin_dir_path(__FILE__));
define('NOTIFIER_URL', plugin_dir_url(__FILE__));

require_once NOTIFIER_DIR . 'includes/class-notifier-constants.php';
require_once NOTIFIER_DIR . 'includes/class-notifier-trigger-registry.php';
require_once NOTIFIER_DIR . 'includes/class-notifier-post-type.php';
require_once NOTIFIER_DIR . 'includes/class-notifier-template.php';
require_once NOTIFIER_DIR . 'includes/class-notifier-admin.php';
require_once NOTIFIER_DIR . 'includes/class-notifier-dispatcher.php';
require_once NOTIFIER_DIR . 'includes/class-notifier-plugin.php';

Notifier_Plugin::boot();
