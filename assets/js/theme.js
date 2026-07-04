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

		/* --- Kompaktní jazykový přepínač na mobilu --- */
		document.querySelectorAll('.amarilla-lang-switcher').forEach(function (switcher) {
			var langToggle = switcher.querySelector('.amarilla-lang-toggle');
			var langLinks = switcher.querySelectorAll('.lang-link');

			if (!langToggle) return;

			function setLanguageMenuState(isOpen) {
				switcher.classList.toggle('is-open', isOpen);
				langToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
			}

			langToggle.addEventListener('click', function (event) {
				event.stopPropagation();
				setLanguageMenuState(!switcher.classList.contains('is-open'));
			});

			langLinks.forEach(function (link) {
				link.addEventListener('click', function () {
					setLanguageMenuState(false);
				});
			});

			document.addEventListener('click', function (event) {
				if (!switcher.contains(event.target)) {
					setLanguageMenuState(false);
				}
			});

			document.addEventListener('keydown', function (event) {
				if (event.key === 'Escape') {
					setLanguageMenuState(false);
				}
			});
		});

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
