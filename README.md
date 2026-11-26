# MalwareGuard

Website phân tích tĩnh cho đồ án Malware Analysis, xây dựng bằng PHP 8.1+ và MySQL/MariaDB. Người dùng đăng nhập để quét tệp, xem bằng chứng và xuất báo cáo JSON. Quản trị viên quản lý chữ ký và bật hoặc tắt tra cứu VirusTotal.

## Cài trên cPanel

1. Chọn PHP 8.1 trở lên với PDO MySQL, fileinfo, ctype và cURL. Dùng HTTPS. Đặt `upload_max_filesize` từ 2M, `post_max_size` từ 4M và thư mục upload tạm bên ngoài webroot.
2. Clone repo vào thư mục riêng, ví dụ `/home/ACCOUNT/malwareguard`. Trong **Domains**, trỏ Document Root của subdomain vào `/home/ACCOUNT/malwareguard/public`. Nếu host chỉ cho dùng `public_html`, chép hai file trong `public/` vào đó và đổi các đường dẫn `dirname(__DIR__).'/app/…'` trong `index.php` để trỏ tới thư mục app thật. Giữ config, database, bin và tests ngoài thư mục công khai.
3. Tạo database trống cùng database user trong **Manage My Databases**. Cấp quyền trên database đó. Import `database/schema.sql` một lần bằng phpMyAdmin.
4. Chép `config/example.php` thành `config/local.php`. Điền tên database, user và mật khẩu của hosting. Có thể để trống `vt_key`.
5. Mở Terminal của cPanel ở thư mục dự án. Dùng PHP CLI cùng phiên bản với website. Tạo tài khoản, nhập mật khẩu ở lời nhắc.

~~~bash
php bin/user.php admin@example.org admin
php bin/user.php analyst@example.org analyst
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

Đăng nhập, chọn **Phân tích file**, tải `tests/demo.txt`. Báo cáo phải hiển thị chữ ký **Demo marker**, phiên bản 1, offset 0, cùng URL và IPv4 của mẫu. Tắt chữ ký trong **Quản trị** rồi quét lại để quan sát sự khác biệt. Báo cáo cũ giữ nguyên bằng chứng ban đầu.

## Bộ phân tích

- Hash toàn tệp theo MD5 và SHA-256. KMP so khớp mẫu byte hex chính xác, lưu offset đầu tiên, nguồn, mẫu và phiên bản chữ ký.
- PE Explorer kiểm tra DOS header, PE signature, COFF, Optional Header PE32/PE32+ và phạm vi dữ liệu section. Tệp có header lỗi vẫn có báo cáo hash.
- Trích chuỗi ASCII và ký tự ASCII mã hóa UTF-16LE cùng URL, domain và IPv4. Offset tính bằng byte từ đầu tệp. Hệ thống chỉ hiển thị các địa chỉ này dưới dạng văn bản.
- Mỗi lượt tối đa 2 MiB, tối đa 32 chữ ký bật, 128 chuỗi và 128 chỉ dấu. Chuỗi dài được đọc theo đoạn tối đa 1.024 ký tự, hiển thị 256 ký tự mỗi đoạn. Có cảnh báo khi dữ liệu bị giới hạn. Ba trạng thái nội bộ gồm khớp chữ ký, chưa khớp và chưa hoàn tất. Chữ ký khớp vẫn được ghi nhận khi một phần phân tích bị giới hạn.
- Bản cài ban đầu có một chữ ký **minh họa vô hại**. Hiệu quả nhận dạng thực tế phụ thuộc bộ chữ ký do quản trị viên bổ sung. Báo cáo và chuỗi trích xuất có thể chứa dữ liệu của tệp gốc. Mỗi tài khoản chỉ xem được lịch sử của mình.

## Đối chiếu VirusTotal

Điền API key vào cấu hình riêng rồi bật tích hợp trong **Quản trị**. Từ báo cáo đã lưu, chọn **Tra cứu SHA-256**. Backend gửi GET theo hash qua TLS. File và tên file không được gửi đi. Một lần tra cứu mạng cách lần tiếp theo ít nhất 16 giây trên mỗi bản cài, có xử lý 429 và Retry-After. Cache báo cáo được 60 phút, kết quả chưa có báo cáo được 5 phút. Kết quả ngoài có nguồn, thời điểm và trạng thái riêng.

Tài liệu kỹ thuật: [VirusTotal file report](https://docs.virustotal.com/reference/file-info) · [Microsoft PE format](https://learn.microsoft.com/en-us/windows/win32/debug/pe-format).
