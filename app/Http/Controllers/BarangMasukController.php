<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BarangMasuk;
use App\Models\Barang;
use App\Models\Kategori;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class BarangMasukController extends Controller
{
    public function index(Request $request)
    {
        $keyword = $request->input('keyword');
    
        // Query untuk mencari barang masuk berdasarkan keyword
        $rsetBarangMasuk = BarangMasuk::with('barang')
            ->whereHas('barang', function ($query) use ($keyword) {
                $query->where('merk', 'LIKE', "%$keyword%")
                      ->orWhere('seri', 'LIKE', "%$keyword%")
                      ->orWhere('spesifikasi', 'LIKE', "%$keyword%");
            })
            ->orWhere('tgl_masuk', 'LIKE', "%$keyword%")
            ->orWhere('qty_masuk', 'LIKE', "%$keyword%")
            ->paginate(10);
    
        return view('barangmasuk.index', compact('rsetBarangMasuk'))
            ->with('i', (request()->input('page', 1) - 1) * 10);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //$abarang = Barang::all();
        //return view('barangmasuk.create',compact('abarang'));

        $abarang = Barang::all(); // Mengambil data barang
        $today = date('Y-m-d'); // Mendapatkan tanggal hari ini dalam format YYYY-MM-DD
        return view('barangmasuk.create', compact('abarang', 'today'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //return $request;
        //validate form
        $request->validate([
            'tgl_masuk'          => 'required',
            'qty_masuk'          => 'required',
            'barang_id'          => 'required',

        ]);
        // BarangMasuk::create([
        //     'tgl_masuk'             => $request->tgl_masuk,
        //     'qty_masuk'             => $request->qty_masuk,
        //     'barang_id'             => $request->barang_id,
        // ]);

        try {
            DB::beginTransaction(); // <= Mulai transaksi
        
            // Simpan data barang
            $barangmasuk = new BarangMasuk();
            $barangmasuk->tgl_masuk = $request->tgl_masuk;
            $barangmasuk->qty_masuk = $request->qty_masuk;
            $barangmasuk->kategori_id = $request->kategori_id;
            $barangmasuk->save();
        
            DB::commit(); // <= Commit perubahan
        } catch (\Exception $e) {
            report($e);
        
            DB::rollBack(); // <= Rollback jika terjadi kesalahan
            // return redirect()->route('barang.index')->with(['error' => 'gagal menyimpan data.']);
        }

        return redirect()->route('barangmasuk.index')->with(['success' => 'Data Berhasil Disimpan!']);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $rsetBarang = BarangMasuk::find($id);
        return view('barangmasuk.show', compact('rsetBarang'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
    $abarang = Barang::all();
    $rsetBarang = BarangMasuk::find($id);
    $selectedBarang = Barang::find($rsetBarang->barang_id);
    return view('barangmasuk.edit', compact('rsetBarang', 'abarang', 'selectedBarang'));
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'tgl_masuk'          => 'required',
            'qty_masuk'          => 'required',
            'barang_id'          => 'required',
        ]);

        $rsetBarang = BarangMasuk::find($id);

            //update post without image
            $rsetBarang->update([
                'tgl_masuk'             => $request->tgl_masuk,
                'qty_masuk'             => $request->qty_masuk,
                'barang_id'             => $request->barang_id,
            ]);

        return redirect()->route('barangmasuk.index')->with(['success' => 'Data Berhasil Diubah!']);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $rsetBarang = BarangMasuk::find($id);
        $rsetBarang->delete();
        return redirect()->route('barangmasuk.index')->with(['success' => 'Data Berhasil Dihapus!']);
    }
}