<?php

namespace Database\Seeders;

use App\Models\buku;
use App\Models\jenis_buku;
use App\Models\kategori_buku;
use App\Models\transaksi_peminjaman;
use App\Models\Guru;
use App\Models\Siswa;
use Illuminate\Support\Str;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Carbon\Carbon;

class TransaksiPeminjamanBukuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // transaksi
        // Ambil data guru dan siswa
        $guruNIP = Guru::pluck('nip')->toArray(); // Ambil NIP guru
        $siswaNISN = Siswa::pluck('nisn')->toArray(); // Ambil NISN siswa

        // Ambil semua buku
        $bukuList = buku::all();

        for ($i = 0; $i < 20; $i++) { // Misalnya 50 transaksi peminjaman
            // Pilih jenis peminjam secara acak (0 = Guru, 1 = Siswa)
            $jenisPeminjam = rand(0, 1);

            // Tentukan kode peminjam
            if ($jenisPeminjam === 0) {
                if (empty($guruNIP)) continue; // Jika tidak ada guru, lanjutkan ke iterasi berikutnya
                $kodePeminjam = $guruNIP[array_rand($guruNIP)];
            } else {
                if (empty($siswaNISN)) continue; // Jika tidak ada siswa, lanjutkan ke iterasi berikutnya
                $kodePeminjam = $siswaNISN[array_rand($siswaNISN)];
            }

            // Pilih buku secara acak
            $buku = $bukuList->random();

            // Cek stok buku
            // if ($buku->stok <= 0) {
            //     continue; // Jika stok tidak mencukupi, lewati iterasi
            // }

            // Hitung jumlah buku yang belum dikembalikan oleh peminjam
            $jumlahBukuBelumDikembalikan = transaksi_peminjaman::where('kode_peminjam', $kodePeminjam)
                ->where('status_pengembalian', 0) // Belum dikembalikan
                ->count();

            // Cek batas peminjaman
            if ($jumlahBukuBelumDikembalikan >= 3) {
                continue; // Jika sudah meminjam 3 buku, lewati iterasi
            }

            // Hitung jumlah denda yang belum dibayar
            $jumlahDendaBelumDibayar = transaksi_peminjaman::where('kode_peminjam', $kodePeminjam)
                ->where('status_denda', 1) // Denda belum dibayar
                ->count();

            // Cek batas denda
            if ($jumlahDendaBelumDibayar >= 3) {
                continue; // Jika ada 3 atau lebih denda belum dibayar, lewati iterasi
            }

            // Tentukan durasi peminjaman
            if ($jenisPeminjam === 1) { // Siswa
                if ($buku->id_jenis_buku === 1) { // Buku paket
                    $tglPengembalian = now()->addYear(); // 1 tahun
                } else { // Buku non-paket
                    $tglPengembalian = now()->addWeeks(2); // 2 minggu
                }
            } else { // Guru
                $tglPengembalian = now()->addYear(); // 1 tahun untuk semua jenis buku
            }

            $transaksi_peminjaman_id = Str::uuid();
            // Buat transaksi peminjaman
            if ($i > 10) {
                // Transaksi Telat
                transaksi_peminjaman::create([
                    'id_transaksi_peminjaman' => $transaksi_peminjaman_id,
                    'id_buku' => $buku->id_buku,
                    'kode_peminjam' => $kodePeminjam,
                    'tgl_awal_peminjaman' => now()->subDays(8),
                    'tgl_pengembalian' => $tglPengembalian->subDays(8),
                    'denda' => 0,
                    'status_pengembalian' => 0, // Belum dikembalikan
                    'jenis_peminjam' => $jenisPeminjam,
                    'status_denda' => 0, // Tidak ada denda
                    'stok' => 1, // Stok selalu bernilai 1 pada transaksi peminjaman
                ]);
            } else {
                // Transaksi Masih Jalan
                transaksi_peminjaman::create([
                    'id_transaksi_peminjaman' => $transaksi_peminjaman_id,
                    'id_buku' => $buku->id_buku,
                    'kode_peminjam' => $kodePeminjam,
                    'tgl_awal_peminjaman' => now(),
                    'tgl_pengembalian' => $tglPengembalian,
                    'denda' => 0,
                    'status_pengembalian' => 0, // Belum dikembalikan
                    'jenis_peminjam' => $jenisPeminjam,
                    'status_denda' => 0, // Tidak ada denda
                    'stok' => 1, // Stok selalu bernilai 1 pada transaksi peminjaman
                ]);
            }
            // Kurangi stok buku di tabel buku
            // $buku->decrement('stok');
        }
    }
}
