<?php
if ( ! function_exists( 'woocommerce_inactive_notice' ) ) {
    function woocommerce_inactive_notice() {
        ?>
        <div id="message" class="error">
            <p><?php 
                printf(
                    /* translators: %1$s: <strong> */
                    /* translators: %2$s: </strong> */
                    /* translators: %3$s: <a target="_blank" href="http://wordpress.org/extend/plugins/woocommerce/"> */
                    /* translators: %4$s: </a> */
                    /* translators: %5$s: <a href="' . admin_url( 'plugins.php' ) . '"> */
                    /* translators: %6$s: &nbsp;&raquo;</a> */
                    esc_html__(
                        '%1$sWoocommerce Top Pick By Sale is inactive.%2$s The %3$sWooCommerce plugin%4$s must be active for the WooCommerce Top Picks to work. Please %5$sinstall & activate WooCommerce%6$s',
                        'wc-top-pick-by-sale'
                    ),
                    '<strong>',
                    '</strong>',
                    '<a target="_blank" href="' . esc_url( 'http://wordpress.org/extend/plugins/woocommerce/' ) . '">',
                    '</a>',
                    '<a href="' . esc_url( admin_url( 'plugins.php' ) ) . '">',
                    '&nbsp;&raquo;</a>'
                );

                ?>
            </p>
        </div>
        <?php
    }
}

if ( ! function_exists( 'wctpbs_get_plugin_settings' ) ) {
    function wctpbs_get_plugin_settings( $key = '', $default = false ) {
        $wctpbs_plugin_settings = array();
        $all_options = apply_filters( 'wctpbs_all_admin_options', array(
            'wctpbs_frontend_tab_settings',
            'wctpbs_general_tab_settings',
            )
        );
        foreach ( $all_options as $option_name ) {
            if ( is_array( get_option( $option_name, array() ) ) ) {
                $wctpbs_plugin_settings = array_merge( $wctpbs_plugin_settings, get_option( $option_name, array() ) );
            }
        }
        if ( empty( $key ) ) {
            return $default;
        }
        if ( ! isset( $wctpbs_plugin_settings[$key] ) || empty( $wctpbs_plugin_settings[$key] ) ) {
            return $default;
        }
        return $wctpbs_plugin_settings[$key];
    }
}

if ( ! function_exists( 'wctpbs_cron_function' ) ) {
    function wctpbs_cron_function() {
        $product_id = $dufault_limit = '';
        $category_id = wctpbs_get_plugin_settings( 'top_pick_category' ) ? absint( wctpbs_get_plugin_settings( 'top_pick_category' )['value'] ) : '';
        if ( $category_id ) {
            $category = get_term( $category_id, 'product_cat' );
            $cat_slug = $category && !is_wp_error( $category ) ? sanitize_title( $category->slug ) : '';

            $selected_month = wctpbs_get_plugin_settings( 'get_items_from_last_date' ) ? absint( wctpbs_get_plugin_settings( 'get_items_from_last_date' )['value'] ) : 1;
            if ( wctpbs_get_plugin_settings( 'order_status_to_include' ) ) {
                $status = wp_list_pluck( array_filter( wctpbs_get_plugin_settings( 'order_status_to_include' ) ), 'value' );
                $order_statuses_sql = array_values( $status );
            } else {
                $order_statuses_sql = array('wc-processing', 'wc-completed');
            }

            // Create a DateTime object for the current date and time
            $currentDate = new DateTime();
            $MonthsAgo = $currentDate->modify('-'.$selected_month.' months');
            $formattedDate = sanitize_text_field( $MonthsAgo->format('Y-m-d') );

            if ( wctpbs_get_plugin_settings( 'unassign_prev_products' ) ) {
                $dufault_limit = wctpbs_get_plugin_settings( 'max_top_picks_products' ) ? absint( wctpbs_get_plugin_settings( 'max_top_picks_products' ) ) : '';
                wctpbs_unassign_old_product_cat( $category_id );
            }

            // Get all products count
            $orders = wc_get_orders( array(
                'type'          => 'shop_order',
                'limit'         => -1,
                'status'        => $order_statuses_sql,
                'date_after'    => $formattedDate,
            ) );

            $order_item_counts = array();

            // Loop through each order
            foreach ( $orders as $order ) {
                // Get order items
                $items = $order->get_items();
                
                // Loop through each order item
                foreach ( $items as $item ) {
                    $product_id = absint( $item->get_product_id() );
                    
                    // Increase the count for this product
                    if ( isset( $order_item_counts[$product_id] ) ) {
                        $order_item_counts[$product_id] += absint( $item->get_quantity() );
                    } else {
                        $order_item_counts[$product_id] = absint( $item->get_quantity() );
                    }
                }
            }

            if ( $order_item_counts ) {
                // Sort the product array by count
                arsort( $order_item_counts );
                if ( $dufault_limit && !empty( $dufault_limit ) ) {
                    $order_item_counts = array_slice( $order_item_counts, 0, $dufault_limit, true );
                }
                // Output the counts
                foreach ( $order_item_counts as $product_id => $count ) {
                    $minimum_order = absint( wctpbs_get_plugin_settings( 'minimum_order_of_product', 0 ) );
                    if ( $count >= $minimum_order ) {
                        wp_set_object_terms( $product_id, sanitize_title( $cat_slug ), 'product_cat', true );
                        update_post_meta( $product_id, 'wctp_sales_count', absint( $count ) );
                    }
                }
            }
        }
    }
}

