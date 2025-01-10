<?php
if ( ! function_exists( 'wctpbs_admin_tabs' ) ) {
    function wctpbs_admin_tabs() {
        $default_massages = wctpbs_default_massages();
        //all category
        $args_cat = array( 'taxonomy'   => 'product_cat', 'orderby' => 'name', 'order' => 'ASC', 'hide_empty' => false  );
		$terms = get_terms( $args_cat );
        $all_product_cat = array();
		if ( $terms && empty( $terms->errors ) ) {
			foreach ( $terms as $term) {
				if ($term) {
					$all_product_cat[] = array(
						'value' => $term->term_id,
						'label' => $term->name,
						'key'   => $term->term_id,
					);
				}
			}
		}
        //all order status
        $order_statuses = wc_get_order_statuses();
        $statuses = array();
        foreach ( $order_statuses as $key => $status ) {
            $statuses[] = array(
                'value' => $key,
                'label' => $status,
                'key'   => $key,
            );
        }

        $periods = array(
            1   => __( 'One', 'wc-top-pick-by-sale' ),
            2   => __( 'Two', 'wc-top-pick-by-sale' ),
            3   => __( 'Three', 'wc-top-pick-by-sale' ),
            4   => __( 'Four', 'wc-top-pick-by-sale' ), 
            5   => __( 'Five', 'wc-top-pick-by-sale' ),
            6   => __( 'Six', 'wc-top-pick-by-sale' ),
            7   => __( 'Seven', 'wc-top-pick-by-sale' ),
            8   => __( 'Eight', 'wc-top-pick-by-sale' ),
            9   => __( 'Nine', 'wc-top-pick-by-sale' ),
            10  => __( 'Ten', 'wc-top-pick-by-sale' ),
            11  => __( 'Eleven', 'wc-top-pick-by-sale' ),
            12  => __( 'Twelfth', 'wc-top-pick-by-sale' )
        );
        //all order periods
        foreach ( $periods as $key => $value ) {
            $order_periods[] = array(
                'value' => $key,
                /* translators: %s: month name */
                'label' => sprintf( __( 'Last %s Month', 'wc-top-pick-by-sale' ), $value ), 
                'key'   => $key,
            );
        } 

        $wctp_settings_page_endpoint = apply_filters( 'wc_top_pick_by_sale_endpoint_fields_before_value', array(
            'general' => array(
                'tablabel'        => __( 'General', 'wc-top-pick-by-sale' ),
                'apiurl'          => 'save_admin_settings',
                'description'     => __( 'Configure Basic Top Pick By Sales Settings. ', 'wc-top-pick-by-sale' ),
                'icon'            => 'dashicons dashicons-admin-generic',
                'submenu'         => 'settings',
                'modulename'      => [
                    [
                        'key'       => 'top_pick_category',
                        'type'      => 'select',
                        'label'     => __( 'Choose a Category as Top Pick', 'wc-top-pick-by-sale' ),
                        'desc'      => __( 'Select a Category to Assign All Top Pick Products', 'wc-top-pick-by-sale' ),
                        'placeholder'=> __( 'Choose options', 'wc-top-pick-by-sale' ),
                        'options' => $all_product_cat,
                        'database_value' => '',
                    ],
                    [
                        'key'        => 'get_items_from_last_date',
                        'type'       => 'select',
                        'label'      => __( 'Choose Previous Ordering Periods', 'wc-top-pick-by-sale' ),
                        'desc'       => __( 'Choose Ordering Periods for Item Searches and Sales Count', 'wc-top-pick-by-sale' ),
                        'placeholder'=> __( 'Choose options', 'wc-top-pick-by-sale' ),
                        'options'    => $order_periods,
                        'database_value' => '',
                    ],
                	[
                        'key'       => 'unassign_prev_products',
                        'label'     => __("Unassign Previous Top Pick Products", 'wc-top-pick-by-sale'),
                        'class'     => 'wctp-toggle-checkbox',
                        'type'      => 'checkbox',
                        'options'   => array(
                            array(
                                'key'   => "unassign_prev_products",
                                'label' => __( 'Unassign Previous Top Pick Products From Selected Catagory', 'wc-top-pick-by-sale' ),
                                'value' => "unassign_prev_products"
                            ),
                        ),
                        'database_value' => array(),
                    ],
                    [
                        'key'               => 'max_top_picks_products',
                        'type'              => 'number',
                        'class'             => 'wctp-setting-wpeditor-class',
                        'depend_checkbox'   => 'unassign_prev_products',
                        'label'             => __( 'Max Assign Products', 'wc-top-pick-by-sale' ),
                        'desc'              => __( 'Maximum Products to be Assign in Selected Category.(Default: Not Set)', 'wc-top-pick-by-sale' ),
                        'database_value'    => '',
                    ],
                    [
                        'key'      => 'minimum_order_of_product',
                        'label'    => __( "Select Minimum Order Quantity of a Product", 'wc-top-pick-by-sale' ),
                        'desc'     => __( 'Minimum Quantity Required to Assign a Product in the Top Pick Category.(Default: 0)', 'wc-top-pick-by-sale' ),
                        'class'    => 'wctp-toggle-checkbox',
                        'type'     => 'number',
                        'database_value' => '',
                    ],
                    [
                        'key'        => 'order_status_to_include',
                        'type'       => 'multi-select',
                        'label'      => __( 'Select Order Status', 'wc-top-pick-by-sale' ),
                        'desc'       => __( 'Choose Order Statuses to Include in Order Item Count', 'wc-top-pick-by-sale' ),
                        'placeholder'=> __( 'Choose options', 'wc-top-pick-by-sale' ),
                        'options'    => $statuses,
                        'database_value' => '',
                    ],
                ]
            ),
            'frontend' => array(
                'tablabel'        => __( 'Frontend', 'wc-top-pick-by-sale' ),
                'apiurl'          => 'save_admin_settings',
                'description'     => __( 'Frontend Settings', 'wc-top-pick-by-sale' ),
                'icon'            => 'dashicons dashicons-format-aside',
                'submenu'         => 'settings',
                'modulename'      =>  [
                    [
                        'key'       => 'enable_show_order_count',
                        'label'     => __( "Show Order Count in Single Product Page", 'wc-top-pick-by-sale' ),
                        'class'     => 'wctp-toggle-checkbox',
                        'type'      => 'checkbox',
                        'options'   => array(
                            array(
                                'key'   => "enable_show_order_count",
                                'label' => __( 'Show Order Count in Single Product Page', 'wc-top-pick-by-sale' ),
                                'value' => "enable_show_order_count"
                            ),
                        ),
                        'database_value' => array(),
                    ],
                    [
                        'key'               => 'order_in_days',
                        'label'             => __( "Specify the Duration for Order Item Sale (Days)", 'wc-top-pick-by-sale' ),
                        'desc'              => __( 'Specify the Duration for Order Item Sale To Show in Single Product Page.(Default: 7 days)', 'wc-top-pick-by-sale' ),
                        'depend_checkbox'   => 'enable_show_order_count',
                        'class'             => 'wctp-toggle-checkbox',
                        'type'              => 'number',
                        'database_value'    => '',
                    ],
                    [
                        'key'               => 'shown_order_count_text',
                        'type'              => 'textarea',
                        'class'             => 'wctp-setting-wpeditor-class',
                        'depend_checkbox'   => 'enable_show_order_count',
                        'label'             => __( 'Order Count Text', 'wc-top-pick-by-sale' ),
                        'placeholder'       => $default_massages['shown_order_count_text'],
                        // translators: %order_count% is replaced with the number of orders, and %day_count% is replaced with the number of days.
                        'desc'              => __( 'Customize the Massage to Show Recent Orders. Note: Use %order_count% as number of orders and %day_count% as days.', 'wc-top-pick-by-sale' ),
                        'database_value'    => '',
                    ],
                    [
                        'key'       => 'avialable_shortcodes',
                        'type'      => 'table',
                        'label'     => __( 'Avialable Shortcodes', 'wc-top-pick-by-sale' ),
                        'label_options' =>  array(
                            __( 'Shortcodes', 'wc-top-pick-by-sale' ),
                            __( 'Description', 'wc-top-pick-by-sale' ),
                        ),
                        'options' => array(
                            array(
                                'variable'=> "<code>[top_pick_by_sale_products]</code>",
                                'description'=> __( 'Show all top pick products in a page', 'wc-top-pick-by-sale' ),
                            ),
                        ),
                        'database_value' => '',
                    ],
                                        
                ]
            )
        ));

        if ( ! empty( $wctp_settings_page_endpoint ) ) {
            foreach ( $wctp_settings_page_endpoint as $settings_key => $settings_value ) {
                if ( isset( $settings_value['modulename'] ) && !empty( $settings_value['modulename'] ) ) {
                    foreach ( $settings_value['modulename'] as $inter_key => $inter_value ) {
                        $change_settings_key = str_replace( "-", "_", $settings_key );
                        $option_name = 'wctpbs_'.$change_settings_key.'_tab_settings';
                        $database_value = get_option($option_name) ? get_option($option_name) : array();
                        if ( ! empty( $database_value ) ) {
                            if ( isset( $inter_value['key'] ) && array_key_exists( $inter_value['key'], $database_value ) ) {
                                if ( empty( $inter_value['database_value'] ) ) {
                                   $wctp_settings_page_endpoint[$settings_key]['modulename'][$inter_key]['database_value'] = $database_value[$inter_value['key']];
                                }
                            }
                        }
                    }
                }
            }
        }

        $wc_top_pick_backend_tab_list = apply_filters( 'wctpbs_admin_tab_list', array(
            'top-picks-settings' => $wctp_settings_page_endpoint,
        ) );
        
        return $wc_top_pick_backend_tab_list;
    }
}

if ( ! function_exists('wctpbs_default_massages' ) ) {
    function wctpbs_default_massages() {
        $default_massages = array(
            // translators: %order_count% is the number of orders, and %day_count% is the number of days.
            'shown_order_count_text' => __( '%order_count% bought in past %day_count% days.', 'wc-top-pick-by-sale' ),
        );
        return $default_massages;
    }
}