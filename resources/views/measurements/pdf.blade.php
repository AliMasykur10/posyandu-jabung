<!DOCTYPE html>
<html>

<head>
    <title>Laporan Posyandu</title>
    <style>
        body {
            font-family: sans-serif;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 8px;
            text-align: center;
        }

        th {
            background-color: #f2f2f2;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>LAPORAN PERKEMBANGAN ANAK</h2>
        <h3>Posyandu Desa Jabung Sisir</h3>
    </div>

    <p><strong>Nama Anak:</strong> {{ $child->name }}</p>
    <p><strong>Jenis Kelamin:</strong> {{ $child->gender == 'L' ? 'Laki-laki' : 'Perempuan' }}</p>
    <p><strong>Tanggal Lahir:</strong> {{ \Carbon\Carbon::parse($child->birth_date)->format('d F Y') }}</p>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal Timbang</th>
                <th>Berat (kg)</th>
                <th>Tinggi (cm)</th>
                <th>Status Gizi</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($measurements as $index => $m)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($m->measurement_date)->format('d/m/Y') }}</td>
                    <td>{{ $m->weight }}</td>
                    <td>{{ $m->height }}</td>
                    <td>{{ $m->status }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div style="text-align: center; margin-top: 20px;">
        <h4>Grafik Perkembangan Berat Badan</h4>
        <img src="{{ $chartImage }}" style="width: 100%; max-width: 600px; height: auto; border: 1px solid #ddd;">
    </div>
</body>

</html>
