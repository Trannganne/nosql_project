# STUDIO 3T ĐƯỢC ỨNG DỤNG VÀO PROJECT NHƯ THẾ NÀO?

Studio 3T không phải database và không thay thế MongoDB Server. Đây là GUI Tool kết nối đến MongoDB để con người thiết kế, xem, sửa, truy vấn, phân tích và bảo trì dữ liệu. Website PHP cũng kết nối MongoDB Server. Vì vậy hai công cụ nhìn thấy cùng một dữ liệu.

```text
Web PHP -- MongoDB PHP Driver -- MongoDB Server -- Studio 3T
                                      |
                        database mini_supermarket
```

## 1. Vai trò trong từng giai đoạn

### Thiết kế database

- Dùng Schema Explorer khảo sát cấu trúc và kiểu dữ liệu từng collection.
- Dùng Collection Properties kiểm tra validator.
- Dùng Index Manager xem unique/text/compound index.
- Dùng Visual Query Builder thử filter trước khi đưa logic vào PHP.

### Tạo và kiểm tra dữ liệu

- Mở IntelliShell, nạp `database/setup.js` để tạo database.
- Dùng Table/Tree/JSON View kiểm tra document.
- Sửa một document để minh họa tính linh hoạt, sau đó cho validator chặn document sai.
- Dùng Schema Explorer chứng minh `sold_at` là Date, `total` là Number, `items` là Array.

### Khai thác dữ liệu

- Chạy `queries_basic.js` trong IntelliShell.
- Dựng từng stage của `queries_advanced.js` trong Aggregation Editor.
- Dùng Query Code để chuyển một filter/pipeline sang PHP syntax, sau đó đối chiếu `Repository.php`.
- Dùng `explain("executionStats")` để chứng minh index `idx_invoices_sold_at` được sử dụng.

### Quản trị dữ liệu

- Import/Export Wizard cho JSON/CSV.
- Data Compare & Sync để so database demo và database backup/test (nếu license hỗ trợ).
- Task Manager để lưu và chạy lại query/export (tùy edition).
- `mongodump`/`mongorestore` để backup BSON đầy đủ; website gọi hai công cụ này qua trang Backup/Restore.

## 2. Chức năng Studio 3T cần trình bày

| Chức năng | Cách ứng dụng trong đề tài | Bằng chứng nên chụp |
| --- | --- | --- |
| Connection Manager | Kết nối local/Atlas, cấu hình replica set | Test Connection thành công |
| Data Explorer | Xem/sửa document ở Table, Tree, JSON | `invoices.items[]` |
| IntelliShell | Chạy CRUD và aggregation bằng MongoDB Query Language | Kết quả truy vấn cơ bản |
| Visual Query Builder | Tạo filter bằng kéo thả | Sản phẩm tồn thấp |
| Aggregation Editor | Chạy/tách từng stage pipeline | Doanh thu tháng, top sản phẩm |
| Query Code | Sinh code truy vấn cho PHP | Filter/pipeline PHP |
| Schema Explorer | Phân tích kiểu field và mức độ xuất hiện | Schema collection invoices |
| Index Manager | Tạo/xem/xóa index | Unique index và compound index |
| Import/Export Wizard | Trao đổi JSON/CSV với hệ thống khác | File products.json |
| Data Compare & Sync | So sánh hai database/collection | DB chính và DB test |
| SQL Query (nếu edition hỗ trợ) | Hỗ trợ người quen SQL đọc dữ liệu MongoDB | Một SELECT minh họa; không dùng làm logic chính |

## 3. Kịch bản truy vấn trên GUI Tool

### Truy vấn cơ bản

1. Mở IntelliShell của connection `MiniSupermarket Local`.
2. Mở file `database/queries_basic.js`.
3. Chạy từng lệnh, không chạy cả file khi demo CRUD vì lệnh cuối có thêm/sửa/xóa.
4. Giải thích filter, projection, sort và toán tử `$gte`, `$lte`, `$regex`, `$expr`.
5. Refresh collection ở Data Explorer để thấy kết quả.

### Truy vấn nâng cao

1. Nhấp phải `invoices` > Open Aggregation Editor.
2. Dán từng stage của pipeline top sản phẩm: `$match` > `$unwind` > `$group` > `$sort` > `$limit`.
3. Sau mỗi stage, xem preview và giải thích document thay đổi thế nào.
4. Lưu pipeline tên `BaoCaoTopSanPham` nếu edition cho phép.
5. Chạy pipeline `$facet` để chứng minh trả nhiều nhóm báo cáo trong một lượt quét dữ liệu.

