<?php

if (!defined('ABSPATH')) {
	exit;
}

final class Notifier_Constants {
	const POST_TYPE = 'notifier_notice';

	const META_ENABLED         = '_notifier_enabled';
	const META_TRIGGER         = '_notifier_trigger';
	const META_TO              = '_notifier_to';
	const META_RECIPIENT_USERS = '_notifier_recipient_users';
	const META_FROM_EMAIL      = '_notifier_from_email';
	const META_SUBJECT         = '_notifier_subject';
	const META_MESSAGE         = '_notifier_message';
	const META_SEND_TO_AUTHOR  = '_notifier_send_to_author';

	const METABOX_NONCE_ACTION = 'notifier_notification_save';
	const METABOX_NONCE_FIELD  = 'notifier_notification_nonce';

	const TRIGGER_PENDING_NEW_POST  = 'post_created_pending_review';
	const TRIGGER_PENDING_PUBLISHED = 'post_pending_to_published';
}
