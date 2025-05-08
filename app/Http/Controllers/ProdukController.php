<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Produk;

class ProdukController extends Controller
{
    public function detail($id) 
    { 
        $fotoProdukTambahan = FotoProduk::where('produk_id', $id)->get(); 
        $detail = Produk::findOrFail($id); 
        $kategori = Kategori::orderBy('nama_kategori', 'desc')->get(); 
        return view('v_produk.detail', [ 
            judul => 'Detail Produk', 
            'kategori' => $kategori, 
            'row' => $detail, 
            'fotoProdukTambahan' => $fotoProdukTambahan 
        ]); 
    }

    public function produkKategori($id) 
    { 
        $kategori = Kategori::orderBy('nama_kategori', 'desc')->get(); 
        $produk = Produk::where('kategori_id', $id)->where('status', 1)->orderBy('updated_at', 'desc')->paginate(6); 
        return view('v_produk.produkkategori', [ 
            'judul' => 'Filter Kategori', 
            'kategori' => $kategori, 
            'produk' => $produk, 
        ]); 
    }

    public function produkAll() 
    { 
        $kategori = Kategori::orderBy('nama_kategori', 'desc')->get(); 
        $produk = Produk::where('status', 1)->orderBy('updated_at', 'desc')->paginate(6); 
        return view('v_produk.index', [ 
            'judul' => 'Semua Produk', 
            'kategori' => $kategori, 
            'produk' => $produk, 
        ]); 
    }
}
