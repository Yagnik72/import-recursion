<?php

class CSVImport
{
    private static $instance;
    private $page_size = 20;

    private function __construct()
    {
    }

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function get_endpoints()
    {
        global $wpdb;
        return array(
            'csv_master' => array(
                'label' => 'Upload CSV',
                'url' => 'file',
                'db_table_prefix' => $wpdb->prefix . 'warranty_',
                'handler' => 'import_csv_master',
                'sample' => trailingslashit( get_stylesheet_directory_uri() ) or plugins_url('/', __FILE__) . 'inc/sample/sample.csv',
                'import_status' => array(
                    'page' => get_option("wr_import_csv", 0)
                )
            ),

        );
    }

    public function import_csv_master($data, $scrip_data)
    {
        global $wpdb;
        $prefix = $scrip_data['db_table_prefix'];

        if (!empty($data) and is_array($data)) {

            $log = '';
            $processed = 0;
            $skipped = 0;

            // $all_models = get_all_models();
echo "<pre>";
print_r($data);
echo "</pre>";
exit;

            foreach ($data as $item) {
                $scrip_code = $item[0];

                // if (!empty($scrip_code) && (!in_array('Product Code', $item) || !in_array('Model', $item) ||  !in_array('Alternative Product Codes', $item)) && (is_array($item) && count($item) == 8) ) {
                    
                //     list($product_code, $alt_product_codes, $full_name, $model, $version, $product_category, $rgii_reg, $warranty_years) = $item;
                    

                //     $model_id = $wpdb->get_var($wpdb->prepare(
                //         "SELECT id FROM {$prefix}models WHERE model_name = %s", $model
                //     ));

                //     if (!$model_id) {
                //         $wpdb->insert("{$prefix}models", ['model_name' => $model]);
                //         $model_id = $wpdb->insert_id;
                //     }

                //     $version_id = $wpdb->get_var($wpdb->prepare(
                //         "SELECT id FROM {$prefix}versions WHERE version_name = %s", $version
                //     ));
                //     if (!$version_id) {
                //         $wpdb->insert("{$prefix}versions", ['version_name' => $version]);
                //         $version_id = $wpdb->insert_id;
                //     }

                //     $product_category_id = $wpdb->get_var($wpdb->prepare(
                //         "SELECT id FROM {$prefix}product_categories WHERE category_name = %s", $product_category
                //     ));
                //     if (!$product_category_id) {
                //         $wpdb->insert("{$prefix}product_categories", ['category_name' => $product_category]);
                //         $product_category_id = $wpdb->insert_id;
                //     }
                    
                //     // Check if the product_code already exists
                //     $existing_serial_number_id = $wpdb->get_var($wpdb->prepare(
                //         "SELECT id FROM {$prefix}serial_numbers WHERE product_code = %s", $product_code
                //     ));

                //     if (!$existing_serial_number_id) {

                //         $wpdb->insert("{$prefix}serial_numbers", [
                //             'product_code' => $product_code,
                //             'full_name' => $full_name,
                //             'model_id' => $model_id,
                //             'version_id' => $version_id,
                //             'product_category_id' => $product_category_id,
                //             'rgii_reg' => $rgii_reg,
                //             'warranty_years' => $warranty_years
                //         ]);

                //         $serial_number_id = $wpdb->insert_id;

                //         $alt_product_codes_added = '';
                //         // Insert alternative product codes if not empty and check for duplicates
                //         if (!empty($alt_product_codes)) {
                //             $alt_codes = explode(';', $alt_product_codes);
                //             foreach ($alt_codes as $code) {
                //                 $existing_alt_code_id = $wpdb->get_var($wpdb->prepare(
                //                     "SELECT id FROM {$prefix}alternative_product_codes WHERE serial_number_id = %d AND alternative_code = %s", 
                //                     $serial_number_id, $code
                //                 ));
                                
                //                 if (!$existing_alt_code_id) {
                //                     $wpdb->insert("{$prefix}alternative_product_codes", [
                //                         'serial_number_id' => $serial_number_id,
                //                         'alternative_code' => $code
                //                     ]);

                //                     $alt_product_codes_added .= $code . ',';
                //                 }
                //             }
                //         }
                        
                //         $log .= "Inserted Product Code: {$product_code} and alt_codes added ({$alt_product_codes_added})<br/>";
                //     }else{

                //         $serial_number_id = $existing_serial_number_id;

                //         $alt_product_codes_added = '';
                //         // Insert alternative product codes if not empty and check for duplicates
                //         if (!empty($alt_product_codes)) {
                //             $alt_codes = explode(';', $alt_product_codes);
                //             foreach ($alt_codes as $code) {
                //                 $existing_alt_code_id = $wpdb->get_var($wpdb->prepare(
                //                     "SELECT id FROM {$prefix}alternative_product_codes WHERE serial_number_id = %d AND alternative_code = %s", 
                //                     $serial_number_id, $code
                //                 ));
                                
                //                 if (!$existing_alt_code_id) {
                //                     $wpdb->insert("{$prefix}alternative_product_codes", [
                //                         'serial_number_id' => $serial_number_id,
                //                         'alternative_code' => $code
                //                     ]);

                //                     $alt_product_codes_added .= $code . ',';
                //                 }
                //             }
                //         }

                //         $log .= "Duplicate Product Codes: {$product_code} new alt_codes added ({$alt_product_codes_added})  <br/>";
                        
                //     }

                //     $processed++;
                // } else {
                //     $skipped++;
                // }
            }
        }

        wp_send_json_success(array(
            'log' => $log,
            'processed' => $processed,
            'skipped' => $skipped,
            'rows' => count($data)
        ));
    }

