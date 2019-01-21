# Codeigniter-3D-SanalPos

Codeigniter 3D Sanal Pos Entegrasyonu
Türkiye'de kullanılan birçok bankanın sanal POS'larını desteklemektedir.

Hangi Bankalarda Geçerlidir ? 
Bu entegrasyon Türkiye'de geçerli birçok bankaların alt yapısını desteklemektedir.

Libraries klasörü altında bankaların kullandığı yöntemlere göre sınıflar bunulmaktadır.

#Nestpay yöntemini kullanan bankalar (değişiklik yapılmadı ise)
Ak Bank
İş Bankası
Halk Bank
TEB Bank
DenizBank
ING Bankası
Anadolu Bank
Ziraat Bankası
Finans Bankası
Kuveyt Bankası

#GVP yöntemini kullanan bankalar
Garanti Bankası

#GET 7/24 yöntemini kullanan bankalar
Vakıf Bank

#POSNET yöntemini kullanan bankalar
Yapı Kredi (Test edilmedi)

Nasıl Kurulur?
phpmyadmin sayesinde sanalpos isminde yeni bir database oluşturup sql klasörü içerisinde ki sql dosyasını içe aktar yaparak table'ları create edelim.
XAMPP, WampServer vs. gibi sunucu kurulu ise httpdocs içerisinde sanalpos isminde yeni bir klasör oluşturup dosyaları içine kopyalayın.

Ödeme ve Banka Tanımlama olarak 2 farklı sayfa bulunmaktadır.
Banka tanımlama sayfasında Yeni Banka Ekle dediğinizde hangi banka ile çalışacaksanız ilgili bankanın üst bilgilerini girdikten sonra Banka Adı olarak görünen select içerisine Güncel BIN Listelerinin bulunduğu bankalar gelmektedir. Girilen kart hangi bankanın BIN listesinde aranacak ise o bankayı seçip ve kaydedin.
Kaydetme işleminden sonra Banka Listesinde yeni eklediğiniz banka görünecektir.
İşlemler sütun'unda ki ayarlar icon'una tıkladığınızda bankanın size vereceği bilgileri gireceğiniz alan gelecektir.
Burada dikkat edilmesi gereken tek konu Taksikler alanı. İlgili bankada kaç taksit seçeneği uygulayacaksanız aşağıdaki gibi bir tanımlama yapmanız gerekmektedir.
Örnek Taksit Alanı : 0=0;2=2;3=3;4=4;5=5;6=6;7=7;8=8;9=9;10=10;11=11;12=12
Taksit seçeneklerini ";" işareti ile ayırmalısınız. Eğer komisyon uygulayacaksanız "=" simgesinden sonra iskonto oranını yazmalısınız.
Daha açıklayıcı olması adına
0=0 'ın açıklaması : Tek Çekim - Komisyon Yok
2=2 'nin açıklaması : 2 Taksit - %2 Komisyon Uygula
3=3 'nin açıklaması : 3 Taksit - %3 Komisyon Uygula
şeklindedir.
