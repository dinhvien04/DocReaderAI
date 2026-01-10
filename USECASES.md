# DocReader AI Studio - Use Cases

Tài liệu mô tả luồng hoạt động của các chức năng chính trong hệ thống.

---

## 📑 Mục lục

1. [Tóm tắt văn bản](#1-tóm-tắt-văn-bản)
2. [Tạo link chia sẻ audio](#2-tạo-link-chia-sẻ-audio)
3. [Chia sẻ công khai (Explore)](#3-chia-sẻ-công-khai-explore)
4. [Quản lý chia sẻ (Admin)](#4-quản-lý-chia-sẻ-admin)
5. [Chia sẻ của tôi](#5-chia-sẻ-của-tôi)

---

## 1. Tóm tắt văn bản

### Tác nhân
Người dùng đã đăng nhập

### Điều kiện trước
Người dùng đã đăng nhập tài khoản

### Điều kiện sau
Văn bản được tóm tắt thành công với độ dài ngắn gọn hơn

### Mô tả
Giúp người dùng nhanh chóng nắm bắt nội dung chính của văn bản dài, tiết kiệm thời gian đọc.

### Luồng chính

| Hành động của người dùng | Hành động của hệ thống |
|--------------------------|------------------------|
| 1. Vào tab "Tóm tắt" trong Dashboard | 2. Hiển thị form với ô nhập văn bản và nút upload file |
| 3. Nhập văn bản hoặc upload file PDF/DOCX | 4. Trích xuất văn bản từ file (nếu upload) và hiển thị trong ô nhập |
| 5. Click nút "Tóm tắt" | 6. Kiểm tra độ dài văn bản (100-10000 ký tự), gửi đến AI để tóm tắt |
| | 7. Hiển thị kết quả tóm tắt với độ dài 150-200 từ, có nút Copy và Download |
| 8. Click "Copy" hoặc "Download" | 9. Copy vào clipboard hoặc tải file TXT về máy |

### Ngoại lệ
- Văn bản quá ngắn (<100 ký tự) → Thông báo "Văn bản quá ngắn để tóm tắt"
- Văn bản quá dài (>10000 ký tự) → Thông báo "Văn bản vượt quá giới hạn"
- File không hợp lệ → Thông báo "Định dạng file không được hỗ trợ"

---

## 2. Tạo link chia sẻ audio

### Tác nhân
Người dùng đã đăng nhập

### Điều kiện trước
- Người dùng đã đăng nhập
- Đã có ít nhất một audio trong lịch sử

### Điều kiện sau
- Link chia sẻ được tạo thành công
- Audio có thể truy cập công khai qua link

### Mô tả
Cho phép người dùng chia sẻ audio với người khác thông qua link công khai, tăng khả năng lan truyền nội dung.

### Luồng chính

| Hành động của người dùng | Hành động của hệ thống |
|--------------------------|------------------------|
| 1. Vào tab "Lịch sử", chọn một audio | 2. Hiển thị danh sách audio với nút "Chia sẻ" trên mỗi dòng |
| 3. Click nút "Chia sẻ" | 4. Mở popup với form: Tiêu đề, Mô tả, Danh mục, Trạng thái (Công khai/Riêng tư) |
| 5. Điền thông tin và click "Tạo link chia sẻ" | 6. Kiểm tra dữ liệu, tạo mã ngẫu nhiên 10 ký tự, lưu vào database |
| | 7. Hiển thị popup thành công với link đầy đủ và nút "Copy link" |
| 8. Click "Copy link" | 9. Copy link vào clipboard, hiển thị thông báo "Đã copy!" |

### Luồng phụ - Quản lý link

| Hành động của người dùng | Hành động của hệ thống |
|--------------------------|------------------------|
| 1. Vào tab "Chia sẻ của tôi" | 2. Hiển thị danh sách link đã tạo với: Tiêu đề, Lượt xem, Trạng thái, Ngày tạo |
| 3. Click "Chỉnh sửa" | 4. Mở popup cho phép sửa tiêu đề, mô tả, trạng thái |
| 5. Click "Xóa" | 6. Hiển thị xác nhận, sau đó xóa link khỏi hệ thống |

### Ngoại lệ
- Audio không tồn tại → Thông báo "Audio không tồn tại"
- Tiêu đề quá dài → Thông báo "Tiêu đề tối đa 200 ký tự"
- Lỗi tạo mã → Thông báo "Lỗi hệ thống, vui lòng thử lại"

---

## 3. Chia sẻ công khai (Explore)

### Tác nhân
- Người dùng chưa đăng nhập (khách)
- Người dùng đã đăng nhập

### Điều kiện trước
Hệ thống có ít nhất một audio công khai

### Điều kiện sau
Người dùng có thể xem và nghe audio từ cộng đồng

### Mô tả
Cho phép mọi người khám phá và nghe các audio được chia sẻ công khai, tạo cảm hứng và thu hút người dùng mới.

### Luồng chính - Xem danh sách

| Hành động của người dùng | Hành động của hệ thống |
|--------------------------|------------------------|
| 1. Truy cập trang chủ hoặc menu "Khám phá" | 2. Hiển thị grid 4 cột với các card audio: Tiêu đề, Tác giả, Lượt xem, Danh mục |
| 3. Click tab "Giáo dục" để lọc | 4. Chỉ hiển thị audio thuộc danh mục Giáo dục |
| 5. Gõ "AI" vào ô tìm kiếm | 6. Hiển thị audio có từ khóa "AI" trong tiêu đề hoặc nội dung |
| 7. Chọn "Sắp xếp: Nhiều lượt xem nhất" | 8. Sắp xếp lại danh sách theo lượt xem giảm dần |
| 9. Cuộn xuống cuối trang | 10. Tự động tải thêm 12 audio tiếp theo |

### Luồng chính - Xem chi tiết

| Hành động của người dùng | Hành động của hệ thống |
|--------------------------|------------------------|
| 1. Click vào card audio "Bài giảng về ML" | 2. Mở popup với tiêu đề, tác giả, lượt xem, audio player và nội dung đầy đủ |
| | 3. Tăng lượt xem lên 1 (nếu chưa xem trong 24h) |
| 4. Click play | 5. Phát audio với controls đầy đủ |
| 6. Click "Chia sẻ" | 7. Hiển thị popup với link và nút copy |
| 8. Click "Tạo audio của riêng bạn" | 9. Chuyển đến trang đăng ký (nếu chưa đăng nhập) |

### Ngoại lệ
- Không có audio → Hiển thị "Chưa có audio nào được chia sẻ"
- Tìm kiếm không có kết quả → Hiển thị "Không tìm thấy audio"
- Link không tồn tại → Hiển thị trang 404
- File audio bị xóa → Hiển thị "File audio không còn tồn tại"

---

## 4. Quản lý chia sẻ (Admin)

### Tác nhân
Admin (Quản trị viên)

### Điều kiện trước
- Admin đã đăng nhập với quyền quản trị
- Có ít nhất một yêu cầu chia sẻ

### Điều kiện sau
Yêu cầu được duyệt/từ chối/xóa thành công

### Mô tả
Kiểm duyệt và quản lý các yêu cầu chia sẻ audio công khai, đảm bảo nội dung phù hợp.

### Luồng chính - Xem và lọc

| Hành động của Admin | Hành động của hệ thống |
|---------------------|------------------------|
| 1. Vào menu "Admin" → "Quản lý chia sẻ" | 2. Hiển thị danh sách với 4 tab: Tất cả, Chờ duyệt, Đã duyệt, Từ chối |
| 3. Click tab "Chờ duyệt" | 4. Chỉ hiển thị yêu cầu đang chờ với badge màu vàng |
| 5. Click tab "Đã duyệt" | 6. Hiển thị audio đã duyệt với badge màu xanh |

### Luồng chính - Nghe thử

| Hành động của Admin | Hành động của hệ thống |
|---------------------|------------------------|
| 1. Click nút "Nghe thử" trên một yêu cầu | 2. Mở popup với tiêu đề, nội dung văn bản và audio player |
| 3. Click play để nghe | 4. Phát audio với controls |
| 5. Click X để đóng | 6. Đóng popup, quay về danh sách |

### Luồng chính - Duyệt

| Hành động của Admin | Hành động của hệ thống |
|---------------------|------------------------|
| 1. Click nút "Duyệt" | 2. Hiển thị xác nhận "Bạn có chắc muốn duyệt?" |
| 3. Click "Duyệt" để xác nhận | 4. Thông báo "Đã duyệt", chuyển trạng thái, audio xuất hiện trên Explore |

### Luồng chính - Từ chối

| Hành động của Admin | Hành động của hệ thống |
|---------------------|------------------------|
| 1. Click nút "Từ chối" | 2. Mở popup với ô nhập lý do |
| 3. Gõ lý do và click "Từ chối" | 4. Thông báo "Đã từ chối", lưu lý do, chuyển trạng thái |

### Luồng chính - Xóa

| Hành động của Admin | Hành động của hệ thống |
|---------------------|------------------------|
| 1. Click nút "Xóa" | 2. Cảnh báo "Hành động không thể hoàn tác!" |
| 3. Click "Xóa" để xác nhận | 4. Thông báo "Đã xóa", xóa khỏi hệ thống và Explore |

### Ngoại lệ
- Không phải admin → Chuyển về trang chủ với thông báo lỗi
- Không tải được danh sách → Hiển thị "Không thể tải dữ liệu"
- File audio không tồn tại → Hiển thị "File không còn tồn tại"
- Yêu cầu đã xử lý → Thông báo "Đã được xử lý bởi admin khác"

---

## 5. Chia sẻ của tôi

### Tác nhân
Người dùng đã đăng nhập

### Điều kiện trước
- Đã đăng nhập
- Đã gửi ít nhất một yêu cầu chia sẻ

### Điều kiện sau
Người dùng có thể xem và quản lý các yêu cầu của mình

### Mô tả
Quản lý các yêu cầu chia sẻ audio, theo dõi trạng thái, xem lý do từ chối, copy link.

### Luồng chính - Xem danh sách

| Hành động của người dùng | Hành động của hệ thống |
|--------------------------|------------------------|
| 1. Vào tab "Chia sẻ của tôi" | 2. Hiển thị danh sách với 4 tab: Tất cả, Chờ duyệt, Đã duyệt, Từ chối |
| 3. Click tab "Đã duyệt" | 4. Hiển thị audio đã duyệt với số lượt xem và nút "Copy link" |
| 5. Click tab "Từ chối" | 6. Hiển thị audio bị từ chối với lý do từ admin |

### Luồng chính - Copy link

| Hành động của người dùng | Hành động của hệ thống |
|--------------------------|------------------------|
| 1. Click nút "Copy link" trên audio đã duyệt | 2. Copy link vào clipboard |
| | 3. Thông báo "Đã copy!", nút đổi thành "✓ Đã copy" trong 2 giây |

### Luồng chính - Xem link

| Hành động của người dùng | Hành động của hệ thống |
|--------------------------|------------------------|
| 1. Click nút "Xem" | 2. Mở tab mới với trang chia sẻ công khai |
| | 3. Không tăng lượt xem (vì là chủ sở hữu) |

### Luồng chính - Hủy yêu cầu

| Hành động của người dùng | Hành động của hệ thống |
|--------------------------|------------------------|
| 1. Click nút "Hủy yêu cầu" trên audio chờ duyệt | 2. Hiển thị xác nhận "Bạn có chắc muốn hủy?" |
| 3. Click "Hủy yêu cầu" | 4. Thông báo "Đã hủy", xóa khỏi danh sách |

### Luồng phụ - Tạo lại sau khi bị từ chối

| Hành động của người dùng | Hành động của hệ thống |
|--------------------------|------------------------|
| 1. Đọc lý do từ chối, click "Tạo lại" | 2. Chuyển đến tab TTS với văn bản gốc đã điền sẵn |
| | 3. Hiển thị banner "Vui lòng chỉnh sửa nội dung để phù hợp" |
| 4. Chỉnh sửa và tạo audio mới | 5. Lưu audio mới, có thể gửi yêu cầu chia sẻ lại |

### Ngoại lệ
- Chưa đăng nhập → Chuyển đến trang đăng nhập
- Không tải được danh sách → Hiển thị "Không thể tải dữ liệu"
- Copy link thất bại → Hiển thị "Vui lòng copy thủ công"
- Yêu cầu đã được duyệt → Không thể hủy, thông báo "Đã được duyệt"
- Link không tồn tại → Hiển thị trang 404
- Chưa có yêu cầu nào → Hiển thị "Bạn chưa có yêu cầu chia sẻ nào"

---

## 📊 Tổng kết

### Danh mục (Categories)
- 📚 Giáo dục (education)
- 🎭 Giải trí (entertainment)
- 📰 Tin tức (news)
- 📖 Truyện (story)
- 🎙️ Podcast (podcast)
- 📦 Khác (other)

### Trạng thái chia sẻ (Share Status)
- ⏳ **Chờ duyệt** (pending) - Màu vàng
- ✅ **Đã duyệt** (approved) - Màu xanh
- ❌ **Từ chối** (rejected) - Màu đỏ

### Quyền hạn (Roles)
- **User** - Tạo audio, gửi yêu cầu chia sẻ, quản lý chia sẻ của mình
- **Admin** - Tất cả quyền của User + Duyệt/Từ chối/Xóa yêu cầu chia sẻ

---

**Cập nhật lần cuối:** 09/01/2025  
**Phiên bản:** 1.0
