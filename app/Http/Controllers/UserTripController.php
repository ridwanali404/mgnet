<?php

namespace App\Http\Controllers;

use App\Models\UmrohTripDaily;
use App\Models\UmrohTripSaving;
use App\Models\User;
use App\Models\Transaction;
use App\Models\OfficialTransaction;
use App\Models\GlobalDailyPoin;
use App\Models\Poin;
use App\Models\KeyValue;
use App\Traits\Helper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use DateTime;
use Carbon\Carbon;
use Session;
use Auth;

class UserTripController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Filter tanggal (default H-1 untuk admin, semua untuk member)
        $selectedDate = $request->date ?? (Auth::user()->type == 'admin' ? Carbon::yesterday()->format('Y-m-d') : null);
        
        if (Auth::user()->type == 'admin') {
            // Untuk admin: filter berdasarkan tanggal yang dipilih
            if ($selectedDate) {
                $userTripDailies = UmrohTripDaily::whereDate('date', $selectedDate)
                    ->orderBy('created_at', 'desc')
                    ->get();
            } else {
                $userTripDailies = UmrohTripDaily::orderBy('date', 'desc')->orderBy('created_at', 'desc')->get();
            }
            
            $userTripSavings = UmrohTripSaving::orderBy('year', 'desc')->get();
            $isQualified = null; // Admin tidak perlu cek qualified
            $qualificationReasons = []; // Admin tidak perlu reasons
            
            // Hitung informasi untuk tanggal yang dipilih
            $dailyInfo = null;
            if ($selectedDate) {
                // Hitung omset nasional harian menggunakan Helper
                $totalOmzet = Helper::transactionPoinDaily($selectedDate) * 1000;
                $totalUmrohAmount = round($totalOmzet * 0.04);
                
                // Hitung jumlah qualified untuk tanggal tersebut
                $qualifiedUsers = User::whereHas('premiumUserPin', function ($q) {
                    $q->whereHas('pin', function ($qPin) {
                        $qPin->whereIn('name', ['Gold', 'Platinum']);
                    });
                })
                ->where('is_active', true)
                ->get()
                ->filter(function ($user) {
                    $sponsorCount = $user->sponsors()
                        ->whereHas('premiumUserPin', function ($q) {
                            $q->whereHas('pin', function ($qPin) {
                                $qPin->whereIn('name', ['Gold', 'Platinum']);
                            });
                        })
                        ->where('is_active', true)
                        ->count();
                    return $sponsorCount >= 3;
                });
                
                $qualifiedCount = $qualifiedUsers->count();
                $umrohAmountPerMember = $qualifiedCount > 0 ? round($totalUmrohAmount / $qualifiedCount) : 0;
                
                // Ambil data per member untuk tanggal tersebut
                $memberDetails = [];
                foreach ($qualifiedUsers as $user) {
                    $tripDaily = UmrohTripDaily::where('user_id', $user->id)
                        ->whereDate('date', $selectedDate)
                        ->first();
                    
                    $memberDetails[] = [
                        'user' => $user,
                        'amount' => $tripDaily ? $tripDaily->amount : $umrohAmountPerMember,
                        'has_data' => $tripDaily ? true : false,
                    ];
                }
                
                $dailyInfo = [
                    'date' => $selectedDate,
                    'date_readable' => Carbon::parse($selectedDate)->translatedFormat('d F Y'),
                    'total_omzet' => $totalOmzet,
                    'total_umroh_amount' => $totalUmrohAmount,
                    'qualified_count' => $qualifiedCount,
                    'amount_per_member' => $umrohAmountPerMember,
                    'member_details' => $memberDetails,
                ];
            }
        } else {
            $user = Auth::user();
            $userTripDailies = $user->umrohTripDailies()->orderBy('date', 'desc')->orderBy('created_at', 'desc')->get();
            $userTripSavings = $user->umrohTripSavings()->orderBy('year', 'desc')->get();
            
            // Cek apakah user qualified untuk Trip
            // Syarat: Gold/Platinum, aktif, dan minimal 3 sponsor langsung (premium dan aktif)
            $isQualified = false;
            $qualificationReasons = [];
            
            // Cek apakah user premium (Gold atau Platinum)
            $isPremium = false;
            if ($user->premiumUserPin && $user->premiumUserPin->pin) {
                $pinName = $user->premiumUserPin->pin->name;
                $isPremium = in_array($pinName, ['Gold', 'Platinum']);
            }
            
            if (!$isPremium) {
                $qualificationReasons[] = 'Anda belum memiliki paket Gold atau Platinum';
            }
            
            // Cek apakah user aktif
            if (!$user->is_active) {
                $qualificationReasons[] = 'Status Anda belum aktif';
            }
            
            // Hitung sponsor langsung (disponsori langsung) yang premium dan aktif
            $sponsorCount = $user->sponsors()
                ->whereHas('premiumUserPin', function ($q) {
                    $q->whereHas('pin', function ($qPin) {
                        $qPin->whereIn('name', ['Gold', 'Platinum']);
                    });
                })
                ->where('is_active', true)
                ->count();
            
            if ($sponsorCount < 3) {
                $qualificationReasons[] = 'Anda belum memiliki minimal 3 sponsor langsung yang premium dan aktif (saat ini: ' . $sponsorCount . ' sponsor)';
            }
            
            // User qualified jika semua syarat terpenuhi
            if ($isPremium && $user->is_active && $sponsorCount >= 3) {
                $isQualified = true;
            }
            
            // Untuk member: tidak ada filter tanggal dan dailyInfo
            $selectedDate = null;
            $dailyInfo = null;
        }
        
        return view('userTrip', compact('userTripDailies', 'userTripSavings', 'isQualified', 'qualificationReasons', 'selectedDate', 'dailyInfo'));
    }

    /**
     * Generate bonus Trip via command
     */
    public function generate(Request $request)
    {
        if (Auth::user()->type != 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $request->validate([
            'date' => 'required|date|date_format:Y-m-d'
        ]);

        $date = $request->date;

        try {
            // Define STDIN if not exists (untuk web context)
            if (!defined('STDIN')) {
                define('STDIN', fopen('php://stdin', 'r'));
            }
            if (!defined('STDOUT')) {
                define('STDOUT', fopen('php://stdout', 'w'));
            }
            if (!defined('STDERR')) {
                define('STDERR', fopen('php://stderr', 'w'));
            }

            // Run the command dengan non-interactive mode
            // Laravel otomatis menambahkan --no-interaction untuk web context
            Artisan::call('trip:generate', [
                'date' => $date,
            ]);

            // Get command output
            $output = Artisan::output();

            return response()->json([
                'success' => true,
                'message' => 'Generate bonus Trip berhasil untuk tanggal ' . \Carbon\Carbon::parse($date)->translatedFormat('d F Y'),
                'summary' => $output
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
