# QUẢN LÝ SIÊU THỊ MINI - WEB PHP, MONGODB VÀ STUDIO 3T

Ứng dụng web quản lý siêu thị mini (**MiniMart**) được xây dựng bằng PHP 8.1+ và hệ quản trị cơ sở dữ liệu tài liệu MongoDB. Công cụ **Studio 3T** được sử dụng kết hợp để thiết kế, quản trị, truy vấn, kiểm chứng chỉ mục, import/export và backup/restore trực tiếp trên cùng cơ sở dữ liệu `mini_supermarket` mà ứng dụng web vận hành.

## 1. Chức năng đã có

- Đăng nhập, phân quyền `admin` và `staff`, mật khẩu bcrypt.
- Dashboard: số sản phẩm, khách hàng, hóa đơn, hàng sắp hết, tổng doanh thu.
- CRUD loại sản phẩm, nhà cung cấp, sản phẩm, khách hàng và người dùng.
- Tìm sản phẩm/đối tượng theo mã hoặc tên.
- Tạo hóa đơn nhiều mặt hàng, tính giảm giá, kiểm tra và trừ tồn kho nguyên tử.
- Lưu chi tiết hóa đơn trong mảng `items[]` đúng mô hình document.
- Báo cáo doanh thu theo tháng và top sản phẩm bằng Aggregation Pipeline.
- Backup/restore ngay trên web (admin) bằng `mongodump` và `mongorestore`.
- Schema validation, unique index, text index và compound index.
- Script seed dữ liệu; bộ truy vấn cơ bản/nâng cao dùng trực tiếp trong Studio 3T.

## 2. Yêu cầu máy

- Windows 10/11, Laragon có PHP 8.1 trở lên và Apache.
- MongoDB Community Server 7/8 (MongoDB Compass không thay thế MongoDB Server).
- MongoDB Shell `mongosh`.
- MongoDB Database Tools để dùng `mongodump`, `mongorestore`, `mongoexport`, `mongoimport`.
- Studio 3T.
- Composer 2.

## 3. Cài MongoDB extension cho PHP trong Laragon

1. Mở Laragon, chọn **Menu > PHP > Version** để xem phiên bản PHP.
2. Mở Command Prompt của Laragon và chạy `php -i | findstr /I "PHP Version Thread Architecture extension_dir"`.
3. Tải DLL `mongodb` đúng cả 4 thông số: phiên bản PHP, `x64`, `TS/NTS` và Visual C++ từ PECL.
4. Giải nén, chép `php_mongodb.dll` vào thư mục được in ở `extension_dir` (thường là `D:\laragon\bin\php\php-8.x.x\ext`).
5. Mở đúng file `php.ini` mà Laragon đang dùng, thêm dòng `extension=mongodb`.
6. Trong Laragon chọn **Stop All**, sau đó **Start All**.
7. Kiểm tra bằng `php -m | findstr /I mongodb`. Phải thấy dòng `mongodb`.

Nếu CLI nhận extension nhưng website không nhận, Apache và Terminal đang dùng hai bản PHP hoặc hai file `php.ini` khác nhau. Tạo tạm file `phpinfo.php` chứa `<?php phpinfo();` để xem `Loaded Configuration File`, sau đó xóa file này.

## 4. Cài và chạy project

1. Chép thư mục `mini-supermarket-nosql` vào `D:\laragon\www\`.
2. Mở Terminal của Laragon tại thư mục project, chạy:

   ```powershell
   composer install
   copy .env.example .env
   ```

3. Mở `.env` và kiểm tra:

   ```env
   MONGODB_URI=mongodb://127.0.0.1:27017
   MONGODB_DATABASE=mini_supermarket
   ```

4. Khởi tạo database và dữ liệu mẫu:

   ```powershell
   mongosh "mongodb://127.0.0.1:27017" database/setup.js
   ```

5. Mở `http://localhost/mini-supermarket-nosql/public`.
6. Đăng nhập `admin` / `password`. Đổi mật khẩu mẫu trước khi dùng thật.

### Chạy transaction trên máy local

Chức năng tạo hóa đơn dùng transaction để việc trừ tồn kho và ghi hóa đơn cùng thành công hoặc cùng hủy. MongoDB transaction yêu cầu replica set. Với máy demo local, cấu hình replica set một node:

1. Dừng MongoDB service.
2. Thêm `replication: { replSetName: rs0 }` vào file `mongod.cfg` theo cú pháp YAML:

   ```yaml
   replication:
     replSetName: rs0
   ```

3. Khởi động lại MongoDB service.
4. Chạy `mongosh`, sau đó chạy `rs.initiate()` và kiểm tra `rs.status()`.
5. Đổi URI thành `mongodb://127.0.0.1:27017/?replicaSet=rs0` trong `.env` và Studio 3T.

## 5. Kết nối Studio 3T

1. Mở Studio 3T > **Connect** > **New Connection**.
2. Chọn **Manually configure my connection settings**.
3. Đặt tên `MiniSupermarket Local`.
4. Server: `127.0.0.1`; Port: `27017`.
5. Nếu dùng replica set, mở tab **Server**, chọn replica set và nhập `rs0`.
6. Nếu local chưa bật xác thực, chọn `No authentication`. Nếu đã tạo user MongoDB, chọn Username/Password và đúng Authentication DB.
7. Bấm **Test Connection**, sau đó **Save** và **Connect**.
8. Mở database `mini_supermarket`. Website và Studio 3T phải trỏ đến cùng URI/database trong `.env`.

## 6. Import, export, backup và restore

### Trên Studio 3T

- Import: nhấp phải collection > **Import Collection** > chọn JSON/CSV > ánh xạ trường > Run.
- Export: nhấp phải collection hoặc kết quả truy vấn > **Export Collection/Query Results** > JSON/CSV.
- Backup: nhấp phải database > **Export/Backup** hoặc mở Task Manager và tạo task dùng `mongodump` (tùy edition Studio 3T).
- Restore: dùng Import Wizard cho JSON hoặc `mongorestore` cho bản BSON đầy đủ.

### Dòng lệnh

```powershell
mongoexport --uri="mongodb://127.0.0.1:27017" --db=mini_supermarket --collection=products --out=products.json --jsonArray
mongoimport --uri="mongodb://127.0.0.1:27017" --db=mini_supermarket --collection=products --file=products.json --jsonArray --mode=upsert --upsertFields=code
mongodump --uri="mongodb://127.0.0.1:27017" --db=mini_supermarket --out=backup
mongorestore --uri="mongodb://127.0.0.1:27017" --db=mini_supermarket --drop backup/mini_supermarket
```

## 7. Cấu trúc project

```text
app/                  Kết nối, xác thực, repository, hóa đơn, backup
database/setup.js     Tạo database, validation, index và seed dữ liệu mẫu
database/queries_*    Truy vấn cơ bản và nâng cao chạy trên IntelliShell / Aggregation Editor
public/index.php      Front controller và giao diện web
public/assets/        CSS, logo và hình ảnh sản phẩm
storage/backups/      Thư mục chứa các bản sao lưu BSON do website tạo
```

## 8. Tài khoản mẫu

| Quyền | Tài khoản | Mật khẩu |
| --- | --- | --- |
| Admin | `admin` | `password` |
| Nhân viên | `staff` | `password` |
