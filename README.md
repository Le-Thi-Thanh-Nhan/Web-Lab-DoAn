# 🛒 Hệ Thống Bán Thực Phẩm Online (Online Food Ordering System)

Dự án **Xây dựng hệ thống bán thực phẩm online** là Đồ án 1 của nhóm sinh viên thuộc Khoa Công nghệ thông tin – Trường Đại học Kinh tế - Kỹ thuật Công nghiệp (UNETI). Hệ thống cung cấp giải pháp thương mại điện tử trực tuyến, giúp tối ưu hóa quy trình kinh doanh thực phẩm tươi sạch và nâng cao trải nghiệm mua sắm của người tiêu dùng.

<img width="753" height="388" alt="image" src="https://github.com/user-attachments/assets/0a62cede-1706-4cfa-aeff-9a991ad57e14" />

## ✨ Các Chức Năng Cốt Lõi

Hệ thống được thiết kế phân quyền rõ ràng cho hai nhóm đối tượng chính với đầy đủ các nghiệp vụ thương mại điện tử:

### 👤 Khách Hàng (User Interface)
* **Quản lý tài khoản:** Đăng ký tài khoản mới, đăng nhập hệ thống, đăng xuất và hỗ trợ cấp lại mật khẩu khi quên.
* **Tìm kiếm & Xem sản phẩm:** Tìm kiếm sản phẩm thực phẩm theo tên, bộ lọc hoặc theo danh mục; xem thông tin chi tiết (mô tả, giá cả, hình ảnh, số lượng tồn kho).
* **Quản lý giỏ hàng:** Thêm thực phẩm vào giỏ hàng, xem giỏ hàng, tùy chỉnh tăng/giảm số lượng hoặc xóa sản phẩm khỏi giỏ hàng.
* **Quy trình thanh toán:** Nhập thông tin giao hàng (họ tên, số điện thoại, địa chỉ), lựa chọn phương thức thanh toán khi nhận hàng (COD) hoặc chuyển khoản ngân hàng.
* **Tính năng mở rộng:** Đánh giá sản phẩm đã mua, thêm thực phẩm vào danh sách yêu thích, nhận thông báo từ hệ thống và gửi biểu mẫu liên hệ hỗ trợ.

### 🛠️ Quản Trị Viên (Admin Dashboard)

<img width="753" height="381" alt="image" src="https://github.com/user-attachments/assets/e0ca3c18-975f-4d22-8ac3-5389606960ff" />

* **Quản lý tài khoản:** Thêm mới, chỉnh sửa, xóa và quản lý quyền truy cập của các tài khoản người dùng.
* **Quản lý sản phẩm:** Thêm mới, sửa đổi thông tin, ẩn/xóa sản phẩm, theo dõi hiệu suất sản phẩm và kiểm soát số lượng tồn kho.
* **Quản lý danh mục:** Thiết lập, sắp xếp cấu trúc danh mục cha (categories) và danh mục con (subcategories) để phân loại thực phẩm.
* **Quản lý đơn hàng:** Tiếp nhận, xem chi tiết thông tin đơn hàng, phê duyệt hóa đơn và cập nhật trạng thái xử lý đơn hàng.
* **Khuyến mãi & Thống kê:** Tạo lập và quản lý các mã giảm giá (voucher); theo dõi biểu đồ báo cáo và thống kê doanh thu bán hàng.

## 🛠️ Công Nghệ Sử Dụng

Dự án sử dụng mô hình Client-Server với các công nghệ lập trình web nền tảng:
* **Backend:** Ngôn ngữ lập trình **PHP** thuần (Hypertext Preprocessor) xử lý logic phía máy chủ và kết nối dữ liệu.
* **Frontend:** **HTML5**, **CSS3** (định dạng, thiết kế giao diện responsive tương thích đa nền tảng) và **JavaScript** (xử lý hiệu ứng động và kiểm tra dữ liệu đầu vào).
* **Database:** Hệ quản trị cơ sở dữ liệu quan hệ **MySQL**.
* **Môi trường chạy máy cục bộ:** Apache (tích hợp sẵn trong XAMPP).

## 🚀 Hướng Dẫn Cài Đặt và Khởi Chạy

Để khởi chạy trang web trên máy tính cá nhân (Localhost), hãy làm theo các bước dưới đây hoặc xem video chi tiết trong thư mục `video/`:

### Bước 1: Tải mã nguồn về máy
Sao chép kho lưu trữ này vào thư mục chứa dự án web:
```bash
git clone https://github.com
```

### Bước 2: Cấu hình Cơ sở dữ liệu (MySQL)
1. Khởi động module **Apache** và **MySQL** trên bảng điều khiển XAMPP Control Panel.
2. Mở trình duyệt và truy cập hệ quản trị dữ liệu: `http://localhost/phpmyadmin/`.
3. Tạo một cơ sở dữ liệu mới
4. Chọn cơ sở dữ liệu vừa tạo, nhấn vào thẻ **Import**, chọn tệp tin cấu hình dữ liệu `.sql` (nằm bên trong thư mục `code/`) và nhấn **Go** để nạp dữ liệu.
5. Kiểm tra file cấu hình kết nối CSDL trong thư mục `code/` để đảm bảo các thông số `localhost`, `root`, mật khẩu và tên database trùng khớp với hệ thống của bạn.

### Bước 3: Truy cập hệ thống
Mở trình duyệt web của bạn và truy cập theo đường dẫn sau (thay đổi tên thư mục theo đúng cấu trúc thực tế của bạn):
* **Giao diện dành cho Khách hàng:** `http://localhost/ten-thu-muc-du-an/code/`
* **Giao diện dành cho Quản trị viên:** `http://localhost/ten-thu-muc-du-an/code/admin/`

## 👥 Thành Viên Thực Hiện

Đồ án được nghiên cứu, phân tích thiết kế hệ thống và phát triển mã nguồn bởi nhóm sinh viên lớp **DHMT16A2HN**:
* **Sinh viên thực hiện:** 
  * Cao Văn Đức
  * Nguyễn Thị Bản
  * Lê Thị Thanh Nhàn
* **Giảng viên hướng dẫn:** Thầy Phạm Văn Công (Bùi Văn Công)
* **Thời gian hoàn thành:** Năm 2025

***
*Kho lưu trữ này được thiết lập nhằm mục đích học tập, nghiên cứu và báo cáo học phần Đồ án 1.*
