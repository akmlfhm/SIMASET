<!DOCTYPE html>
<html>
<head>
    <title>Label Barang - {{ $barang->kode_barang }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            margin: 0;
            padding: 0;
        }
        .label-card {
            width: 500px; /* Sesuaikan dengan ukuran stiker Anda */
            height: auto;
            border: 2px solid #007d43; /* Hijau khas Muhammadiyah */
            border-radius: 10px;
            overflow: hidden;
            position: relative;
        }
        /* Header Label */
        .label-header {
            background-color: #007d43;
            color: white;
            padding: 8px 15px;
            text-align: center;
        }
        .label-header h2 {
            margin: 0;
            font-size: 18px;
            letter-spacing: 2px;
            text-transform: uppercase;
        }
        .label-header p {
            margin: 0;
            font-size: 10px;
            opacity: 0.9;
        }
        /* Body Label */
        .label-body {
            display: table;
            width: 100%;
            background: #fff;
        }
        .logo-section {
            display: table-cell;
            width: 25%;
            vertical-align: middle;
            text-align: center;
            padding: 10px;
            border-right: 1px solid #eee;
        }
        .info-section {
            display: table-cell;
            width: 50%;
            vertical-align: middle;
            padding: 10px 15px;
        }
        .qr-section {
            display: table-cell;
            width: 25%;
            vertical-align: middle;
            text-align: center;
            padding: 10px;
            border-left: 1px solid #eee;
        }
        /* Typography */
        .label-item {
            margin-bottom: 6px;
        }
        .label-item .title {
            font-size: 9px;
            color: #888;
            text-transform: uppercase;
            display: block;
            margin-bottom: 1px;
        }
        .label-item .value {
            font-size: 13px;
            font-weight: bold;
            color: #333;
            display: block;
        }
        .kode-highlight {
            font-size: 16px !important;
            color: #007d43 !important;
        }
    </style>
</head>
<body>

    <div class="label-card">
        <div class="label-header">
            <h2>SIMASET</h2>
            <p>Pimpinan Wilayah Muhammadiyah Jawa Tengah</p>
        </div>

        <div class="label-body">
            <div class="logo-section">
                <img src="data:image/png;base64,{{ $logoInstansi }}" style="width: 75px; height: auto;">
            </div>

            <div class="info-section">
                <div class="label-item">
                    <span class="title">Kode Barang</span>
                    <span class="value kode-highlight">{{ $barang->kode_barang }}</span>
                </div>
                <div class="label-item">
                    <span class="title">Nama Aset</span>
                    <span class="value">{{ str($barang->nama)->limit(25) }}</span>
                </div>
                <div class="label-item">
                    <span class="title">Lokasi</span>
                    <span class="value">{{ $barang->lokasi->nama_lokasi }}</span>
                </div>
                <div class="label-item" style="margin-bottom: 0;">
                    <span class="title">Tanggal Input</span>
                    <span class="value">{{ date('d M Y', strtotime($barang->tanggal)) }}</span>
                </div>
            </div>

            <div class="qr-section">
                <img src="data:image/png;base64,{{ $qrCode }}" style="width: 90px; height: auto;">
                <p style="font-size: 8px; margin-top: 5px; color: #666;">Scan untuk Detail</p>
            </div>
        </div>
    </div>

</body>
</html>