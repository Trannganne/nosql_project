<?php
declare(strict_types=1);

namespace App;

use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;

final class InvoiceService
{
    public function all(): array
    {
        return Database::connection()->invoices->find([], ['sort' => ['sold_at' => -1]])->toArray();
    }

    public function create(array $input): string
    {
        $db = Database::connection();
        $codes = $input['product_code'] ?? [];
        $quantities = $input['quantity'] ?? [];
        $discounts = $input['discount'] ?? [];
        $items = [];
        $subtotal = 0.0;

        foreach ($codes as $i => $code) {
            $code = trim((string) $code);
            $quantity = max(1, (int) ($quantities[$i] ?? 1));
            $discount = min(100, max(0, (float) ($discounts[$i] ?? 0)));
            $product = $db->products->findOne(['code' => $code]);
            if (!$product) throw new \RuntimeException("Không tìm thấy sản phẩm {$code}.");
            if ((int) $product['stock'] < $quantity) throw new \RuntimeException("Sản phẩm {$code} không đủ tồn kho.");
            $price = (float) $product['sale_price'];
            $lineTotal = $price * $quantity * (1 - $discount / 100);
            $items[] = ['product_id' => $product['_id'], 'product_code' => $code, 'product_name' => $product['name'], 'quantity' => $quantity, 'unit_price' => $price, 'discount_percent' => $discount, 'line_total' => $lineTotal];
            $subtotal += $lineTotal;
        }
        if (!$items) throw new \RuntimeException('Hóa đơn phải có ít nhất một sản phẩm.');

        $code = 'HD' . date('YmdHis') . random_int(10, 99);
        $session = $db->getManager()->startSession();
        $session->startTransaction();
        try {
            foreach ($items as $item) {
                $result = $db->products->updateOne(
                    ['_id' => $item['product_id'], 'stock' => ['$gte' => $item['quantity']]],
                    ['$inc' => ['stock' => -$item['quantity']], '$set' => ['updated_at' => new UTCDateTime()]],
                    ['session' => $session]
                );
                if ($result->getModifiedCount() !== 1) throw new \RuntimeException("Tồn kho {$item['product_code']} vừa thay đổi. Vui lòng thử lại.");
            }
            $customer = !empty($input['customer_id']) ? $db->customers->findOne(['_id' => new ObjectId((string) $input['customer_id'])]) : null;
            $db->invoices->insertOne([
                'code' => $code,
                'sold_at' => new UTCDateTime(),
                'employee' => ['code' => Auth::user()['code'], 'name' => Auth::user()['name']],
                'customer' => $customer ? ['id' => $customer['_id'], 'code' => $customer['code'], 'name' => $customer['name']] : null,
                'items' => $items,
                'subtotal' => $subtotal,
                'total' => $subtotal,
                'payment_method' => (string) ($input['payment_method'] ?? 'cash'),
                'status' => 'completed',
                'created_at' => new UTCDateTime(),
            ], ['session' => $session]);
            $session->commitTransaction();
            return $code;
        } catch (\Throwable $e) {
            $session->abortTransaction();
            throw $e;
        }
    }
}
