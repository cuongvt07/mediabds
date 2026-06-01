# Tài liệu API — MediaBDS

REST API phục vụ website Next.js (xem listing, đăng nhập CTV, gửi lead...).
Xác thực theo cơ chế **Sanctum SPA (cookie + CSRF)**, **không dùng Bearer token**.

> Cập nhật theo branch `feature/revert-image-upload-flow`.

---

## 1. Thông tin chung

| Mục | Giá trị |
|---|---|
| Base URL (dev) | `http://localhost:8000` |
| Tiền tố API | `/api` → các route nghiệp vụ nằm dưới `/api/v1` |
| Origin frontend (dev) | `http://localhost:3000` |
| Định dạng | JSON. Luôn gửi header `Accept: application/json` |
| Charset | UTF-8 (dữ liệu tiếng Việt có dấu) |

Luôn gửi `Accept: application/json`. Nếu thiếu, Laravel có thể trả HTML/redirect thay vì JSON khi lỗi validate.

---

## 2. Xác thực (Sanctum SPA — cookie)

API dùng **session cookie**, không trả về token. Trình duyệt phải gửi kèm cookie và header CSRF.

### Luồng chuẩn cho frontend

```
1. GET  /sanctum/csrf-cookie          → server set cookie XSRF-TOKEN + session
2. POST /api/v1/auth/login            → tạo session đăng nhập
3. GET  /api/v1/me, POST /api/v1/...  → cookie tự gửi kèm, đã đăng nhập
4. POST /api/v1/auth/logout           → huỷ session
```

**Bắt buộc:**
- Mọi request gửi kèm cookie → `withCredentials: true` (axios) / `credentials: 'include'` (fetch).
- Mọi request **ghi** (`POST/PUT/DELETE`) phải kèm header `X-XSRF-TOKEN` = giá trị cookie `XSRF-TOKEN` (đã URL-decode). Axios làm tự động; fetch phải tự set.
- Domain của frontend phải nằm trong `SANCTUM_STATEFUL_DOMAINS` của server.

### Ví dụ axios (khuyên dùng)

```js
import axios from 'axios';

const api = axios.create({
  baseURL: 'http://localhost:8000',
  withCredentials: true,   // gửi/nhận cookie
  withXSRFToken: true,     // axios >= 1.6: tự gắn X-XSRF-TOKEN
  headers: { Accept: 'application/json' },
});

// 1) Lấy CSRF cookie (chỉ cần gọi 1 lần trước khi POST/PUT/DELETE)
await api.get('/sanctum/csrf-cookie');

// 2) Đăng nhập
await api.post('/api/v1/auth/login', { phone: '0900000000', password: 'secret' });

// 3) Gọi API đã xác thực — cookie tự kèm
const { data } = await api.get('/api/v1/me');
```

### Ví dụ fetch (phải tự set X-XSRF-TOKEN)

```js
function getCookie(name) {
  return document.cookie.split('; ').find(r => r.startsWith(name + '='))?.split('=')[1];
}

await fetch('http://localhost:8000/sanctum/csrf-cookie', { credentials: 'include' });

await fetch('http://localhost:8000/api/v1/auth/login', {
  method: 'POST',
  credentials: 'include',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'X-XSRF-TOKEN': decodeURIComponent(getCookie('XSRF-TOKEN')),
  },
  body: JSON.stringify({ phone: '0900000000', password: 'secret' }),
});
```

> ⚠️ Vì là cookie-SPA, client **không phải trình duyệt** (mobile, server-to-server) gọi `login` sẽ lỗi session. Nếu cần token cho mobile, phải bổ sung phát hành token ở backend (chưa có).

---

## 3. Quy ước Response

⚠️ **Envelope không đồng nhất giữa các endpoint** — frontend xử lý theo từng nhóm dưới đây.

### a) Endpoint hành động / auth / lead / stats — có bọc `success`
```json
{ "success": true, "data": { ... }, "message": "OK" }
```
Lỗi nghiệp vụ:
```json
{ "success": false, "message": "Mô tả lỗi", "errors": { ... } }
```

### b) Danh sách (có phân trang) — `index`, `me/listings`
```json
{
  "data": [ { ...listing }, ... ],
  "links": { "first": "...", "last": "...", "prev": null, "next": "..." },
  "meta": { "current_page": 1, "per_page": 12, "total": 134, "last_page": 12 }
}
```

### c) Chi tiết 1 bản ghi — `show`, `me`
```json
{ "data": { ...listing } }
```

### d) Lỗi validate (HTTP 422) — format mặc định Laravel, **không có** `success`
```json
{
  "message": "The phone field is required.",
  "errors": { "phone": ["The phone field is required."] }
}
```

