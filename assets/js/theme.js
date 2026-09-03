/**
 * Amarilla Tenerife — frontend JavaScript
 *
 * Zajišťuje:
 * - Synchronizaci převodovek s vozidlem v poptávkovém CF7 formuláři
 * - Smooth scroll pro kotvy
 * - Mobilní menu toggle
 */

(function () {
	'use strict';

	document.addEventListener('DOMContentLoaded', function () {
		/* --- Převodovky podle vybraného vozu (Contact Form 7) --- */
		document.querySelectorAll('.wpcf7 form').forEach(function (form) {
			var vehicleSelect = form.querySelector('select[name="select-auto"]');
			var transmissionSelect = form.querySelector('select[name="transmission"]');
			var dataElement = form.querySelector('.amarilla-vehicle-transmission-data');

			if (!vehicleSelect || !transmissionSelect || !dataElement || vehicleSelect.dataset.amarillaTransmissionBound) {
				return;
			}

			var data;
			try {
				data = JSON.parse(dataElement.textContent);
			} catch (error) {
				return;
			}

			if (!data || !data.vehicles || !data.labels) {
				return;
			}

			function getTransmissionChoices() {
				var available = data.vehicles[vehicleSelect.value];

				if (Array.isArray(available) && available.length === 1 && data.labels[available[0]]) {
					return [data.labels[available[0]]];
				}

				return [data.labels.any, data.labels.manual, data.labels.automatic];
			}

			function updateTransmissionChoices() {
				var currentValue = transmissionSelect.value;
				var choices = getTransmissionChoices();

				while (transmissionSelect.firstChild) {
					transmissionSelect.removeChild(transmissionSelect.firstChild);
				}

				choices.forEach(function (choice) {
					var option = document.createElement('option');
					option.value = choice;
					option.textContent = choice;
					transmissionSelect.appendChild(option);
				});

				transmissionSelect.value = choices.indexOf(currentValue) !== -1 ? currentValue : choices[0];
			}

			vehicleSelect.dataset.amarillaTransmissionBound = '1';
			vehicleSelect.addEventListener('change', updateTransmissionChoices);
			form.addEventListener('reset', function () {
				window.setTimeout(updateTransmissionChoices, 0);
			});
			updateTransmissionChoices();
		});

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
