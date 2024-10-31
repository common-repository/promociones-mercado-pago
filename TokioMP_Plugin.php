<?php


include_once('TokioMP_LifeCycle.php');

class TokioMP_Plugin extends TokioMP_LifeCycle {

    /**
     * See: http://plugin.michael-simpson.com/?page_id=31
     * @return array of option meta data.
     */
    public function getOptionMetaData() {
        //  http://plugin.michael-simpson.com/?page_id=31
        return array(
            //'_version' => array('Installed Version'), // Leave this one commented-out. Uncomment to test upgrades.
            'ATextInput' => array(__('Enter in some text', 'my-awesome-plugin')),
            'AmAwesome' => array(__('I like this awesome plugin', 'my-awesome-plugin'), 'false', 'true'),
            'CanDoSomething' => array(__('Which user role can do something', 'my-awesome-plugin'),
                'Administrator', 'Editor', 'Author', 'Contributor', 'Subscriber', 'Anyone')
        );
    }

//    protected function getOptionValueI18nString($optionValue) {
//        $i18nValue = parent::getOptionValueI18nString($optionValue);
//        return $i18nValue;
//    }

    protected function initOptions() {
        $options = $this->getOptionMetaData();
        if (!empty($options)) {
            foreach ($options as $key => $arr) {
                if (is_array($arr) && count($arr > 1)) {
                    $this->addOption($key, $arr[1]);
                }
            }
        }
    }

    public function getPluginDisplayName() {
        return 'Promociones Mercado Pago';
    }

    protected function getMainPluginFileName() {
        return 'promociones-mercado-pago.php';
    }

    /**
     * See: http://plugin.michael-simpson.com/?page_id=101
     * Called by install() to create any database tables if needed.
     * Best Practice:
     * (1) Prefix all table names with $wpdb->prefix
     * (2) make table names lower case only
     * @return void
     */
    protected function installDatabaseTables() {
        //        global $wpdb;
        //        $tableName = $this->prefixTableName('mytable');
        //        $wpdb->query("CREATE TABLE IF NOT EXISTS `$tableName` (
        //            `id` INTEGER NOT NULL");
    }

    /**
     * See: http://plugin.michael-simpson.com/?page_id=101
     * Drop plugin-created tables on uninstall.
     * @return void
     */
    protected function unInstallDatabaseTables() {
        //        global $wpdb;
        //        $tableName = $this->prefixTableName('mytable');
        //        $wpdb->query("DROP TABLE IF EXISTS `$tableName`");
    }


    /**
     * Perform actions when upgrading from version X to version Y
     * See: http://plugin.michael-simpson.com/?page_id=35
     * @return void
     */
    public function upgrade() {
    }

    public function addActionsAndFilters() {

        // Add options administration page
        // http://plugin.michael-simpson.com/?page_id=47
        add_action('admin_menu', array(&$this, 'addSettingsSubMenuPage'));

        // Example adding a script & style just for the options administration page
        // http://plugin.michael-simpson.com/?page_id=47
        //        if (strpos($_SERVER['REQUEST_URI'], $this->getSettingsSlug()) !== false) {
        //            wp_enqueue_script('my-script', plugins_url('/js/my-script.js', __FILE__));
        //            wp_enqueue_style('my-style', plugins_url('/css/my-style.css', __FILE__));
        //        }


        // Add Actions & Filters
        // http://plugin.michael-simpson.com/?page_id=37


        // Adding scripts & styles to all pages
        // Examples:
        //        wp_enqueue_script('jquery');
        //        wp_enqueue_style('my-style', plugins_url('/css/my-style.css', __FILE__));
        //        wp_enqueue_script('my-script', plugins_url('/js/my-script.js', __FILE__));
        wp_enqueue_style( 'styles', plugins_url( '/css/styles.css', __FILE__ ) );

        // Register short codes
        // http://plugin.michael-simpson.com/?page_id=39
        add_shortcode('promociones-mercado-pago', array($this, 'render'));


        // Register AJAX hooks
        // http://plugin.michael-simpson.com/?page_id=41

    }

    // https://www.mercadopago.com/mla/credit_card_promos.json?marketplace=NONE&callback=jQuery1900955840101407722_1520350114759&_=1520350114760
    public function render() {
        $cacheKey = 'promociones-mercado-pago-data';

        $body = wp_cache_get( $cacheKey );

        if ( false === $body) {
            $url = 'https://www.mercadopago.com/mla/credit_card_promos.json?marketplace=NONE';

            $request = wp_remote_get( $url );

            if( is_wp_error( $request ) ) {
                return 'Mensaje de error';
            }

            $body = wp_remote_retrieve_body( $request );

            wp_cache_set( $cacheKey, $body, null, 60 * 10 );
        }

        $data = json_decode( $body );

        $template = '<section class="promociones-mercado-pago"><main class="main"><ul class="bank-description__list">';

        foreach ( $data as $item ) {
            if ( !$item->max_installments ) {
                continue;
            }

            $time = strtotime($item->expiration_date);
            $date = strtolower(date_i18n('d/M/Y', $time));

            $template .= '<li class="bank-description__item">';
            $template .= '<i class="issuer issuer-' . $item->issuer->id . '"></i>';
            $template .= '<p class="bank-description__cuotas">' . $item->max_installments . ' cuotas sin interés</p>';
            $template .= '<p class="bank-description__date-to">Hasta el ' . $date . '</p>';
            $template .= '</li>';
        }

        $template .= '</ul></main></section>';

        return trim($template);
    }
}
