(function () {
	'use strict';

	var cfg = window.milkbrosCfg || {};

	/* ---------- Словарь интерфейса ---------- */

	var I18N = {
		ru: {
			fbOpen: 'Обратная связь',
			fbTitle: '// обратная связь',
			email: '> ваш email',
			message: '> сообщение',
			send: 'Отправить',
			sending: 'Отправка…',
			done: 'Спасибо, ваше письмо отправлено!',
			errEmail: 'Укажите корректный email.',
			errMessage: 'Напишите сообщение.',
			errNet: 'Ошибка сети. Попробуйте позже.',
			errSend: 'Не удалось отправить письмо. Попробуйте позже.',
			errWait: 'Слишком часто. Подождите полминуты.'
		},
		en: {
			fbOpen: 'Contact us',
			fbTitle: '// contact',
			email: '> your email',
			message: '> message',
			send: 'Send',
			sending: 'Sending…',
			done: 'Thank you, your message has been sent!',
			errEmail: 'Please enter a valid email.',
			errMessage: 'Please write a message.',
			errNet: 'Network error. Please try again later.',
			errSend: 'Could not send the message. Please try again later.',
			errWait: 'Too many requests — wait half a minute.'
		}
	};

	// Ошибки сервер присылает по-русски — сопоставление для перевода.
	var SERVER_MSG = {
		'Укажите корректный email.': 'errEmail',
		'Напишите сообщение.': 'errMessage',
		'Не удалось отправить письмо. Попробуйте позже.': 'errSend',
		'Слишком часто. Подождите полминуты.': 'errWait'
	};

	var lang = 'ru';
	try {
		if (localStorage.getItem('mb_lang') === 'en') { lang = 'en'; }
	} catch (e) {}

	function t(key) { return I18N[lang][key]; }

	/* ---------- Галерея ---------- */

	var slides  = Array.prototype.slice.call(document.querySelectorAll('[data-slide]'));
	var dots    = Array.prototype.slice.call(document.querySelectorAll('[data-dot]'));
	var infos   = Array.prototype.slice.call(document.querySelectorAll('[data-info]'));
	var prevBtn = document.querySelector('[data-prev]');
	var nextBtn = document.querySelector('[data-next]');
	var current = 0;
	var autoTimer = null;

	// Видим только текст активного слайда на активном языке.
	function syncInfos() {
		infos.forEach(function (el) {
			var on = Number(el.getAttribute('data-info')) === current &&
				el.getAttribute('data-lang') === lang;
			el.classList.toggle('is-active', on);
			el.hidden = !on;
		});
	}

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
		syncInfos();
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

	/* ---------- Переключатель языка ---------- */

	var langBtn = document.querySelector('[data-lang-toggle]');

	function applyLang() {
		document.documentElement.setAttribute('lang', lang === 'en' ? 'en' : 'ru-RU');
		Array.prototype.forEach.call(document.querySelectorAll('[data-i18n]'), function (el) {
			if (el === submitBtn && submitBtn.disabled) { return; } // идёт отправка
			var key = el.getAttribute('data-i18n');
			if (I18N[lang][key]) { el.textContent = I18N[lang][key]; }
		});
		if (langBtn) {
			Array.prototype.forEach.call(langBtn.querySelectorAll('[data-lang-opt]'), function (opt) {
				opt.classList.toggle('is-on', opt.getAttribute('data-lang-opt') === lang);
			});
		}
		syncInfos();
	}

	if (langBtn) {
		langBtn.addEventListener('click', function () {
			lang = lang === 'ru' ? 'en' : 'ru';
			try { localStorage.setItem('mb_lang', lang); } catch (e) {}
			applyLang();
		});
	}

	/* ---------- Модалка ---------- */

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
			submitBtn.textContent = t('send');
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
				statusEl.textContent = t('errEmail');
				return;
			}
			if (!message) {
				statusEl.textContent = t('errMessage');
				return;
			}

			statusEl.textContent = '';
			submitBtn.disabled = true;
			submitBtn.textContent = t('sending');

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
						var raw = (data && data.data && data.data.message) || '';
						statusEl.textContent = SERVER_MSG[raw] ? t(SERVER_MSG[raw]) : (raw || t('errSend'));
					}
				})
				.catch(function () {
					statusEl.textContent = t('errNet');
				})
				.finally(function () {
					submitBtn.disabled = false;
					submitBtn.textContent = t('send');
				});
		});
	}

	/* ---------- Старт ---------- */

	applyLang();
})();
