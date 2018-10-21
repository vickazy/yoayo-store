<?php

use Illuminate\Http\Request;

/**
 * --------------------------------------------------------------------------
 * ROUTE HALAMAN PENGGUNA
 * --------------------------------------------------------------------------
 *
 */

/** Halaman Beranda Utama */

# METHOD GET
Route::get('/', 'Pengguna\BerandaController@index')->name('beranda');
Route::get('get_kategori', function() {

    $kategori = [];

    foreach(DB::table('tbl_kategori')->select('nama_kategori')->get() as $key => $value) {
        $kategori[] = $value->nama_kategori;
    }

    return response()->json($kategori);

})->name('get_kategori');
Route::get('get_data_counter', function() {

    $list = [
        'keranjang' => DB::table('tbl_keranjang')->where('id_pengguna', session('id_pengguna'))->count(),
        'pesanan'   => DB::table('tbl_pesanan')->where([['id_pengguna', '=', session('id_pengguna')], ['status_pesanan', '<=', 4]])->count(),
        'pembayaran'=> DB::table('tbl_pembayaran')->where('id_pengguna', session('id_pengguna'))->count(),
    ];

    return response()->json($list);

})->name('data_counter');


/** Halaman Autentikasi Pengguna */

# METHOD GET
Route::get('masuk', 'Pengguna\Autentikasi\LoginController@index')->name('login');
Route::get('daftar', 'Pengguna\Autentikasi\RegisterController@index')->name('register');
Route::get('lupa-password', 'Pengguna\Autentikasi\ResetPasswordController@lupa_password')->name('lupa_password');
Route::get('keluar', 'Pengguna\Autentikasi\LoginController@logout')->name('logout');

# METHOD POST
Route::post('masuk', 'Pengguna\Autentikasi\LoginController@login')->name('proses_login');
Route::post('daftar', 'Pengguna\Autentikasi\RegisterController@register')->name('proses_regis');



/** Halaman Akun Pengguna */

# METHOD GET
Route::get('info-akun', 'Pengguna\Akun\AkunController@index')->name('info_akun');



/** Halaman Keranjang */

# METHOD GET
Route::get('keranjang', 'Pengguna\Keranjang\KeranjangController@index')->name('keranjang');

# METHOD PUT
Route::put('keranjang/update/{id_barang}', 'Pengguna\Keranjang\KeranjangController@update')->name('update_keranjang');

# METHOD DELETE
Route::delete('keranjang/delete/{id_barang}', 'Pengguna\Keranjang\KeranjangController@delete')->name('delete_keranjang');


/** Halaman Keranjang */

# METHOD GET
Route::get('get_provinsi', function() {
    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => "https://api.rajaongkir.com/starter/province",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "UTF-8",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => [
            "key: 1a84ef0ff7cac9bb764f1087e64da8d3"
        ],
    ]);

    $result = curl_exec($curl);

    return response()->json($result);
});
Route::get('get_kota', function() {
    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => "https://api.rajaongkir.com/starter/city",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "UTF-8",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => [
            "key: 1a84ef0ff7cac9bb764f1087e64da8d3"
        ],
    ]);

    $result = curl_exec($curl);

    return response()->json($result);
});


/** Halaman Produk*/

# METHOD GET
Route::get('produk', 'Pengguna\Produk\ProdukController@index')->name('produk');
Route::get('produk/detail/{id_barang}', 'Pengguna\Produk\DetailProdukController@index')->name('detail_produk');

# METHOD POST
Route::post('produk/tambah-keranjang/{id_barang}', 'Pengguna\Produk\DetailProdukController@masukan_keranjang')->name('tambah_keranjang');




/**
 * --------------------------------------------------------------------------
 * ROUTE HALAMAN ADMIN
 * --------------------------------------------------------------------------
 *
 */
