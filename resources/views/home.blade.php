@extends('layouts.main')
{{-- Kalender --}}
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Cek apakah elemen ada untuk menghindari error JS
        var dateElement = document.getElementById("datetimepicker-dashboard");
        
        if (dateElement) {
            var date = new Date();
            var defaultDate = date.getFullYear() + "-" + (date.getMonth() + 1) + "-" + date.getDate();
            
            // Inisialisasi Flatpickr
            flatpickr(dateElement, {
                inline: true,
                prevArrow: "<span title=\"Previous month\">&laquo;</span>",
                nextArrow: "<span title=\"Next month\">&raquo;</span>",
                defaultDate: defaultDate,
                // Tambahkan locale Indonesia jika perlu
                monthSelectorType: "static",
            });
        } else {
            console.error("Elemen kalender tidak ditemukan!");
        }
    });
</script>
@section('content')
<style>
    /* Custom Styling untuk Dashboard Muhammadiyah */
    .stat-card {
        transition: transform 0.3s ease;
        border: none;
        border-radius: 15px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    }
    .stat-card:hover {
        transform: translateY(-5px);
    }
    .bg-muhammadiyah {
        background: linear-gradient(135deg, #007d43 0%, #00a65a 100%);
        color: white;
    }
    .stat-icon-circle {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .bg-soft-green { background-color: rgba(0, 125, 67, 0.1); color: #007d43; }
    .bg-soft-blue { background-color: rgba(0, 123, 255, 0.1); color: #007bff; }
    .bg-soft-orange { background-color: rgba(255, 152, 0, 0.1); color: #ff9800; }
    .bg-soft-purple { background-color: rgba(156, 39, 176, 0.1); color: #9c27b0; }
</style>

<div class="container-fluid p-0">
    <div class="row mb-4">
        <div class="col-auto">
            <h1 class="h3 mb-0"><strong>Dashboard</strong> </h1>
            <p class="text-muted">Selamat datang kembali, {{ $users->name }} ({{ $users->roles }})</p>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted mb-1 fw-bold">TOTAL ASET</p>
                            <h2 class="mb-0">{{ $countBarang }}</h2>
                        </div>
                        <div class="stat-icon-circle bg-soft-green">
                            <i data-feather="database"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted mb-1 fw-bold">LOKASI</p>
                            <h2 class="mb-0">{{ $countLokasi }}</h2>
                        </div>
                        <div class="stat-icon-circle bg-soft-blue">
                            <i data-feather="map-pin"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted mb-1 fw-bold">KATEGORI</p>
                            <h2 class="mb-0">{{ $countKategori }}</h2>
                        </div>
                        <div class="stat-icon-circle bg-soft-orange">
                            <i data-feather="tag"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted mb-1 fw-bold">USER AKTIF</p>
                            <h2 class="mb-0">{{ $countUsers }}</h2>
                        </div>
                        <div class="stat-icon-circle bg-soft-purple">
                            <i data-feather="users"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-2">
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0 fw-bold">Grafik Tren Pengadaan Aset</h5>
                </div>
                <div class="card-body">
                    <div class="chart chart-sm" style="height: 300px;">
                        <canvas id="chartjs-dashboard-line"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-lg-5">
            <div class="card shadow-sm border-0 mb-4" style="border-radius: 15px;">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0 fw-bold">Kalender</h5>
                </div>
                <div class="card-body d-flex">
                    <div class="align-self-center w-100">
                        <div id="datetimepicker-dashboard"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Script tetap sama namun disesuaikan warnanya --}}
<script>
    document.addEventListener("DOMContentLoaded", function() {
        var ctx = document.getElementById("chartjs-dashboard-line").getContext("2d");
        var gradient = ctx.createLinearGradient(0, 0, 0, 225);
        gradient.addColorStop(0, "rgba(0, 125, 67, 0.2)"); // Hijau transparan
        gradient.addColorStop(1, "rgba(0, 125, 67, 0)");

        new Chart(document.getElementById("chartjs-dashboard-line"), {
            type: "{{ $chart->type }}",
            data: {
                labels: {!! json_encode($chart->labels) !!},
                datasets: [{
                    label: "Aset Masuk",
                    data: {!! json_encode($chart->data) !!},
                    fill: true,
                    backgroundColor: gradient,
                    borderColor: "#007d43", // Warna Hijau Muhammadiyah
                    borderWidth: 3,
                    pointBackgroundColor: "#fff",
                    pointBorderColor: "#007d43",
                    pointHoverRadius: 5,
                    tension: 0.3
                }]
            },
            options: {
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false } },
                    y: { 
                        beginAtZero: true,
                        ticks: { stepSize: 1 } 
                    }
                }
            }
        });
    });
</script>
@endsection