<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Carbon\Carbon;

class ReportExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithCustomStartCell, WithEvents
{
    protected $startDate;
    protected $endDate;
    protected $deliveryType;
    protected $paymentMethod;
    protected $ids;
    
    // Variabel untuk perhitungan di PHP
    protected $totalCash = 0;
    protected $totalQRIS = 0;
    protected $totalDeliveryFee = 0;
    protected $grandTotalHarga = 0;
    protected $totalUangMasuk = 0;
    protected $totalKembalian = 0;
    protected $rowCount = 0;

    public function __construct($startDate, $endDate, $deliveryType = 'all', $paymentMethod = 'all', $ids = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->deliveryType = $deliveryType;
        $this->paymentMethod = $paymentMethod;
        $this->ids = $ids;
    }

    public function startCell(): string
    {
        return 'A8';
    }

    public function query()
    {
        $query = \App\Models\Order::query()->where('status', 'DIAMBIL');
        
        if (!empty($this->ids)) {
            $query->whereIn('id', $this->ids);
        } else {
            $query->whereBetween('updated_at', [$this->startDate . ' 00:00:00', $this->endDate . ' 23:59:59']);
            
            if ($this->deliveryType !== 'all') {
                if ($this->deliveryType === 'none') {
                    $query->where(function($q) {
                        $q->where('delivery_type', 'none')->orWhereNull('delivery_type');
                    });
                } else {
                    $query->where('delivery_type', $this->deliveryType);
                }
            }

            if ($this->paymentMethod !== 'all') {
                $query->where('payment_method', $this->paymentMethod);
            }
        }

        return $query;
    }
    public function headings(): array
    {
        $headers = [
            'No. Order', 'Tanggal Selesai', 'Nama Pelanggan', 
            'Layanan', 'Jumlah', 'Harga Layanan',
            'Metode', 'Layanan Driver'
        ];

        if (strtoupper($this->paymentMethod) !== 'QRIS') {
            $headers[] = 'Uang Masuk';
            $headers[] = 'Kembalian';
        }

        $headers[] = 'Ongkir';
        $headers[] = 'Total Harga';

        return $headers;
    }