### Kiểm tra index

1. Mở Index Manager của `invoices`, chỉ ra `idx_invoices_sold_at`.
2. Chạy truy vấn theo tháng có `.explain("executionStats")`.
3. Chỉ ra `winningPlan` có `IXSCAN`, `totalDocsExamined` và `nReturned`.
4. Giải thích nếu thấy `COLLSCAN`: query/index chưa phù hợp hoặc dữ liệu quá ít nên optimizer chọn quét collection.

## 4. Demo validator

Trong IntelliShell, thử thêm sản phẩm có `stock` âm:

```javascript
db.products.insertOne({
  code: "SP_BAD", name: "Sai dữ liệu", category_code: "L001",
  supplier_code: "NCC001", unit: "cái", stock: -5, min_stock: 2,
  purchase_price: 1000, sale_price: 2000, active: true
});
```

MongoDB phải báo lỗi document failed validation. Đây là bằng chứng database tự bảo vệ dữ liệu, không chỉ phụ thuộc form PHP.

## 5. So sánh Studio 3T với MongoDB Compass

| Tiêu chí | Studio 3T | MongoDB Compass |
| --- | --- | --- |
| Chi phí | Có trial và các edition trả phí | Miễn phí |
| IntelliShell | Mạnh, quản lý nhiều tab/script | Có mongosh tích hợp tùy phiên bản |
| Aggregation | Editor mạnh, preview từng stage | Pipeline Builder tốt, trực quan |
| Visual Query Builder | Nổi bật, phù hợp người mới | Filter bar/GUI đơn giản hơn |
| Query Code | Sinh code nhiều ngôn ngữ, hữu ích khi viết PHP | Có export pipeline/code nhưng phạm vi tùy bản |
| Schema | Schema Explorer chuyên sâu | Schema visualization cơ bản, dễ dùng |
| Import/Export | Nhiều tùy chọn, workflow chuyên nghiệp | Đủ cho JSON/CSV phổ biến |
| Compare/Sync | Điểm mạnh, phù hợp quản trị nhiều môi trường | Không mạnh bằng Studio 3T |
| Độ dễ dùng | Nhiều chức năng nên cần thời gian học | Gọn và phù hợp người mới |
| Phù hợp | Dev/DBA cần truy vấn, compare, automation | Học tập, xem dữ liệu, CRUD nhanh |

Kết luận: Studio 3T mạnh hơn khi dự án cần khai thác query phức tạp, phân tích schema, sinh code và so sánh/đồng bộ môi trường. Compass phù hợp hơn nếu cần GUI miễn phí, chính chủ và thao tác cơ bản.

## 6. Studio 3T mạnh với loại ứng dụng nào?

- Thương mại/bán lẻ: hóa đơn có danh sách mặt hàng, dashboard và phân tích doanh thu.
- E-commerce/catalog: thuộc tính sản phẩm linh hoạt, tìm kiếm và aggregation.
- CMS/log/event: document không hoàn toàn đồng nhất, cần Schema Explorer.
- IoT/telemetry: dữ liệu lớn theo thời gian, cần index và query performance.
- Ứng dụng nhiều môi trường dev/test/prod: cần Compare & Sync, import/export và task lặp lại.

Không nên chọn Studio 3T chỉ để thay thế hoàn toàn logic ứng dụng. Quy tắc nghiệp vụ vẫn nằm trong PHP; Studio 3T hỗ trợ thiết kế, kiểm chứng, khai thác và quản trị dữ liệu.

## 7. Lỗi kết nối thường gặp

| Lỗi | Nguyên nhân | Cách xử lý |
| --- | --- | --- |
| Connection refused | MongoDB service chưa chạy/sai port | Mở Services, start MongoDB; kiểm tra 27017 |
| Authentication failed | Sai user/password/authSource | Kiểm tra Authentication DB thường là `admin` |
| Server selection timeout | Firewall, host sai, service lỗi | Thử `mongosh` cùng URI trước |
| ReplicaSetNoPrimary | `rs0` chưa initiate hoặc URI sai | Chạy `rs.status()`, `rs.initiate()` |
| Web có dữ liệu nhưng Studio 3T không có | Khác URI/database | So `.env` với Connection Properties |
| Transaction not supported | MongoDB standalone | Bật replica set một node theo README |
