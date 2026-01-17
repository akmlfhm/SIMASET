<!DOCTYPE html>
<html>
<head>
    <title>Laporan Pengadaan Barang</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12px;
            color: #333;
        }
        .container {
            margin: 0 auto;
            width: 100%;
        }
        /* Style untuk Header sesuai file pertama Anda */
        .header-container {
            text-align: center;
            margin-bottom: 20px;
        }
        .header-img {
            width: 100%;
            height: auto;
        }
        .line-separator {
            border: 1px solid #000;
            margin-top: -10px;
            margin-bottom: 20px;
        }
        /* Style Judul Laporan */
        .report-title {
            text-align: center;
            text-transform: uppercase;
            font-size: 16px;
            margin-bottom: 20px;
            text-decoration: underline;
        }
        /* Style Tabel Data sesuai image_2043ae.png */
        .table-data {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .table-data th {
            background-color: #ffff00; /* Warna kuning sesuai gambar */
            border: 1px solid #000;
            padding: 10px;
            text-transform: uppercase;
            font-size: 12px;
        }
        .table-data td {
            border: 1px solid #000;
            padding: 8px;
            font-size: 12px;
            vertical-align: top;
        }
        /* Badge Status */
        .badge {
            font-weight: bold;
            padding: 3px 8px;
            border-radius: 3px;
            text-transform: uppercase;
            font-size: 10px;
            display: inline-block;
        }
        .bg-warning { background-color: #ffc107; color: #000; }
        .bg-success { background-color: #28a745; color: #fff; }
        .bg-danger { background-color: #dc3545; color: #fff; }

        .footer-note {
            margin-top: 30px;
            font-size: 11px;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header-container">
            <img src="{{ public_path('assets/img/header.png') }}" class="header-img" alt="Header Kop Surat">
        </div>

        <div class="line-separator"></div>

        <div class="report-title">
            <strong>Laporan Data Pengajuan Pengadaan Barang</strong>
            <br>
            <span style="font-size: 13px; text-decoration: none; text-transform: none;">
                Per Tanggal: {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}
            </span>
        </div>

        <table class="table-data">
            <thead>
                <tr>
                    <th width="30">No</th>
                    <th width="25%">Nama Pengadaan</th>
                    <th width="15%">Tanggal</th>
                    <th width="10%">Qty</th>
                    <th width="20%">Lokasi Unit</th>
                    <th width="15%">Status</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td align="center">1</td>
                    <td>
                        <strong>{{ $pengadaan->nama_pengadaan }}</strong>
                        <br>
                        <small style="color: #555;">{!! $pengadaan->deskripsi !!}</small>
                    </td>
                    <td align="center">{{ \Carbon\Carbon::parse($pengadaan->tanggal_pengajuan)->format('d/m/Y') }}</td>
                    <td align="center">{{ $pengadaan->quantity }}</td>
                    <td>{{ $pengadaan->lokasi->nama_lokasi }}</td>
                    <td align="center">
                        @if ($status->status == 'pending')
                            <span class="badge bg-warning">{{ $status->status }}</span>
                        @elseif ($status->status == 'disetujui')
                            <span class="badge bg-success">{{ $status->status }}</span>
                        @else
                            <span class="badge bg-danger">{{ $status->status }}</span>
                        @endif
                    </td>
                </tr>
                
                @if($status->catatan)
                <tr style="background-color: #f9f9f9;">
                    <td colspan="5" align="right"><strong>Catatan:</strong></td>
                    <td>{!! $status->catatan !!}</td>
                </tr>
                @endif
            </tbody>
        </table>

        <div class="footer-note">
            <p>Dokumen ini dicetak otomatis melalui Sistem Informasi Manajemen Aset pada {{ date('d/m/Y H:i') }}</p>
        </div>
    </div>
</body>
</html>