1.

bây giờ, hệ thống cơ bản đã hoàn thiện về UI. Tuy nhiên data của hệ thống hiện tại đang sử dụng là dữ liệu tạm bằng json. giai đoạn tiếp theo tôi có nhiều công việc phải làm tuy nhiên cần phải lên kế hoạch cụ thể và theo quy trình để tránh bị lạc hướng.

tôi muốn chuyển web từ dữ liệu tĩnh json hiện tại sang dữ liệu sql. tech của tôi là php html css js sql. tuy nhiên chưa biết sẽ chọn loại sql nào phù hợp cho hệ thống, có thư viện nào hỗ trợ tối ưu sql ko? như là jquery, ... chẳng hạn. và thay vì xây dựng 1 bộ sql tổng thể thì chúng tôi lựa chọn phương án là xây dựng theo các migrations, seeders và cuối cùng sẽ ghép lại thành schema.

2. 

thứ hai, bạn xem ở folder app, có thể thấy nó được tổ chức theo m - v  - c và hiện tại tôi chỉ tổ chức ở views, nên cần tách ra thành m - v -c chuẩn chỉnh. cả hệ thống cần được tách thành api, route. core và config nữa (tức là mvc api route core config, phần route tôi thắc mắc rằng có nên tách ra hay ko? tôi đọc trên các diễn đàn nói rằng có thể tách thành 1 folder services nữa có nên ko?)

3. ở web của tôi, ThuongLo là web bán data của nguồn nhập khẩu hàng, cụ thể:

SP chính: Bán gói data nguồn hàng

SP phụ:

Vận chuyển chính ngạch

Mua hàng trọn gói – gói hàng TT cho NCC → VCCN → Giao hàng

Dịch vụ TT quốc tế

Dịch vụ đánh hàng → phiên dịch → đi lại → ăn ở

với sản phẩm chính, ví dụ: gói 100 data nguồn nhập hàng chẳng hạn. sản phẩm khi mua về sẽ được bố trí theo dạng bảng sheet (bao gồm, tên địa chỉ sđt wechat ảnh ...) khi chưa mua và đã đăng nhập thì các cột quan trọng sẽ bị blur, chỉ hiện cột ngành hàng thôi. còn khi chưa đăng nhập thì vào xem chi tiết sản phẩm sẽ bị blur hoàn toàn và yêu cầu đăng nhập mới có thể xem được. -> cụ thể hãy xem ở tool/docs_content.txt để hiểu nghiệp vụ hơn. khách yêu cầu họ muốn ở giao diện admin có thể dùng excel/sheet để tải danh sách data cho mỗi sản phẩm, có thể tùy chỉnh cột nào sẽ blur khi khách chưa mua và đã đăng nhập. điều này có thể xử lý được không? và có thể thêm chức năng đăng hàng loạt sản phẩm cho tiện hay ko? ngoài ra, bạn đọc ở .txt tôi gửi ấy, họ bảo có cơ chế chống bán lại data thì gắn thêm water mark động ấy, xử lý được chứ? rõ hơn thì đọc ở folder plan nhé. ngoài ra còn có chia dữ liệu thành từng phần nữa. với dữ liệu dạng cột thế này thì nên chia các cột thành từng phần dữ liệu và khi truy vấn ra màn hình sẽ ghép cột nhỉ? và khi blur thì có cách nào để người dùng không thể xem được nội dung khi xóa blur bằng f12 hay ko?
