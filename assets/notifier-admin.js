(function($) {
	$(function() {
		var $wraps = $('.notifier-user-picker');
		if (!$wraps.length) {
			return;
		}

		$wraps.each(function() {
			var $wrap = $(this);
			var $toggle = $wrap.find('.notifier-user-picker__toggle');
			var $panel = $wrap.find('.notifier-user-picker__panel');
			var $checks = $wrap.find('input[type="checkbox"]');

			var selectLabel = $toggle.data('empty-label') || 'Select';
			var suffixLabel = $toggle.data('selected-suffix') || 'selected';

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
	});
})(jQuery);
