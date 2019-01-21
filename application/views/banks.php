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
    <script src="<?php echo base_url('assets/js/jquery.min.js'); ?>"></script>
    <script src="<?php echo base_url('assets/js/bootstrap.bundle.min.js'); ?>"></script>
    <script src="<?php echo base_url('assets/credit-card/dist/card.js'); ?>"></script>
    <script src="<?php echo base_url('assets/js/toastr/toastr.min.js'); ?>"></script>
</head>

<body class="navbar-top">
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <a class="navbar-brand" href="<?php echo base_url(); ?>">Sanal Pos</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav mr-auto">
            <li class="nav-item">
                <a class="nav-link" href="<?php echo base_url(); ?>">Ödeme</a>
            </li>
            <li class="nav-item active">
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
                    <h5 class="card-title">Bankalar</h5>
                    <a href="#" class="breadcrumb-elements-item" data-toggle="modal" data-target="#bank_modal"><i class="icon-plus3 mr-2"></i>Yeni Banka Tanımla</a>
                </div>
                <div class="card-body" style="padding: 0.0rem;"></div>
                <table class="table">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Banka Adı</th>
                        <th>Yöntem</th>
                        <th>Model</th>
                        <th>Durum</th>
                        <th>İşlemler</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($banks as $bank) { ?>
                        <tr>
                            <td><?php echo $bank->bank_id; ?></td>
                            <td><a href="#" class="bank_detail" data-bank-id="<?php echo $bank->bank_id; ?>"><?php echo $bank->name; ?></a></td>
                            <td><?php echo $bank->method; ?></td>
                            <td><?php echo $bank->model; ?></td>
                            <td><?php echo $bank->status == 1 ? "Aktif" : "Pasif"; ?></td>
                            <td>
                                <div class="list-icons">
                                    <a href="#" class="list-icons-item bank_settings" data-bank-id="<?php echo $bank->bank_id; ?>"><i class="icon-cog6"></i></a>
                                    <a href="#" class="list-icons-item bank_detail" data-bank-id="<?php echo $bank->bank_id; ?>"><i class="icon-pencil7"></i></a>
                                    <a href="#" class="list-icons-item delete_bank" data-bank-id="<?php echo $bank->bank_id; ?>"><i class="icon-trash"></i></a>
                                </div>
                            </td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div id="bank_modal" class="modal fade" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="icon-plus3 mr-2"></i> &nbsp;Yeni Banka Ekle</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <form id="bank_form" name="bank_form" method="post" action="<?php echo base_url('banks/save') ?>">
                        <div class="modal-body">
                            <div class="form-group">
                                <div class="row">
                                    <input type="text" name="bank_id" hidden="hidden">
                                    <div class="col-md-12 mt-2">
                                        <label class="d-block font-weight-semibold"><span class="text-danger">*</span> Banka Adı</label>
                                        <input type="text" class="form-control" name="name" required="required">
                                    </div>
                                    <div class="col-md-12 mt-2">
                                        <label class="d-block font-weight-semibold"><span class="text-danger">*</span> Yöntem</label>
                                        <select name="method" class="form-control" required="required">
                                            <option value="">Seçiniz...</option>
                                            <option value="nestpay">Nestpay</option>
                                            <option value="gvp">Gvp</option>
                                            <option value="get724">Get 7/24</option>
                                            <option value="posnet">Posnet</option>
                                        </select>
                                    </div>
                                    <div class="col-md-12 mt-2">
                                        <label class="d-block font-weight-semibold"><span class="text-danger">*</span> Model</label>
                                        <select name="model" class="form-control" required="required">
                                            <option value="">Seçiniz...</option>
                                            <option value="3d_model">3D Model</option>
                                        </select>
                                    </div>
                                    <div class="col-md-12 mt-2">
                                        <label class="d-block font-weight-semibold"><span class="text-danger">*</span> Banka Adı</label>
                                        <select name="bank_code" class="form-control" required="required">
                                            <option value="">Seçiniz...</option>
                                            <?php foreach ($bin_banks as $bin_bank) { ?>
                                                <option value="<?php echo $bin_bank->bank_code; ?>"><?php echo $bin_bank->bank_name; ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                    <div class="col-md-12 mt-2">
                                        <label class="d-block font-weight-semibold"><span class="text-danger">*</span> Varsayılan Banka</label>
                                        <select name="default_bank" class="form-control" required="required">
                                            <option value="0">Hayır</option>
                                            <option value="1">Evet</option>
                                        </select>
                                    </div>
                                    <div class="col-md-12 mt-2">
                                        <label class="d-block font-weight-semibold"><span class="text-danger">*</span> Durumu</label>
                                        <select name="status" class="form-control" required="required">
                                            <option value="1">Aktif</option>
                                            <option value="0">Pasif</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn bg-primary">Kaydet</button>
                            <button type="button" class="btn btn-link" data-dismiss="modal">Kapat</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div id="bank_settings_modal" class="modal fade" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title info_title"><i class="icon-plus3 mr-2"></i></h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <form id="bank_detail_form" name="bank_detail_form" method="post" action="<?php echo base_url('banks/edit_info') ?>">
                        <div class="modal-body">
                            <input type="text" name="bank_id" hidden="hidden">
                            <div class="form-group">
                                <div id="nestpay" class="row" hidden="hidden">
                                    <div class="col-md-12 mt-2">
                                        <label class="d-block font-weight-semibold"><span class="text-danger">*</span>Müşteri No (Client ID):</label>
                                        <input type="text" class="form-control" name="nestpay_client_id" required="required">
                                    </div>
                                    <div class="col-md-12 mt-2">
                                        <label class="d-block font-weight-semibold"><span class="text-danger">*</span>Müşteri Adı (Client-Api Name):</label>
                                        <input type="text" class="form-control" name="nestpay_classic_name" required="required">
                                    </div>
                                    <div class="col-md-12 mt-2">
                                        <label class="d-block font-weight-semibold"><span class="text-danger">*</span>Şifre (Classic-Api Password):</label>
                                        <input type="text" class="form-control" name="nestpay_classic_password" required="required">
                                    </div>
                                    <div class="col-md-12 mt-2">
                                        <label class="d-block font-weight-semibold"><span class="text-danger">*</span>3D Şifresi (3D Store Key,Merchant Key):</label>
                                        <input type="text" class="form-control" name="nestpay_3D_storekey" required="required">
                                    </div>
                                    <div class="col-md-12 mt-2">
                                        <label class="d-block font-weight-semibold"><span class="text-danger">*</span>Doğrudan Ödeme Bağlantısı (Api-Classic URL):</label>
                                        <input type="text" class="form-control" name="nestpay_classic_url" required="required">
                                    </div>
                                    <div class="col-md-12 mt-2">
                                        <label class="d-block font-weight-semibold"><span class="text-danger">*</span>3D Bağlantısı (3D,3DGate URL):</label>
                                        <input type="text" class="form-control" name="nestpay_3D_url" required="required">
                                    </div>
                                </div>
                                <div id="gvp" class="row" hidden="hidden">
                                    <div class="col-md-12 mt-2">
                                        <label class="d-block font-weight-semibold"><span class="text-danger">*</span>Terminal ID (TID):</label>
                                        <input type="text" class="form-control" name="gvp_terminal_id" required="required">
                                    </div>
                                    <div class="col-md-12 mt-2">
                                        <label class="d-block font-weight-semibold"><span class="text-danger">*</span>Merchant ID (MID):</label>
                                        <input type="text" class="form-control" name="gvp_merchant_id" required="required">
                                    </div>
                                    <div class="col-md-12 mt-2">
                                        <label class="d-block font-weight-semibold"><span class="text-danger">*</span>Müşteri Adı (Kullanıcı Adı, User Name):</label>
                                        <input type="text" class="form-control" name="gvp_user_name" required="required">
                                    </div>
                                    <div class="col-md-12 mt-2">
                                        <label class="d-block font-weight-semibold"><span class="text-danger">*</span>Provizyon Şifresi (Provaut Password):</label>
                                        <input type="text" class="form-control" name="gvp_provaut_password" required="required">
                                    </div>
                                    <div class="col-md-12 mt-2">
                                        <label class="d-block font-weight-semibold"><span class="text-danger">*</span>3D Şifresi (3D Store Key, Merchant Key):</label>
                                        <input type="text" class="form-control" name="gvp_3D_storekey" required="required">
                                    </div>
                                    <div class="col-md-12 mt-2">
                                        <label class="d-block font-weight-semibold"><span class="text-danger">*</span>Doğrudan Ödeme Bağlantısı (Api-Classic URL):</label>
                                        <input type="text" class="form-control" name="gvp_classic_url" required="required">
                                    </div>
                                    <div class="col-md-12 mt-2">
                                        <label class="d-block font-weight-semibold"><span class="text-danger">*</span>3D Bağlantısı (3D,3DGate URL):</label>
                                        <input type="text" class="form-control" name="gvp_3D_url" required="required">
                                    </div>
                                </div>
                                <div id="get724" class="row" hidden="hidden">
                                    <div class="col-md-12 mt-2">
                                        <label class="d-block font-weight-semibold"><span class="text-danger">*</span>Merchant ID (MID):</label>
                                        <input type="text" class="form-control" name="get724_merchant_id" required="required">
                                    </div>
                                    <div class="col-md-12 mt-2">
                                        <label class="d-block font-weight-semibold"><span class="text-danger">*</span>Müşteri Adı (Kullanıcı Adı, User Name):</label>
                                        <input type="text" class="form-control" name="get724_user_name" required="required">
                                    </div>
                                    <div class="col-md-12 mt-2">
                                        <label class="d-block font-weight-semibold"><span class="text-danger">*</span>Provizyon Şifresi (Provaut Password):</label>
                                        <input type="text" class="form-control" name="get724_merchant_password" required="required">
                                    </div>
                                    <div class="col-md-12 mt-2">
                                        <label class="d-block font-weight-semibold"><span class="text-danger">*</span>3D Şifresi (3D Store Key, Merchant Key):</label>
                                        <input type="text" class="form-control" name="get724_3D_storekey" required="required">
                                    </div>
                                    <div class="col-md-12 mt-2">
                                        <label class="d-block font-weight-semibold"><span class="text-danger">*</span>Doğrudan Ödeme Bağlantısı (Api-Classic URL):</label>
                                        <input type="text" class="form-control" name="get724_classic_url" required="required">
                                    </div>
                                    <div class="col-md-12 mt-2">
                                        <label class="d-block font-weight-semibold"><span class="text-danger">*</span>3D Bağlantısı (3D,3DGate URL):</label>
                                        <input type="text" class="form-control" name="get724_3D_url" required="required">
                                    </div>
                                </div>
                                <div id="posnet" class="row" hidden="hidden">
                                    <div class="col-md-12 mt-2">
                                        <label class="d-block font-weight-semibold"><span class="text-danger">*</span>Posnet ID (Client ID, PID):</label>
                                        <input type="text" class="form-control" name="posnet_id" required="required">
                                    </div>
                                    <div class="col-md-12 mt-2">
                                        <label class="d-block font-weight-semibold"><span class="text-danger">*</span>Terminal ID (TID):</label>
                                        <input type="text" class="form-control" name="posnet_terminal_id" required="required">
                                    </div>
                                    <div class="col-md-12 mt-2">
                                        <label class="d-block font-weight-semibold"><span class="text-danger">*</span>Merchant ID (MID):</label>
                                        <input type="text" class="form-control" name="posnet_merchant_id" required="required">
                                    </div>
                                    <div class="col-md-12 mt-2">
                                        <label class="d-block font-weight-semibold"><span class="text-danger">*</span>Müşteri Adı (Kullanıcı Adı, User Name):</label>
                                        <input type="text" class="form-control" name="posnet_user_name" required="required">
                                    </div>
                                    <div class="col-md-12 mt-2">
                                        <label class="d-block font-weight-semibold"><span class="text-danger">*</span>Provizyon Şifresi (Provaut Password):</label>
                                        <input type="text" class="form-control" name="posnet_provaut_password" required="required">
                                    </div>
                                    <div class="col-md-12 mt-2">
                                        <label class="d-block font-weight-semibold"><span class="text-danger">*</span>Doğrudan Ödeme Bağlantısı (Api-Classic URL):</label>
                                        <input type="text" class="form-control" name="posnet_classic_url" required="required">
                                    </div>
                                    <div class="col-md-12 mt-2">
                                        <label class="d-block font-weight-semibold"><span class="text-danger">*</span>3D Bağlantısı (3D, 3DGate URL):</label>
                                        <input type="text" class="form-control" name="posnet_3D_url" required="required">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="d-block font-weight-semibold">Taksitler <a data-popup="tooltip" title="" data-placement="right" id="right" data-original-title="Lütfen Taksit1=Oran1;Taksit2=Oran2 Şeklinde Giriniz Örnek: 1=2.23;2=2.25;3=0;4=3.10"><i class="icon-question3"></i></a></label>
                                <input type="text" class="form-control" name="instalment">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn bg-primary">Kaydet</button>
                            <button type="button" class="btn btn-link" data-dismiss="modal">Kapat</button>
                        </div>
                    </form>
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