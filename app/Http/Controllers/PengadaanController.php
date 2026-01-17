<?php

namespace App\Http\Controllers;

use App\Models\Lokasi;
use App\Models\Pengadaan;
use Illuminate\Http\Request;
use App\Models\Statuspengadaan;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use RealRashid\SweetAlert\Facades\Alert;

class PengadaanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $query = Pengadaan::leftJoinSub(
                            DB::table('statuspengadaans')
                                ->select('pengadaan_id', DB::raw('MAX(created_at) as latest_created_at'))
                                ->groupBy('pengadaan_id'),
                            'latest_status',
                            function ($join) {
                                $join->on('pengadaans.id', '=', 'latest_status.pengadaan_id');
                            }
                        )
                            ->leftJoin('statuspengadaans', function ($join) {
                                $join->on('latest_status.pengadaan_id', '=', 'statuspengadaans.pengadaan_id')
                                    ->on('latest_status.latest_created_at', '=', 'statuspengadaans.created_at');
                            })
                            ->select('pengadaans.*', 'statuspengadaans.status');

        $currentUser = Auth::user();
        
        // Filter berdasarkan role user
        if ($currentUser->roles !== 'admin') {
            // Jika user bukan admin, hanya tampilkan data milik mereka sendiri
            $query->where('pengadaans.user_id', $currentUser->id);
        }
        // Jika admin, tampilkan semua data

        $pengadaans = $query->orderBy('created_at', 'desc')->get();

        return view('pengadaan.index', [
            'users'      => $currentUser,
            'pengadaans' => $pengadaans
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pengadaan.create', [
            'users'   => Auth::user(),
            'lokasis' => Lokasi::all()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_pengadaan'    => 'required',
            'quantity'          => 'required|numeric',
            'deskripsi'         => 'required',
            'lokasi_id'         => 'required',  
        ]);

        $validated['user_id'] = auth()->user()->id;
        $validated['tanggal_pengajuan'] = now();
        $validated['status'] = 'pending';

        $pengadaan = Pengadaan::create($validated);

        $status = new Statuspengadaan;
        $status->status = 'pending';
        $status->pengadaan_id = $pengadaan->id;
        $status->save();
        
        Alert::success('Berhasil', 'Berhasil Mengajukan Pengadaan Barang');
        return redirect('/pengadaan');
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
            return redirect('/pengadaan');
        }

        return view('pengadaan.show', [
            'users'     => $currentUser,
            'pengadaan' => $pengadaan,
            'status'    => Statuspengadaan::find($id)
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Pengadaan $pengadaan)
    {
        $currentUser = Auth::user();

        // Cek otorisasi: hanya user pemilik atau admin yang bisa edit
        if ($currentUser->roles !== 'admin' && $pengadaan->user_id !== $currentUser->id) {
            Alert::error('Akses Ditolak', 'Anda tidak memiliki akses untuk mengedit data ini');
            return redirect('/pengadaan');
        }

        return view('pengadaan.edit', [
            'users'     => $currentUser,
            'pengadaan' => $pengadaan,
            'lokasis'   => Lokasi::all(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Pengadaan $pengadaan)
    {
        $currentUser = Auth::user();

        // Cek otorisasi: hanya user pemilik atau admin yang bisa update
        if ($currentUser->roles !== 'admin' && $pengadaan->user_id !== $currentUser->id) {
            Alert::error('Akses Ditolak', 'Anda tidak memiliki akses untuk mengubah data ini');
            return redirect('/pengadaan');
        }

        $rules = [
            'nama_pengadaan'    => 'required',
            'quantity'          => 'required|numeric',
            'deskripsi'         => 'required',
            'lokasi_id'         => 'required',
            // 'tanggal_pengajuan' => 'required',
        ];

        // jika tanggal_pengajuan tidak diubah, gunakan nilai yang ada pada pengadaan saat ini
        if ($request->tanggal_pengajuan == $pengadaan->tanggal_pengajuan) {
            $validated['tanggal_pengajuan'] = $pengadaan->tanggal_pengajuan;
        }

        $validated = $request->validate($rules);
        $validated['user_id'] = auth()->user()->id;

    

        $pengadaan->update($validated);

        
        Alert::success('Berhasil !', 'Berhasil Mengedit Pengajuan');
        return redirect('/pengadaan');   
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Pengadaan $pengadaan)
    {
        $currentUser = Auth::user();

        // Cek otorisasi: hanya user pemilik atau admin yang bisa delete
        if ($currentUser->roles !== 'admin' && $pengadaan->user_id !== $currentUser->id) {
            Alert::error('Akses Ditolak', 'Anda tidak memiliki akses untuk menghapus data ini');
            return redirect('/pengadaan');
        }

        $pengadaan->delete();
        Statuspengadaan::where('pengadaan_id', $pengadaan->id)->delete();

        Alert::success('Berhasil', 'Berhasil Menghapus Pengadaan');
        return redirect('/pengadaan');
    }
}