Route::group(['prefix' => 'admin'], function(){

    /** Halaman Beranda Utama */

    # METHOD GET
    Route::get('/', 'Admin\BerandaController@index')->name('beranda_admin');
    Route::get('sidebar_counter', function() {

        $table = ['barang', 'kategori', 'merk', 'pengguna', 'admin', 'pesanan', 'pembayaran', 'pengiriman'];

        $data = [];

        foreach($table as $key) {

            if($key == 'pengiriman') {

                $data[] = DB::table('tbl_pesanan')->select('id_pesanan')
                    ->where('status_pesanan', 3)->count();

            } else if ($key == 'pesanan') {

                $data[] = DB::table('tbl_pesanan')->select('id_pesanan')
                    ->whereBetween('status_pesanan', [1, 2])->count();

            } else if($key == 'pembayaran') {

                $data[] = DB::table('tbl_pembayaran')->select('id_pesanan')
                    ->where('selesai', 0)->count();

            } else {

                $data[] = DB::table('tbl_'.$key)->count();

            }
        }

        return response()->json($data);

    }); // AJAX



    /** Halaman Beranda Utama */

    # METHOD GET
    Route::get('profile/{id_admin}', 'Admin\ProfileController@index')->name('profile_admin');

    # METHOD PUT
    Route::put('profile/ganti_password/{id_admin}', 'Admin\ProfileController@ganti_password')->name('ganti_password_admin');



    /** Halaman Autentikasi */

    # METHOD GET
    Route::get('login', 'Admin\Autentikasi\LoginController@index')->name('login_admin');
    Route::get('logout', 'Admin\Autentikasi\LoginController@logout')->name('logout_admin');

    # METHOD POST
    Route::post('login', 'Admin\Autentikasi\LoginController@login')->name('proses_login_admin');



    /** Halaman Produk */

    # METHOD GET
    Route::get('produk', 'Admin\Produk\ProdukController@index')->name('list_produk');
    Route::get('get_produk/{id_barang}', function($id_barang) {

        $data = DB::table('tbl_barang')->where('id_barang', $id_barang)->first();

        return response()->json($data);

    }); // AJAX

    # METHOD POST
    Route::post('produk', 'Admin\Produk\ProdukController@tambah_produk')->name('tambah_produk');

    # METHOD PUT
    Route::put('edit_produk/{id_barang}', 'Admin\Produk\ProdukController@edit_produk');

    # METHOD DELETE
    Route::delete('hapus_produk/{id_barang}', 'Admin\Produk\ProdukController@hapus_produk');



    /** Halaman Kategori */

    # METHOD GET
    Route::get('kategori', 'Admin\Produk\KategoriController@index')->name('kategori_produk');
    Route::get('check_kategori/{nama_kategori}', function($nama_kategori){

        $nama_kategori = str_replace('%20', ' ', $nama_kategori);

        $data = DB::table('tbl_kategori')->where('nama_kategori', $nama_kategori)->exists();

        return response()->json($data);

    }); // AJAX

    # METHOD POST
    Route::post('kategori', 'Admin\Produk\KategoriController@tambah_kategori')->name('tambah_kategori');

    # METHOD PUT
    Route::put('edit_kategori/{id_kategori}', 'Admin\Produk\KategoriController@edit_kategori');

    # METHOD DELETE
    Route::delete('hapus_kategori/{id_kategori}', 'Admin\Produk\KategoriController@hapus_kategori');



    /** Halaman Merk */

    # METHOD GET
    Route::get('merk', 'Admin\Produk\MerkController@index')->name('merk_produk');
    Route::get('check_merk', 'Admin\Produk\MerkController@check_merk'); // AJAX

    # METHOD POST
    Route::post('tambah_merk', 'Admin\Produk\MerkController@tambah_merk')->name('tambah_merk');

    # METHOD PUT
    Route::put('edit_merk/{id_merk}', 'Admin\Produk\MerkController@edit_merk');

    # METHOD DELETE
    Route::delete('hapus_merk/{id_merk}', 'Admin\Produk\MerkController@hapus_merk');



    /** Halaman Superadmin : Admin */

    # METHOD GET
    Route::get('superadmin/admin', 'Admin\Superadmin\AdminController@index')->name('superadmin_admin');
    Route::get('superadmin/blokir_admin/{id_admin}', 'Admin\Superadmin\AdminController@blokir_admin')->name('blokir');
    Route::get('superadmin/get_admin/{id_admin}', 'Admin\Superadmin\AdminController@get_admin'); // AJAX

    # METHOD POST
    Route::post('superadmin/tambah_admin', 'Admin\Superadmin\AdminController@tambah_admin')->name('tambah_admin');

    # METHOD PUT
    Route::put('superadmin/edit_admin/}id_admin}', 'Admin\Superadmin\AdminController@edit_admin');
    Route::put('superadmin/ubah_status_admin/{id_admin}', 'Admin\Superadmin\AdminController@ubah_status_admin');

    # METHOD DELETE
    Route::delete('superadmin/hapus_admin/{id_admin}', 'Admin\Superadmin\AdminController@hapus_admin');



    /** Halaman Superadmin : Pengguna */

    # METHOD GET
    Route::get('superadmin/pengguna', 'Admin\Superadmin\PenggunaController@index')->name('superadmin_pengguna');
    Route::get('superadmin/get_pengguna/{id_pengguna}', 'Admin\Superadmin\PenggunaController@get_pengguna'); // AJAX

    # METHOD DELETE
    Route::delete('superadmin/hapus_pengguna/{id_pengguna}', 'Admin\Superadmin\PenggunaController@hapus_pengguna');



    /** Halaman Transaksi : Pembayaran */

    # METHOD GET
    Route::get('transaksi/pembayaran', 'Admin\Transaksi\PembayaranController@index')->name('pembayaran_admin');
    Route::get('transaksi/get_pembayaran/{id_pesanan}', 'Admin\Transaksi\PembayaranController@get_pembayaran'); // AJAX

    # METHOD PUT
    Route::put('transaksi/pembayaran/status/{id_pesanan}', 'Admin\Transaksi\PembayaranController@rubah_status')->name('rubah_status_pembayaran');



    /** Halaman Transaksi : Pesanan */

    # METHOD GET
    Route::get('transaksi/pesanan', 'Admin\Transaksi\PesananController@index')->name('pesanan_admin');
    Route::get('transaksi/pesanan/detail/{id_pesanan}', 'Admin\Transaksi\PesananController@detail_pesanan')->name('detail_pesanan_admin');
    Route::get('transaksi/get_penerima/{id_pesanan}', 'Admin\Transaksi\PesananController@get_info_penerima'); // AJAX

    # METHOD PUT
    Route::put('transaksi/proses_pesanan/{id_pesanan}', 'Admin\Transaksi\PesananController@proses_pesanan');
    Route::put('transaksi/kirim_pesanan/{id_pesanan}', 'Admin\Transaksi\PesananController@kirim_pesanan');
    Route::put('transaksi/batalkan_pesanan/{id_pesanan}', 'Admin\Transaksi\PesananController@batalkan_pesanan');
    Route::put('transaksi/edit_pesanan/{id_pesanan}', 'Admin\Transaksi\PesananController@edit_pesanan');

    # METHOD DELETE
    Route::delete('transaksi/hapus_pesanan/{id_pesanan}', 'Admin\Transaksi\PesananController@hapus_pesanan');



    /** Halaman Transaksi : Pengiriman */

    # METHOD GET
    Route::get('transaksi/pengiriman', 'Admin\Transaksi\PengirimanController@index')->name('pengiriman_admin');

    # METHOD PUT
    Route::put('transaksi/selesai/{id_pesanan}', 'Admin\Transaksi\PengirimanController@selesai');


});

/**
 * --------------------------------------------------------------------------
 * Testing Unit Route
 * --------------------------------------------------------------------------
 *
 */

 # METHOD GET
// Route::get('test', 'Test\TestingController@index');
Route::get('test', function(Request $request) {
    return view('test');
});


# METHOD POST
Route::post('test', 'Test\TestingController@test')->name('test_form');
Route::get('send', 'Pengguna\EmailController@lupa_password');
