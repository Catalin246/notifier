<?php

if (!defined('ABSPATH')) {
	exit;
}

final class Notifier_Template {
	/**
	 * @param string $content Template string.
	 * @param WP_Post $post Trigger post.
	 * @return string
	 */
	public function replace_tokens($content, $post) {
		$author = get_userdata((int) $post->post_author);
		$tokens = array(
			'{post_title}'   => get_the_title($post),
			'{post_url}'     => get_permalink($post),
			'{post_status}'  => $post->post_status,
			'{author_name}'  => $author ? $author->display_name : '',
			'{author_email}' => $author ? $author->user_email : '',
			'{site_name}'    => get_bloginfo('name'),
			'{admin_email}'  => get_option('admin_email'),
		);

		return strtr((string) $content, $tokens);
	}

	/**
	 * @param string $input Comma-separated emails.
	 * @return array<int,string>
	 */
	public function resolve_email_list($input) {
		$emails = array();

		foreach (explode(',', (string) $input) as $item) {
			$email = sanitize_email(trim($item));
			if ('' !== $email && is_email($email)) {
				$emails[] = $email;
			}
		}

		return array_values(array_unique($emails));
	}

	/**
	 * @param mixed $user_ids List of user IDs.
	 * @return array<int,string>
	 */
	public function resolve_user_emails($user_ids) {
		if (!is_array($user_ids)) {
			return array();
		}

		$user_ids = array_values(array_filter(array_map('absint', $user_ids)));
		if (empty($user_ids)) {
			return array();
		}

		$users = get_users(
			array(
				'include' => $user_ids,
				'fields'  => array('user_email'),
			)
		);

		$emails = array();
		foreach ($users as $user) {
			if (!empty($user->user_email) && is_email($user->user_email)) {
				$emails[] = sanitize_email($user->user_email);
			}
		}

		return array_values(array_unique($emails));
	}
}
