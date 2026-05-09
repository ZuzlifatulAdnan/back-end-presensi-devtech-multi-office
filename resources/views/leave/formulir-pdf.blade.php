<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulir Permohonan Cuti</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
            color: #000;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }

        .header h1 {
            font-size: 16px;
            font-weight: bold;
            margin: 0;
            line-height: 1.2;
        }

        .header p {
            margin: 5px 0;
            font-size: 12px;
        }

        .content {
            margin: 20px 0;
        }

        .field-row {
            margin: 8px 0;
            display: flex;
        }

        .field-label {
            width: 200px;
            display: inline-block;
        }

        .field-value {
            font-weight: bolder;
        }

        .paragraph {
            margin: 15px 0;
            text-align: justify;
        }

        .signature-section {
            margin-top: 12px;
            display: table;
            width: 100%;
            table-layout: fixed;
            border: #000;

        }

        .signature-box {
            display: table-cell;
            text-align: center;
            width: 33.33%;
            vertical-align: top;
            padding: 0 10px;
        }

        .signature-name {
            margin-top: 80px;
            font-weight: bold;
            text-decoration: underline;
            font-size: 12px;

        }

        .date {
            text-align: left;
            /* margin: 30px 0 0px 0; */
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>PERMOHONAN PENGAMBILAN CUTI</h1>
        <h1>{{ $company }}</h1>
        <p>Jl {{ $address_company }}</p>
    </div>

    <div class="content">
        <p>Yth. HRD {{ $company }}<br>
            di tempat</p>

        <p>Dengan hormat,<br>
            yang bertanda tangan di bawah ini:</p>

        <div class="field-row">
            <span class="field-label">Nama</span>
            <span>: <span class="field-value">{{ $nama }}</span></span>
        </div>

        <div class="field-row">
            <span class="field-label">Jabatan</span>
            <span>: <span class="field-value">{{ $jabatan }}</span></span>
        </div>

        <div class="field-row">
            <span class="field-label">Tanggal Pengambilan Cuti</span>
            <span>: <span class="field-value">{{ $hari }}</span></span>
        </div>

        <div class="field-row">
            <span class="field-label">Keperluan</span>
            <span>: <span class="field-value">{{ $keperluan }}</span></span>
        </div>

        <div class="paragraph">
            Bermaksud mengajukan selama 1 hari, pada tanggal
            <strong>{{ $hari }}</strong>.
        </div>

        <div class="paragraph">
            Demikian permohonan cuti ini saya ajukan. Terimakasih atas perhatian Bapak/Ibu.
        </div>

        <div class="date">
            <p>Tanggal {{ $tanggal_permohonan }}</p>
        </div>

        <div class="signature-section">
            <div class="signature-box">
                <p>Pemohon</p>
                <div class="signature-name">{{ strtoupper($nama) }}</div>
            </div>
            <div class="signature-box">
                <p>Menyetujui</p>
                <div class="signature-name">{{ $menyetujui_leave }}</div>
            </div>
            <div class="signature-box">
                <p>Mengetahui</p>
                <div class="signature-name">{{ $mengetahui_leave }}</div>
            </div>
        </div>
    </div>
</body>

</html>
