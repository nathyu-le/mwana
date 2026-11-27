# MalwareGuard 1.2.0

Website phân tích tĩnh cho đồ án Malware Analysis, xây dựng bằng PHP 8.1+ và MySQL/MariaDB. Người dùng đăng nhập để quét tệp, xem bằng chứng và xuất báo cáo JSON. Quản trị viên quản lý chữ ký và bật hoặc tắt tra cứu VirusTotal.

## Cài trên cPanel

1. Chọn PHP 8.1 trở lên với PDO MySQL, fileinfo, ctype và cURL. Dùng HTTPS. Đặt `upload_max_filesize` từ 2M, `post_max_size` từ 4M và thư mục upload tạm bên ngoài webroot.
2. Clone repo vào thư mục riêng, ví dụ `/home/ACCOUNT/malwareguard`. Trong **Domains**, trỏ Document Root của subdomain vào `/home/ACCOUNT/malwareguard/public`. Nếu host chỉ cho dùng `public_html`, chép toàn bộ nội dung `public/` (gồm CSS, JavaScript và logo) vào đó và đổi dòng `require` duy nhất trong `index.php` để trỏ đến `app/Http/workspace.php` trong thư mục riêng. Giữ config, database, bin và tests ngoài thư mục công khai.
3. Tạo database trống cùng database user trong **Manage My Databases**. Cấp quyền trên database đó. Import `database/schema.sql` một lần bằng phpMyAdmin.
4. Chép `config/example.php` thành `config/local.php`. Điền tên database, user và mật khẩu của hosting. Có thể để trống `vt_key`.
5. Mở Terminal của cPanel ở thư mục dự án. Dùng PHP CLI cùng phiên bản với website. Tạo tài khoản, nhập mật khẩu ở lời nhắc.

~~~bash
php bin/user.php admin@example.org admin
php bin/user.php analyst@example.org analyst
~~~

## Cấu trúc giao diện

- `public/index.php` nhận yêu cầu. `app/Http/workspace.php` xử lý điều hướng và phân quyền.
- `app/Views/pages/` chứa trang đăng nhập, quét tệp, lịch sử, tổng quan, báo cáo, quản trị và trang lỗi.
- `app/Views/partials/` chứa head, header, footer, hình quét và danh sách lịch sử dùng chung.
- `public/assets/css/` chia kiểu cơ bản, giao diện làm việc, màn hình nhỏ và hiệu ứng. JavaScript nằm trong `public/assets/js/`, logo nằm trong `public/assets/img/`.

## Đồng bộ thitruongmmo.com

Hosting kiểm tra nhánh `main` mỗi 5 phút. Mỗi lần phát hành chạy test PHP, kiểm tra nội dung từng asset qua HTTPS rồi mới chuyển trang chủ sang commit mới. Nếu trang đăng nhập không vượt qua kiểm tra, script khôi phục index trước đó. Cấu hình riêng nằm ở `~/thitruongmmo.com/malwareguard_app/config/local.php`. Lịch sử triển khai nằm ở `~/thitruongmmo.com/.mg-deploy/deploy.log`.

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
