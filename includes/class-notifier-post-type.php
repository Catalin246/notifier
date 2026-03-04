<?php

if (!defined('ABSPATH')) {
	exit;
}

final class Notifier_Post_Type {
	public function hooks() {
		add_action('init', array($this, 'register'));
	}

	public function register() {
		$labels = array(
			'name'               => __('Notifier Notifications', 'notifier'),
			'singular_name'      => __('Notifier Notification', 'notifier'),
			'menu_name'          => __('Notifications', 'notifier'),
			'add_new'            => __('Add Notification', 'notifier'),
			'add_new_item'       => __('Add Notification', 'notifier'),
			'edit_item'          => __('Edit Notification', 'notifier'),
			'new_item'           => __('New Notification', 'notifier'),
			'view_item'          => __('View Notification', 'notifier'),
			'search_items'       => __('Search Notifications', 'notifier'),
			'not_found'          => __('No notifications found', 'notifier'),
			'not_found_in_trash' => __('No notifications found in Trash', 'notifier'),
		);

		register_post_type(
			Notifier_Constants::POST_TYPE,
			array(
				'labels'              => $labels,
				'public'              => false,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'show_in_admin_bar'   => false,
				'has_archive'         => false,
				'exclude_from_search' => true,
				'publicly_queryable'  => false,
				'supports'            => array('title'),
				'menu_position'       => 59,
				'menu_icon'           => 'dashicons-email-alt',
			)
		);
	}
}
