<?php

// Menentukan namespace dari controller ini di bawah direktori App\Http\Controllers
namespace App\Http\Controllers;

// Mengimpor model Food untuk mengambil data menu makanan
use App\Models\Events;
// Mengimpor model Order untuk menyimpan dan memanipulasi data pesanan
use App\Models\orders;
// Mengimpor model OrderDetail untuk menyimpan rincian setiap item yang dipesan
use App\Models\order_details;
// Mengimpor Request untuk menangani HTTP request dan data formulir pemesanan
use Illuminate\Http\Request;
// Mengimpor DB facade untuk database transaction agar integritas data terjamin
use Illuminate\Support\Facades\DB;

// Deklarasi kelas controller OrderController untuk mengelola alur pemesanan
class OrdersController extends Controller
{
    // Method untuk menampilkan halaman katalog menu restoran bagi pelanggan
    public function index()
    {
        // Mengambil seluruh data menu makanan dari tabel foods
        $events = Events::all();
        // Merender tampilan customer/index.blade.php dengan menyertakan data makanan ($foods)
        return view('customer.index', compact('events'));
    }

    // Method untuk memproses checkout dan penyimpanan pesanan baru dari pelanggan
    public function store(Request $request)
    {
        // Validasi input data dari form pemesanan pelanggan
        $request->validate([
            // Nama pelanggan wajib diisi, berupa string, maks 255 karakter
            'customer_name' => 'required|string|max:255',
            // Nomor meja wajib diisi, berupa angka integer minimal 1
            'customer_email'  => 'required|string|min:1',
            // Array items wajib ada
            'items'         => 'required|array',
            // Setiap item pada array bernilai integer dan minimal 0
            'items.*'       => 'nullable|integer|min:0',
        ]);

        // Menyaring hanya menu-menu yang memiliki kuantitas porsi lebih besar dari 0
        $orderedItems = array_filter($request->items, fn ($qty) => $qty > 0);

        // Jika tidak ada satu pun makanan yang dipesan dengan jumlah > 0
        if (empty($orderedItems)) {
            // Mengembalikan pelanggan ke halaman sebelumnya dengan pesan error
            return back()->with('error', 'Pilih minimal satu Events!');
        }

        // Memulai database transaction untuk memastikan seluruh query order dan order details berhasil bersamaan
        DB::beginTransaction();
        try {
            // Membuat data order induk terlebih dahulu dengan total_price awal 0 dan status 'pending'
            $order = Orders::create([
                // Menyimpan nama pelanggan
                'customer_name' => $request->customer_name,
                // Menyimpan nomor meja pelanggan
                'customer_email'  => $request->customer_email,
                // Menginisialisasi total harga dengan 0
                'total_price'   => 0,
                // Menetapkan status awal pesanan menjadi 'pending'
                'status'        => 'pending',
            ]);

            // Inisialisasi variabel untuk menghitung akumulasi total harga keseluruhan pesanan
            $totalPrice = 0;

            // Melakukan perulangan untuk setiap item menu yang dipesan (ID makanan => kuantitas)
            foreach ($orderedItems as $eventsId => $quantity) {
                // Mencari data makanan berdasarkan foodId atau lempar error jika tidak ditemukan
                $events = Events::findOrFail($eventsId);
                // Menghitung subtotal untuk item menu ini (harga menu dikali jumlah pesanan)
                $subtotal = $events->ticket_price * $quantity;
                // Menambahkan subtotal ke total keseluruhan harga
                $totalPrice += $subtotal;

                // Membuat record rincian pesanan baru di tabel orderdetails
                order_details::create([
                    // Mengaitkan rincian pesanan dengan ID order induk yang baru dibuat
                    'order_id' => $order->id,
                    // Menyimpan ID makanan yang dipesan
                    'event_id'  => $events->id,
                    // Menyimpan jumlah porsi pesanan
                    'quantity' => $quantity,
                    // Menyimpan subtotal biaya item tersebut
                    'subtotal' => $subtotal,
                ]);
            }

            // Memperbarui total harga riil pada order induk setelah selesai menghitung seluruh item
            $order->update(['total_price' => $totalPrice]);
            // Melakukan commit transaksi database karena semua proses berjalan tanpa error
            DB::commit();

            // Mengarahkan kembali ke katalog pelanggan dengan pesan sukses beserta nomor mejanya
            return redirect()->route('customer.index')->with('success', 'Pesanan berhasil dibuat!' );
        } catch (\Exception $e) {
            // Membatalkan seluruh query database jika terjadi kesalahan di tengah jalan
            DB::rollBack();
            // Mengarahkan kembali ke halaman pemesanan dengan pesan error
            return back()->with('error', 'Gagal memproses pesanan: ' . $e->getMessage());
        }
    }

    public function adminDashboard()
    {
        // Ganti Order:: menjadi orders:: dan sesuaikan relasi untuk dashboard
        $orders = orders::with('orderDetails.event')->latest()->get();
        return view('dashboard', compact('orders'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate(['status' => 'required|string']);
        
        // Ganti Order:: menjadi orders::
        $order = orders::findOrFail($id);
        $order->update(['status' => $request->status]);

        return back()->with('success', 'Status pesanan #' . $order->id . ' berhasil diperbarui!');
    }
}