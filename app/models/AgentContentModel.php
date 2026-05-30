<?php
/**
 * Agent Content Model
 * Handles database operations for dynamic agent/affiliate pages
 */

require_once __DIR__ . '/BaseModel.php';

class AgentContentModel extends BaseModel {
    protected $table = 'agent_contents';
    protected $fillable = [
        'page_key', 'title', 'subtitle', 'content', 'image', 'meta_title', 'meta_description'
    ];
    
    public function __construct() {
        parent::__construct();
        $this->checkAndAddSubtitleColumn();
    }

    /**
     * Check if subtitle column exists in agent_contents, if not add it and populate default values
     */
    private function checkAndAddSubtitleColumn() {
        try {
            $pdo = $this->db->getPdo();
            // Check if column exists
            $stmt = $pdo->prepare("SHOW COLUMNS FROM {$this->table} LIKE 'subtitle'");
            $stmt->execute();
            if (!$stmt->fetch()) {
                // Column does not exist, add it
                $pdo->exec("ALTER TABLE {$this->table} ADD COLUMN subtitle VARCHAR(255) NULL AFTER title");
                
                // Update default subtitles for existing rows
                $defaultSubtitles = [
                    'chuong_trinh' => 'Cùng Thuong Lo phát triển sự nghiệp kinh doanh bền vững',
                    'huong_dan' => 'Quy trình đăng ký và kích hoạt tài khoản đại lý tiếp thị liên kết',
                    'chinh_sach' => 'Các chính sách, quy định và quyền lợi dành cho đại lý Thuong Lo',
                    'tai_nguyen' => 'Kho tài nguyên hình ảnh, banner và tài liệu giới thiệu phục vụ bán hàng'
                ];
                
                foreach ($defaultSubtitles as $key => $sub) {
                    $pdo->prepare("UPDATE {$this->table} SET subtitle = :subtitle WHERE page_key = :page_key AND subtitle IS NULL")
                        ->execute(['subtitle' => $sub, 'page_key' => $key]);
                }
            }

            // Also check if the table is empty and auto-seed if needed
            $countStmt = $pdo->query("SELECT COUNT(*) FROM {$this->table}");
            $count = $countStmt->fetchColumn();
            if ($count == 0) {
                $defaultPages = [
                    [
                        'page_key' => 'chuong_trinh',
                        'title' => 'Chương trình đại lý',
                        'subtitle' => 'Cùng Thuong Lo phát triển sự nghiệp kinh doanh bền vững',
                        'content' => '<h2>Chào mừng bạn đến với Chương trình Đại lý Thuong Lo</h2><p>Hệ thống đại lý của chúng tôi cung cấp giải pháp gia tăng thu nhập vượt trội cùng các công cụ hỗ trợ bán hàng tối tân nhất. Nội dung đang được cập nhật thêm bởi quản trị viên...</p>',
                        'image' => '',
                        'meta_title' => 'Chương trình Đại lý - Thuong Lo',
                        'meta_description' => 'Tham gia chương trình đại lý Thuong Lo để nhận chiết khấu và hoa hồng cực cao.'
                    ],
                    [
                        'page_key' => 'huong_dan',
                        'title' => 'Hướng dẫn đăng ký đại lý',
                        'subtitle' => 'Quy trình đăng ký và kích hoạt tài khoản đại lý tiếp thị liên kết',
                        'content' => '<h2>Hướng dẫn các bước đăng ký làm đại lý Thuong Lo</h2><p>Để trở thành đại lý, vui lòng nhấp vào nút "Đăng Ký Ngay" ở chân trang, điền đầy đủ thông tin cá nhân và tài khoản thanh toán PayOS để được kích hoạt tự động. Nội dung chi tiết đang được cập nhật...</p>',
                        'image' => '',
                        'meta_title' => 'Hướng dẫn đăng ký đại lý - Thuong Lo',
                        'meta_description' => 'Xem chi tiết các bước đăng ký tài khoản đại lý tại Thuong Lo nhanh chóng.'
                    ],
                    [
                        'page_key' => 'chinh_sach',
                        'title' => 'Chính sách đại lý',
                        'subtitle' => 'Các chính sách, quy định và quyền lợi dành cho đại lý Thuong Lo',
                        'content' => '<h2>Chính sách đại lý & Quyền lợi hợp tác</h2><p>Chúng tôi cam kết mức chiết khấu hấp dẫn lên đến 30% giá trị gói dữ liệu cùng chế độ rút tiền tự động hoàn toàn miễn phí qua cổng PayOS. Chi tiết chính sách đang được cập nhật...</p>',
                        'image' => '',
                        'meta_title' => 'Chính sách đại lý - Thuong Lo',
                        'meta_description' => 'Đọc các chính sách, điều khoản và quyền lợi dành cho đại lý Thuong Lo.'
                    ],
                    [
                        'page_key' => 'tai_nguyen',
                        'title' => 'Tài nguyên - tài liệu đại lý',
                        'subtitle' => 'Kho tài nguyên hình ảnh, banner và tài liệu giới thiệu phục vụ bán hàng',
                        'content' => '<h2>Kho tài nguyên & Tài liệu phục vụ đại lý tiếp thị</h2><p>Hệ thống cung cấp sẵn các bộ Banner truyền thông, File thiết kế SVG, Tài liệu giới thiệu sản phẩm và Đường dẫn tiếp thị tùy biến. Nội dung kho tài nguyên đang được cập nhật...</p>',
                        'image' => '',
                        'meta_title' => 'Tài nguyên - Tài liệu đại lý - Thuong Lo',
                        'meta_description' => 'Tải xuống banner, hình ảnh, tài liệu giới thiệu và công cụ hỗ trợ bán hàng.'
                    ]
                ];
                
                foreach ($defaultPages as $page) {
                    $pdo->prepare("INSERT INTO {$this->table} (page_key, title, subtitle, content, image, meta_title, meta_description) VALUES (:page_key, :title, :subtitle, :content, :image, :meta_title, :meta_description)")
                        ->execute($page);
                }
            }
        } catch (Exception $e) {
            error_log("AgentContentModel::checkAndAddSubtitleColumn error: " . $e->getMessage());
        }
    }
    
    /**
     * Get agent content by page key
     * @param string $key
     * @return array|null
     */
    public function getByPageKey($key) {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE page_key = :page_key LIMIT 1";
            $stmt = $this->db->getPdo()->prepare($sql);
            $stmt->execute(['page_key' => $key]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (Exception $e) {
            error_log("Get agent content error (key: $key): " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get all agent contents
     * @return array
     */
    public function getAllContents() {
        try {
            $sql = "SELECT * FROM {$this->table} ORDER BY id ASC";
            return $this->db->getPdo()->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("Get all agent contents error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Update agent content by key
     * @param string $key
     * @param array $data
     * @return bool
     */
    public function updateByKey($key, $data) {
        try {
            $filteredData = array_intersect_key($data, array_flip($this->fillable));
            if (empty($filteredData)) {
                return false;
            }
            
            $setClause = [];
            $params = ['page_key' => $key];
            
            foreach ($filteredData as $column => $value) {
                $setClause[] = "`$column` = :$column";
                $params[$column] = $value;
            }
            
            $sql = "UPDATE {$this->table} SET " . implode(', ', $setClause) . " WHERE page_key = :page_key";
            $stmt = $this->db->getPdo()->prepare($sql);
            return $stmt->execute($params);
        } catch (Exception $e) {
            error_log("Update agent content by key error (key: $key): " . $e->getMessage());
            return false;
        }
    }
}
