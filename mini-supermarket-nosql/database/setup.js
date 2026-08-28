// Chạy bằng mongosh: mongosh "mongodb://127.0.0.1:27017" database/setup.js
const dbName = "mini_supermarket";
const appDb = db.getSiblingDB(dbName);
appDb.dropDatabase();

const validators = {
  categories: {$jsonSchema:{bsonType:"object",required:["code","name","active"],properties:{code:{bsonType:"string"},name:{bsonType:"string"},description:{bsonType:"string"},active:{bsonType:"bool"}}}},
  suppliers: {$jsonSchema:{bsonType:"object",required:["code","name","phone","address","active"],properties:{code:{bsonType:"string"},name:{bsonType:"string"},phone:{bsonType:"string"},email:{bsonType:"string"},address:{bsonType:"string"},active:{bsonType:"bool"}}}},
  products: {$jsonSchema:{bsonType:"object",required:["code","name","category_code","supplier_code","unit","stock","min_stock","purchase_price","sale_price","active"],properties:{code:{bsonType:"string"},name:{bsonType:"string"},category_code:{bsonType:"string"},supplier_code:{bsonType:"string"},unit:{bsonType:"string"},stock:{bsonType:["int","long","double","decimal"],minimum:0},min_stock:{bsonType:["int","long","double","decimal"],minimum:0},purchase_price:{bsonType:["int","long","double","decimal"],minimum:0},sale_price:{bsonType:["int","long","double","decimal"],minimum:0},active:{bsonType:"bool"}}}},
  customers: {$jsonSchema:{bsonType:"object",required:["code","name","phone","points","active"],properties:{code:{bsonType:"string"},name:{bsonType:"string"},phone:{bsonType:"string"},address:{bsonType:"string"},points:{bsonType:["int","long"],minimum:0},active:{bsonType:"bool"}}}},
  users: {$jsonSchema:{bsonType:"object",required:["code","name","username","password_hash","role","active"],properties:{code:{bsonType:"string"},name:{bsonType:"string"},username:{bsonType:"string"},password_hash:{bsonType:"string"},role:{enum:["admin","staff"]},active:{bsonType:"bool"}}}},
  invoices: {$jsonSchema:{bsonType:"object",required:["code","sold_at","employee","items","total","status"],properties:{code:{bsonType:"string"},sold_at:{bsonType:"date"},employee:{bsonType:"object",required:["code","name"]},customer:{bsonType:["object","null"]},items:{bsonType:"array",minItems:1,items:{bsonType:"object",required:["product_code","product_name","quantity","unit_price","line_total"]}},total:{bsonType:["int","long","double","decimal"],minimum:0},status:{enum:["completed","cancelled"]}}}}
};

Object.entries(validators).forEach(([name, validator]) => appDb.createCollection(name, {validator, validationLevel:"strict", validationAction:"error"}));

appDb.categories.insertMany([
  {code:"L001",name:"Nước giải khát",description:"Nước đóng chai, nước ngọt",active:true},
  {code:"L002",name:"Sữa",description:"Sữa tươi và sữa chua",active:true},
  {code:"L003",name:"Mì và thực phẩm khô",description:"Mì, gạo và đồ khô",active:true},
  {code:"L004",name:"Gia vị",description:"Nước mắm, dầu ăn, đường",active:true},
  {code:"L005",name:"Hóa mỹ phẩm",description:"Sản phẩm vệ sinh gia đình",active:true},
  {code:"L006",name:"Bánh kẹo",description:"Bánh, kẹo, snack",active:true}
]);
appDb.suppliers.insertMany([
  {code:"NCC001",name:"Vinamilk",phone:"02854155555",email:"contact@vinamilk.com.vn",address:"TP.HCM",active:true},
  {code:"NCC002",name:"Masan Consumer",phone:"02862563862",email:"contact@masanconsumer.com",address:"TP.HCM",active:true},
  {code:"NCC003",name:"Tân Hiệp Phát",phone:"02743755066",email:"info@thp.com.vn",address:"Bình Dương",active:true},
  {code:"NCC004",name:"Acecook Việt Nam",phone:"02838154064",email:"info@acecookvietnam.com",address:"TP.HCM",active:true},
  {code:"NCC005",name:"Unilever Việt Nam",phone:"02854135686",email:"contact@unilever.com",address:"TP.HCM",active:true}
]);

