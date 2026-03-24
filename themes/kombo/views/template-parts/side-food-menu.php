<?php

/**
 * Template part for side food menu
 */

?>
<?php
$current_lang = pll_current_language();

// Query za standardni meni (ova nedelja)
$args_standard = array(
    'post_type' => 'weekly_menu',
    'posts_per_page' => 1,
    'orderby' => 'date',
    'order' => 'DESC',
    'lang' => $current_lang
);
$menu_query_standard = new WP_Query($args_standard);

// Query za vege meni (ova nedelja)
$args_vege = array(
    'post_type' => 'vege_menu',
    'posts_per_page' => 1,
    'orderby' => 'date',
    'order' => 'DESC',
    'lang' => $current_lang
);
$menu_query_vege = new WP_Query($args_vege);

// Query za standardni meni (sledeća nedelja)
$args_next_standard = array(
    'post_type' => 'next_weekly_menu',
    'posts_per_page' => 1,
    'orderby' => 'date',
    'order' => 'DESC',
    'lang' => $current_lang
);
$menu_query_next_standard = new WP_Query($args_next_standard);

// Query za vege meni (sledeća nedelja)
$args_next_vege = array(
    'post_type' => 'next_vege_menu',
    'posts_per_page' => 1,
    'orderby' => 'date',
    'order' => 'DESC',
    'lang' => $current_lang
);
$menu_query_next_vege = new WP_Query($args_next_vege);

if ($menu_query_standard->have_posts()):
    while ($menu_query_standard->have_posts()):
        $menu_query_standard->the_post();
        $side_container_standard = get_field('side_container');
    endwhile;
    wp_reset_postdata();
endif;

if ($menu_query_vege->have_posts()):
    while ($menu_query_vege->have_posts()):
        $menu_query_vege->the_post();
        $side_container_vege = get_field('side_container');
    endwhile;
    wp_reset_postdata();
endif;

if ($menu_query_next_standard->have_posts()):
    while ($menu_query_next_standard->have_posts()):
        $menu_query_next_standard->the_post();
        $side_container_next_standard = get_field('side_container');
    endwhile;
    wp_reset_postdata();
endif;

if ($menu_query_next_vege->have_posts()):
    while ($menu_query_next_vege->have_posts()):
        $menu_query_next_vege->the_post();
        $side_container_next_vege = get_field('side_container');
    endwhile;
    wp_reset_postdata();
