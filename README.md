# Notifier

Notifier is a lightweight notification plugin inspired by Better Notifications for WP.

## Current functionality

- Notifications are stored as a custom post type: `Notifier Notifications`
- Create/edit notifications in WordPress admin from the `Notifier` menu
- Notification fields:
  - Enabled
  - Trigger
  - Email recipients (multi-select real WP users)
  - Send to post author
  - From email (optional)
  - Subject
  - Message
- Token replacement in subject/message:
  - `{post_title}`
  - `{post_url}`
  - `{post_status}`
  - `{author_name}`
  - `{author_email}`
  - `{site_name}`
  - `{admin_email}`
- Triggers implemented:
  - `When a post is created in pending review`
  - `When a post is sent from pending review to published`

## How to use

1. Activate the plugin.
2. Go to `Notifier -> Add Notification`.
3. Configure trigger, recipients, subject, and message.
4. Optionally enable `Also send to the author of the post that triggered this notification`.
5. Publish the notification post.
6. Create a new post and set status to `Pending Review` on creation.

## Custom triggers (extensible)

You can register additional trigger labels via filter:

```php
add_filter('notifier_triggers', function($triggers) {
	$triggers['my_custom_trigger'] = 'My custom trigger label';
	return $triggers;
});
```

Then dispatch your trigger from plugin code that handles your event.

## Architecture (Separation of Concerns)

- `notifier.php`: plugin bootstrap only (constants + wiring)
- `includes/class-notifier-plugin.php`: application composition and hook bootstrapping
- `includes/class-notifier-constants.php`: shared keys/slugs/meta constants
- `includes/class-notifier-post-type.php`: custom post type registration
- `includes/class-notifier-trigger-registry.php`: trigger registration/validation/filter exposure
- `includes/class-notifier-admin.php`: admin menu, metabox UI, admin assets, save handling
- `includes/class-notifier-dispatcher.php`: trigger listeners and notification sending flow
- `includes/class-notifier-template.php`: token replacement and email resolution helpers
- `assets/notifier-admin.js`, `assets/notifier-admin.css`: recipients picker behavior and styling
