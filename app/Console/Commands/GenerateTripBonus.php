<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Traits\Helper;
use App\Models\UmrohTripDaily;
use App\Models\UmrohTripSaving;
use App\Models\User;
use DateTime;
use Carbon\Carbon;

class GenerateTripBonus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trip:generate {date?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate bonus Trip untuk tanggal tertentu (default: hari ini). Format: Y-m-d (contoh: 2025-12-12)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Ambil tanggal dari argument atau gunakan hari ini
        $dateInput = $this->argument('date') ?? date('Y-m-d');
        
        // Validasi format tanggal
        try {
            $date = DateTime::createFromFormat('Y-m-d', $dateInput);
            if (!$date || $date->format('Y-m-d') !== $dateInput) {
                throw new \Exception('Format tanggal tidak valid');
            }
        } catch (\Exception $e) {
            $this->error("Format tanggal tidak valid! Gunakan format Y-m-d (contoh: 2025-12-12)");
            return 1;
        }
        
        $targetDate = Carbon::parse($date->format('Y-m-d'));
        $targetDateFormatted = $targetDate->format('Y-m-d');
        $targetDateReadable = $targetDate->translatedFormat('d F Y');
        
        $this->info("==========================================");
        $this->info("GENERATE BONUS TRIP");
        $this->info("==========================================");
        $this->info("Target Tanggal: {$targetDateReadable} ({$targetDateFormatted})");
        $this->newLine();
        
        // Cari tanggal pertama UserPin dibuat (untuk menentukan tanggal mulai generate)
        $firstUserPinDate = \App\Models\UserPin::min('created_at');
        $startDate = null;
        
        if ($firstUserPinDate) {
            $startDate = Carbon::parse($firstUserPinDate)->startOfDay();
        }
        
        // Cek tanggal terakhir yang sudah di-generate
        $lastGeneratedDate = UmrohTripDaily::max('date');
        
        $datesToGenerate = [];
        
        if ($lastGeneratedDate) {
            $lastDate = Carbon::parse($lastGeneratedDate);
            $lastDateReadable = $lastDate->translatedFormat('d F Y');
            $this->info("Tanggal terakhir di-generate: {$lastDateReadable} ({$lastGeneratedDate})");
            
            // Tentukan range tanggal yang perlu di-generate
            if ($lastDate->lt($targetDate)) {
                // Tanggal terakhir < target date: generate dari tanggal terakhir (regenerate) sampai target
                $currentDate = $lastDate->copy();
                while ($currentDate->lte($targetDate)) {
                    $datesToGenerate[] = $currentDate->format('Y-m-d');
                    $currentDate->addDay();
                }
                
                $this->info("Akan generate: " . count($datesToGenerate) . " hari");
                $this->info("  - Regenerate: {$lastDateReadable} (untuk memastikan data terbaru)");
                if (count($datesToGenerate) > 1) {
                    $nextDate = $lastDate->copy()->addDay();
                    $this->info("  - Generate baru: " . $nextDate->translatedFormat('d F Y') . " sampai {$targetDateReadable}");
                }
            } elseif ($lastDate->eq($targetDate)) {
                // Tanggal terakhir == target date: hanya regenerate
                $datesToGenerate = [$lastDate->format('Y-m-d')];
                $this->info("Akan regenerate: {$lastDateReadable} (untuk memastikan data terbaru)");
            } else {
                // Tanggal terakhir > target date: hanya regenerate tanggal target
                $datesToGenerate = [$targetDate->format('Y-m-d')];
                
                $this->warn("⚠️  Tanggal terakhir di-generate ({$lastDateReadable}) lebih baru dari target tanggal ({$targetDateReadable})!");
                $this->info("Akan regenerate: {$targetDateReadable} (untuk memastikan data terbaru)");
            }
        } else {
            // Belum ada data sama sekali: generate dari tanggal pertama UserPin sampai target
            if ($startDate && $startDate->lte($targetDate)) {
                $currentDate = $startDate->copy();
                while ($currentDate->lte($targetDate)) {
                    $datesToGenerate[] = $currentDate->format('Y-m-d');
                    $currentDate->addDay();
                }
                
                $startDateReadable = $startDate->translatedFormat('d F Y');
                $this->info("Belum ada data sebelumnya.");
                $this->info("Tanggal pertama UserPin dibuat: {$startDateReadable}");
                $this->info("Akan generate: " . count($datesToGenerate) . " hari dari {$startDateReadable} sampai {$targetDateReadable}");
            } else {
                // Jika tidak ada UserPin atau startDate > targetDate, generate target date saja
                $datesToGenerate = [$targetDateFormatted];
                $this->info("Belum ada data sebelumnya. Akan generate untuk: {$targetDateReadable}");
            }
        }
        
        // Pastikan datesToGenerate sudah diurutkan ascending (dari tanggal paling awal ke terbaru)
        sort($datesToGenerate);
        
        $this->newLine();
        
        // Set memory dan execution time
        ini_set('max_execution_time', '-1');
        ini_set('memory_limit', '-1');
        
        $totalRecordsGenerated = 0;
        $totalBonusDistributed = 0;
        $summaryData = [];
        
