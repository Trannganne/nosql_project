// Dán từng pipeline vào IntelliShell hoặc Aggregation Editor của Studio 3T.
use("mini_supermarket");

// 1. Join sản phẩm - loại - nhà cung cấp bằng hai $lookup.
db.products.aggregate([
  {$lookup:{from:"categories",localField:"category_code",foreignField:"code",as:"category"}},
  {$lookup:{from:"suppliers",localField:"supplier_code",foreignField:"code",as:"supplier"}},
  {$set:{category:{$first:"$category.name"},supplier:{$first:"$supplier.name"}}},
  {$project:{_id:0,code:1,name:1,category:1,supplier:1,stock:1,sale_price:1}},
  {$sort:{category:1,name:1}}
]);

// 2. Doanh thu và số hóa đơn theo tháng.
db.invoices.aggregate([
  {$match:{status:"completed",sold_at:{$gte:ISODate("2026-01-01"),$lt:ISODate("2027-01-01")}}},
  {$group:{_id:{$dateToString:{format:"%Y-%m",date:"$sold_at"}},orders:{$sum:1},revenue:{$sum:"$total"},average_order:{$avg:"$total"}}},
  {$sort:{_id:1}}
]);

// 3. Top 10 sản phẩm bán chạy và doanh thu tương ứng.
db.invoices.aggregate([
  {$match:{status:"completed"}},{$unwind:"$items"},
  {$group:{_id:"$items.product_code",name:{$first:"$items.product_name"},quantity:{$sum:"$items.quantity"},revenue:{$sum:"$items.line_total"}}},
  {$sort:{quantity:-1}},{$limit:10}
]);

// 4. Phân hạng khách hàng theo tổng chi tiêu bằng $setWindowFields.
db.invoices.aggregate([
  {$match:{status:"completed",customer:{$ne:null}}},
  {$group:{_id:"$customer.code",name:{$first:"$customer.name"},spending:{$sum:"$total"},orders:{$sum:1}}},
  {$setWindowFields:{sortBy:{spending:-1},output:{rank:{$rank:{}}}}},
  {$set:{segment:{$switch:{branches:[{case:{$gte:["$spending",1000000]},then:"VIP"},{case:{$gte:["$spending",500000]},then:"Thân thiết"}],default:"Thường"}}}},
  {$project:{_id:0,customer_code:"$_id",name:1,spending:1,orders:1,rank:1,segment:1}}
]);

// 5. Thống kê doanh thu, lợi nhuận ước tính theo loại sản phẩm.
db.invoices.aggregate([
  {$match:{status:"completed"}},{$unwind:"$items"},
  {$lookup:{from:"products",localField:"items.product_code",foreignField:"code",as:"product"}},
  {$set:{product:{$first:"$product"}}},
  {$group:{_id:"$product.category_code",revenue:{$sum:"$items.line_total"},cost:{$sum:{$multiply:["$product.purchase_price","$items.quantity"]}}}},
  {$set:{estimated_profit:{$subtract:["$revenue","$cost"]}}},{$sort:{estimated_profit:-1}}
]);

// 6. Facet: một lần chạy trả về KPI, top sản phẩm và doanh thu tháng.
db.invoices.aggregate([{$match:{status:"completed"}},{$facet:{
  kpi:[{$group:{_id:null,revenue:{$sum:"$total"},orders:{$sum:1},average:{$avg:"$total"}}}],
  topProducts:[{$unwind:"$items"},{$group:{_id:"$items.product_code",name:{$first:"$items.product_name"},quantity:{$sum:"$items.quantity"}}},{$sort:{quantity:-1}},{$limit:5}],
  monthly:[{$group:{_id:{$dateToString:{format:"%Y-%m",date:"$sold_at"}},revenue:{$sum:"$total"}}},{$sort:{_id:1}}]
}}]);

// 7. Kiểm tra index đã dùng cho tìm hóa đơn theo khoảng ngày.
db.invoices.find({sold_at:{$gte:ISODate("2026-06-01"),$lt:ISODate("2026-07-01")}}).explain("executionStats");
