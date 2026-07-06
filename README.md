# Posyandu Data Management System

Sistem Informasi Posyandu adalah aplikasi berbasis web yang dirancang untuk mendigitalisasi proses pemantauan pertumbuhan balita. Proyek ini dikembangkan sebagai solusi untuk mempermudah kader dalam melakukan pencatatan, perhitungan, dan analisis status gizi anak secara akurat sesuai standar kesehatan nasional.

## 🌟 Latar Belakang & Tujuan

Di lapangan, kader Posyandu sering menghadapi tantangan dalam menghitung status gizi secara manual yang memakan waktu dan rentan terhadap kesalahan. Sistem ini hadir untuk:

- **Akurasi Standar:** Mengotomatisasi penentuan status berat badan (BB/U)
- **Deteksi Dini:** Mengidentifikasi balita yang memerlukan perhatian khusus (misal: _Gizi Kurang_ atau _Buruk_) sehingga intervensi seperti PMT dapat segera diberikan.
- **Efisiensi Administrasi:** Menggantikan pencatatan kertas menjadi database digital yang terintegrasi, aman, dan mudah diakses.

## 🛠️ Stack Teknologi

Proyek ini dikembangkan dengan teknologi modern untuk menjamin performa dan kemudahan pengembangan:

- **Backend:** Laravel (PHP) dengan arsitektur MVC.
- **Database:** MySQL dengan relasi data anak dan pengukuran yang optimal.
- **Frontend & Asset:** Vite untuk manajemen aset yang efisien dan cepat.
- **Logika Bisnis:** Implementasi algoritma Z-Score untuk klasifikasi gizi balita.

## 📋 Fitur Utama

- **Manajemen Data Anak:** Pencatatan biodata balita dan data orang tua.
- **Modul Pengukuran:** Input berat badan secara berkala dengan validasi data.
- **Otomasi Status Gizi:** Penentuan status gizi otomatis berdasarkan umur, jenis kelamin, dan berat badan.
- **Dashboard Kader:** Visualisasi ringkasan data yang memerlukan tindak lanjut segera.
- **Riwayat Pertumbuhan:** Grafik dan tabel perkembangan balita dari waktu ke waktu.

## 🚀 Panduan Instalasi

Pastikan Anda telah menginstal PHP, Composer, dan Node.js di komputer Anda.