        // Generate untuk setiap tanggal
        foreach ($datesToGenerate as $dateToProcess) {
            $processDate = Carbon::parse($dateToProcess);
            $processDateReadable = $processDate->translatedFormat('d F Y');
            
            $this->info("Processing: {$processDateReadable} ({$dateToProcess})");
            
            // Untuk regenerate: hapus data existing dan kurangi dari akumulasi
            // Regenerate jika tanggal ini sudah pernah di-generate sebelumnya
            $isRegenerate = UmrohTripDaily::whereDate('date', $dateToProcess)->exists();
            if ($isRegenerate) {
                $this->line("  → Regenerate (hapus data lama dan generate ulang)");
                
                // Ambil data existing untuk dikurangi dari akumulasi
                $existingData = UmrohTripDaily::whereDate('date', $dateToProcess)->get();
                $existingTotal = $existingData->sum('amount');
                
                if ($existingData->count() > 0) {
                    // Kurangi dari akumulasi tahunan per user
                    foreach ($existingData as $tripDaily) {
                        $year = $processDate->format('Y');
                        $saving = \App\Models\UmrohTripSaving::where('user_id', $tripDaily->user_id)
                            ->where('year', $year)
                            ->first();
                        
                        if ($saving) {
                            $newAccumulation = max(0, $saving->yearly_accumulation - $tripDaily->amount);
                            $saving->update(['yearly_accumulation' => $newAccumulation]);
                        }
                    }
                    
                    // Hapus data daily
                    UmrohTripDaily::whereDate('date', $dateToProcess)->delete();
                    $this->line("  → Data lama dihapus (Total: Rp " . number_format($existingTotal, 0, ',', '.') . ")");
                }
            }
            
            // Hitung omset untuk tanggal ini (harian)
            $totalOmzet = Helper::transactionPoinDaily($dateToProcess) * 1000;
            $totalUmrohAmount = round($totalOmzet * 0.04);
            
            // Hitung jumlah qualified users untuk tanggal ini
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
            
            if ($qualifiedCount == 0) {
                $this->warn("  ⚠️  Tidak ada member qualified untuk tanggal ini");
                $summaryData[] = [
                    'date' => $processDateReadable,
                    'date_formatted' => $dateToProcess,
                    'omzet' => $totalOmzet,
                    'qualified' => 0,
                    'bonus_per_member' => 0,
                    'records' => 0,
                    'total_bonus' => 0,
                ];
                continue;
            }
            
            $umrohAmountPerMember = round($totalUmrohAmount / $qualifiedCount);
            
            // Generate bonus Trip
            try {
                Helper::calculateUmrohTrip($dateToProcess);
                
                // Ambil data yang baru di-generate
                $generatedData = UmrohTripDaily::whereDate('date', $dateToProcess)->get();
                $recordsCount = $generatedData->count();
                $bonusTotal = $generatedData->sum('amount');
                
                $totalRecordsGenerated += $recordsCount;
                $totalBonusDistributed += $bonusTotal;
                
                $summaryData[] = [
                    'date' => $processDateReadable,
                    'date_formatted' => $dateToProcess,
                    'omzet' => $totalOmzet,
                    'qualified' => $qualifiedCount,
                    'bonus_per_member' => $umrohAmountPerMember,
                    'records' => $recordsCount,
                    'total_bonus' => $bonusTotal,
                ];
                
                $this->line("  ✓ Omset: Rp " . number_format($totalOmzet, 0, ',', '.') . " | Qualified: {$qualifiedCount} | Bonus/Member: Rp " . number_format($umrohAmountPerMember, 0, ',', '.') . " | Records: {$recordsCount}");
            } catch (\Exception $e) {
                $this->error("  ✗ Error: " . $e->getMessage());
                $summaryData[] = [
                    'date' => $processDateReadable,
                    'date_formatted' => $dateToProcess,
                    'error' => $e->getMessage(),
                ];
            }
            
            $this->newLine();
        }
        
        // Summary hasil generate
        $this->info("==========================================");
        $this->info("SUMMARY BONUS TRIP");
        $this->info("==========================================");
        
        foreach ($summaryData as $summary) {
            if (isset($summary['error'])) {
                $this->error("  {$summary['date']}: ERROR - {$summary['error']}");
                continue;
            }
            
            $this->line("  {$summary['date']}:");
            $this->line("    - Omset: Rp " . number_format($summary['omzet'], 0, ',', '.'));
            $this->line("    - Qualified: {$summary['qualified']} orang");
            if ($summary['qualified'] > 0) {
                $this->line("    - Bonus/Member: Rp " . number_format($summary['bonus_per_member'], 0, ',', '.'));
                $this->line("    - Records: {$summary['records']}");
                $this->line("    - Total Bonus: Rp " . number_format($summary['total_bonus'], 0, ',', '.'));
            }
            $this->newLine();
        }
        
        $this->info("TOTAL:");
        $this->info("  - Total Records: {$totalRecordsGenerated}");
        $this->info("  - Total Bonus Dibagikan: Rp " . number_format($totalBonusDistributed, 0, ',', '.'));
        $this->info("==========================================");
        $this->info("✓ Generate selesai!");
        $this->info("==========================================");
        
        return 0;
    }
}



