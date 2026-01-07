# Hệ Thống Quản Trị Lưu Trữ File - System Design Specification

## 1. Giới Thiệu Dự Án (Project Overview)
Xây dựng hệ thống quản lý tập tin trên nền tảng web với trải nghiệm người dùng tiệm cận ứng dụng native (Windows Explorer/macOS Finder). Hệ thống được xây dựng trên Laravel Livewire, tối ưu hóa cho tương tác thời gian thực và quản lý tài nguyên hiệu quả trên Cloud Storage.

## 2. Kiến Trúc Hệ Thống (System Architecture)

### 2.1. Tech Stack
- **Backend Core**: Laravel 11.x
- **Frontend Logic**: Laravel Livewire 3.x
- **Interactivity**: Alpine.js v3 (Drag & Drop, Modal, Context Menu)
- **Styling**: Tailwind CSS (Utility-first framework)
- **Database**: MySQL 8.0+ hoặc PostgreSQL
- **Storage**: AWS S3 / MinIO / DigitalOcean Spaces (S3 Adapter)
- **Queue/Cache**: Redis

### 2.2. Luồng Xử Lý Dữ Liệu (Data Flow) - Optimized for Hosting
1. **Upload (Direct S3)**: Client -> Request Presigned URL (Server) -> Upload to S3 (Direct) -> Confirm (Server) -> Database Record.
    *   *Không đi qua Server Disk để tránh giới hạn hosting và timeout.*
2. **Download**: Client -> Generate Presigned URL -> S3 Direct Download.
3. **Delete**: Client -> Server Request -> S3 API Delete (Sync) -> DB Delete.

## 3. Thiết Kế Cơ Sở Dữ Liệu (Database Schema)

### Bảng `folders`
| Column | Type | Description |
|--------|------|-------------|
| id | UUID | Primary Key |
| parent_id | UUID | FK -> folders.id (Nullable) |
| name | String | Tên thư mục |
| path | String | Đường dẫn vật lý hoặc logic |
| user_id | BigInt | Người tạo |
| created_at | Timestamp | |

### Bảng `files`
| Column | Type | Description |
|--------|------|-------------|
| id | UUID | Primary Key |
| folder_id | UUID | FK -> folders.id (Nullable - Root) |
| name | String | Tên hiển thị |
| path | String | Đường dẫn trên S3 |
| disk | String | Tên disk (s3, local, etc) |
| mime_type | String | Loại file |
| size | BigInt | Kích thước (bytes) |
| checksum | String | MD5/SHA256 hash |
| metadata | JSON | Info chi tiết (width, height, duration) |
| created_at | Timestamp | |

## 4. Đặc Tả Tính Năng (Feature Specifications)

### 4.1. Quản Lý File & Thư Mục (File Operations)
- **Tree Structure**: Sử dụng đệ quy component hoặc thư viện cây để hiển thị sidebar.
- **Drag & Drop**:
    - Sử dụng HTML5 Drag and Drop API kết hợp Alpine.js directives (`@dragstart`, `@drop`).
    - Hỗ trợ kéo file từ máy tính vào trình duyệt để upload.
    - Hỗ trợ kéo file/folder nội bộ để di chuyển (Move).
- **Multi-select**:
    - Quản lý state `selectedIds` array trong Livewire.
    - Key modifier handling (Shift/Ctrl/Cmd) trong Alpine.js.

### 4.2. Cloud Storage Integration (Direct Upload)
- **Cấu hình**: `config/filesystems.php` với driver `s3`.
- **Cơ chế Upload (4GB+ Support)**:
    - Sử dụng **Multipart Upload API** của S3 (thông qua AWS SDK JS hoặc thư viện Upload như Uppy/Dropzone ở Client).
    - **Bước 1**: Client gọi API `initiate-upload`.
    - **Bước 2**: Server trả về `UploadId` và danh sách Presigned URLs cho từng chunk.
    - **Bước 3**: Client upload song song các chunk lên S3.
    - **Bước 4**: Client gọi API `complete-upload`. Server verify và lưu vào DB.
- **Lợi ích**:
    - Không ngốn disk server (0GB local storage used).
    - Không phụ thuộc config `upload_max_filesize` của PHP/Nginx.
    - Không cần Queue/Job phức tạp.

### 4.3. Giao Diện & User Experience
- **Responsive**: Mobile-first design. Trên mobile ẩn sidebar, dùng drawer menu.
- **Context Menu**: Custom context menu component (position fixed, calculated by mouse coordinates).
- **Shortcuts**:
    - `F2`: Rename
    - `Del`: Delete
    - `Ctrl+C`/`Ctrl+V`: Copy/Paste logic (lưu clipboard state).

## 5. Kế Hoạch Triển Khai (Implementation Roadmap)
1. **Phase 1: Setup & Core**: Cài đặt Laravel, DB Migration, Authentication.
2. **Phase 2: UI Foundation**: Layout, Sidebar, File Grid Components.
3. **Phase 3: File Logic**: Upload, Create Folder, Rename, Delete.
4. **Phase 4: Advanced Features**: Drag & Drop, Multi-select, Context Menu.
5. **Phase 5: Cloud & Optimization**: S3 Integration, Chunked Upload, Queues.

---
*Tài liệu này được tạo tự động bởi Antigravity Agent theo yêu cầu dự án.*
