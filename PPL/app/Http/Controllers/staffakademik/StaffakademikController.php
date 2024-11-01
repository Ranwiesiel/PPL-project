<?php

namespace App\Http\Controllers\staffakademik;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class StaffakademikController extends Controller
{
    public function index()
    {
        // Logic for the dashboard, e.g., fetching data or statistics for the dashboard view
        return view('staff_akademik.dashboard'); // Adjust view path as needed
    }

    /**
     * START JADWAL MANAGEMENT
     */

    //  read jadwal
    public function jadwalIndex()
    {
        $kelas = DB::table('kelas')
            ->orderByRaw('LENGTH(nama_kelas)')
            ->orderBy('nama_kelas')
            ->get();
        $hari = DB::table('hari')
            ->orderByRaw("FIELD(nama_hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu')")
            ->get();
        $data = DB::table('kelas_mata_pelajaran')
            ->join('kelas', 'kelas_mata_pelajaran.kelas_id', '=', 'kelas.id_kelas')
            ->join('mata_pelajaran', 'kelas_mata_pelajaran.mata_pelajaran_id', '=', 'mata_pelajaran.id_matpel')
            ->join('guru', 'kelas_mata_pelajaran.guru_id', '=', 'guru.id_guru')
            ->join('hari', 'kelas_mata_pelajaran.hari_id', '=', 'hari.id_hari')
            ->join('tahun_ajaran', 'kelas_mata_pelajaran.tahun_ajaran_id', '=', 'tahun_ajaran.id_tahun_ajaran')
            ->select(
                'kelas_mata_pelajaran.id_kelas_mata_pelajaran',
                'kelas_mata_pelajaran.kelas_id',
                'kelas.nama_kelas',
                'kelas_mata_pelajaran.mata_pelajaran_id',
                'mata_pelajaran.nama_matpel',
                'kelas_mata_pelajaran.guru_id',
                'guru.nip',
                'guru.nama_guru',
                'kelas_mata_pelajaran.hari_id',
                'hari.nama_hari',
                'kelas_mata_pelajaran.waktu_mulai',
                'kelas_mata_pelajaran.waktu_selesai',
                'kelas_mata_pelajaran.tahun_ajaran_id',
                'tahun_ajaran.tahun_mulai',
                'tahun_ajaran.tahun_selesai',
                'tahun_ajaran.semester',
                'tahun_ajaran.aktif'
            )
            ->where('tahun_ajaran.aktif', 1) // Kondisi where
            ->orderBy('hari.id_hari') // Urutkan berdasarkan hari
            ->orderBy('kelas_mata_pelajaran.waktu_mulai') // Urutkan berdasarkan waktu mulai
            ->get();

        return view('staff_akademik.jadwalManagemen.index', compact('data', 'kelas'));
    }

    // tambah jadwal
    public function createJadwal()
    {
        $tahunAjaran = DB::table('tahun_ajaran')->where('aktif', 1)->first();
        $kelas = DB::table('kelas')
            ->orderByRaw('LENGTH(nama_kelas)')
            ->orderBy('nama_kelas')
            ->get();
        $hari = DB::table('hari')
            ->orderByRaw("FIELD(nama_hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu')")
            ->get();
        $guruMataPelajaran = DB::table('guru_mata_pelajaran')
            ->join('guru', 'guru_mata_pelajaran.guru_id', '=', 'guru.id_guru')
            ->join('mata_pelajaran', 'guru_mata_pelajaran.matpel_id', '=', 'mata_pelajaran.id_matpel')
            ->select('guru.id_guru', 'guru.nama_guru', 'mata_pelajaran.id_matpel', 'mata_pelajaran.nama_matpel')
            ->get();
        // dd($guruMataPelajaran);
        return view('staff_akademik.jadwalManagemen.create', compact('kelas', 'hari', 'guruMataPelajaran', 'tahunAjaran'));
    }

