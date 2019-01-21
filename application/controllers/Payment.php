<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Payment extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Payment_Model');
    }

    public function index()
    {
        $this->load->view('payment');
        $this->load->view('payment_js');
    }

    public function replaceSpace($string)
    {
        $string = str_replace(" ", "", $string);
        $string = trim($string);
        return $string;
    }

    public function ip_address()
    {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            return $_SERVER['REMOTE_ADDR'];
        }
    }

    public function card_type($type)
    {
        $data = array('visa' => '1', 'visaelectron' => '1', 'mastercard' => '2');
        return $data[$type];
    }

    public function get724_card_type($type)
    {
        $data = array('amex' => '400', 'visa' => '100', 'visaelectron' => '100', 'mastercard' => '200');
        return $data[$type];
    }

    public function card_validate($post)
    {
        $webpos_error = "";
        if (strlen(trim($post['name'])) < 1) {
            $webpos_error .= "- Kart Sahibi Boş Bırakılamaz<br>";
        }
        if ((strlen($this->replaceSpace($post['number'])) < 15) || (strlen($this->replaceSpace($post['number'])) > 16)) {
            $webpos_error .= "- Kart Numarası Geçersiz<br>";
        }
        if (strlen($post['cvc']) != 3) {
            $webpos_error .= "- Güvenlik Kodu 3 Haneli Olmalıdır<br>";
        }
        $today = date("Y-m-d H:i:s");
        $expiry_data = explode(' / ', $post['expiry']);
        $expire_date_year = strlen($expiry_data[1]) == 4 ? $expiry_data[1] : substr(date('Y'), 0, (4 - strlen($expiry_data[1]))) . $expiry_data[1];
        $date = $expire_date_year . "-" . $expiry_data[0] . "-31 00:00:00";
        if ($date < $today) {
            $webpos_error .= "- Kartın Kullanım Süresi Dolmuş<br>";
        }
        if (!empty($webpos_error)) {
            $webpos_error = "Lütfen Aşağıdaki Hataları Düzeltiniz:" . "<br>" . $webpos_error;
        }
        return $webpos_error;
    }

    public function bin_bank()
    {
        if ($this->input->is_ajax_request()) {
            $bin = $this->Payment_Model->bin_bank($this->input->post('bin_number'));
            $result = array();
            if (!empty($bin)) {
                $bank = $this->Payment_Model->bank('', $bin->bank_code, '1');
                if (!empty($bank)) {
                    $output['bank_id'] = $bank->bank_id;
                    $output['image'] = !empty($bank->image) ? $bank->image : '';
                    $bank_info = json_decode($bank->info);
                    $instalments = explode(";", $bank_info->instalment);
                    foreach ($instalments as $instalment) {
                        $ins = explode("=", $instalment);
                        $data = array(
                            'count' => $ins[0],
                            'monthly' => round(($ins[1] > 0 ? ($this->input->post('total_price') + (($this->input->post('total_price') * $ins[1]) / 100)) : $this->input->post('total_price')) / ($ins[0] > 0 ? $ins[0] : 1), 2),
                            'total' => round((($ins[1] > 0 ? ($this->input->post('total_price') + (($this->input->post('total_price') * $ins[1]) / 100)) : $this->input->post('total_price')) / ($ins[0] > 0 ? $ins[0] : 1)) * ($ins[0] > 0 ? $ins[0] : 1), 2)
                        );
                        array_push($result, $data);
                    }
                } else {
                    $output['image'] = '';
                    $output['bank_id'] = $this->Payment_Model->default_bank()->bank_id;
                    $data = array('count' => 0, 'monthly' => $this->input->post('total_price'), 'total' => $this->input->post('total_price'));
                    array_push($result, $data);
                }
            } else {
                $output['image'] = '';
                $output['bank_id'] = $this->Payment_Model->default_bank()->bank_id;
                $data = array('count' => 0, 'monthly' => $this->input->post('total_price'), 'total' => $this->input->post('total_price'));
                array_push($result, $data);
            }
            $instalments_data['instalments'] = $result;
            $output['instalments'] = $this->load->view('instalment', $instalments_data, true);
            $this->output->set_content_type('application/json')->set_output(json_encode($output));
        }
    }

    public function pay()
    {
        if ($this->input->is_ajax_request()) {
            $result = array();
            $validate = $this->card_validate($this->input->post());
            if (!empty($validate)) {
                $result['status'] = 'status';
                $result['error_message'] = $validate;
            } else {
                $bank = $this->Payment_Model->bank($this->input->post('selected_bank_id'));
                if (!empty($bank)) {
                    if ($bank->method == 'nestpay') {
                        $result = $this->nestpay($bank, $this->input->post());
                    }
                    if ($bank->method == 'gvp') {
                        $result = $this->gvp($bank, $this->input->post());
                    }
                    if ($bank->method == 'get724') {
                        $result = $this->get724($bank, $this->input->post());
                    }
                    if ($bank->method == 'posnet') {
                        $result = $this->posnet($bank, $this->input->post());
                    }
                }
            }
            $this->output->set_content_type('application/json')->set_output(json_encode($result));
        }
    }

    public function callback()
    {
        if ($this->input->post()) {
            $bank = $this->Payment_Model->bank($this->session->flashdata('bank_id'));
            if (!empty($bank)) {
                if ($bank->method == 'nestpay') {
                    $this->nestpay_callback($bank, $this->input->post());
                }
                if ($bank->method == 'gvp') {
                    $this->gvp_callback($bank, $this->input->post());
                }
                if ($bank->method == 'get724') {
                    $this->get724_callback($bank, $this->input->post());
                }
                if ($bank->method == 'posnet') {
                    $this->posnet_callback($bank, $this->input->post());
                }
            }
        } else {
            redirect(base_url());
        }
    }

    public function nestpay($bank, $post)
    {
        $this->load->library('Nestpay');
        $bank_info = json_decode($bank->info);
        $webpos_bank = array();
        $webpos_bank['nestpay_client_id'] = $bank_info->nestpay_client_id;
        $webpos_bank['nestpay_3D_storekey'] = $bank_info->nestpay_3D_storekey;
        $webpos_bank['nestpay_3D_url'] = $bank_info->nestpay_3D_url;
        $expiry_data = explode(' / ', $post['expiry']);
        $webpos_bank['cc_owner'] = $post['name'];
        $webpos_bank['cc_number'] = $this->replaceSpace($post['number']);
        $webpos_bank['cc_cvv2'] = $post['cvc'];
        $webpos_bank['cc_expire_date_month'] = $expiry_data[0];
        $webpos_bank['cc_expire_date_year'] = strlen($expiry_data[1]) > 2 ? substr($expiry_data[1], strlen($expiry_data[1]) - 2, 2) : $expiry_data[1];
        $webpos_bank['cc_type'] = $this->card_type($this->input->post('card_type'));
        $webpos_bank['success_url'] = base_url('payment/callback');
        $webpos_bank['fail_url'] = base_url('payment/callback');
        $webpos_bank['order_id'] = '';
        $webpos_bank['total'] = $post['new_total_price'];
        $this->session->set_flashdata('amount', $post['new_total_price']);
        if (!empty($post['instalment_count'])) {
            $this->session->set_flashdata('instalment', $post['instalment_count']);
        } else {
            $this->session->set_flashdata('instalment', "");
        }
        $this->session->set_flashdata('cv2', $post['cvc']);
        $this->session->set_flashdata('bank_id', $bank->bank_id);
        $method_response = $this->nestpay->methodResponse($webpos_bank);
        $post_form['form'] = $method_response['form'];
        return $post_form;
    }

    public function nestpay_callback($bank, $post)
    {
        $this->load->library('Nestpay');
        $bank_info = json_decode($bank->info);
        $instalment = $this->session->flashdata('instalment');
        $this->session->set_flashdata('bank_id', $bank->bank_id);
        $this->session->set_flashdata('bank_reference', $post['oid']);
        $this->session->set_flashdata('instalment', $instalment);
        $selected_bank = array(
            'name' => $bank->name,
            'nestpay_client_id' => $bank_info->nestpay_client_id,
            'nestpay_classic_name' => $bank_info->nestpay_classic_name,
            'nestpay_classic_password' => $bank_info->nestpay_classic_password,
            'nestpay_3D_storekey' => $bank_info->nestpay_3D_storekey,
            'nestpay_classic_url' => $bank_info->nestpay_classic_url,
            'nestpay_3D_url' => $bank_info->nestpay_3D_url,
            'instalment' => $instalment,
            'cv2' => $this->session->flashdata('cv2')
        );
        $method_response = $this->nestpay->bankResponse($post, $selected_bank);
        $this->session->set_flashdata('bank_message', $method_response['message']);
        if ($method_response['result'] == 1) {
            redirect(base_url('payment/success'));
        } else {
            redirect(base_url('payment/failure'));
        }
    }

    public function gvp($bank, $post)
    {
        $this->load->library('Gvp');
        $bank_info = json_decode($bank->info);
        $webpos_bank = array();
        $webpos_bank['gvp_terminal_id'] = $bank_info->gvp_terminal_id;
        $webpos_bank['gvp_merchant_id'] = $bank_info->gvp_merchant_id;
        $webpos_bank['gvp_user_name'] = $bank_info->gvp_user_name;
        $webpos_bank['gvp_provaut_password'] = $bank_info->gvp_provaut_password;
        $webpos_bank['gvp_3D_storekey'] = $bank_info->gvp_3D_storekey;
        $webpos_bank['gvp_classic_url'] = $bank_info->gvp_classic_url;
        $webpos_bank['gvp_3D_url'] = $bank_info->gvp_3D_url;
        $expiry_data = explode(' / ', $post['expiry']);
        $webpos_bank['cc_owner'] = $post['name'];
        $webpos_bank['cc_number'] = $this->replaceSpace($post['number']);
        $webpos_bank['cc_cvv2'] = $post['cvc'];
        $webpos_bank['cc_expire_date_month'] = $expiry_data[0];
        $webpos_bank['cc_expire_date_year'] = strlen($expiry_data[1]) > 2 ? substr($expiry_data[1], strlen($expiry_data[1]) - 2, 2) : $expiry_data[1];
        $webpos_bank['cc_type'] = $this->card_type($this->input->post('card_type'));
        $webpos_bank['success_url'] = base_url('payment/callback');
        $webpos_bank['fail_url'] = base_url('payment/callback');
        $webpos_bank['customer_ip'] = $this->ip_address();
        $webpos_bank['order_id'] = substr(md5(mt_rand()), 0, 10);
        $webpos_bank['total'] = $post['new_total_price'];
        $this->session->set_flashdata('amount', $post['new_total_price']);
        if (!empty($post['instalment_count'])) {
            $this->session->set_flashdata('instalment', $post['instalment_count']);
            $webpos_bank['instalment'] = $post['instalment_count'];
        } else {
            $this->session->set_flashdata('instalment', "");
            $webpos_bank['instalment'] = 0;
        }
        $this->session->set_flashdata('cv2', $post['cvc']);
        $this->session->set_flashdata('bank_id', $bank->bank_id);
        $method_response = $this->gvp->methodResponse($webpos_bank);
        $post_form['form'] = $method_response['form'];
        return $post_form;
    }

    public function gvp_callback($bank, $post)
    {
        $this->load->library('Gvp');
        $bank_info = json_decode($bank->info);
        $instalment = $this->session->flashdata('instalment');
        $this->session->set_flashdata('bank_id', $bank->bank_id);
        $this->session->set_flashdata('bank_reference', $post['orderid']);
        $this->session->set_flashdata('instalment', $instalment);
        $selected_bank = array(
            'name' => $bank->name,
            'gvp_terminal_id' => $bank_info->gvp_terminal_id,
            'gvp_merchant_id' => $bank_info->gvp_merchant_id,
            'gvp_user_name' => $bank_info->gvp_user_name,
            'gvp_provaut_password' => $bank_info->gvp_provaut_password,
            'gvp_3D_storekey' => $bank_info->gvp_3D_storekey,
            'gvp_classic_url' => $bank_info->gvp_classic_url,
            'gvp_3D_url' => $bank_info->gvp_classic_url,
            'instalment' => $instalment,
            'cv2' => $this->session->flashdata('cv2')
        );
        $method_response = $this->gvp->bankResponse($post, $selected_bank);
        $this->session->set_flashdata('bank_message', $method_response['message']);
        if ($method_response['result'] == 1) {
            redirect(base_url('payment/success'));
        } else {
            redirect(base_url('payment/failure'));
        }
    }

    public function get724($bank, $post)
    {
        $this->load->library('Get724');
        $bank_info = json_decode($bank->info);
        $webpos_bank = array();
        $webpos_bank['get724_merchant_id'] = $bank_info->get724_merchant_id;
        $webpos_bank['get724_user_name'] = $bank_info->get724_user_name;
        $webpos_bank['get724_merchant_password'] = $bank_info->get724_merchant_password;
        $webpos_bank['get724_3D_storekey'] = $bank_info->get724_3D_storekey;
        $webpos_bank['get724_classic_url'] = $bank_info->get724_classic_url;
        $webpos_bank['get724_3D_url'] = $bank_info->get724_3D_url;
        $expiry_data = explode(' / ', $post['expiry']);
        $webpos_bank['cc_owner'] = $post['name'];
        $webpos_bank['cc_number'] = $this->replaceSpace($post['number']);
        $webpos_bank['cc_cvv2'] = $post['cvc'];
        $webpos_bank['cc_expire_date_month'] = $expiry_data[0];
        $webpos_bank['cc_expire_date_year'] = strlen($expiry_data[1]) > 2 ? substr($expiry_data[1], strlen($expiry_data[1]) - 2, 2) : $expiry_data[1];
        $webpos_bank['cc_type'] = $this->get724_card_type($this->input->post('card_type'));
        $webpos_bank['success_url'] = base_url('payment/callback');
        $webpos_bank['fail_url'] = base_url('payment/callback');
        $webpos_bank['customer_ip'] = $this->ip_address();
        $order_id = substr(md5(mt_rand()), 0, 10);
        $webpos_bank['order_id'] = $order_id;
        $webpos_bank['total'] = number_format($post['new_total_price'], 2);
        $this->session->set_flashdata('amount', number_format($post['new_total_price'], 2));
        if (!empty($post['instalment_count'])) {
            $this->session->set_flashdata('instalment', $post['instalment_count']);
            $webpos_bank['instalment'] = $post['instalment_count'];
        } else {
            $this->session->set_flashdata('instalment', "");
            $webpos_bank['instalment'] = 0;
        }
        $this->session->set_flashdata('expire_date', $expiry_data[1] . $expiry_data[0]);
        $this->session->set_flashdata('cv2', $post['cvc']);
        $this->session->set_flashdata('customer_ip', $this->ip_address());
        $this->session->set_flashdata('order_id', $order_id);
        $this->session->set_flashdata('card_holders_name', $post['name']);
        $this->session->set_flashdata('bank_id', $bank->bank_id);
        $method_response = $this->get724->methodResponse($webpos_bank);
        return $method_response;
    }

    public function get724_callback($bank, $post)
    {
        $this->load->library('Get724');
        $bank_info = json_decode($bank->info);
        $selected_bank = array(
            'name' => $bank->name,
            'get724_merchant_password' => $bank_info->get724_merchant_password,
            'get724_user_name' => $bank_info->get724_user_name,
            'get724_classic_url' => $bank_info->get724_classic_url,
            'amount' => $this->session->flashdata('amount'),
            'expire_date' => $this->session->flashdata('expire_date'),
            'cv2' => $this->session->flashdata('cv2'),
            'instalment' => $this->session->flashdata('instalment'),
            'order_id' => $this->session->flashdata('order_id'),
            'customer_ip' => $this->session->flashdata('customer_ip'),
            'card_holders_name' => $this->session->flashdata('card_holders_name')
        );
        $method_response = $this->get724->bankResponse($post, $selected_bank);
        $this->session->set_flashdata('bank_message', $method_response['message']);
        if ($method_response['result'] == 1) {
            redirect(base_url('payment/success'));
        } else {
            redirect(base_url('payment/failure'));
        }
    }

    public function posnet($bank, $post)
    {
        $this->load->library('Posnet');
        $bank_info = json_decode($bank->info);
        $webpos_bank = array();
        $webpos_bank['posnet_id'] = $bank_info->posnet_id;
        $webpos_bank['posnet_terminal_id'] = $bank_info->posnet_terminal_id;
        $webpos_bank['posnet_merchant_id'] = $bank_info->posnet_merchant_id;
        $webpos_bank['posnet_user_name'] = $bank_info->posnet_user_name;
        $webpos_bank['posnet_provaut_password'] = $bank_info->posnet_provaut_password;
        $webpos_bank['posnet_classic_url'] = $bank_info->posnet_classic_url;
        $webpos_bank['posnet_3D_url'] = $bank_info->posnet_3D_url;
        $expiry_data = explode(' / ', $post['expiry']);
        $webpos_bank['cc_owner'] = $post['name'];
        $webpos_bank['cc_number'] = $this->replaceSpace($post['number']);
        $webpos_bank['cc_cvv2'] = $post['cvc'];
        $webpos_bank['cc_expire_date_month'] = $expiry_data[0];
        $webpos_bank['cc_expire_date_year'] = strlen($expiry_data[1]) > 2 ? substr($expiry_data[1], strlen($expiry_data[1]) - 2, 2) : $expiry_data[1];
        $webpos_bank['cc_type'] = $this->card_type($this->input->post('card_type'));
        $webpos_bank['success_url'] = base_url('payment/callback');
        $webpos_bank['fail_url'] = base_url('payment/callback');
        $webpos_bank['customer_ip'] = $this->ip_address();
        $webpos_bank['order_id'] = substr(md5(mt_rand()), 0, 10);
        $webpos_bank['total'] = $post['new_total_price'];
        $this->session->set_flashdata('amount', $post['new_total_price']);
        if (!empty($post['instalment_count'])) {
            $this->session->set_flashdata('instalment', $post['instalment_count']);
            $webpos_bank['instalment'] = $post['instalment_count'];
        } else {
            $this->session->set_flashdata('instalment', "");
            $webpos_bank['instalment'] = 0;
        }
        $this->session->set_flashdata('cv2', $post['cvc']);
        $this->session->set_flashdata('bank_id', $bank->bank_id);
        $method_response = $this->posnet->methodResponse($webpos_bank);
        $post_form['form'] = $method_response['form'];
        return $post_form;
    }

    public function posnet_callback($bank, $post)
    {
        $this->load->library('Posnet');
        $bank_info = json_decode($bank->info);
        $instalment = $this->session->flashdata('instalment');
        $this->session->set_flashdata('bank_id', $bank->bank_id);
        $this->session->set_flashdata('bank_reference', $post['orderid']);
        $this->session->set_flashdata('instalment', $instalment);
        $selected_bank = array(
            'name' => $bank->name,
            'posnet_id' => $bank_info->posnet_id,
            'posnet_terminal_id' => $bank_info->posnet_terminal_id,
            'posnet_merchant_id' => $bank_info->posnet_merchant_id,
            'posnet_user_name' => $bank_info->posnet_user_name,
            'posnet_provaut_password' => $bank_info->posnet_provaut_password,
            'posnet_classic_url' => $bank_info->posnet_classic_url,
            'posnet_3D_url' => $bank_info->posnet_classic_url,
            'instalment' => $instalment,
            'cv2' => $this->session->flashdata('cv2')
        );
        $method_response = $this->posnet->bankResponse($post, $selected_bank);
        $this->session->set_flashdata('bank_message', $method_response['message']);
        if ($method_response['result'] == 1) {
            redirect(base_url('payment/success'));
        } else {
            redirect(base_url('payment/failure'));
        }
    }

    public function success()
    {
        $data['message'] = $this->session->flashdata('bank_message');
        $this->load->view('success', $data);
    }

    public function failure()
    {
        $data['message'] = $this->session->flashdata('bank_message');
        $this->load->view('failure', $data);
    }
}
