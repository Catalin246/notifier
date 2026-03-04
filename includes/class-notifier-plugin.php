<?php

if (!defined('ABSPATH')) {
	exit;
}

final class Notifier_Plugin {
	/**
	 * @var Notifier_Plugin|null
	 */
	private static $instance = null;

	/**
	 * @var Notifier_Trigger_Registry
	 */
	private $trigger_registry;

	/**
	 * @var Notifier_Post_Type
	 */
	private $post_type;

	/**
	 * @var Notifier_Admin
	 */
	private $admin;

	/**
	 * @var Notifier_Dispatcher
	 */
	private $dispatcher;

	private function __construct() {
		$template              = new Notifier_Template();
		$this->trigger_registry = new Notifier_Trigger_Registry();
		$this->post_type        = new Notifier_Post_Type();
		$this->admin            = new Notifier_Admin($this->trigger_registry);
		$this->dispatcher       = new Notifier_Dispatcher($template);
	}

	public static function boot() {
		if (null === self::$instance) {
			self::$instance = new self();
			self::$instance->hooks();
		}

		return self::$instance;
	}

	private function hooks() {
		$this->post_type->hooks();
		$this->admin->hooks();
		$this->dispatcher->hooks();
	}
}