    public function storeJadwal(Request $request)
    {
        $jadwalData = $request->input('jadwal');
        $tahunAjaranId = $request->input('tahun_ajaran_id');
        $kelasId = $request->input('kelas_id');
        $bentrok = []; // Array untuk menampung jadwal yang bentrok

        foreach ($jadwalData as $jadwal) {
            $guruid_matpelid = explode('_', $jadwal['guru_id']);
            $guruId = $guruid_matpelid[0];
            $mataPelajaranId = $guruid_matpelid[1];
            
            // Pisahkan waktu mulai dan selesai
            [$waktuMulai, $waktuSelesai] = explode('-', $jadwal['jam_pelajaran']);
            $hariId = $jadwal['hari_id'];

            // Pengecekan bentrok di semua kelas pada tahun ajaran aktif untuk guru
            $bentrokGuru = DB::table('kelas_mata_pelajaran')
                ->where('guru_id', $guruId)
                ->where('hari_id', $hariId)
                ->where('tahun_ajaran_id', $tahunAjaranId)
                ->where(function ($query) use ($waktuMulai, $waktuSelesai) {
                    $query->whereBetween('waktu_mulai', [$waktuMulai, $waktuSelesai])
                        ->orWhereBetween('waktu_selesai', [$waktuMulai, $waktuSelesai])
                        ->orWhere(function ($query) use ($waktuMulai, $waktuSelesai) {
                            $query->where('waktu_mulai', '<=', $waktuMulai)
                                ->where('waktu_selesai', '>=', $waktuSelesai);
                        });
                })
                ->exists();

            // Pengecekan bentrok di kelas yang sama, hari yang sama, dan jam yang sama
            $bentrokKelas = DB::table('kelas_mata_pelajaran')
                ->where('kelas_id', $kelasId)
                ->where('hari_id', $hariId)
                ->where('tahun_ajaran_id', $tahunAjaranId)
                ->where(function ($query) use ($waktuMulai, $waktuSelesai) {
                    $query->whereBetween('waktu_mulai', [$waktuMulai, $waktuSelesai])
                        ->orWhereBetween('waktu_selesai', [$waktuMulai, $waktuSelesai])
                        ->orWhere(function ($query) use ($waktuMulai, $waktuSelesai) {
                            $query->where('waktu_mulai', '<=', $waktuMulai)
                                ->where('waktu_selesai', '>=', $waktuSelesai);
                        });
                })
                ->exists();

            // Jika bentrok guru atau bentrok kelas, tambahkan ke array bentrok
            if ($bentrokGuru) {
                $bentrok[] = [
                    'tipe' => 'guru',
                    'nama_guru' => DB::table('guru')->where('id_guru', $guruId)->value('nama_guru'),
                    'nama_kelas' => DB::table('kelas')->where('id_kelas', $kelasId)->value('nama_kelas'),
                    'nama_hari' => DB::table('hari')->where('id_hari', $hariId)->value('nama_hari'),
                    'jam_pelajaran' => "{$waktuMulai}-{$waktuSelesai}"
                ];
            } elseif ($bentrokKelas) {
                $bentrok[] = [
                    'tipe' => 'kelas',
                    'nama_kelas' => DB::table('kelas')->where('id_kelas', $kelasId)->value('nama_kelas'),
                    'nama_hari' => DB::table('hari')->where('id_hari', $hariId)->value('nama_hari'),
                    'jam_pelajaran' => "{$waktuMulai}-{$waktuSelesai}"
                ];
            } else {
                // Jika tidak bentrok, lakukan insert
                DB::table('kelas_mata_pelajaran')->insert([
                    'id_kelas_mata_pelajaran' => (string) Str::uuid(),
                    'kelas_id' => $kelasId,
                    'hari_id' => $hariId,
                    'waktu_mulai' => $waktuMulai,
                    'waktu_selesai' => $waktuSelesai,
                    'guru_id' => $guruId,
                    'mata_pelajaran_id' => $mataPelajaranId,
                    'tahun_ajaran_id' => $tahunAjaranId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Jika ada bentrok, kembalikan ke halaman index dengan pesan error
        if (!empty($bentrok)) {
            return redirect()->route('staff_akademik.jadwal')->with('error', 'List jadwal bentrok')->with('bentrok', $bentrok);
        }

        return redirect()->route('staff_akademik.jadwal')->with('success', 'Jadwal berhasil ditambahkan.');
    }

    /**
     * END JADWAL MANAGEMENT
     */
}
