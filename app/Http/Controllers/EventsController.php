<?php

// Menentukan namespace tempat controller ini berada

namespace App\Http\Controllers;

// Mengimpor model Food untuk berinteraksi dengan tabel foods
use App\Models\events;
// Mengimpor Request untuk menangani HTTP request dan input data form
use Illuminate\Http\Request;
// Mengimpor Storage facade untuk mengelola upload dan penghapusan file di sistem penyimpanan
use Illuminate\Support\Facades\Storage;

// Deklarasi kelas controller FoodController yang mewarisi Controller dasar
class EventsController extends Controller
{
    // Method untuk menampilkan daftar makanan ke halaman admin
    public function index()
    {
        // Mengambil data makanan yang diurutkan dari yang terbaru dan dibagi per 10 data per halaman (paginasi)
        $events = events::latest()->paginate(10);

        // Mengirimkan data makanan ke view admin/foods/index.blade.php
        return view('admin.events.index', compact('events'));
    }

    // Method untuk menampilkan form tambah makanan baru
    public function create()
    {
        // Merender view form penambahan makanan admin/foods/create.blade.php
        return view('admin.events.create');
    }

    // Method untuk menyimpan data makanan baru ke database
    public function store(Request $request)
    {
        // Melakukan validasi input formulir request
        $request->validate([
            // Nama wajib diisi, string, maksimal 255 karakter
            'title' => 'required|string|max:255',
            // Kategori wajib diisi dan harus berupa salah satu dari Makanan, Minuman, atau Cemilan
            'category' => 'required|in:Musik,Festival,Seminar',
            // Harga wajib diisi angka dengan nilai minimal 0
            'ticket_price' => 'required|numeric|min:0',
            // Deskripsi wajib berupa teks string
            'description' => 'required|string',
            // Gambar bersifat opsional, harus file gambar jpg/jpeg/png maksimal 2MB
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // Inisialisasi variabel penampung path gambar dengan nilai default null
        $imagePath = null;
        // Mengecek apakah file gambar diunggah oleh pengguna
        if ($request->hasFile('image')) {
            // Mengunggah gambar ke folder 'food_images' pada disk penyimpanan publik
            $imagePath = $request->file('image')->store('events_images', 'public');
        }

        // Menyimpan record data makanan baru ke tabel foods
        events::create([
            // Mengambil nilai input nama makanan
            'title' => $request->title,
            // Mengambil nilai input kategori
            'category' => $request->category,
            // Mengambil nilai input harga
            'ticket_price' => $request->ticket_price,
            // Mengambil nilai input deskripsi
            'description' => $request->description,
            // Menyimpan path gambar yang berhasil diupload
            'image' => $imagePath,
        ]);

        // Mengarahkan kembali ke halaman index makanan dengan pesan sukses
        return redirect()->route('events.index')->with('success', 'Data berhasil ditambahkan.');
    }

    // Method untuk menampilkan formulir edit makanan berdasarkan model binding Food
    public function edit(events $event)
    {
        // Menampilkan view edit makanan dengan mengirimkan data instance $food
        return view('admin.events.edit', compact('event'));
    }

    // Method untuk memperbarui data makanan yang sudah ada di database
    public function update(Request $request, events $event)
    {
        // Validasi input
        $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|in:Musik,Festival,Seminar',
            'ticket_price' => 'required|numeric|min:0',
            'description' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);
        $imagePath = $event->image;
        // Cek jika ada gambar baru
        if ($request->hasFile('image')) {
            if ($event->image && Storage::disk('public')->exists($event->image)) {
                Storage::disk('public')->delete($event->image);
            }
            $imagePath = $request->file('image')->store('events_images', 'public');
        }
        // Update database
        $event->update([
            'title' => $request->title,
            'category' => $request->category,
            'ticket_price' => $request->ticket_price,
            'description' => $request->description,
            'image' => $imagePath,
        ]);

        return redirect()->route('events.index')->with('success', 'Data berhasil diperbarui.');
    }

    // Method untuk menghapus data event dari database dan file sistem
    public function destroy(events $event)
    {
        // Mengecek apakah event memiliki gambar yang tersimpan pada penyimpanan
        if ($event->image && Storage::disk('public')->exists($event->image)) {
            // Menghapus file gambar dari disk publik
            Storage::disk('public')->delete($event->image);
        }

        // Menghapus record event dari tabel database
        $event->delete();

        // Mengalihkan ke halaman daftar event dengan pesan sukses
        return redirect()->route('events.index')->with('success', 'Data event berhasil dihapus.');
    }
}