endif;
?>
<div class="side_container">
    <section class="menu-trigger-section">
        <div class="trigger-side-menu">
            <svg xmlns="http://www.w3.org/2000/svg" width="126" height="126" viewBox="0 0 126 126" fill="none"
                class="circle-arrow">
                <circle cx="63" cy="63" r="63" fill="#FEEC69" />
                <g class="arrow" shape-rendering="crispEdges">
                    <path d="M82 63H44M44 63L63 44M44 63L63 82" stroke="black" stroke-width="4" stroke-linecap="round"
                        stroke-linejoin="round" />
                </g>
            </svg>
      <h3><?php echo pll_ru('Meni za ovu nedelju', 'Меню на эту неделю') ?></h3>
        </div>
    </section>
    <div class="sliding-menu">
        <div class="menu_container">

            <!-- Menu Toggle Buttons -->
            <!-- <div class="menu-type-buttons menu-type-buttons-desktop">
                <button class="menu-type-btn active " data-menu="standard">
                    <span> <?php echo pll__('Standardni paketi'); ?></span>

                </button>
                <button class="menu-type-btn" data-menu="vege">
                    <span><?php echo pll__('Vege paketi'); ?></span>
                </button>
            </div> -->

            <!-- Week Toggle Button -->
            <div class="week-toggle-wrapper">
                <div class="next-week-menu" data-week="next"><span><?php echo pll_ru('Sledeća nedelja', 'На следующей неделе') ?></span> <svg xmlns="http://www.w3.org/2000/svg" width="15" height="16" viewBox="0 0 15 16" fill="none">
                        <path d="M-3.0907e-07 7.62656L13 7.62656M13 7.62656L6.25961 14.6973M13 7.62656L6.25961 0.697266" stroke="black" stroke-width="2" />
                    </svg>
                </div>
                <div class="current-week-menu" data-week="current" style="display: none;"><svg xmlns="http://www.w3.org/2000/svg" width="15" height="16" viewBox="0 0 15 16" fill="none" style="transform: rotate(180deg);">
                        <path d="M-3.0907e-07 7.62656L13 7.62656M13 7.62656L6.25961 14.6973M13 7.62656L6.25961 0.697266" stroke="black" stroke-width="2" />
                    </svg> <span><?php echo pll_ru('Ova nedelja', 'На этой неделе') ?></span>
                </div>
            </div>

            <!-- CURRENT WEEK WRAPPER -->
            <div class="week-content current-week-content" id="current-week">

                <!-- Standardni meni -->
                <div class="menu-content" id="standard-menu">
                    <?php
                    if (!empty($side_container_standard['weekly_info'])): ?>
                        <div class="weekly_info">
                            <div class="title-wrapper">
                                <h2><?php echo $side_container_standard['weekly_info']['weekly_time']; ?></h2>
                                <h6><?php echo pll_ru('Meni se objavljuje svake nedelje u sedmici.', 'Меню публикуется еженедельно.') ?></h6>
                            </div>
                            <div class="menu-type-buttons menu-type-buttons-desktop">
                                <button class="menu-type-btn active " data-menu="standard">
                                    <span> <?php echo pll_ru('Standardni paketi', 'Стандартные пакеты'); ?></span>

                                </button>
                                <button class="menu-type-btn" data-menu="vege">
                                    <span><?php echo pll_ru('Vege paketi', 'Веганский наборы'); ?></span>
                                </button>
                            </div>
                        </div>
                    <?php endif; ?>
                    <div class="menu-type-buttons menu-type-buttons-mobile override-class">
                        <button class="menu-type-btn active" data-menu="standard">
                            <span> <?php echo pll_ru('Standardni paketi', 'Стандартные пакеты'); ?></span>

                        </button>
                        <button class="menu-type-btn " data-menu="vege">
                            <span><?php echo pll_ru('Vege paketi', 'Веганский наборы'); ?></span>
                        </button>
                    </div>
                    <div class="menu_container_main">

                        <?php

                        $days = array();
                        $current_language = pll_current_language();

                        if ($current_language === 'ru') {
                            $days = array(
                                'monday_menu' => array('name' => 'Понедельник', 'prefix' => 'ponedeljak'),
                                'tuesday_menu' => array('name' => 'Вторник', 'prefix' => 'utorak'),
                                'wednesday_menu' => array('name' => 'Среда', 'prefix' => 'sreda'),
                                'thursday_menu' => array('name' => 'Четверг', 'prefix' => 'cetvrtak'),
                                'friday_menu' => array('name' => 'Пятница', 'prefix' => 'petak'),
                                'saturday_menu' => array('name' => 'Суббота', 'prefix' => 'subota')
                            );
                        } else {
                            $days = array(
                                'monday_menu' => array('name' => pll__('Ponedeljak'), 'prefix' => 'ponedeljak'),
                                'tuesday_menu' => array('name' => pll__('Utorak'), 'prefix' => 'utorak'),
                                'wednesday_menu' => array('name' => pll__('Sreda'), 'prefix' => 'sreda'),
                                'thursday_menu' => array('name' => pll__('Cetvrtak'), 'prefix' => 'cetvrtak'),
                                'friday_menu' => array('name' => pll__('Petak'), 'prefix' => 'petak'),
                                'saturday_menu' => array('name' => pll__('Subota'), 'prefix' => 'subota')
                            );
                        }

                        foreach ($days as $group_key => $day_info):
                            if (!empty($side_container_standard[$group_key])):
                                $day_menu = $side_container_standard[$group_key];
                        ?>
                                <div class="day-menu">
                                    <h4><?php echo $day_info['name']; ?></h4>
                                    <?php
                                    $meals = array();
                                    if ($current_language === 'ru') {
                                        $meals = array(
                                            'dorucak' => 'Завтрак',
                                            'uzina1' => 'Перекус 1',
                                            'rucak' => 'Обед',
                                            'uzina2' => 'Перекус 2',
                                            'vecera' => 'Ужин'
                                        );
                                    } else {
                                        $meals = array(
                                            'dorucak' => pll__('Doručak'),
                                            'uzina1' => pll__('Užina 1'),
                                            'rucak' => pll__('Ručak'),
                                            'uzina2' => pll__('Užina 2'),
                                            'vecera' => pll__('Večera')
                                        );
                                    }

                                    foreach ($meals as $meal_key => $meal_name):
                                        $field_name = $meal_key . '_' . $day_info['prefix'];
                                        if (!empty($day_menu[$field_name])): ?>
                                            <p><span><?php echo $meal_name; ?>:</span> <?php echo $day_menu[$field_name]; ?></p>
                                    <?php endif;
                                    endforeach; ?>
                                    <?php if ($group_key === "saturday_menu"): ?>

                                    <?php endif; ?>
                                </div>
                        <?php endif;
                        endforeach; ?>

                    </div>
                </div>

                <!-- Vege meni -->
                <div class="menu-content" id="vege-menu" style="display: none;">
                    <?php
                    if (!empty($side_container_vege['weekly_info'])): ?>
                        <div class="weekly_info">
                            <div class="title-wrapper">
                                <h2><?php echo $side_container_vege['weekly_info']['weekly_time']; ?></h2>
                                <h6><?php echo pll_ru('Meni se objavljuje svake nedelje u sedmici.', 'Меню публикуется еженедельно.') ?></h6>
                            </div>
                            <div class="menu-type-buttons menu-type-buttons-desktop">
                                <button class="menu-type-btn active " data-menu="standard">
                                    <span> <?php echo pll_ru('Standardni paketi', 'Стандартные пакеты'); ?></span>

                                </button>
                                <button class="menu-type-btn" data-menu="vege">
                                    <span><?php echo pll_ru('Vege paketi', 'Веганский наборы'); ?></span>
                                </button>
                            </div>
                        </div>
                    <?php endif; ?>
                    <div class="menu-type-buttons menu-type-buttons-mobile override-class">
                        <button class="menu-type-btn " data-menu="standard">
                            <span> <?php echo pll_ru('Standardni paketi', 'Стандартные пакеты'); ?></span>

                        </button>
                        <button class="menu-type-btn active" data-menu="vege">
                            <span><?php echo pll_ru('Vege paketi', 'Веганский наборы'); ?></span>
                        </button>
                    </div>
                    <div class="menu_container_main">

                        <?php

                        $days = array();
                        $current_language = pll_current_language();

                        if ($current_language === 'ru') {
                            $days = array(
                                'monday_menu' => array('name' => 'Понедельник', 'prefix' => 'ponedeljak'),
                                'tuesday_menu' => array('name' => 'Вторник', 'prefix' => 'utorak'),
                                'wednesday_menu' => array('name' => 'Среда', 'prefix' => 'sreda'),
                                'thursday_menu' => array('name' => 'Четверг', 'prefix' => 'cetvrtak'),
                                'friday_menu' => array('name' => 'Пятница', 'prefix' => 'petak'),
                                'saturday_menu' => array('name' => 'Суббота', 'prefix' => 'subota')
                            );
                        } else {
                            $days = array(
                                'monday_menu' => array('name' => pll__('Ponedeljak'), 'prefix' => 'ponedeljak'),
                                'tuesday_menu' => array('name' => pll__('Utorak'), 'prefix' => 'utorak'),
                                'wednesday_menu' => array('name' => pll__('Sreda'), 'prefix' => 'sreda'),
                                'thursday_menu' => array('name' => pll__('Cetvrtak'), 'prefix' => 'cetvrtak'),
                                'friday_menu' => array('name' => pll__('Petak'), 'prefix' => 'petak'),
                                'saturday_menu' => array('name' => pll__('Subota'), 'prefix' => 'subota')
                            );
                        }

                        foreach ($days as $group_key => $day_info):
                            if (!empty($side_container_vege[$group_key])):
                                $day_menu = $side_container_vege[$group_key];
                        ?>
                                <div class="day-menu">
                                    <h4><?php echo $day_info['name']; ?></h4>
                                    <?php
                                    $meals = array();
                                    if ($current_language === 'ru') {
                                        $meals = array(
                                            'dorucak' => 'Завтрак',
                                            'uzina1' => 'Перекус 1',
                                            'rucak' => 'Обед',
                                            'uzina2' => 'Перекус 2',
                                            'vecera' => 'Ужин'
                                        );
                                    } else {
                                        $meals = array(
                                            'dorucak' => pll__('Doručak'),
                                            'uzina1' => pll__('Užina 1'),
                                            'rucak' => pll__('Ručak'),
                                            'uzina2' => pll__('Užina 2'),
                                            'vecera' => pll__('Večera')
                                        );
                                    }

                                    foreach ($meals as $meal_key => $meal_name):
                                        $field_name = $meal_key . '_' . $day_info['prefix'];
                                        if (!empty($day_menu[$field_name])): ?>
                                            <p><span><?php echo $meal_name; ?>:</span> <?php echo $day_menu[$field_name]; ?></p>
                                    <?php endif;
                                    endforeach; ?>
                                    <?php if ($group_key === "saturday_menu"): ?>
                                      
                                    <?php endif; ?>
                                </div>
                        <?php endif;
                        endforeach; ?>

                    </div>

                </div>
                <div class="weekly_info_button_wrapper">
                    <div class="button-wrapper">
                        <a href="<?php
                                    $current_language = pll_current_language();
                                    if ($current_language === 'sr') {
                                        echo get_site_url() . '/porucivanje';
                                    } elseif ($current_language === 'en') {
                                        echo get_site_url() . '/en/ordering';
                                    } elseif ($current_language === 'ru') {
                                        echo get_site_url() . '/ru/заказ';
                                    }
                                    ?>" class="button-main"><span>
                                <?php
                                $current_language = pll_current_language();
                                if ($current_language === 'ru') {
                                    echo 'Заказ';
                                } else {
                                    echo pll__('Naruči');
                                }
                                ?>
                                <svg xmlns="http://www.w3.org/2000/svg" width="32px" height="32px" viewBox="0 0 16 16" fill="#fff" class="bi bi-arrow-right-short">
                                    <path fill-rule="evenodd"
                                        d="M4 8a.5.5 0 0 1 .5-.5h5.793L8.146 5.354a.5.5 0 1 1 .708-.708l3 3a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708-.708L10.293 8.5H4.5A.5.5 0 0 1 4 8z" />
                                </svg>
                            </span>
                        </a>

                    </div>
                </div>
            </div>
            <!-- END CURRENT WEEK WRAPPER -->

            <!-- NEXT WEEK WRAPPER -->
            <div class="week-content next-week-content" id="next-week" style="display: none;">

                <!-- Standardni meni - Sledeća nedelja -->
                <div class="menu-content" id="next-standard-menu">
                    <?php
                    if (!empty($side_container_next_standard['weekly_info'])): ?>
                        <div class="weekly_info">
                            <div class="title-wrapper">
                                <h2><?php echo $side_container_next_standard['weekly_info']['weekly_time']; ?></h2>
                                <h6><?php echo pll_ru('Meni se objavljuje svake nedelje u sedmici.', 'Меню публикуется еженедельно.') ?></h6>
                            </div>
                            <div class="menu-type-buttons menu-type-buttons-desktop">
                                <button class="menu-type-btn active" data-menu="next-standard">
                                    <span> <?php echo pll_ru('Standardni paketi', 'Стандартные пакеты'); ?></span>
                                </button>
                                <button class="menu-type-btn" data-menu="next-vege">
                                    <span><?php echo pll_ru('Vege paketi', 'Веганский наборы'); ?></span>
                                </button>
                            </div>
                        </div>
                    <?php endif; ?>
                    <div class="menu-type-buttons menu-type-buttons-mobile override-class">
                        <button class="menu-type-btn active" data-menu="next-standard">
                            <span> <?php echo pll_ru('Standardni paketi', 'Стандартные пакеты'); ?></span>
                        </button>
                        <button class="menu-type-btn" data-menu="next-vege">
                            <span><?php echo pll_ru('Vege paketi', 'Веганский наборы'); ?></span>
                        </button>
                    </div>
                    <div class="menu_container_main">
                        <?php
                        $days = array();
                        $current_language = pll_current_language();

                        if ($current_language === 'ru') {
                            $days = array(
                                'monday_menu' => array('name' => 'Понедельник', 'prefix' => 'ponedeljak'),
                                'tuesday_menu' => array('name' => 'Вторник', 'prefix' => 'utorak'),
                                'wednesday_menu' => array('name' => 'Среда', 'prefix' => 'sreda'),
                                'thursday_menu' => array('name' => 'Четверг', 'prefix' => 'cetvrtak'),
                                'friday_menu' => array('name' => 'Пятница', 'prefix' => 'petak'),
                                'saturday_menu' => array('name' => 'Суббота', 'prefix' => 'subota')
                            );
                        } else {
                            $days = array(
                                'monday_menu' => array('name' => pll__('Ponedeljak'), 'prefix' => 'ponedeljak'),
                                'tuesday_menu' => array('name' => pll__('Utorak'), 'prefix' => 'utorak'),
                                'wednesday_menu' => array('name' => pll__('Sreda'), 'prefix' => 'sreda'),
                                'thursday_menu' => array('name' => pll__('Cetvrtak'), 'prefix' => 'cetvrtak'),
                                'friday_menu' => array('name' => pll__('Petak'), 'prefix' => 'petak'),
                                'saturday_menu' => array('name' => pll__('Subota'), 'prefix' => 'subota')
                            );
                        }

                        foreach ($days as $group_key => $day_info):
                            if (!empty($side_container_next_standard[$group_key])):
                                $day_menu = $side_container_next_standard[$group_key];
                        ?>
                                <div class="day-menu">
                                    <h4><?php echo $day_info['name']; ?></h4>
                                    <?php
                                    $meals = array();
                                    if ($current_language === 'ru') {
                                        $meals = array(
                                            'dorucak' => 'Завтрак',
                                            'uzina1' => 'Перекус 1',
                                            'rucak' => 'Обед',
                                            'uzina2' => 'Перекус 2',
                                            'vecera' => 'Ужин'
                                        );
                                    } else {
                                        $meals = array(
                                            'dorucak' => pll__('Doručak'),
                                            'uzina1' => pll__('Užina 1'),
                                            'rucak' => pll__('Ručak'),
                                            'uzina2' => pll__('Užina 2'),
                                            'vecera' => pll__('Večera')
                                        );
                                    }

                                    foreach ($meals as $meal_key => $meal_name):
                                        $field_name = $meal_key . '_' . $day_info['prefix'];
                                        if (!empty($day_menu[$field_name])): ?>
                                            <p><span><?php echo $meal_name; ?>:</span> <?php echo $day_menu[$field_name]; ?></p>
                                    <?php endif;
                                    endforeach; ?>
                                    <?php if ($group_key === "saturday_menu"): ?>
                                        <div class="button-wrapper">
                                            <a href="<?php
                                                        $current_language = pll_current_language();
                                                        if ($current_language === 'sr') {
                                                            echo get_site_url() . '/porucivanje';
                                                        } elseif ($current_language === 'en') {
                                                            echo get_site_url() . '/en/ordering';
                                                        } elseif ($current_language === 'ru') {
                                                            echo get_site_url() . '/ru/заказ';
                                                        }
                                                        ?>" class="button-main"><span>
                                                    <?php
                                                    $current_language = pll_current_language();
                                                    if ($current_language === 'ru') {
                                                        echo 'Заказ';
                                                    } else {
                                                        echo pll__('Naruči');
                                                    }
                                                    ?>
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="32px" height="32px" viewBox="0 0 16 16" fill="#fff" class="bi bi-arrow-right-short">
                                                        <path fill-rule="evenodd"
                                                            d="M4 8a.5.5 0 0 1 .5-.5h5.793L8.146 5.354a.5.5 0 1 1 .708-.708l3 3a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708-.708L10.293 8.5H4.5A.5.5 0 0 1 4 8z" />
                                                    </svg>
                                                </span>
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                        <?php endif;
                        endforeach; ?>
                    </div>
                </div>

                <!-- Vege meni - Sledeća nedelja -->
                <div class="menu-content" id="next-vege-menu" style="display: none;">
                    <?php
                    if (!empty($side_container_next_vege['weekly_info'])): ?>
                        <div class="weekly_info">
                            <div class="title-wrapper">
                                <h2><?php echo $side_container_next_vege['weekly_info']['weekly_time']; ?></h2>
                                <h6><?php echo pll_ru('Meni se objavljuje svake nedelje u sedmici.', 'Меню публикуется еженедельно.') ?></h6>
                            </div>
                            <div class="menu-type-buttons menu-type-buttons-desktop">
                                <button class="menu-type-btn" data-menu="next-standard">
                                    <span> <?php echo pll_ru('Standardni paketi', 'Стандартные пакеты'); ?></span>
                                </button>
                                <button class="menu-type-btn active" data-menu="next-vege">
                                    <span><?php echo pll_ru('Vege paketi', 'Веганский наборы'); ?></span>
                                </button>
                            </div>
                        </div>
                    <?php endif; ?>
                    <div class="menu-type-buttons menu-type-buttons-mobile override-class">
                        <button class="menu-type-btn" data-menu="next-standard">
                            <span> <?php echo pll_ru('Standardni paketi', 'Стандартные пакеты'); ?></span>
                        </button>
                        <button class="menu-type-btn active" data-menu="next-vege">
                            <span><?php echo pll_ru('Vege paketi', 'Веганский наборы'); ?></span>
                        </button>
                    </div>
                    <div class="menu_container_main">
                        <?php
                        $days = array();
                        $current_language = pll_current_language();

                        if ($current_language === 'ru') {
                            $days = array(
                                'monday_menu' => array('name' => 'Понедельник', 'prefix' => 'ponedeljak'),
                                'tuesday_menu' => array('name' => 'Вторник', 'prefix' => 'utorak'),
                                'wednesday_menu' => array('name' => 'Среда', 'prefix' => 'sreda'),
                                'thursday_menu' => array('name' => 'Четверг', 'prefix' => 'cetvrtak'),
                                'friday_menu' => array('name' => 'Пятница', 'prefix' => 'petak'),
                                'saturday_menu' => array('name' => 'Суббота', 'prefix' => 'subota')
                            );
                        } else {
                            $days = array(
                                'monday_menu' => array('name' => pll__('Ponedeljak'), 'prefix' => 'ponedeljak'),
                                'tuesday_menu' => array('name' => pll__('Utorak'), 'prefix' => 'utorak'),
                                'wednesday_menu' => array('name' => pll__('Sreda'), 'prefix' => 'sreda'),
                                'thursday_menu' => array('name' => pll__('Cetvrtak'), 'prefix' => 'cetvrtak'),
                                'friday_menu' => array('name' => pll__('Petak'), 'prefix' => 'petak'),
                                'saturday_menu' => array('name' => pll__('Subota'), 'prefix' => 'subota')
                            );
                        }

                        foreach ($days as $group_key => $day_info):
                            if (!empty($side_container_next_vege[$group_key])):
                                $day_menu = $side_container_next_vege[$group_key];
                        ?>
                                <div class="day-menu">
                                    <h4><?php echo $day_info['name']; ?></h4>
                                    <?php
                                    $meals = array();
                                    if ($current_language === 'ru') {
                                        $meals = array(
                                            'dorucak' => 'Завтрак',
                                            'uzina1' => 'Перекус 1',
                                            'rucak' => 'Обед',
                                            'uzina2' => 'Перекус 2',
                                            'vecera' => 'Ужин'
                                        );
                                    } else {
                                        $meals = array(
                                            'dorucak' => pll__('Doručak'),
                                            'uzina1' => pll__('Užina 1'),
                                            'rucak' => pll__('Ručak'),
                                            'uzina2' => pll__('Užina 2'),
                                            'vecera' => pll__('Večera')
                                        );
                                    }

                                    foreach ($meals as $meal_key => $meal_name):
                                        $field_name = $meal_key . '_' . $day_info['prefix'];
                                        if (!empty($day_menu[$field_name])): ?>
                                            <p><span><?php echo $meal_name; ?>:</span> <?php echo $day_menu[$field_name]; ?></p>
                                    <?php endif;
                                    endforeach; ?>
                                    <?php if ($group_key === "saturday_menu"): ?>
                                        <div class="button-wrapper">
                                            <a href="<?php
                                                        $current_language = pll_current_language();
                                                        if ($current_language === 'sr') {
                                                            echo get_site_url() . '/porucivanje';
                                                        } elseif ($current_language === 'en') {
                                                            echo get_site_url() . '/en/ordering';
                                                        } elseif ($current_language === 'ru') {
                                                            echo get_site_url() . '/ru/заказ';
                                                        }
                                                        ?>" class="button-main"><span>
                                                    <?php
                                                    $current_language = pll_current_language();
                                                    if ($current_language === 'ru') {
                                                        echo 'Заказ';
                                                    } else {
                                                        echo pll__('Naruči');
                                                    }
                                                    ?>
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="32px" height="32px" viewBox="0 0 16 16" fill="#fff" class="bi bi-arrow-right-short">
                                                        <path fill-rule="evenodd"
                                                            d="M4 8a.5.5 0 0 1 .5-.5h5.793L8.146 5.354a.5.5 0 1 1 .708-.708l3 3a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708-.708L10.293 8.5H4.5A.5.5 0 0 1 4 8z" />
                                                    </svg>
                                                </span>
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                        <?php endif;
                        endforeach; ?>
                    </div>
                </div>

            </div>
            <!-- END NEXT WEEK WRAPPER -->

            <div class="button-wrapper-side-menu-mobile">
                <a href="<?php
                            $current_language = pll_current_language();
                            if ($current_language === 'sr') {
                                echo get_site_url() . '/porucivanje';
                            } elseif ($current_language === 'en') {
                                echo get_site_url() . '/en/ordering';
                            } elseif ($current_language === 'ru') {
                                echo get_site_url() . '/ru/заказ';
                            }
                            ?>" class="button-main overlay-button"><span>
                        <?php
                        $current_language = pll_current_language();
                        if ($current_language === 'ru') {
                            echo 'Заказ';
                        } else {
                            echo pll__('Naruči');
                        }
                        ?>
                        <svg xmlns="http://www.w3.org/2000/svg" width="32px" height="32px" viewBox="0 0 16 16" fill="#fff" class="bi bi-arrow-right-short">
                            <path fill-rule="evenodd"
                                d="M4 8a.5.5 0 0 1 .5-.5h5.793L8.146 5.354a.5.5 0 1 1 .708-.708l3 3a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708-.708L10.293 8.5H4.5A.5.5 0 0 1 4 8z" />
                        </svg>
                    </span>
                </a>

            </div>
        </div>
    </div>
