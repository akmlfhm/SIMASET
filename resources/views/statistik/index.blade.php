@extends('layouts.main')

@section('content')
<style>
    .chart-container { position: relative; height: 300px; width: 100%; }
    .card { border-radius: 15px; border: none; box-shadow: 0 4px 12px rgba(0,0,0,0.05); transition: 0.3s; }
    .card:hover { box-shadow: 0 8px 15px rgba(0,0,0,0.1); }
    .badge-label { width: 12px; height: 12px; border-radius: 3px; display: inline-block; margin-right: 8px; }
</style>

<div class="container-fluid p-0">
    <h1 class="h3 mb-4 fw-bold text-dark">Ringkasan Statistik Aset</h1>

    <div class="row">
        <div class="col-xl-8 col-lg-7">
            <div class="card mb-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-bold">Tren Penambahan Aset Tahunan</h5>
                    <span class="badge bg-soft-success text-success">{{ date('Y') }}</span>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="chartjs-dashboard-line"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-lg-5">
            <div class="card mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0 fw-bold">Sebaran Per Lokasi</h5>
                </div>
                <div class="card-body">
                    <div style="height: 200px;">
                        <canvas id="chartjs-dashboard-pie-lokasi"></canvas>
                    </div>
                    <div class="mt-4" style="max-height: 120px; overflow-y: auto;">
                        <table class="table table-sm table-borderless">
                            <tbody>
                                @foreach ($lokasi as $l)
                                <tr>
                                    <td><i class="align-middle text-success me-2" data-feather="map-pin"></i> {{ $l->nama_lokasi }}</td>
                                    <td class="text-end fw-bold">{{ $l->total }} <span class="text-muted small">Aset</span></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-5">
            <div class="card mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0 fw-bold">Kategori Aset</h5>
                </div>
                <div class="card-body">
                    <div style="height: 220px;">
                        <canvas id="chartjs-dashboard-pie"></canvas>
                    </div>
                    <div class="mt-3">
                        <div class="row small g-2">
                            @foreach ($kategori as $k)
                            <div class="col-6 text-muted">
                                <i class="align-middle me-1" data-feather="tag" style="width:12px"></i> {{ $k->nama }}
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

		<div class="col-md-7">
			<div class="card mb-4"> <div class="card-header bg-white py-3">
					<h5 class="card-title mb-0 fw-bold text-dark">Nilai Valuasi Aset (IDR)</h5>
				</div>
				<div class="card-body">
					<div class="chart-container" style="height: 280px;">
						<canvas id="chartjs-line"></canvas>
					</div>
				</div>
			</div>
		</div>
    </div>
</div>

{{-- Scripts tetap menggunakan logika Anda namun dengan penyesuaian warna --}}
<script>
    document.addEventListener("DOMContentLoaded", function() {
		const primaryColor = "#3b7ddd"; // Ini warna biru default AdminKit
		const successColor = "#28a745";
        const warningColor = "#ffc107";
        const dangerColor = "#dc3545";
        const infoColor = "#17a2b8";

        // LINE CHART: Penambahan Aset
        new Chart(document.getElementById("chartjs-dashboard-line"), {
            type: "{{ $chart->type }}",
            data: {
                labels: {!! json_encode($chart->labels) !!},
                datasets: [{
                    label: "Jumlah Aset",
                    data: {!! json_encode($chart->data) !!},
                    fill: true,
                    backgroundColor: "rgba(0, 125, 67, 0.1)",
                    borderColor: primaryColor,
                    tension: 0.4,
                    pointRadius: 4
                }]
            },
            options: { maintainAspectRatio: false, plugins: { legend: { display: false } } }
        });

        // PIE CHART: Kategori
        new Chart(document.getElementById("chartjs-dashboard-pie"), {
            type: "doughnut", // Mengubah pie menjadi doughnut agar lebih modern
            data: {
                labels: {!! json_encode($pieChart->labels) !!},
                datasets: [{
                    data: {!! json_encode($pieChart->data) !!},
                    backgroundColor: [primaryColor, warningColor, dangerColor, infoColor, "#6c757d"],
                    borderWidth: 0
                }]
            },
            options: { cutout: "70%", maintainAspectRatio: false, plugins: { legend: { display: false } } }
        });

        // PIE CHART: Lokasi
        new Chart(document.getElementById("chartjs-dashboard-pie-lokasi"), {
            type: "pie",
            data: {
                labels: {!! json_encode($lokasiChart->labels) !!},
                datasets: [{
                    data: {!! json_encode($lokasiChart->data) !!},
                    backgroundColor: [successColor, "#20c997", primaryColor, "#82d399"],
                    borderWidth: 2,
                    borderColor: "#fff"
                }]
            },
            options: { maintainAspectRatio: false, plugins: { legend: { display: false } } }
        });

        // LINE CHART: Nilai Harga (Warna Putih karena Background Hijau)
        new Chart(document.getElementById("chartjs-line"), {
            type: "line",
            data: {
                labels: {!! json_encode($keuanganChart->labels) !!},
                datasets: [{
                    label: "Nilai Aset",
                    data: {!! json_encode($keuanganChart->data) !!},
                    fill: false,
                    borderColor: "#ffffff",
                    pointBackgroundColor: "#ffffff",
                    borderWidth: 3,
                    tension: 0.1
                }]
            },
            options: {
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { ticks: { color: "#fff" }, grid: { color: "rgba(255,255,255,0.1)" } },
                    x: { ticks: { color: "#fff" }, grid: { display: false } }
                }
            }
        });
    });
</script>
@endsection