# PRD — PadelHub
### Platform Booking Padel Modern & Premium

| | |
|---|---|
| **Version** | 1.0 |
| **Date** | Agustus 2026 |
| **Status** | Draft |
| **Tech Stack** | Laravel 12, Vue 3, TypeScript, Shadcn Vue, PostgreSQL, Filament, Laravel Reverb, Redis |

---

## 1. Executive Summary

PadelHub adalah platform booking lapangan padel modern yang memungkinkan pemain melakukan pemesanan lapangan secara real-time, melakukan pembayaran online, bergabung dengan pertandingan terbuka, mengikuti turnamen, dan mengelola aktivitas bermain dalam satu aplikasi.

Platform ini juga menyediakan dashboard operasional bagi pemilik venue untuk mengelola lapangan, jadwal, pembayaran, promo, serta analitik bisnis.

---

## 2. Product Vision

Menjadi platform booking dan komunitas padel terbesar di Indonesia dengan pengalaman pengguna yang modern, cepat, elegan, dan terintegrasi.

---

## 3. Problem Statement

### 3.1 Masalah Pemain
- Booking masih dilakukan melalui WhatsApp
- Sulit mengetahui slot lapangan yang tersedia
- Tidak ada konfirmasi otomatis
- Sulit mencari partner bermain
- Tidak ada histori permainan yang terstruktur

### 3.2 Masalah Venue
- Jadwal lapangan dikelola manual
- Risiko double booking tinggi
- Sulit memantau okupansi lapangan
- Sulit mengelola promo dan membership
- Tidak memiliki data analitik pelanggan

---

## 4. Product Goals

### 4.1 Business Goals
- Mengurangi proses booking manual hingga 90%
- Meningkatkan okupansi lapangan minimal 30%
- Menambah revenue melalui membership dan event
- Menjadi platform booking padel nasional

### 4.2 User Goals
- Booking lapangan kurang dari 1 menit
- Mengetahui ketersediaan lapangan secara real-time
- Pembayaran otomatis dan aman
- Dapat menemukan lawan bermain dengan mudah

---

## 5. Target Users

### 5.1 Player
**Karakteristik**
- Usia 18–45 tahun
- Bermain padel secara rutin
- Membutuhkan booking cepat

**Kebutuhan**
- Booking lapangan
- Cari partner bermain
- Riwayat permainan
- Pembayaran online

### 5.2 Venue Owner
**Karakteristik**
- Pemilik lapangan padel
- Operator venue

**Kebutuhan**
- Manajemen lapangan
- Pengelolaan booking
- Monitoring pendapatan
- Analitik bisnis

### 5.3 Administrator
**Kebutuhan**
- Monitoring sistem
- Verifikasi venue
- Monitoring transaksi
- Pengelolaan promo

---

## 6. Success Metrics

| Metric | Target |
|---|---|
| Booking Success Rate | > 95% |
| Payment Success Rate | > 98% |
| Average Booking Time | < 60 detik |
| Monthly Active Users | 10.000+ |
| Occupancy Increase | +30% |
| User Satisfaction | > 4.5 / 5 |

---

## 7. User Journey — Booking Flow

```
Login
  ↓
Cari Venue
  ↓
Pilih Court
  ↓
Pilih Tanggal
  ↓
Pilih Slot
  ↓
Checkout
  ↓
Pembayaran
  ↓
QR Check-In
```

---

## 8. Functional Requirements

### Module A — Authentication

| Fitur | Detail |
|---|---|
| Register | Full Name, Email, Phone Number, Password |
| Login | Email, Google OAuth, Apple Login |
| Forgot Password | Email Verification, Password Reset Link |
| Profile Management | Avatar, Full Name, Phone, Gender, Birth Date, Skill Level |

### Module B — Venue Discovery

| Fitur | Detail |
|---|---|
| Search Venue | Filter: City, Price Range, Rating, Indoor, Outdoor |
| Venue Detail | Gallery, Description, Facilities, Operating Hours, Available Courts, Reviews |
| Favorite Venue | User dapat menyimpan venue |

### Module C — Real-Time Court Availability

| Fitur | Detail |
|---|---|
| Interactive Calendar | Status: Available, Reserved, Booked, Maintenance |
| Real-Time Update | Teknologi: Laravel Reverb (WebSocket). Tidak perlu refresh halaman, perubahan langsung ter-reflect |

### Module D — Booking Management

| Fitur | Detail |
|---|---|
| Create Booking | User memilih Venue, Court, Date, Time Slot |
| Slot Locking | Slot dikunci 5 menit saat checkout dimulai; otomatis dilepas jika pembayaran gagal |
| Booking Status | Pending, Confirmed, Checked In, Finished, Cancelled, Expired |

### Module E — Payment System

| Fitur | Detail |
|---|---|
| Payment Gateway | QRIS, Bank Transfer, Credit Card, E-Wallet |
| Provider Disarankan | Midtrans, Xendit |
| Payment Status | Pending, Paid, Failed, Expired, Refunded |

### Module F — QR Check-In

| Fitur | Detail |
|---|---|
| Generate QR Code | Dibuat otomatis setelah pembayaran berhasil |
| Check-In Status | Booked, Checked In, Completed, No Show |
| Scan | Staff scan QR melalui venue dashboard |

### Module G — Open Match

| Fitur | Detail |
|---|---|
| Create Match | Fields: Venue, Court, Date, Time, Skill Level, Available Slots |
| Join Match | Pemain lain dapat bergabung |
| Match Status | Open, Full, Completed, Cancelled |

### Module H — Social Features

| Fitur | Detail |
|---|---|
| Player Finder | Cari pemain berdasarkan Skill Level, City, Gender |
| Chat | Realtime Messaging via Laravel Reverb + Echo. Mendukung Direct Message & Match Group Chat |

