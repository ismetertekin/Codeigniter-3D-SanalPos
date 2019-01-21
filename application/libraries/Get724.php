<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Get724
{
    public function __construct()
    {
    }

    public function createForm($bank)
    {
        $instalment = $bank["instalment"] == 0 ? '' : $bank["instalment"];
        $post_data = "Pan=" . $bank["cc_number"] . "&ExpiryDate=" . $bank["cc_expire_date_year"] . $bank["cc_expire_date_month"] . "&PurchaseAmount=" . $bank["total"] . "&Currency=949&BrandName=" . $bank['cc_type'] . "&VerifyEnrollmentRequestId=" . $bank["order_id"] . "&SessionInfo=1&MerchantId=" . $bank["get724_merchant_id"] . "&MerchantPassword=" . $bank["get724_merchant_password"] . "&SuccessUrl=" . $bank["success_url"] . "&FailureUrl=" . $bank["fail_url"] . "&InstallmentCount=" . $instalment . "";
        $url = $bank['get724_3D_url'];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type" => "application/x-www-form-urlencoded"));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        $resultXml = curl_exec($ch);
        curl_close($ch);
        $result = $this->read_result($resultXml);

        if ($result["Status"] == "Y") {
            $inputs = array(
                'PaReq' => $result['PaReq'],
                'TermUrl' => $result['TermUrl'],
                'MD' => $result['MerchantData']
            );
            $form = '<form id="webpos_form" name="webpos_form" method="post" action="' . $result['ACSUrl'] . '">';
            foreach ($inputs as $key => $value) {
                $form .= '<input type="hidden" name="' . $key . '" value="' . $value . '" />';
            }
            $form .= '</form>';
            $data['status'] = "1";
            $data['form'] = $form;
            $data['post_data'] = $post_data;
            $data['result'] = $result;
            return $data;
        } else {
            $error_message = "3D-Secure Verify Enrollment Sonucu :<br>";
            $error_message .= $result["Status"] . ": " . $result["MessageErrorCode"] . "<br>";
            $error_message .= "İşlem İsteğini Sanal Pos'a gönderiniz.<br>";
            $data['status'] = "0";
            $data['error_message'] = $error_message;
            return $data;
        }
    }

    public function methodResponse($bank)
    {
        $response = array();
        $result = $this->createForm($bank);
        if ($result['status'] == "1") {
            $response['form'] = $result['form'];
        } else {
            $response['form'] = '';
            $response['status'] = 'error';
            $response['error_message'] = $result['error_message'];
        }
        return $response;
    }

    public function bankResponse($bank_response, $bank)
    {
        $response = array();
        $response['message'] = '';
        if ($bank_response['Status'] == "Y") {
            $response['message'] .= '3D Onayı Başarılı.<br/>';
            $xml_fields = array(
                'MerchantId' => $bank_response['MerchantId'],
                'Pan' => $bank_response['Pan'],
                'CurrencyCode' => $bank_response['PurchCurrency'],
                'MpiTransactionId' => $bank_response['VerifyEnrollmentRequestId'],
                'CAVV' => $bank_response['Cavv'],
                'ECI' => $bank_response['Eci'],
                'Password' => $bank['get724_merchant_password'],
                'TerminalNo' => $bank['get724_user_name'],
                'Url' => $bank['get724_classic_url'],
                'CurrencyAmount' => $bank['amount'],
                'Expiry' => $bank['expire_date'],
                'Cvv' => $bank['cv2'],
                'NumberOfInstallments' => $bank_response['InstallmentCount'],
                'OrderId' => $bank['order_id'],
                'ClientIp' => $bank['customer_ip'],
                'CardHoldersName' => $bank['card_holders_name']
            );
            $xml_response = $this->xmlSend($xml_fields);
            $xml = simplexml_load_string($xml_response);
            if ($xml->ResultCode == "0000") {
                $response['result'] = 1;
                $response['message'] .= 'Ödeme Başarılı<br/>';
                $response['message'] .= 'Banka Adı : ' . $bank["name"] . '<br/>';
                if ($bank_response['InstallmentCount']) {
                    $response['message'] .= 'Taksit : ' . $bank_response['InstallmentCount'] . '<br/>';
                } else {
                    $response['message'] .= 'Taksit : Yok <br/>';
                }
                $response['message'] .= 'Sipariş Id : ' . $bank_response['VerifyEnrollmentRequestId'] . '<br/>';
            } else {
                $response['result'] = 0;
                $response['message'] .= 'Ödeme Başarısız<br/>';
                $response['message'] .= 'Banka Adı : ' . $bank["name"] . '<br/>';
                $response['message'] .= 'Hata Kodu : ' . $xml->ResultCode . '<br/>';
                $response['message'] .= 'Hata Açıklaması : ' . $xml->ResultDetail . '<br/>';
            }
        } else {
            $response['result'] = 0;
            $response['message'] .= '3D doğrulama başarısız<br/>';
            $response['message'] .= 'ErrDesc : ' . $this->error_description($bank_response['ErrorCode']) . '<br/>';
        }
        return $response;
    }

    public function xmlSend($fields)
    {
        $instalment = false;
        $request = "";
        if ($fields['NumberOfInstallments'] != "" && $fields['NumberOfInstallments'] != "0") {
            $instalment = true;
        }
        if ($instalment) {
            $request = "prmstr=<?xml version=\"1.0\" encoding=\"UTF-8\"?>
                    <VposRequest>
                        <MerchantId>" . $fields['MerchantId'] . "</MerchantId>
                        <Password>" . $fields['Password'] . "</Password>
                        <TerminalNo>" . $fields['TerminalNo'] . "</TerminalNo>
                        <TransactionType>Sale</TransactionType>
                        <TransactionId></TransactionId>
                        <CurrencyAmount>" . $fields['CurrencyAmount'] . "</CurrencyAmount>
                        <CurrencyCode>" . $fields['CurrencyCode'] . "</CurrencyCode>
                        <CardHoldersName>" . $fields['CardHoldersName'] . "</CardHoldersName>
                        <Pan>" . $fields['Pan'] . "</Pan>
                        <Cvv>" . $fields['Cvv'] . "</Cvv>
                        <Expiry>" . $fields['Expiry'] . "</Expiry>
                        <ECI>" . $fields['ECI'] . "</ECI>
                        <CAVV>" . $fields['CAVV'] . "</CAVV>
                        <MpiTransactionId>" . $fields['MpiTransactionId'] . "</MpiTransactionId>
                        <OrderId>" . $fields['OrderId'] . "</OrderId>
                        <ClientIp>" . $fields['ClientIp'] . "</ClientIp>
                        <NumberOfInstallments>" . $fields['NumberOfInstallments'] . "</NumberOfInstallments>
                        <TransactionDeviceSource>0</TransactionDeviceSource>
                </VposRequest>";
        } else {
            $request = "prmstr=<?xml version=\"1.0\" encoding=\"UTF-8\"?>
                    <VposRequest>
                        <MerchantId>" . $fields['MerchantId'] . "</MerchantId>
                        <Password>" . $fields['Password'] . "</Password>
                        <TerminalNo>" . $fields['TerminalNo'] . "</TerminalNo>
                        <TransactionType>Sale</TransactionType>
                        <TransactionId></TransactionId>
                        <CurrencyAmount>" . $fields['CurrencyAmount'] . "</CurrencyAmount>
                        <CurrencyCode>" . $fields['CurrencyCode'] . "</CurrencyCode>
                        <CardHoldersName>" . $fields['CardHoldersName'] . "</CardHoldersName>
                        <Pan>" . $fields['Pan'] . "</Pan>
                        <Cvv>" . $fields['Cvv'] . "</Cvv>
                        <Expiry>" . $fields['Expiry'] . "</Expiry>
                        <ECI>" . $fields['ECI'] . "</ECI>
                        <CAVV>" . $fields['CAVV'] . "</CAVV>
                        <MpiTransactionId>" . $fields['MpiTransactionId'] . "</MpiTransactionId>
                        <OrderId>" . $fields['OrderId'] . "</OrderId>
                        <ClientIp>" . $fields['ClientIp'] . "</ClientIp>
                        <TransactionDeviceSource>0</TransactionDeviceSource>
                </VposRequest>";
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $fields['Url']);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 59);
        $result = curl_exec($ch);
        if ($errno = curl_errno($ch)) {
            $error_message = curl_strerror($errno);
            $result = "<VposResponse><ResultCode>-1</ResultCode><ResultDetail>cUrl Error: " . $errno . ":" . $error_message . "</ResultDetail></VposResponse>";
        }
        curl_close($ch);
        return $result;
    }

    public function read_result($result)
    {
        $resultDocument = new DOMDocument();
        $resultDocument->loadXML($result);

        //Status Bilgisi okunuyor
        $statusNode = $resultDocument->getElementsByTagName("Status")->item(0);
        $status = "";
        if ($statusNode != null)
            $status = $statusNode->nodeValue;

        //PAReq Bilgisi okunuyor
        $PAReqNode = $resultDocument->getElementsByTagName("PaReq")->item(0);
        $PaReq = "";
        if ($PAReqNode != null)
            $PaReq = $PAReqNode->nodeValue;

        //ACSUrl Bilgisi okunuyor
        $ACSUrlNode = $resultDocument->getElementsByTagName("ACSUrl")->item(0);
        $ACSUrl = "";
        if ($ACSUrlNode != null)
            $ACSUrl = $ACSUrlNode->nodeValue;

        //Term Url Bilgisi okunuyor
        $TermUrlNode = $resultDocument->getElementsByTagName("TermUrl")->item(0);
        $TermUrl = "";
        if ($TermUrlNode != null)
            $TermUrl = $TermUrlNode->nodeValue;

        //MD Bilgisi okunuyor
        $MDNode = $resultDocument->getElementsByTagName("MD")->item(0);
        $MD = "";
        if ($MDNode != null)
            $MD = $MDNode->nodeValue;

        //MessageErrorCode Bilgisi okunuyor
        $messageErrorCodeNode = $resultDocument->getElementsByTagName("MessageErrorCode")->item(0);
        $messageErrorCode = "";
        if ($messageErrorCodeNode != null)
            $messageErrorCode = $messageErrorCodeNode->nodeValue;

        // Sonuç dizisi oluşturuluyor
        $result = array
        (
            "Status" => $status,
            "PaReq" => $PaReq,
            "ACSUrl" => $ACSUrl,
            "TermUrl" => $TermUrl,
            "MerchantData" => $MD,
            "MessageErrorCode" => $messageErrorCode
        );
        return $result;
    }

    public function error_description($error_code)
    {
        $data = array(
            '0000' => 'Başarılı',
            '0001' => 'BANKANIZI ARAYIN',
            '0002' => 'BANKANIZI ARAYIN',
            '0003' => 'ÜYE KODU HATALI/TANIMSIZ',
            '0004' => 'KARTA EL KOYUNUZ',
            '0005' => 'İŞLEM ONAYLANMADI.',
            '0006' => 'HATALI İŞLEM',
            '0007' => 'KARTA EL KOYUNUZ',
            '0009' => 'TEKRAR DENEYİNİZ',
            '0010' => 'TEKRAR DENEYİNİZ',
            '0011' => 'TEKRAR DENEYİNİZ',
            '0012' => 'Geçersiz İşlem',
            '0013' => 'Geçersiz İşlem Tutarı',
            '0014' => 'Geçersiz Kart Numarası',
            '0015' => 'MÜŞTERİ YOK/BIN HATALI',
            '0021' => 'İŞLEM ONAYLANMADI',
            '0030' => 'MESAJ FORMATI HATALI (ÜYE İŞYERİ)',
            '0032' => 'DOSYASINA ULAŞILAMADI',
            '0033' => 'SÜRESİ BİTMİŞ/İPTAL KART',
            '0034' => 'SAHTE KART',
            '0036' => 'İŞLEM ONAYLANMADI',
            '0038' => 'ŞİFRE AŞIMI/KARTA EL KOY',
            '0041' => 'KAYIP KART- KARTA EL KOY',
            '0043' => 'ÇALINTI KART-KARTA EL KOY',
            '0051' => 'LIMIT YETERSIZ',
            '0052' => 'HESAP NOYU KONTROL EDİN',
            '0053' => 'HESAP YOK',
            '0054' => 'GEÇERSİZ KART',
            '0055' => 'Hatalı Kart Şifresi',
            '0056' => 'Kart Tanımlı Değil.',
            '0057' => 'KARTIN İŞLEM İZNİ YOK',
            '0058' => 'POS İŞLEM TİPİNE KAPALI',
            '0059' => 'SAHTEKARLIK ŞÜPHESİ',
            '0061' => 'Para çekme tutar limiti aşıldı',
            '0062' => 'YASAKLANMIŞ KART',
            '0063' => 'Güvenlik ihlali',
            '0065' => 'GÜNLÜK İŞLEM ADEDİ LİMİTİ AŞILDI',
            '0075' => 'Şifre Deneme Sayısı Aşıldı',
            '0077' => 'ŞİFRE SCRIPT TALEBİ REDDEDİLDİ',
            '0078' => 'ŞİFRE GÜVENİLİR BULUNMADI',
            '0089' => 'İŞLEM ONAYLANMADI',
            '0091' => 'KARTI VEREN BANKA HİZMET DIŞI',
            '0092' => 'BANKASI BİLİNMİYOR',
            '0093' => 'İŞLEM ONAYLANMADI',
            '0096' => 'BANKASININ SİSTEMİ ARIZALI',
            '0312' => 'GEÇERSİZ KART',
            '0315' => 'TEKRAR DENEYİNİZ',
            '0320' => 'ÖNPROVİZYON KAPATILAMADI',
            '0323' => 'ÖNPROVİZYON KAPATILAMADI',
            '0357' => 'İŞLEM ONAYLANMADI',
            '0358' => 'Kart Kapalı',
            '0381' => 'RED KARTA EL KOY',
            '0382' => 'SAHTE KART-KARTA EL KOYUNUZ',
            '0501' => 'GEÇERSİZ TAKSİT/İŞLEM TUTARI',
            '0503' => 'KART NUMARASI HATALI',
            '0504' => 'İŞLEM ONAYLANMADI',
            '0540' => 'İade Edilecek İşlemin Orijinali Bulunamadı',
            '0541' => 'Orj. İşlemin tamamı iade edildi',
            '0542' => 'İADE İŞLEMİ GERÇEKLEŞTİRİLEMEZ',
            '0550' => 'İŞLEM YKB POS UNDAN YAPILMALI',
            '0570' => 'YURTDIŞI KART İŞLEM İZNİ YOK',
            '0571' => 'İşyeri Amex İşlem İzni Yok',
            '0572' => 'İşyeri Amex Tanımları Eksik',
            '0574' => 'ÜYE İŞYERİ İŞLEM İZNİ YOK',
            '0575' => 'İŞLEM ONAYLANMADI',
            '0577' => 'TAKSİTLİ İŞLEM İZNİ YOK',
            '0580' => 'HATALI 3D GÜVENLİK BİLGİSİ',
            '0581' => 'ECI veya CAVV bilgisi eksik',
            '0582' => 'HATALI 3D GÜVENLİK BİLGİSİ',
            '0583' => 'TEKRAR DENEYİNİZ',
            '0880' => 'İŞLEM ONAYLANMADI',
            '0961' => 'İŞLEM TİPİ GEÇERSİZ',
            '0962' => 'TerminalID Tanımısız',
            '0963' => 'Üye İşyeri Tanımlı Değil',
            '0966' => 'İŞLEM ONAYLANMADI',
            '0971' => 'Eşleşmiş bir işlem iptal edilemez',
            '0972' => 'Para Kodu Geçersiz',
            '0973' => 'İŞLEM ONAYLANMADI',
            '0974' => 'İŞLEM ONAYLANMADI',
            '0975' => 'ÜYE İŞYERİ İŞLEM İZNİ YOK',
            '0976' => 'İŞLEM ONAYLANMADI',
            '0978' => 'KARTIN TAKSİTLİ İŞLEME İZNİ YOK',
            '0980' => 'İŞLEM ONAYLANMADI',
            '0981' => 'EKSİK GÜVENLİK BİLGİSİ',
            '0982' => 'İŞLEM İPTAL DURUMDA. İADE EDİLEMEZ',
            '0983' => 'İade edilemez,iptal',
            '0984' => 'İADE TUTAR HATASI',
            '0985' => 'İŞLEM ONAYLANMADI.',
            '0986' => 'GIB Taksit Hata',
            '0987' => 'İŞLEM ONAYLANMADI.',
            '8484' => 'Birden fazla hata olması durumunda geri dönülür. ResultDetail alanından detayları alınabilir.',
            '1001' => 'Sistem hatası.',
            '1006' => 'Bu TransactionId ile daha önce başarılı bir işlem gerçekleştirilmiş',
            '1007' => 'Referans transaction alınamadı',
            '1046' => 'İade işleminde tutar hatalı.',
            '1047' => 'İşlem tutarı geçersizdir.',
            '1049' => 'Geçersiz tutar.',
            '1050' => 'CVV hatalı.',
            '1051' => 'Kredi kartı numarası hatalıdır.',
            '1052' => 'Kredi kartı son kullanma tarihi hatalı.',
            '1054' => 'İşlem numarası hatalıdır.',
            '1059' => 'Yeniden iade denemesi.',
            '1060' => 'Hatalı taksit sayısı.',
            '2011' => 'TerminalNo Bulunamadı.',
            '2200' => 'İş yerinin işlem için gerekli hakkı yok.',
            '2202' => 'İşlem iptal edilemez. ( Batch Kapalı )',
            '5001' => 'İş yeri şifresi yanlış.',
            '5002' => 'İş yeri aktif değil.',
            '1073' => 'Terminal üzerinde aktif olarak bir batch bulunamadı',
            '1074' => 'İşlem henüz sonlanmamış yada referans işlem henüz tamamlanmamış.',
            '1075' => 'Sadakat puan tutarı hatalı',
            '1076' => 'Sadakat puan kodu hatalı',
            '1077' => 'Para kodu hatalı',
            '1078' => 'Geçersiz sipariş numarası',
            '1079' => 'Geçersiz sipariş açıklaması',
            '1080' => 'Sadakat tutarı ve para tutarı gönderilmemiş.',
            '1061' => 'Aynı sipariş numarasıyla (OrderId) daha önceden başarılı işlem yapılmış',
            '1065' => 'Ön provizyon daha önceden kapatılmış',
            '1082' => 'Geçersiz işlem tipi',
            '1083' => 'Referans işlem daha önceden iptal edilmiş.',
            '1084' => 'Geçersiz poaş kart numarası',
            '7777' => 'Banka tarafında gün sonu yapıldığından işlem gerçekleştirilemedi',
            '1087' => 'Yabancı para birimiyle taksitli provizyon kapama işlemi yapılamaz',
            '1088' => 'Önprovizyon iptal edilmiş',
            '1089' => 'Referans işlem yapılmak istenen işlem için uygun değil',
            '1091' => 'Recurring işlemin toplam taksit sayısı hatalı',
            '1092' => 'Recurring işlemin tekrarlama aralığı hatalı',
            '1093' => 'Sadece Satış (Sale) işlemi recurring olarak işaretlenebilir',
            '1095' => 'Lütfen geçerli bir email adresi giriniz',
            '1096' => 'Lütfen geçerli bir IP adresi giriniz',
            '1097' => 'Lütfen geçerli bir CAVV değeri giriniz',
            '1098' => 'Lütfen geçerli bir ECI değeri giriniz.',
            '1099' => 'Lütfen geçerli bir Kart Sahibi ismi giriniz.',
            '1100' => 'Lütfen geçerli bir brand girişi yapın.',
            '1105' => 'Üye işyeri IP si sistemde tanımlı değil',
            '1102' => 'Recurring işlem aralık tipi hatalı bir değere sahip',
            '1101' => 'Referans transaction reverse edilmiş.',
            '1104' => 'İlgili taksit için tanım yok',
            '1111' => 'Bu üye işyeri Non Secure işlem yapamaz',
            '1122' => 'SurchargeAmount değeri 0 dan büyük olmalıdır.',
            '6000' => 'Talep Mesajı okunamadı.',
            '6001' => 'İstek HttpPost Yöntemi ile yapılmalıdır.',
            '6003' => 'POX Request Adresine istek yapıyorsunuz. Mesaj Boş Geldi. İstek Xml Mesajını prmstr parametresi ile iletiniz.',
            '9117' => '3DSecure Islemlerde ECI degeri bos olamaz.',
            '33' => 'Kartın 3D Secure şifre doğrulaması yapılamadı',
            '400' => '3D Şifre doğrulaması yapılamadı.',
            '1026' => 'FailureUrl format hatası',
            '2000' => 'Acquirer info is empty',
            '2005' => 'Merchant cannot be found for this bank',
            '2006' => 'Merchant acquirer bin password required',
            '2009' => 'Brand not found',
            '2010' => 'CardHolder info is empty',
            '2011' => 'Pan is empty',
            '2012' => 'DeviceCategory must be between 0 and 2',
            '2013' => 'Threed secure message can not be found',
            '2014' => 'Pares message id does not match threed secure message id',
            '2015' => 'Signature verification false',
            '2017' => 'AcquireBin Can not be found',
            '2018' => 'Merchant acquirer bin password wrong',
            '2019' => 'Bank not found',
            '2020' => 'Bank Id does not match merchant bank',
            '2021' => 'Invalid Currency Code',
            '2022' => 'Verify EnrollmentRequest Id cannot be empty',
            '2023' => 'Verify Enrollment Request Id Already exist for this merchant',
            '2024' => 'Acs certificate cannot be found in database',
            '2025' => 'Certificate could not be found in certificate store',
            '2026' => 'Brand certificate not found in store',
            '2027' => 'Invalid xml file',
            '2028' => 'Threed Secure Message is Invalid State',
            '2029' => 'Invalid Pan',
            '2030' => 'Invalid Expire Date',
            '1001' => 'Fail Url Format is Invalid',
            '1002' => 'SuccessUrl format is invalid.',
            '1003' => 'BrandId format is invalid',
            '1004' => 'DeviceCategory format is invalid',
            '1005' => 'SessionInfo format is invalid',
            '1006' => 'Xid format is invalid',
            '1007' => 'Currency format is invalid',
            '1008' => 'PurchaseAmount format is invalid',
            '1009' => 'Expire Date format is invalid',
            '1010' => 'Pan format is invalid',
            '1011' => 'Merchant acquirer bin password format is invalid',
            '1012' => 'HostMerchant format is invalid',
            '1013' => 'BankId format is invalid',
            '2031' => 'Verification failed: No Signature was found in the document',
            '2032' => 'Verification failed: More that one signature was found for the document',
            '2033' => 'Actual Brand Can not be Found',
            '2034' => 'Invalid Amount',
            '1014' => 'Is Recurring Format is Invalid',
            '1015' => 'Recurring Frequency Format Is Invalid',
            '1016' => 'Recurring End Date Format Is Invalid',
            '2035' => 'Invalid Recurring Information',
            '2036' => 'Invalid Recurring Frequency',
            '2037' => 'Invalid Reccuring End Date',
            '2038' => 'Recurring End Date Must Be Greater Than Expire Date',
            '2039' => 'Invalid x509 certificate Data',
            '2040' => 'Invalid Installment',
            '1017' => 'Installment count format is invalid',
            '3000' => 'Bank not found',
            '3001' => 'Country not found',
            '3002' => 'Invalid FailUrl',
            '3003' => 'HostMerchantNumber cannot  be empty',
            '3004' => 'MerchantBrandAcquirerBin cannot be empty',
            '3005' => 'MerchantName cannot be empty',
            '3006' => 'MerchantPassword cannot be empty',
            '3007' => 'Invalid SucessUrl',
            '3008' => 'Invalid MerchantSiteUrl',
            '3009' => 'Invalid AcquirerBin length',
            '3010' => 'Brand cannot be null',
            '3011' => 'Invalid AcquirerBinPassword length',
            '3012' => 'Invalid HostMerchantNumber length',
            '2041' => 'Pares Exponent Value Does Not Match Pareq Exponent',
            '2042' => 'Pares Acquirer Bin Value Does Not Match Pareq Acqiurer Bin',
            '2043' => 'Pares Merchant Id Does Not Match Pareq Merchant Id',
            '2044' => 'Pares Xid Does Not Match Pareq Xid',
            '2045' => 'Pares Purchase Amount Does Not Match Pareq Purchase Amount',
            '2046' => 'Pares Currency Does Not Match Pareq Currency',
            '2047' => 'VeRes Xsd Validation Error',
            '2048' => 'PaRes Xsd Validation Exception',
            '2049' => 'Invalid Request',
            '2050' => 'File Is Empty',
            '2051' => 'Custom Error',
            '2052' => 'Bank Brand Bin Already Exist',
            '3013' => 'End Date Must Be Greater Than Start',
            '3014' => 'Start Date Must Be Greater Than DateTime MinVal',
            '3015' => 'End Date Must Be Greater Than DateTime MinVal',
            '3016' => 'Invalid Search Period',
            '3017' => 'Bin Cannot Be Empty',
            '3018' => 'Card Type Cannot Be Empty',
            '3019' => 'Bank Brand Bin Not Found',
            '3020' => 'Bin Length Must Be Six',
            '2053' => 'Directory Server Communication Error',
            '2054' => 'ACS Hata Bildirdi',
            '5037' => 'SuccessUrl alanı hatalıdır.'
        );
        return $data[$error_code];
    }
}