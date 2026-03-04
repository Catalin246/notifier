<?php

if (!defined('ABSPATH')) {
	exit;
}

final class Notifier_Admin {
	/**
	 * @var Notifier_Trigger_Registry
	 */
	private $trigger_registry;

	public function __construct(Notifier_Trigger_Registry $trigger_registry) {
		$this->trigger_registry = $trigger_registry;
	}

	public function hooks() {
		add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
		add_action('add_meta_boxes', array($this, 'register_meta_boxes'));
		add_action('save_post_' . Notifier_Constants::POST_TYPE, array($this, 'save_notification_meta'));
	}

	/**
	 * @param string $hook_suffix Current admin hook.
	 */
	public function enqueue_admin_assets($hook_suffix) {
		if (!$this->is_notification_editor_screen($hook_suffix)) {
			return;
		}

		wp_enqueue_style(
			'notifier-admin',
			NOTIFIER_URL . 'assets/notifier-admin.css',
			array(),
			NOTIFIER_VERSION
		);

		wp_enqueue_script(
			'notifier-admin',
			NOTIFIER_URL . 'assets/notifier-admin.js',
			array('jquery'),
			NOTIFIER_VERSION,
			true
		);

		wp_localize_script(
			'notifier-admin',
			'notifierAdminI18n',
			array(
				'selectRecipients' => __('Select recipients', 'notifier'),
				'selectedSuffix'   => __('user(s) selected', 'notifier'),
			)
		);
	}

	public function register_meta_boxes() {
		add_meta_box(
			'notifier-notification-settings',
			__('Notification Settings', 'notifier'),
			array($this, 'render_notification_metabox'),
			Notifier_Constants::POST_TYPE,
			'normal',
			'default'
		);
	}

	/**
	 * @param WP_Post $post Notification post.
	 */
	public function render_notification_metabox($post) {
		$defaults = $this->default_notification_values();
		$triggers = $this->trigger_registry->all();

		$enabled        = get_post_meta($post->ID, Notifier_Constants::META_ENABLED, true);
		$trigger        = get_post_meta($post->ID, Notifier_Constants::META_TRIGGER, true);
		$recipient_ids  = get_post_meta($post->ID, Notifier_Constants::META_RECIPIENT_USERS, true);
		$from_email     = get_post_meta($post->ID, Notifier_Constants::META_FROM_EMAIL, true);
		$subject        = get_post_meta($post->ID, Notifier_Constants::META_SUBJECT, true);
		$message        = get_post_meta($post->ID, Notifier_Constants::META_MESSAGE, true);
		$send_to_author = get_post_meta($post->ID, Notifier_Constants::META_SEND_TO_AUTHOR, true);

		if ('' === $enabled) {
			$enabled = $defaults['enabled'];
		}
		if ('' === $trigger || !isset($triggers[$trigger])) {
			$trigger = $defaults['trigger'];
		}
		if ('' === $subject) {
			$subject = $defaults['subject'];
		}
		if ('' === $from_email) {
			$from_email = $defaults['from_email'];
		}
		if ('' === $message) {
			$message = $defaults['message'];
		}
		if ('' === $send_to_author) {
			$send_to_author = $defaults['send_to_author'];
		}
		if (!is_array($recipient_ids)) {
			$recipient_ids = $defaults['recipient_ids'];
		}

		$selected_ids = array_map('intval', $recipient_ids);
		$all_users    = get_users(
			array(
				'fields'  => array('ID', 'display_name', 'user_email'),
				'orderby' => 'display_name',
				'order'   => 'ASC',
			)
		);

		wp_nonce_field(Notifier_Constants::METABOX_NONCE_ACTION, Notifier_Constants::METABOX_NONCE_FIELD);
		?>
		<p>
			<label>
				<input type="checkbox" name="notifier_enabled" value="1" <?php checked(!empty($enabled)); ?> />
				<?php esc_html_e('Enabled', 'notifier'); ?>
			</label>
		</p>

		<p>
			<label for="notifier_trigger"><strong><?php esc_html_e('Trigger', 'notifier'); ?></strong></label><br />
			<select id="notifier_trigger" name="notifier_trigger">
				<?php foreach ($triggers as $key => $label) : ?>
					<option value="<?php echo esc_attr($key); ?>" <?php selected($trigger, $key); ?>><?php echo esc_html($label); ?></option>
				<?php endforeach; ?>
			</select>
		</p>

		<p>
			<label><strong><?php esc_html_e('Email Recipients', 'notifier'); ?></strong></label><br />
			<div class="notifier-user-picker">
				<button type="button" class="button notifier-user-picker__toggle" aria-expanded="false"><?php esc_html_e('Select recipients', 'notifier'); ?></button>
				<div class="notifier-user-picker__panel">
					<?php foreach ($all_users as $user) : ?>
						<label class="notifier-user-picker__item">
							<input type="checkbox" name="notifier_recipient_users[]" value="<?php echo esc_attr($user->ID); ?>" <?php checked(in_array((int) $user->ID, $selected_ids, true)); ?> />
							<?php echo esc_html($user->display_name . ' (' . $user->user_email . ')'); ?>
						</label>
					<?php endforeach; ?>
				</div>
			</div>
			<span class="description"><?php esc_html_e('Open dropdown and select one or more users.', 'notifier'); ?></span>
		</p>

		<p>
			<label>
				<input type="checkbox" name="notifier_send_to_author" value="1" <?php checked(!empty($send_to_author)); ?> />
				<?php esc_html_e('Also send to the author of the post that triggered this notification', 'notifier'); ?>
			</label>
		</p>

		<p>
			<label for="notifier_from_email"><strong><?php esc_html_e('From Email', 'notifier'); ?></strong></label><br />
			<input type="email" class="widefat" id="notifier_from_email" name="notifier_from_email" value="<?php echo esc_attr($from_email); ?>" />
			<span class="description"><?php esc_html_e('Optional. If set, emails are sent with this From address.', 'notifier'); ?></span>
		</p>

		<p>
			<label for="notifier_subject"><strong><?php esc_html_e('Email Subject', 'notifier'); ?></strong></label><br />
			<input type="text" class="widefat" id="notifier_subject" name="notifier_subject" value="<?php echo esc_attr($subject); ?>" />
		</p>

		<p>
			<label for="notifier_message"><strong><?php esc_html_e('Email Message', 'notifier'); ?></strong></label><br />
			<textarea class="widefat" rows="8" id="notifier_message" name="notifier_message"><?php echo esc_textarea($message); ?></textarea>
		</p>

		<p class="description">
			<?php esc_html_e('Available tokens: {post_title}, {post_url}, {post_status}, {author_name}, {author_email}, {site_name}, {admin_email}.', 'notifier'); ?>
		</p>
		<?php
	}

