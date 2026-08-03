PadelHub — AI Coding Instructions
Baca file ini dulu sebelum melakukan perubahan apapun. Ini adalah aturan kerja untuk project ini, bukan saran opsional.
1. Apa Project Ini
PadelHub — platform booking lapangan padel (booking real-time, pembayaran, QR check-in, dashboard venue & admin). Detail lengkap fitur ada di prd.md di root project. Baca prd.md setiap kali mengerjakan fitur baru untuk memastikan konsisten dengan requirement.
Saat ini masih tahap MVP / awal development. Prioritaskan fitur yang ada di section "Final MVP Scope" pada prd.md. Jangan bangun fitur dari "Phase 2/3" atau "Future Enhancements" kecuali diminta eksplisit.
2. Tech Stack — JANGAN GANTI TANPA DIMINTA
Layer	Teknologi
Backend	Laravel 12
Frontend	Vue 3 (Composition API, <script setup>)
Bridge	Inertia.js — bukan REST API terpisah
Styling	Tailwind CSS v4
Database	PostgreSQL
Auth	Laravel Breeze (Inertia + Vue stack)
Local server	php artisan serve (bukan Herd/Valet)
Aturan keras:
Jangan install package baru tanpa alasan jelas. Kalau ragu perlu library atau tidak, tanya dulu — jangan langsung npm install / composer require.
Jangan ganti Inertia jadi REST API + axios. Semua page pakai Inertia::render() dari controller.
Jangan bikin folder frontend/ atau backend/ terpisah. Ini satu project, satu folder root.
Jangan downgrade/upgrade versi Laravel, Vue, atau Tailwind tanpa diminta.
3. Struktur Folder (Acuan)
padel-booking/
├── app/
│   ├── Http/Controllers/       # Controller per module (BookingController, VenueController, dst)
│   ├── Models/                 # Eloquent models sesuai schema di prd.md section 13
├── database/
│   ├── migrations/
│   └── seeders/
├── resources/
│   ├── js/
│   │   ├── Pages/               # 1 file Vue = 1 halaman (Inertia::render('Booking/Index'))
│   │   ├── Components/          # Komponen reusable (Button, Card, dll)
│   │   ├── Layouts/             # AuthenticatedLayout, GuestLayout
│   │   └── app.js
│   └── css/app.css
├── routes/
│   └── web.php                  # Semua route Inertia di sini
├── prd.md                       # Sumber kebenaran fitur — SELALU CEK INI
└── CLAUDE.md                    # File ini
Ikuti pola ini. Jangan bikin struktur folder baru yang menyimpang tanpa alasan kuat.
4. Cara Kerja yang Diharapkan
Kerjakan satu fitur/module dalam satu waktu. Jangan sekaligus bikin 5 module berbeda dalam satu response — ini bikin sulit di-review dan gampang error.
Ikuti urutan dependency logis: users → venues → courts → bookings → payments → fitur lain. Jangan bikin fitur booking sebelum tabel venues dan courts ada.
Sebelum membuat migration/model baru, cek dulu prd.md section 13 (Database Schema) — field-field sudah didefinisikan di sana, ikuti nama & tipe yang sudah ada, jangan improvisasi nama kolom sendiri.
Kalau requirement ambigu atau tidak ada di prd.md, tanya dulu sebelum asumsi dan mengerjakan. Jangan menebak-nebak lalu bikin fitur besar berdasarkan asumsi salah.
Jangan over-engineer. Ini masih MVP — tidak perlu bikin abstraction layer, repository pattern, service container kompleks, atau microservice-style architecture kecuali diminta. Pakai pola standar Laravel (Controller → Model, Form Request untuk validasi).
Selalu jalankan migration setelah membuat migration baru, jangan biarkan menumpuk belum dijalankan.
5. Konvensi Kode
PHP/Laravel: ikuti PSR-12, gunakan Form Request untuk validasi (bukan validasi manual di controller).
Vue: Composition API + <script setup>, bukan Options API.
Nama file Vue Page: PascalCase sesuai nama route, contoh resources/js/Pages/Bookings/Index.vue, resources/js/Pages/Bookings/Create.vue.
Nama Controller: singular + Controller, contoh BookingController, bukan BookingsController.
Migration: gunakan nama tabel plural sesuai schema di prd.md (bookings, courts, venues).
Commit granular: satu perubahan logis = satu penjelasan, jangan gabung banyak hal tak berhubungan dalam satu perubahan besar.
6. Hal yang WAJIB Ditanyakan Sebelum Dikerjakan
Jangan langsung eksekusi kalau menyangkut hal berikut — konfirmasi dulu ke user:
Integrasi payment gateway (Midtrans/Xendit) — perlu API key asli, jangan buat dummy yang menyesatkan.
Perubahan struktur database yang sudah ada data-nya.
Instalasi package besar (Filament, Reverb, Redis) — pastikan memang sudah waktunya dipakai sesuai fase development.
Perubahan .env atau konfigurasi server.
7. Batasan Scope MVP Saat Ini
Kerjakan (Must Have): Authentication, Venue Management, Court Management, Real-Time Availability, Booking System, Payment Gateway, QR Check-In, User Dashboard, Venue Dashboard, Admin Panel.
Jangan kerjakan dulu (Nice to Have / Future): Open Match, Chat, Membership, Loyalty Program, Tournament, AI Features, Dynamic Pricing, Wearable Integration — kecuali diminta eksplisit oleh user.
8. Kalau Ragu
Cek prd.md dulu.
Kalau masih ragu, tanya ke user dengan pertanyaan spesifik — jangan bikin asumsi besar lalu kerjakan banyak hal berdasarkan asumsi itu.
Lebih baik selesaikan satu bagian kecil dengan benar, daripada banyak bagian sekaligus tapi berantakan.