### Module I — Membership

| Level | Benefit |
|---|---|
| Bronze | Standard Booking |
| Silver | 5% Cashback, Priority Booking |
| Gold | 10% Cashback, Free Cancellation |

### Module J — Loyalty Program

| Fitur | Detail |
|---|---|
| Point System | 1 Booking = 100 Points |
| Redeem | Discount Voucher, Merchandise, Free Court Hours |

### Module K — Event & Tournament

| Fitur | Detail |
|---|---|
| Venue Dapat Membuat | Tournament, Coaching Clinic, Open Sparring |
| Registration | User dapat mendaftar langsung |
| Event Fields | Banner, Description, Date, Location, Fee, Capacity |

### Module L — Review & Rating

| Fitur | Detail |
|---|---|
| Review | User dapat memberi review setelah booking selesai |
| Fields | Rating (1–5 Stars), Review Text |

---

## 9. Venue Dashboard

### 9.1 Revenue Analytics
- **Metrics:** Daily Revenue, Weekly Revenue, Monthly Revenue
- **Charts:** Revenue Trend, Booking Trend

### 9.2 Occupancy Analytics
- **Metrics:** Court Usage, Peak Hours, Utilization Rate

### 9.3 Court Management
- **CRUD:** Courts, Pricing, Availability, Maintenance Schedule

---

## 10. Admin Dashboard (Filament)

| Modul | Fitur |
|---|---|
| User Management | View Users, Suspend User, Verify User |
| Venue Management | Approve Venue, Reject Venue, Edit Venue |
| Booking Management | Refund Booking, Manual Override |
| Promotion Management | Coupons, Promo Codes, Membership Plans |
| System Monitoring | Active Users, Revenue, Transactions |

---

## 11. Non-Functional Requirements

| Aspek | Requirement |
|---|---|
| **Performance** | Page Load < 2 detik, API Response < 500 ms |
| **Scalability** | Mendukung 100.000 Users, 10.000 Concurrent Sessions |
| **Security** | Laravel Sanctum, CSRF Protection, XSS Protection, SQL Injection Protection, Rate Limiting |
| **Availability** | Target Uptime 99.9% |

---

## 12. UI/UX Requirements

### 12.1 Design Principles
**Keywords:** Premium, Elegant, Modern, Minimalist, Fast
**References:** Apple, Airbnb, Linear, Stripe

### 12.2 Color Palette

| Nama | Hex |
|---|---|
| Primary | `#0F172A` |
| Accent | `#22C55E` |
| Background | `#F8FAFC` |
| Border | `#E2E8F0` |
| Text | `#0F172A` |

### 12.3 Typography
- **Primary:** Inter
- **Fallback:** System Sans

### 12.4 Components (Shadcn Vue)
Button, Card, Dialog, Drawer, Calendar, Command Search, Table, Tabs, Badge, Skeleton, Toast, Popover

### 12.5 Responsive Design
Breakpoints: Mobile, Tablet, Desktop, Large Desktop

---

## 13. Database Schema

### `users`
`id`, `name`, `email`, `phone`, `password`, `avatar`, `gender`, `birth_date`, `skill_level`, `created_at`, `updated_at`

### `venues`
`id`, `name`, `slug`, `description`, `address`, `city`, `latitude`, `longitude`, `rating`, `status`, `created_at`, `updated_at`

### `courts`
`id`, `venue_id`, `name`, `price_per_hour`, `status`, `created_at`, `updated_at`

### `bookings`
`id`, `user_id`, `court_id`, `booking_date`, `start_time`, `end_time`, `status`, `payment_status`, `total_amount`, `created_at`, `updated_at`

### `payments`
`id`, `booking_id`, `amount`, `method`, `provider`, `status`, `transaction_reference`, `created_at`, `updated_at`

### `memberships`
`id`, `name`, `price`, `benefits`, `created_at`, `updated_at`

### `open_matches`
`id`, `creator_id`, `court_id`, `booking_id`, `skill_level`, `slots_needed`, `status`, `created_at`, `updated_at`

### `reviews`
`id`, `user_id`, `venue_id`, `rating`, `review`, `created_at`, `updated_at`

---

## 14. API Architecture

| Komponen | Teknologi |
|---|---|
| Backend | Laravel 12 REST API |
| Authentication | Sanctum |
| Realtime | Laravel Reverb |
| Queue | Redis Queue |
| Cache | Redis |
| Storage | S3 Compatible Storage |

---

## 15. Release Plan

### MVP (Phase 1) — 8–10 Minggu
Authentication, Venue Management, Court Management, Booking, Real-Time Availability, Payment Gateway, QR Check-In, User Dashboard, Venue Dashboard, Admin Dashboard

### Phase 2 — 4–6 Minggu
Open Match, Chat, Loyalty Program, Membership, Review System

### Phase 3 — 6–8 Minggu
Tournament, Coaching Events, Dynamic Pricing, AI Recommendation, AI Matchmaking

---

## 16. Future Enhancements

- AI Court Recommendation
- AI Matchmaking
- Dynamic Pricing
- Apple Watch Integration
- Garmin Integration
- Strava Integration
- Referral Program
- Corporate Booking
- Multi-Venue Franchise Support

---

## 17. Final MVP Scope

| Prioritas | Fitur |
|---|---|
| **Must Have** | Authentication, Venue Management, Court Management, Real-Time Availability, Booking System, Payment Gateway, QR Check-In, User Dashboard, Venue Dashboard, Admin Panel |
| **Nice to Have** | Open Match, Chat, Membership, Loyalty Program, Tournament Module |
| **Future** | AI Features, Dynamic Pricing, Wearable Integration |

---

*END OF DOCUMENT*