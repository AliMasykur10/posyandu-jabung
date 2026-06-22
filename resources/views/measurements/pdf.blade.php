<!DOCTYPE html>
<html>

<head>
    <title>Laporan Posyandu</title>
    <style>
        body {
            font-family: sans-serif;
            color: #333;
            line-height: 1.4;
        }

        .header {
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }

        .header h2 {
            margin: 0;
            font-size: 20px;
        }

        .header h3 {
            margin: 5px 0 0 0;
            font-size: 16px;
            font-weight: normal;
        }

        .biodata {
            margin-bottom: 20px;
            font-size: 13px;
        }

        .biodata table {
            width: auto;
            margin-top: 0;
        }

        .biodata td {
            border: none;
            text-align: left;
            padding: 3px 10px 3px 0;
        }

        /* Desain Tabel Utama Laporan */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 11px;
            /* Diperkecil sedikit agar 9 kolom muat dengan rapi */
        }

        .data-table th,
        .data-table td {
            border: 1px solid #666;
            padding: 6px 4px;
            text-align: center;
            vertical-align: middle;
        }

        .data-table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .intervensi-list {
            text-align: left;
            font-size: 10px;
            margin: 0;
            padding-left: 5px;
            list-style-type: none;
        }

        .chart-section {
            text-align: center;
            margin-top: 30px;
            page-break-inside: avoid;
            /* Mencegah grafik terpotong antar halaman */
        }

        .chart-section h4 {
            margin-bottom: 10px;
            font-size: 14px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>LAPORAN PERKEMBANGAN ANAK</h2>
        <h3>Posyandu Desa Jabung Sisir</h3>
    </div>

    <div class="biodata">
        <table>
            <tr>
                <td><strong>Nama Anak</strong></td>
                <td>: {{ $child->name }}</td>
            </tr>
            <tr>
                <td><strong>Jenis Kelamin</strong></td>
                <td>: {{ $child->gender == 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
            </tr>
            <tr>
                <td><strong>Tanggal Lahir</strong></td>
                <td>: {{ \Carbon\Carbon::parse($child->birth_date)->translatedFormat('d F Y') }}</td>
            </tr>
        </table>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal Timbang</th>
                <th>Usia</th>
                <th>Berat</th>
                <th>Tinggi</th>
                <th>L. Kepala</th>
                <th>LiLA</th>
                <th>Intervensi Tambahan</th>
                <th>Status Berat Badan (BB/U)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($measurements as $index => $m)
                @php
                    // Hitung umur secara dinamis untuk baris cetak PDF
                    $birthDate = \Carbon\Carbon::parse($child->birth_date);
                    $checkDate = \Carbon\Carbon::parse($m->measurement_date);
                    $ageInMonths = floor($birthDate->diffInMonths($checkDate));
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($m->measurement_date)->format('d/m/Y') }}</td>
                    <td style="font-weight: bold; color: #1d4ed8;">{{ $ageInMonths }} Bulan</td>
                    <td>{{ $m->weight }} kg</td>
                    <td>{{ $m->height }} cm</td>

                    <td>{{ $m->head_circumference ? $m->head_circumference . ' cm' : '-' }}</td>
                    <td>{{ $m->arm_circumference ? $m->arm_circumference . ' cm' : '-' }}</td>

                    <td>
                        @php $hasIntervensi = false; @endphp
                        <ul class="intervensi-list">
                            @if ($m->vitamin_a)
                                <li><strong>Vit:</strong> {{ $m->vitamin_a }}</li>
                                @php $hasIntervensi = true; @endphp
                            @endif
                            @if ($m->deworming_medicine)
                                <li><strong>Obat Cacing:</strong> Ya</li>
                                @php $hasIntervensi = true; @endphp
                            @endif
                            @if ($m->pmt_status)
                                <li><strong>PMT:</strong> {{ $m->pmt_status }}</li>
                                @php $hasIntervensi = true; @endphp
                            @endif

                            @if (!$hasIntervensi)
                                <li style="text-align: center;">-</li>
                            @endif
                        </ul>
                    </td>

                    <td style="font-weight: bold;">{{ $m->status ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="chart-section">
        <h4>Grafik Perkembangan Tumbuh Kembang Balita</h4>
        <img src="{{ $chartImage }}" style="width: 100%; max-width: 650px; height: auto; border: 1px solid #ddd;">
    </div>
</body>

</html>
