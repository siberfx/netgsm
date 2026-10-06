<?php

return [
    'message_too_long' => 'Mesaj metnindeki bir problemden dolayı gönderilemedi veya standart maksimum mesaj karakter sayısını geçtiniz.',
    'start_date_incorrect' => 'Mesaj gönderim baslangıç tarihinde hata var. Sistem tarihi ile değiştirilip işleme alındı.',
    'end_date_incorrect' => 'Mesaj gönderim sonlandırılma tarihinde hata var. Sistem tarihi ile değiştirilip işleme alındı.Bitiş tarihi başlangıç tarihinden küçük girilmiş ise, sistem bitiş tarihine içinde bulunduğu tarihe 24 saat ekler.',
    'sender_incorrect' => 'Mesaj başlığınız (gönderici adınızın) sistemde tanımlı değil.',
    'credentials_incorrect' => 'Geçersiz kullanıcı adı, şifre veya kullanıcınızın API erişim izni yok.',
    'parameters_incorrect' => 'Hatalı sorgulama. Gönderdiğiniz parametrelerden birisi hatalı veya zorunlu alanlardan biri eksik.',
    'receiver_incorrect' => 'Gönderilen numara hatalı.',
    'otp_account_not_defined' => 'Hesabınızda OTP SMS Paketi tanımlı değildir.',
    'query_limit_exceed' => 'Sorgulama limiti aşıldı.',
    'duplicate_limit_exceed' => 'Mükerrer gönderim limiti aşıldı. Aynı numara için 1 dakika içinde 20 adetten fazla görev oluşturulamaz.',
    'iys_controlled' => 'Abonelik hesabınız ile İYS kontrollü gönderim yapılamamaktadır.',
    'iys_brand_not_found' => 'Aboneliğinize ait İYS marka bilgisi bulunamadı.',
    'system_error' => 'Sistem hatası.',
    'netgsm_general_error' => 'NetGsm hatalı cevap döndü :',
    'no_record' => 'Kayıt yok',
    'job_id_not_found' => 'Job id bulunamadı',

    'invalid_netgsm_message' => 'Geçerli bir NetGsm mesajı değil',
];
