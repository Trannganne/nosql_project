<?php

declare(strict_types=1);

use App\Auth;
use App\BackupService;
use App\Database;
use App\InvoiceService;
use App\Repository;
use App\Security;
use Dotenv\Dotenv;

require dirname(__DIR__) . '/vendor/autoload.php';
if (file_exists(dirname(__DIR__) . '/.env')) Dotenv::createImmutable(dirname(__DIR__))->safeLoad();
session_start();

function redirect(string $page): never
{
    header('Location: index.php?page=' . urlencode($page));
    exit;
}
function flash(string $message, string $type = 'success'): void
{
    $_SESSION['flash'] = compact('message', 'type');
}
function val(object|array|null $doc, string $key, mixed $default = ''): mixed
{
    return $doc[$key] ?? $default;
}
function money(mixed $value): string
{
    return number_format((float) $value, 0, ',', '.') . ' đ';
}

$page = (string) ($_GET['page'] ?? 'dashboard');
$error = null;
try {
    Database::connection();
    if ($page === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        Security::verifyCsrf();
        Auth::attempt(trim((string) $_POST['username']), (string) $_POST['password']) ? redirect('dashboard') : $error = 'Tên đăng nhập hoặc mật khẩu không đúng.';
    }
    if ($page === 'logout') {
        Auth::logout();
        redirect('login');
    }
    if (!Auth::check() && $page !== 'login') redirect('login');

    $repo = new Repository();
    if (Auth::check() && $_SERVER['REQUEST_METHOD'] === 'POST') {
        Security::verifyCsrf();
        $action = (string) ($_POST['action'] ?? '');
        if ($action === 'save') {
            $collection = (string) $_POST['collection'];
            if ($collection === 'users' && !Auth::isAdmin()) throw new RuntimeException('Chỉ admin được quản trị người dùng.');
            $data = normalizeForm($collection, $_POST);
            $repo->save($collection, $data, $_POST['id'] ?: null);
            flash('Đã lưu dữ liệu thành công.');
            redirect($collection);
        }
        if ($action === 'delete') {
            $collection = (string) $_POST['collection'];
            if ($collection === 'users' && !Auth::isAdmin()) throw new RuntimeException('Chỉ admin được xóa người dùng.');
            $repo->delete($collection, (string) $_POST['id']);
            flash('Đã xóa dữ liệu.');
            redirect($collection);
        }
        if ($action === 'invoice') {
            $code = (new InvoiceService())->create($_POST);
            flash("Đã tạo hóa đơn {$code} và trừ tồn kho.");
            redirect('invoices');
        }
        if ($action === 'backup') {
            if (!Auth::isAdmin()) throw new RuntimeException('Chỉ admin được backup.');
            $name = (new BackupService())->backup();
            flash("Đã tạo {$name}.");
            redirect('backup');
        }
        if ($action === 'restore') {
            if (!Auth::isAdmin()) throw new RuntimeException('Chỉ admin được restore.');
            (new BackupService())->restore((string) $_POST['backup']);
            flash('Đã restore dữ liệu.');
            redirect('backup');
        }
    }
} catch (Throwable $e) {
    $error = $e->getMessage();
}

