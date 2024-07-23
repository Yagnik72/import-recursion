jQuery(document).ready(function ($) {

    window.csv_import_runner = function ( data , data0file, $log ) {
        jQuery('.scrip-item-log').show();

        jQuery.ajax({
            url: ajax_object.ajax_url,
            type: "POST",
            data: data,
            contentType: false,  // Important for multipart/form-data
            processData: false,  // Important for multipart/form-data
            success: function(response) {
                if(response.success) {
                    $log.append( response.data.log );
                    if(response.data.hasOwnProperty('done')) {
                        jQuery($log).closest('.scrip-list-item .import-actions .button.import-scrip').html('Import');
                        let _file_imput = jQuery($log).closest('.scrip-list-item').find('[type="file"]');
                        if(_file_imput.length > 0) {
                            _file_imput.get(0).value = '';
                        }
                        /* fadeout after 3s */
                        window.setTimeout(function () {
                            jQuery($log).closest('.scrip-list-item .import-actions .button.import-scrip').html('');
                        },3000);
                        return true;
                    } else {

                        setTimeout(function () {
                            window.csv_import_runner(data0file, data0file, $log);
                        }, 200);
                    }
                } else {
                    $log.append( response.data );
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
            
            }
        });
    }

    jQuery('.button.import-scrip').click(function (){
        let _action_container = jQuery(this).closest('.import-actions');

        let _data = new FormData();
        let _data0file = new FormData();

        _data.append('scrip_code' , jQuery(this).attr('data-scrip') );
        _data0file.append('scrip_code' , jQuery(this).attr('data-scrip') );

        _data.append('action' , 'scrip_import' );
        _data0file.append('action' , 'scrip_import' );

        if(_action_container.find('[type="file"]').length > 0) {
            _data.append('file' , _action_container.find('[type="file"]').get(0).files[0] );
        }

        setTimeout(function () {
            jQuery('.scrip-item-log').html("");
            jQuery(_action_container).closest('.scrip-list-item').find('.scrip-item-log').html("");

            window.csv_import_runner(_data, _data0file, jQuery(_action_container).closest('.scrip-list-item').find('.scrip-item-log'));
        }, 200);
    });


    jQuery('#add-new-serial-number').on('submit', function(e) {
        e.preventDefault();

        var formData = {
            action: 'add_new_serial_number',
            product_model: jQuery('select#product_model').val(),
            serial_number: $('#serial_number').val(),
        };

        $.post(ajax_object.ajax_url, formData, function(response) {

            if (response.success) {
                swal({
                    title: "Success!",
                    text: response.data.message,
                    icon: "success",
                    confirmButtonText: "OK",
                })

                jQuery('#add-new-serial-number')[0].reset();
                jQuery("#product_model").select2();


            } else {

                swal({
                    title: "Error!",
                    text: response.data.message,
                    icon: "error",
                    confirmButtonText: "OK",
                })
            }
        });
    });

});