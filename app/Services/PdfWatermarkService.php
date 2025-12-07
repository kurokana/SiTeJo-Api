<?php

namespace App\Services;

use setasign\Fpdi\Fpdi;
use Illuminate\Support\Facades\Storage;

class PdfWatermarkService
{
    /**
     * Add watermark to PDF with letter number
     * 
     * @param string $pdfPath - Path to original PDF in storage
     * @param string $nomorSurat - Letter number to watermark
     * @param string $approvedAt - Approval date
     * @return string - Path to watermarked PDF
     */
    public function addWatermark($pdfPath, $nomorSurat, $approvedAt = null)
    {
        $fullPath = Storage::disk('public')->path($pdfPath);
        
        if (!file_exists($fullPath)) {
            throw new \Exception("PDF file not found: {$fullPath}");
        }

        $pdf = new Fpdi();
        $pageCount = $pdf->setSourceFile($fullPath);

        // Format tanggal approval
        $tanggalStr = $approvedAt 
            ? date('d/m/Y', strtotime($approvedAt))
            : date('d/m/Y');

        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            // Import halaman
            $templateId = $pdf->importPage($pageNo);
            $size = $pdf->getTemplateSize($templateId);
            
            // Tentukan orientasi berdasarkan ukuran
            $orientation = $size['width'] > $size['height'] ? 'L' : 'P';
            
            // Tambah halaman baru dengan orientasi yang sesuai
            $pdf->AddPage($orientation, [$size['width'], $size['height']]);
            
            // Use template (paste original content)
            $pdf->useTemplate($templateId);
            
            // ========== WATERMARK HEADER ==========
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->SetTextColor(0, 102, 204); // Biru #0066CC
            
            // Position header watermark
            $pdf->SetXY(10, 5);
            
            // Text header
            $headerText = "No. Surat: {$nomorSurat} | Disetujui: {$tanggalStr}";
            $pdf->Cell(0, 5, $headerText, 0, 0, 'C');
            
            // Garis bawah header
            $pdf->SetDrawColor(0, 102, 204);
            $pdf->SetLineWidth(0.5);
            $pdf->Line(10, 12, $size['width'] - 10, 12);
            
            // ========== WATERMARK FOOTER ==========
            // Position footer
            $footerY = $size['height'] - 15;
            
            // Garis atas footer
            $pdf->Line(10, $footerY, $size['width'] - 10, $footerY);
            
            // Text footer
            $pdf->SetXY(10, $footerY + 2);
            $pdf->SetFont('Arial', 'I', 8);
            $footerText = "Dokumen Resmi - Teknik Elektro Unila | No: {$nomorSurat}";
            $pdf->Cell(0, 5, $footerText, 0, 0, 'C');
            
            // QR Code hint (opsional)
            $pdf->SetXY(10, $footerY + 7);
            $pdf->SetFont('Arial', '', 7);
            $pdf->SetTextColor(100, 100, 100);
            $verifyUrl = env('APP_URL', 'http://localhost:3000') . "/verify-letter";
            $pdf->Cell(0, 3, "Verifikasi keaslian: {$verifyUrl}", 0, 0, 'C');
        }

        // Save watermarked PDF
        $watermarkedPath = 'documents/watermarked_' . basename($pdfPath);
        $outputFullPath = Storage::disk('public')->path($watermarkedPath);
        
        // Ensure directory exists
        $outputDir = dirname($outputFullPath);
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }
        
        $pdf->Output('F', $outputFullPath);

        return $watermarkedPath;
    }

    /**
     * Add watermark and return file content for download
     */
    public function addWatermarkForDownload($pdfPath, $nomorSurat, $approvedAt = null)
    {
        $watermarkedPath = $this->addWatermark($pdfPath, $nomorSurat, $approvedAt);
        $fullPath = Storage::disk('public')->path($watermarkedPath);
        
        $content = file_get_contents($fullPath);
        
        // Clean up watermarked file
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
        
        return $content;
    }

    /**
     * Check if file is PDF
     */
    public function isPdf($filePath)
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        return $extension === 'pdf';
    }
}
