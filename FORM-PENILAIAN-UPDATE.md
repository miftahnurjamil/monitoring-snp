# Update Form Penilaian - Tree View Integration

## 🎯 Tujuan Update

Menyesuaikan form penilaian dengan struktur tree view baru (Aspek → Indikator → Sub-Indikator) yang konsisten dengan master pertanyaan.

## 📁 File yang Dimodifikasi

### 1. **penilaian-form.php**

Form input penilaian untuk mengisi skor setiap SNP.

#### Perubahan Utama:

- ✅ Query diubah menggunakan struktur tree view
- ✅ Tampilan menggunakan gradient cards untuk hierarki
- ✅ Aspek (Purple gradient)
- ✅ Indikator (Pink gradient)
- ✅ Sub-Indikator (White dengan border biru)
- ✅ Progress bar otomatis untuk tracking pengisian
- ✅ Validasi sebelum submit
- ✅ Load existing scores untuk mode edit

#### Fitur Baru:

1. **Progress Tracker**

   - Real-time progress bar
   - Menampilkan jumlah item yang sudah diberi skor
   - Color coding (Red < 50% < Yellow < 100% Green)

2. **Visual Hierarchy**

   - Aspek: Purple gradient header dengan badge jumlah indikator
   - Indikator: Pink gradient card dengan badge jumlah sub
   - Sub-Indikator: White card dengan border biru dan kode badge
   - Indikator tunggal: White card dengan border pink

3. **Score Selector**
   - Radio buttons dengan custom styling
   - Nilai 0-4 dengan hover effect
   - Selected state dengan gradient background
   - Smooth animations

### 2. **penilaian-detail.php**

Halaman detail hasil penilaian yang sudah disimpan.

#### Perubahan Utama:

- ✅ Query menggunakan tree structure
- ✅ Tampilan detail dengan visual hierarchy
- ✅ Skor ditampilkan dalam badge circular
- ✅ Konsisten dengan master-pertanyaan.php

#### Fitur Baru:

1. **Tree View Display**

   - Aspek header dengan purple gradient
   - Indikator card dengan pink gradient
   - Sub-indikator list dengan badges
   - Skor circular badge untuk setiap item

2. **Better Readability**
   - Clear visual separation
   - Color-coded levels
   - Icon indicators
   - Responsive layout

## 🎨 Komponen Visual

### Color Scheme

```css
Aspek:       #667eea → #764ba2 (Purple)
Indikator:   #f093fb → #f5576c (Pink)
Sub:         #4facfe → #00f2fe (Cyan)
Score:       #667eea → #764ba2 (Purple)
```

### Layout Structure

```
┌─ Aspek (Purple) ────────────────────────┐
│ 📊 Kode. Nama Aspek [X Indikator]      │
│                                          │
│  ┌─ Indikator (Pink) ────────────────┐ │
│  │ 📋 1.1. Pertanyaan [X Sub]        │ │
│  │                                     │ │
│  │   ┌─ Sub-Indikator (White+Blue) ─┐│ │
│  │   │ [a] Sub pertanyaan   [Skor]  ││ │
│  │   └──────────────────────────────┘│ │
│  └─────────────────────────────────────┘│
└──────────────────────────────────────────┘
```

## 📊 Data Flow

### Form Penilaian

```
1. Load transaksi data
2. Get tree structure (Aspek → Indikator → Sub)
3. Load existing scores if edit mode
4. Display form with visual hierarchy
5. Track progress real-time
6. Validate before submit
7. Save scores to detail_penilaian
8. Calculate & save rekapitulasi
```

### Detail Penilaian

```
1. Load transaksi data
2. Get rekapitulasi per SNP
3. Build tree structure with scores
4. Display with visual hierarchy
5. Show score badges
6. Calculate totals
```

## 💡 Cara Penggunaan

### Mengisi Penilaian

1. Pilih transaksi dari list penilaian
2. Klik "Isi Penilaian" untuk SNP tertentu
3. Form akan menampilkan tree view
4. Klik skor 0-4 untuk setiap item
5. Progress bar akan update otomatis
6. Klik "Simpan Penilaian"
7. Validasi akan muncul jika ada yang kosong

### Melihat Detail

1. Dari list penilaian, klik "Detail"
2. Lihat rekapitulasi per SNP (tabel)
3. Scroll ke bawah untuk detail tree view
4. Setiap skor ditampilkan dalam badge
5. Export PDF atau Print jika diperlukan

## 🔧 Technical Details

### Query Optimization

- Menggunakan tree structure loading
- LEFT JOIN untuk scores
- Efficient grouping
- Index-friendly queries

### JavaScript Features

```javascript
updateProgress(); // Real-time progress tracking
confirmBeforeSubmit(); // Validation before save
```

### CSS Features

- CSS Grid for layout
- Flexbox for alignment
- Linear gradients
- Smooth transitions
- Responsive design
- Print-friendly styles

## 📝 Database Structure

### Tables Used

```sql
aspek_snp
  ├─ id, snp_id, kode_aspek, nama_aspek, urutan

pertanyaan_snp (Indikator)
  ├─ id, aspek_id, snp_id, nomor_pertanyaan
  ├─ pertanyaan, jenis_jawaban, urutan

sub_pertanyaan (Sub-Indikator)
  ├─ id, pertanyaan_id, kode_sub
  ├─ sub_pertanyaan, skor_maksimal, urutan

detail_penilaian
  ├─ transaksi_id, snp_id, pertanyaan_id
  ├─ sub_pertanyaan_id, skor

rekapitulasi_snp
  ├─ transaksi_id, snp_id
  ├─ total_skor_perolehan, total_skor_maksimal
  ├─ nilai, kategori
```

## ✨ Benefits

1. **Consistency**: Sama dengan master-pertanyaan.php
2. **User-Friendly**: Visual hierarchy yang jelas
3. **Intuitive**: Tree structure mudah dipahami
4. **Real-time Feedback**: Progress tracking
5. **Responsive**: Works on all devices
6. **Print-Ready**: PDF export & print support

## 🐛 Testing Checklist

- [x] Load form penilaian
- [x] Display tree structure correctly
- [x] Score selection works
- [x] Progress bar updates
- [x] Form validation
- [x] Save scores successfully
- [x] Load existing scores
- [x] Detail page displays correctly
- [x] Export PDF works
- [x] Print layout correct

## 🔄 Migration Notes

Jika ada data lama:

1. Data existing tetap compatible
2. Query backward compatible
3. Tidak perlu migration script
4. Auto-adapt to new structure

## 📱 Responsive Behavior

- **Desktop**: Full tree view dengan sidebar
- **Tablet**: Stacked layout
- **Mobile**: Vertical scroll, condensed badges

## 🎓 Learning Resources

File terkait untuk dipelajari:

- `master-pertanyaan.php` - Master tree view
- `penilaian-form.php` - Form input
- `penilaian-detail.php` - Display result
- `TREE-VIEW-FEATURES.md` - Tree view documentation

## 📅 Version History

**v2.0** (2026-01-08)

- Integrated tree view structure
- Added progress tracking
- Enhanced visual hierarchy
- Improved user experience
- Consistent with master data

---

**Catatan**: Pastikan tabel `sub_pertanyaan` memiliki kolom `skor_maksimal` (gunakan file `update_add_skor.sql` jika belum ada).
