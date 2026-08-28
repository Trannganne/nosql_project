# KỊCH BẢN DEMO 12-15 PHÚT

## Trước khi trình bày

- Start MongoDB và Laragon; mở sẵn web, Studio 3T, IntelliShell và Aggregation Editor.
- Kiểm tra đăng nhập `admin/password`.
- Tạo sẵn một bản backup, nhưng vẫn demo tạo bản mới.
- Không chạy lại `setup.js` nếu đang cần giữ dữ liệu vừa demo.

## Trình tự

1. **1 phút - Tổng quan:** nêu đề tài, kiến trúc PHP - MongoDB - Studio 3T.
2. **2 phút - Thiết kế:** mở `invoices` dạng JSON, giải thích embed `items[]`; mở validator và index.
3. **2 phút - GUI Tool:** giới thiệu Data Explorer, IntelliShell, Aggregation Editor, Schema Explorer, Import/Export.
4. **2 phút - Truy vấn:** chạy sản phẩm tồn thấp; chạy pipeline top sản phẩm hoặc doanh thu tháng; chỉ ra kết quả từng stage.
5. **4 phút - Web:** đăng nhập, CRUD sản phẩm, tạo hóa đơn, xem tồn kho giảm, dashboard/báo cáo cập nhật.
6. **1 phút - Đồng bộ:** refresh Studio 3T để thấy hóa đơn web vừa tạo.
7. **1 phút - Backup:** tạo backup trên web, nêu restore có xác nhận và chỉ admin được dùng.
8. **1 phút - Kết luận:** lợi ích, hạn chế, hướng phát triển.

## Câu hỏi dễ gặp

### Vì sao dùng MongoDB thay SQL Server?

Hóa đơn và chi tiết hóa đơn có quan hệ một-nhiều nhưng thường đọc cùng nhau. MongoDB cho phép nhúng `items[]`, giảm join và giữ snapshot tên/giá tại thời điểm bán. Danh mục/NCC vẫn tách collection vì dùng chung và thay đổi độc lập.

### Studio 3T có phải cơ sở dữ liệu không?

Không. MongoDB Server mới lưu dữ liệu. Studio 3T là GUI Tool quản trị và khai thác MongoDB; website PHP và Studio 3T cùng kết nối một server/database.

### MongoDB không có khóa ngoại thì bảo đảm dữ liệu thế nào?

Database dùng schema validator, unique index; ứng dụng kiểm tra mã liên quan trước khi ghi. Hóa đơn nhúng snapshot để giảm phụ thuộc. Nếu cần toàn vẹn nhiều document khi bán hàng, dùng transaction trên replica set.

### Tại sao cần index?

Unique index chống trùng mã. Index ngày bán hỗ trợ báo cáo theo khoảng thời gian. Compound index hỗ trợ lọc theo loại/NCC và sản phẩm trong hóa đơn. Đổi lại, index tốn dung lượng và làm ghi chậm hơn.

### Backup khác export JSON thế nào?

Export JSON/CSV phù hợp trao đổi hoặc đọc dữ liệu. `mongodump` tạo BSON và metadata/index để khôi phục database đầy đủ bằng `mongorestore`.

### Vì sao không nhúng category và supplier hoàn toàn vào product?

Tên/thông tin NCC được nhiều sản phẩm dùng chung và cần sửa tập trung. Dùng reference bằng code tránh lặp nhiều. Riêng invoice cần lịch sử bất biến nên nhúng snapshot sản phẩm.

### Project đã xử lý hai người bán cùng sản phẩm chưa?

Có. Update kho dùng điều kiện `stock >= quantity` và `$inc` nguyên tử, toàn bộ trừ kho/ghi hóa đơn nằm trong transaction. Một yêu cầu sẽ thất bại nếu tồn kho vừa thay đổi.
