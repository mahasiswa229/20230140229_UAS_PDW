<?php
$pageTitle = 'Manajemen Pengguna';
$activePage = 'users'; // Anda mungkin perlu menambahkan ini di navigasi header asisten
require_once 'templates/header.php';
require_once '../config.php';

// Logika untuk CUD
$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Tambah atau Update Data
    if (isset($_POST['save'])) {
        $id = $_POST['id'];
        $nama = $_POST['nama'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $role = $_POST['role'];

        if (empty($id)) { // Create
            if(empty($password)) { $message = "Password wajib diisi untuk pengguna baru."; }
            else {
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $conn->prepare("INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", $nama, $email, $hashed_password, $role);
                $stmt->execute();
                $message = "Pengguna berhasil ditambahkan.";
            }
        } else { // Update
            if (!empty($password)) {
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $conn->prepare("UPDATE users SET nama = ?, email = ?, password = ?, role = ? WHERE id = ?");
                $stmt->bind_param("ssssi", $nama, $email, $hashed_password, $role, $id);
            } else {
                $stmt = $conn->prepare("UPDATE users SET nama = ?, email = ?, role = ? WHERE id = ?");
                $stmt->bind_param("sssi", $nama, $email, $role, $id);
            }
            $stmt->execute();
            $message = "Pengguna berhasil diperbarui.";
        }
        if(isset($stmt)) $stmt->close();
    }
    // Hapus Data
    if (isset($_POST['delete'])) {
        $id = $_POST['id'];
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        $message = "Pengguna berhasil dihapus.";
    }
}

// Logika untuk Read (mengambil data untuk diedit)
$edit_data = ['id' => '', 'nama' => '', 'email' => '', 'role' => 'mahasiswa'];
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $stmt = $conn->prepare("SELECT id, nama, email, role FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $edit_data = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}
?>

<?php if ($message): ?>
<div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative mb-4" role="alert">
    <span><?php echo $message; ?></span>
</div>
<?php endif; ?>

<div class="bg-white p-6 rounded-lg shadow-md mb-8">
    <h3 class="text-xl font-bold mb-4"><?php echo empty($edit_data['id']) ? 'Tambah Pengguna Baru' : 'Edit Pengguna'; ?></h3>
    <form action="manage_users.php" method="POST">
        <input type="hidden" name="id" value="<?php echo $edit_data['id']; ?>">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="nama" class="block text-gray-700">Nama Lengkap</label>
                <input type="text" name="nama" value="<?php echo htmlspecialchars($edit_data['nama']); ?>" class="w-full px-3 py-2 border rounded" required>
            </div>
            <div>
                <label for="email" class="block text-gray-700">Email</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($edit_data['email']); ?>" class="w-full px-3 py-2 border rounded" required>
            </div>
            <div>
                <label for="password" class="block text-gray-700">Password</label>
                <input type="password" name="password" class="w-full px-3 py-2 border rounded" placeholder="<?php echo empty($edit_data['id']) ? 'Wajib diisi' : 'Kosongkan jika tidak diubah'; ?>">
            </div>
            <div>
                <label for="role" class="block text-gray-700">Role</label>
                <select name="role" class="w-full px-3 py-2 border rounded">
                    <option value="mahasiswa" <?php echo ($edit_data['role'] == 'mahasiswa') ? 'selected' : ''; ?>>Mahasiswa</option>
                    <option value="asisten" <?php echo ($edit_data['role'] == 'asisten') ? 'selected' : ''; ?>>Asisten</option>
                </select>
            </div>
        </div>
        <div class="mt-4">
            <button type="submit" name="save" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Simpan Pengguna</button>
            <?php if (!empty($edit_data['id'])): ?>
                <a href="manage_users.php" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">Batal</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<div class="bg-white p-6 rounded-lg shadow-md">
    <h3 class="text-xl font-bold mb-4">Daftar Semua Pengguna</h3>
    <table class="min-w-full bg-white">
        <thead class="bg-gray-800 text-white">
            <tr>
                <th class="py-3 px-4">Nama</th>
                <th class="py-3 px-4">Email</th>
                <th class="py-3 px-4">Role</th>
                <th class="py-3 px-4">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $result = $conn->query("SELECT id, nama, email, role FROM users ORDER BY nama");
            while ($row = $result->fetch_assoc()):
            ?>
            <tr class="border-b">
                <td class="py-3 px-4"><?php echo htmlspecialchars($row['nama']); ?></td>
                <td class="py-3 px-4"><?php echo htmlspecialchars($row['email']); ?></td>
                <td class="py-3 px-4"><span class="capitalize"><?php echo htmlspecialchars($row['role']); ?></span></td>
                <td class="py-3 px-4 flex gap-2">
                    <a href="manage_users.php?edit=<?php echo $row['id']; ?>" class="bg-yellow-500 text-white p-2 rounded text-xs">Edit</a>
                    <form action="manage_users.php" method="POST" onsubmit="return confirm('Yakin ingin menghapus pengguna ini?');">
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