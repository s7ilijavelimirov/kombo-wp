<?php
/**
 * Template part for meal plan form
 */
?>
<div class="meal-plan-form">
    <div class="form-arrow">
        <svg width="41" height="43" viewBox="0 0 41 43" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M41 21.697L3 21.697M3 21.697L22.7027 2M3 21.697L22.7027 41" stroke="black" stroke-width="4" />
        </svg>

    </div>
    <form id="mealPlanForm" action="" method="POST">
        <?php wp_nonce_field('add_meal_plan_to_cart', 'meal_plan_nonce'); ?>

        <!-- Menu Type Selection -->
        <div class="form-section" id="menu-type-selection">
            <div class="menu-type-buttons expand-class">
                <button type="button" class="menu-type-btn active" data-menu-type="standard">
                   <?php echo pll_ru('Standardni paketi', 'Стандартные пакеты'); ?>
                </button>
                <button type="button" class="menu-type-btn" data-menu-type="vege">
                    <?php echo pll_ru('Vege paketi', 'Веганский наборы'); ?>
                </button>
            </div>
            <input type="hidden" name="menu_type" id="selectedMenuType" value="standard">
        </div>

        <!-- Gender Selection -->
        <div>
            <div class="form-section" id="gender-buttons">
                <p class="subtitle"><?php echo pll_ru('Na osnovu odabira paketa, cene planova se menjaju', 'В зависимости от выбранного пакета, цены на тарифные планы меняются.') ?></p>
                <h2><?php echo pll_ru('Izaberi paket', 'Выберите пакет') ?></h2>
                <div class="gender-buttons">
                    <button type="button" class="form-button" data-gender="slim"><?php echo pll_ru('Slim', 'Слим') ?><span>1300 /
                            1600<?php echo pll_ru('kcal', 'ккал') ?></span></button>
                    <button type="button" class="form-button" data-gender="fit"><?php echo pll_ru('Fit', 'Фит') ?><span>1600 /
                            1900<?php echo pll_ru('kcal', 'ккал') ?></span></button>
                    <button type="button" class="form-button"
                        data-gender="protein"><?php echo pll_ru('Protein plus', 'Белок плюс') ?><span>2000 / 2600<?php echo pll_ru('kcal', 'ккал') ?></span></button>
                </div>
                <input type="hidden" name="selected_gender" id="selectedGender">
            </div>

            <!-- Calories Selection -->
            <div class="form-section packages-section">
                <div class="package-infos">
                    <h2><?php echo pll_ru('Izaberi veličinu', 'Выберите размер') ?></h2>
                </div>
                <div class="calories-buttons">
                    <button type="button" class="form-button calorie-placeholder">
                       <?php echo pll_ru('kcal', 'ккал') ?>
                    </button>
                    <button type="button" class="form-button calorie-placeholder">
                     <?php echo pll_ru('kcal', 'ккал') ?>
                    </button>
                    <!-- <button type="button" class="form-button calorie-placeholder">
                        <?php echo pll__('kcal') ?>
                    </button> -->
                </div>
                <input type="hidden" name="selected_calories" id="selectedCalories">
            </div>
            <!-- Package Selection -->
            <div class="form-section">
                <h2><?php echo pll__('Izaberi plan', 'Выберите тарифный план') ?></h2>
                <div class="package-buttons">
                    <div class="block-one">
                        <button type="button" class="form-button" data-package="dnevni">
                            <div class="package-block">
                                <span class="package-name"><?php echo pll_ru('Dnevni', 'Ежедневно') ?></span>
                            </div>
                            <span class="package-price"><?php echo pll_ru('RSD po danu', 'РСД в день') ?></span>
                        </button>
                        <button type="button" class="form-button" data-package="nedeljni5">
                            <div class="package-block">
                                <span class="package-name"><?php echo pll_ru('Nedeljni 5', 'Воскресенье 5') ?></span>
                                <span class="package-description"><?php echo pll_ru('(radni dani)', '(рабочие дни)') ?></span>
                            </div>
                            <span class="package-price"><?php echo pll_ru('RSD', 'РСД') ?></span>
                        </button>
                        <button type="button" class="form-button max-button" data-package="nedeljni6">
                            <div class="package-block">
                                <span class="package-name"><?php echo pll_ru('Nedeljni 6', 'Воскресенье 6') ?></span>
                                <span class="package-description"><?php echo pll_ru('(radni dani i subota)', '(в будние дни и по субботам)') ?></span>
                            </div>
                            <span class="package-price"><?php echo pll_ru('RSD', 'РСД') ?></span>
                        </button>
                    </div>
                    <div class="block-two">
                        <button type="button" class="form-button" data-package="mesecni20">
                            <div class="package-block">
                                <span class="package-name"><?php echo pll_ru('Mesečni 20', 'Ежемесячно 20') ?></span>
                                <span class="package-description"><?php echo pll_ru('(radni dani)', '(рабочие дни)') ?></span>
                            </div>
                            <span class="package-price"><?php echo pll_ru('RSD', 'РСД') ?></span>
                        </button>
                        <button type="button " class="form-button button-max-two" data-package="mesecni24">
                            <div class="package-block">
                                <span class="package-name"><?php echo pll_ru('Mesečni 24', 'Ежемесячно 24') ?></span>
                                <span class="package-description"><?php echo pll_ru('(radni dani i subota)', '(в будние дни и по субботам)') ?></span>
                            </div>
                            <span class="package-price"><?php echo pll_ru('RSD', 'РСД') ?></span>
                        </button>
                    </div>
                </div>
                <input type="hidden" name="selected_package" id="selectedPackage">
            </div>

            <!-- Date Selection -->

            <div class="form-section calendar-fields">
                <h2><?php echo pll_ru('Izaberi datum za naručivanje', 'Выберите дату для оформления заказа') ?></h2>
                <div class="date-selection">
                    <div class="date-range-wrapper">
                        <div class="date-range-display">
                            <div class="calendar-trigger">
                                <svg width="26" height="26" viewBox="0 0 26 26" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M7.66667 6.33333V1M18.3333 6.33333V1M6.33333 11.6667H19.6667M3.66667 25H22.3333C23.0406 25 23.7189 24.719 24.219 24.219C24.719 23.7189 25 23.0406 25 22.3333V6.33333C25 5.62609 24.719 4.94781 24.219 4.44772C23.7189 3.94762 23.0406 3.66667 22.3333 3.66667H3.66667C2.95942 3.66667 2.28115 3.94762 1.78105 4.44772C1.28095 4.94781 1 5.62609 1 6.33333V22.3333C1 23.0406 1.28095 23.7189 1.78105 24.219C2.28115 24.719 2.95942 25 3.66667 25Z"
                                        stroke="#2D2D2D" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                </svg>
                                <span class="start-date"></span>
                                <span class="calendar-label">
                               <?php echo pll_ru('Kalendar', 'Календарь') ?>
                                </span>
                            </div>
                        </div>
                        <span class="end-date-display"></span>
                    </div>
                    <input type="text" id="deliveryDate" class="datepicker" readonly style="display: none;">
                    <div class="selected-dates"></div>
                </div>
            </div>
            <div class="meal-plan-price-wrapper">
                <span><?php echo pll_ru('Cena', 'Цена') ?>:</span>
                <span class="meal-plan-price"></span>
            </div>
            <div class="form-buttons" style="display: none;">
                <!-- <button type="submit" class="submit-button add-to-cart"
                    disabled><?php echo pll__('Dodaj u korpu') ?>
                
                </button>
                <button type="button" class="submit-button buy-now"
                    disabled><?php echo pll__('Naruči odmah') ?>

                </button> -->
                <div class="button-wrapper">
                    <button disabled type="submit" class="submit-button add-to-cart button-main cart-btn" href="#"
                        class="button-main"><span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="28px" height="28px" viewBox="0 0 43 44"
                                fill="none">
                                <path fill-rule="evenodd" clip-rule="evenodd"
                                    d="M13.3 35C10.935 35 9 36.935 9 39.3C9 41.665 10.935 43.6 13.3 43.6C15.665 43.6 17.6 41.665 17.6 39.3C17.6 36.935 15.665 35 13.3 35ZM34.8 35C32.435 35 30.5 36.935 30.5 39.3C30.5 41.665 32.435 43.6 34.8 43.6C37.165 43.6 39.1 41.665 39.1 39.3C39.1 36.935 37.165 35 34.8 35Z"
                                    fill="#0E0E0E" />
                                <path fill-rule="evenodd" clip-rule="evenodd"
                                    d="M0 0V4.3H4.3L12.04 20.64L9.02999 25.8C8.81499 26.445 8.59999 27.305 8.59999 27.95C8.59999 30.315 10.535 32.25 12.9 32.25H38.7V27.95H13.76C13.545 27.95 13.33 27.735 13.33 27.52V27.305L15.265 23.65H31.175C32.895 23.65 34.185 22.79 34.83 21.5L42.57 7.525C43 7.095 43 6.88 43 6.45C43 5.16 42.14 4.3 40.85 4.3H9.02999L7.09499 0H0Z"
                                    fill="#0E0E0E" />
                            </svg>
                                  <?php echo pll_ru('Dodaj u korpu', 'Добавить в корзину') ?>
                        </span>
                    </button>
                </div>
                <div class="button-wrapper">
                    <button disabled type="button"
                        class="submit-button buy-now button-main"><span><?php echo pll__('Naruči odmah', 'Заказать сейчас') ?>
                            <svg xmlns="http://www.w3.org/2000/svg" width="32px" height="32px" viewBox="0 0 16 16"
                                fill="#fff" class="bi bi-arrow-right-short">
                                <path fill-rule="evenodd"
                                    d="M4 8a.5.5 0 0 1 .5-.5h5.793L8.146 5.354a.5.5 0 1 1 .708-.708l3 3a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708-.708L10.293 8.5H4.5A.5.5 0 0 1 4 8z" />
                            </svg>
                        </span>

                    </button>
                </div>
            </div>
        </div>
    </form>
</div>