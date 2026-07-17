<?php
/**
 * MILK BROS — одностраничная тема.
 *
 * Сорта (этикетки, составы, видимость), язык по умолчанию и email формы
 * настраиваются в «Внешний вид → Настроить → MILK BROS: сорта и контакты».
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'MILKBROS_VERSION', '1.3.0' );
define( 'MILKBROS_MAX_SLIDES', 10 );

add_action( 'after_setup_theme', function () {
	add_theme_support( 'title-tag' );
	add_theme_support( 'html5', array( 'script', 'style' ) );
} );

add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style( 'milkbros', get_stylesheet_uri(), array(), MILKBROS_VERSION );
	wp_enqueue_script( 'milkbros', get_theme_file_uri( 'assets/js/app.js' ), array(), MILKBROS_VERSION, true );
	wp_localize_script( 'milkbros', 'milkbrosCfg', array(
		'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
		'nonce'       => wp_create_nonce( 'milkbros_feedback' ),
		'defaultLang' => milkbros_get_default_lang(),
	) );
} );

/**
 * Данные видимых сортов: картинка этикетки и составы RU/EN.
 * Слот попадает на сайт, если включена галочка «Показывать этот сорт»
 * (по умолчанию включены первые пять). Пустая картинка — заглушка из темы,
 * пустой RU-текст — текст по умолчанию, пустой EN — тот же русский текст.
 */
function milkbros_get_slide_data() {
	$defaults = milkbros_default_overlay_texts();
	$slides   = array();

	for ( $i = 1; $i <= MILKBROS_MAX_SLIDES; $i++ ) {
		if ( ! get_theme_mod( "milkbros_slide_on_{$i}", $i <= 5 ) ) {
			continue;
		}

		$img = get_theme_mod( "milkbros_slide_{$i}", '' );
		if ( ! $img ) {
			$img = get_theme_file_uri( 'assets/slides/slide-' . ( ( $i - 1 ) % 5 + 1 ) . '.svg' );
		}

		$ru = get_theme_mod( "milkbros_overlay_text_{$i}", '' );
		if ( '' === $ru ) {
			$ru = isset( $defaults[ $i ] ) ? $defaults[ $i ] : 'MILK BROS';
		}
		$en = get_theme_mod( "milkbros_overlay_text_en_{$i}", '' );

		$slides[] = array(
			'img' => $img,
			'ru'  => $ru,
			'en'  => ( '' !== $en ) ? $en : $ru,
		);
	}

	// Если скрыли вообще все сорта — показываем первый слот, чтобы сайт не опустел.
	if ( ! $slides ) {
		$slides[] = array(
			'img' => get_theme_file_uri( 'assets/slides/slide-1.svg' ),
			'ru'  => $defaults[1],
			'en'  => $defaults[1],
		);
	}

	return $slides;
}

/**
 * Тексты составов по умолчанию — свой для каждого слайда-сорта.
 */
function milkbros_default_overlay_texts() {
	return array(
		1 => "MILK BROS · AMBER ALE\nСОСТАВ: вода, ячменный солод, карамельный солод, хмель, дрожжи.\nАЛК. 4,9% об. · ЭКСТРАКТ 12%",
		2 => "MILK BROS · RED LAGER\nСОСТАВ: вода, ячменный солод, жжёный солод, хмель, дрожжи.\nАЛК. 4,5% об. · ЭКСТРАКТ 11,5%",
		3 => "MILK BROS · HOP GREEN IPA\nСОСТАВ: вода, ячменный солод, хмель, дрожжи.\nАЛК. 6,2% об. · ЭКСТРАКТ 14,5%\nОхмелённое на сухую.",
		4 => "MILK BROS · NIGHT BLUE STOUT\nСОСТАВ: вода, ячменный и овсяный солод, хмель, дрожжи.\nАЛК. 5,8% об. · ЭКСТРАКТ 15%",
		5 => "MILK BROS · VIOLET HAZE\nСОСТАВ: вода, пшеничный солод, ячменный солод, хмель, дрожжи.\nАЛК. 4,7% об. · ЭКСТРАКТ 12%\nНефильтрованное.",
	);
}

/**
 * Текст состава → HTML: экранирование, переносы строк и простая разметка
 * [b]жирный[/b], [i]курсив[/i].
 */
function milkbros_format_overlay_text( $text ) {
	$html = nl2br( esc_html( $text ) );
	return str_replace(
		array( '[b]', '[/b]', '[i]', '[/i]' ),
		array( '<strong>', '</strong>', '<em>', '</em>' ),
		$html
	);
}

/**
 * Язык по умолчанию для посетителей, которые ещё не выбрали свой
 * кнопкой RU/EN. Настраивается в кастомайзере.
 */
function milkbros_get_default_lang() {
	return 'en' === get_theme_mod( 'milkbros_default_lang', 'ru' ) ? 'en' : 'ru';
}

/**
 * Куда отправлять письма из формы. Пока адрес не задан в кастомайзере —
 * используется email администратора сайта.
 */
function milkbros_get_feedback_email() {
	$email = get_theme_mod( 'milkbros_feedback_email', '' );
	return is_email( $email ) ? $email : get_option( 'admin_email' );
}

