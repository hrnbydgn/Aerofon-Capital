# Yapılacaklar Listesi - Flet Android Uygulaması

Python Flet ile geliştirilmiş, Android uyumlu basit bir yapılacaklar listesi uygulaması.

## Özellikler

- Görev ekleme / silme
- Görev tamamlama (checkbox)
- Görev sayacı
- Tümünü temizleme
- Mobil uyumlu modern arayüz

## Kurulum

```bash
pip install -r requirements.txt
```

## Çalıştırma

### Masaüstünde test etmek için:

```bash
flet run main.py
```

### Android APK oluşturmak için:

```bash
flet build apk
```

> **Not:** APK derlemesi için sisteminizde Flutter SDK ve Android SDK kurulu olmalıdır.  
> Detaylar: [Flet Packaging Docs](https://flet.dev/docs/publish)
