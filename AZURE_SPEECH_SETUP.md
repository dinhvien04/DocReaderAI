# Azure Speech Service Setup Guide

## 📋 Tổng quan

Azure Speech Service là dịch vụ Text-to-Speech của Microsoft với giọng đọc Neural chất lượng cao, hỗ trợ nhiều ngôn ngữ.

## 🔑 Lấy API Keys

### Bước 1: Tạo Azure Account
1. Truy cập: https://portal.azure.com/
2. Đăng ký tài khoản (Free tier có 5 triệu ký tự/tháng miễn phí)

### Bước 2: Tạo Speech Service Resource
1. Trong Azure Portal, click "Create a resource"
2. Search "Speech" và chọn "Speech Services"
3. Click "Create"
4. Điền thông tin:
   - **Subscription**: Chọn subscription của bạn
   - **Resource group**: Tạo mới hoặc chọn existing
   - **Region**: Chọn `Southeast Asia` (Singapore) hoặc `East Asia` (Hong Kong)
   - **Name**: Đặt tên cho resource (vd: docreader-speech)
   - **Pricing tier**: Chọn `Free F0` (5M chars/month) hoặc `Standard S0`
5. Click "Review + create" → "Create"

### Bước 3: Lấy Keys và Region
1. Sau khi tạo xong, vào resource vừa tạo
2. Trong menu bên trái, chọn "Keys and Endpoint"
3. Copy:
   - **KEY 1** → `AZURE_SPEECH_KEY`
   - **KEY 2** → `AZURE_SPEECH_KEY2`
   - **Location/Region** → `AZURE_SPEECH_REGION`


## ⚙️ Cấu hình trong Project

### 1. Update file .env
```env
# Azure Speech Service
AZURE_SPEECH_KEY=your_key_1_here
AZURE_SPEECH_KEY2=your_key_2_here
AZURE_SPEECH_REGION=southeastasia
```

### 2. Test Connection
```bash
php test-azure-tts.php
```

Kết quả mong đợi:
```
=== Azure Speech Service Test ===

1. Checking credentials...
✅ Credentials found
   Region: southeastasia

2. Initializing Azure Speech Service...
✅ Service initialized

3. Testing connection...
✅ Connection successful

4. Getting available voices...
✅ Found 6 voices:
   - Hoài My (Nữ, Miền Bắc) (vi-VN-HoaiMyNeural)
   - Nam Minh (Nam, Miền Bắc) (vi-VN-NamMinhNeural)
   ...

5. Testing TTS conversion...
✅ TTS conversion successful
   Audio URL: http://localhost/uploads/audio/azure_xxx.wav
   File size: 123,456 bytes

=== All tests passed! ===
```


## 🎙️ Giọng đọc có sẵn

### Tiếng Việt
- **vi-VN-HoaiMyNeural**: Nữ, Miền Bắc (Recommended)
- **vi-VN-NamMinhNeural**: Nam, Miền Bắc

### Tiếng Anh (US)
- **en-US-JennyNeural**: Female (Recommended)
- **en-US-GuyNeural**: Male
- **en-US-AriaNeural**: Female
- **en-US-DavisNeural**: Male

## 💰 Pricing

### Free Tier (F0)
- 5 triệu ký tự/tháng
- Neural voices: 0.5 triệu ký tự/tháng
- Không cần credit card

### Standard Tier (S0)
- Neural voices: $16/1 triệu ký tự
- Standard voices: $4/1 triệu ký tự
- Pay as you go

## 🔧 Troubleshooting

### Lỗi "Failed to get access token"
- Kiểm tra AZURE_SPEECH_KEY đúng chưa
- Kiểm tra AZURE_SPEECH_REGION đúng chưa
- Thử dùng KEY 2 nếu KEY 1 không hoạt động

### Lỗi "Invalid region"
- Region phải match với region khi tạo resource
- Các region phổ biến: `southeastasia`, `eastasia`, `eastus`

### Lỗi "Quota exceeded"
- Đã vượt quá 5M chars/month của Free tier
- Upgrade lên Standard tier hoặc đợi tháng sau

## 📚 Tài liệu tham khảo

- Azure Speech Documentation: https://docs.microsoft.com/azure/cognitive-services/speech-service/
- Voice Gallery: https://speech.microsoft.com/portal/voicegallery
- Pricing: https://azure.microsoft.com/pricing/details/cognitive-services/speech-services/
