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
                        <div class="container">
                            <div class="row">
                                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 content-offset">
                                    <div id="content">
                                        <div style="padding: 12px 18px; margin-bottom: 20px; margin-top: 20px;">
                                            <h1 style="text-align: center; margin-bottom: 6px; color: #678361;">Tebrikler!</h1>
                                            <span style="display: block; text-align: center; margin-bottom: 10px;"><img src="<?php echo base_url('assets/img/check.png'); ?>" alt="checkmark"/></span>
                                            <p style="text-align: center; font-weight: 700;">Ödeme işleminiz başarıyla gerçekleştirildi.</p>
                                            <div class="group-button" style="padding-bottom: 0px; margin-bottom: 0px; margin-top: 15px; text-align: center;">
                                                <a style="background-color: #f29f29; font-size: 13px; color: #fff; font-weight: 600; line-height: 40px; padding: 0 40px; display: inline-block; text-transform: uppercase; border-radius: 3px;" href="<?php echo base_url(); ?>" class="button">YENİ ÖDEME YAP</a>
                                            </div>
                                            <?php if (!empty($message)) { ?>
                                                <p style="text-align: center;">
                                                    <?php echo $message; ?>
                                                </p>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
