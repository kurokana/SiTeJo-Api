<?php

namespace App\Helpers;

use App\Models\Ticket;
use Illuminate\Support\Str;

class LetterNumberHelper
{
    /**
     * Generate nomor surat dengan format:
     * NOMOR/TE-UNILA/KATEGORI/BULAN/TAHUN/KODE
     * 
     * Contoh: 001/TE-UNILA/SKP/XII/2025/A1B2C3
     * 
     * Penjelasan:
     * - NOMOR: Sequential number (001, 002, dst)
     * - TE-UNILA: Teknik Elektro - Universitas Lampung
     * - KATEGORI: SKP (Surat Keterangan), SRK (Surat Rekomendasi), IJN (Ijin), LNY (Lainnya)
     * - BULAN: Bulan Romawi (I-XII)
     * - TAHUN: Tahun penuh (2025)
     * - KODE: Random alphanumeric 6 karakter untuk keamanan
     */
    public static function generate($ticketType)
    {
        // Get sequential number dari tickets yang sudah approved di tahun ini
        $year = date('Y');
        $month = date('n'); // 1-12
        
        $lastNumber = Ticket::whereNotNull('nomor_surat')
            ->whereYear('approved_at', $year)
            ->whereMonth('approved_at', $month)
            ->count();
        
        $sequentialNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        
        // Mapping kategori
        $categoryMap = [
            'surat_keterangan' => 'SKP',
            'surat_rekomendasi' => 'SRK',
            'ijin' => 'IJN',
            'lainnya' => 'LNY'
        ];
        
        $category = $categoryMap[$ticketType] ?? 'LNY';
        
        // Bulan Romawi
        $romanMonths = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
            7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'
        ];
        
        $romanMonth = $romanMonths[$month];
        
        // Generate kode unik 6 karakter alphanumeric
        $uniqueCode = strtoupper(Str::random(6));
        
        // Format: NOMOR/TE-UNILA/KATEGORI/BULAN/TAHUN/KODE
        $nomorSurat = "{$sequentialNumber}/TE-UNILA/{$category}/{$romanMonth}/{$year}/{$uniqueCode}";
        
        // Pastikan unique (sangat jarang collision karena ada random code)
        while (Ticket::where('nomor_surat', $nomorSurat)->exists()) {
            $uniqueCode = strtoupper(Str::random(6));
            $nomorSurat = "{$sequentialNumber}/TE-UNILA/{$category}/{$romanMonth}/{$year}/{$uniqueCode}";
        }
        
        return $nomorSurat;
    }
    
    /**
     * Verify nomor surat format dan keberadaan di database
     */
    public static function verify($nomorSurat)
    {
        // Check format
        $pattern = '/^\d{3}\/TE-UNILA\/(SKP|SRK|IJN|LNY)\/[IVX]+\/\d{4}\/[A-Z0-9]{6}$/';
        
        if (!preg_match($pattern, $nomorSurat)) {
            return [
                'valid' => false,
                'message' => 'Format nomor surat tidak valid'
            ];
        }
        
        // Check existence in database
        $ticket = Ticket::where('nomor_surat', $nomorSurat)->first();
        
        if (!$ticket) {
            return [
                'valid' => false,
                'message' => 'Nomor surat tidak ditemukan dalam sistem'
            ];
        }
        
        return [
            'valid' => true,
            'message' => 'Nomor surat valid',
            'ticket' => $ticket->load(['student', 'lecturer'])
        ];
    }
    
    /**
     * Parse informasi dari nomor surat
     */
    public static function parse($nomorSurat)
    {
        $parts = explode('/', $nomorSurat);
        
        if (count($parts) !== 6) {
            return null;
        }
        
        $categoryMap = [
            'SKP' => 'Surat Keterangan',
            'SRK' => 'Surat Rekomendasi',
            'IJN' => 'Ijin',
            'LNY' => 'Lainnya'
        ];
        
        return [
            'sequential_number' => $parts[0],
            'institution' => $parts[1],
            'category_code' => $parts[2],
            'category_name' => $categoryMap[$parts[2]] ?? 'Tidak diketahui',
            'month' => $parts[3],
            'year' => $parts[4],
            'unique_code' => $parts[5]
        ];
    }
}
