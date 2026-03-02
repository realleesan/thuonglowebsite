<?php
/**
 * Wishlist Model - Quản lý danh sách yêu thích
 */

require_once __DIR__ . '/BaseModel.php';

class WishlistModel extends BaseModel {
    protected $table = 'wishlists';
    protected $primaryKey = 'id';
    protected $fillable = [
        'user_id',
        'product_id',
        'notes',
        'created_at',
        'updated_at'
    ];

    /**
     * Lấy danh sách yêu thích của user
     */
    public function getByUser($userId) {
        return $this->db->table($this->table)
            ->select('*')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->get();
    }

    /**
     * Kiểm tra sản phẩm đã có trong wishlist chưa
     */
    public function hasProduct($userId, $productId) {
        $item = $this->db->table($this->table)
            ->select('*')
            ->where('user_id', $userId)
            ->where('product_id', $productId)
            ->first();
        
        return $item !== null;
    }

    /**
     * Thêm sản phẩm vào wishlist
     */
    public function addProduct($userId, $productId, $notes = '') {
        // Kiểm tra xem sản phẩm đã có chưa
        if ($this->hasProduct($userId, $productId)) {
            return false;
        }
        
        return $this->create([
            'user_id' => $userId,
            'product_id' => $productId,
            'notes' => $notes,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Cập nhật ghi chú
     */
    public function updateNotes($id, $notes) {
        return $this->db->table($this->table)
            ->where('id', $id)
            ->update([
                'notes' => $notes,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
    }

    /**
     * Xóa sản phẩm khỏi wishlist
     */
    public function removeProduct($id) {
        return $this->db->table($this->table)
            ->where('id', $id)
            ->delete();
    }

    /**
     * Xóa sản phẩm theo user và product
     */
    public function removeByProduct($userId, $productId) {
        return $this->db->table($this->table)
            ->where('user_id', $userId)
            ->where('product_id', $productId)
            ->delete();
    }

    /**
     * Xóa tất cả sản phẩm trong wishlist của user
     */
    public function clearWishlist($userId) {
        return $this->db->table($this->table)
            ->where('user_id', $userId)
            ->delete();
    }

    /**
     * Đếm số sản phẩm trong wishlist
     */
    public function getCount($userId) {
        $items = $this->getByUser($userId);
        return count($items);
    }
}