    public function map($order): array
    {
        $this->rowCount++;

        // 1. Ambil Data Layanan dari Order Items
        $layananArr = [];
        $jumlahArr = [];
        $hargaLayananArr = [];

        if ($order->items) {
            foreach ($order->items as $item) {
                $layananArr[] = $item->service_name;
                
                // Format jumlah agar tidak ada 0 berlebih di belakang koma (misal 2.000 -> 2)
                $qty = floatval($item->qty_or_weight);
                $qtyStr = rtrim(rtrim(number_format($qty, 3, '.', ''), '0'), '.');
                $jumlahArr[] = $qtyStr . ' ' . $item->unit;
                
                $hargaLayananArr[] = $item->price;
            }
        }

        $layananStr = implode(",\n", $layananArr);
        $jumlahStr = implode(",\n", $jumlahArr);
        $hargaLayananStr = implode(",\n", $hargaLayananArr);

        // 2. Bersihkan Angka
        $valTotal = (int) preg_replace('/[^0-9]/', '', $order->total_price);
        $valReceived = (int) preg_replace('/[^0-9]/', '', $order->cash_received ?? $order->total_price);
        $valChange = (int) preg_replace('/[^0-9]/', '', $order->cash_change ?? 0);
        $valOngkir = (int) ($order->delivery_fee ?? 0);

        $method = strtoupper($order->payment_method);
        
        // 3. Klasifikasi Pendapatan
        if ($method == 'CASH') {
            $this->totalCash += $valTotal;
            $uangMasuk = $valReceived;
            $kembalian = $valChange;
        } else {
            $this->totalQRIS += $valTotal;
            $uangMasuk = $valTotal;
            $kembalian = 0;
        }

        $this->totalDeliveryFee += $valOngkir;
        $this->grandTotalHarga += $valTotal;
        $this->totalUangMasuk += $uangMasuk;
        $this->totalKembalian += $kembalian;

        $typeLabel = "Ambil Sendiri";
        if ($order->delivery_type === 'pickup') $typeLabel = "Pick-up";
        else if ($order->delivery_type === 'delivery') $typeLabel = "Delivery";
        else if ($order->delivery_type === 'both') $typeLabel = "Delivery & Pick-up";

        // Return data dengan kolom baru
        $row = [
            '#' . ($order->order_number ?? $order->invoice_number),
            $order->updated_at->format('d/m/Y H:i'),
            $order->customer_name,
            $layananStr,
            $jumlahStr,
            $hargaLayananStr,
            $method == 'CASH' ? 'TUNAI' : $method,
            $typeLabel,
        ];

        if (strtoupper($this->paymentMethod) !== 'QRIS') {
            $row[] = $uangMasuk;
            $row[] = $kembalian;
        }

        $row[] = $valOngkir;
        $row[] = $valTotal;

        return $row;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $lastDataRow = $this->rowCount + 8;
                $summaryBottomRow = $lastDataRow + 1;

                $isQris = strtoupper($this->paymentMethod) === 'QRIS';
                $lastCol = $isQris ? 'J' : 'L';

                // --- BAGIAN ATAS: JUDUL (Disesuaikan ke kolom L atau J) ---
                $title = 'LAPORAN PENDAPATAN LAUNDRY';
                $filters = [];
                
                if ($this->deliveryType !== 'all') {
                    $deliveryLabel = '';
                    if ($this->deliveryType === 'none') $deliveryLabel = 'Ambil Sendiri';
                    else if ($this->deliveryType === 'pickup') $deliveryLabel = 'Pick-up';
                    else if ($this->deliveryType === 'delivery') $deliveryLabel = 'Delivery';
                    else if ($this->deliveryType === 'both') $deliveryLabel = 'Delivery & Pick-up';
                    
                    if ($deliveryLabel) $filters[] = $deliveryLabel;
                }
                
                if ($this->paymentMethod !== 'all') {
                    $paymentLabel = strtoupper($this->paymentMethod);
                    if ($paymentLabel === 'CASH') $paymentLabel = 'TUNAI';
                    $filters[] = $paymentLabel;
                }
                
                if (!empty($filters)) {
                    $title .= ' (' . implode(' - ', $filters) . ')';
                }

                $event->sheet->mergeCells("A1:{$lastCol}1");
                $event->sheet->setCellValue('A1', $title);
                
                $periode = Carbon::parse($this->startDate)->format('d M Y') . ' - ' . Carbon::parse($this->endDate)->format('d M Y');
                $event->sheet->mergeCells("A2:{$lastCol}2");
                $event->sheet->setCellValue('A2', 'Periode: ' . $periode);

                // Ringkasan Pendapatan di Atas (Baris 4-6)
                $event->sheet->setCellValue('A4', 'TOTAL KESELURUHAN');
                $event->sheet->setCellValue('B4', ': Rp ' . number_format($this->grandTotalHarga, 0, ',', '.'));
                
                $event->sheet->setCellValue('A5', 'TOTAL JASA LAUNDRY');
                $event->sheet->setCellValue('B5', ': Rp ' . number_format($this->grandTotalHarga - $this->totalDeliveryFee, 0, ',', '.'));

                $event->sheet->setCellValue('A6', 'TOTAL ONGKIR (DRIVER)');
                $event->sheet->setCellValue('B6', ': Rp ' . number_format($this->totalDeliveryFee, 0, ',', '.'));

                $currentRow = 4;
                if ($this->paymentMethod === 'all' || strtoupper($this->paymentMethod) === 'CASH') {
                    $event->sheet->setCellValue('D' . $currentRow, 'TOTAL TUNAI (Fisik)');
                    $event->sheet->setCellValue('E' . $currentRow, ': Rp ' . number_format($this->totalCash, 0, ',', '.'));
                    $currentRow++;
                }
                
                if ($this->paymentMethod === 'all' || strtoupper($this->paymentMethod) === 'QRIS') {
                    $event->sheet->setCellValue('D' . $currentRow, 'TOTAL QRIS (Digital)');
                    $event->sheet->setCellValue('E' . $currentRow, ': Rp ' . number_format($this->totalQRIS, 0, ',', '.'));
                    $currentRow++;
                }

                // --- BAGIAN BAWAH: TOTAL KESELURUHAN KOLOM ---
                $event->sheet->mergeCells("A{$summaryBottomRow}:H{$summaryBottomRow}");
                $event->sheet->setCellValue("A{$summaryBottomRow}", "TOTAL KESELURUHAN");
                if ($isQris) {
                    $event->sheet->setCellValue("I{$summaryBottomRow}", $this->totalDeliveryFee);
                    $event->sheet->setCellValue("J{$summaryBottomRow}", $this->grandTotalHarga);
                } else {
                    $event->sheet->setCellValue("I{$summaryBottomRow}", "");
                    $event->sheet->setCellValue("J{$summaryBottomRow}", "");
                    $event->sheet->setCellValue("K{$summaryBottomRow}", $this->totalDeliveryFee);
                    $event->sheet->setCellValue("L{$summaryBottomRow}", $this->grandTotalHarga);
                }

                // --- STYLING ---
                $event->sheet->getStyle('A1:A2')->getFont()->setBold(true)->setSize(14);
                $event->sheet->getStyle('A1:A2')->getAlignment()->setHorizontal('center');

                $event->sheet->getStyle('A4:A6')->getFont()->setBold(true);
                $lastRowStyle = $currentRow - 1;
                if ($lastRowStyle >= 4) {
                    $event->sheet->getStyle('D4:D' . $lastRowStyle)->getFont()->setBold(true);
                    $event->sheet->getStyle('E4:E' . $lastRowStyle)->getFont()->getColor()->setRGB('1D6F42');
                }
                $event->sheet->getStyle('B4:B6')->getFont()->getColor()->setRGB('1D6F42');

                // Styling Baris Total Bawah
                $event->sheet->getStyle("A{$summaryBottomRow}:{$lastCol}{$summaryBottomRow}")->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'E2EFDA']], // Warna hijau muda yang lebih rapi
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
                ]);
                $event->sheet->getStyle("A{$summaryBottomRow}")->getAlignment()->setHorizontal('left');
                if ($isQris) {
                    $event->sheet->getStyle("I{$summaryBottomRow}:J{$summaryBottomRow}")->getNumberFormat()->setFormatCode('#,##0');
                } else {
                    $event->sheet->getStyle("I{$summaryBottomRow}:L{$summaryBottomRow}")->getNumberFormat()->setFormatCode('#,##0');
                }
                
                // Set wrap text agar comma-separated dengan \n tampil rapi
                $event->sheet->getStyle("D8:F{$lastDataRow}")->getAlignment()->setWrapText(true);
            },
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();
        $isQris = strtoupper($this->paymentMethod) === 'QRIS';
        $lastCol = $isQris ? 'J' : 'L';
        
        // Header Tabel (Baris 8, disesuaikan sampai kolom L atau J)
        $sheet->getStyle("A8:{$lastCol}8")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '1D6F42']],
            'alignment' => ['horizontal' => 'center'],
        ]);

        // Border Tabel
        $sheet->getStyle("A8:{$lastCol}" . $highestRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // Format Ribuan Kolom Keuangan
        $sheet->getStyle('F9:F' . $highestRow)->getNumberFormat()->setFormatCode('#,##0');
        if ($isQris) {
            $sheet->getStyle('I9:J' . $highestRow)->getNumberFormat()->setFormatCode('#,##0');
        } else {
            $sheet->getStyle('I9:L' . $highestRow)->getNumberFormat()->setFormatCode('#,##0');
        }

        return [];
    }
}