const names=["Nước suối 500ml","Trà xanh 0 độ","Nước tăng lực","Nước ngọt cola","Sữa tươi có đường","Sữa chua ăn","Sữa đặc","Sữa đậu nành","Mì Hảo Hảo","Mì Omachi","Gạo ST25 5kg","Bún khô","Dầu ăn 1L","Nước mắm 500ml","Đường tinh luyện 1kg","Hạt nêm 400g","Nước rửa chén","Bột giặt 3kg","Nước lau sàn","Dầu gội 650g","Snack khoai tây","Bánh quy bơ","Kẹo mềm trái cây","Bánh gạo","Cà phê hòa tan","Trà túi lọc","Khăn giấy","Giấy vệ sinh","Tương ớt","Nước tương"];
const category=["L001","L001","L001","L001","L002","L002","L002","L002","L003","L003","L003","L003","L004","L004","L004","L004","L005","L005","L005","L005","L006","L006","L006","L006","L001","L001","L005","L005","L004","L004"];
const supplier=["NCC003","NCC003","NCC003","NCC003","NCC001","NCC001","NCC001","NCC001","NCC004","NCC004","NCC002","NCC002","NCC002","NCC002","NCC002","NCC002","NCC005","NCC005","NCC005","NCC005","NCC002","NCC002","NCC002","NCC002","NCC002","NCC002","NCC005","NCC005","NCC002","NCC002"];
const products=names.map((name,i)=>({code:`SP${String(i+1).padStart(3,"0")}`,name,category_code:category[i],supplier_code:supplier[i],unit:i===10?"túi":"sản phẩm",stock:i%7===0?4:25+(i*7)%80,min_stock:8,purchase_price:6000+i*1700,sale_price:8000+i*2200,active:true,created_at:new Date(),updated_at:new Date()}));
appDb.products.insertMany(products);

const customerNames=["Nguyễn Minh Anh","Trần Hoàng Nam","Lê Thị Hương","Phạm Gia Bảo","Võ Thu Trang","Đặng Quốc Huy","Bùi Thanh Hà","Huỳnh Văn Khang","Đỗ Ngọc Lan","Hồ Nhật Minh"];
appDb.customers.insertMany(customerNames.map((name,i)=>({code:`KH${String(i+1).padStart(3,"0")}`,name,phone:`090${String(1234567+i*731).padStart(7,"0")}`,address:`Quận ${(i%10)+1}, TP.HCM`,points:i*15,active:true,created_at:new Date(),updated_at:new Date()})));

// Hash bcrypt tương ứng mật khẩu "password". Đổi ngay sau lần đăng nhập đầu.
appDb.users.insertMany([
  {code:"NV001",name:"Quản trị hệ thống",username:"admin",password_hash:"$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.",role:"admin",active:true,created_at:new Date(),updated_at:new Date()},
  {code:"NV002",name:"Nhân viên bán hàng",username:"staff",password_hash:"$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.",role:"staff",active:true,created_at:new Date(),updated_at:new Date()}
]);

const admin={code:"NV001",name:"Quản trị hệ thống"};
const customers=appDb.customers.find().toArray();
for(let n=1;n<=36;n++){
  const chosen=[products[n%30],products[(n*3)%30],products[(n*7)%30]].slice(0,2+n%2);
  const items=chosen.map((p,j)=>{const q=1+(n+j)%4,discount=(n+j)%3===0?5:0,line=p.sale_price*q*(1-discount/100);return{product_code:p.code,product_name:p.name,quantity:q,unit_price:p.sale_price,discount_percent:discount,line_total:line};});
  const total=items.reduce((s,x)=>s+x.line_total,0);
  const soldAt=new Date(2026,(n-1)%8,1+(n*3)%26,8+n%10,15);
  const c=customers[n%customers.length];
  appDb.invoices.insertOne({code:`HD2026${String(n).padStart(4,"0")}`,sold_at:soldAt,employee:admin,customer:{id:c._id,code:c.code,name:c.name},items,subtotal:total,total,payment_method:n%2?"cash":"bank_transfer",status:"completed",created_at:soldAt});
}

appDb.categories.createIndex({code:1},{unique:true,name:"uq_categories_code"});
appDb.suppliers.createIndex({code:1},{unique:true,name:"uq_suppliers_code"});
appDb.products.createIndex({code:1},{unique:true,name:"uq_products_code"});
appDb.products.createIndex({name:"text"},{name:"txt_products_name",default_language:"none"});
appDb.products.createIndex({category_code:1,supplier_code:1},{name:"idx_products_category_supplier"});
appDb.products.createIndex({stock:1,min_stock:1},{name:"idx_products_stock"});
appDb.customers.createIndex({code:1},{unique:true,name:"uq_customers_code"});
appDb.customers.createIndex({phone:1},{unique:true,name:"uq_customers_phone"});
appDb.users.createIndex({username:1},{unique:true,name:"uq_users_username"});
appDb.invoices.createIndex({code:1},{unique:true,name:"uq_invoices_code"});
appDb.invoices.createIndex({sold_at:-1},{name:"idx_invoices_sold_at"});
appDb.invoices.createIndex({"items.product_code":1,sold_at:-1},{name:"idx_invoices_product_date"});
printjson({database:dbName,collections:appDb.getCollectionNames(),products:appDb.products.countDocuments(),invoices:appDb.invoices.countDocuments(),message:"Khởi tạo thành công"});
