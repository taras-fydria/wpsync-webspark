<?php

namespace WpsyncWebspark\Inc;

use Exception;

class SyncProducts extends Singleton
{
    const API_URI = 'https://wp.webspark.dev/wp-api/products';

    public static function sync_products(): void
    {
        $existing_skus = array();

        try {
            // Get the current list of products from the database
            $existing_products = self::get_existing_products();

            // Create an array of existing product SKUs
            foreach ($existing_products as $existing_product) {
                $existing_skus[] = $existing_product->sku;
            }

            // Perform the GET request
            $response = wp_remote_get(self::API_URI, ['timeout' => 21]);

            // Check the response code
            $response_code = wp_remote_retrieve_response_code($response);

            if ($response_code === 200) {
                $body_json = wp_remote_retrieve_body($response);
                $data = json_decode($body_json, true);
                $products = !empty($data['data']) ? array_map(['WpsyncWebspark\Inc\ProductInput', 'build_item'], $data['data']) : [];
                if (!empty($products)) {
                    /** Process each product from the API
                     * @var $product ProductInput
                     */
                    foreach ($products as $product) {
                        echo '<pre>';
                        var_dump($product);
                        echo '</pre>';
                        $sku = $product->sku;
                        if (!in_array($sku, $existing_skus)) {
                            // Create a new product
                            self::create_product($product);
                        } else {
                            // Update the existing product
                            self::update_product($product);
                        }
                    }
                }
            } else {
                // Handle the response code error
                error_log('Error syncing products. Response code: ' . $response_code);
            }
        } catch (Exception $e) {
            // Handle the error
            error_log('Error syncing products: ' . $e->getMessage());
        }

        // Delete unavailable products
        self::delete_unavailable_products($existing_skus);
    }

    private static function get_existing_products(): array
    {
        global $wpdb;

        $query = "
        SELECT ID, meta_value AS sku
        FROM {$wpdb->posts} AS p
        JOIN {$wpdb->postmeta} AS pm ON p.ID = pm.post_id
        WHERE p.post_type = 'product'
        AND p.post_status = 'publish'
        AND pm.meta_key = '_sku'
    ";

        $results = $wpdb->get_results($query, ARRAY_A);

        return $results;
        return [];
    }

    private static function delete_unavailable_products(array $existing_skus)
    {
    }

    private static function create_product(mixed $product)
    {
    }

    private static function update_product(mixed $product)
    {

    }
}