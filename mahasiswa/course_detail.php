<?php
$pageTitle = 'Detail Praktikum';
require_once 'templates/header_mahasiswa.php';
require_once '../config.php';

// Validasi ID praktikum
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<p>ID Praktikum tidak valid.</p>";
    require_once 'templates/footer_mahasiswa.php';
    exit;
}

$praktikum_id = (int)$_GET['id'];
$mahasiswa_id = $_SESSION['user_id'];

// Proses upload laporan
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_laporan'])) {
    $modul_id = (int)$_POST['modul_id'];
    $target_dir = "../uploads/laporan/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    $file_name = uniqid() . '-' . basename($_FILES["file_laporan"]["name"]);
    $target_file = $target_dir . $file_name;
    
    // Pindahkan file yang diupload
    if (move_uploaded_file($_FILES["file_laporan"]["tmp_name"], $target_file)) {
        // Hapus laporan lama jika ada, lalu insert yang baru
        $conn->query("DELETE FROM laporan WHERE modul_id = $modul_id AND mahasiswa_id = $mahasiswa_id");
        $stmt = $conn->prepare("INSERT INTO laporan (modul_id, mahasiswa_id, file_laporan) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $modul_id, $mahasiswa_id, $file_name);
        $stmt->execute();
        $stmt->close();
        echo "<div class='bg-green-100 text-green-800 p-4 rounded-md mb-4'>Laporan berhasil diunggah.</div>";
    } else {
        echo "<div class='bg-red-100 text-red-800 p-4 rounded-md mb-4'>Gagal mengunggah laporan.</div>";
    }
}


// Ambil detail nama praktikum
$stmt = $conn->prepare("SELECT nama_praktikum FROM mata_praktikum WHERE id = ?");
$stmt->bind_param("i", $praktikum_id);
$stmt->execute();
$result = $stmt->get_result();
$praktikum = $result->fetch_assoc();
$pageTitle = $praktikum['nama_praktikum'];
$stmt->close();

?>

<h1 class="text-3xl font-bold text-gray-800 mb-6"><?php echo htmlspecialchars($praktikum['nama_praktikum']); ?></h1>

<div class="bg-white p-6 rounded-xl shadow-md">
    <h3 class="text-2xl font-bold text-gray-800 mb-4">Daftar Modul & Tugas</h3>
    <div class="space-y-6">
        <?php
        $sql_modul = "
            SELECT 
                m.id, m.nama_modul, m.file_materi,
                l.file_laporan, l.nilai, l.feedback, l.tanggal_kumpul
            FROM modul m
            LEFT JOIN laporan l ON m.id = l.modul_id AND l.mahasiswa_id = $mahasiswa_id
            WHERE m.praktikum_id = $praktikum_id
            ORDER BY m.id
        ";
        $result_modul = $conn->query($sql_modul);
        if ($result_modul->num_rows > 0):
            while($modul = $result_modul->fetch_assoc()):
        ?>
        <div class="border border-gray-200 p-4 rounded-lg">
            <h4 class="text-xl font-semibold"><?php echo htmlspecialchars($modul['nama_modul']); ?></h4>
            
            <div class="mt-4 flex flex-col md:flex-row md:items-center gap-4">
                <?php if ($modul['file_materi']): ?>
                <a href="../uploads/materi/<?php echo htmlspecialchars($modul['file_materi']); ?>" download class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded transition-colors duration-300 inline-block text-center">
                    Unduh Materi
                </a>
                <?php endif; ?>

                <form action="" method="post" enctype="multipart/form-data" class="flex-grow">
                    <input type="hidden" name="modul_id" value="<?php echo $modul['id']; ?>">
                    <div class="flex items-center">
                         <input type="file" name="file_laporan" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" required>
                        <button type="submit" name="submit_laporan" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded-full transition-colors duration-300 ml-4">Upload</button>
                    </div>
                </form>
            </div>

            <div class="mt-4 bg-gray-50 p-3 rounded-md">
                <h5 class="font-semibold text-gray-700">Status Laporan Anda:</h5>
                <?php if ($modul['file_laporan']): ?>
                    <p class="text-sm text-gray-600">
                        Terkumpul pada: <?php echo date('d M Y, H:i', strtotime($modul['tanggal_kumpul'])); ?>
                        (<a href="../uploads/laporan/<?php echo htmlspecialchars($modul['file_laporan']); ?>" download class="text-blue-600 hover:underline">Lihat file</a>)
                    </p>
                    <p class="font-bold text-lg mt-2">Nilai: 
                        <span class="<?php echo $modul['nilai'] ? 'text-green-600' : 'text-yellow-600'; ?>">
                            <?php echo $modul['nilai'] ?? 'Belum Dinilai'; ?>
                        </span>
                    </p>
                    <?php if($modul['feedback']): ?>
                         <p class="mt-1 text-sm text-gray-800 bg-gray-200 p-2 rounded"><strong>Feedback Asisten:</strong> <?php echo htmlspecialchars($modul['feedback']); ?></p>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="text-yellow-600 font-semibold">Belum mengumpulkan laporan.</p>
                <?php endif; ?>
            </div>
        </div>
        <?php 
            endwhile;
        else:
        ?>
            <p class="text-gray-500">Belum ada modul yang ditambahkan untuk praktikum ini.</p>
        <?php endif; ?>
    </div>
</div>

<?php
$conn->close();
require_once 'templates/footer_mahasiswa.php';
?>