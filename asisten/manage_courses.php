<?php
$pageTitle = 'Manajemen Praktikum';
$activePage = 'courses'; // Anda mungkin perlu menambahkan ini di navigasi header asisten
require_once 'templates/header.php';
require_once '../config.php';

// Logika untuk CUD (Create, Update, Delete)
$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Tambah atau Update Data
    if (isset($_POST['save'])) {
        $id = $_POST['id'];
        $nama = $_POST['nama_praktikum'];
        $deskripsi = $_POST['deskripsi'];

        if (empty($id)) { // Create
            $stmt = $conn->prepare("INSERT INTO mata_praktikum (nama_praktikum, deskripsi) VALUES (?, ?)");
            $stmt->bind_param("ss", $nama, $deskripsi);
            $message = "Praktikum berhasil ditambahkan.";
        } else { // Update
            $stmt = $conn->prepare("UPDATE mata_praktikum SET nama_praktikum = ?, deskripsi = ? WHERE id = ?");
            $stmt->bind_param("ssi", $nama, $deskripsi, $id);
            $message = "Praktikum berhasil diperbarui.";
        }
        $stmt->execute();
        $stmt->close();
    }
    // Hapus Data
    if (isset($_POST['delete'])) {
        $id = $_POST['id'];
        $stmt = $conn->prepare("DELETE FROM mata_praktikum WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        $message = "Praktikum berhasil dihapus.";
    }
}

// Logika untuk Read (mengambil data untuk diedit)
$edit_data = ['id' => '', 'nama_praktikum' => '', 'deskripsi' => ''];
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM mata_praktikum WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_data = $result->fetch_assoc();
    $stmt->close();
}
?>

<?php if ($message): ?>
<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
    <span class="block sm:inline"><?php echo $message; ?></span>
</div>
<?php endif; ?>

<div class="bg-white p-6 rounded-lg shadow-md mb-8">
    <h3 class="text-xl font-bold mb-4"><?php echo empty($edit_data['id']) ? 'Tambah Praktikum Baru' : 'Edit Praktikum'; ?></h3>
    <form action="manage_courses.php" method="POST">
        <input type="hidden" name="id" value="<?php echo $edit_data['id']; ?>">
        <div class="mb-4">
            <label for="nama_praktikum" class="block text-gray-700">Nama Praktikum</label>
            <input type="text" name="nama_praktikum" id="nama_praktikum" value="<?php echo htmlspecialchars($edit_data['nama_praktikum']); ?>" class="w-full px-3 py-2 border rounded" required>
        </div>
        <div class="mb-4">
            <label for="deskripsi" class="block text-gray-700">Deskripsi</label>
            <textarea name="deskripsi" id="deskripsi" rows="3" class="w-full px-3 py-2 border rounded"><?php echo htmlspecialchars($edit_data['deskripsi']); ?></textarea>
        </div>
        <button type="submit" name="save" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Simpan</button>
        <?php if (!empty($edit_data['id'])): ?>
            <a href="manage_courses.php" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">Batal Edit</a>
        <?php endif; ?>
    </form>
</div>

<div class="bg-white p-6 rounded-lg shadow-md">
    <h3 class="text-xl font-bold mb-4">Daftar Mata Praktikum</h3>
    <table class="min-w-full bg-white">
        <thead class="bg-gray-800 text-white">
            <tr>
                <th class="py-3 px-4 uppercase font-semibold text-sm">Nama Praktikum</th>
                <th class="py-3 px-4 uppercase font-semibold text-sm">Deskripsi</th>
                <th class="py-3 px-4 uppercase font-semibold text-sm">Aksi</th>
            </tr>
        </thead>
        <tbody class="text-gray-700">
            <?php
            $result = $conn->query("SELECT * FROM mata_praktikum ORDER BY nama_praktikum");
            while ($row = $result->fetch_assoc()):
            ?>
            <tr class="border-b">
                <td class="py-3 px-4"><?php echo htmlspecialchars($row['nama_praktikum']); ?></td>
                <td class="py-3 px-4"><?php echo htmlspecialchars($row['deskripsi']); ?></td>
                <td class="py-3 px-4 flex items-center gap-2">
                    <a href="manage_modules.php?course_id=<?php echo $row['id']; ?>" class="bg-green-500 text-white p-2 rounded text-xs">Kelola Modul</a>
                    <a href="manage_courses.php?edit=<?php echo $row['id']; ?>" class="bg-yellow-500 text-white p-2 rounded text-xs">Edit</a>
                    <form action="manage_courses.php" method="POST" onsubmit="return confirm('Yakin ingin menghapus?');">
                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
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