add_action( 'customize_register', function ( $wp_customize ) {
	$wp_customize->add_panel( 'milkbros', array(
		'title'    => 'MILK BROS: сорта и контакты',
		'priority' => 10,
	) );

	$wp_customize->add_section( 'milkbros_general', array(
		'panel'       => 'milkbros',
		'title'       => 'Общие настройки',
		'description' => 'В текстах составов работают переносы строк и разметка: [b]жирный[/b], [i]курсив[/i].',
	) );

	$wp_customize->add_setting( 'milkbros_default_lang', array(
		'default'           => 'ru',
		'sanitize_callback' => function ( $value ) {
			return 'en' === $value ? 'en' : 'ru';
		},
	) );
	$wp_customize->add_control( 'milkbros_default_lang', array(
		'label'       => 'Язык по умолчанию',
		'description' => 'Что видит посетитель при первом заходе. Его собственный выбор кнопкой RU/EN важнее этой настройки.',
		'section'     => 'milkbros_general',
		'type'        => 'radio',
		'choices'     => array(
			'ru' => 'Русский',
			'en' => 'English',
		),
	) );

	$wp_customize->add_setting( 'milkbros_feedback_email', array(
		'default'           => '',
		'sanitize_callback' => 'sanitize_email',
	) );
	$wp_customize->add_control( 'milkbros_feedback_email', array(
		'label'       => 'Email для обратной связи',
		'description' => 'Куда приходят письма из формы. Если пусто — на email администратора сайта.',
		'section'     => 'milkbros_general',
		'type'        => 'email',
	) );

	$overlay_defaults = milkbros_default_overlay_texts();

	for ( $i = 1; $i <= MILKBROS_MAX_SLIDES; $i++ ) {
		$wp_customize->add_section( "milkbros_slide_sec_{$i}", array(
			'panel' => 'milkbros',
			'title' => "Сорт {$i}",
		) );

		$wp_customize->add_setting( "milkbros_slide_on_{$i}", array(
			'default'           => $i <= 5,
			'sanitize_callback' => function ( $value ) {
				return (bool) $value;
			},
		) );
		$wp_customize->add_control( "milkbros_slide_on_{$i}", array(
			'label'   => 'Показывать этот сорт',
			'section' => "milkbros_slide_sec_{$i}",
			'type'    => 'checkbox',
		) );

		$wp_customize->add_setting( "milkbros_slide_{$i}", array(
			'default'           => '',
			'sanitize_callback' => 'esc_url_raw',
		) );
		$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, "milkbros_slide_{$i}", array(
			'label'   => 'Этикетка',
			'section' => "milkbros_slide_sec_{$i}",
		) ) );

		$wp_customize->add_setting( "milkbros_overlay_text_{$i}", array(
			'default'           => isset( $overlay_defaults[ $i ] ) ? $overlay_defaults[ $i ] : '',
			'sanitize_callback' => 'sanitize_textarea_field',
		) );
		$wp_customize->add_control( "milkbros_overlay_text_{$i}", array(
			'label'   => 'Состав (RU)',
			'section' => "milkbros_slide_sec_{$i}",
			'type'    => 'textarea',
		) );

		$wp_customize->add_setting( "milkbros_overlay_text_en_{$i}", array(
			'default'           => '',
			'sanitize_callback' => 'sanitize_textarea_field',
		) );
		$wp_customize->add_control( "milkbros_overlay_text_en_{$i}", array(
			'label'       => 'Состав (EN)',
			'description' => 'Если пусто — в английской версии показывается русский текст.',
			'section'     => "milkbros_slide_sec_{$i}",
			'type'        => 'textarea',
		) );
	}
} );

add_action( 'wp_ajax_milkbros_feedback', 'milkbros_handle_feedback' );
add_action( 'wp_ajax_nopriv_milkbros_feedback', 'milkbros_handle_feedback' );

/**
 * Приём формы обратной связи (AJAX) и отправка письма.
 */
function milkbros_handle_feedback() {
	check_ajax_referer( 'milkbros_feedback', 'nonce' );

	// Скрытое поле-приманка: люди его не видят, боты заполняют.
	if ( ! empty( $_POST['mb_hp'] ) ) {
		wp_send_json_success();
	}

	$email   = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$message = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';

	if ( ! is_email( $email ) ) {
		wp_send_json_error( array( 'message' => 'Укажите корректный email.' ), 400 );
	}
	if ( '' === $message ) {
		wp_send_json_error( array( 'message' => 'Напишите сообщение.' ), 400 );
	}
	if ( mb_strlen( $message ) > 5000 ) {
		$message = mb_substr( $message, 0, 5000 );
	}

	$lock = 'milkbros_fb_' . md5( isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : '' );
	if ( get_transient( $lock ) ) {
		wp_send_json_error( array( 'message' => 'Слишком часто. Подождите полминуты.' ), 429 );
	}
	set_transient( $lock, 1, 30 );

	$sent = wp_mail(
		milkbros_get_feedback_email(),
		'Обратная связь — ' . wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ),
		"От: {$email}\n\n{$message}",
		array( 'Reply-To: ' . $email )
	);

	if ( $sent ) {
		wp_send_json_success();
	}
	wp_send_json_error( array( 'message' => 'Не удалось отправить письмо. Попробуйте позже.' ), 500 );
}
