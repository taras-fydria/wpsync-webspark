<?php

namespace WpsyncWebspark\Inc;

use Exception;
use WC_Data_Exception;
use WC_Product_Simple;

class SyncProducts extends Singleton
{
    const API_URI = 'https://wp.webspark.dev/wp-api/products';

    public static function sync_products(): void
    {
        $existing_skus = array();

        try {
            // Get the current list of products from the database
            $existing_products = self::get_existing_products();
            $existing_skus = !empty($existing_products) ? array_map(function ($item) {
                return $item['sku'];
            }, $existing_products) : [];
            $existing_products_id = !empty($existing_products) ? array_map(function ($item) {
                return $item['ID'];
            }, $existing_products) : [];

            // Perform the GET request
            $response = wp_remote_get(self::API_URI, ['timeout' => 21]);

            // Check the response code
            $response_code = wp_remote_retrieve_response_code($response);

            $proceed_products_id = [];

            if ($response_code === 200) {
                $body_json = wp_remote_retrieve_body($response);
                $data = json_decode($body_json, true);
                $products = !empty($data['data']) ? array_map([ProductInput::class, 'build_item'], $data['data']) : [];

                if (!empty($products)) {
                    /** Process each product from the API
                     * @var $product ProductInput
                     */


                    $array_chunks = array_chunk($products, 100);
                    foreach ($array_chunks as $chunk) {

                        foreach ($chunk as $product) {

                            $product_index = array_search($product->sku, $existing_skus);
                            if (gettype($product_index) === 'integer' && $product_index >= 0) {
                                // Create a new product
                                $product_id = $existing_products_id[$product_index];
                                $proceed_products_id[] = self::update_product($product_id, $product);
                            } else {
                                // Update the existing product
                                $proceed_products_id[] = self::create_product($product);
                            }
                        }
                    }

                    //Delete products  which don't was in response
                    foreach ($existing_products_id as $existing_id) {
                        if (!in_array($existing_products_id, $proceed_products_id)) {
                            $wc_product = wc_get_product($existing_id);
                            if ($wc_product) $wc_product->delete(true);
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

        $query = "SELECT ID, meta_value AS sku FROM {$wpdb->posts} AS p JOIN {$wpdb->postmeta} AS pm ON p.ID = pm.post_id WHERE p.post_type = 'product' AND pm.meta_key = '_sku'";

        $results = $wpdb->get_results($query, ARRAY_A);

        return $results;
    }

    private static function delete_unavailable_products(array $existing_skus)
    {
    }

    /**
     * @throws WC_Data_Exception
     */
    private static function create_product(ProductInput $product): int
    {
        $wc_product = new WC_Product_Simple();
        $wc_product->set_name($product->name);
        $wc_product->set_description($product->description);
        $wc_product->set_price($product->price);
        $wc_product->set_sku($product->sku);
        $wc_product->set_manage_stock($product->stock_count);
        $wc_product->set_stock_quantity($product->stock_count);
        $wc_product->set_status('publish');
        if ($product->picture_url !== '') {
            self::save_and_attach_image_by_url($wc_product->get_id(), $product->picture_url);
        }
        // Return the created product ID
        return $wc_product->save();
    }

    private static function update_product(int $product_id, ProductInput $product_data): ?int
    {
        $wc_product = wc_get_product($product_id);
        if (!$wc_product) return null;

        if ($wc_product->get_name('') !== $product_data->name) $wc_product->set_name($product_data->name);

        if ($wc_product->get_description('') !== $product_data->description) $wc_product->set_description($product_data->description);

        if ($wc_product->get_price('') !== $product_data->price) $wc_product->set_price($product_data->price);

        if ($wc_product->get_stock_quantity('') !== $product_data->stock_count) $wc_product->set_stock_quantity($product_data->stock_count);

        self::save_and_attach_image_by_url($product_id, $product_data->picture_url);

        return $wc_product->save();
    }

    static function save_and_attach_image_by_url(int $post_id, string $image_url): int | bool
    {
        require_once ABSPATH . 'wp-admin/includes/image.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';

        $thumbnail_id = get_post_thumbnail_id($post_id);
        if ($thumbnail_id && get_post_meta($thumbnail_id, 'image_url') === $image_url) {
            return $thumbnail_id;
        }

        if ($thumbnail_id) {
            wp_delete_attachment($thumbnail_id, true);
        }

        // Download the image from the URL
        $media_id = media_sideload_image($image_url, $post_id);
        add_post_meta($post_id, 'image_url', $image_url);

        // If the image was successfully attached, set it as the post thumbnail
        if (!is_wp_error($media_id)) {
            return set_post_thumbnail($post_id, $media_id);
        } else {
            return false;
        }
    }
}