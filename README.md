# SISTEM INFORMASI KASIR POS - SEVENTEEN PETSHOP (WINDOWS DESKTOP APP)
**Referensi:** *Perancangan Aplikasi Sistem Kasir Berbasis Web Menggunakan Metode Waterfall pada Seventeen Petshop* (Jurnal JATI Vol. 8 No. 4, Agustus 2024)

---

## 📌 Deskripsi Sistem
Aplikasi Kasir (Point of Sale) Seventeen Petshop dirancang khusus untuk memodernisasi proses transaksi penjualan ritel kebutuhan hewan peliharaan, mengotomatisasi pemotongan stok secara real-time, memberikan notifikasi batas stok minimum, mencetak struk nota belanja, serta menyajikan laporan penjualan bulanan dan ekspor data ke Excel.

Aplikasi ini hadir dalam **dua bentuk sekaligus**:
1. **Aplikasi Desktop Windows (.exe):** Berjalan mandiri tanpa browser bar, memiliki ikon toko, mendukung pencetakan nota kasir thermal langsung, dan dilengkapi fitur auto-check service XAMPP.
2. **Aplikasi Web Terbuka:** Dapat diakses melalui browser di alamat `http://localhost/seventeen_kasir/`.

---

## 🚀 Cara Menjalankan Aplikasi

### Opsi 1: Melalui Shortcut Desktop (Paling Mudah)
1. Buka layar **Desktop Windows** Anda.
2. Klik 2x pada ikon shortcut **`Seventeen Kasir POS`**.
3. Aplikasi kasir akan langsung terbuka dalam layar penuh (*fullscreen ready*).

### Opsi 2: Menjalankan File Eksekutabel Langsung
1. Buka folder `C:\CXampp\htdocs\seventeen_kasir\dist\`.
2. Klik 2x pada **`SeventeenKasir.exe`** (aplikasi ini bersifat *Self-Contained*, tidak memerlukan instalasi runtime tambahan).

### Opsi 3: Melalui Browser Web
1. Pastikan Apache & MySQL di XAMPP Control Panel telah berjalan.
2. Buka browser (Chrome, Edge, Firefox) dan akses: **`http://localhost/seventeen_kasir/`**.

---

## 🔑 Kredensial Login Kasir Default
- **Username:** `admin`
- **Password:** `admin123`
*(Password dapat diubah kapan saja melalui menu Pengaturan Toko)*

---

## ⌨️ Pintasan Keyboard (*Hotkeys*) pada Aplikasi Windows
- **F5:** Muat ulang halaman (*Refresh*).
- **F11:** Beralih ke mode Layar Penuh (*Fullscreen Toggle*).
- **Ctrl + P:** Buka dialog cetak printer langsung.
- **Ctrl + Home:** Kembali ke Dashboard Utama.

---

## 📂 Struktur Direktori Proyek
```
C:\CXampp\htdocs\seventeen_kasir\
├── assets\                   # Asset CSS & JS lokal (100% Offline Ready)
│   ├── css\                  # Bootstrap 5 & Bootstrap Icons lokal
│   ├── js\                   # Bootstrap Bundle JS lokal
│   └── img\                  # Favicon & logo Seventeen Petshop
├── config\
│   └── config.php            # Koneksi PDO terpusat, BASE_URL, sanitasi input
├── database\
│   └── seventeen_kasir.sql   # Skema 7 tabel JATI + Data Awal Seventeen Petshop
├── dist\                     # Hasil Build Aplikasi Desktop Windows
│   ├── SeventeenKasir.exe    # File Eksekutabel Mandiri (Self-Contained)
│   └── app.ico               # Ikon Aplikasi Petshop
├── includes\                 # Template modular (header, sidebar, footer)
├── module\
│   ├── barang\               # Katalog produk, batas stok minimum, restock
│   ├── kategori\             # CRUD Kategori produk petshop
│   ├── transaksi\            # POS kasir, keranjang, hitung kembalian, checkout
│   ├── laporan\              # Filter penjualan & cetak/ekspor Excel
│   └── toko\                 # Identitas toko Seventeen Petshop & ganti password
├── windows_app\              # Source code C# .NET Windows Forms + WebView2
├── dashboard.php             # Halaman utama ringkasan metrik & alert stok
├── login.php                 # Form autentikasi kasir aman
├── logout.php                # Penghancur sesi kasir
├── index.php                 # Router utama sistem
└── Jalankan_Seventeen_Kasir.bat # Skrip launcher cepat
```

---

## 🛡️ Standar Kualitas & Keamanan
- **Metode Pengembangan:** Waterfall sekuensial linier (Analisis $\rightarrow$ Desain $\rightarrow$ Implementasi $\rightarrow$ Pengujian $\rightarrow$ Pemeliharaan).
- **Keamanan Query:** Menggunakan *Prepared Statements* (PDO) untuk mencegah kerentanan SQL Injection.
- **Keamanan XSS:** Sanitasi keluaran dinamis dengan `htmlspecialchars()`.
- **Integritas Relasi Basis Data:** Menggunakan *Foreign Key Constraints* (InnoDb) sehingga produk yang telah memiliki riwayat transaksi nota terlindungi dari penghapusan tidak sengaja.
- **Offline First:** Seluruh pustaka CSS (Bootstrap 5) dan font ikon telah tersimpan secara lokal tanpa ketergantungan internet.

