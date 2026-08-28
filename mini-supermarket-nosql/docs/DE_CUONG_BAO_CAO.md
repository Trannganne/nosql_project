# ĐỀ CƯƠNG BÁO CÁO 50-60 TRANG

Barem yêu cầu Times New Roman 13, giãn dòng 1.5, tổng 50-60 trang gồm lý thuyết, thiết kế database, ứng dụng demo và tài liệu tham khảo. Số trang dưới đây là phân bổ đề xuất, không tính bìa/phụ lục tùy quy định khoa.

| Phần | Nội dung | Trang dự kiến |
| --- | --- | ---: |
| Mở đầu | Lý do, mục tiêu, phạm vi, phương pháp, phân công | 3-4 |
| Chương 1 | Tổng quan NoSQL, document database, MongoDB | 7-8 |
| Chương 2 | Studio 3T: cài đặt, chức năng, so sánh Compass, ứng dụng phù hợp | 10-12 |
| Chương 3 | Phân tích bài toán quản lý siêu thị mini | 6-7 |
| Chương 4 | Thiết kế MongoDB: collection, embed/reference, validator, index | 8-9 |
| Chương 5 | Xây dựng web PHP và kết nối MongoDB | 8-9 |
| Chương 6 | Truy vấn, import/export, backup/restore và demo Studio 3T | 7-8 |
| Chương 7 | Kiểm thử, kết quả, hạn chế và hướng phát triển | 4-5 |
| Kết luận + tài liệu tham khảo | Kết quả và nguồn | 2-3 |
| Tổng |  | 55-65; cần biên tập về 50-60 |

## Nội dung chi tiết

### Mở đầu

1. Lý do chọn đề tài.
2. Mục tiêu: tìm hiểu Studio 3T, xây dựng CSDL tài liệu và ứng dụng minh họa.
3. Phạm vi: cửa hàng đơn, quản lý danh mục/kho/bán hàng; chưa làm chuỗi chi nhánh.
4. Đối tượng nghiên cứu: MongoDB, Studio 3T, PHP MongoDB Library.
5. Phương pháp: nghiên cứu tài liệu, phân tích project cũ, thiết kế lại, cài đặt, thử nghiệm.
6. Bảng phân công và tiến độ.

### Chương 1. Cơ sở lý thuyết

1. NoSQL là gì, động cơ ra đời.
2. Bốn nhóm chính: key-value, document, column-family, graph.
3. CAP và eventual consistency ở mức phù hợp môn học.
4. MongoDB: database, collection, document, BSON, `_id`.
5. CRUD, query operator, aggregation pipeline.
6. Index, validation, transaction, backup/restore.
7. Ưu/nhược điểm MongoDB so với SQL Server trong bài toán này.

### Chương 2. Studio 3T

1. Giới thiệu và yêu cầu cài đặt.
2. Kết nối local/Atlas/replica set.
3. Trình bày từng chức năng ở `HUONG_DAN_STUDIO_3T.md` kèm ảnh tự chụp.
4. Bảng so sánh Studio 3T và Compass.
5. Điểm mạnh, điểm yếu, ứng dụng phù hợp.
6. Vai trò Studio 3T trong vòng đời project.

### Chương 3. Phân tích bài toán

1. Hiện trạng project WinForms/SQL Server.
2. Tác nhân: admin, nhân viên.
3. Yêu cầu chức năng và phi chức năng.
4. Quy trình quản lý sản phẩm, khách hàng, bán hàng, báo cáo, backup.
5. Use case tổng quát và đặc tả use case chính.

### Chương 4. Thiết kế database

1. Chuyển bảng sang collection.
2. Sơ đồ quan hệ logic giữa collection.
3. Cấu trúc document mẫu cho từng collection.
4. Lý do embed `items` trong invoice; lý do reference category/supplier.
5. Validator và quy tắc toàn vẹn.
6. Danh sách index, query phục vụ và đánh đổi chi phí ghi.
7. Dữ liệu mẫu và thống kê số lượng.

### Chương 5. Xây dựng ứng dụng

1. Công nghệ và cấu trúc thư mục.
2. Cài PHP extension và Composer package.
3. `.env` và luồng kết nối.
4. Đăng nhập/phân quyền/bảo mật.
5. CRUD từng chức năng.
6. Nghiệp vụ hóa đơn, transaction và chống tồn kho âm.
7. Dashboard/báo cáo aggregation.
8. Backup/restore trên web.

### Chương 6. Khai thác bằng Studio 3T

1. Chạy setup và seed.
2. Truy vấn cơ bản: trình bày câu lệnh, kết quả, ý nghĩa.
3. Truy vấn nâng cao: vẽ pipeline, giải thích từng stage.
4. Import/export JSON/CSV.
5. Backup/restore BSON.
6. Schema Explorer và Index Manager.
7. `explain` trước/sau index.

### Chương 7. Kiểm thử và đánh giá

1. Test đăng nhập/quyền.
2. Test validation biểu mẫu/database.
3. Test CRUD và tìm kiếm.
4. Test bán quá tồn kho và tạo hóa đơn hợp lệ.
5. Test backup/restore trên database phụ.
6. Đối chiếu kết quả web với Studio 3T.
7. Hạn chế: chưa quản lý lô/hạn dùng, nhập hàng, nhiều chi nhánh.
8. Hướng phát triển: phiếu nhập, cảnh báo hạn dùng, barcode, biểu đồ, audit log.

## Mẫu phân công ba thành viên

| Thành viên | Công việc chính | Minh chứng |
| --- | --- | --- |
| 1 | Lý thuyết MongoDB/Studio 3T, cài đặt, so sánh GUI | Chương 1-2, ảnh demo, commit tài liệu |
| 2 | Phân tích, thiết kế database, seed, query | Chương 3-4 và 6, script database |
| 3 | Web PHP, kiểm thử, tích hợp, đóng gói | Chương 5 và 7, source/commit/test |

Cả nhóm cùng review, chạy demo tích hợp, làm slide và luyện trả lời. Mỗi thành viên phải hiểu toàn hệ thống vì giảng viên chấm điểm cá nhân.

## Checklist nộp

- Báo cáo Word đúng 50-60 trang, Times New Roman 13, line 1.5.
- Slide PowerPoint gọn, có kiến trúc, document mẫu, query và ảnh demo.
- Source code không chứa `.env`, mật khẩu thật, thư mục `vendor` nếu giảng viên không yêu cầu.
- Script `setup.js`, query cơ bản/nâng cao và dữ liệu import.
- File README chạy project từ máy mới.
- Bản backup demo.
- Danh sách nguồn tham khảo; chú thích mọi hình/bảng; không sao chép.
- Lịch sử Git hoặc bảng nhật ký làm việc để chứng minh phân công.
