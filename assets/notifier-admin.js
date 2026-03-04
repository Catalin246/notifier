(function($) {
	$(function() {
		var $wrap = $('.notifier-user-picker');
		if (!$wrap.length) {
			return;
		}

		var $toggle = $wrap.find('.notifier-user-picker__toggle');
		var $panel = $wrap.find('.notifier-user-picker__panel');
		var $checks = $wrap.find('input[type="checkbox"]');
		var selectLabel = (window.notifierAdminI18n && window.notifierAdminI18n.selectRecipients) || 'Select recipients';
		var suffixLabel = (window.notifierAdminI18n && window.notifierAdminI18n.selectedSuffix) || 'user(s) selected';

		function updateLabel() {
			var count = $checks.filter(':checked').length;
			$toggle.text(count ? count + ' ' + suffixLabel : selectLabel);
		}

		$toggle.on('click', function(e) {
			e.preventDefault();
			$panel.toggle();
			$toggle.attr('aria-expanded', $panel.is(':visible') ? 'true' : 'false');
		});

		$(document).on('click', function(e) {
			if (!$wrap.is(e.target) && $wrap.has(e.target).length === 0) {
				$panel.hide();
				$toggle.attr('aria-expanded', 'false');
			}
		});

		$checks.on('change', updateLabel);
		updateLabel();
	});
})(jQuery);
