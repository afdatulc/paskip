# Panduan Penulisan Rumus LaTeX (Variabel X dan Y) — SAKIP BPS

Dokumen ini berisi panduan dan contoh penulisan sintaks **LaTeX** pada menu **Capaian Kinerja > Kelola IKU** (serta bidang teks lainnya yang mendukung editor TinyMCE & MathJax).

---

## 1. Format Dasar Sintaks (Variabel X dan Y)

Rumus ditulis menggunakan pengapit `$$ ... $$` agar ditampilkan sebagai blok rumus di tengah.

```latex
$$y = \frac{n}{N} \times 100\% = \frac{X}{Y} \times 100\% = \text{Hasil}\%$$
```

* Keterangan Variabel:
  * **$X$**: Nilai Pembilang (Realisasi/Target $X$ periode berjalan).
  * **$Y$**: Nilai Penyebut (Target Basis $Y$).
  * **$n / N$**: Simbol umum rumus dasar SAKIP.

---

## 2. Contoh Penulisan Nyata

### A. Contoh untuk Target (misal $X = 0$, $Y = 4$, Target = $0\%$)
```latex
$$y = \frac{n}{N} \times 100\% = \frac{0}{4} \times 100\% = 0\text{ persen}$$
```

### B. Contoh untuk Realisasi (misal $X = 3$, $Y = 4$, Realisasi = $75\%$)
```latex
$$y = \frac{n}{N} \times 100\% = \frac{3}{4} \times 100\% = 75\text{ persen}$$
```

### C. Contoh Realisasi Kumulatif (misal $X = 12$, $Y = 15$, Realisasi = $80\%$)
```latex
$$y = \frac{n}{N} \times 100\% = \frac{12}{15} \times 100\% = 80\text{ persen}$$
```

---

## 3. Tabel Referensi Elemen LaTeX

| Elemen Matematika | Kode Sintaks LaTeX | Hasil Tampilan MathJax |
|---|---|---|
| Pecahan $\frac{a}{b}$ | `\frac{X}{Y}` | Pecahan dengan $X$ di atas dan $Y$ di bawah |
| Simbol Perkalian | `\times` | Simbol kali ($\times$) |
| Simbol Persen | `\%` | Simbol persen ($\%$) — *wajib diawali backslash `\`* |
| Teks Biasa | `\text{persen}` | Mencegah kata tercetak miring |
| Pengapit Blok | `$$ ... $$` | Menampilkan rumus di tengah (baris baru) |
| Pengapit Inline | `\( ... \)` | Menampilkan rumus sejajar dalam paragraf |

---

## 4. Cara Memasukkan Rumus pada Aplikasi

1. Buka menu **Capaian Kinerja** -> Klik tombol **Kelola** pada IKU yang dituju.
2. Pada bagian **2. Narasi & Argumen**, Anda dapat:
   * **Ketik Langsung**: Ketik kode sintaks ber-pengapit `$$ ... $$` langsung ke dalam teks editor.
   * **Atau Gunakan Tombol Formula**: Klik tombol **Formula** pada toolbar TinyMCE, ketik sintaksnya di dalam kotak dialog (tanpa `$$`), lalu klik **Insert**.
3. Simpan data. Rumus akan otomatis terender dengan rapi pada tampilan laporan dan ekspor Word.
