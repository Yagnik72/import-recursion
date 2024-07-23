<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Admin_CSV {

    public function __construct() {

        add_action('admin_menu', array($this,'register_custom_menu_page'));
    }

    
    function register_custom_menu_page(){
        // add_menu_page('CSV Manager', 'CSV Manager', 'manage_options', 'csv-upload', array($this, 'csv_upload_page'), '', 6);


        add_menu_page('CSV Manager', 'CSV Manager', 'manage_options', 'csv-manager', array($this,'manage_csv_page'), '', 6);
        add_submenu_page('csv-manager', 'Upload CSV', 'Upload CSV', 'manage_options', 'csv-upload', array($this, 'csv_upload_page'));
    }

    function manage_csv_page() {

        include_once(WRXP_PATH . 'inc/admin/wp_list_table.php'); 

        $csv_list_table = new Manage_CSV_List_Table();
        $csv_list_table->prepare_items();
        ?>
        <div class="wrap">
            <h1>Serial Numbers Manager</h1>
            
            <form method="get" style="margin-top:10px">
                <a href="<?php echo home_url( '/' ) . 'wp-admin/admin.php?page=csv-upload'?>" class="button">New Record</a> 
                <p class="search-box">
                    <input type="hidden" name="page" value="<?php echo esc_attr($_REQUEST['page']); ?>">
                    <input type="text" name="s" placeholder="Search Record" value="<?php echo isset($_REQUEST['s']) ? esc_attr($_REQUEST['s']) : ''; ?>">
                    <input type="submit" class="button" value="Search">
                </p>
            </form>

            <!-- Display the WP_List_Table -->
            <form method="get">
                <input type="hidden" name="page" value="csv-manager">
                <input type="hidden" name="s" value="<?php echo isset($_REQUEST['s']) ? esc_attr($_REQUEST['s']) : ''; ?>">
                <?php $csv_list_table->display(); ?>
            </form>
    
        </div>
        <?php
    }
    
    

    function csv_upload_page() {
            update_option("wr_import_csv", 0);
            $importer =  CSVImport::getInstance();

            $products = wc_get_products( array( 'limit' => -1 ) );


            ?>

            <div id="scrip-import-container" class="wrap">
                <h1>Import CSV</h1>
                <div id="poststuff" class="poststuff">
                    <div class="postbox-container">
                        <div class="meta-box-sortables ui-sortable">
                            <div class="postbox">
                                <div class="scrip-list-items">
                                    <?php foreach($importer->get_endpoints() as $endpoint_key=>$endpoint) {
                                        ?>
                                        <div class="scrip-list-item">
                                            <div class="scrip-item-row">
                                                <div class="scrip-item-label"><?php echo $endpoint['label'] ?></div>
                                                <div class="import-actions">

                                                    <?php if($endpoint['url']==='file' or $endpoint['url']==='fs_direct'){
                                                        ?><input type="file" name="<?php echo $endpoint_key.'_file' ?>"><?php
                                                    }?>
                                                    <button data-scrip="<?php echo $endpoint_key; ?>" data-import='<?php echo htmlspecialchars(json_encode($endpoint['import_status']), ENT_QUOTES, 'UTF-8'); ?>' class="button import-scrip"><?php if($endpoint['import_status']['page']>1){ echo 'Resume'; } else { echo 'Import'; } ?></button>

                                                </div>
                                            </div>
                                            <div class="scrip-item-log" style="display:none"></div>
                                        </div>
                                        <?php
                                    } ?>
                                </div>
                            </div>

                            <hr>

                            <div class="add-new-record">
                                <h2 style="padding-left: 0;">Add New Record</h2>
                                <form method="post" id="add-new-serial-number" action="">
                                    <input type="hidden" name="action" value="add_csv_entry">
                                    <div class="form-wrapper">
                                        <div style="width:33%;">
                                            <label for="product_id">Product ID:</label>
                                            <select id="product_model" name="product_model" required>
                                            <option value="">Select a product...</option>
                                                <?php foreach ( $products as $product ) : ?>
                                                    <option value="<?php echo esc_attr( $product->get_id() ); ?>">
                                                        <?php echo esc_html( $product->get_name() ) . ' (' . $product->get_id() .')'; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div>
                                            <label for="serial_number">Serial Number:</label>
                                            <input type="text" name="serial_number" id="serial_number" required><br>
                                        </div>
                                        <div>
                                            <input type="submit" class="button button-primary" name="add_entry" value="Add Entry">
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <style>
                .scrip-item-log {
                    background-color: black;
                    color: white;
                    padding: 1rem;
                    line-height: 1.5rem;
                }

                .icon.error { display: none; }

                .select2.select2-container {
                    width: 70% !important;
                }

                #scrip-import-container .scrip-list-items {
                    padding: 2rem;
                }

                form#add-new-serial-number .form-wrapper {
                    display: flex;
                }

                form#add-new-serial-number .form-wrapper input {
                    margin-left: 8px;
                }

                form#add-new-serial-number .form-wrapper label[for="serial_number"] {
                    margin-left: 14px;
                }
            </style>
            <script>
                // jQuery(document).ready(function($) {
                //     $('#product_model').select2();
                // });
            </script>
            <?php
    }

}

new Admin_CSV();
