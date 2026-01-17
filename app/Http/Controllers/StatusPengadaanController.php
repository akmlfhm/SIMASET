<?php

namespace App\Http\Controllers;

use Dompdf\Dompdf;
use App\Models\Pengadaan;
use Illuminate\Http\Request;
use App\Models\Statuspengadaan;
use Barryvdh\DomPDF\Facade\PDF;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use RealRashid\SweetAlert\Facades\Alert;

class StatusPengadaanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $currentUser = Auth::user();
        
        $query = DB::table('pengadaans')
                        ->leftJoin('statuspengadaans', 'pengadaans.id', '=', 'statuspengadaans.pengadaan_id')
                        ->select('pengadaans.*', 'statuspengadaans.status')
                        ->whereIn('pengadaans.id', function ($subquery) {
                            $subquery->select(DB::raw('MAX(id)'))
                                ->from('pengadaans')
                                ->groupBy('nama_pengadaan');
                        });

        // Filter berdasarkan role user
        if ($currentUser->roles !== 'admin') {
            // Jika user bukan admin, hanya tampilkan data milik mereka sendiri
            $query->where('pengadaans.user_id', $currentUser->id);
        }
        // Jika admin, tampilkan semua data

        $permintaans = $query->orderBy('created_at', 'desc')->get();

        return view('permintaan.index', [
            'users'       => $currentUser,
            'permintaans' => $permintaans
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request,)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $pengadaan = Pengadaan::find($id);
        $currentUser = Auth::user();

        // Cek otorisasi: hanya user pemilik atau admin yang bisa melihat
        if ($currentUser->roles !== 'admin' && $pengadaan->user_id !== $currentUser->id) {
            Alert::error('Akses Ditolak', 'Anda tidak memiliki akses untuk melihat data ini');
            return redirect('/permintaan');
        }

        return view('permintaan.show', [
            'users'     => $currentUser,
            'pengadaan' => $pengadaan,
            'status'    => Statuspengadaan::find($id)
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Statuspengadaan $statuspengadaan, $id)
    {
        $statusData = Statuspengadaan::findOrFail($id);
        $pengadaan = Pengadaan::find($statusData->pengadaan_id);
        $currentUser = Auth::user();

        // Cek otorisasi: hanya user pemilik atau admin yang bisa edit
        if ($currentUser->roles !== 'admin' && $pengadaan->user_id !== $currentUser->id) {
            Alert::error('Akses Ditolak', 'Anda tidak memiliki akses untuk mengedit data ini');
            return redirect('/permintaan');
        }

        return view('permintaan.edit', [
            'users'             => $currentUser,
            'status'            => $statusData,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $statusData = Statuspengadaan::findOrFail($id);
        $pengadaan = Pengadaan::find($statusData->pengadaan_id);
        $currentUser = Auth::user();

        // Cek otorisasi: hanya user pemilik atau admin yang bisa update
        if ($currentUser->roles !== 'admin' && $pengadaan->user_id !== $currentUser->id) {
            Alert::error('Akses Ditolak', 'Anda tidak memiliki akses untuk mengubah data ini');
            return redirect('/permintaan');
        }

        Statuspengadaan::where('id', $id)
        ->update([
            'catatan'  => $request->catatan,
            'user_id' => Auth::id()
        ]);
        Alert::success('Berhasil !', 'Berhasil Mengirim Catatan');
        return redirect('/permintaan');  
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Statuspengadaan $statuspengadaan)
    {
        //
    }

    public function setPersetujuan($id)
    {
        // Validasi role admin
        if(auth()->user()->roles !== 'admin'){
            abort(403, 'Hanya Admin yang dapat menyetujui pengadaan.');
        }

        Statuspengadaan::where('id', $id)
            ->update([
                'status'  => 'disetujui',
                'user_id' => Auth::id()
            ]);
        Alert::success('Berhasil', 'Pengadaan Barang Disetujui');
        return redirect()->back()->with('success', 'Persetujuan berhasil disimpan.');
    }

    public function setPenolakan($id)
    {
        // Validasi role admin
        if(auth()->user()->roles !== 'admin'){
            abort(403, 'Hanya Admin yang dapat menolak pengadaan.');
        }

        Statuspengadaan::where('id', $id)
            ->update([
                'status' => 'ditolak',
                'user_id' => Auth::id()
            ]);

        Alert::success('Berhasil', 'Pengadaan Barang Ditolak');
        return redirect()->back()->with('success', 'Penolakan berhasil disimpan.');
    }

    public function cetakPengadaanBarang($id)
    {
        $pengadaan = Pengadaan::find($id);
        $currentUser = Auth::user();

        // Cek otorisasi: hanya user pemilik atau admin yang bisa print
        if ($currentUser->roles !== 'admin' && $pengadaan->user_id !== $currentUser->id) {
            Alert::error('Akses Ditolak', 'Anda tidak memiliki akses untuk mencetak data ini');
            return redirect('/permintaan');
        }

        $logoInstansiPath = storage_path('app/public/logo-instansi/logo.png');
        $logoInstansi = base64_encode(file_get_contents($logoInstansiPath));

        $pdf = new Dompdf();
        $pdf = PDF::loadView('permintaan.laporan-pengadaan', [
            'pengadaan'         => $pengadaan,
            'status'            => Statuspengadaan::find($id),
            'logoInstansi'      => $logoInstansi,
        ]);

        return $pdf->download('laporan-pengadaan.pdf');
    }
}