	/**
	 * @param int $post_id Notification post ID.
	 */
	public function save_notification_meta($post_id) {
		if (!isset($_POST[Notifier_Constants::METABOX_NONCE_FIELD])) {
			return;
		}

		$nonce = sanitize_text_field(wp_unslash($_POST[Notifier_Constants::METABOX_NONCE_FIELD]));
		if (!wp_verify_nonce($nonce, Notifier_Constants::METABOX_NONCE_ACTION)) {
			return;
		}

		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return;
		}

		if (!current_user_can('edit_post', $post_id)) {
			return;
		}

		$trigger = isset($_POST['notifier_trigger']) ? sanitize_key(wp_unslash($_POST['notifier_trigger'])) : '';
		if (!$this->trigger_registry->is_valid($trigger)) {
			$trigger = Notifier_Constants::TRIGGER_PENDING_NEW_POST;
		}

		$recipient_ids = isset($_POST['notifier_recipient_users']) && is_array($_POST['notifier_recipient_users'])
			? array_map('absint', wp_unslash($_POST['notifier_recipient_users']))
			: array();
		$recipient_ids = array_values(array_filter($recipient_ids));

		$from_email     = isset($_POST['notifier_from_email']) ? sanitize_email(wp_unslash($_POST['notifier_from_email'])) : '';
		$subject        = isset($_POST['notifier_subject']) ? sanitize_text_field(wp_unslash($_POST['notifier_subject'])) : '';
		$message        = isset($_POST['notifier_message']) ? wp_kses_post(wp_unslash($_POST['notifier_message'])) : '';
		$enabled        = isset($_POST['notifier_enabled']) ? 1 : 0;
		$send_to_author = isset($_POST['notifier_send_to_author']) ? 1 : 0;

		update_post_meta($post_id, Notifier_Constants::META_ENABLED, $enabled);
		update_post_meta($post_id, Notifier_Constants::META_TRIGGER, $trigger);
		update_post_meta($post_id, Notifier_Constants::META_RECIPIENT_USERS, $recipient_ids);
		update_post_meta($post_id, Notifier_Constants::META_FROM_EMAIL, $from_email);
		update_post_meta($post_id, Notifier_Constants::META_SUBJECT, $subject);
		update_post_meta($post_id, Notifier_Constants::META_MESSAGE, $message);
		update_post_meta($post_id, Notifier_Constants::META_SEND_TO_AUTHOR, $send_to_author);
	}

	/**
	 * @return array<string,mixed>
	 */
	private function default_notification_values() {
		return array(
			'enabled'        => 1,
			'trigger'        => Notifier_Constants::TRIGGER_PENDING_NEW_POST,
			'recipient_ids'  => array(),
			'send_to_author' => 0,
			'from_email'     => '',
			'subject'        => '',
			'message'        => '',
		);
	}

	/**
	 * @param string $hook_suffix Current admin hook.
	 * @return bool
	 */
	private function is_notification_editor_screen($hook_suffix) {
		if ('post.php' !== $hook_suffix && 'post-new.php' !== $hook_suffix) {
			return false;
		}

		$screen = get_current_screen();
		return $screen && Notifier_Constants::POST_TYPE === $screen->post_type;
	}
}
