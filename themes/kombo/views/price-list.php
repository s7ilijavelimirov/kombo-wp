<?php
/*
 * Page template name: Cenovnik
 */
?>

<?php get_header(); ?>

<div class="price-wrapper">
    <div class="price-list-wrapper">
        <?php
        // Učitavamo cene iz opcija
        $prices = get_option('meal_plan_prices', array());

        // Definišemo grupe planova
        $plan_groups = array(
            'slim' => pll__('Slim'),
            'fit' => pll__('Fit'),
            'protein' => pll__('Protein plus'),
            'vege' => pll__('Vege')
        );

        // Definišemo konfiguraciju kalorija za svaki plan
        $plan_calories = array(
            'slim' => array(
                'mali' => '1300',
                'veliki' => '1600'
            ),
            'fit' => array(
                'mali' => '1600',
                'veliki' => '1900'
            ),
            'protein' => array(
                'mali' => '2000',
                'veliki' => '2600'
            ),
            'vege' => array(
                'mali' => '1400',
                'veliki' => '1900'
            )
        );

        // Definišemo tipove paketa
        $packages = array(
            'dnevni' => pll__('Dnevni'),
            'nedeljni5' => array(
                'name' => pll__('Nedeljni 5'),
                'subtitle' => pll__('(radni dani)')
            ),
            'nedeljni6' => array(
                'name' => pll__('Nedeljni 6'),
                'subtitle' => pll__('(radni dani i subota)')
            ),
            'mesecni20' => array(
                'name' => pll__('Mesečni 20'),
                'subtitle' => pll__('(radni dani)')
            ),
            'mesecni24' => array(
                'name' => pll__('Mesečni 24'),
                'subtitle' => pll__('(radni dani i subota)')
            )
        );
        ?>

        <!-- Nazivi grupa -->
        <div class="price-list-wrapper__groups">
            <?php foreach ($plan_groups as $plan_type => $group_name): ?>
                <span><?php echo esc_html( $group_name ); ?></span>
            <?php endforeach; ?>
        </div>

        <!-- Naslovi kolona (kalorije) -->
        <div class="price-list-wrapper__column-headings">
            <?php foreach ($plan_groups as $plan_type => $group_name):
                foreach ($plan_calories[$plan_type] as $size => $calories): ?>
                    <div><?php printf('%s - %s%s', pll__($size === 'mali' ? 'Mali' : 'Veliki'), $calories, pll__('kcal')); ?></div>
            <?php endforeach;
            endforeach; ?>
        </div>
        <!-- Redovi sa cenama -->
        <div>
            <?php foreach ($packages as $package_key => $package_info): ?>
                <div class="price-list-wrapper__row">
                    <div>
                        <?php
                        echo esc_html( is_array( $package_info ) ? $package_info['name'] : $package_info );
                        if ( is_array( $package_info ) && isset( $package_info['subtitle'] ) ) {
                            echo '<br>' . esc_html( $package_info['subtitle'] );
                        }
                        ?>
                    </div>
                    <?php foreach ($plan_groups as $plan_type => $group_name):
                        foreach ($plan_calories[$plan_type] as $size => $calories): ?>
                            <div><?php echo isset($prices[$plan_type][$calories][$package_key]) ? wc_price($prices[$plan_type][$calories][$package_key]) : '0 RSD'; ?></div>
                    <?php endforeach;
                    endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Responsive verzija -->
    <div class="price-list-wrapper-responsive">
        <?php foreach ($plan_groups as $plan_type => $group_name): ?>
            <div class="price-list-wrapper-responsive__column-heading"><?php echo esc_html( $group_name ); ?></div>

            <div class="price-list-wrapper-responsive__package-size <?php echo esc_attr( $plan_type === 'vege' ? 'vege-package' : '' ); ?>">
                <?php foreach ($plan_calories[$plan_type] as $size => $calories): ?>
                    <div><?php printf('%s - %s%s', pll__($size === 'mali' ? 'Mali' : 'Veliki'), $calories, pll__('kcal')); ?></div>
                <?php endforeach; ?>
            </div>

            <div class="prices-wrapper">
                <?php foreach ($packages as $package_key => $package_info): ?>
                    <div>
                        <div class="price-list-wrapper-responsive__package-time">
                            <?php
                            echo esc_html( is_array( $package_info ) ? $package_info['name'] : $package_info );
                            if ( is_array( $package_info ) && isset( $package_info['subtitle'] ) ) {
                                echo ' ' . esc_html( $package_info['subtitle'] );
                            }
                            ?>
                        </div>
                        <div class="price-list-wrapper-responsive__package-prices">
                            <?php foreach ($plan_calories[$plan_type] as $size => $calories): ?>
                                <div><?php echo isset($prices[$plan_type][$calories][$package_key]) ? wc_price($prices[$plan_type][$calories][$package_key]) : '0 RSD'; ?></div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php get_footer(); ?>