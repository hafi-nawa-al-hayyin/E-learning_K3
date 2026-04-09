# 📱 Panduan Penggunaan K3-VirtuAI di Mobile/Handphone

## 🎯 Daftar Isi

1. [Fitur Mobile](#fitur-mobile)
2. [Optimasi Responsive](#optimasi-responsive)
3. [Panduan Pengguna](#panduan-pengguna)
4. [Tips & Trik](#tips--trik)
5. [Troubleshooting](#troubleshooting)

---

## ✨ Fitur Mobile

### 1. **Navigasi Hamburger Menu**

- Pada layar kecil (< 768px), tombol menu (☰) akan muncul di navbar
- Klik untuk membuka/tutup menu navigasi
- Menu akan otomatis tertutup saat Anda mengklik salah satu link

### 2. **Layout Responsif Otomatis**

Aplikasi akan menyesuaikan layout berdasarkan ukuran layar:

- **Desktop (> 1024px)**: Layout 2 kolom untuk simulasi dan kontrol
- **Tablet (768px - 1024px)**: Layout hybrid dengan penyesuaian
- **Mobile (<768px)**: Layout full width, stacked vertikal
- **Ponsel kecil (<480px)**: Optimasi maksimal untuk usability

### 3. **Forms & Input Mobile-Friendly**

- Font size 16px untuk mencegah zoom otomatis
- Padding yang lebih besar untuk touch-friendly
- Select dropdown dengan styling custom
- Password visibility toggle (👁️/🙈)

---

## 🔧 Optimasi Responsive

### Breakpoints CSS

```css
Desktop:       > 1024px (Landscape layout)
Tablet:        768px - 1024px (Hybrid layout)
Mobile:        < 768px (Stack layout)
Small Phone:   < 480px (Compact layout)
```

### File CSS Responsive

- **dashboard.css** - Styling utama dengan media queries
- **mobile.css** - Stylesheet khusus untuk mobile devices
- **login.php** - Halaman login responsif (inline CSS)

---

## 📖 Panduan Pengguna

### Login di Mobile

1. Buka halaman login di browser mobile
2. Pilih role (Mahasiswa/Dosen/Admin)
3. Masukkan NIM/NIDN dan password
4. Form akan auto-adjust sesuai ukuran layar
5. Klik **MASUK** atau **DAFTAR**

### Menggunakan Dashboard di Mobile

1. **Navigation**
   - Desktop: Menu horizontal di navbar
   - Mobile: Klik ☰ untuk buka menu dropdown
2. **Simulasi 3D**
   - Pengalaman A-Frame tetap sama di mobile
   - Gunakan gyroskop untuk kontrol kamera (jika tersedia)
   - Tap untuk click/interact dengan objek

3. **Tombol & Kontrol**
   - Semua tombol min-height 44px untuk touch-friendly
   - Spacing yang cukup untuk menghindari tap error
4. **Tabel Data**
   - Horizontal scroll untuk tabel yang panjang (auto di mobile)
   - Font size yang lebih kecil untuk tempat terbatas
   - Kolom terpenting ditampilkan lebih dulu

---

## 💡 Tips & Trik

### Untuk Pengguna

1. **Landscape Mode**: Gunakan landscape untuk simulasi 3D yang lebih immersive
2. **Dark Mode**: Browser akan otomatis apply dark mode jika device preference diset
3. **Offline Access**: A-Frame scene akan cache untuk akses offline
4. **Touch Controls**: Gunakan 2-finger pinch untuk zoom di 3D scene

### Untuk Developer

1. **Media Queries Priority**: Mobile CSS (`mobile.css`) override dashboard CSS
2. **Viewport Meta Tag**: Sudah termasuk untuk responsive scaling
3. **Future Enhancements**: Siap untuk PWA (Progressive Web App)
4. **Accessibility**: Support untuk prefers-reduced-motion dan dark mode

---

## 🔍 Troubleshooting

### Masalah 1: Halaman Tidak Responsive

**Solusi:**

- Clear browser cache (Ctrl+Shift+Delete)
- Pastikan Viewport meta tag ada di `<head>`
- Check CSS file loaded: `mobile.css` harus responsive

### Masalah 2: Menu Berlebih Kecil di Mobile

**Solusi:**

- Font min-size 13px
- Padding min 8px per elemen
- Touch target min-height 44px

### Masalah 3: Simulasi 3D Lag di Mobile

**Solusi:**

- Gunakan device yang lebih powerful
- Kurangi geometry complexity (WebGL)
- Disable motion animation jika lag

### Masalah 4: Form Zoom Otomatis (iOS)

**Solusi:**

- Font size input tetap 16px ✓ (sudah optimized)
- Use viewport-fit untuk notch devices
- Disable user-zoom hanya jika perlu

### Masalah 5: Password Toggle Tidak Bekerja

**Solusi:**

- Pastikan JavaScript enabled
- Check browser console untuk error
- Reload halaman

---

## 📱 Tested Devices

Responsive testing dilakukan pada:

- ✅ iPhone 12/13/14 (Safari)
- ✅ Samsung Galaxy A12/A13 (Chrome)
- ✅ iPad Air/Pro (Safari)
- ✅ Google Pixel 6 (Chrome)
- ✅ Windows Phone (Edge)

---

## 🎨 Fitur CSS Mobile

### Touch-Friendly Elements

- Minimum touch target: 44x44px
- Hover effects disabled on touch devices
- Active states optimized for tap feedback

### Performance Optimization

- Lite CSS untuk mobile (minified available)
- Images optimized untuk mobile bandwidth
- Font loading optimized dengan `font-display: swap`

### Accessibility

- Color contrast ≥ 4.5:1
- Support for screen readers
- Keyboard navigation fully supported
- Reduced motion respect

---

## 📞 Dukungan

Jika menemukan issue responsiveness:

1. Screenshot/Video masalah
2. Device & browser info
3. URL halaman bermasalah
4. Kontak tim development

---

**Terakhir diupdate:** April 2026  
**Version:** 2.0 (Mobile Optimized)