> 👉 Khuyến nghị frontend **luôn dựa vào HTTP status code**, không chỉ dựa vào cờ `success`.

### Bảng HTTP status
| Code | Khi nào | Body |
|---|---|---|
| 200 | Thành công | tuỳ endpoint |
| 201 | Tạo mới (register, store, leads) | có `data` |
| 401 | Chưa đăng nhập / sai thông tin | `{ "message": "Unauthenticated." }` hoặc `{ success:false, message }` |
| 403 | Không đủ quyền (policy) | `{ "message": "This action is unauthorized." }` |
| 404 | Không tìm thấy | `{ success:false, message }` hoặc mặc định |
| 422 | Lỗi validate | `{ message, errors }` |
| 429 | Vượt rate limit | `{ "message": "Too Many Attempts." }` + header `Retry-After` |

---

## 4. Danh sách Endpoint

| Method | Path | Auth | Rate limit |
|---|---|---|---|
| POST | `/api/v1/auth/register` | Không | 5 / phút |
| POST | `/api/v1/auth/login` | Không | 10 / phút |
| GET | `/api/v1/auth/me` | ✅ | — |
| POST | `/api/v1/auth/logout` | ✅ | — |
| GET | `/api/v1/listings` | Không | — |
| GET | `/api/v1/listings/{idOrCode}` | Không | — |
| POST | `/api/v1/listings` | ✅ (CTV) | — |
| PUT | `/api/v1/listings/{id}` | ✅ (CTV, chủ tin/admin) | — |
| DELETE | `/api/v1/listings/{id}` | ✅ (admin) | — |
| POST | `/api/v1/leads` | Không | 3 / 5 phút |
| GET | `/api/v1/me` | ✅ | — |
| GET | `/api/v1/me/listings` | ✅ | — |
| GET | `/api/v1/me/stats` | ✅ | — |

---

## 5. Auth

### 5.1 Đăng ký — `POST /api/v1/auth/register`

| Field | Kiểu | Bắt buộc | Ràng buộc |
|---|---|---|---|
| `name` | string | ✅ | tối đa 120 |
| `phone` | string | ✅ | 9–15 ký tự, **duy nhất** |
| `password` | string | ✅ | tối thiểu 6, cần `password_confirmation` khớp |
| `password_confirmation` | string | ✅ | trùng `password` |
| `invite_code` | string | — | nếu có phải tồn tại trong hệ thống |

- Có `invite_code` hợp lệ → role = **`ctv`**; không có → **`buyer`**.
- Hệ thống tự sinh `invite_code` cho user mới: `[mã người mời][id]` hoặc `BD[id]` với buyer.
- Đăng ký xong **tự đăng nhập** (tạo session).

