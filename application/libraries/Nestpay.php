<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Nestpay
{
    public function __construct()
    {
    }

    public function createHash($client_id, $store_key, $ok_url, $fail_url, $order_id, $amount, $rnd)
    {
        $hash_str = $client_id . $order_id . $amount . $ok_url . $fail_url . $rnd . $store_key;
        $hash = base64_encode(pack('H*', sha1($hash_str)));
        return $hash;
    }

    public function createForm($bank)
    {
        $rnd = microtime();
        $client_id = $bank['nestpay_client_id'];
        $store_key = $bank['nestpay_3D_storekey'];
        $ok_url = $bank['success_url'];
        $fail_url = $bank['fail_url'];
        $order_id = $bank['order_id'];
        $amount = $bank['total'];
        $hash = $this->createHash($client_id, $store_key, $ok_url, $fail_url, $order_id, $amount, $rnd);
        $inputs = array(
            'pan' => $bank['cc_number'],
            'cv2' => $bank['cc_cvv2'],
            'Ecom_Payment_Card_ExpDate_Year' => $bank['cc_expire_date_year'],
            'Ecom_Payment_Card_ExpDate_Month' => $bank['cc_expire_date_month'],
            'cardType' => $bank['cc_type'],
            'clientid' => $bank['nestpay_client_id'],
            'amount' => $amount,
            'oid' => '',
            'okUrl' => $ok_url,
            'failUrl' => $fail_url,
            'rnd' => $rnd,
            'hash' => $hash,
            'storetype' => "3d",
            'lang' => "tr",
            'currency' => "949"
        );
        $action = $bank['nestpay_3D_url'];
        $form = '<form id="webpos_form" name="webpos_form" method="post" action="' . $action . '">';
        foreach ($inputs as $key => $value) {
            $form .= '<input type="hidden" name="' . $key . '" value="' . $value . '" />';
        }
        $form .= '</form>';
        return $form;
    }

    public function methodResponse($bank)
    {
        $response = array();
        $response['form'] = $this->createForm($bank);
        return $response;
    }

    public function bankResponse($bank_response, $bank)
    {
        $response = array();
        $hashparams = $bank_response["HASHPARAMS"];
        $hashparamsval = $bank_response["HASHPARAMSVAL"];
        $hashparam = $bank_response["HASH"];
        $storekey = $bank['nestpay_3D_storekey'];
        $paramsval = "";
        $index1 = 0;
        $index2 = 0;
        while ($index1 < strlen($hashparams)) {
            $index2 = strpos($hashparams, ":", $index1);
            $vl = $_POST[substr($hashparams, $index1, $index2 - $index1)];
            if ($vl == null)
                $vl = "";
            $paramsval = $paramsval . $vl;
            $index1 = $index2 + 1;
        }
        $hashval = $paramsval . $storekey;
        $hash = base64_encode(pack('H*', sha1($hashval)));

        if ($paramsval != $hashparamsval || $hashparam != $hash) {
            $response['message'] = $bank_response['mdErrorMsg'] . '<h4>Güvenlik Uyarısı. Sayısal İmza Geçersiz !</h4>';
            $response['result'] = 0;
        } else {
            $mdStatus = $bank_response['mdStatus'];
            $mdArray = array('1', '2', '3', '4');
            if (in_array($mdStatus, $mdArray)) {
                $response['message'] = '3D Onayı Başarılı.<br/>';
                $xml_fields = array(
                    'name' => $bank['nestpay_classic_name'],
                    'password' => $bank['nestpay_classic_password'],
                    'clientid' => $bank['nestpay_client_id'],
                    'url' => $bank['nestpay_classic_url'],
                    'mode' => 'P',
                    'type' => 'Auth',
                    'expires' => $bank_response['Ecom_Payment_Card_ExpDate_Month'] . '/' . $bank_response['Ecom_Payment_Card_ExpDate_Year'],
                    'cv2' => $bank['cv2'],
                    'tutar' => $bank_response['amount'],
                    'taksit' => $bank['instalment'],
                    'oid' => $bank_response['oid'],
                    'ip' => $bank_response['clientIp'],
                    'email' => '',
                    'xid' => $bank_response['xid'],
                    'eci' => $bank_response['eci'],
                    'cavv' => $bank_response['cavv'],
                    'md' => $bank_response['md']
                );
                $xml_response = $this->xmlSend($xml_fields);
                $xml = simplexml_load_string($xml_response);

                $Response = isset($xml->Response) ? (string)$xml->Response : '';
                $OrderId = isset($xml->OrderId) ? (string)$xml->OrderId : '';
                $AuthCode = isset($xml->AuthCode) ? (string)$xml->AuthCode : '';
                $ProcReturnCode = isset($xml->ProcReturnCode) ? (string)$xml->ProcReturnCode : '';
                $ErrMsg = isset($xml->ErrMsg) ? (string)$xml->ErrMsg : '';
                $HostRefNum = isset($xml->HostRefNum) ? (string)$xml->HostRefNum : '';
                $TransId = isset($xml->TransId) ? (string)$xml->TransId : '';

                if ($ProcReturnCode == "00" || $Response === "Approved") {
                    $response['result'] = 1;
                    $response['message'] .= 'Ödeme Başarılı<br/>';
                    $response['message'] .= 'OrderId : ' . $OrderId . '<br/>';
                    $response['message'] .= 'Banka Adı : ' . $bank["name"] . '<br/>';
                    if (!empty($bank['instalment'])) {
                        $response['message'] .= 'Taksit : ' . $bank['instalment'] . '<br/>';
                    } else {
                        $response['message'] .= 'Taksit : Yok <br/>';
                    }
                    $response['message'] .= 'AuthCode : ' . $AuthCode . '<br/>';
                    $response['message'] .= 'Response : ' . $Response . '<br/>';
                    $response['message'] .= 'HostRefNum : ' . $HostRefNum . '<br/>';
                    $response['message'] .= 'ProcReturnCode : ' . $ProcReturnCode . '<br/>';
                    $response['message'] .= 'TransId : ' . $TransId . '<br/>';
                    $response['message'] .= 'ErrMsg : ' . $ErrMsg . '<br/>';
                } else {
                    $response['result'] = 0;
                    $response['message'] .= 'Ödeme Başarısız.<br/>';
                    $response['message'] .= 'ErrMsg : ' . $ErrMsg . '<br/>';
                }
            } else {
                $response['result'] = 0;
                $response['message'] = '3D doğrulama başarısız<br/>';
                $response['message'] .= $bank_response['mdErrorMsg'];
            }
        }
        return $response;
    }

    public function xmlSend($fields)
    {
        $request = "DATA=<?xml version=\"1.0\" encoding=\"ISO-8859-9\"?>" .
            "<CC5Request>" .
            "<Name>" . $fields['name'] . "</Name>" .
            "<Password>" . $fields['password'] . "</Password>" .
            "<ClientId>" . $fields['clientid'] . "</ClientId>" .
            "<IPAddress>" . $fields['ip'] . "</IPAddress>" .
            "<Email>" . $fields['email'] . "</Email>" .
            "<Mode>" . $fields['mode'] . "</Mode>" .
            "<OrderId>" . $fields['oid'] . "</OrderId>" .
            "<GroupId></GroupId>" .
            "<TransId></TransId>" .
            "<UserId></UserId>" .
            "<Type>" . $fields['type'] . "</Type>" .
            "<Number>" . $fields['md'] . "</Number>" .
            "<Expires></Expires>" .
            "<Cvv2Val></Cvv2Val>" .
            "<Total>" . $fields['tutar'] . "</Total>" .
            "<Currency>949</Currency>" .
            "<Taksit>" . $fields['taksit'] . "</Taksit>" .
            "<PayerTxnId>" . $fields['xid'] . "</PayerTxnId>" .
            "<PayerSecurityLevel>" . $fields['eci'] . "</PayerSecurityLevel>" .
            "<PayerAuthenticationCode>" . $fields['cavv'] . "</PayerAuthenticationCode>" .
            "<CardholderPresentCode>13</CardholderPresentCode>" .
            "<BillTo>" .
            "<Name></Name>" .
            "<Street1></Street1>" .
            "<Street2></Street2>" .
            "<Street3></Street3>" .
            "<City></City>" .
            "<StateProv></StateProv>" .
            "<PostalCode></PostalCode>" .
            "<Country></Country>" .
            "<Company></Company>" .
            "<TelVoice></TelVoice>" .
            "</BillTo>" .
            "<ShipTo>" .
            "<Name></Name>" .
            "<Street1></Street1>" .
            "<Street2></Street2>" .
            "<Street3></Street3>" .
            "<City></City>" .
            "<StateProv></StateProv>" .
            "<PostalCode></PostalCode>" .
            "<Country></Country>" .
            "</ShipTo>" .
            "<Extra></Extra>" .
            "</CC5Request>";

        $url = $fields['url'];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_SSLVERSION, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 90);
        curl_setopt($ch, CURLOPT_POSTFIELDS, urlencode($request));
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            $result = '<CC5Response><ErrMsg>cUrl Error: ' . curl_error($ch) . '</ErrMsg></CC5Response>';
        }
        curl_close($ch);
        return $result;
    }
}