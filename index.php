<?php
/**
 * Единственный шаблон темы: полноэкранная галерея, инфоблок и форма обратной связи.
 */

$milkbros_slides = milkbros_get_slides();
$milkbros_texts  = milkbros_get_overlay_texts();
$milkbros_lang   = milkbros_get_default_lang();
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php if ( function_exists( 'wp_body_open' ) ) { wp_body_open(); } ?>

<main class="mb-stage" aria-label="Галерея">
<?php foreach ( $milkbros_slides as $i => $url ) : ?>
	<div class="mb-slide<?php echo 0 === $i ? ' is-active' : ''; ?>" data-slide>
		<img src="<?php echo esc_url( $url ); ?>" alt="<?php echo esc_attr( 'Этикетка ' . ( $i + 1 ) ); ?>" decoding="async">
	</div>
<?php endforeach; ?>
</main>

<aside class="mb-info">
	<div class="mb-info__bar">
		<button type="button" class="mb-lang" data-lang-toggle aria-label="Язык / Language">
			<span class="mb-lang__opt<?php echo 'ru' === $milkbros_lang ? ' is-on' : ''; ?>" data-lang-opt="ru">RU</span><span class="mb-lang__sep">/</span><span class="mb-lang__opt<?php echo 'en' === $milkbros_lang ? ' is-on' : ''; ?>" data-lang-opt="en">EN</span>
		</button>
		<button type="button" class="mb-info__x" data-info-min data-i18n-aria="infoMin" aria-label="Свернуть описание">&times;</button>
	</div>
<?php foreach ( $milkbros_texts as $i => $pair ) : ?>
	<?php $milkbros_ru_on = ( 0 === $i && 'ru' === $milkbros_lang ); ?>
	<?php $milkbros_en_on = ( 0 === $i && 'en' === $milkbros_lang ); ?>
	<div class="mb-info__text<?php echo $milkbros_ru_on ? ' is-active' : ''; ?>" data-info="<?php echo (int) $i; ?>" data-lang="ru"<?php echo $milkbros_ru_on ? '' : ' hidden'; ?>>
		<?php echo milkbros_format_overlay_text( $pair['ru'] ); ?>
	</div>
	<div class="mb-info__text<?php echo $milkbros_en_on ? ' is-active' : ''; ?>" data-info="<?php echo (int) $i; ?>" data-lang="en"<?php echo $milkbros_en_on ? '' : ' hidden'; ?>>
		<?php echo milkbros_format_overlay_text( $pair['en'] ); ?>
	</div>
<?php endforeach; ?>
</aside>

<button type="button" class="mb-info-dot" data-info-restore data-i18n-aria="infoShow" aria-label="Показать описание" hidden>
	<span class="mb-info-dot__mark" aria-hidden="true"></span>
</button>

<nav class="mb-nav" aria-label="Управление галереей">
	<button type="button" class="mb-arrow" data-prev aria-label="Предыдущий слайд">&#8249;</button>
	<div class="mb-dots">
	<?php foreach ( $milkbros_slides as $i => $url ) : ?>
		<button type="button" class="mb-dot<?php echo 0 === $i ? ' is-active' : ''; ?>" data-dot<?php echo 0 === $i ? ' aria-current="true"' : ''; ?> aria-label="Слайд <?php echo (int) ( $i + 1 ); ?>"></button>
	<?php endforeach; ?>
	</div>
	<button type="button" class="mb-arrow" data-next aria-label="Следующий слайд">&#8250;</button>
</nav>

<button type="button" class="mb-fb-open" data-fb-open data-i18n="fbOpen">Обратная связь</button>

<div class="mb-modal" data-modal hidden>
	<div class="mb-modal__backdrop" data-modal-close></div>
	<section class="mb-modal__panel" role="dialog" aria-modal="true" aria-label="Обратная связь">
		<button type="button" class="mb-modal__x" data-modal-close aria-label="Закрыть">&times;</button>
		<h2 class="mb-modal__title" data-i18n="fbTitle">// обратная связь</h2>
		<form class="mb-form" data-fb-form novalidate>
			<label class="mb-field">
				<span data-i18n="email">&gt; ваш email</span>
				<input type="email" name="email" required autocomplete="email" spellcheck="false">
			</label>
			<label class="mb-field">
				<span data-i18n="message">&gt; сообщение</span>
				<textarea name="message" rows="5" required maxlength="5000"></textarea>
			</label>
			<input type="text" name="mb_hp" class="mb-hp" tabindex="-1" autocomplete="off" aria-hidden="true">
			<p class="mb-form__status" data-fb-status role="status"></p>
			<button type="submit" class="mb-form__submit" data-fb-submit data-i18n="send">Отправить</button>
		</form>
		<p class="mb-form__done" data-fb-done data-i18n="done" hidden>Спасибо, ваше письмо отправлено!</p>
	</section>
</div>

<?php wp_footer(); ?>
</body>
</html>