function handleProductImage(array $post): string
{
    $code = trim((string) ($post['code'] ?? ''));
    if (!empty($_FILES['image_file']['name']) && ($_FILES['image_file']['error'] ?? -1) === UPLOAD_ERR_OK) {
        $file = $_FILES['image_file'];
        $ext = strtolower(pathinfo((string) $file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'];
        if (in_array($ext, $allowed, true)) {
            $filename = ($code !== '' ? preg_replace('/[^a-zA-Z0-9_-]/', '', $code) : 'prod_' . time()) . '.' . $ext;
            $uploadDir = __DIR__ . '/assets/images/products/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            if (move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
                return 'assets/images/products/' . $filename;
            }
        }
    }
    $imageInput = trim((string) ($post['image'] ?? ''));
    if ($imageInput !== '') {
        return $imageInput;
    }
    if ($code !== '' && file_exists(__DIR__ . "/assets/images/products/{$code}.jpg")) {
        return "assets/images/products/{$code}.jpg";
    }
    return 'assets/images/products/default.svg';
}

function normalizeForm(string $collection, array $post): array
{
    $base = ['code' => trim((string) ($post['code'] ?? '')), 'name' => trim((string) ($post['name'] ?? ''))];
    return match ($collection) {
        'products' => $base + [
            'image' => handleProductImage($post),
            'category_code' => trim((string) $post['category_code']),
            'supplier_code' => trim((string) $post['supplier_code']),
            'unit' => trim((string) $post['unit']),
            'stock' => (int) $post['stock'],
            'min_stock' => (int) $post['min_stock'],
            'purchase_price' => (float) $post['purchase_price'],
            'sale_price' => (float) $post['sale_price'],
            'active' => isset($post['active']),
        ],
        'categories' => $base + ['description' => trim((string) ($post['description'] ?? '')), 'active' => isset($post['active'])],
        'suppliers' => $base + ['phone' => trim((string) $post['phone']), 'address' => trim((string) $post['address']), 'email' => trim((string) ($post['email'] ?? '')), 'active' => isset($post['active'])],
        'customers' => $base + ['phone' => trim((string) $post['phone']), 'address' => trim((string) $post['address']), 'points' => (int) $post['points'], 'active' => isset($post['active'])],
        'users' => userData($base, $post),
        default => throw new InvalidArgumentException('Collection không hợp lệ.'),
    };
}

function userData(array $base, array $post): array
{
    $data = $base + ['username' => trim((string) $post['username']), 'role' => in_array($post['role'] ?? '', ['admin', 'staff'], true) ? $post['role'] : 'staff', 'active' => isset($post['active'])];
    if (!empty($post['password'])) $data['password_hash'] = password_hash((string) $post['password'], PASSWORD_DEFAULT);
    return $data;
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
$labels = ['dashboard' => 'Tổng quan', 'products' => 'Sản phẩm', 'categories' => 'Loại sản phẩm', 'suppliers' => 'Nhà cung cấp', 'customers' => 'Khách hàng', 'invoices' => 'Hóa đơn', 'reports' => 'Báo cáo', 'users' => 'Người dùng', 'backup' => 'Backup/Restore'];
?>
<!doctype html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= Security::e($labels[$page] ?? 'Đăng nhập') ?> - MiniMart</title>
    <link rel="stylesheet" href="assets/app.css?v=<?= file_exists(__DIR__ . '/assets/app.css') ? filemtime(__DIR__ . '/assets/app.css') : time() ?>">
</head>

<body>
    <?php if (!Auth::check()): ?>
        <main class="login-wrap">
            <form method="post" class="card login-card">
                <h1>MiniMart NoSQL</h1>
                <p>Web PHP + MongoDB + Studio 3T</p><input type="hidden" name="_token" value="<?= Security::csrfToken() ?>"><label>Tên đăng nhập<input name="username" required autofocus></label><label>Mật khẩu<input type="password" name="password" required></label><?php if ($error): ?><div class="alert error"><?= Security::e($error) ?></div><?php endif ?><button>Đăng nhập</button><small>Dữ liệu mẫu: admin / password</small>
            </form>
        </main>
    <?php else: ?>
        <aside>
            <h2>MiniMart</h2>
            <p class="muted"><?= Security::e(Auth::user()['name']) ?> · <?= Security::e(Auth::user()['role']) ?></p>
            <nav><?php foreach ($labels as $key => $label): if ($key === 'users' && !Auth::isAdmin()) continue; ?><a class="<?= $page === $key ? 'active' : '' ?>" href="?page=<?= $key ?>"><?= $label ?></a><?php endforeach ?><a href="?page=logout">Đăng xuất</a></nav>
        </aside>
        <main class="content">
            <header>
                <h1><?= Security::e($labels[$page] ?? $page) ?></h1>
            </header><?php if ($flash): ?><div class="alert <?= Security::e($flash['type']) ?>"><?= Security::e($flash['message']) ?></div><?php endif ?><?php if ($error): ?><div class="alert error"><?= Security::e($error) ?></div><?php endif ?>
            <?php
            if ($page === 'dashboard') renderDashboard($repo->dashboard());
            elseif (in_array($page, ['products', 'categories', 'suppliers', 'customers', 'users'], true)) renderCrud($repo, $page);
            elseif ($page === 'invoices') renderInvoices();
            elseif ($page === 'reports') renderReports($repo->reports());
            elseif ($page === 'backup') renderBackup();
            ?>
        </main><?php endif ?>
</body>

</html>
<?php
function renderDashboard(array $d): void
{ ?><section class="stats"><?php foreach (['products' => 'Sản phẩm', 'customers' => 'Khách hàng', 'invoices' => 'Hóa đơn', 'low_stock' => 'Sắp hết hàng'] as $k => $label): ?><article class="card"><span><?= $label ?></span><strong><?= $d[$k] ?></strong></article><?php endforeach ?><article class="card accent"><span>Doanh thu</span><strong><?= money($d['revenue']) ?></strong></article>
    </section>
    <section class="card">
        <h3>Phạm vi demo</h3>
        <p>Quản trị danh mục, nhà cung cấp, sản phẩm, khách hàng, người dùng; bán hàng có kiểm tra tồn kho; báo cáo aggregation; backup và restore.</p>
    </section><?php }

function renderCrud(Repository $repo, string $collection): void
{
    $edit = !empty($_GET['id']) ? $repo->find($collection, (string) $_GET['id']) : null;
    $docs = $repo->all($collection, trim((string) ($_GET['q'] ?? '')));
    $fields = match ($collection) {
        'products' => ['code' => 'Mã', 'name' => 'Tên', 'category_code' => 'Mã loại', 'supplier_code' => 'Mã NCC', 'unit' => 'Đơn vị', 'stock' => 'Tồn kho', 'min_stock' => 'Tồn tối thiểu', 'purchase_price' => 'Giá nhập', 'sale_price' => 'Giá bán'],
        'categories' => ['code' => 'Mã', 'name' => 'Tên', 'description' => 'Mô tả'],
        'suppliers' => ['code' => 'Mã', 'name' => 'Tên', 'phone' => 'Điện thoại', 'email' => 'Email', 'address' => 'Địa chỉ'],
        'customers' => ['code' => 'Mã', 'name' => 'Tên', 'phone' => 'Điện thoại', 'address' => 'Địa chỉ', 'points' => 'Điểm'],
        'users' => ['code' => 'Mã NV', 'name' => 'Họ tên', 'username' => 'Tài khoản', 'role' => 'Quyền', 'password' => 'Mật khẩu mới'],
    }; ?>
    <section class="grid">
        <form method="post" enctype="multipart/form-data" class="card form">
            <h3><?= $edit ? 'Cập nhật' : 'Thêm mới' ?></h3>
            <input type="hidden" name="_token" value="<?= Security::csrfToken() ?>">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="collection" value="<?= $collection ?>">
            <input type="hidden" name="id" value="<?= Security::e($edit ? (string)$edit['_id'] : '') ?>">
            <?php if ($collection === 'products'): ?>
                <div class="product-image-field">
                    <label>Ảnh sản phẩm (tải file lên hoặc nhập URL)
                        <?php if (!empty(val($edit, 'image'))): ?>
                            <div class="thumb-preview">
                                <img class="product-thumb-lg" src="<?= Security::e(val($edit, 'image')) ?>" onerror="this.src='assets/images/products/default.svg'" alt="Xem trước">
                            </div>
                        <?php endif ?>
                        <input type="file" name="image_file" accept="image/*">
                        <input type="text" name="image" value="<?= Security::e(val($edit, 'image', '')) ?>" placeholder="assets/images/products/SP001.jpg hoặc URL">
                    </label>
                </div>
            <?php endif ?>
            <?php foreach ($fields as $key => $label): $type = str_contains($key, 'price') || in_array($key, ['stock', 'min_stock', 'points'], true) ? 'number' : ($key === 'password' ? 'password' : 'text'); ?>
                <label><?= $label ?><input type="<?= $type ?>" name="<?= $key ?>" value="<?= $key === 'password' ? '' : Security::e(val($edit, $key)) ?>" <?= (!$edit || !in_array($key, ['password', 'email', 'description'], true)) ? 'required' : '' ?>></label>
            <?php endforeach ?>
            <label class="check"><input type="checkbox" name="active" <?= !$edit || val($edit, 'active', true) ? 'checked' : '' ?>> Đang hoạt động</label>
            <button>Lưu</button>
        </form>
        <section>
            <form class="search"><input type="hidden" name="page" value="<?= $collection ?>"><input name="q" value="<?= Security::e($_GET['q'] ?? '') ?>" placeholder="Tìm theo mã hoặc tên"><button>Tìm</button></form>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <?php if ($collection === 'products'): ?><th style="width:60px;">Ảnh</th><?php endif ?>
                            <?php foreach (array_slice($fields, 0, 5, true) as $label): ?><th><?= $label ?></th><?php endforeach ?>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($docs as $doc): ?>
                            <tr>
                                <?php if ($collection === 'products'): ?>
                                    <td class="col-thumb">
                                        <img class="product-thumb" src="<?= Security::e(val($doc, 'image', 'assets/images/products/default.svg')) ?>" alt="<?= Security::e(val($doc, 'name')) ?>" onerror="this.src='assets/images/products/default.svg'" loading="lazy">
                                    </td>
                                <?php endif ?>
                                <?php foreach (array_slice($fields, 0, 5, true) as $key => $label): ?><td><?= Security::e(val($doc, $key)) ?></td><?php endforeach ?>
                                <td>
                                    <div class="actions">
                                        <a href="?page=<?= $collection ?>&id=<?= $doc['_id'] ?>">Sửa</a>
                                        <form method="post" onsubmit="return confirm('Xóa bản ghi này?')"><input type="hidden" name="_token" value="<?= Security::csrfToken() ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="collection" value="<?= $collection ?>"><input type="hidden" name="id" value="<?= $doc['_id'] ?>"><button class="link danger">Xóa</button></form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>
            </div>
        </section>
    </section><?php }

function renderInvoices(): void
{
    $db = Database::connection();
    $products = $db->products->find(['active' => true], ['sort' => ['name' => 1]])->toArray();
    $customers = $db->customers->find(['active' => true], ['sort' => ['name' => 1]])->toArray();
    $invoices = (new InvoiceService())->all();

    $invoiceJsonMap = [];
    foreach ($invoices as $i) {
        $code = (string) $i['code'];
        $items = [];
        foreach ($i['items'] ?? [] as $item) {
            $prodCode = (string) ($item['product_code'] ?? '');
            $img = file_exists(__DIR__ . "/assets/images/products/{$prodCode}.jpg")
                ? "assets/images/products/{$prodCode}.jpg"
                : "assets/images/products/default.svg";
            $items[] = [
                'product_code' => $prodCode,
                'product_name' => (string) ($item['product_name'] ?? ''),
                'quantity' => (int) ($item['quantity'] ?? 1),
                'unit_price' => (float) ($item['unit_price'] ?? 0),
                'unit_price_formatted' => money($item['unit_price'] ?? 0),
                'discount_percent' => (float) ($item['discount_percent'] ?? 0),
                'line_total' => (float) ($item['line_total'] ?? 0),
                'line_total_formatted' => money($item['line_total'] ?? 0),
                'image' => $img,
            ];
        }
        $paymentText = match ((string) ($i['payment_method'] ?? 'cash')) {
            'bank_transfer' => 'Chuyển khoản',
            default => 'Tiền mặt',
        };
        $statusText = match ((string) ($i['status'] ?? 'completed')) {
            'completed' => 'Đã hoàn thành',
            'cancelled' => 'Đã hủy',
            default => (string) ($i['status'] ?? 'completed'),
        };
        $customerInfo = 'Khách lẻ';
        if (!empty($i['customer'])) {
            $cName = (string) ($i['customer']['name'] ?? '');
            $cCode = (string) ($i['customer']['code'] ?? '');
            $customerInfo = $cCode !== '' ? "{$cName} ({$cCode})" : $cName;
        }
        $employeeInfo = (string) ($i['employee']['name'] ?? 'Nhân viên');
        if (!empty($i['employee']['code'])) {
            $employeeInfo .= ' (' . $i['employee']['code'] . ')';
        }

        $invoiceJsonMap[$code] = [
            'code' => $code,
            'sold_at' => $i['sold_at']->toDateTime()->format('d/m/Y H:i:s'),
            'customer' => $customerInfo,
            'employee' => $employeeInfo,
            'payment_method' => $paymentText,
            'status' => $statusText,
            'subtotal' => money($i['subtotal'] ?? $i['total'] ?? 0),
            'total' => money($i['total'] ?? 0),
            'items' => $items,
        ];
    }
?>
    <section class="card">
        <h3>Tạo hóa đơn</h3>
        <form method="post" class="invoice-form"><input type="hidden" name="_token" value="<?= Security::csrfToken() ?>"><input type="hidden" name="action" value="invoice"><label>Khách hàng<select name="customer_id">
                    <option value="">Khách lẻ</option><?php foreach ($customers as $c): ?><option value="<?= $c['_id'] ?>"><?= Security::e($c['code'] . ' - ' . $c['name']) ?></option><?php endforeach ?>
                </select></label><label>Thanh toán<select name="payment_method">
                    <option value="cash">Tiền mặt</option>
                    <option value="bank_transfer">Chuyển khoản</option>
                </select></label>
            <div id="items">
                <div class="item"><select name="product_code[]" required>
                        <option value="">Chọn sản phẩm</option><?php foreach ($products as $p): ?><option value="<?= Security::e($p['code']) ?>"><?= Security::e($p['code'] . ' - ' . $p['name'] . ' (tồn ' . $p['stock'] . ')') ?></option><?php endforeach ?>
                    </select><input type="number" name="quantity[]" min="1" value="1" required><input type="number" name="discount[]" min="0" max="100" value="0" required></div>
            </div><button type="button" class="secondary" onclick="document.getElementById('items').append(document.querySelector('.item').cloneNode(true))">+ Sản phẩm</button> <button>Tạo hóa đơn</button>
        </form>
    </section>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Mã</th>
                    <th>Ngày bán</th>
                    <th>Khách hàng</th>
                    <th>Nhân viên</th>
                    <th>Tổng tiền</th>
                    <th>Trạng thái</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody><?php foreach ($invoices as $i): ?><tr>
                        <td><code><?= Security::e($i['code']) ?></code></td>
                        <td><?= $i['sold_at']->toDateTime()->format('d/m/Y H:i') ?></td>
                        <td><?= Security::e($i['customer']['name'] ?? 'Khách lẻ') ?></td>
                        <td><?= Security::e($i['employee']['name']) ?></td>
                        <td><strong><?= money($i['total']) ?></strong></td>
                        <td><span class="badge badge-success"><?= Security::e($i['status'] === 'completed' ? 'Hoàn thành' : $i['status']) ?></span></td>
                        <td>
                            <button type="button" class="btn-sm secondary" onclick="viewInvoiceDetail('<?= Security::e($i['code']) ?>')">
                                👁 Chi tiết
                            </button>
                        </td>
                    </tr><?php endforeach ?></tbody>
        </table>
    </div>

    <div id="invoiceModal" class="modal-backdrop" onclick="if(event.target===this)closeInvoiceModal()">
        <div class="modal-card" id="printableInvoice">
            <div class="modal-header">
                <div>
                    <span class="badge badge-success" id="modalStatus">Đã hoàn thành</span>
                    <h3 style="margin-top:6px;margin-bottom:0">Hóa đơn <span id="modalCode" style="color:var(--brand)"></span></h3>
                </div>
                <button type="button" class="modal-close" onclick="closeInvoiceModal()" title="Đóng (Esc)">&times;</button>
            </div>
            <div class="modal-body">
                <div class="invoice-meta">
                    <div class="invoice-meta-item">
                        <span>Khách hàng</span>
                        <strong id="modalCustomer"></strong>
                    </div>
                    <div class="invoice-meta-item">
                        <span>Nhân viên bán</span>
                        <strong id="modalEmployee"></strong>
                    </div>
                    <div class="invoice-meta-item">
                        <span>Thời gian bán</span>
                        <strong id="modalSoldAt"></strong>
                    </div>
                    <div class="invoice-meta-item">
                        <span>Hình thức thanh toán</span>
                        <strong id="modalPayment"></strong>
                    </div>
                </div>

                <div>
                    <h4 style="margin: 0 0 10px 0; font-size: 15px; color: var(--ink);">Chi tiết mặt hàng</h4>
                    <div class="table-wrap" style="border: 1px solid var(--line); border-radius: 8px;">
                        <table class="modal-table">
                            <thead>
                                <tr>
                                    <th style="width: 40px; text-align: center;">STT</th>
                                    <th style="width: 50px; text-align: center;">Ảnh</th>
                                    <th>Mã SP</th>
                                    <th>Tên sản phẩm</th>
                                    <th style="text-align: right;">Đơn giá</th>
                                    <th style="text-align: center;">SL</th>
                                    <th style="text-align: center;">Giảm</th>
                                    <th style="text-align: right;">Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody id="modalItemsBody">
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="modal-summary">
                    <div class="summary-box">
                        <div class="summary-row">
                            <span>Tạm tính:</span>
                            <strong id="modalSubtotal">0 đ</strong>
                        </div>
                        <div class="summary-row total">
                            <span>Tổng thanh toán:</span>
                            <strong id="modalTotal" style="color:var(--brand)">0 đ</strong>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="secondary" onclick="window.print()" style="display:inline-flex;align-items:center;gap:6px;">
                    🖨 In hóa đơn
                </button>
                <button type="button" onclick="closeInvoiceModal()">Đóng</button>
            </div>
        </div>
    </div>

    <script>
    const invoicesData = <?= json_encode($invoiceJsonMap, JSON_UNESCAPED_UNICODE) ?>;

    function viewInvoiceDetail(code) {
        const inv = invoicesData[code];
        if (!inv) return;
        document.getElementById('modalCode').textContent = '#' + inv.code;
        document.getElementById('modalStatus').textContent = inv.status;
        document.getElementById('modalCustomer').textContent = inv.customer;
        document.getElementById('modalEmployee').textContent = inv.employee;
        document.getElementById('modalSoldAt').textContent = inv.sold_at;
        document.getElementById('modalPayment').textContent = inv.payment_method;
        document.getElementById('modalSubtotal').textContent = inv.subtotal;
        document.getElementById('modalTotal').textContent = inv.total;

        const tbody = document.getElementById('modalItemsBody');
        tbody.innerHTML = '';
        inv.items.forEach((item, index) => {
            const tr = document.createElement('tr');

            const tdStt = document.createElement('td');
            tdStt.style.textAlign = 'center';
            tdStt.style.color = 'var(--muted)';
            tdStt.textContent = index + 1;

            const tdImg = document.createElement('td');
            tdImg.style.textAlign = 'center';
            const img = document.createElement('img');
            img.className = 'product-thumb';
            img.style.width = '36px';
            img.style.height = '36px';
            img.style.padding = '2px';
            img.src = item.image;
            img.alt = item.product_name;
            img.onerror = function() { this.src = 'assets/images/products/default.svg'; };
            tdImg.appendChild(img);

            const tdCode = document.createElement('td');
            const codeElem = document.createElement('code');
            codeElem.textContent = item.product_code;
            tdCode.appendChild(codeElem);

            const tdName = document.createElement('td');
            const strongName = document.createElement('strong');
            strongName.textContent = item.product_name;
            tdName.appendChild(strongName);

            const tdPrice = document.createElement('td');
            tdPrice.style.textAlign = 'right';
            tdPrice.textContent = item.unit_price_formatted;

            const tdQty = document.createElement('td');
            tdQty.style.textAlign = 'center';
            tdQty.textContent = item.quantity;

            const tdDiscount = document.createElement('td');
            tdDiscount.style.textAlign = 'center';
            tdDiscount.textContent = item.discount_percent > 0 ? '-' + item.discount_percent + '%' : '0%';

            const tdTotal = document.createElement('td');
            tdTotal.style.textAlign = 'right';
            tdTotal.style.fontWeight = '600';
            tdTotal.style.color = 'var(--brand)';
            tdTotal.textContent = item.line_total_formatted;

            tr.append(tdStt, tdImg, tdCode, tdName, tdPrice, tdQty, tdDiscount, tdTotal);
            tbody.appendChild(tr);
        });

        const modal = document.getElementById('invoiceModal');
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeInvoiceModal() {
        const modal = document.getElementById('invoiceModal');
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeInvoiceModal();
        }
    });
    </script>
<?php }

function renderReports(array $r): void
{ ?><section class="grid two">
        <div class="card">
            <h3>Doanh thu theo tháng</h3>
            <table>
                <tr>
                    <th>Tháng</th>
                    <th>Số đơn</th>
                    <th>Doanh thu</th>
                </tr><?php foreach ($r['monthly'] as $x): ?><tr>
                        <td><?= Security::e($x['_id']) ?></td>
                        <td><?= $x['orders'] ?></td>
                        <td><?= money($x['revenue']) ?></td>
                    </tr><?php endforeach ?>
            </table>
        </div>
        <div class="card">
            <h3>Top sản phẩm</h3>
            <table>
                <tr>
                    <th style="width:60px;">Ảnh</th>
                    <th>Sản phẩm</th>
                    <th>SL bán</th>
                    <th>Doanh thu</th>
                </tr><?php foreach ($r['top_products'] as $x): ?><tr>
                        <td class="col-thumb"><img class="product-thumb" src="assets/images/products/<?= Security::e($x['_id']) ?>.jpg" onerror="this.src='assets/images/products/default.svg'" alt="<?= Security::e($x['name']) ?>"></td>
                        <td><?= Security::e($x['name']) ?></td>
                        <td><?= $x['quantity'] ?></td>
                        <td><?= money($x['revenue']) ?></td>
                    </tr><?php endforeach ?>
            </table>
        </div>
    </section><?php }

            function renderBackup(): void
            {
                if (!Auth::isAdmin()) {
                    echo '<div class="alert error">Chỉ admin được sử dụng chức năng này.</div>';
                    return;
                }
                $service = new BackupService(); ?><section class="grid two">
        <form method="post" class="card">
            <h3>Tạo bản sao lưu</h3>
            <p>Dùng MongoDB Database Tools (mongodump).</p><input type="hidden" name="_token" value="<?= Security::csrfToken() ?>"><input type="hidden" name="action" value="backup"><button>Backup ngay</button>
        </form>
        <form method="post" class="card" onsubmit="return confirm('Restore sẽ ghi đè database hiện tại. Tiếp tục?')">
            <h3>Khôi phục</h3><input type="hidden" name="_token" value="<?= Security::csrfToken() ?>"><input type="hidden" name="action" value="restore"><select name="backup" required>
                <option value="">Chọn bản backup</option><?php foreach ($service->list() as $b): ?><option><?= Security::e($b) ?></option><?php endforeach ?>
            </select><button class="danger-button">Restore</button>
        </form>
    </section><?php }
