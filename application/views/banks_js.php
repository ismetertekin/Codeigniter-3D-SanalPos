<?php header('Access-Control-Allow-Origin: *'); ?>
<?php header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS'); ?>
<?php header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token'); ?>

<script type="text/javascript">
    $(document).on("click", ".bank_detail", function (e) {
        e.preventDefault();
        var bank_id = $(this).data('bank-id');
        $.post('<?php echo base_url('banks/bank_detail');?>', {bank_id: bank_id}, function (data) {
            $("input[name='bank_id']").val(data['bank_id']);
            $("input[name='name']").val(data['name']);
            $("select[name='method']").val(data['method']).trigger('change');
            $("select[name='model']").val(data['model']).trigger('change');
            $("select[name='status']").val(data['status']).trigger('change');
            $("select[name='bank_code']").val(data['bank_code']).trigger('change');
            $("select[name='default_bank']").val(data['default_bank']).trigger('change');
            $('#bank_modal').modal('show');
        });
    });

    $(document).on("click", ".bank_settings", function (e) {
        e.preventDefault();
        var bank_id = $(this).data('bank-id');
        $.post('<?php echo base_url('banks/bank_info');?>', {bank_id: bank_id}, function (data) {
            $("input[name='bank_id']").val(data['bank_id']);
            $(".info_title").html(data['bank_name']);
            disabled_info_input(data['method']);
            if (data['method'] == 'nestpay') {
                $('#nestpay').removeAttr('hidden');
                $('#gvp').attr('hidden', 'hidden');
                $('#get724').attr('hidden', 'hidden');
                $('#posnet').attr('hidden', 'hidden');
                if (data['bank_info'] != null) {
                    $("input[name='nestpay_client_id']").val(data['bank_info']['nestpay_client_id']);
                    $("input[name='nestpay_classic_name']").val(data['bank_info']['nestpay_classic_name']);
                    $("input[name='nestpay_classic_password']").val(data['bank_info']['nestpay_classic_password']);
                    $("input[name='nestpay_3D_storekey']").val(data['bank_info']['nestpay_3D_storekey']);
                    $("input[name='nestpay_classic_url']").val(data['bank_info']['nestpay_classic_url']);
                    $("input[name='nestpay_3D_url']").val(data['bank_info']['nestpay_3D_url']);
                }
            }
            if (data['method'] == 'gvp') {
                $('#nestpay').attr('hidden', 'hidden');
                $('#gvp').removeAttr('hidden');
                $('#get724').attr('hidden', 'hidden');
                $('#posnet').attr('hidden', 'hidden');
                if (data['bank_info'] != null) {
                    $("input[name='gvp_terminal_id']").val(data['bank_info']['gvp_terminal_id']);
                    $("input[name='gvp_merchant_id']").val(data['bank_info']['gvp_merchant_id']);
                    $("input[name='gvp_user_name']").val(data['bank_info']['gvp_user_name']);
                    $("input[name='gvp_provaut_password']").val(data['bank_info']['gvp_provaut_password']);
                    $("input[name='gvp_3D_storekey']").val(data['bank_info']['gvp_3D_storekey']);
                    $("input[name='gvp_classic_url']").val(data['bank_info']['gvp_classic_url']);
                    $("input[name='gvp_3D_url']").val(data['bank_info']['gvp_3D_url']);
                }
            }
            if (data['method'] == 'get724') {
                $('#nestpay').attr('hidden', 'hidden');
                $('#gvp').attr('hidden', 'hidden');
                $('#get724').removeAttr('hidden');
                $('#posnet').attr('hidden', 'hidden');
                if (data['bank_info'] != null) {
                    $("input[name='get724_merchant_id']").val(data['bank_info']['get724_merchant_id']);
                    $("input[name='get724_user_name']").val(data['bank_info']['get724_user_name']);
                    $("input[name='get724_merchant_password']").val(data['bank_info']['get724_merchant_password']);
                    $("input[name='get724_3D_storekey']").val(data['bank_info']['get724_3D_storekey']);
                    $("input[name='get724_classic_url']").val(data['bank_info']['get724_classic_url']);
                    $("input[name='get724_3D_url']").val(data['bank_info']['get724_3D_url']);
                }
            }
            if (data['method'] == 'posnet') {
                $('#nestpay').attr('hidden', 'hidden');
                $('#gvp').attr('hidden', 'hidden');
                $('#get724').attr('hidden', 'hidden');
                $('#posnet').removeAttr('hidden');
                if (data['bank_info'] != null) {
                    $("input[name='posnet_id']").val(data['bank_info']['posnet_id']);
                    $("input[name='posnet_terminal_id']").val(data['bank_info']['posnet_terminal_id']);
                    $("input[name='posnet_merchant_id']").val(data['bank_info']['posnet_merchant_id']);
                    $("input[name='posnet_user_name']").val(data['bank_info']['posnet_user_name']);
                    $("input[name='posnet_provaut_password']").val(data['bank_info']['posnet_provaut_password']);
                    $("input[name='posnet_classic_url']").val(data['bank_info']['posnet_classic_url']);
                    $("input[name='posnet_3D_url']").val(data['bank_info']['posnet_3D_url']);
                }
            }
            if (data['bank_info'] != null) {
                if (data['bank_info']['instalment']) {
                    $("input[name='instalment']").val(data['bank_info']['instalment']);
                }
            }
            $('#bank_settings_modal').modal('show');
        });
    });

    $(document).on("click", ".delete_bank", function (e) {
        e.preventDefault();
        var bank_id = $(this).data('bank-id');
        $.post('<?php echo base_url('banks/delete_bank');?>', {bank_id: bank_id}, function (data) {
            window.location.reload();
        });
    });

    $('#bank_modal').on('hidden.bs.modal', function () {
        $('#bank_form').trigger('reset');
        $("select[name='method']").val('').trigger('change');
        $("select[name='model']").val('').trigger('change');
        $("select[name='bank_code']").val('').trigger('change');
        $("select[name='default_bank']").val('0').trigger('change');
        $("select[name='status']").val('1').trigger('change');
    });

    $('#bank_settings_modal').on('hidden.bs.modal', function () {
        $('#bank_detail_form').trigger('reset');
    });

    function disabled_info_input(type) {
        if (type == 'nestpay') {
            $("input[name='nestpay_client_id']").removeAttr('disabled');
            $("input[name='nestpay_classic_name']").removeAttr('disabled');
            $("input[name='nestpay_classic_password']").removeAttr('disabled');
            $("input[name='nestpay_3D_storekey']").removeAttr('disabled');
            $("input[name='nestpay_classic_url']").removeAttr('disabled');
            $("input[name='nestpay_3D_url']").removeAttr('disabled');

            $("input[name='gvp_terminal_id']").attr('disabled', 'disabled');
            $("input[name='gvp_merchant_id']").attr('disabled', 'disabled');
            $("input[name='gvp_user_name']").attr('disabled', 'disabled');
            $("input[name='gvp_provaut_password']").attr('disabled', 'disabled');
            $("input[name='gvp_3D_storekey']").attr('disabled', 'disabled');
            $("input[name='gvp_classic_url']").attr('disabled', 'disabled');
            $("input[name='gvp_3D_url']").attr('disabled', 'disabled');

            $("input[name='get724_merchant_id']").attr('disabled', 'disabled');
            $("input[name='get724_user_name']").attr('disabled', 'disabled');
            $("input[name='get724_merchant_password']").attr('disabled', 'disabled');
            $("input[name='get724_3D_storekey']").attr('disabled', 'disabled');
            $("input[name='get724_classic_url']").attr('disabled', 'disabled');
            $("input[name='get724_3D_url']").attr('disabled', 'disabled');

            $("input[name='posnet_id']").attr('disabled', 'disabled');
            $("input[name='posnet_terminal_id']").attr('disabled', 'disabled');
            $("input[name='posnet_merchant_id']").attr('disabled', 'disabled');
            $("input[name='posnet_user_name']").attr('disabled', 'disabled');
            $("input[name='posnet_provaut_password']").attr('disabled', 'disabled');
            $("input[name='posnet_classic_url']").attr('disabled', 'disabled');
            $("input[name='posnet_3D_url']").attr('disabled', 'disabled');
        }
        if (type == 'gvp') {
            $("input[name='nestpay_client_id']").attr('disabled', 'disabled');
            $("input[name='nestpay_classic_name']").attr('disabled', 'disabled');
            $("input[name='nestpay_classic_password']").attr('disabled', 'disabled');
            $("input[name='nestpay_3D_storekey']").attr('disabled', 'disabled');
            $("input[name='nestpay_classic_url']").attr('disabled', 'disabled');
            $("input[name='nestpay_3D_url']").attr('disabled', 'disabled');

            $("input[name='gvp_terminal_id']").removeAttr('disabled');
            $("input[name='gvp_merchant_id']").removeAttr('disabled');
            $("input[name='gvp_user_name']").removeAttr('disabled');
            $("input[name='gvp_provaut_password']").removeAttr('disabled');
            $("input[name='gvp_3D_storekey']").removeAttr('disabled');
            $("input[name='gvp_classic_url']").removeAttr('disabled');
            $("input[name='gvp_3D_url']").removeAttr('disabled');

            $("input[name='get724_merchant_id']").attr('disabled', 'disabled');
            $("input[name='get724_user_name']").attr('disabled', 'disabled');
            $("input[name='get724_merchant_password']").attr('disabled', 'disabled');
            $("input[name='get724_3D_storekey']").attr('disabled', 'disabled');
            $("input[name='get724_classic_url']").attr('disabled', 'disabled');
            $("input[name='get724_3D_url']").attr('disabled', 'disabled');

            $("input[name='posnet_id']").attr('disabled', 'disabled');
            $("input[name='posnet_terminal_id']").attr('disabled', 'disabled');
            $("input[name='posnet_merchant_id']").attr('disabled', 'disabled');
            $("input[name='posnet_user_name']").attr('disabled', 'disabled');
            $("input[name='posnet_provaut_password']").attr('disabled', 'disabled');
            $("input[name='posnet_classic_url']").attr('disabled', 'disabled');
            $("input[name='posnet_3D_url']").attr('disabled', 'disabled');
        }
        if (type == 'get724') {
            $("input[name='nestpay_client_id']").attr('disabled', 'disabled');
            $("input[name='nestpay_classic_name']").attr('disabled', 'disabled');
            $("input[name='nestpay_classic_password']").attr('disabled', 'disabled');
            $("input[name='nestpay_3D_storekey']").attr('disabled', 'disabled');
            $("input[name='nestpay_classic_url']").attr('disabled', 'disabled');
            $("input[name='nestpay_3D_url']").attr('disabled', 'disabled');

            $("input[name='gvp_terminal_id']").attr('disabled', 'disabled');
            $("input[name='gvp_merchant_id']").attr('disabled', 'disabled');
            $("input[name='gvp_user_name']").attr('disabled', 'disabled');
            $("input[name='gvp_provaut_password']").attr('disabled', 'disabled');
            $("input[name='gvp_3D_storekey']").attr('disabled', 'disabled');
            $("input[name='gvp_classic_url']").attr('disabled', 'disabled');
            $("input[name='gvp_3D_url']").attr('disabled', 'disabled');

            $("input[name='get724_merchant_id']").removeAttr('disabled');
            $("input[name='get724_user_name']").removeAttr('disabled');
            $("input[name='get724_merchant_password']").removeAttr('disabled');
            $("input[name='get724_3D_storekey']").removeAttr('disabled');
            $("input[name='get724_classic_url']").removeAttr('disabled');
            $("input[name='get724_3D_url']").removeAttr('disabled');

            $("input[name='posnet_id']").attr('disabled', 'disabled');
            $("input[name='posnet_terminal_id']").attr('disabled', 'disabled');
            $("input[name='posnet_merchant_id']").attr('disabled', 'disabled');
            $("input[name='posnet_user_name']").attr('disabled', 'disabled');
            $("input[name='posnet_provaut_password']").attr('disabled', 'disabled');
            $("input[name='posnet_classic_url']").attr('disabled', 'disabled');
            $("input[name='posnet_3D_url']").attr('disabled', 'disabled');
        }
        if (type == 'posnet') {
            $("input[name='nestpay_client_id']").attr('disabled', 'disabled');
            $("input[name='nestpay_classic_name']").attr('disabled', 'disabled');
            $("input[name='nestpay_classic_password']").attr('disabled', 'disabled');
            $("input[name='nestpay_3D_storekey']").attr('disabled', 'disabled');
            $("input[name='nestpay_classic_url']").attr('disabled', 'disabled');
            $("input[name='nestpay_3D_url']").attr('disabled', 'disabled');

            $("input[name='gvp_terminal_id']").attr('disabled', 'disabled');
            $("input[name='gvp_merchant_id']").attr('disabled', 'disabled');
            $("input[name='gvp_user_name']").attr('disabled', 'disabled');
            $("input[name='gvp_provaut_password']").attr('disabled', 'disabled');
            $("input[name='gvp_3D_storekey']").attr('disabled', 'disabled');
            $("input[name='gvp_classic_url']").attr('disabled', 'disabled');
            $("input[name='gvp_3D_url']").attr('disabled', 'disabled');

            $("input[name='get724_merchant_id']").attr('disabled', 'disabled');
            $("input[name='get724_user_name']").attr('disabled', 'disabled');
            $("input[name='get724_merchant_password']").attr('disabled', 'disabled');
            $("input[name='get724_3D_storekey']").attr('disabled', 'disabled');
            $("input[name='get724_classic_url']").attr('disabled', 'disabled');
            $("input[name='get724_3D_url']").attr('disabled', 'disabled');

            $("input[name='posnet_id']").removeAttr('disabled');
            $("input[name='posnet_terminal_id']").removeAttr('disabled');
            $("input[name='posnet_merchant_id']").removeAttr('disabled');
            $("input[name='posnet_user_name']").removeAttr('disabled');
            $("input[name='posnet_provaut_password']").removeAttr('disabled');
            $("input[name='posnet_classic_url']").removeAttr('disabled');
            $("input[name='posnet_3D_url']").removeAttr('disabled');
        }
    }

</script>