if ( ! function_exists( 'wctpbs_unassign_old_product_cat' ) ) {
    function wctpbs_unassign_old_product_cat( $cat_id ) {
        $attachments = array();
        //get already assign products of selected product catagory
        $product_args = array(
            'numberposts' => -1,
            'post_status' => array( 'publish', 'pending', 'private', 'draft' ),
            'post_type' => array( 'product', 'product_variation' ),
            'orderby' => 'ID',
            'suppress_filters' => false,
            'order' => 'ASC',
            'offset' => 0,
            'fields' => 'ids',
            'tax_query' => array(
                array(
                    'taxonomy' => 'product_cat',
                    'field' => 'id',
                    'terms' => array( $cat_id ),
                    'operator' => 'IN',
                )
            )
        );
        $product_ids = get_posts( $product_args );
        $email = WC()->mailer()->emails['WC_Admin_Email_Cron_Update'];
        
        $args = array(
            'filename' => gmdate('d-m-Y').'-unassign-products.csv',
            'action' => 'temp',
        );
        
        if ( isset( $product_ids ) && ! empty( $product_ids ) ) {
            $csv = wctpbs_export_assign_products_data( $product_ids, $args, 'Removed' );
            if ( $csv )
            $attachments[] = $csv;
            if ( $email->trigger( $attachments ) ) {
                if ( file_exists( $csv ) ) {
                    wp_delete_file($csv);
                }
            } else {
                if ( file_exists( $csv ) ) {
                    wp_delete_file( $csv );
                }
            }
            foreach ( $product_ids as $product_id ) {
               delete_post_meta( $product_id, 'wctp_sales_count' );
               wp_remove_object_terms( $product_id, $cat_id, 'product_cat' ); 
            }
        }
    }
}

if ( ! function_exists( 'wctpbs_export_assign_products_data' ) ) {
    function wctpbs_export_assign_products_data( $products, $args, $status = null ) {
        $index = 0;
        if ( ! empty( $products ) ) {
            $export_data_index = array();
            
            $default = array(
                'filename' => 'unassign-list.csv',
                'iostream' => 'php://output',
                'buffer' => 'w',
                'action' => 'download',
            );
            $args = wp_parse_args( $args, $default );
    
            $filename = $args['filename'];
            if ( $args['action'] == 'download' ) {
                header("Pragma: public");
                header("Expires: 0");
                header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
                header("Content-Type: application/force-download");
                header("Content-Type: application/octet-stream");
                header("Content-Type: application/download");
                header("Content-Disposition: attachment;filename={$filename}");
                header("Content-Transfer-Encoding: binary");
            }
            
            // Set CSV headers
            $headers = array(
                'product_id'    => __( 'Product Id', 'wc-top-pick-by-sale' ),
                'product_name'  => __( 'Product Name', 'wc-top-pick-by-sale' ),
                'product_sku'   => __( 'Product SKU', 'wc-top-pick-by-sale' ),
                'sales_count'   => __( 'Sales Count', 'wc-top-pick-by-sale' ),
                'status'        => __( 'Status', 'wc-top-pick-by-sale' ),
            );

            if ( ! empty( $products ) ) {
                foreach ( $products as $product_id ) {
                    $product = wc_get_product( $product_id );
                    $sales_count = get_post_meta( $product_id, 'wctp_sales_count', true );
                    $export_data_index = array(
                        'product_id'    => $product_id,
                        'product_name'  => $product->get_name(),
                        'product_sku'   => $product->get_sku(),
                        'sales_count'   => $sales_count,
                        'status'        => $status ? $status : '-'
                    );
                }
            }
            
            ob_start();
            if ( $args['action'] == 'download' && $args['iostream'] == 'php://output' ) {
                $file = fopen($args['iostream'], $args['buffer']);
            } elseif ( $args['action'] == 'temp' && $args['filename'] ) {
                $filename = sys_get_temp_dir() . '/' . $args['filename'];
                $file = fopen( $filename, $args['buffer'] );
            }
            // Add headers to file
            fputcsv( $file, $headers );
            fputcsv( $file, $export_data_index );
            // Close file and get data from output buffer
            fclose($file);
            $csv = ob_get_clean();
            if ( $args['action'] == 'temp' ) {
                return $filename;
            } else {
                // Send CSV to browser for download
                echo $csv;
                die();
            }
        }
    }
}

if ( ! function_exists( 'wctpbs_get_settings_value' ) ) {

    /**
     * get settings value by key
     * @return string
     */
    function wctpbs_get_settings_value( $key = array(), $default = 'false' ) {
        if ( empty( $key ) ) {
            return $default;
        }
        if ( is_array( $key ) && isset( $key['value'] ) ) {
            return $key['value'];
        }
        return $default;
    }

}
