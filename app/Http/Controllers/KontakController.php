<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contact;

use Illuminate\Support\Facades\Schema;

class KontakController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        
        $parentFitur = \App\Models\Fitur::where('nama', 'Kelola Kontak')->first();
        $sub_menus = $parentFitur ? \App\Models\Fitur::where('parent_id', $parentFitur->id)->orderBy('id')->get() : collect();

        $hasPelanggan = false;
        $hasSupplier = false;

        foreach($sub_menus as $sm) {
            if ($sm->nama == 'Pelanggan' && $user->hasFeature($sm->id)) $hasPelanggan = true;
            if ($sm->nama == 'Supplier' && $user->hasFeature($sm->id)) $hasSupplier = true;
        }

        $active_tab = $request->query('tab', 'customer');
        if ($active_tab == 'customer' && !$hasPelanggan) $active_tab = '';
        if ($active_tab == 'supplier' && !$hasSupplier) $active_tab = '';

        if (!$active_tab) {
            if ($hasPelanggan) $active_tab = 'customer';
            elseif ($hasSupplier) $active_tab = 'supplier';
        }

        $sort = $request->get('sort', 'terbaru');
        $order = $sort == 'terlama' ? 'asc' : 'desc';
        
        // Cek apakah kolom created_at ada, jika tidak gunakan uuid sebagai fallback
        $sortBy = Schema::hasColumn('contacts', 'created_at') ? 'created_at' : 'uuid';

        $query = Contact::where('tipe', 'customer');
        
        // Menghitung transaksi dan mengambil username otomatis dengan Super Normalisasi (highly optimized SQL, no regexp table scans)
        $query->select('contacts.*')
            ->selectSub(function ($q) {
                $q->from('payment_orders')
                  ->whereIn('payment_status', ['paid', 'settlement', 'success', 'capture', 'pending'])
                  ->where(function($sub) {
                      $sub->whereRaw('CAST(payment_orders.user_id AS varchar) = CAST(contacts.user_id AS varchar)')
                          ->orWhereColumn('payment_orders.recipient_phone', 'contacts.no_hp')
                          ->orWhereRaw('LOWER(TRIM(payment_orders.recipient_name)) = LOWER(TRIM(contacts.nama))');
                  })
                  ->selectRaw('count(*)');
            }, 'total_transaksi')
            ->selectSub(function ($q) {
                $hasUserId = Schema::hasColumn('contacts', 'user_id');
                $q->from('users')
                  ->where(function($sub) use ($hasUserId) {
                      if ($hasUserId) {
                          $sub->whereRaw('CAST(users.uuid AS varchar) = CAST(contacts.user_id AS varchar)');
                      }
                      $sub->orWhereColumn('users.no_hp', 'contacts.no_hp')
                          ->orWhereRaw('LOWER(TRIM(users.username)) = LOWER(TRIM(contacts.nama))');
                  })
                  ->select('username')
                  ->limit(1);
            }, 'matching_username')
            ->selectSub(function ($q) {
                $hasUserId = Schema::hasColumn('contacts', 'user_id');
                $q->from('users')
                  ->where(function($sub) use ($hasUserId) {
                      if ($hasUserId) {
                          $sub->whereRaw('CAST(users.uuid AS varchar) = CAST(contacts.user_id AS varchar)');
                      }
                      $sub->orWhereColumn('users.no_hp', 'contacts.no_hp')
                          ->orWhereRaw('LOWER(TRIM(users.username)) = LOWER(TRIM(contacts.nama))');
                  })
                  ->select('email')
                  ->limit(1);
            }, 'matching_email');

        // Eager load only lightweight user relation (no heavy paymentOrders.items.product nested graphs)
        if (Schema::hasColumn('contacts', 'user_id')) {
            $query->with(['user']);
        }
        
        $pelanggan = $query->orderBy($sortBy, $order)->paginate(10, ['*'], 'pelanggan_page');
        $supplier = Contact::where('tipe', 'supplier');
        if (Schema::hasColumn('contacts', 'user_id')) {
            $supplier->with(['user']);
        }
        $supplier = $supplier->orderBy($sortBy, $order)->paginate(10, ['*'], 'supplier_page');
        $users = \App\Models\User::orderBy('username')->get();
        
        // Removed heavy all orders query (unused in view index)
        $orders = collect();

        // QUICK STATS
        $totalPelanggan = Contact::where('tipe', 'customer')->count();
        $aktifBulanIni = \App\Models\PaymentOrder::whereIn('payment_status', ['paid', 'settlement', 'success', 'capture', 'pending'])
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->distinct()
            ->count('user_id');
        $topSpender = $pelanggan->sortByDesc('total_transaksi')->first();

        return view('kontak.index', compact('pelanggan', 'supplier', 'orders', 'sort', 'users', 'totalPelanggan', 'aktifBulanIni', 'topSpender', 'hasPelanggan', 'hasSupplier', 'sub_menus', 'active_tab'));
    }

    public function getTransactions(Request $request, $id)
    {
        $contact = Contact::findOrFail($id);
        $hasUserId = Schema::hasColumn('contacts', 'user_id');

        $query = \App\Models\PaymentOrder::with(['items.product'])
            ->whereIn('payment_status', ['paid', 'settlement', 'success', 'capture', 'pending']);

        $query->where(function($sub) use ($contact, $hasUserId) {
            if ($hasUserId && $contact->user_id) {
                $sub->whereRaw('CAST(user_id AS varchar) = CAST(? AS varchar)', [(string)$contact->user_id]);
            }
            $sub->orWhere('recipient_phone', $contact->no_hp)
                ->orWhereRaw('LOWER(TRIM(recipient_name)) = LOWER(TRIM(?))', [$contact->nama]);
        });

        $orders = $query->orderBy('created_at', 'desc')->paginate(5);

        return response()->json($orders);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'no_hp' => 'required|string|max:20',
            'tipe' => 'required|in:customer,supplier',
        ]);

        Contact::create([
            'nama' => $request->nama,
            'no_hp' => $request->no_hp,
            'tipe' => $request->tipe,
        ]);

        return redirect()->route('kontak.index')->with('success', 'Kontak berhasil ditambahkan');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'no_hp' => 'required|string|max:20',
        ]);

        $contact = Contact::findOrFail($id);
        $contact->update([
            'nama' => $request->nama,
            'no_hp' => $request->no_hp,
        ]);

        return redirect()->route('kontak.index')->with('success', 'Kontak berhasil diperbarui');
    }

    public function destroy($id)
    {
        $contact = Contact::findOrFail($id);
        try {
            $contact->delete();
            return redirect()->route('kontak.index')->with('success', 'Kontak berhasil dihapus');
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == 23503 || $e->getCode() == 1451) {
                return redirect()->route('kontak.index')->with('error', 'Gagal menghapus! Kontak ini tidak bisa dihapus karena masih terhubung dengan data lain (misalnya memiliki riwayat Piutang/Utang).');
            }
            return redirect()->route('kontak.index')->with('error', 'Terjadi kesalahan sistem saat mencoba menghapus kontak.');
        }
    }

    public function syncFromOrders()
    {
        // Ambil semua user_id unik dari payment_orders
        $userIds = \App\Models\PaymentOrder::whereNotNull('user_id')
            ->distinct()
            ->pluck('user_id');

        $syncedCount = 0;
        $updatedCount = 0;

        foreach ($userIds as $userId) {
            // Cari user di tabel users
            $user = \App\Models\User::where('uuid', $userId)->first();
            
            if ($user) {
                // Gunakan updateOrCreate untuk menghindari duplikasi berdasarkan user_id atau no_hp
                // Kita prioritaskan user_id jika kolomnya ada
                $hasUserId = Schema::hasColumn('contacts', 'user_id');
                
                $contact = null;
                if ($hasUserId) {
                    $contact = Contact::where('user_id', $user->uuid)->first();
                }
                
                if (!$contact && $user->no_hp) {
                    $contact = Contact::where('no_hp', $user->no_hp)->first();
                }

                $updateData = [
                    'nama' => $user->username,
                    'no_hp' => $user->no_hp ?? ($contact ? $contact->no_hp : null),
                    'store_id' => $user->store_id ?? ($contact ? $contact->store_id : null),
                ];
                
                if ($hasUserId) {
                    $updateData['user_id'] = $user->uuid;
                }

                if ($contact) {
                    $contact->update($updateData);
                    $updatedCount++;
                } else {
                    $createData = $updateData;
                    $createData['tipe'] = 'customer';
                    Contact::create($createData);
                    $syncedCount++;
                }
            }
        }

        return redirect()->route('kontak.index')->with('success', "$syncedCount kontak baru ditambahkan dan $updatedCount kontak diperbarui dari data pesanan.");
    }

    public function broadcast(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
            'contacts' => 'required|array',
            'contacts.*' => 'required|string'
        ]);

        $token = '5zVfQUgWooaSnJjjcTNk'; // Fonnte Token
        $targets = implode(',', $request->contacts);

        $curl = curl_init();
        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://api.fonnte.com/send',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS => array(
            'target' => $targets,
            'message' => $request->message,
            'delay' => '2' // Delay antar pesan (detik)
          ),
          CURLOPT_HTTPHEADER => array(
            'Authorization: ' . $token
          ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            return response()->json(['success' => false, 'message' => 'Gagal terhubung ke Fonnte: ' . $err], 500);
        } else {
            $respObj = json_decode($response, true);
            if(isset($respObj['status']) && $respObj['status'] == true){
                return response()->json(['success' => true, 'message' => 'Pesan berhasil masuk antrean server WA!']);
            } else {
                $errorMsg = $respObj['reason'] ?? 'Gagal mengirim pesan.';
                return response()->json(['success' => false, 'message' => $errorMsg], 400);
            }
        }
    }
}
