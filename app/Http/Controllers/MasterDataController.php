<?php

namespace App\Http\Controllers;

use App\Models\Dokter;
use App\Models\JadwalDokter;
use App\Models\Layanan;
use App\Models\Pasien;
use App\Models\Pegawai;
use App\Models\Poli;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MasterDataController extends Controller
{
    public function index(Request $request, string $resource)
    {
        $config = $this->config($resource);
        $query = $config['model']::query()->with($config['with'] ?? []);

        if ($request->filled('q')) {
            $query->where(function ($builder) use ($request, $config) {
                foreach ($config['search'] as $column) {
                    $builder->orWhere($column, 'like', '%'.$request->q.'%');
                }
            });
        }

        $items = $query->latest()->paginate(10)->withQueryString();

        return view('master.index', [
            'resource' => $resource,
            'config' => $this->withOptions($config),
            'items' => $items,
        ]);
    }

    public function create(string $resource)
    {
        $config = $this->withOptions($this->config($resource));

        return view('master.form', [
            'resource' => $resource,
            'config' => $config,
            'item' => null,
            'defaults' => $this->defaults($resource),
        ]);
    }

    public function store(Request $request, string $resource)
    {
        $config = $this->config($resource);
        $data = $this->validated($request, $config);

        if ($resource === 'pasien' && blank($data['no_rm'] ?? null)) {
            $data['no_rm'] = $this->nextNumber(Pasien::class, 'no_rm', 'RM');
        }

        if ($resource === 'dokter' && blank($data['kode_dokter'] ?? null)) {
            $data['kode_dokter'] = $this->nextNumber(Dokter::class, 'kode_dokter', 'DR');
        }

        $config['model']::create($data);

        return redirect()->route('master.index', $resource)->with('success', $config['singular'].' berhasil ditambahkan.');
    }

    public function edit(string $resource, int $id)
    {
        $config = $this->withOptions($this->config($resource));
        $item = $config['model']::findOrFail($id);

        return view('master.form', [
            'resource' => $resource,
            'config' => $config,
            'item' => $item,
            'defaults' => [],
        ]);
    }

    public function update(Request $request, string $resource, int $id)
    {
        $config = $this->config($resource);
        $item = $config['model']::findOrFail($id);
        $item->update($this->validated($request, $config, $item));

        return redirect()->route('master.index', $resource)->with('success', $config['singular'].' berhasil diperbarui.');
    }

    public function destroy(string $resource, int $id)
    {
        $config = $this->config($resource);
        $item = $config['model']::findOrFail($id);

        try {
            $item->delete();
        } catch (QueryException) {
            return back()->with('error', 'Data belum bisa dihapus karena masih dipakai oleh transaksi lain.');
        }

        return back()->with('success', $config['singular'].' berhasil dihapus.');
    }

    private function validated(Request $request, array $config, mixed $item = null): array
    {
        $rules = $config['rules'];

        foreach ($config['unique'] ?? [] as $field => $table) {
            $rules[$field][] = Rule::unique($table, $field)->ignore($item?->id);
        }

        return $request->validate($rules);
    }

    private function config(string $resource): array
    {
        $resources = $this->resources();
        abort_unless(isset($resources[$resource]), 404);

        $config = $resources[$resource];
        abort_unless(in_array(auth()->user()->role, $config['roles'], true), 403);

        return $config;
    }

    private function withOptions(array $config): array
    {
        foreach ($config['fields'] as &$field) {
            if (($field['source'] ?? null) === 'poli') {
                $field['options'] = Poli::orderBy('nama_poli')->pluck('nama_poli', 'id')->all();
            }

            if (($field['source'] ?? null) === 'dokter') {
                $field['options'] = Dokter::with('poli')->orderBy('nama_dokter')->get()
                    ->mapWithKeys(fn (Dokter $dokter) => [
                        $dokter->id => $dokter->nama_dokter.' - '.$dokter->poli?->nama_poli,
                    ])->all();
            }
        }

        return $config;
    }

    private function defaults(string $resource): array
    {
        return match ($resource) {
            'pasien' => ['no_rm' => $this->nextNumber(Pasien::class, 'no_rm', 'RM')],
            'dokter' => ['kode_dokter' => $this->nextNumber(Dokter::class, 'kode_dokter', 'DR')],
            default => [],
        };
    }

    private function nextNumber(string $model, string $column, string $prefix): string
    {
        $datePrefix = $prefix.now()->format('ymd');
        $next = $model::where($column, 'like', $datePrefix.'%')->count() + 1;

        return $datePrefix.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    private function resources(): array
    {
        $activeOptions = ['Aktif' => 'Aktif', 'Tidak Aktif' => 'Tidak Aktif'];

        return [
            'pasien' => [
                'title' => 'Data Pasien',
                'singular' => 'Pasien',
                'model' => Pasien::class,
                'roles' => ['admin', 'pendaftaran'],
                'search' => ['no_rm', 'nik', 'nama_pasien'],
                'columns' => [
                    'no_rm' => 'No RM',
                    'nik' => 'NIK',
                    'nama_pasien' => 'Nama',
                    'jenis_kelamin' => 'Jenis Kelamin',
                    'no_hp' => 'No HP',
                ],
                'fields' => [
                    ['name' => 'no_rm', 'label' => 'Nomor Rekam Medis', 'type' => 'text'],
                    ['name' => 'nik', 'label' => 'NIK', 'type' => 'text'],
                    ['name' => 'nama_pasien', 'label' => 'Nama Pasien', 'type' => 'text'],
                    ['name' => 'jenis_kelamin', 'label' => 'Jenis Kelamin', 'type' => 'select', 'options' => ['Laki-laki' => 'Laki-laki', 'Perempuan' => 'Perempuan']],
                    ['name' => 'tanggal_lahir', 'label' => 'Tanggal Lahir', 'type' => 'date'],
                    ['name' => 'alamat', 'label' => 'Alamat', 'type' => 'textarea'],
                    ['name' => 'no_hp', 'label' => 'Nomor HP', 'type' => 'text'],
                    ['name' => 'golongan_darah', 'label' => 'Golongan Darah', 'type' => 'select', 'options' => ['' => '-', 'A' => 'A', 'B' => 'B', 'AB' => 'AB', 'O' => 'O']],
                    ['name' => 'alergi', 'label' => 'Alergi / Catatan Medis', 'type' => 'textarea'],
                ],
                'rules' => [
                    'no_rm' => ['nullable', 'string', 'max:30'],
                    'nik' => ['required', 'string', 'max:30'],
                    'nama_pasien' => ['required', 'string', 'max:255'],
                    'jenis_kelamin' => ['required', 'string', 'max:20'],
                    'tanggal_lahir' => ['required', 'date'],
                    'alamat' => ['required', 'string'],
                    'no_hp' => ['nullable', 'string', 'max:30'],
                    'golongan_darah' => ['nullable', 'string', 'max:5'],
                    'alergi' => ['nullable', 'string'],
                ],
                'unique' => ['no_rm' => 'pasien', 'nik' => 'pasien'],
            ],
            'poli' => [
                'title' => 'Data Poli',
                'singular' => 'Poli',
                'model' => Poli::class,
                'roles' => ['admin'],
                'search' => ['nama_poli', 'status'],
                'columns' => ['nama_poli' => 'Nama Poli', 'deskripsi' => 'Deskripsi', 'status' => 'Status'],
                'fields' => [
                    ['name' => 'nama_poli', 'label' => 'Nama Poli', 'type' => 'text'],
                    ['name' => 'deskripsi', 'label' => 'Deskripsi', 'type' => 'textarea'],
                    ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => $activeOptions],
                ],
                'rules' => [
                    'nama_poli' => ['required', 'string', 'max:255'],
                    'deskripsi' => ['nullable', 'string'],
                    'status' => ['required', 'string', 'max:20'],
                ],
            ],
            'layanan' => [
                'title' => 'Data Layanan',
                'singular' => 'Layanan',
                'model' => Layanan::class,
                'roles' => ['admin'],
                'with' => ['poli'],
                'search' => ['nama_layanan', 'kategori', 'status'],
                'columns' => ['poli.nama_poli' => 'Poli', 'nama_layanan' => 'Layanan', 'kategori' => 'Kategori', 'harga' => 'Harga', 'status' => 'Status'],
                'fields' => [
                    ['name' => 'poli_id', 'label' => 'Poli', 'type' => 'select', 'source' => 'poli'],
                    ['name' => 'nama_layanan', 'label' => 'Nama Layanan', 'type' => 'text'],
                    ['name' => 'kategori', 'label' => 'Kategori', 'type' => 'text'],
                    ['name' => 'harga', 'label' => 'Harga', 'type' => 'number'],
                    ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => $activeOptions],
                ],
                'rules' => [
                    'poli_id' => ['required', 'exists:poli,id'],
                    'nama_layanan' => ['required', 'string', 'max:255'],
                    'kategori' => ['required', 'string', 'max:255'],
                    'harga' => ['required', 'integer', 'min:0'],
                    'status' => ['required', 'string', 'max:20'],
                ],
            ],
            'dokter' => [
                'title' => 'Data Dokter',
                'singular' => 'Dokter',
                'model' => Dokter::class,
                'roles' => ['admin'],
                'with' => ['poli'],
                'search' => ['nama_dokter', 'kode_dokter', 'spesialisasi'],
                'columns' => ['poli.nama_poli' => 'Poli', 'nama_dokter' => 'Nama Dokter', 'kode_dokter' => 'Kode', 'spesialisasi' => 'Spesialisasi', 'status' => 'Status'],
                'fields' => [
                    ['name' => 'poli_id', 'label' => 'Poli', 'type' => 'select', 'source' => 'poli'],
                    ['name' => 'nama_dokter', 'label' => 'Nama Dokter', 'type' => 'text'],
                    ['name' => 'kode_dokter', 'label' => 'Kode Dokter / SIP', 'type' => 'text'],
                    ['name' => 'spesialisasi', 'label' => 'Spesialisasi', 'type' => 'text'],
                    ['name' => 'no_hp', 'label' => 'Nomor HP', 'type' => 'text'],
                    ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => $activeOptions],
                ],
                'rules' => [
                    'poli_id' => ['required', 'exists:poli,id'],
                    'nama_dokter' => ['required', 'string', 'max:255'],
                    'kode_dokter' => ['nullable', 'string', 'max:30'],
                    'spesialisasi' => ['nullable', 'string', 'max:255'],
                    'no_hp' => ['nullable', 'string', 'max:30'],
                    'status' => ['required', 'string', 'max:20'],
                ],
                'unique' => ['kode_dokter' => 'dokter'],
            ],
            'pegawai' => [
                'title' => 'Data Pegawai',
                'singular' => 'Pegawai',
                'model' => Pegawai::class,
                'roles' => ['admin'],
                'search' => ['nama_pegawai', 'jabatan', 'email'],
                'columns' => ['nama_pegawai' => 'Nama Pegawai', 'jabatan' => 'Jabatan', 'no_hp' => 'No HP', 'email' => 'Email', 'status' => 'Status'],
                'fields' => [
                    ['name' => 'nama_pegawai', 'label' => 'Nama Pegawai', 'type' => 'text'],
                    ['name' => 'jabatan', 'label' => 'Jabatan', 'type' => 'text'],
                    ['name' => 'no_hp', 'label' => 'Nomor HP', 'type' => 'text'],
                    ['name' => 'email', 'label' => 'Email', 'type' => 'email'],
                    ['name' => 'alamat', 'label' => 'Alamat', 'type' => 'textarea'],
                    ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => $activeOptions],
                ],
                'rules' => [
                    'nama_pegawai' => ['required', 'string', 'max:255'],
                    'jabatan' => ['required', 'string', 'max:255'],
                    'no_hp' => ['nullable', 'string', 'max:30'],
                    'email' => ['nullable', 'email', 'max:255'],
                    'alamat' => ['nullable', 'string'],
                    'status' => ['required', 'string', 'max:20'],
                ],
            ],
            'jadwal' => [
                'title' => 'Jadwal Dokter',
                'singular' => 'Jadwal',
                'model' => JadwalDokter::class,
                'roles' => ['admin'],
                'with' => ['dokter.poli'],
                'search' => ['hari', 'status'],
                'columns' => ['dokter.nama_dokter' => 'Dokter', 'hari' => 'Hari', 'jam_mulai' => 'Mulai', 'jam_selesai' => 'Selesai', 'status' => 'Status'],
                'fields' => [
                    ['name' => 'dokter_id', 'label' => 'Dokter', 'type' => 'select', 'source' => 'dokter'],
                    ['name' => 'hari', 'label' => 'Hari', 'type' => 'select', 'options' => array_combine(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'], ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'])],
                    ['name' => 'jam_mulai', 'label' => 'Jam Mulai', 'type' => 'time'],
                    ['name' => 'jam_selesai', 'label' => 'Jam Selesai', 'type' => 'time'],
                    ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => $activeOptions],
                ],
                'rules' => [
                    'dokter_id' => ['required', 'exists:dokter,id'],
                    'hari' => ['required', 'string', 'max:20'],
                    'jam_mulai' => ['required', 'date_format:H:i'],
                    'jam_selesai' => ['required', 'date_format:H:i', 'after:jam_mulai'],
                    'status' => ['required', 'string', 'max:20'],
                ],
            ],
        ];
    }
}
