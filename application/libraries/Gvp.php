<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Gvp
{
    public function __construct()
    {
    }

    public function createHash($terminal_id, $order_id, $amount, $ok_url, $fail_url, $type, $instalment, $store_key, $provaut_password)
    {
        $secData = strtoupper(sha1($provaut_password . "0" . $terminal_id));
        $hashstr = $terminal_id . $order_id . $amount . $ok_url . $fail_url . $type . $instalment . $store_key . $secData;
        $hash = strtoupper(sha1($hashstr));
        return $hash;
    }

    public function createForm($bank)
    {
        if ($bank['instalment'] != 0) {
            $instalment = $bank['instalment'];
        } else {
            $instalment = "";
        }
        $new_order_id = round(111, 999) . $bank['order_id'];
        $amount = (int)($bank['total'] * 100);
        $hash = $this->createHash($bank['gvp_terminal_id'], $new_order_id, $amount, $bank['success_url'], $bank['fail_url'], "sales", $instalment, $bank['gvp_3D_storekey'], $bank['gvp_provaut_password']);
        $inputs = array(
            'secure3dsecuritylevel' => "3D",
            'cardnumber' => $bank['cc_number'],
            'cardexpiredatemonth' => $bank['cc_expire_date_month'],
            'cardexpiredateyear' => $bank['cc_expire_date_year'],
            'cardcvv2' => $bank['cc_cvv2'],
            'mode' => "PROD",
            'apiversion' => "v0.01",
            'terminalprovuserid' => "PROVAUT",
            'terminaluserid' => $bank['gvp_user_name'],
            'terminalmerchantid' => $bank['gvp_merchant_id'],
            'txntype' => "sales",
            'txnamount' => $amount,
            'txncurrencycode' => "949",
            'txninstallmentcount' => $instalment,
            'orderid' => $new_order_id,
            'terminalid' => $bank['gvp_terminal_id'],
            'successurl' => $bank['success_url'],
            'errorurl' => $bank['fail_url'],
            'customeripaddress' => $bank['customer_ip'],
            'customeremailaddress' => "",
            'secure3dhash' => $hash
        );
        $action = $bank['gvp_3D_url'];
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
        $response['message'] = '';
        $mdStatus = $bank_response['mdstatus'];
        $mdArray = array('1', '2', '3', '4');
        if (in_array($mdStatus, $mdArray)) {
            $response['message'] .= '3D Onayı Başarılı.<br/>';
            $secData = strtoupper(sha1($bank['gvp_provaut_password'] . "0" . $bank_response['clientid']));
            $hashstr = $bank_response['orderid'] . $bank_response['clientid'] . $bank_response['txnamount'] . $secData;
            $hash = strtoupper(sha1($hashstr));
            $xml_fields = array(
                'mode' => $bank_response['mode'],
                'version' => $bank_response['apiversion'],
                'terminal_id' => $bank_response['clientid'],
                'prov_user_id' => $bank_response['terminalprovuserid'],
                'hash' => $hash,
                'user_id' => $bank_response['terminaluserid'],
                'merchant_id' => $bank_response['terminalmerchantid'],
                'customer_ip' => $bank_response['customeripaddress'],
                'email' => $bank_response['customeremailaddress'],
                'oid' => $bank_response['orderid'],
                'type' => $bank_response['txntype'],
                'instalment' => $bank_response['txninstallmentcount'],
                'amount' => $bank_response['txnamount'],
                'currency' => $bank_response['txncurrencycode'],
                'auth_code' => $bank_response['cavv'],
                'sec_level' => $bank_response['eci'],
                'txn_id' => $bank_response['xid'],
                'md' => $bank_response['md'],
                'url' => $bank['gvp_classic_url']
            );
            $xml_response = $this->xmlSend($xml_fields);
            $xml = simplexml_load_string($xml_response);
            $ReasonCode = (string)$xml->Transaction->Response->ReasonCode;
            $Response = (string)$xml->Transaction->Response->Message;

            if ($ReasonCode == "00" || $Response === "Approved") {
                $response['result'] = 1;
                $response['message'] .= 'Ödeme Başarılı<br/>';
                $response['message'] .= 'Banka Adı : ' . $bank["name"] . '<br/>';
                if ($bank_response['txninstallmentcount']) {
                    $response['message'] .= 'Taksit : ' . $bank_response['txninstallmentcount'] . '<br/>';
                } else {
                    $response['message'] .= 'Taksit : Yok <br/>';
                }
                $response['message'] .= 'Sipariş Id : ' . $bank_response['orderid'] . '<br/>';
                $response['message'] .= 'AuthCode : ' . (string)$xml->Transaction->AuthCode[0] . '<br/>';
                $response['message'] .= 'Response : ' . $Response . '<br/>';
                $response['redirect'] = 'success';
            } else {
                $response['result'] = 0;
                $response['message'] .= 'Ödeme Başarısız.<br/>';
                $response['message'] .= 'Response : ' . $Response . '<br/>';
                $response['message'] .= 'ErrMsg : ' . (string)$xml->Transaction->Response->SysErrMsg . '<br/>';
                $response['message'] .= 'ErrMsg : ' . (string)$xml->Transaction->Response->ErrorMsg . '<br/>';
                $response['message'] .= 'ErrCode : ' . (string)$xml->Transaction->Response->Code . '<br/>';
                $response['message'] .= 'ErrDesc : ' . $this->error_description((string)$xml->Transaction->Response->Code) . '<br/>';
            }
        } else {
            $response['result'] = 0;
            $response['message'] .= '3D doğrulama başarısız<br/>';
            $response['message'] .= $bank_response['mderrormessage'];
        }
        return $response;
    }

    public function xmlSend($fields)
    {
        $request = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
					<GVPSRequest>
					<Mode>" . $fields['mode'] . "</Mode>
					<Version>" . $fields['version'] . "</Version>
					<ChannelCode></ChannelCode>
					<Terminal>
					<ProvUserID>" . $fields['prov_user_id'] . "</ProvUserID>
					<HashData>" . $fields['hash'] . "</HashData>
					<UserID>" . $fields['user_id'] . "</UserID>
					<ID>" . $fields['terminal_id'] . "</ID>
					<MerchantID>" . $fields['merchant_id'] . "</MerchantID>
					</Terminal>
					<Customer>
					<IPAddress>" . $fields['customer_ip'] . "</IPAddress>
					<EmailAddress>" . $fields['email'] . "</EmailAddress>
					</Customer>
					<Card>
					<Number></Number>
					<ExpireDate></ExpireDate>
					<CVV2></CVV2>
					</Card>
					<Order>
					<OrderID>" . $fields['oid'] . "</OrderID>					
					<GroupID></GroupID>					
					<AddressList>
					<Address>
					<Type>B</Type>
					<Name></Name>
					<LastName></LastName>
					<Company></Company>
					<Text></Text>
					<District></District>
					<City></City>
					<PostalCode></PostalCode>
					<Country></Country>
					<PhoneNumber></PhoneNumber>
					</Address>
					</AddressList>
					</Order>
					<Transaction>
					<Type>" . $fields['type'] . "</Type>
					<InstallmentCnt>" . $fields['instalment'] . "</InstallmentCnt>
					<Amount>" . $fields['amount'] . "</Amount>
					<CurrencyCode>" . $fields['currency'] . "</CurrencyCode>
					<CardholderPresentCode>13</CardholderPresentCode>
					<MotoInd>N</MotoInd>
					<Secure3D>
					<AuthenticationCode>" . $fields['auth_code'] . "</AuthenticationCode>
					<SecurityLevel>" . $fields['sec_level'] . "</SecurityLevel>
					<TxnID>" . $fields['txn_id'] . "</TxnID>
					<Md>" . $fields['md'] . "</Md>
					</Secure3D>
					</Transaction>
					</GVPSRequest>";
        $url = $fields['url'];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_SSLVERSION, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 90);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "data=" . $request);
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            $result = '<GVPSResponse><Transaction><Response><SysErrMsg>cUrl Error: ' . curl_error($ch) . '</SysErrMsg><ErrorMsg>cUrl Error: ' . curl_error($ch) . '</ErrorMsg></Response></Transaction></GVPSResponse>';
        }
        curl_close($ch);
        return $result;
    }

    public function error_description($error_code)
    {
        $data = array(
            '1' => 'Bankasından Provizyon Alınız',
            '2' => 'Bankasından Provizyon Alınız (VISA)',
            '3' => 'Üye İşyeri Kategori Kodu Hatalı',
            '4' => 'Karta El Koyunuz',
            '5' => 'İşlem Onaylanmadı',
            '6' => 'İsteminiz Kabul Edilmedi',
            '7' => 'Karta El Koyunuz',
            '8' => 'Kimliğini Kontrol Ederek İşlem Yapınız',
            '9' => 'Kart Yenilenmiş.Müşteriden İsteyin',
            '11' => 'İşlem Gerçekleştirildi (VIP)',
            '12' => 'Geçersiz İşlem',
            '13' => 'Geçersiz Tutar',
            '14' => 'Kart Numarası Hatalı',
            '15' => 'Bankası Bulunamadı/IEM Routing Problem',
            '16' => 'Bakiye Yetersiz. Yarın Tekrar Deneyin',
            '17' => 'İşlem İptal Edildi',
            '18' => 'Kapalı Kart.Tekrar Denemeyin',
            '19' => 'Bir Kere Daha Provizyon Talep Ediniz',
            '21' => 'İşlem İptal Edilemedi',
            '25' => 'Böyle Bir Bilgi Bulunamadı',
            '28' => 'Orijinali Reddedilmiş/Dosya Servisdışı',
            '29' => 'İptal Yapılamadı (Orjinali bulunamadı)',
            '30' => 'Mesajın Formatı Hatalı',
            '31' => 'Issuersign-on Olmamış',
            '32' => 'İşlem Kısmen Gerçekleştirilebildi',
            '33' => 'Kartın Süresi Dolmuş. Karta El Koyunuz',
            '34' => 'Muhtemelen Çalıntı Kart. El Koyunuz',
            '36' => 'Sınırlandırılmış Kart. El Koyunuz',
            '37' => 'Lütfen Banka Güvenliğini Arayınız',
            '38' => 'Şifre Giriş Limiti Aşıldı. El Koyunuz',
            '39' => 'Kredi Hesabı Tanımsız',
            '41' => 'Kayıp Kart. Karta El Koyunuz',
            '43' => 'Çalıntı Kart. Karta El Koyunuz',
            '51' => 'Hesap Müsait Değil',
            '52' => 'Çek Hesabı Tanımsız',
            '53' => 'Hesap Tanımsız',
            '54' => 'Vadesi Dolmuş Kart',
            '55' => 'Şifresi Hatalı',
            '56' => 'Bu Kart Mevcut Değil',
            '57' => 'Kart Sahibi Bu İşlemi Yapamaz',
            '58' => 'Bu İşlemi Yapmanıza Müsaade Edilmiyor',
            '61' => 'Para Çekme Limiti Aşılıyor',
            '62' => 'Kısıtlı Kart/Kendi Ülkesinde Geçerli',
            '63' => 'Bu İşlemi Yapmaya Yetkili Değilsiniz',
            '65' => 'Günlük İşlem Adedi Dolmuş',
            '68' => 'Cevap Çok Geç Geldi. İşlemi İptal Ediniz',
            '75' => 'Şifre Giriş Limiti Aşıldı',
            '76' => 'Şifre Hatalı. Şifre Giriş Limiti Aşıldı',
            '77' => 'Orijinal İşlem İle Uyumsuz Bilgi Alındı',
            '78' => 'Account Balance Not Available',
            '80' => 'Hatalı Tarih/Network Hatası',
            '81' => 'Şifreleme/Yabancı Network Hatası',
            '82' => 'Hatalı CVV/Issuer Cevap Vermedi',
            '83' => 'Şifre Doğrulanamıyor/İletişim Hatası',
            '85' => 'Hesap Doğrulandı',
            '86' => 'Şifre Doğrulanamıyor',
            '88' => 'Şifreleme Hatası',
            '89' => 'Authentication Hatası',
            '90' => 'Günsonu İşlemleri Yapılıyor',
            '91' => 'Bankasına Ulaşılamıyor',
            '92' => 'İşlem Gerekli Yere Yönlendirilemedi',
            '93' => 'Hukuki Nedenlerle İşleminiz Rededildi',
            '94' => 'Duplicate Transmission',
            '95' => 'Günlük Toplamlar Hatalı/İptal Rededildi',
            '96' => 'Sistem Hatası',
            '98' => 'Duplicate Reversal'
        );
        return $data[$error_code];
    }
}