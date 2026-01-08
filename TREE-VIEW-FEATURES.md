# Tree View Dinamis - Master Pertanyaan SNP

## Fitur Utama

Tree view dinamis telah ditambahkan pada halaman **Master Pertanyaan SNP** dengan struktur hierarki:

```
📊 Aspek
   └─ 📋 Indikator
      └─ ✓ Sub-Indikator
```

## Fitur-Fitur

### 1. **Visualisasi Hierarki**

- Struktur tree yang jelas dengan warna berbeda untuk setiap level:
  - **Aspek**: Gradient ungu (purple)
  - **Indikator**: Gradient pink-red
  - **Sub-Indikator**: Gradient biru (cyan)

### 2. **Interaktif**

- **Expand/Collapse**: Klik pada node untuk membuka/menutup child nodes
- **Expand All**: Tombol untuk membuka semua node sekaligus
- **Collapse All**: Tombol untuk menutup semua node sekaligus

### 3. **Informasi Lengkap**

- Badge jumlah child nodes pada setiap level
- Skor maksimal ditampilkan pada sub-indikator
- Aksi quick access (tambah, edit, hapus)

### 4. **Animasi Smooth**

- Transisi smooth saat expand/collapse
- Hover effect dengan transform dan shadow
- Rotating icon untuk toggle

### 5. **Quick Add Forms**

- Form tambah Aspek di sidebar
- Form tambah Indikator di sidebar
- Navigasi langsung ke halaman sub-indikator

## Cara Penggunaan

### Menambah Data

1. **Tambah Aspek**

   - Isi form "Tambah Aspek" di sidebar kanan
   - Masukkan kode aspek (contoh: 1, 2, 3)
   - Masukkan nama aspek
   - Tentukan urutan
   - Klik "Simpan Aspek"

2. **Tambah Indikator**

   - Pilih aspek dari dropdown
   - Isi nomor pertanyaan (contoh: 1.1, 1.2)
   - Tulis pertanyaan/indikator
   - Pilih jenis jawaban
   - Klik "Simpan Indikator"

3. **Tambah Sub-Indikator**
   - Klik tombol hijau "+ Sub" pada indikator
   - Akan redirect ke halaman sub-indikator
   - Tambahkan sub-indikator dengan skor

### Navigasi Tree View

- **Klik pada node Aspek** → Buka/tutup daftar indikator
- **Klik pada node Indikator** → Buka/tutup daftar sub-indikator
- **Tombol Expand All** → Buka semua level sekaligus
- **Tombol Collapse All** → Tutup semua level

### Aksi Cepat

- **Tombol Trash (merah)** → Hapus aspek/indikator
- **Tombol + Sub (hijau)** → Kelola sub-indikator
- **Event.stopPropagation()** → Mencegah toggle saat klik tombol aksi

## Struktur CSS

### Tree View Classes

- `.tree-view` - Container utama
- `.tree-node` - Node individual
- `.tree-node-aspek` - Node level aspek
- `.tree-node-indikator` - Node level indikator
- `.tree-node-sub` - Node level sub-indikator
- `.tree-toggle` - Icon toggle (chevron)
- `.tree-children` - Container child nodes
- `.tree-badge` - Badge informasi
- `.tree-actions` - Container tombol aksi

### Animations

- **Transform**: translateX(5px) on hover
- **Box Shadow**: Meningkat saat hover
- **Max Height**: Animasi smooth expand/collapse
- **Rotate**: Icon toggle 90° saat collapse

## JavaScript Functions

```javascript
toggleNode(element); // Toggle expand/collapse node
expandAll(); // Expand semua nodes
collapseAll(); // Collapse semua nodes
confirmDelete(url); // Konfirmasi hapus data
```

## Browser Compatibility

✅ Chrome/Edge (Latest)
✅ Firefox (Latest)
✅ Safari (Latest)
✅ Mobile browsers

## Teknologi

- **HTML5**: Struktur semantic
- **CSS3**: Gradients, transitions, transforms
- **JavaScript (Vanilla)**: Interaktivitas
- **Bootstrap Icons**: Icon set
- **PHP**: Server-side rendering

## Update Log

- **v1.0** (2026-01-08): Initial release dengan tree view dinamis
  - Tree structure dengan 3 level
  - Expand/collapse functionality
  - Quick add forms
  - Smooth animations
  - Responsive design
