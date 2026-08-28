// Dán từng truy vấn vào IntelliShell của Studio 3T.
use("mini_supermarket");

// 1. Hiển thị sản phẩm đang kinh doanh.
db.products.find({active:true},{_id:0,code:1,name:1,stock:1,sale_price:1}).sort({name:1});

// 2. Tìm gần đúng theo tên, không phân biệt hoa thường.
db.products.find({name:{$regex:"sữa",$options:"i"}});

// 3. Sản phẩm giá từ 20.000 đến 100.000 đồng.
db.products.find({sale_price:{$gte:20000,$lte:100000}}).sort({sale_price:1});

// 4. Sản phẩm sắp hết hàng.
db.products.find({$expr:{$lte:["$stock","$min_stock"]}},{code:1,name:1,stock:1,min_stock:1});

// 5. Thêm, sửa, xóa một bản ghi minh họa.
db.categories.insertOne({code:"L999",name:"Dữ liệu demo",description:"Bản ghi dùng khi trình bày",active:true,created_at:new Date(),updated_at:new Date()});
db.categories.updateOne({code:"L999"},{$set:{name:"Dữ liệu demo đã sửa",updated_at:new Date()}});
db.categories.deleteOne({code:"L999"});

// 6. Đếm sản phẩm theo loại.
db.products.aggregate([{$group:{_id:"$category_code",quantity:{$sum:1}}},{$sort:{quantity:-1}}]);
