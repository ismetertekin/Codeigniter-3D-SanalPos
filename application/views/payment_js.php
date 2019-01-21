<script>
    $(document).ready(function () {
        var card = new Card({
            form: document.querySelector('form[id=form-credit-card]'),
            container: '.card-wrapper',
            placeholders: {
                number: '•••• •••• •••• ••••',
                name: 'AD SOYAD',
                expiry: '••/••••',
                cvc: '•••'
            }
        });
        $('.jp-card-shiny').attr('style', 'display: none;');
    });

    $(document).on('click', '.instalment_select', function (event) {
        event.preventDefault();
        var value = $(this).data('count-number');
        $('.check_' + value).iCheck('check');
        $('.instalment_select').attr('style', 'margin-bottom: 15px; cursor: pointer; background-color: #fff !important; padding: 5px; color: #666; border: solid 1px #e6e6e6;');
        $(this).attr('style', 'margin-bottom: 15px; cursor: pointer; background-color: #eee !important; border-top-color: #f8ac59; padding: 5px; color: #666; border: solid 1px #e6e6e6;');
    });

    $(".get_bank").keyup(function () {
        var value = $(this).val();
        value = value.replace(" ", "");
        var price = $('input[name=amount]').val();
        if (value.length >= 6 && price > 0) {
            value = value.substring(0, 6);
            $.ajax({
                url: "<?php echo base_url('payment/bin_bank');?>",
                type: "POST",
                dataType: "JSON",
                data: {
                    bin_number: value,
                    total_price: price
                },
                beforeSend: function () {
                    $(".div_instalment").before(' <i class="fa fa-circle-o-notch fa-spin"></i>');
                    $('div[id=bank_logo]').html('');
                    $('div[id=bank_logo]').attr('style', '');
                },
                complete: function () {
                    $('.fa-spin').remove();
                },
                success: function (data) {
                    if (data) {
                        $('.div_instalment').html(data['instalments']);
                        $('input[name=selected_bank_id]').val(data['bank_id']);
                        $('input[name=card_type]').val(data['card_type']);
                        if (data['image'] !== '' && data['image'] !== null && data['image'] !== undefined) {
                            $('div[id=bank_logo]').html('<img src="' + data['image'] + '" width="160px" height="60px">');
                            $('div[id=bank_logo]').attr('style', 'top: 6%; opacity: 1; right: 58%; box-shadow: none; width: 120px;');
                        } else {
                            $('div[id=bank_logo]').html('');
                        }
                        $('.i-checks').iCheck({
                            radioClass: 'iradio_square-green',
                        }).on('ifChanged', function (e) {
                            if (e.target.name === "installment") {
                                $("input[name='installment']:checked").each(function (index, elem) {
                                    var instalment_count = $(this).val();
                                    var new_total = $(this).data('total');
                                    $('input[name=new_total_price]').val(new_total);
                                    $('input[name=instalment_count]').val(instalment_count);
                                });
                            }
                        });
                    }
                }
            });
        } else {
            $('.div_instalment').html('');
            $('div[id=bank_logo]').html('');
            $('div[id=bank_logo]').attr('style', '');
        }
    });

    $(document).on('click', 'button[name=save_payment]', function (event) {
        event.preventDefault();
        if ($('form[name=form-credit-card]')[0].checkValidity()) {
            $.ajax({
                url: "<?php echo base_url('payment/pay');?>",
                type: "POST",
                dataType: "JSON",
                data: $("#form-credit-card").serialize(),
                success: function (data) {
                    if (data) {
                        if (data['status'] && data['status'] == 'status') {
                            toastr.error(data['error_message'], "Üzgünüm!");
                        } else {
                            $('body').append(data['form']);
                            $('#webpos_form').submit();
                        }
                    }
                }
            });
        } else {
            toastr.error("Kredi Kartı Bilgilerini Doldurunuz!", "Üzgünüm!");
        }
    });
</script>