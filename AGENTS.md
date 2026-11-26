
# AGENTS.md — LONTARA BPKP SULSEL

## 1. Identitas Proyek

- Nama: LONTARA BPKP SULSEL
- Repository: https://github.com/amri-oss/apk-bpkp
- Branch pengembangan: develop
- Production URL: https://lontara.ewako.tech
- Deployment: Coolify
- Framework: Laravel 12
- PHP: 8.3
- Database: MySQL
- Frontend: Vite 7, Alpine.js, Tailwind CSS v3
- Deployment container: Dockerfile dengan Apache.

## 2. Prinsip Utama Agent AI

1. Pahami struktur proyek sebelum mengubah kode.
2. Periksa implementasi aktual, jangan mengasumsikan kode berdasarkan dokumentasi atau percakapan lama.
3. Kerjakan satu lingkup perubahan dalam satu waktu.
4. Pertahankan fitur yang sudah berjalan.
5. Jangan melakukan refactoring besar tanpa persetujuan.
6. Jangan membuat perubahan yang tidak diminta.
7. Jelaskan risiko sebelum melakukan perubahan penting.
8. Jangan mengklaim pekerjaan selesai sebelum pengujian berhasil.

## 3. Perlindungan Database dan File

- Jangan menghapus database, tabel, atau data produksi.
- Jangan menjalankan migrate:fresh atau db:wipe pada production.
- Jangan menghapus data pengguna atau riwayat konsultasi.
- Jangan mengubah struktur database tanpa memeriksa dampaknya.
- Gunakan migration untuk perubahan struktur database.
- Lindungi file upload pengguna.
- Jangan menghapus atau menimpa file storage produksi.
- Jangan menampilkan password, token, API key, atau kredensial.

## 4. Aturan Laravel

- Ikuti struktur dan konvensi Laravel.
- Gunakan validasi pada input pengguna.
- Periksa authorization dan middleware pada setiap fitur yang membutuhkan pembatasan akses.
- Hindari query database yang tidak efisien.
- Gunakan transaksi database untuk proses yang memerlukan konsistensi data.
- Jangan menaruh kredensial di source code.
- Periksa route, controller, model, middleware, dan Blade sebelum mengubah fitur.

## 5. Frontend

- Pertahankan Tailwind CSS v3.
- Gunakan Alpine.js untuk interaksi frontend yang sudah menggunakan Alpine.
- Periksa resources/js/app.js sebelum mengubah inisialisasi JavaScript.
- Hindari duplikasi event listener.
- Pastikan dropdown, modal, navigasi, dan komponen interaktif tetap berfungsi.
- Jalankan npm run build setelah mengubah aset frontend.
- Jangan mengganti framework frontend tanpa persetujuan.

## 6. Storage dan File Upload

- File publik Laravel menggunakan storage/app/public.
- Akses publik melalui public/storage.
- Symbolic link harus tersedia melalui php artisan storage:link.
- Pastikan Docker deployment mempertahankan konfigurasi symbolic link.
- Periksa ownership dan permission storage.
- Jangan menganggap file lokal otomatis tersedia di VPS.
- Jangan menghapus file upload ketika deployment.

## 7. HTTPS dan Proxy

- Production menggunakan https://lontara.ewako.tech.
- Perhatikan konfigurasi trusted proxies Laravel.
- Perhatikan X-Forwarded-Proto dan HTTPS.
- Pastikan URL redirect dan asset menggunakan HTTPS.
- Jangan menonaktifkan proteksi HTTPS untuk mengatasi masalah sementara.

## 8. Reverb dan Real-Time

- Paket laravel/reverb tersedia dalam composer.json.
- Keberadaan paket tidak berarti server Reverb sudah berjalan.
- Periksa konfigurasi Echo, Reverb, Docker, dan Coolify sebelum mengaktifkan real-time.
- Jangan mengasumsikan REVERB_HOST dan VITE_REVERB_HOST harus sama.
- Pastikan WebSocket host dapat dijangkau browser.
- Kegagalan Echo tidak boleh menghentikan inisialisasi Alpine.
- Jangan menghapus fitur real-time tanpa persetujuan.

## 9. Git Workflow

Branch pengembangan utama: develop.

Sebelum mengubah kode:

    git status
    git branch --show-current

Setelah perubahan:

    npm run build

Jalankan pengujian Laravel yang relevan:

    php artisan test

Periksa perubahan:

    git diff
    git status

Commit dengan pesan yang menjelaskan perubahan.

    git add <file>
    git commit -m "Deskripsi perubahan"
    git push origin develop

Jangan push perubahan yang tidak diperiksa.

Jangan melakukan force push tanpa persetujuan.

Jangan mengubah branch main atau production secara langsung tanpa persetujuan.

## 10. Deployment Coolify

- Deployment production dilakukan melalui Coolify.
- Periksa commit yang akan dideploy.
- Periksa build log dan deployment log.
- Jangan mengubah environment production tanpa alasan yang jelas.
- Perhatikan perbedaan build-time dan runtime environment variables.
- Jangan mengasumsikan perubahan container bersifat permanen.
- Setelah deployment, lakukan smoke test.
- Periksa login, dashboard, navigasi, upload, download, dan fitur yang terdampak.

## 11. Prosedur Penanganan Error

Setiap error harus ditangani secara sistematis:

1. Identifikasi gejala.
2. Periksa log dan pesan error.
3. Temukan sumber masalah.
4. Tentukan file yang terkait.
5. Ajukan solusi paling kecil dan aman.
6. Uji secara lokal.
7. Periksa diff.
8. Commit dan push setelah disetujui.
9. Deploy.
10. Verifikasi production.

Jangan memperbaiki error dengan mengubah banyak file sekaligus tanpa alasan.

## 12. Komunikasi Agent

- Gunakan Bahasa Indonesia.
- Jelaskan langkah teknis secara bertahap.
- Berikan perintah terminal yang siap digunakan.
- Jangan meminta pengguna mengulang informasi yang sudah tersedia.
- Jangan memberikan beberapa perubahan berisiko sekaligus.
- Tunggu hasil pengujian sebelum melanjutkan.
- Minta persetujuan sebelum perubahan database, storage, autentikasi, dan production.

## 13. Aturan Mutlak

KEAMANAN DAN INTEGRITAS DATA LEBIH PENTING DARIPADA KECEPATAN PENGERJAAN.

Jangan pernah:
- Menghapus data production tanpa persetujuan.
- Mengganti kredensial production secara sembarangan.
- Mengubah konfigurasi server tanpa memahami dampaknya.
- Melakukan deploy kode yang belum diuji.
- Mengklaim fitur berfungsi tanpa pengujian.