    public function get_uploaded_file($scrip2import)
    {
        $file_path = get_option("wr_import_csv_file");
        if (!empty($file_path) and file_exists($file_path)) {
            return $file_path;
        } elseif (!empty($_FILES) and !empty($_FILES['file'])) {
            $file_data = wp_handle_upload($_FILES['file'], array('test_form' => false));
            if (!empty($file_data) and !is_wp_error($file_data) and !empty($file_data['file'])) {
                update_option("wr_import_csv_file", $file_data['file']);
                return $file_data['file'];
            }
        }
        wp_send_json_error("File upload failed or missing!");
    }

    public function parse_csv($file_path)
    {
        $data = array();
        $fileh = fopen($file_path, "r");
        if (!empty($fileh)) {
            while (($row = fgetcsv($fileh)) !== false) {
                if ($row[1] != "serial_number") {
                    $data = array_merge($data, [$row]);
                }
            }
        }
        return $data;
    }


    public function scrip_import()
    {
        if (defined('DOING_AJAX')) {
            
            if ($scrip_data['url'] === 'file') {
                update_option("wr_import_csv", 0);
                update_option("wr_import_csv_file", "");
            }

            $endpoints = $this->get_endpoints();
            $scrip2import = sanitize_text_field($_POST['scrip_code']);
            $scrip_data = false;
            if (!empty($endpoints[$scrip2import])) {
                $scrip_data = $endpoints[$scrip2import];
            }

            if ($scrip_data['url'] === 'file') {

                $file_path = $this->get_uploaded_file($scrip2import);

                if (!empty($file_path) and file_exists($file_path)) {
                    $file_data = $this->parse_csv($file_path);

                    if (!empty($_FILES) and !empty($_FILES['file'])) {

                        $path_data = pathinfo($file_path);
                        if ($path_data['extension'] !== 'csv') {
                            wp_send_json_error("Import Failed, unsupported file type. Please upload a CSV file.");
                        }
                    }

                    if (!empty($file_data) and is_array($file_data)) {
                        $data_chunks = array_chunk($file_data, $this->page_size, true);
                        $page_data = $data_chunks[$scrip_data['import_status']['page']];
                        if (empty($page_data)) {
                            update_option("wr_import_csv", 0);
                            if (file_exists($file_path)) {
                                @unlink($file_path);
                            }
                            update_option("wr_import_csv_file", "");
                            wp_send_json_success(array(
                                'log' => "Import completed!<br/>",
                                'processed' => 0,
                                'skipped' => 0,
                                'rows' => 0,
                                'done' => true
                            ));
                        }
                        update_option("wr_import_csv", $scrip_data['import_status']['page'] + 1);
                        call_user_func_array(array($this, $scrip_data['handler']), array($page_data, $scrip_data));
                    } else {
                        wp_send_json_error("No data is available for import.");
                    }
                } else {
                    wp_send_json_error("No data is available for import.");
                }
            }
        }
    }
}

add_action('wp_ajax_scrip_import', array(CSVImport::getInstance(), 'scrip_import'));
