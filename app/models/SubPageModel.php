<?php
/**
 * SubPageModel Class
 * Handles dynamic content management for subpages (about, faq, terms, privacy, etc.)
 */
require_once __DIR__ . '/BaseModel.php';

class SubPageModel extends BaseModel {
    protected $table = 'sub_pages';

    public function __construct() {
        parent::__construct();
        $this->checkAndAddSubtitleColumn();
    }

    /**
     * Check if subtitle column exists in sub_pages, if not add it and populate default values
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
                    'about' => 'Khám phá năng lực cốt lõi của ThuongLo.com - Giải pháp tự động hóa logistics & nguồn hàng gốc',
                    'faq' => 'Tổng hợp các thắc mắc thường gặp về Logistics, Đơn hàng và Thanh toán tại ThuongLo',
                    'shopping_guide' => 'Quy trình đặt mua nguồn hàng & tự động Logistics tại ThuongLo nhanh chóng, bảo mật và an toàn',
                    'terms' => 'Điều khoản dịch vụ và quy chế hoạt động chính thức của hệ thống ThuongLo',
                    'privacy' => 'Cam kết bảo mật dữ liệu, thông tin cá nhân và tài sản thông tin tuyệt đối tại ThuongLo'
                ];
                
                foreach ($defaultSubtitles as $key => $sub) {
                    $pdo->prepare("UPDATE {$this->table} SET subtitle = :subtitle WHERE page_key = :page_key AND subtitle IS NULL")
                        ->execute(['subtitle' => $sub, 'page_key' => $key]);
                }
            }
        } catch (Exception $e) {
            error_log("SubPageModel::checkAndAddSubtitleColumn error: " . $e->getMessage());
        }
    }

    /**
     * Get page by its unique page_key
     * @param string $pageKey
     * @return array|false
     */
    public function getByPageKey($pageKey) {
        try {
            $result = $this->db->table($this->table)->where('page_key', $pageKey)->first();
            return $result ? $result : false;
        } catch (Exception $e) {
            error_log("SubPageModel::getByPageKey error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all managed sub pages
     * @return array
     */
    public function getAllPages() {
        try {
            return $this->db->table($this->table)->select()->orderBy('id', 'ASC')->get() ?? [];
        } catch (Exception $e) {
            error_log("SubPageModel::getAllPages error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Update page content and image by key
     * @param string $pageKey
     * @param array $data
     * @return bool
     */
    public function updateByKey($pageKey, $data) {
        try {
            return $this->db->table($this->table)->where('page_key', $pageKey)->update($data);
        } catch (Exception $e) {
            error_log("SubPageModel::updateByKey error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Initialize subpages table with default data if empty
     * @return bool
     */
    public function seedDefaultSubPages($force = false) {
        try {
            // Check if table is empty
            if (!$force) {
                $count = $this->count();
                if ($count > 0) {
                    return true; // Already seeded
                }
            }

            // Clean table first if forcing
            if ($force) {
                $this->db->exec("TRUNCATE TABLE {$this->table}");
            }

            // Seed structure & default contents
            $defaultPages = [
                [
                    'page_key' => 'about',
                    'title' => 'Giới thiệu',
                    'subtitle' => 'Khám phá năng lực cốt lõi của ThuongLo.com - Giải pháp tự động hóa logistics & nguồn hàng gốc',
                    'content' => '<h2>ThuongLo<br><span class="highlight">Nguồn Hàng Gốc</span> &amp; <span class="highlight">Tự Động Hóa Logistics</span></h2><p>Nền tảng tiên phong cung cấp các gói dữ liệu nhà cung cấp độc quyền và giải pháp vận chuyển thông minh. Chúng tôi bảo vệ tài sản thông tin của bạn bằng công nghệ mã hóa hiện đại, đồng thời tự động hóa quy trình từ thanh toán đến bàn giao dữ liệu chỉ trong vài giây.</p><h3>Kho Nguồn Hàng Độc Quyền</h3><p>Thượng Lộ cung cấp các gói dữ liệu nhà máy, xưởng sản xuất đã qua kiểm duyệt kỹ lưỡng. Là "vũ khí bí mật" giúp bạn tối ưu biên lợi nhuận ngay từ khâu nhập hàng.</p><h3>Công Nghệ Chống Sao Chép</h3><p>Hệ thống của chúng tôi tích hợp các lớp bảo mật cấp cao, ngăn chặn hành vi chia sẻ trái phép hoặc bán lại, đảm bảo lợi thế cạnh tranh độc tôn cho chủ sở hữu gói.</p><h3>Thanh Toán &amp; Kích Hoạt Tự Động</h3><p>Loại bỏ quy trình xác nhận thủ công chậm chạp. Với Thượng Lộ, ngay sau khi quét QR thanh toán, hệ thống Logistics được kích hoạt và kho dữ liệu được mở khóa tự động 100%.</p>',
                    'image' => null,
                    'meta_title' => 'Giới thiệu về Thuong Lo - Nguồn hàng gốc & Logistics',
                    'meta_description' => 'Nền tảng tiên phong cung cấp các gói dữ liệu nhà cung cấp độc quyền và giải pháp vận chuyển thông minh Thượng Lộ.'
                ],
                [
                    'page_key' => 'faq',
                    'title' => 'Câu hỏi thường gặp',
                    'subtitle' => 'Tổng hợp các thắc mắc thường gặp về Logistics, Đơn hàng và Thanh toán tại ThuongLo',
                    'content' => '<h3>Đơn hàng &amp; Thanh toán</h3><p><strong>Làm thế nào để đặt hàng trên ThuongLo?</strong><br>Bạn có thể đặt hàng trực tiếp trên website bằng cách chọn sản phẩm, thêm vào giỏ hàng và tiến hành thanh toán. Chúng tôi hỗ trợ nhiều hình thức thanh toán tiện lợi.</p><p><strong>Các phương thức thanh toán nào được chấp nhận?</strong><br>Chúng tôi chấp nhận thanh toán khi nhận hàng (COD), chuyển khoản ngân hàng, ví điện tử và cổng thanh toán tự động PayOS.</p><h3>Giao hàng &amp; Đổi trả</h3><p><strong>Thời gian giao hàng bao lâu?</strong><br>Thời gian giao hàng nội thành 2-3 ngày, các tỉnh khác từ 3-5 ngày làm việc.</p><p><strong>Chính sách đổi trả như thế nào?</strong><br>Bạn có thể đổi trả sản phẩm trong vòng 30 ngày nếu sản phẩm còn nguyên tem mác, chưa qua sử dụng và kèm hóa đơn mua hàng.</p>',
                    'image' => null,
                    'meta_title' => 'Câu hỏi thường gặp (FAQ) - Thuong Lo',
                    'meta_description' => 'Tổng hợp và giải đáp các câu hỏi thường gặp về mua sắm, thanh toán, vận chuyển và đổi trả hàng tại Thuong Lo.'
                ],
                [
                    'page_key' => 'shopping_guide',
                    'title' => 'Hướng dẫn mua hàng',
                    'subtitle' => 'Quy trình đặt mua nguồn hàng & tự động Logistics tại ThuongLo nhanh chóng, bảo mật và an toàn',
                    'content' => '<h2>Hướng dẫn đặt mua hàng tại Thuong Lo</h2><p>Chào mừng bạn đến với Thuong Lo! Nhằm giúp bạn có trải nghiệm mua sắm dễ dàng, thuận tiện nhất, dưới đây là hướng dẫn các bước đặt mua hàng nhanh chóng:</p><ol><li><strong>Bước 1: Tìm kiếm sản phẩm</strong> - Sử dụng thanh tìm kiếm hoặc duyệt qua danh mục sản phẩm, thương hiệu để tìm sản phẩm mong muốn.</li><li><strong>Bước 2: Thêm vào giỏ hàng</strong> - Chọn số lượng và nhấp vào nút "Thêm vào giỏ" hoặc click "Mua ngay".</li><li><strong>Bước 3: Điền thông tin giao nhận</strong> - Nhập chính xác tên, số điện thoại, địa chỉ nhận hàng để đảm bảo giao hàng chính xác.</li><li><strong>Bước 4: Chọn phương thức thanh toán</strong> - Chọn COD, quét mã chuyển khoản QR PayOS hoặc thẻ ngân hàng.</li><li><strong>Bước 5: Xác nhận đơn hàng</strong> - Bấm nút "Đặt hàng" để hoàn tất quy trình mua sắm. Đơn hàng của bạn sẽ được kích hoạt xử lý tự động trong tích tắc.</li></ol>',
                    'image' => null,
                    'meta_title' => 'Hướng dẫn đặt mua hàng nhanh chóng - Thuong Lo',
                    'meta_description' => 'Hướng dẫn chi tiết các bước đặt mua hàng, thanh toán và kiểm tra tình trạng đơn hàng tại Thuong Lo.'
                ],
                [
                    'page_key' => 'terms',
                    'title' => 'Điều khoản dịch vụ',
                    'subtitle' => 'Điều khoản dịch vụ và quy chế hoạt động chính thức của hệ thống ThuongLo',
                    'content' => '<h2>Điều khoản dịch vụ và chính sách sử dụng</h2><p>Chào mừng bạn đến với hệ thống ThuongLo.com. Khi bạn truy cập, đăng ký tài khoản hoặc sử dụng dịch vụ của chúng tôi, đồng nghĩa với việc bạn đồng ý tuân thủ các điều khoản dịch vụ dưới đây:</p><h3>1. Tài khoản Người dùng</h3><p>Bạn chịu trách nhiệm bảo mật tài khoản và mật khẩu của mình. Mọi hoạt động phát sinh dưới tài khoản của bạn sẽ thuộc trách nhiệm cá nhân của bạn.</p><h3>2. Sở hữu trí tuệ</h3><p>Tất cả nội dung, gói dữ liệu nhà cung cấp, hình ảnh, mã nguồn và hệ thống tự động hóa thuộc quyền sở hữu trí tuệ độc quyền của ThuongLo. Nghiêm cấm mọi hành vi sao chép, phân phối hoặc bán lại khi chưa được sự đồng ý bằng văn bản của ban quản trị.</p><h3>3. Giới hạn trách nhiệm</h3><p>Chúng tôi luôn nỗ lực đảm bảo độ chính xác cao nhất của thông tin, tuy nhiên không chịu trách nhiệm trước bất kỳ tổn thất gián tiếp nào phát sinh do quá trình sử dụng dữ liệu.</p>',
                    'image' => null,
                    'meta_title' => 'Điều khoản dịch vụ & Quy chế hoạt động - Thuong Lo',
                    'meta_description' => 'Đọc kỹ các quy định, điều khoản dịch vụ và chính sách sử dụng đối với khách hàng và đại lý khi tham gia Thuong Lo.'
                ],
                [
                    'page_key' => 'privacy',
                    'title' => 'Chính sách bảo mật',
                    'subtitle' => 'Cam kết bảo mật dữ liệu, thông tin cá nhân và tài sản thông tin tuyệt đối tại ThuongLo',
                    'content' => '<h2>Chính sách bảo mật thông tin cá nhân</h2><p>Thuong Lo cam kết bảo vệ tuyệt đối thông tin cá nhân của người dùng. Chính sách bảo mật dưới đây làm rõ cách thức chúng tôi thu thập, sử dụng và bảo vệ thông tin của bạn:</p><h3>1. Thu thập thông tin</h3><p>Chúng tôi thu thập thông tin khi bạn đăng ký tài khoản, đặt mua gói dữ liệu hoặc đăng ký làm đại lý (gồm Tên, Email, Số điện thoại và thông tin thanh toán phục vụ rút tiền qua PayOS).</p><h3>2. Sử dụng thông tin</h3><p>Thông tin thu thập được sử dụng để xử lý đơn hàng, gửi thông báo kích hoạt, hỗ trợ xử lý giao nhận logistics, và gửi ưu đãi khuyến mãi định kỳ (nếu bạn đồng ý nhận).</p><h3>3. Bảo mật dữ liệu</h3><p>Chúng tôi sử dụng giao thức mã hóa dữ liệu SSL bảo mật cao và lưu trữ dữ liệu trên máy chủ an toàn. Cam kết không chia sẻ, mua bán thông tin cá nhân của bạn cho bên thứ ba dưới bất kỳ hình thức nào.</p>',
                    'image' => null,
                    'meta_title' => 'Chính sách bảo mật thông tin khách hàng - Thuong Lo',
                    'meta_description' => 'Cam kết bảo mật tuyệt đối dữ liệu và thông tin cá nhân của khách hàng khi tham gia giao dịch trên hệ thống Thuong Lo.'
                ],
                [
                    'page_key' => 'footer_socials',
                    'title' => 'Mạng xã hội Footer',
                    'subtitle' => null,
                    'content' => '{"facebook":{"name":"Facebook","url":"https://facebook.com","visible":true,"icon":"fab fa-facebook"},"youtube":{"name":"Youtube","url":"https://youtube.com","visible":true,"icon":"fab fa-youtube"},"instagram":{"name":"Instagram","url":"https://instagram.com","visible":true,"icon":"fab fa-instagram"},"twitter":{"name":"X (Twitter)","url":"https://twitter.com","visible":true,"icon":"fab fa-twitter"},"tiktok":{"name":"Tiktok","url":"https://tiktok.com","visible":true,"icon":"fab fa-tiktok"},"linkedin":{"name":"Linkedin","url":"https://linkedin.com","visible":true,"icon":"fab fa-linkedin"}}',
                    'image' => null,
                    'meta_title' => 'Social Connections',
                    'meta_description' => 'Footer social media icon connections configurations.'
                ]
            ];

            // Insert each
            foreach ($defaultPages as $page) {
                // Check if page already exists to prevent duplicate key
                $existing = $this->db->table($this->table)->where('page_key', $page['page_key'])->first();
                if ($existing) {
                    $this->db->table($this->table)->where('page_key', $page['page_key'])->update([
                        'title' => $page['title'],
                        'subtitle' => $page['subtitle'] ?? null,
                        'content' => $page['content'],
                        'meta_title' => $page['meta_title'],
                        'meta_description' => $page['meta_description']
                    ]);
                } else {
                    $this->db->table($this->table)->insert([
                        'page_key' => $page['page_key'],
                        'title' => $page['title'],
                        'subtitle' => $page['subtitle'] ?? null,
                        'content' => $page['content'],
                        'image' => $page['image'],
                        'meta_title' => $page['meta_title'],
                        'meta_description' => $page['meta_description']
                    ]);
                }
            }
            return true;
        } catch (Exception $e) {
            error_log("SubPageModel::seedDefaultSubPages error: " . $e->getMessage());
            return false;
        }
    }
}
