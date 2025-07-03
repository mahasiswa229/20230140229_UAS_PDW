<?php
$pageTitle = 'Manajemen Modul';
require_once 'templates/header.php';
require_once '../config.php';

if (!isset($_GET['course_id']) || !is_numeric($_GET['course_id'])) {
    echo "ID Praktikum tidak valid.";
    require_once 'templates/footer.php';
    exit;
}
$praktikum_id = (int)$_GET['course_id'];

// Logika CUD
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_modul = $_POST['nama_modul'];

    // Create / Update
    if (isset($_POST['save'])) {
        $modul_id = $_POST['modul_id'];
        $file_materi = $_FILES['file_materi'];
        $existing_file = $_POST['existing_file'];
        $file_name = $existing_file;

        if ($file_materi['name']) {
            $target_dir = "../uploads/materi/";
            if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
            $file_name = uniqid() . '-' . basename($file_materi["name"]);
            move_uploaded_file($file_materi["tmp_name"], $target_dir . $file_name);
        }

        if (empty($modul_id)) { // Create
            $stmt = $conn->prepare("INSERT INTO modul (praktikum_id, nama_modul, file_materi) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $praktikum_id, $nama_modul, $file_name);
        } else { // Update
            $stmt = $conn->prepare("UPDATE modul SET nama_modul = ?, file_materi = ? WHERE id = ?");
            $stmt->bind_param("ssi", $nama_modul, $file_name, $modul_id);
        }
        $stmt->execute();
        $stmt->close();
    }
    // Delete
    if (isset($_POST['delete'])) {
        $modul_id = $_POST['modul_id'];
        // Hapus file fisik jika ada
        $file_to_delete = $conn->query("SELECT file_materi FROM modul WHERE id=$modul_id")->fetch_assoc()['file_materi'];
        if ($file_to_delete && file_exists("../uploads/materi/" . $file_to_delete)) {
            unlink("../uploads/materi/" . $file_to_delete);
        }
        $conn->query("DELETE FROM modul WHERE id = $modul_id");
    }
}

// Data untuk form edit
$edit_data = ['id' => '', 'nama_modul' => '', 'file_materi' => ''];
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $edit_data = $conn->query("SELECT * FROM modul WHERE id = $edit_id")->fetch_assoc();
}

// Nama praktikum untuk judul
$course_name = $conn->query("SELECT nama_praktikum FROM mata_praktikum WHERE id = $praktikum_id")->fetch_assoc()['nama_praktikum'];
?>

<h1 class="text-2xl font-bold text-gray-800 mb-2">Kelola Modul untuk: <?php echo htmlspecialchars($course_name); ?></h1>
<a href="manage_courses.php" class="text-blue-500 hover:underline mb-6 block">&larr; Kembali ke Daftar Praktikum</a>

<div class="bg-white p-6 rounded-lg shadow-md mb-8">
    <h3 class="text-xl font-bold mb-4"><?php echo empty($edit_data['id']) ? 'Tambah Modul Baru' : 'Edit Modul'; ?></h3>
    <form action="manage_modules.php?course_id=<?php echo $praktikum_id; ?>" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="modul_id" value="<?php echo $edit_data['id']; ?>">
        <input type="hidden" name="existing_file" value="<?php echo $edit_data['file_materi']; ?>">
        <div class="mb-4">
            <label for="nama_modul" class="block text-gray-700">Nama Modul</label>
            <input type="text" name="nama_modul" value="<?php echo htmlspecialchars($edit_data['nama_modul']); ?>" class="w-full px-3 py-2 border rounded" required>
        </div>
        <div class="mb-4">
            <label for="file_materi" class="block text-gray-700">File Materi (PDF/DOCX)</label>
            <input type="file" name="file_materi" class="w-full">
            <?php if($edit_data['file_materi']): ?>
            <p class="text-sm text-gray-500 mt-1">File saat ini: <?php echo htmlspecialchars($edit_data['file_materi']); ?>. Kosongkan jika tidak ingin mengubah file.</p>
            <?php endif; ?>
        </div>
        <button type="submit" name="save" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Simpan Modul</button>
    </form>
</div>

<div class="bg-white p-6 rounded-lg shadow-md">
    <h3 class="text-xl font-bold mb-4">Daftar Modul</h3>
    <table class="min-w-full bg-white">
         <thead class="bg-gray-800 text-white">
            <tr>
                <th class="py-2 px-4">Nama Modul</th>
                <th class="py-2 px-4">File Materi</th>
                <th class="py-2 px-4">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $result = $conn->query("SELECT * FROM modul WHERE praktikum_id = $praktikum_id ORDER BY id");
            while ($row = $result->fetch_assoc()):
            ?>
            <tr class="border-b">
                <td class="py-2 px-4"><?php echo htmlspecialchars($row['nama_modul']); ?></td>
                <td class="py-2 px-4">
                    <?php if($row['file_materi']): ?>
                    <a href="../uploads/materi/<?php echo $row['file_materi']; ?>" download class="text-blue-500 hover:underline"><?php echo htmlspecialchars($row['file_materi']); ?></a>
                    <?php else: echo 'Tidak ada'; endif; ?>
                </td>
                <td class="py-2 px-4 flex gap-2">
                    <a href="manage_modules.php?course_id=<?php echo $praktikum_id; ?>&edit=<?php echo $row['id']; ?>" class="bg-yellow-500 text-white p-2 rounded text-xs">Edit</a>
                     <form action="manage_modules.php?course_id=<?php echo $praktikum_id; ?>" method="POST" onsubmit="return confirm('Yakin ingin menghapus?');">
                        <input type="hidden" name="modul_id" value="<?php echo $row['id']; ?>">
                        <button type="submit" name="delete" class="bg-red-500 text-white p-2 rounded text-xs">Hapus</button>
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php
$conn->close();
require_once 'templates/footer.php';
?>