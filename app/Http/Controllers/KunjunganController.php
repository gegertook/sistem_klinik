<?php

namespace App\Http\Controllers;

use App\Models\Dokter;
use App\Models\Kunjungan;
use App\Models\Pasien;
use App\Models\Poli;
use Illuminate\Http\Request;

class KunjunganController extends Controller
{
    public function index(Request $request)
    {
        $query = Kunjungan::with(['pasien', 'poli', 'dokter', 'tagihan']);

        if ($request->filled('q')) {
            $query->where(function ($builder) use ($request) {
                $builder
                    ->where('no_kunjungan', 'like', '%'.$request->q.'%')
                    ->orWhereHas('pasien', fn ($pasien) => $pasien
                        ->where('nama_pasien', 'like', '%'.$request->q.'%')
                        ->orWhere('no_rm', 'like', '%'.$request->q.'%'));
            });
        }

        if ($request->filled('status')) {
            $query->where('status_kunjungan', $request->status);
        }

        return view('kunjungan.index', [
            'items' => $query->latest()->paginate(10)->withQueryString(),
            'statuses' => $this->statuses(),
        ]);
    }

    public function create(Request $request)
    {
        $selectedPasienId = $request->integer('pasien_id');

        return view('kunjungan.form', [
            'pasien' => Pasien::orderBy('nama_pasien')->get(),
            'poli' => Poli::where('status', 'Aktif')->orderBy('nama_poli')->get(),
            'dokter' => Dokter::with('poli')->where('status', 'Aktif')->orderBy('nama_dokter')->get(),
            'kunjungan' => null,
            'selectedPasienId' => Pasien::whereKey($selectedPasienId)->exists() ? $selectedPasienId : null,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'pasien_id' => ['required', 'exists:pasien,id'],
            'poli_id' => ['required', 'exists:poli,id'],
            'dokter_id' => ['required', 'exists:dokter,id'],
            'tanggal_kunjungan' => ['required', 'date'],
            'keluhan' => ['nullable', 'string'],
        ]);

        $data['no_kunjungan'] = $this->nextNumber();
        $data['status_kunjungan'] = 'Menunggu Pemeriksaan';

        Kunjungan::create($data);

        return redirect()->route('kunjungan.index')->with('success', 'Kunjungan pasien berhasil didaftarkan.');
    }

    public function edit(Kunjungan $kunjungan)
    {
        abort_if($kunjungan->pemeriksaan()->exists(), 422, 'Kunjungan yang sudah diperiksa tidak dapat diubah dari pendaftaran.');

        return view('kunjungan.form', [
            'pasien' => Pasien::orderBy('nama_pasien')->get(),
            'poli' => Poli::where('status', 'Aktif')->orderBy('nama_poli')->get(),
            'dokter' => Dokter::with('poli')->where('status', 'Aktif')->orderBy('nama_dokter')->get(),
            'kunjungan' => $kunjungan,
        ]);
    }

    public function update(Request $request, Kunjungan $kunjungan)
    {
        abort_if($kunjungan->pemeriksaan()->exists(), 422, 'Kunjungan yang sudah diperiksa tidak dapat diubah dari pendaftaran.');

        $data = $request->validate([
            'pasien_id' => ['required', 'exists:pasien,id'],
            'poli_id' => ['required', 'exists:poli,id'],
            'dokter_id' => ['required', 'exists:dokter,id'],
            'tanggal_kunjungan' => ['required', 'date'],
            'keluhan' => ['nullable', 'string'],
        ]);

        $kunjungan->update($data);

        return redirect()->route('kunjungan.index')->with('success', 'Kunjungan berhasil diperbarui.');
    }

    public function destroy(Kunjungan $kunjungan)
    {
        abort_if($kunjungan->pemeriksaan()->exists(), 422, 'Kunjungan yang sudah diperiksa tidak dapat dihapus.');

        $kunjungan->delete();

        return back()->with('success', 'Kunjungan berhasil dihapus.');
    }

    private function nextNumber(): string
    {
        $prefix = 'KJ'.now()->format('Ymd');
        $next = Kunjungan::where('no_kunjungan', 'like', $prefix.'%')->count() + 1;

        return $prefix.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    private function statuses(): array
    {
        return [
            'Terdaftar',
            'Menunggu Pemeriksaan',
            'Sedang Diperiksa',
            'Selesai Diperiksa',
            'Menunggu Pembayaran',
            'Selesai',
        ];
    }
}
