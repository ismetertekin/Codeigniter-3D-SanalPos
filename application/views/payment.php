<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="author" content="İsmet ERTEKİN"/>
    <title>Online Ödeme</title>
    <link href="<?php echo base_url('assets/css/bootstrap.min.css'); ?>" rel="stylesheet" type="text/css">
    <link href="<?php echo base_url('assets/css/icons/icomoon/styles.css'); ?>" rel="stylesheet" type="text/css">
    <link href="<?php echo base_url('assets/css/toastr/toastr.min.css'); ?>" rel="stylesheet">
    <link href="<?php echo base_url('assets/css/icheck/custom.css'); ?>" rel="stylesheet">
    <script src="<?php echo base_url('assets/js/jquery.min.js'); ?>"></script>
    <script src="<?php echo base_url('assets/js/bootstrap.bundle.min.js'); ?>"></script>
    <script src="<?php echo base_url('assets/credit-card/dist/card.js'); ?>"></script>
    <script src="<?php echo base_url('assets/js/toastr/toastr.min.js'); ?>"></script>
    <script src="<?php echo base_url('assets/js/icheck/icheck.min.js'); ?>"></script>
</head>

<body class="navbar-top">
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <a class="navbar-brand" href="<?php echo base_url(); ?>">Sanal Pos</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav mr-auto">
            <li class="nav-item active">
                <a class="nav-link" href="#">Ödeme</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?php echo base_url('banks'); ?>">Banka Tanımlama</a>
            </li>
        </ul>
    </div>
</nav>
<div class="page-content">
    <div class="content-wrapper">
        <div class="content">
            <div class="card">
                <div class="card-header header-elements-inline">
                    <h5 class="card-title">Ödeme</h5>
                </div>
                <div class="card-body" style="padding-left: 2.25rem;">
                    <div class="row">
                        <div class="col-sm-6">
                            <form role="form" id="form-credit-card" name="form-credit-card" method="post" action="<?php echo base_url('payment/pay'); ?>">
                                <input type="hidden" name="selected_bank_id">
                                <input type="hidden" name="instalment_count">
                                <input type="hidden" name="new_total_price">
                                <input type="hidden" name="card_type" id="card_type">
                                <div class="row mb-3">
                                    <label style="text-transform: uppercase;">TUTAR</label>
                                    <input type="number" class="form-control get_bank" placeholder="ÖDEME YAPILACAK TUTAR" name="amount" autocomplete="off" required/>
                                </div>
                                <div class="row mb-3">
                                    <label>KART NUMARASI</label>
                                    <input type="tel" class="form-control credit-card-number get_bank" name="number" placeholder="GEÇERLİ KART NUMARASI" autocomplete="off" required/>
                                </div>
                                <div class="row mb-3">
                                    <label>KART ÜZERİNDEKİ İSİM</label>
                                    <input type="text" class="form-control" name="name" placeholder="ADINIZ VE SOYADINIZ" autocomplete="off" required/>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-xs-7 col-md-7 pl-0">
                                        <label>SON KULLANMA TARİHİ</label>
                                        <input type="tel" class="form-control" name="expiry" placeholder="AA/YYYY" autocomplete="off" required/>
                                    </div>
                                    <div class="col-xs-5 col-md-5 pl-0">
                                        <label>CVC KODU</label>
                                        <input type="number" class="form-control" name="cvc" placeholder="CVC" autocomplete="off" required/>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="col-sm-6" style="margin-top: 15px;">
                            <div class="card-wrapper"></div>
                        </div>
                    </div>
                    <div class="div_instalment mt-5"></div>
                    <div class="row mt-5">
                        <button class="button submit" name="save_payment">ÖDEME YAP</button>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
</body>
<?php if (!empty($msg)) { ?>
    <?php if ($msg_type == "success") { ?>
        <script>
            $(document).ready(function () {
                toastr.info("<?php echo $msg; ?>", "Tebrikler!")
            });
        </script>
    <?php } else { ?>
        <script>
            $(document).ready(function () {
                toastr.error("<?php echo $msg; ?>", "Üzgünüm!");
            });
        </script>
    <?php } ?>
<?php } ?>
</html>