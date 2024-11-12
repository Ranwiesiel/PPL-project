<x-staffakademik-layout>
    <!-- KONTEN -->
    <div class="col-span-full xl:col-auto">
            
        <div class="p-4 mb-4 space-y-6 bg-white border border-gray-200 rounded-lg shadow-sm 2xl:col-span-2 dark:border-gray-700 sm:p-6 dark:bg-gray-800">

            <!-- Filter kelas -->
            <div>
                <form action="{{ route('staff_akademik.jadwal') }}" method="GET" class="flex items-center">
                    <label for="kelas_id" class="mr-2">Pilih Kelas:</label>
                    <select name="kelas_id" id="kelas_id" class="border-gray-300 rounded-md shadow-sm" onchange="this.form.submit()">
                        <option value="">Semua Kelas</option>
                        @foreach($kelas as $kls)
                            <option value="{{ $kls->id_kelas }}" {{ isset($kelas_id) && $kelas_id == $kls->id_kelas ? 'selected' : '' }}>
                                {{ $kls->nama_kelas }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>

            <!-- Pesan Bentrok -->
            @if(session('error'))
                <div class="bg-red-500 text-white p-4 rounded mb-4">
                    <strong>List jadwal bentrok</strong>
                    <ul class="list-disc pl-5">
                        @foreach(session('bentrok') as $item)
                            <li>
                                @if($item['tipe'] == 'guru')
                                    Guru {{ $item['nama_guru'] }} memiliki jadwal bentrok di kelas lain pada hari {{ $item['nama_hari'] }} jam {{ $item['jam_pelajaran'] }}.
                                @elseif($item['tipe'] == 'kelas')
                                    Kelas {{ $item['nama_kelas'] }} sudah memiliki jadwal pada hari {{ $item['nama_hari'] }} jam {{ $item['jam_pelajaran'] }}.
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Pesan Update Bentrok -->
            @if(session('error-update'))
                <div class="bg-red-500 text-white p-4 rounded mb-4">
                    <strong>List jadwal bentrok</strong>
                    <ul class="list-disc pl-5">
                        <li>{{ session('error-update') }}</li>
                    </ul>
                </div>
            @endif

            <!-- Pesan error excel -->
            @if (session('error-excel'))
            <div class="bg-red-500 text-white p-4 rounded mb-4">
                <strong>Terjadi kesalahan saat mengimpor jadwal:</strong>
                <ul class="list-disc pl-5">
                    @foreach (explode(";", session('error-excel')) as $error)
                    @if ($loop->last)
                        @break
                    @endif
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <!-- Pesan Sukses -->
            @if(session('success'))
                <div class="bg-green-500 text-white p-4 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif
            
            <div>
                @foreach ($kelas as $kls)
                @if (isset($kelas_id) && $kelas_id != $kls->id_kelas)
                @continue
                @endif

                @php
                    $cek = DB::table('kelas')
                    ->join('kelas_mata_pelajaran', 'kelas.id_kelas', '=', 'kelas_mata_pelajaran.kelas_id')
                    ->join('mata_pelajaran', 'kelas_mata_pelajaran.mata_pelajaran_id', '=', 'mata_pelajaran.id_matpel')
                    ->join('tahun_ajaran', 'kelas_mata_pelajaran.tahun_ajaran_id', '=', 'tahun_ajaran.id_tahun_ajaran')
                    ->where('tahun_ajaran.aktif', 1)
                    ->where('kelas.nama_kelas', $kls->nama_kelas)
                    ->get();
                @endphp

                @if ($cek->isEmpty())
                    @continue
                @endif

                <!-- nama kelas -->
                <div class="px-4 py-2 mt-4 text-gray-700 border-b border-gray-200 dark:border-gray-600">
                    <h3 class="font-semibold text-lg">Jadwal Kelas {{ $kls->nama_kelas }}</h3>
                </div>
                <!-- Table -->
                <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                    <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                            <tr>
                                <th scope="col" class="px-6 py-3">
                                    Hari
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Jam
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Mata Pelajaran
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Guru
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($data as $item)
                                @if ($item->nama_kelas == $kls->nama_kelas && $item->nama_hari=="Senin")
                                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                        <td class="px-6 py-4">
                                            {{ $item->nama_hari }}
                                        </td>
                                        <td class="px-6 py-4">
                                            {{ $item->waktu_mulai }} - {{ $item->waktu_selesai }}
                                        </td>
                                        <td class="px-6 py-4">
                                            {{ $item->nama_matpel }}
                                        </td>
                                        <td class="px-6 py-4">
                                            {{ $item->nama_guru }}
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                            <!-- Repeat foreach loop for other days as well (Selasa, Rabu, etc.) without the "Action" column -->
                        </tbody>
                    </table>
                </div>
                @endforeach
            </div>
        </div>
</x-staffakademik-layout>