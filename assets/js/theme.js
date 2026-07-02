/**
 * Amarilla Tenerife — frontend JavaScript
 *
 * Zajišťuje:
 * - Předvyplnění poptávkového formuláře jménem vozu (z URL parametru)
 * - Smooth scroll pro kotvy
 * - Mobilní menu toggle
 */

(function () {
	'use strict';

	document.addEventListener('DOMContentLoaded', function () {

		/* --- Předvyplnění poptávkového formuláře (Contact Form 7) --- */
		var urlParams = new URLSearchParams(window.location.search);
		var vehicle = urlParams.get('vehicle');
		if (vehicle) {
			var fields = document.querySelectorAll('input[name="vehicle"], input[name="vozidlo"], input[name="your-vehicle"]');
			fields.forEach(function (field) {
				field.value = decodeURIComponent(vehicle);
			});

			// Zvýraznění předmětu v message poli, pokud existuje
			var subjectField = document.querySelector('input[name="your-subject"], input[name="subject"]');
			if (subjectField && !subjectField.value) {
				subjectField.value = 'Poptávka: ' + decodeURIComponent(vehicle);
			}
		}

		/* --- Mobilní menu toggle --- */
		var menuToggle = document.querySelector('.amarilla-menu-toggle');
		var nav = document.querySelector('.amarilla-main-nav');
		if (menuToggle && nav) {
			menuToggle.addEventListener('click', function () {
				var isOpen = nav.classList.toggle('is-open');
				menuToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
			});
		}

		/* --- Smooth scroll pro interní odkazy --- */
		document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
			anchor.addEventListener('click', function (e) {
				var targetId = this.getAttribute('href');
				if (targetId === '#' || targetId.length <= 1) return;
				var target = document.querySelector(targetId);
				if (target) {
					e.preventDefault();
					target.scrollIntoView({ behavior: 'smooth', block: 'start' });
				}
			});
		});

	});

})();
