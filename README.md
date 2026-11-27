# MalwareGuard 1.3.0

Website phân tích tĩnh cho đồ án Malware Analysis, xây dựng bằng PHP 8.1+ và MySQL/MariaDB. Người dùng đăng nhập để quét tệp, xem bằng chứng và xuất báo cáo JSON. Quản trị viên quản lý chữ ký và bật hoặc tắt tra cứu VirusTotal.

## Cài trên cPanel

1. Chọn PHP 8.1 trở lên với PDO MySQL, fileinfo, ctype và cURL. Dùng HTTPS. Đặt `upload_max_filesize` từ 2M, `post_max_size` từ 4M và thư mục upload tạm bên ngoài webroot.
2. Đặt nội dung repo trực tiếp trong thư mục domain. Trong **Domains**, đặt Document Root của riêng domain này thành thư mục con `public/`. Ví dụ dự án ở `~/thitruongmmo.com/` thì Document Root là `~/thitruongmmo.com/public/`.
3. Tạo database trống cùng database user trong **Manage My Databases**. Cấp quyền trên database đó. Import `database/schema.sql` một lần bằng phpMyAdmin.
4. Chép `config/example.php` thành `config/local.php`. Điền tên database, user và mật khẩu của hosting. Có thể để trống `vt_key`.
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

Hosting kiểm tra nhánh `main` mỗi 5 phút. Bản đang chạy nằm trong `~/thitruongmmo.com/`, với đầy đủ thư mục như trên Git. Document root là `~/thitruongmmo.com/public/`. CSS, JS, font và logo có đường dẫn cố định dưới `assets/`. Cấu hình riêng nằm tại `config/local.php`. Script kiểm tra PHP, chạy migration, so sánh từng tệp với Git và kiểm tra tài nguyên qua HTTPS. Nếu kiểm tra thất bại, các tệp đã thay được phục hồi. Nhật ký triển khai nằm ở `~/.malwareguard-deploy/deploy.log`.

Người dùng đăng ký từ trang đăng nhập và đổi mật khẩu hoặc ngôn ngữ trong **Tài khoản**. **Admin Panel** có thống kê toàn hệ thống, cài đặt website và quản lý chữ ký. Tiếng Việt là mặc định ban đầu. Admin có mặc định riêng là tiếng Việt, mỗi tài khoản có thể lưu lựa chọn cá nhân.

Để thiết lập lần đầu trên đúng tài khoản hosting của website:

~~~bash
mkdir -pm 700 ~/.malwareguard-deploy
git clone https://github.com/nathyu-le/mwana.git ~/.malwareguard-deploy/source
bash ~/.malwareguard-deploy/source/deploy/cpanel.sh
bash ~/.malwareguard-deploy/source/deploy/install-cron.sh
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

Đăng nhập, vào **Trình quét**, chọn hoặc thả tệp `tests/demo.txt`. Báo cáo phải hiển thị chữ ký **Demo marker**, phiên bản 1, offset 0, cùng URL và IPv4 của mẫu. Tắt chữ ký trong **Chữ ký** rồi quét lại để quan sát sự khác biệt. Báo cáo cũ giữ nguyên bằng chứng ban đầu.

## Bộ phân tích

- Hash toàn tệp theo MD5 và SHA-256. KMP so khớp mẫu byte hex chính xác, lưu offset đầu tiên, nguồn, mẫu và phiên bản chữ ký.
- PE Explorer kiểm tra DOS header, PE signature, COFF, Optional Header PE32/PE32+ và phạm vi dữ liệu section. Tệp có header lỗi vẫn có báo cáo hash.
- Trích chuỗi ASCII và ký tự ASCII mã hóa UTF-16LE cùng URL, domain và IPv4. Offset tính bằng byte từ đầu tệp. Hệ thống chỉ hiển thị các địa chỉ này dưới dạng văn bản.
- Mỗi lượt tối đa 2 MiB, tối đa 32 chữ ký bật, 128 chuỗi và 128 chỉ dấu. Chuỗi dài được đọc theo đoạn tối đa 1.024 ký tự, hiển thị 256 ký tự mỗi đoạn. Có cảnh báo khi dữ liệu bị giới hạn. Ba trạng thái nội bộ gồm khớp chữ ký, chưa khớp và chưa hoàn tất. Chữ ký khớp vẫn được ghi nhận khi một phần phân tích bị giới hạn.
- Bản cài ban đầu có một chữ ký **minh họa vô hại**. Hiệu quả nhận dạng thực tế phụ thuộc bộ chữ ký do quản trị viên bổ sung. Báo cáo và chuỗi trích xuất có thể chứa dữ liệu của tệp gốc. Mỗi tài khoản chỉ xem được lịch sử của mình.

## Đối chiếu VirusTotal

Điền API key vào cấu hình riêng rồi bật tích hợp trong **Chữ ký**. Từ báo cáo đã lưu, chọn **Tra cứu SHA-256**. Backend gửi GET theo hash qua TLS. File và tên file không được gửi đi. Một lần tra cứu mạng cách lần tiếp theo ít nhất 16 giây trên mỗi bản cài, có xử lý 429 và Retry-After. Cache báo cáo được 60 phút, kết quả chưa có báo cáo được 5 phút. Kết quả ngoài có nguồn, thời điểm và trạng thái riêng.

Tài liệu kỹ thuật: [VirusTotal file report](https://docs.virustotal.com/reference/file-info) · [Microsoft PE format](https://learn.microsoft.com/en-us/windows/win32/debug/pe-format).
