<?php

if (!defined('ABSPATH')) {
	exit;
}

final class Notifier_Dispatcher {
	/**
	 * @var Notifier_Template
	 */
	private $template;

	public function __construct(Notifier_Template $template) {
		$this->template = $template;
	}

	public function hooks() {
		add_action('wp_after_insert_post', array($this, 'on_post_inserted'), 10, 4);
		add_action('transition_post_status', array($this, 'on_pending_to_publish'), 10, 3);
	}

	/**
	 * Fires after post insert/update to detect newly created pending posts.
	 *
	 * @param int          $post_id     Post ID.
	 * @param WP_Post      $post        Post object.
	 * @param bool         $update      Whether this is an existing post update.
	 * @param WP_Post|null $post_before Previous post object before update.
	 */
	public function on_post_inserted($post_id, $post, $update, $post_before) {
		if (!$post instanceof WP_Post || 'post' !== $post->post_type) {
			return;
		}

		if ($update) {
			return;
		}

		if ('pending' !== $post->post_status) {
			return;
		}

		$this->dispatch(Notifier_Constants::TRIGGER_PENDING_NEW_POST, $post);
	}

	/**
	 * @param string $new_status New status.
	 * @param string $old_status Old status.
	 * @param WP_Post $post Post object.
	 */
	public function on_pending_to_publish($new_status, $old_status, $post) {
		if (!$post instanceof WP_Post || 'post' !== $post->post_type) {
			return;
		}

		if ('pending' !== $old_status || 'publish' !== $new_status) {
			return;
		}

		$this->dispatch(Notifier_Constants::TRIGGER_PENDING_PUBLISHED, $post);
	}

	/**
	 * @param string $trigger Trigger key.
	 * @param WP_Post $post Trigger post.
	 */
	private function dispatch($trigger, $post) {
		$notifications = get_posts(
			array(
				'post_type'      => Notifier_Constants::POST_TYPE,
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'orderby'        => 'ID',
				'order'          => 'ASC',
				'meta_query'     => array(
					'relation' => 'AND',
					array(
						'key'   => Notifier_Constants::META_ENABLED,
						'value' => '1',
					),
					array(
						'key'   => Notifier_Constants::META_TRIGGER,
						'value' => $trigger,
					),
				),
			)
		);

		if (empty($notifications)) {
			return;
		}

		foreach ($notifications as $notification) {
			$recipient_ids  = get_post_meta($notification->ID, Notifier_Constants::META_RECIPIENT_USERS, true);
			$legacy_to      = (string) get_post_meta($notification->ID, Notifier_Constants::META_TO, true);
			$from_email     = (string) get_post_meta($notification->ID, Notifier_Constants::META_FROM_EMAIL, true);
			$subject        = (string) get_post_meta($notification->ID, Notifier_Constants::META_SUBJECT, true);
			$message        = (string) get_post_meta($notification->ID, Notifier_Constants::META_MESSAGE, true);
			$send_to_author = (int) get_post_meta($notification->ID, Notifier_Constants::META_SEND_TO_AUTHOR, true);

			$resolved_to = $this->template->resolve_user_emails($recipient_ids);

			if ('' !== $legacy_to) {
				$resolved_to = array_merge(
					$resolved_to,
					$this->template->resolve_email_list($this->template->replace_tokens($legacy_to, $post))
				);
			}

			if (1 === $send_to_author) {
				$author = get_userdata((int) $post->post_author);
				if ($author && !empty($author->user_email) && is_email($author->user_email)) {
					$resolved_to[] = sanitize_email($author->user_email);
				}
			}

			$resolved_to = array_values(array_unique(array_filter($resolved_to)));
			if (empty($resolved_to)) {
				continue;
			}

			$headers = array();
			if ('' !== $from_email) {
				$from_email = sanitize_email($this->template->replace_tokens($from_email, $post));
				if ('' !== $from_email && is_email($from_email)) {
					$headers[] = 'From: ' . $from_email;
				}
			}

			wp_mail(
				$resolved_to,
				$this->template->replace_tokens($subject, $post),
				$this->template->replace_tokens($message, $post),
				$headers
			);
		}
	}
}
