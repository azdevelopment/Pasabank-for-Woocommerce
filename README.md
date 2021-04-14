# Pasabank-for-Woocommerce
Home qovluğunda olan bütün faylları root folder içərisinə atın. Paşabanka Callback url üçün https://saytiniz.com/okpayment.php təyin etmələrini tələb edin
payment.php və okpayment.php fayllarındakı sertifikatlarızın yolunu təyin edin.

Plugin kommersiya məqsədi daşımadığı üçün avtomatlaşdırılmayıb hər şeyi manual etməlisiniz. 

Qeyd 
əgər satış dollar ilə deyilsə payment.php içərisindəki 13-cü sətirdəki : $amount = number_format(($amount), 2, '', '') * 1.70; aşağıdakı kod ilə dəyişin.
$amount = number_format(($amount), 2, '', ''); 
