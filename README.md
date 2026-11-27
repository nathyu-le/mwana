# MalwareGuard 1.4.0

Website tra cứu báo cáo tệp từ VirusTotal, lưu lịch sử theo tài khoản và xuất JSON. Trình duyệt tính SHA-256 khi người dùng chọn tệp. Máy chủ nhận tên, kích thước và mã băm, sau đó chỉ gửi mã băm tới VirusTotal.

## Cài trên cPanel

1. Chọn PHP 8.1 trở lên với PDO MySQL, fileinfo, ctype và cURL. Dùng HTTPS để trình duyệt tính mã băm.
2. Clone repo vào thư mục riêng, ví dụ `/home/ACCOUNT/malwareguard`. Trong **Domains**, trỏ Document Root của subdomain vào `/home/ACCOUNT/malwareguard/public`. Nếu host chỉ cho dùng `public_html`, chép toàn bộ nội dung `public/` (gồm CSS, JavaScript và logo) vào đó và đổi dòng `require` duy nhất trong `index.php` để trỏ đến `app/Http/workspace.php` trong thư mục riêng. Giữ config, database, bin và tests ngoài thư mục công khai.
3. Tạo database trống cùng database user trong **Manage My Databases**. Cấp quyền trên database đó. Import `database/schema.sql` một lần bằng phpMyAdmin.
4. Chép `config/example.php` thành `config/local.php`. Điền tên database, user và mật khẩu của hosting. Điền API key VirusTotal vào `vt_key` để tra cứu.
5. Mở Terminal của cPanel ở thư mục dự án. Dùng PHP CLI cùng phiên bản với website. Chạy `php bin/migrate.php` để khởi tạo cài đặt và ngôn ngữ trước khi mở trang. Tạo tài khoản, nhập mật khẩu ở lời nhắc.

~~~bash
php bin/migrate.php
php bin/user.php admin@example.org admin
php bin/user.php analyst@example.org analyst
~~~

## Cấu trúc giao diện

- `public/index.php` nhận yêu cầu. `app/Http/workspace.php` xử lý điều hướng và phân quyền.
- `app/Views/pages/` chứa trang đăng nhập, quét tệp, lịch sử, tổng quan, báo cáo, quản trị và trang lỗi.
- `app/Views/partials/` chứa head, header, footer, hình quét và danh sách lịch sử dùng chung.
- `public/assets/css/` chia kiểu cơ bản, giao diện làm việc, màn hình nhỏ và hiệu ứng. JavaScript nằm trong `public/assets/js/`, logo nằm trong `public/assets/img/`.

## Đồng bộ thitruongmmo.com

Hosting kiểm tra nhánh `main` mỗi 5 phút. Bản đang chạy nằm trong `~/thitruongmmo.com/malwareguard_app/`, với đầy đủ thư mục như trên Git. `public/` trỏ đến document root hiện tại `~/thitruongmmo.com/public_html/malwareguard/`. CSS, JS, font và logo có đường dẫn cố định dưới `assets/`. Cấu hình riêng nằm tại `config/local.php`. Script kiểm tra PHP, chạy migration, so sánh từng tệp với Git và kiểm tra tài nguyên qua HTTPS. Nếu kiểm tra thất bại, các tệp đã thay được phục hồi. Nhật ký triển khai nằm ở `~/thitruongmmo.com/.mg-deploy/deploy.log`.

Người dùng đăng ký từ trang đăng nhập và đổi mật khẩu hoặc ngôn ngữ trong **Tài khoản**. **Admin Panel** có thống kê toàn hệ thống, cài đặt website và trạng thái cấu hình VirusTotal. Tiếng Việt là mặc định ban đầu. Admin có mặc định riêng là tiếng Việt, mỗi tài khoản có thể lưu lựa chọn cá nhân.

Để thiết lập lần đầu trên đúng tài khoản hosting của website:

~~~bash
cd ~/thitruongmmo.com
mkdir -pm 700 .mg-deploy
git clone https://github.com/nathyu-le/mwana.git .mg-deploy/source
bash .mg-deploy/source/deploy/cpanel.sh
bash .mg-deploy/source/deploy/install-cron.sh
~~~

## Chạy thử

Chạy lệnh sau từ thư mục dự án để kiểm tra thuật toán và cách xử lý phản hồi VirusTotal. Bộ test CLI dùng dữ liệu giả lập và không cần database hoặc API key.

~~~bash
php tests/run.php
~~~

Để chạy web trên máy cá nhân, thực hiện các bước tạo database, cấu hình và tạo tài khoản như trên, sau đó chạy:

~~~bash
php -S 127.0.0.1:8000 -t public
~~~

Đăng nhập và chọn tệp tối đa 2 MiB trong **Trình quét** để mở báo cáo. Nếu chưa có kết quả cho SHA-256, trang hiển thị trạng thái chưa có báo cáo. Mỗi lần tra cứu mạng cách nhau ít nhất 16 giây. Hệ thống lưu đệm báo cáo 60 phút và kết quả chưa tìm thấy 5 phút. Các báo cáo nội bộ trước phiên bản 1.4 vẫn giữ được bằng chứng ban đầu.

Báo cáo hiển thị loại và họ mã độc khi VirusTotal cung cấp. Nhãn riêng của từng engine có tên nguồn đi kèm. Mức nguy hiểm và kết luận của VirusTotal được giữ riêng với điểm đồng thuận trên 100. Điểm bằng số engine báo độc chia cho số engine trả về một trong bốn kết quả malicious, suspicious, harmless, undetected, nhân 100 và làm tròn. Tỷ lệ đáng ngờ được tính riêng. Khi thiếu bộ đếm hoặc không có engine trả kết quả, điểm để trống. Điểm 0 chỉ cho biết chưa có engine báo độc. Các gợi ý điều tra quan hệ mạng và tệp được tạo ra chỉ xuất hiện khi báo cáo có chỉ dấu tương ứng.
