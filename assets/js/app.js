(function () {
	'use strict';

	var cfg = window.milkbrosCfg || {};

	/* ---------- Галерея ---------- */

	var slides  = Array.prototype.slice.call(document.querySelectorAll('[data-slide]'));
	var dots    = Array.prototype.slice.call(document.querySelectorAll('[data-dot]'));
	var infos   = Array.prototype.slice.call(document.querySelectorAll('[data-info]'));
	var prevBtn = document.querySelector('[data-prev]');
	var nextBtn = document.querySelector('[data-next]');
	var current = 0;
	var autoTimer = null;

	function show(i) {
		if (!slides.length) { return; }
		current = (i % slides.length + slides.length) % slides.length;
		slides.forEach(function (slide, n) {
			slide.classList.toggle('is-active', n === current);
		});
		dots.forEach(function (dot, n) {
			dot.classList.toggle('is-active', n === current);
			if (n === current) {
				dot.setAttribute('aria-current', 'true');
			} else {
				dot.removeAttribute('aria-current');
			}
		});
		infos.forEach(function (info, n) {
			info.classList.toggle('is-active', n === current);
		});
	}

	function stopAuto() {
		if (autoTimer !== null) {
			clearInterval(autoTimer);
			autoTimer = null;
		}
	}

	// Ручное переключение отключает автопрокрутку насовсем.
	function manual(i) {
		stopAuto();
		show(i);
	}

	if (slides.length > 1) {
		autoTimer = setInterval(function () {
			if (!document.hidden) { show(current + 1); }
		}, 5000);
	}

	dots.forEach(function (dot, n) {
		dot.addEventListener('click', function () { manual(n); });
	});
	if (prevBtn) { prevBtn.addEventListener('click', function () { manual(current - 1); }); }
	if (nextBtn) { nextBtn.addEventListener('click', function () { manual(current + 1); }); }

	/* ---------- Форма обратной связи ---------- */

	var modal     = document.querySelector('[data-modal]');
	var openBtn   = document.querySelector('[data-fb-open]');
	var form      = document.querySelector('[data-fb-form]');
	var statusEl  = document.querySelector('[data-fb-status]');
	var doneEl    = document.querySelector('[data-fb-done]');
	var submitBtn = document.querySelector('[data-fb-submit]');

	function modalOpen() {
		return !!modal && !modal.hidden;
	}

	function openModal() {
		if (!modal) { return; }
		modal.hidden = false;
		var email = form ? form.querySelector('input[name="email"]') : null;
		if (email && !form.hidden) { email.focus(); }
	}

	function resetForm() {
		if (form) {
			form.reset();
			form.hidden = false;
		}
		if (doneEl) { doneEl.hidden = true; }
		if (statusEl) { statusEl.textContent = ''; }
		if (submitBtn) {
			submitBtn.disabled = false;
			submitBtn.textContent = 'Отправить';
		}
	}

	function closeModal() {
		if (!modal) { return; }
		modal.hidden = true;
		resetForm();
		if (openBtn) { openBtn.focus(); }
	}

	if (openBtn) { openBtn.addEventListener('click', openModal); }

	// Закрытие по крестику и по клику вне панели (по подложке).
	if (modal) {
		modal.addEventListener('click', function (e) {
			if (e.target.closest('[data-modal-close]')) { closeModal(); }
		});
	}

	/* ---------- Клавиатура ---------- */

	document.addEventListener('keydown', function (e) {
		if (modalOpen()) {
			if (e.key === 'Escape') { closeModal(); }
			return;
		}
		if (e.key === 'ArrowLeft') {
			manual(current - 1);
		} else if (e.key === 'ArrowRight') {
			manual(current + 1);
		}
	});

	/* ---------- Отправка ---------- */

	if (form) {
		form.addEventListener('submit', function (e) {
			e.preventDefault();
			if (!statusEl || !submitBtn) { return; }

			var email   = form.elements.email.value.trim();
			var message = form.elements.message.value.trim();

			if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
				statusEl.textContent = 'Укажите корректный email.';
				return;
			}
			if (!message) {
				statusEl.textContent = 'Напишите сообщение.';
				return;
			}

			statusEl.textContent = '';
			submitBtn.disabled = true;
			submitBtn.textContent = 'Отправка…';

			var fd = new FormData(form);
			fd.append('action', 'milkbros_feedback');
			fd.append('nonce', cfg.nonce || '');

			fetch(cfg.ajaxUrl || '', { method: 'POST', body: fd, credentials: 'same-origin' })
				.then(function (r) { return r.json(); })
				.then(function (data) {
					if (data && data.success) {
						form.hidden = true;
						if (doneEl) { doneEl.hidden = false; }
					} else {
						statusEl.textContent =
							(data && data.data && data.data.message) ||
							'Не удалось отправить. Попробуйте позже.';
					}
				})
				.catch(function () {
					statusEl.textContent = 'Ошибка сети. Попробуйте позже.';
				})
				.finally(function () {
					submitBtn.disabled = false;
					submitBtn.textContent = 'Отправить';
				});
		});
	}
})();
