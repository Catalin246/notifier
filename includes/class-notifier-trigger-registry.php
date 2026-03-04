<?php

if (!defined('ABSPATH')) {
	exit;
}

final class Notifier_Trigger_Registry {
	/**
	 * @var array<string,string>
	 */
	private $triggers = array();

	public function __construct() {
		$this->register(
			Notifier_Constants::TRIGGER_PENDING_NEW_POST,
			__('Post created for pending review', 'notifier')
		);

		$this->register(
			Notifier_Constants::TRIGGER_PENDING_PUBLISHED,
			__('Post sent from pending review to published', 'notifier')
		);
	}

	/**
	 * @param string $key Trigger key.
	 * @param string $label Trigger label.
	 */
	public function register($key, $label) {
		$key = sanitize_key($key);
		if ('' === $key || '' === $label) {
			return;
		}

		$this->triggers[$key] = $label;
	}

	/**
	 * @return array<string,string>
	 */
	public function all() {
		return apply_filters('notifier_triggers', $this->triggers);
	}

	/**
	 * @param string $key Trigger key.
	 * @return bool
	 */
	public function is_valid($key) {
		$triggers = $this->all();
		return isset($triggers[$key]);
	}
}