</div>

<div class="side_menu_button_hidden">
    <svg xmlns="http://www.w3.org/2000/svg" width="126" height="126" viewBox="0 0 126 126" fill="none"
        class="circle-arrow">
        <circle cx="63" cy="63" r="63" fill="#FEEC69" />
        <g class="arrow" shape-rendering="crispEdges">
            <path d="M82 63H44M44 63L63 44M44 63L63 82" stroke="black" stroke-width="4" stroke-linecap="round"
                stroke-linejoin="round" />
        </g>
    </svg>
</div>



<script>
    document.addEventListener('DOMContentLoaded', function() {
        const triggerButton = document.querySelector('.side_container');
        const sideMenuButton = document.querySelector(".side_menu_button_hidden");
        const body = document.body;
        const slidingMenu = document.querySelector('.sliding-menu');
        const menuLinks = document.querySelectorAll('.sliding-menu a');
        const menuTypeButtons = document.querySelectorAll('.sliding-menu .menu_container .menu-type-btn');

        // Current week menus
        const standardMenu = document.getElementById('standard-menu');
        const vegeMenu = document.getElementById('vege-menu');

        // Next week menus
        const nextStandardMenu = document.getElementById('next-standard-menu');
        const nextVegeMenu = document.getElementById('next-vege-menu');

        // Week wrappers
        const currentWeekContent = document.getElementById('current-week');
        const nextWeekContent = document.getElementById('next-week');

        // Week toggle buttons
        const nextWeekButton = document.querySelector('.next-week-menu');
        const currentWeekButton = document.querySelector('.current-week-menu');

        if (triggerButton) {
            triggerButton.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                body.classList.toggle('menu-open');
                if (window.innerWidth > 600) {
                    document.documentElement.scrollTop = 0;
                }
            });
        }

        if (sideMenuButton) {
            sideMenuButton.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                body.classList.toggle('menu-open');
                this.classList.toggle("opened");
                if (window.innerWidth > 600) {
                    document.documentElement.scrollTop = 0;
                }
            });
        }

        // Week toggle functionality
        if (nextWeekButton) {
            nextWeekButton.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                // Hide current week, show next week
                currentWeekContent.style.display = 'none';
                nextWeekContent.style.display = 'block';

                // Toggle button visibility
                nextWeekButton.style.display = 'none';
                currentWeekButton.style.display = 'inline-flex';
            });
        }

        if (currentWeekButton) {
            currentWeekButton.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                // Hide next week, show current week
                nextWeekContent.style.display = 'none';
                currentWeekContent.style.display = 'block';

                // Toggle button visibility
                currentWeekButton.style.display = 'none';
                nextWeekButton.style.display = 'inline-flex';
            });
        }

        // Menu type toggle functionality
        menuTypeButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                const menuType = this.getAttribute('data-menu');
                const isNextWeek = menuType.startsWith('next-');

                // Get the current week context
                const currentWeekButtons = isNextWeek ?
                    document.querySelectorAll('#next-week .menu-type-btn') :
                    document.querySelectorAll('#current-week .menu-type-btn');

                // Remove active class from buttons in the same week context
                currentWeekButtons.forEach(btn => {
                    btn.classList.remove('active');
                    btn.style.background = '';
                    btn.style.borderColor = '';
                });

                // Add active class to all buttons with same data-menu
                currentWeekButtons.forEach(btn => {
                    if (btn.getAttribute('data-menu') === menuType) {
                        btn.classList.add('active');
                        btn.style.background = '#fdfdfd';
                        btn.style.borderColor = '#0e0e0e';
                    }
                });

                // Show/hide appropriate menu based on week and type
                if (menuType === 'standard') {
                    standardMenu.style.display = 'block';
                    vegeMenu.style.display = 'none';
                } else if (menuType === 'vege') {
                    standardMenu.style.display = 'none';
                    vegeMenu.style.display = 'block';
                } else if (menuType === 'next-standard') {
                    nextStandardMenu.style.display = 'block';
                    nextVegeMenu.style.display = 'none';
                } else if (menuType === 'next-vege') {
                    nextStandardMenu.style.display = 'none';
                    nextVegeMenu.style.display = 'block';
                }
            });
        });

        // Prevent menu closing when clicking links
        menuLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        });

        // Zatvaranje na Escape
        document.addEventListener('keyup', function(e) {
            if (e.key === "Escape") {
                body.classList.remove('menu-open');
                sideMenuButton.classList.remove("opened");
            }
        });

        // Zatvaranje na klik van menija
        document.addEventListener('click', function(e) {
            if (body.classList.contains('menu-open') &&
                !slidingMenu.contains(e.target) &&
                !triggerButton.contains(e.target)) {
                body.classList.remove('menu-open');
            }
        });
    });
</script>