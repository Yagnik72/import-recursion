<?php
// Ensure the WP_List_Table class is loaded
if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

// Define a custom WP_List_Table class for managing CSV data
class Manage_CSV_List_Table extends WP_List_Table {
    function __construct() {
        parent::__construct([
            'singular' => 'csv_entry',
            'plural' => 'csv_entries',
            'ajax' => false
        ]);
    }

    function get_columns() {
        // 'is_registered' => 'Is Registered'
        return [
            'cb' => '<input type="checkbox" />',
            'product_code' => 'Product code',
            'product_alternative_id' => 'Alternative product code',
            'full_name' => 'Full name',
            'model_name' => 'Model',
            'version_name' => 'Version',
            'category_name' => 'Category',
            'rgii_reg' => 'Rgii Reg',
            'warranty_years' => 'Warranty Years'
        ];
    }

    function filter_result(){
        global $wpdb;
        $serial_numbers_table = $wpdb->prefix . 'warranty_'. 'serial_numbers';

        $columns = $this->get_columns();
        $hidden = []; // Columns to hide
        $sortable = []; // Columns to make sortable

        $this->_column_headers = [$columns, $hidden, $sortable];

        // Handle bulk actions
        $this->process_bulk_action();

        // Handle search
        $search_term = isset($_REQUEST['s']) ? sanitize_text_field($_REQUEST['s']) : '';
        $user_type = isset($_REQUEST['user_type']) ? sanitize_text_field($_REQUEST['user_type']) : '';
        $where_clause = '';
        if (!empty($search_term)) {
            $where_clause = $wpdb->prepare(
                "WHERE (serial_numbers.product_code LIKE '%%%s%%' OR models.model_name LIKE '%%%s%%' OR versions.version_name LIKE '%%%s%%' OR product_categories.category_name LIKE '%%%s%%' OR alternative_codes.alternative_code LIKE '%%%s%%')",
                $search_term, $search_term, $search_term, $search_term, $search_term, $search_term
            );
        }
        
        if (!empty($user_type)) {
            $where_clause .= empty($where_clause) ? 'WHERE' : ' AND';
            $where_clause .= $wpdb->prepare(" um.meta_value = %s", $user_type);
        }
        
        $per_page = 15;
        $current_page = $this->get_pagenum();
        $total_items = $wpdb->get_var("SELECT COUNT(serial_numbers.id) FROM {$wpdb->prefix}warranty_serial_numbers AS serial_numbers $where_clause");
        
        $orderby = (!empty($_REQUEST['orderby'])) ? sanitize_text_field($_REQUEST['orderby']) : 'serial_numbers.id';
        $order = (!empty($_REQUEST['order'])) ? sanitize_text_field($_REQUEST['order']) : 'ASC';
        
        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page' => $per_page
        ]);
        
        $offset = ($current_page - 1) * $per_page;
        
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT 
                    serial_numbers.product_code, 
                    serial_numbers.id, 
                    serial_numbers.full_name, 
                    serial_numbers.rgii_reg, 
                    serial_numbers.warranty_years, 
                    models.model_name, 
                    versions.version_name, 
                    product_categories.category_name,
                    GROUP_CONCAT(alternative_codes.alternative_code) AS alternative_product_codes
                FROM {$wpdb->prefix}warranty_serial_numbers AS serial_numbers
                LEFT JOIN {$wpdb->prefix}warranty_models AS models ON serial_numbers.model_id = models.id
                LEFT JOIN {$wpdb->prefix}warranty_versions AS versions ON serial_numbers.version_id = versions.id
                LEFT JOIN {$wpdb->prefix}warranty_product_categories AS product_categories ON serial_numbers.product_category_id = product_categories.id
                LEFT JOIN {$wpdb->prefix}warranty_alternative_product_codes AS alternative_codes ON serial_numbers.id = alternative_codes.serial_number_id
                $where_clause
                GROUP BY serial_numbers.id
                ORDER BY $orderby $order
                LIMIT %d OFFSET %d",
                $per_page,
                $offset
            )
        );        

        //                     um.meta_value AS user_type
        //                 LEFT JOIN {$wpdb->prefix}usermeta AS um ON serial_numbers.is_registered = um.user_id AND um.meta_key = 'user_type'

        return $results;

        // LEFT JOIN {$wpdb->prefix}usermeta AS um ON {$serial_numbers_table}.is_registered = um.user_id AND um.meta_key = 'user_type'

    }

    function prepare_items() {
        
        $results = $this->filter_result();

        $items = [];
        foreach ($results as $result) {

            $items[] = (object) [
                'id' => $result->id,
                'product_code' => $result->product_code,
                'product_alternative_id' => $result->alternative_product_codes,
                'full_name' => $result->full_name,
                'model_name' => $result->model_name,
                'version_name' => $result->version_name,
                'category_name' => $result->category_name,
                'rgii_reg' => $result->rgii_reg,
                'warranty_years' => $result->warranty_years
            ];
        }

        $this->items = $items;
        
    }

    function column_default($item, $column_name) {
        switch ($column_name) {
            case 'id':
                return $item->id;
            case 'product_code':
                return $item->product_code;
            case 'product_alternative_id':
                return $item->product_alternative_id;
            case 'full_name':
                return $item->full_name;
            case 'model_name':
                return $item->model_name;
            case 'version_name':
                return $item->version_name;
            case 'category_name':
                return $item->category_name;
            case 'rgii_reg':
                return $item->rgii_reg;
            case 'warranty_years':
                return $item->warranty_years;
            default:
                return print_r($item, true);
        }
    }

    function column_cb($item) {
        return sprintf(
            '<input type="checkbox" name="csv_entry[]" value="%s" />',
            $item->id
        );
    }

    function display_tablenav($which) {
        if ($which === 'top') {
            $user_type = isset($_GET['user_type']) ? $_GET['user_type'] : '';
            ?>
            <div class="tablenav <?php echo esc_attr($which); ?>">
                <div class="alignleft actions">
                    <select id="user_type" name="user_type">
                        <option value="">User Type</option>
                        <option value="homeowner" <?php selected( $user_type, 'homeowner'); ?>>Homeowner</option>
                        <option value="installer" <?php selected( $user_type, 'installer'); ?>>Installer</option>
                    </select>

                    <input type="submit" class="button" value="Filter">

                </div>
                <div class="alignleft actions">
                    <input class="button button-primary" type="submit" name="export_csv" id="export-csv-record" value="Export CSV">
                </div>
                <?php $this->pagination('top'); ?>
                <br class="clear" />
            </div>
            <?php
        }
    
        if ($which === 'bottom') {
            ?>
            <div class="tablenav bottom">		
                <div class="alignleft actions">
                <?php $this->bulk_actions( $which ); ?>

                </div>
                <div class="tablenav-pages">
                    <?php $this->pagination('bottom'); ?>
                    <br class="clear">
                </div>
            </div>
            <?php
        }
    }

    function get_bulk_actions() {
        $actions = [
            'delete' => 'Delete'
        ];
        return $actions;
    }

    function process_bulk_action() {
        global $wpdb;
        $serial_numbers_table = $wpdb->prefix . 'warranty_serial_numbers';

        if ('delete' === $this->current_action()) {
            $ids = isset($_REQUEST['csv_entry']) ? $_REQUEST['csv_entry'] : [];
            if (is_array($ids) && !empty($ids)) {
                $ids = implode(',', $ids);
                $wpdb->query("DELETE FROM $serial_numbers_table WHERE id IN ($ids)");
            }
        }
    }
}


// Handle bulk actions (delete)
function handle_bulk_actions() {
    if (isset($_GET['action']) && $_GET['action'] === 'delete') {
        $list_table = new Manage_CSV_List_Table();
        $list_table->process_bulk_action();
    }
}
add_action('admin_init', 'handle_bulk_actions');
