<?php
/**
 * Cart Model - Quản lý giỏ hàng
 */

require_once __DIR__ . '/BaseModel.php';

class CartModel extends BaseModel {
    protected $table = 'carts';
    protected $primaryKey = 'id';
    protected $fillable = [
        'user_id',
        'product_id',
        'quantity',
        'price',
        'created_at',
        'updated_at'
    ];

    /**
     * Lấy giỏ hàng của user
     */
    public function getByUser($userId) {
        return $this->db->table($this->table)
            ->select('*')
            ->where('user_id', $userId)
            ->get();
    }

    /**
     * Lấy sản phẩm trong giỏ hàng
     */
    public function getItem($userId, $productId) {
        return $this->db->table($this->table)
            ->select('*')
            ->where('user_id', $userId)
            ->where('product_id', $productId)
            ->first();
    }

    /**
     * Thêm sản phẩm vào giỏ hàng
     */
    public function addItem($userId, $productId, $quantity, $price) {
        // Kiểm tra xem sản phẩm đã có trong giỏ hàng chưa
        $existingItem = $this->getItem($userId, $productId);
        
        if ($existingItem) {
            // Cập nhật số lượng
            $newQuantity = $existingItem['quantity'] + $quantity;
            return $this->db->table($this->table)
                ->where('id', $existingItem['id'])
                ->update([
                    'quantity' => $newQuantity,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
        } else {
            // Thêm mới
            return $this->create([
                'user_id' => $userId,
                'product_id' => $productId,
                'quantity' => $quantity,
                'price' => $price,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }
    }

    /**
     * Cập nhật số lượng sản phẩm
     */
    public function updateQuantity($id, $quantity) {
        return $this->db->table($this->table)
            ->where('id', $id)
            ->update([
                'quantity' => $quantity,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
    }

    /**
     * Xóa sản phẩm khỏi giỏ hàng
     */
    public function removeItem($id) {
        return $this->db->table($this->table)
            ->where('id', $id)
            ->delete();
    }

    /**
     * Xóa tất cả sản phẩm trong giỏ hàng của user
     */
    public function clearCart($userId) {
        return $this->db->table($this->table)
            ->where('user_id', $userId)
            ->delete();
    }

    /**
     * Tính tổng tiền giỏ hàng
     */
    public function getTotalAmount($userId) {
        $items = $this->getByUser($userId);
        $total = 0;
        foreach ($items as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        return $total;
    }

    /**
     * Đếm số sản phẩm trong giỏ hàng
     */
    public function getItemCount($userId) {
        $items = $this->getByUser($userId);
        return count($items);
    }
}

