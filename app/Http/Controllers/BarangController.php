<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Lokasi;
use App\Models\Satuan;
use App\Models\Kategori;
use Barryvdh\DomPDF\Facade\PDF;
use Endroid\QrCode\QrCode;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;
use Dompdf\Dompdf;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use RealRashid\SweetAlert\Facades\Alert;

class BarangController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $userLogin = auth()->user()->roles;

        if($userLogin == 'user'){
            $barangs = Barang::where('user_id', auth()->user()->id)->get();
        } else {
            $barangs = Barang::all();
        }
        
        return view('barang.index', [
            'users'   => Auth::user(),
            'barangs' => $barangs
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        return view('barang.create', [
            'users'     => Auth::user(),
            'kategoris' => Kategori::all(),
            'lokasis'   => Lokasi::all(),
            'satuans'   => Satuan::all(),
        ]); 
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama'              => 'required',
            'deskripsi'         => 'required',
            'gambar'            => 'required|mimes:jpeg,png,jpg',
            'harga'             => 'required|numeric',
            'tahun_pembelian'   => 'required|numeric|digits:4|min:1900',
            'kategori_id'       => 'required',
            'lokasi_id'         => 'required',
            'satuan_id'         => 'required'
        ]);

        if ($request->hasFile('gambar')) {
            $file = $request->file('gambar');
            $fileName = $file->getClientOriginalName();
            Storage::disk('public')->put('gambar-barang/'.$fileName, file_get_contents($file));
            $validated['gambar'] = 'gambar-barang/'.$fileName;
        }
              
        // Generate unique code based on kategori only
        $kategoriId = $validated['kategori_id'];
        $tahunPembelian = (int)$validated['tahun_pembelian'];

        // Get the category code
        $kategori = Kategori::find($kategoriId);
        if (!$kategori || !$kategori->kode_kategori) {
            Alert::error('Error', 'Kategori tidak ditemukan atau tidak memiliki kode kategori');
            return redirect()->back();
        }

        $kodeKategori = $kategori->kode_kategori;

        // Cari data terakhir berdasarkan kategori SAJA (tidak peduli tahun)
        $lastBarang = Barang::where('kategori_id', $kategoriId)
                            ->orderBy('id', 'desc')
                            ->first();

        if (!$lastBarang) {
            // Jika belum ada data untuk kategori ini, mulai dari 1
            $nomorUrut = 1;
        } else {
            // Ambil kode terakhir (PWM-E-003-2025), pecah berdasarkan tanda strip (-)
            $segments = explode('-', $lastBarang->kode_barang);
            
            // Ambil angka urut di posisi ketiga (index 2), lalu tambah 1
            $nomorUrut = intval($segments[2]) + 1;
        }

        // Susun formatnya: PWM - {KODE_KATEGORI} - (nomor urut 3 digit) - (tahun pembelian)
        $validated['kode_barang'] = 'PWM-' . $kodeKategori . '-' . str_pad($nomorUrut, 3, '0', STR_PAD_LEFT) . '-' . $tahunPembelian;

        $qrCode = new QrCode($validated['kode_barang']);
        $writer = new PngWriter();
        $result = $writer->write($qrCode);
        $image = $result->getDataUri();
        Storage::disk('public')->put('qrcode-barang/'. $validated['kode_barang'] . '.png', file_get_contents($image));

        $validated['tanggal'] = now();
        $validated['user_id'] = auth()->user()->id;

        Barang::create($validated);
        Alert::success('Berhasil', 'Berhasil Menambahkan Barang !');
        return redirect('/barang');
    }

    /**
     * Display the specified resource.
     */
    public function show(Barang $barang)
    {
        $qrCode = new QrCode($barang->kode_barang);
        return view('barang.show', [
            'users'     => Auth::user(),
            'barang'    => $barang,
            'qrCode'    => $qrCode
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Barang $barang)
    {
        return view('barang.edit', [
            'users'     => Auth::user(),
            'barang'    => $barang,
            'kategoris' => Kategori::all(),
            'lokasis'   => Lokasi::all(),
            'satuans'   => Satuan::all(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Barang $barang)
    {
        $rules = [
            'nama'              => 'required',
            'deskripsi'         => 'required',
            'gambar'            => 'image|file',
            'harga'             => 'required|numeric',
            'tahun_pembelian'   => 'required|numeric|digits:4|min:1900',
            'kategori_id'       => 'required',
            'lokasi_id'         => 'required',
            'satuan_id'         => 'required'
        ];

        $validated = $request->validate($rules);

        if($request->hasFile('gambar')){
            if($barang->gambar){
                Storage::delete($barang->gambar);
            }
            $file = $request->file('gambar');
            $fileName = $file->getClientOriginalName();
            Storage::disk('public')->put('gambar-barang/' .$fileName, file_get_contents($file));
            $validated['gambar'] = 'gambar-barang/' .$fileName;
        }

        // Regenerate code if kategori berubah
        if ($barang->kategori_id !== (int)$validated['kategori_id']) {
            $kategoriId = $validated['kategori_id'];
            $tahunPembelian = (int)$validated['tahun_pembelian'];

            // Get the category code
            $kategori = Kategori::find($kategoriId);
            if (!$kategori || !$kategori->kode_kategori) {
                Alert::error('Error', 'Kategori tidak ditemukan atau tidak memiliki kode kategori');
                return redirect()->back();
            }

            $kodeKategori = $kategori->kode_kategori;

            // Cari data terakhir berdasarkan kategori SAJA (tidak peduli tahun)
            // Exclude current item
            $lastBarang = Barang::where('kategori_id', $kategoriId)
                                ->where('id', '!=', $barang->id)
                                ->orderBy('id', 'desc')
                                ->first();

            if (!$lastBarang) {
                // Jika belum ada data untuk kategori ini, mulai dari 1
                $nomorUrut = 1;
            } else {
                // Ambil kode terakhir (PWM-E-003-2025), pecah berdasarkan tanda strip (-)
                $segments = explode('-', $lastBarang->kode_barang);
                
                // Ambil angka urut di posisi ketiga (index 2), lalu tambah 1
                $nomorUrut = intval($segments[2]) + 1;
            }

            // Susun formatnya: PWM - {KODE_KATEGORI} - (nomor urut 3 digit) - (tahun pembelian)
            $validated['kode_barang'] = 'PWM-' . $kodeKategori . '-' . str_pad($nomorUrut, 3, '0', STR_PAD_LEFT) . '-' . $tahunPembelian;
        }

        $validated['user_id'] = auth()->user()->id;

        Barang::where('id', $barang->id)
            ->update($validated);
        
        Alert::success('Berhasil !', 'Berhasil Mengedit Barang');
        return redirect('/barang');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Barang $barang)
    {
        $barang->delete($barang->id);
        Alert::success('Berhasil', 'Berhasil Menghapus Barang');
        return redirect('/barang');
    }

    public function cetakLabel(Barang $id)
    { 
        $barang = Barang::find($id->id);

        $qrCodePath = storage_path('app/public/qrcode-barang/' . $barang->kode_barang . '.png');
        $logoInstansiPath = public_path('loginassets/img/backgrounds/logo-muhammadiyah.png');

        $qrCode = base64_encode(file_get_contents($qrCodePath));
        $logoInstansi = base64_encode(file_get_contents($logoInstansiPath));

        $pdf = PDF::loadView('barang.label', [
            'users'         => Auth::user(),
            'barang'        => $barang,
            'qrCode'        => $qrCode,
            'logoInstansi'  => $logoInstansi
        ]);

        return $pdf->stream('label.pdf');
        $customPaper = [0, 0, 283.46, 141.73];
    }
}
