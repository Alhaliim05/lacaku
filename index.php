<?php
session_start(); // Memulai sesi untuk menyimpan data hasil pencarian

// Jika form dikirim
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $noPermohonan = htmlspecialchars($_POST["noPermohonan"]);
    $url = "https://sicantik.go.id/api/TemplateData/keluaran/44143.json";  // URL API sesuai dengan noPermohonan

    // Inisialisasi CURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url . '?no_permohonan=' . urlencode($noPermohonan));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode != 200 || !$response) {
        $_SESSION['result'] = "<div class='error'>Permohonan tidak ditemukan atau terjadi kesalahan saat mengakses API.</div>";
    } else {
        $data = json_decode($response, true);

        // Memeriksa apakah data ada dan array data tidak kosong
        if ($data && isset($data['data']['data'])) {
            $result = $data['data']['data'];

            // Menyusun hasil proses berdasarkan status dan nama proses
            $jenisProsesList = '';
            foreach ($result as $proses) {
                $namaProses = isset($proses['nama_proses']) ? $proses['nama_proses'] : 'Tidak tersedia';
                $status = isset($proses['status']) ? $proses['status'] : 'Tidak tersedia';

                // Menambahkan status proses dalam hasil
                $jenisProsesList .= "<li><strong>{$namaProses}</strong> - Status: {$status}</li>";
            }

            // Menyimpan data hasil pencarian ke dalam session
            $_SESSION['result'] = "<div class='result-box'>
                <h2>Hasil Pencarian:</h2>
                <ul>$jenisProsesList</ul>
            </div>";
        } else {
            $_SESSION['result'] = "<div class='error'>Data tidak ditemukan.</div>";
        }
    }

    // Redirect untuk menerapkan PRG dan mencegah ulang form submission
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Tampilkan hasil pencarian setelah redirect
$result = isset($_SESSION['result']) ? $_SESSION['result'] : '';
unset($_SESSION['result']); // Hapus data session setelah ditampilkan
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tracking Permohonan SiCantik Cloud</title>
    <style>
        body {
    font-family: Arial, sans-serif;
    background: linear-gradient(to right, #6a11cb, #2575fc);
    color: #fff;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    margin: 0;
}

.container {
    background-color: rgba(255, 255, 255, 0.1);
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.5);
    width: 100%;
    max-width: 500px;
    animation: fadeIn 2s ease-in-out;
    text-align: left; /* Perubahan di sini: agar hasil pencarian rata kiri */
}

h1 {
    font-size: 2em;
    margin-bottom: 20px;
    text-align: center;
}

form {
    display: flex;
    flex-direction: column;
    gap: 10px;
    text-align: left; /* Perubahan di sini: agar form berada di kiri */
}

input[type="text"] {
    padding: 10px;
    border-radius: 5px;
    border: none;
    font-size: 1em;
    outline: none;
    transition: all 0.3s ease;
}

input[type="text"]:focus {
    box-shadow: 0 0 10px #fff;
}

.btn {
    padding: 10px;
    border: none;
    border-radius: 5px;
    background-color: #2575fc;
    color: #fff;
    font-size: 1em;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.btn:hover {
    background-color: #6a11cb;
}

.result-box {
    margin-top: 20px;
    padding: 15px;
    border-radius: 5px;
    background-color: rgba(255, 255, 255, 0.2);
    animation: slideIn 1s ease-out;
}

.error {
    color: #ff6666;
    margin-top: 10px;
}

.loading {
    display: none;
    margin-top: 20px;
}

.loading img {
    width: 50px;
    animation: spin 2s linear infinite;
}

ul {
    list-style-type: none; /* Menghilangkan bullet points pada list */
    padding-left: 0;
}

ul li {
    margin-bottom: 10px;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: scale(0.9);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

    </style>
</head>
<body>
    <div class="container">
        <h1>Lacak Permohonan KKPR Nonberusaha Sicantik</h1>
        <form method="post" id="trackingForm">
            <label for="noPermohonan">Masukkan dibawah ini nomor surat pengantar kantor pertanahan yang diterbitkan oleh aplikasi sicantik:</label>
            <input type="text" id="noPermohonan" name="noPermohonan" required>
            <button type="submit" class="btn">Lacak Permohonan</button>
        </form>

        <div class="loading" id="loading">
            <img src="https://i.imgur.com/llF5iyg.gif" alt="Loading...">
            <p>Mencari data...</p>
        </div>

        <div id="result">
            <?php echo $result; ?>
        </div>
    </div>

    <script>
        document.getElementById("trackingForm").onsubmit = function() {
            document.getElementById("loading").style.display = "block";
            document.getElementById("result").innerHTML = "";
        };
    </script>
</body>
</html>
