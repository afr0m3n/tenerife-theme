/**
 * Poměry stran pro justified galerii detailu vozidla.
 */
(function () {
	'use strict';

	document.querySelectorAll('.vehicle-detail-more .wp-block-gallery.has-nested-images').forEach(function (gallery) {
		Array.prototype.forEach.call(gallery.children, function (figure) {
			if (!figure.matches('figure.wp-block-image')) return;

			var image = figure.querySelector('img');
			if (!image) return;

			var setRatio = function () {
				var width = image.naturalWidth || Number(image.getAttribute('width'));
				var height = image.naturalHeight || Number(image.getAttribute('height'));

				if (width > 0 && height > 0) {
					figure.style.setProperty('--vehicle-image-ratio', (width / height).toFixed(6));
				}
			};

			setRatio();
			if (!image.complete || !image.naturalWidth) {
				image.addEventListener('load', setRatio, { once: true });
			}
		});
	});
})();
