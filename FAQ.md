# ❓ FAQ - PEACE SEAFOOD WMS

**Pertanyaan yang Sering Ditanyakan**

---

## 📚 DAFTAR ISI

1. [Umum](#umum)
2. [Login & Akun](#login--akun)
3. [Stok & Produk](#stok--produk)
4. [Penjualan & Nota](#penjualan--nota)
5. [Keuangan & Piutang](#keuangan--piutang)
6. [Langganan & Pembayaran](#langganan--pembayaran)
7. [Laporan](#laporan)
8. [Technical Issues](#technical-issues)

---

## 🌐 UMUM

### **Q: Apa itu Peace Seafood WMS?**
**A:** Sistem manajemen gudang berbasis cloud khusus untuk bisnis seafood. Membantu kelola stok, penjualan, penitipan, retur, dan keuangan.

### **Q: Siapa yang bisa pakai sistem ini?**
**A:** Pemilik gudang/toko seafood dan tim operasional mereka (admin, checker, helper, dll).

### **Q: Apakah sistem ini gratis?**
**A:** Trial gratis 14 hari. Setelah itu ada biaya langganan bulanan. Hubungi admin untuk pricing.

### **Q: Bisa diakses dari HP/tablet?**
**A:** Ya! Sistem responsive dan bisa dibuka dari browser HP/tablet (Chrome/Safari recommended).

### **Q: Apakah data saya aman?**
**A:** Ya. Sistem menggunakan enkripsi, backup otomatis harian, dan security best practices.

### **Q: Bisa dipakai offline?**
**A:** Tidak. Sistem memerlukan koneksi internet karena berbasis cloud.

---

## 🔑 LOGIN & AKUN

### **Q: Lupa password, bagaimana?**
**A:** 
1. Klik **"Lupa Password"** di halaman login
2. Masukkan email Anda
3. Cek email untuk link reset
4. Klik link dan buat password baru
5. Jika tidak terima email, cek folder spam atau hubungi support

### **Q: Kenapa saya tidak bisa login?**
**A:** Kemungkinan:
- Email/password salah (perhatikan huruf besar-kecil)
- Akun belum aktif (hubungi admin)
- Browser issue (coba clear cache atau ganti browser)
- Masa aktif gudang habis (perpanjang langganan)

### **Q: Berapa lama session login?**
**A:** Session aktif selama 8 jam. Setelah itu harus login ulang.

### **Q: Bisa login di beberapa device sekaligus?**
**A:** Ya, tapi tidak direkomendasikan. Gunakan 1 device per user untuk tracking yang akurat.

### **Q: Bagaimana cara ganti password?**
**A:** Menu **Settings** → **Ganti Password** → Isi password lama & baru → **Simpan**

### **Q: Password harus seperti apa?**
**A:** Minimal 8 karakter dengan kombinasi:
- Huruf besar (A-Z)
- Huruf kecil (a-z)
- Angka (0-9)
- Simbol (!@#$%^&*)

---

## 📦 STOK & PRODUK

### **Q: Bagaimana cara input stok masuk?**
**A:** Menu **Stok** → **Stok Masuk** → **+ Tambah** → Isi form (tanggal, supplier, produk, jumlah, harga) → **Simpan**

### **Q: Stok saya tidak update setelah input. Kenapa?**
**A:** 
1. Refresh halaman (F5)
2. Cek apakah data tersimpan di riwayat stok masuk
3. Cek activity log untuk konfirmasi
4. Jika tetap tidak muncul, hubungi support dengan screenshot

### **Q: Bisa input stok masuk untuk tanggal kemarin?**
**A:** Ya, bisa pilih tanggal saat input stok masuk.

### **Q: Bagaimana cara menambah produk baru?**
**A:** Menu **Master Data** → **Produk** → **+ Tambah Produk** → Isi detail → **Simpan**

### **Q: Apa bedanya Grade A, B, C di produk?**
**A:** Grade menunjukkan kualitas produk (A = premium, B = standard, C = economy). Bisa custom sesuai bisnis Anda.

### **Q: Bisa transfer stok antar gudang?**
**A:** Ya. Menu **Stok** → **Transfer** → Pilih gudang asal & tujuan → Transfer

### **Q: Apa itu Stok Opname?**
**A:** Stock taking/pengecekan fisik stok. Untuk memastikan stok di sistem sesuai dengan fisik.

### **Q: Bagaimana cara menangani stok minus?**
**A:** Sistem mencegah stok minus. Jika terjadi penjualan melebihi stok, akan ada warning. Pastikan input stok masuk tepat waktu.

---

## 💰 PENJUALAN & NOTA

### **Q: Bagaimana cara buat nota penjualan?**
**A:** Menu **Penjualan** → **Buat Nota** → Pilih pelanggan → Tambah produk → Pilih pembayaran → **Simpan**

### **Q: Bisa simpan nota yang belum selesai?**
**A:** Ya. Klik **Save as Draft**. Nanti bisa dilanjutkan dari menu **Draft**.

### **Q: Bagaimana cara edit nota yang sudah tersimpan?**
**A:** Nota yang sudah tersimpan tidak bisa diedit (untuk audit trail). Jika ada kesalahan, buat **Retur** dan buat nota baru.

### **Q: Bisa cetak ulang nota?**
**A:** Ya. Masuk ke **Daftar Nota** → Klik nota → **Print**

### **Q: Format nota bisa custom?**
**A:** Saat ini menggunakan format standard. Custom format bisa request ke tim development.

### **Q: Nota hilang/tidak muncul. Bagaimana?**
**A:** 
1. Cek filter tanggal (mungkin terlalu sempit)
2. Cek di menu Draft (mungkin tersimpan sebagai draft)
3. Search berdasarkan nomor nota/nama pelanggan
4. Hubungi support jika tidak ketemu

### **Q: Bisa kasih diskon di nota?**
**A:** Ya, bisa edit harga per item atau total diskon di akhir.

### **Q: Apa itu Draft Nota?**
**A:** Nota yang belum final/belum dicetak. Bisa diedit atau dihapus.

---

## 💵 KEUANGAN & PIUTANG

### **Q: Bagaimana cara catat penjualan tempo (piutang)?**
**A:** Saat buat nota, pilih **Metode Pembayaran: Tempo** → Isi tanggal jatuh tempo → **Simpan**

### **Q: Bagaimana cara bayar piutang?**
**A:** Menu **Keuangan** → **Piutang** → Klik pelanggan → **Bayar** → Isi jumlah → **Simpan**

### **Q: Bisa bayar piutang sebagian (cicilan)?**
**A:** Ya. Isi jumlah yang dibayar (tidak harus full). Sisa akan tetap tercatat.

### **Q: Bagaimana cara lihat riwayat pembayaran piutang?**
**A:** Klik nama pelanggan di menu Piutang → Lihat detail per nota + riwayat pembayaran

### **Q: Apa itu Aging Piutang?**
**A:** Klasifikasi piutang berdasarkan jatuh tempo:
- 🟢 Belum jatuh tempo
- 🟡 Telat 1-7 hari
- 🟠 Telat 8-30 hari
- 🔴 Telat > 30 hari

### **Q: Bisa kirim reminder piutang ke pelanggan?**
**A:** Fitur ini sedang development. Saat ini manual via WA/telepon.

### **Q: Bagaimana cara hapus piutang yang tidak bisa ditagih?**
**A:** Hubungi admin untuk write-off. Ada approval process khusus.

---

## 💳 LANGGANAN & PEMBAYARAN

### **Q: Berapa lama masa trial?**
**A:** 14 hari gratis setelah onboarding selesai.

### **Q: Bagaimana cara perpanjang langganan?**
**A:**
1. Transfer ke rekening yang tertera di sistem
2. Upload bukti transfer saat diminta
3. Tunggu approval (< 1 jam)
4. Masa aktif otomatis diperpanjang

### **Q: Kapan harus perpanjang?**
**A:** Sistem akan kirim notifikasi H-7, H-3, H-1 sebelum masa aktif habis.

### **Q: Apa yang terjadi jika tidak perpanjang tepat waktu?**
**A:** Akses akan terkunci. Tidak bisa input data. Tapi data tetap aman dan bisa diakses kembali setelah perpanjang.

### **Q: Berapa biaya langganan?**
**A:** Hubungi admin untuk pricing. Ada paket bulanan dan tahunan (lebih hemat).

### **Q: Bisa bayar via QRIS?**
**A:** Ya. Scan QR code yang tertera di form pembayaran.

### **Q: Bukti transfer tidak diterima. Kenapa?**
**A:** Kemungkinan:
- File terlalu besar (max 5MB)
- Format tidak didukung (harus JPG/PNG/PDF)
- Koneksi internet putus saat upload
- Coba compress file atau screenshot ulang

### **Q: Berapa lama proses approval setelah upload bukti?**
**A:** Biasanya < 1 jam di jam kerja (08:00-17:00 WIB). Di luar jam kerja maksimal keesokan harinya.

---

## 📊 LAPORAN

### **Q: Laporan apa saja yang tersedia?**
**A:**
- Laporan Penjualan (harian, bulanan, per produk)
- Laba Rugi
- Stok Realtime
- Aging Piutang
- Performa Karyawan
- Transaksi per Kategori

### **Q: Bagaimana cara download laporan?**
**A:** Buka laporan yang diinginkan → Klik **Export** → Pilih format (PDF/Excel) → **Download**

### **Q: Laporan tidak muncul data. Kenapa?**
**A:** Cek filter:
- Tanggal range (mungkin terlalu sempit)
- Gudang yang dipilih (jika multi-gudang)
- Pastikan ada transaksi di periode tersebut

### **Q: Bisa custom laporan sesuai kebutuhan?**
**A:** Laporan standard sudah mencakup kebutuhan umum. Untuk custom laporan, bisa request ke tim development.

### **Q: Data laporan tidak real-time?**
**A:** Data seharusnya real-time. Jika tidak update, coba:
1. Refresh laporan
2. Check koneksi internet
3. Logout & login ulang
4. Hubungi support jika masih bermasalah

### **Q: Bisa lihat laporan bulan/tahun lalu?**
**A:** Ya. Pilih range tanggal sesuai yang diinginkan.

---

## 🔧 TECHNICAL ISSUES

### **Q: Browser apa yang recommended?**
**A:** Chrome, Firefox, Safari, Edge (versi terbaru). Hindari IE.

### **Q: Kenapa tampilan berantakan di HP saya?**
**A:** Gunakan browser terbaru (Chrome/Safari). Update jika versi lama.

### **Q: Sistem lambat/loading lama. Kenapa?**
**A:** Kemungkinan:
- Koneksi internet lambat
- Banyak data yang diload (gunakan filter)
- Browser butuh clear cache
- Server sedang maintenance (cek status page)

### **Q: Error "Session Expired" terus muncul. Kenapa?**
**A:** Login sudah 8 jam atau browser clear cookies. Login ulang.

### **Q: Bisa pakai sistem di komputer kantor dan HP bergantian?**
**A:** Ya, bisa. Tapi tidak sekaligus untuk menghindari conflict.

### **Q: Data saya hilang. Apa yang harus dilakukan?**
**A:** Jangan panik! Data di-backup otomatis setiap hari. Hubungi support segera dengan detail:
- Data apa yang hilang
- Kapan terakhir lihat data tersebut
- Screenshot jika ada

### **Q: Bagaimana cara update browser?**
**A:** 
- **Chrome:** Menu → Help → About Google Chrome (auto-update)
- **Firefox:** Menu → Help → About Firefox (auto-update)
- **Safari:** Update via Mac App Store

### **Q: Pop-up blocker menghalangi cetak nota. Bagaimana?**
**A:** Allow pop-up untuk situs ini:
- Chrome: Icon 🚫 di address bar → Allow
- Firefox: Preferences → Permissions → Pop-ups → Exceptions
- Safari: Preferences → Websites → Pop-up Windows → Allow

---

## 💡 TIPS & TRICKS

### **Q: Apa shortcut keyboard yang berguna?**
**A:**
- `Ctrl + S` = Simpan (di form)
- `Ctrl + P` = Print
- `Esc` = Close modal
- `F5` = Refresh
- `Ctrl + F` = Search

### **Q: Bagaimana cara kerja lebih efisien?**
**A:**
- Gunakan filter & search untuk cari data cepat
- Save as draft untuk nota yang kompleks
- Bookmark URL untuk akses cepat
- Pelajari shortcut keyboard
- Review dashboard setiap pagi

### **Q: Bisa integrate dengan sistem lain (e-commerce, accounting)?**
**A:** Fitur API integration sedang development. Coming soon!

### **Q: Ada video tutorial?**
**A:** Video tutorial sedang dalam produksi. Saat ini gunakan panduan tertulis.

---

## 📞 MASIH ADA PERTANYAAN?

**Contact Support:**

📧 **Email:** support@peaceseafood.com  
💬 **WhatsApp:** +62xxx (tampil di sistem)  
⏰ **Jam Kerja:** 08:00 - 17:00 WIB  
📱 **Response Time:** < 24 jam

**Saat menghubungi, siapkan:**
- Screenshot error (jika ada)
- Email akun Anda
- Langkah yang sudah dicoba
- Browser & device yang digunakan

---

## 📚 RESOURCES TAMBAHAN

- **Panduan Lengkap:** `PANDUAN_PENGGUNA.md`
- **Quick Reference:** `QUICK_REFERENCE.md` (print & tempel!)
- **Video Tutorial:** Coming soon
- **Webinar:** Monthly (cek notifikasi)

---

**FAQ ini akan terus diupdate berdasarkan pertanyaan pengguna.**

**Ver 3.0 | Updated: June 6, 2026**  
**© Peace Seafood WMS**

---

**💡 Tidak menemukan jawaban? Jangan ragu bertanya ke support!**
