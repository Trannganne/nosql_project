# CÁC THAY ĐỔI TỪ PROJECT .NET SANG NOSQL WEB PHP

## 1. Thay đổi kiến trúc

| Project cũ | Project mới | Lý do |
| --- | --- | --- |
| WinForms .NET Framework 4.8 | Web PHP 8.1+ chạy Laragon | Đúng nền tảng web PHP theo yêu cầu |
| Form `.cs` và Designer | Front controller PHP + HTML/CSS responsive | Dùng được trên trình duyệt, dễ demo |
| SQL Server/LocalDB | MongoDB document database | Đúng môn Dữ liệu NoSQL |
| `SqlConnection`, `SqlCommand`, `DataTable` | MongoDB PHP Library, Collection, BSON Document | API truy cập MongoDB chính thức |
| SQL ghép chuỗi | Filter/update document dạng mảng | Loại bỏ SQL injection |
| DataSet và RDLC | Aggregation Pipeline và bảng báo cáo web | Báo cáo trực tiếp từ MongoDB |
| Cấu hình connection hard-code tên máy | `.env` | Chuyển máy không cần sửa source |
| Mật khẩu rõ trong `tblNhanVien` | `password_hash()` / `password_verify()` | Bảo mật đúng cách |
| Không CSRF, session đơn giản | Session regenerate + CSRF token + HTML escaping | Giảm session fixation, CSRF, XSS |

## 2. Chuyển đổi dữ liệu

| SQL Server cũ | MongoDB mới | Cách mô hình hóa |
| --- | --- | --- |
| `tblLoaiSanPham` | `categories` | Collection riêng vì dùng chung và cần CRUD |
| `tblNhaCungcap` | `suppliers` | Collection riêng |
| `tblHang` | `products` | Tham chiếu bằng `category_code`, `supplier_code` |
| `tblKhach` | `customers` | Collection riêng |
| `tblNhanVien` | `users` | Gộp nhân viên và tài khoản; thêm `role`, `active`, hash mật khẩu |
| `tblHDBan` + `tblChiTietHDBan` | `invoices` với `items[]` | Nhúng chi tiết vào hóa đơn vì luôn được đọc cùng hóa đơn |

Hóa đơn lưu snapshot `product_code`, `product_name`, `unit_price` trong `items[]`. Khi tên/giá sản phẩm thay đổi, hóa đơn cũ vẫn đúng tại thời điểm bán. Đây là điểm mạnh của mô hình document so với việc lúc nào cũng join bảng hiện tại.

## 3. Chức năng giữ lại và bổ sung

- Giữ lại: đăng nhập, sản phẩm, loại, nhà cung cấp, khách hàng, nhân viên/người dùng, hóa đơn, tìm kiếm, báo cáo.
- Bổ sung: dashboard KPI, hàng sắp hết, phân quyền admin/staff, backup/restore trên web, validation, index, báo cáo doanh thu tháng, top sản phẩm.
- Sửa nghiệp vụ hóa đơn: trừ kho theo điều kiện `stock >= quantity`, dùng transaction để tránh bán âm kho khi hai yêu cầu chạy đồng thời.
- Loại bỏ phụ thuộc RDLC, Microsoft Office Interop, SQL Server Types và file `.mdf`.

## 4. Các file cũ không tái sử dụng

- Toàn bộ `*.Designer.cs`, `*.resx`, `QL_shopsaleDataSet.*`, `Report1.rdlc`, `App.config`.
- `Class/Functions.cs` vì chứa connection SQL Server hard-code và hàm chạy câu SQL chuỗi.
- `QL_SieuThiMini.sql` chỉ được dùng để đối chiếu nghiệp vụ/dữ liệu, không chạy trong project mới.
- Thư mục `packages/` của NuGet không cần cho PHP.

## 5. Các thay đổi dữ liệu bắt buộc

- Tên field chuyển sang tiếng Anh, `snake_case`, nhất quán.
- Tiền và số lượng lưu bằng kiểu số, ngày lưu bằng BSON Date, không lưu chuỗi định dạng.
- Tất cả collection có validation; mã nghiệp vụ có unique index.
- Dữ liệu mẫu tăng từ 5 sản phẩm, 3 khách hàng, 2 hóa đơn lên 30 sản phẩm, 10 khách hàng, 36 hóa đơn.
- Dữ liệu có nhiều tháng và nhiều mặt hàng để aggregation tạo kết quả có ý nghĩa.
