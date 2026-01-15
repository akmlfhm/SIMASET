<!DOCTYPE html>
<html>
<head>
    <title>Laporan Keuangan</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12px;
        }
        .container {
            margin: 0 auto;
            width: 100%;
        }
        /* Style untuk Header/Kop Surat */
        .header-image {
            width: 100%;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .report-title {
            text-align: center;
            text-transform: uppercase;
            font-size: 16px;
            margin-bottom: 20px;
            text-decoration: underline;
        }
        .info-table {
            margin-bottom: 15px;
            border: none;
        }
        .info-table td {
            padding: 2px;
            text-align: left;
        }
        /* Style untuk Tabel Data */
        .table {
            border-collapse: collapse;
            width: 100%;
        }
        .table th, .table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: center;
        }
        .table th {
            background-color: #ffff00; /* Warna kuning sesuai gambar */
        }
        .text-right {
            text-align: right !important;
        }
    </style>
</head>
<body>
    <div class="container">
    <div style="text-align: center; margin-bottom: 20px;">
        <img src="{{ public_path('assets/img/header.png') }}" 
             style="width: 100%; height: auto;" 
             alt="Header Kop Surat">
    </div>

    <hr style="border: 1px solid #000; margin-top: -10px; margin-bottom: 20px;">
    
    <table class="info-table" style="width: 100%; margin-bottom: 10px;">
    </table>

        <div class="report-title">
            <strong>
                @if(auth()->user()->roles === 'kepalausaha')
                    Laporan Data Aset {{ auth()->user()->lokasi->nama_lokasi }} Tahun {{ date('Y') }}
                @else
                    Laporan Data Aset Seluruh Unit Tahun {{ date('Y') }}
                @endif
            </strong>
			<br>
    <span style="font-size: 14px; text-decoration: none;">
        Per Tanggal: {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}
    </span>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th width="30">No.</th>
                    <th>Kode Barang</th>
                    <th>Nama Barang</th>
                    <th>Tanggal Pembelian</th>
                    <th>Kategori</th>
                    <th>Lokasi</th>
                    <th>Harga</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($laporans as $laporan)
                <tr>
                    <td>{{ $loop->iteration }}</td>             
                    <td>{{ $laporan->kode_barang }}</td>
                    <td>{{ $laporan->nama }}</td>
                    <td>{{ $laporan->tanggal }}</td>
                    <td>{{ $laporan->kategori->nama }}</td>
                    <td>{{ $laporan->lokasi->nama_lokasi }}</td>
                    <td class="text-right">Rp. {{ number_format($laporan->harga, 0, ',', '.') }}</td>
                </tr>  
                @endforeach  
            </tbody>
            <tfoot>
                <tr style="background-color: #f2f2f2;">
                    <td colspan="6"><strong>Total Harga</strong></td>
                    <td class="text-right">
                        <strong>
                            @if(auth()->user()->roles == 'kepalausaha')
                                Rp. {{ number_format($totalHargaUsaha, 0, ',', '.') }}
                            @else
                                Rp. {{ number_format($totalHarga, 0, ',', '.') }}
                            @endif
                        </strong>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</body>
</html>