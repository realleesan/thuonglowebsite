-- Insert default why choose section and items data
INSERT INTO `why_choose_section` (`id`, `title`, `is_active`) VALUES 
(1, '<h2 class="section-title">Tại sao chọn <span class="highlight">ThuongLo?</span></h2>', 1)
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`);

INSERT INTO `why_choose_items` (`section_id`, `title`, `content`, `sort_order`) VALUES 
(1, 'Kinh nghiệm dày dặn', 'Hơn 10 năm kinh nghiệm trong lĩnh vực thương mại xuyên biên giới, hiểu rõ thị trường và quy trình', 1),
(1, 'Dịch vụ toàn diện', 'Cung cấp giải pháp từ A-Z cho thương mại xuyên biên giới, từ tìm nguồn hàng đến vận chuyển', 2),
(1, 'Hỗ trợ 24/7', 'Đội ngũ hỗ trợ chuyên nghiệp sẵn sàng giải đáp mọi thắc mắc và hỗ trợ khách hàng mọi lúc', 3),
(1, 'Giá cả cạnh tranh', 'Cam kết mang đến giá cả tốt nhất thị trường với chất lượng dịch vụ cao nhất', 4),
(1, 'Đội ngũ chuyên nghiệp', 'Đội ngũ nhân viên giàu kinh nghiệm, nhiệt tình và tận tâm với khách hàng', 5),
(1, 'Uy tín và đáng tin cậy', 'Được hàng ngàn khách hàng tin tưởng và lựa chọn trong nhiều năm', 6);