**Response `201`:** `{ success:true, data:<User>, message:"Registered" }` (xem [User object](#71-user-object)).

```bash
curl -X POST http://localhost:8000/api/v1/auth/register \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -H "X-XSRF-TOKEN: <token>" --cookie "..." \
  -d '{"name":"Nguyễn A","phone":"0900000001","password":"secret","password_confirmation":"secret","invite_code":"BD12"}'
```

### 5.2 Đăng nhập — `POST /api/v1/auth/login`

| Field | Kiểu | Bắt buộc | Ràng buộc |
|---|---|---|---|
| `phone` | string | ✅ | 9–15 ký tự |
| `password` | string | ✅ | tối thiểu 6 |

- **200:** `{ success:true, data:<User>, message:"OK" }`
- **401:** `{ success:false, message:"Sai số điện thoại hoặc mật khẩu" }`

### 5.3 Thông tin tôi — `GET /api/v1/auth/me`  *(auth)*
**200:** `{ success:true, data:<User> }` · **401** nếu chưa đăng nhập.

### 5.4 Đăng xuất — `POST /api/v1/auth/logout`  *(auth)*
**200:** `{ success:true, message:"Logged out" }` (huỷ session).

---

## 6. Listings

### 6.1 Danh sách — `GET /api/v1/listings`

Công khai. Chỉ trả tin **chưa bán** (`is_sold = false`). Có phân trang.

**Query params (tất cả tuỳ chọn):**

| Param | Kiểu | Mô tả |
|---|---|---|
| `per_page` | int | Mặc định 12, tối đa 30 |
| `page` | int | Trang (phân trang Laravel) |
| `type` | string | `Cần bán` \| `Cho thuê` \| `Cần mua` |
| `property_type` | int | Mã loại BĐS — xem [bảng property_type](#72-property_type) |
| `province` | string | **Mã tỉnh (id) hoặc tên** — khớp cả hai |
| `district` | string | **Mã huyện (id) hoặc tên** |
| `ward` | string | **Mã xã (id) hoặc tên** |
| `bedrooms` | int | Lọc số phòng ngủ **≥** giá trị |
| `direction` | string | Hướng (vd `Đông Nam`) |
| `min_area` / `max_area` | number | Diện tích m² (khoảng) |
| `min_price` / `max_price` | number | **Đơn vị: TỶ** (vd `min_price=2` = 2 tỷ). Server tự quy đổi theo `price_unit` |
| `sort_by` | string | `created_at` (mặc định) \| `price` \| `area` |
| `sort_order` | string | `desc` (mặc định) \| `asc` |

**Response `200`:** dạng [danh sách phân trang](#b-danh-sách-có-phân-trang--index-melistings), mỗi phần tử là [Listing object](#73-listing-object).

```bash
curl "http://localhost:8000/api/v1/listings?type=Cần%20bán&property_type=107&province=Hà%20Nội&min_price=2&max_price=5&bedrooms=3&sort_by=price&sort_order=asc&per_page=12" \
  -H "Accept: application/json"
```

> ℹ️ Khi **chưa đăng nhập**, `contact_phone` bị che (`090*******`) và `contact_phones` rỗng. Đăng nhập rồi mới thấy đầy đủ (`can_view_phone: true`).

### 6.2 Chi tiết — `GET /api/v1/listings/{idOrCode}`

Công khai. `{idOrCode}` là **id** (số) hoặc **code** (vd `API-AB12CD`).

- **200:** `{ "data": <Listing> }`
- **404:** `{ success:false, message:"Không tìm thấy tin" }`

### 6.3 Tạo tin — `POST /api/v1/listings`  *(auth, CTV)*

Yêu cầu đăng nhập **và** là CTV/admin (`isCtv`). Không đủ quyền → **403**.

| Field | Kiểu | Bắt buộc | Ràng buộc |
|---|---|---|---|
| `title` | string | ✅ | tối đa 255 |
| `type` | string | ✅ | `Cần bán` \| `Cho thuê` \| `Cần mua` |
| `property_type` | int | ✅ | mã loại BĐS (xem bảng) |
| `price` | number | ✅ | ≥ 0 |
| `price_unit` | string | ✅ | `Tỷ` \| `Triệu` \| `VNĐ/tháng` \| `Thỏa thuận` |
| `area` | number | ✅ | ≥ 0 (m²) |
| `contact_phone` | string | ✅ | có thể nhiều số, ngăn cách `, / -` |
| `address` | string | — | |
| `description` | string | — | |
| `bedrooms` | int | — | |
| `toilets` | int | — | |
| `floors` | int | — | |
| `direction` | string | — | |
| `front_width` | number | — | mặt tiền (m) |
| `road_width` | number | — | đường trước nhà (m) |

- `code` được sinh tự động dạng `API-XXXXXX`; `user_id` = user hiện tại.
- **201:** `{ success:true, data:<Listing>, message:"Created" }`

### 6.4 Cập nhật — `PUT /api/v1/listings/{id}`  *(auth, chủ tin hoặc admin)*

Cùng bộ field như [tạo tin](#63-tạo-tin--post-apiv1listings). Quyền: **admin hoặc chủ tin** (`user_id` trùng). Sai quyền → **403**, không tồn tại → **404**.

**200:** `{ success:true, data:<Listing> }`

### 6.5 Xoá — `DELETE /api/v1/listings/{id}`  *(auth, admin)*

Chỉ **admin**. **200:** `{ success:true, message:"Deleted" }` · **403** nếu không phải admin.

---

## 7. Me (tài khoản hiện tại) *(auth)*

### 7.1 `GET /api/v1/me`
**200:** `{ "data": <User> }`

### 7.2 `GET /api/v1/me/listings`
Tin do user đăng, mới nhất trước, có phân trang (`per_page`, mặc định 10).
**200:** [danh sách phân trang](#b-danh-sách-có-phân-trang--index-melistings) các [Listing](#73-listing-object).

### 7.3 `GET /api/v1/me/stats`
**200:**
```json
{
  "success": true,
  "data": {
    "total_revenue": 1500000000,
    "invites_count": 4,
    "rank": { "name": "Vàng", "min_price": 1 },
    "listings_count": 12,
    "listings_sold": 3
  }
}
```
`rank` có thể `null` nếu chưa đạt hạng nào.

---

## 8. Leads — `POST /api/v1/leads`

Công khai (form liên hệ trên website). Rate limit **3 lần / 5 phút**.

| Field | Kiểu | Bắt buộc | Ràng buộc |
|---|---|---|---|
| `name` | string | ✅ | tối đa 120 |
| `phone` | string | ✅ | 9–15 ký tự |
| `budget_from` | number | — | ≥ 0 (VNĐ) |
| `budget_to` | number | — | ≥ 0 (VNĐ) |
| `message` | string | — | tối đa 1000 |
| `listing_id` | int | — | id listing đang quan tâm (nếu có) |

- Tạo `Customer` (status `mua`, chưa phân công) + 1 dòng timeline `CustomerWork`.
- **201:**
```json
{ "success": true, "data": { "lead_id": 88, "code": "KH000088" }, "message": "Đã ghi nhận, chúng tôi sẽ liên hệ sớm" }
```

```bash
curl -X POST http://localhost:8000/api/v1/leads \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -H "X-XSRF-TOKEN: <token>" --cookie "..." \
  -d '{"name":"Trần B","phone":"0900000002","budget_from":2000000000,"budget_to":5000000000,"message":"Cần nhà 3 phòng ngủ","listing_id":12}'
```

---

## 9. Phụ lục — Cấu trúc dữ liệu

### 9.1 User object
```json
{
  "id": 12,
  "name": "Nguyễn A",
  "email": null,
  "phone": "0900000001",
  "role": "ctv",
  "avatar": "https://...",
  "invite_code": "BD12",
  "property_types": [107, 108],
  "rank": { "id": 2, "name": "Vàng", "min_price": 1, "min_invites": 3 },
  "total_revenue": 1500000000,
  "invites_count": 4,
  "trial_ends_at": null,
  "license_expires_at": "2026-12-31T00:00:00.000000Z",
  "is_admin": false,
  "created_at": "2026-05-30T10:00:00.000000Z"
}
```
> Không bao giờ trả `password`, `view_phone_pin`, `remember_token`. `role`: `buyer` | `ctv` | `admin`.

### 9.2 property_type
Request gửi **mã số (int)**; response trả về **nhãn (string)** ở field `property_type`.

| Mã | Nhãn |
|---|---|
| 102 | Biệt thự |
| 103 | Căn hộ – chung cư |
| 104 | Đất |
| 105 | Đất nền dự án |
| 106 | Mặt tiền |
| 107 | Nhà mặt phố |
| 108 | Nhà riêng |
| 109 | Trang trại |
| 110 | Bất động sản khác |
| 111 | Nhà mặt phố (LG 4M-5M) |
| 112 | Khách sạn |
| 113 | Nhà nghỉ |
| 114 | Homestay |
| 115 | Nhà trọ |

### 9.3 Listing object
```json
{
  "id": 12,
  "code": "API-AB12CD",
  "title": "Nhà mặt phố Cầu Giấy",
  "type": "Cần bán",
  "property_type": "Nhà mặt phố",
  "price": 5.5,
  "price_unit": "Tỷ",
  "area": 60,
  "address": "Số 1 ...",
  "ward_name": "Dịch Vọng",
  "district_name": "Cầu Giấy",
  "province_name": "Hà Nội",
  "floors": 4,
  "bedrooms": 3,
  "toilets": 3,
  "direction": "Đông Nam",
  "front_width": 4.5,
  "road_width": 8,
  "avatar": "https://...",
  "images": ["https://...", "https://..."],
  "is_sold": false,
  "created_at": "2026-05-30T10:00:00.000000Z",
  "updated_at": "2026-05-30T10:00:00.000000Z",
  "can_view_phone": false,
  "contact_phone": "090*******",
  "contact_phones": []
}
```
- `property_type`: **nhãn** (đã map từ mã).
- `price` + `price_unit`: giá hiển thị theo đơn vị (vd `5.5` `Tỷ`).
- `contact_phone` / `contact_phones`: chỉ đầy đủ khi đăng nhập (`can_view_phone: true`); khách vãng lai bị che.

---

## 10. Lưu ý quan trọng cho frontend

1. **Xác thực = cookie**, không phải Bearer token → bắt buộc `withCredentials` + gọi `/sanctum/csrf-cookie` trước khi POST/PUT/DELETE.
2. **Envelope khác nhau** giữa list / chi tiết / hành động → bám theo HTTP status, đừng chỉ đọc `success`.
3. **`property_type`**: gửi đi là **số**, nhận về là **chữ**.
4. **`min_price`/`max_price`** đơn vị **TỶ**; `budget_from`/`budget_to` (leads) đơn vị **VNĐ**.
5. **SĐT bị che** khi chưa đăng nhập.
6. Quyền: tạo tin cần **CTV**, sửa cần **chủ tin/admin**, xoá cần **admin**.
