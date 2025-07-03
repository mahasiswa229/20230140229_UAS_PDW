<?php
$pageTitle = 'Beri Nilai Laporan';
require_once 'templates/header.php';
require_once '../config.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "ID Laporan tidak valid.";
    require_once 'templates/footer.php';
    exit;
}
$laporan_id = (int)$_GET['id'];

// Proses simpan nilai
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_nilai'])) {
    $nilai = $_POST['nilai'];
    $feedback = $_POST['feedback'];
    
    $stmt = $conn->prepare("UPDATE laporan SET nilai = ?, feedback = ? WHERE id = ?");
    $stmt->bind_param("isi", $nilai, $feedback, $laporan_id);
    if($stmt->execute()){
        echo "<div class='bg-green-100 text-green-800 p-4 rounded-md mb-4'>Nilai berhasil disimpan.</div>";
    } else {
        echo "<div class='bg-red-100 text-red-800 p-4 rounded-md mb-4'>Gagal menyimpan nilai.</div>";
    }
    $stmt->close();
}


// Ambil detail laporan
$sql = "
    SELECT 
        l.*,
        u.nama AS nama_mahasiswa, u.email,
        m.nama_modul,
        mp.nama_praktikum
    FROM laporan l
    JOIN users u ON l.mahasiswa_id = u.id
    JOIN modul m ON l.modul_id = m.id
    JOIN mata_praktikum mp ON m.praktikum_id = mp.id
    WHERE l.id = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $laporan_id);
$stmt->execute();
$result = $stmt->get_result();
$laporan = $result->fetch_assoc();

if (!$laporan) {
    echo "Laporan tidak ditemukan.";
    require_once 'templates/footer.php';
    exit;
}
?>

<h1 class="text-2xl font-bold text-gray-800 mb-2">Detail Laporan</h1>
<a href="manage_reports.php" class="text-blue-500 hover:underline mb-6 block">&larr; Kembali ke Daftar Laporan</a>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-1 bg-white p-6 rounded-lg shadow-md">
        <h3 class="font-bold text-lg mb-4">Informasi Pengumpulan</h3>
        <div class="space-y-2 text-sm">
            <p><strong>Mahasiswa:</strong> <?php echo htmlspecialchars($laporan['nama_mahasiswa']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($laporan['email']); ?></p>
            <p><strong>Praktikum:</strong> <?php echo htmlspecialchars($laporan['nama_praktikum']); ?></p>
            <p><strong>Modul:</strong> <?php echo htmlspecialchars($laporan['nama_modul']); ?></p>
            <p><strong>Tanggal Kumpul:</strong> <?php echo date('d M Y, H:i', strtotime($laporan['tanggal_kumpul'])); ?></p>
        </div>
        <hr class="my-4">
        <a href="../uploads/laporan/<?php echo htmlspecialchars($laporan['file_laporan']); ?>" download class="w-full text-center bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition-colors duration-300 inline-block">
            Unduh File Laporan
        </a>
    </div>

    <div class="lg:col-span-2 bg-white p-6 rounded-lg shadow-md">
        <h3 class="font-bold text-lg mb-4">Form Penilaian</h3>
        <form action="grade_report.php?id=<?php echo $laporan_id; ?>" method="POST">
            <div class="mb-4">
                <label for="nilai" class="block text-gray-700 font-medium">Nilai (0-100)</label>
                <input type="number" name="nilai" id="nilai" min="0" max="100" value="<?php echo htmlspecialchars($laporan['nilai']); ?>" class="w-full mt-1 px-3 py-2 border rounded-md" required>
            </div>
            <div class="mb-4">
                <label for="feedback" class="block text-gray-700 font-medium">Feedback / Catatan</label>
                <textarea name="feedback" id="feedback" rows="5" class="w-full mt-1 px-3 py-2 border rounded-md"><?php echo htmlspecialchars($laporan['feedback']); ?></textarea>
            </div>
            <button type="submit" name="submit_nilai" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded">
                Simpan Nilai & Feedback
            </button>
        </form>
    </div>
</div>

<?php
$stmt->close();
$conn->close();
require_once 'templates/footer.php';
?>