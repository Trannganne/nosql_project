<?php
declare(strict_types=1);

namespace App;

use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;

final class Repository
{
    private const ALLOWED = ['products', 'categories', 'suppliers', 'customers', 'users'];

    public function all(string $collection, string $search = ''): array
    {
        $this->guard($collection);
        $filter = [];
        if ($search !== '') {
            $filter = ['$or' => [
                ['code' => ['$regex' => preg_quote($search), '$options' => 'i']],
                ['name' => ['$regex' => preg_quote($search), '$options' => 'i']],
            ]];
        }
        return Database::connection()->selectCollection($collection)
            ->find($filter, ['sort' => ['created_at' => -1]])->toArray();
    }

    public function find(string $collection, string $id): ?object
    {
        $this->guard($collection);
        try { return Database::connection()->selectCollection($collection)->findOne(['_id' => new ObjectId($id)]); }
        catch (\Throwable) { return null; }
    }

    public function save(string $collection, array $data, ?string $id = null): void
    {
        $this->guard($collection);
        $data['updated_at'] = new UTCDateTime();
        $target = Database::connection()->selectCollection($collection);
        if ($id) {
            $target->updateOne(['_id' => new ObjectId($id)], ['$set' => $data]);
        } else {
            $data['created_at'] = new UTCDateTime();
            $target->insertOne($data);
        }
    }

    public function delete(string $collection, string $id): void
    {
        $this->guard($collection);
        Database::connection()->selectCollection($collection)->deleteOne(['_id' => new ObjectId($id)]);
    }

    public function dashboard(): array
    {
        $db = Database::connection();
        $revenue = $db->invoices->aggregate([
            ['$match' => ['status' => 'completed']],
            ['$group' => ['_id' => null, 'total' => ['$sum' => '$total']]],
        ])->toArray();
        return [
            'products' => $db->products->countDocuments(),
            'customers' => $db->customers->countDocuments(),
            'invoices' => $db->invoices->countDocuments(),
            'low_stock' => $db->products->countDocuments(['$expr' => ['$lte' => ['$stock', '$min_stock']]]),
            'revenue' => $revenue[0]['total'] ?? 0,
        ];
    }

    public function reports(): array
    {
        $db = Database::connection();
        return [
            'monthly' => $db->invoices->aggregate([
                ['$match' => ['status' => 'completed']],
                ['$group' => ['_id' => ['$dateToString' => ['format' => '%Y-%m', 'date' => '$sold_at']], 'revenue' => ['$sum' => '$total'], 'orders' => ['$sum' => 1]]],
                ['$sort' => ['_id' => 1]],
            ])->toArray(),
            'top_products' => $db->invoices->aggregate([
                ['$unwind' => '$items'],
                ['$group' => ['_id' => '$items.product_code', 'name' => ['$first' => '$items.product_name'], 'quantity' => ['$sum' => '$items.quantity'], 'revenue' => ['$sum' => '$items.line_total']]],
                ['$sort' => ['quantity' => -1]], ['$limit' => 10],
            ])->toArray(),
        ];
    }

    private function guard(string $collection): void
    {
        if (!in_array($collection, self::ALLOWED, true)) throw new \InvalidArgumentException('Collection không hợp lệ.');
    }
}
