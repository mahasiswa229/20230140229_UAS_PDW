<?php
$pageTitle = 'Laporan Masuk';
$activePage = 'laporan'; // Anda mungkin perlu menambahkan ini di navigasi header asisten
require_once 'templates/header.php';
require_once '../config.php';

// Logika untuk filtering
$where_clauses = [];
$filter_praktikum = $_GET['filter_praktikum'] ?? '';
$filter_mahasiswa = $_GET['filter_mahasiswa'] ?? '';

if (!empty($filter_praktikum)) {
    $where_clauses[] = "mp.id = " . (int)$filter_praktikum;
}
if (!empty($filter_mahasiswa)) {
    // Cari user ID berdasarkan nama
    $stmt_user = $conn->prepare("SELECT id FROM users WHERE nama LIKE ?");
    $search_term = "%" . $filter_mahasiswa . "%";
    $stmt_user->bind_param("s", $search_term);
    $stmt_user->execute();
    $result_user = $stmt_user->get_result();
    $user_ids = [];
    while($user = $result_user->fetch_assoc()) {
        $user_ids[] = $user['id'];
    }
    if(!empty($user_ids)) {
        $where_clauses[] = "u.id IN (" . implode(',', $user_ids) . ")";
    } else {
        $where_clauses[] = "u.id = 0"; // No user found
    }
}

$sql = "
    SELECT 
        l.id, l.tanggal_kumpul, l.nilai,
        u.nama AS nama_mahasiswa,
        m.nama_modul,
        mp.nama_praktikum
    FROM laporan l
    JOIN users u ON l.mahasiswa_id = u.id
    JOIN modul m ON l.modul_id = m.id
    JOIN mata_praktikum mp ON m.praktikum_id = mp.id
";

if (!empty($where_clauses)) {
    $sql .= " WHERE " . implode(' AND ', $where_clauses);
}

$sql .= " ORDER BY l.tanggal_kumpul DESC";

$result_laporan = $conn->query($sql);
?>

<h1 class="text-2xl font-bold text-gray-800 mb-6">Laporan Masuk dari Mahasiswa</h1>

<div class="bg-white p-4 rounded-lg shadow-md mb-6">
    <form action="manage_reports.php" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label for="filter_praktikum" class="block text-sm font-medium text-gray-700">Filter berdasarkan Praktikum:</label>
            <select name="filter_praktikum" id="filter_praktikum" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                <option value="">Semua Praktikum</option>
                <?php
                $result_praktikum = $conn->query("SELECT id, nama_praktikum FROM mata_praktikum ORDER BY nama_praktikum");
                while ($prak = $result_praktikum->fetch_assoc()) {
                    $selected = ($filter_praktikum == $prak['id']) ? 'selected' : '';
                    echo "<option value='{$prak['id']}' {$selected}>" . htmlspecialchars($prak['nama_praktikum']) . "</option>";
                }
                ?>
            </select>
        </div>
        <div>
            <label for="filter_mahasiswa" class="block text-sm font-medium text-gray-700">Filter berdasarkan Nama Mahasiswa:</label>
            <input type="text" name="filter_mahasiswa" id="filter_mahasiswa" value="<?php echo htmlspecialchars($filter_mahasiswa); ?>" class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm">
        </div>
        <div class="self-end">
            <button type="submit" class="w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Filter</button>
        </div>
    </form>
</div>


<div class="bg-white p-6 rounded-lg shadow-md overflow-x-auto">
    <table class="min-w-full bg-white">
        <thead class="bg-gray-800 text-white">
            <tr>
                <th class="py-3 px-4">Tanggal Kumpul</th>
                <th class="py-3 px-4">Mahasiswa</th>
                <th class="py-3 px-4">Praktikum</th>
                <th class="py-3 px-4">Modul</th>
                <th class="py-3 px-4">Status Nilai</th>
                <th class="py-3 px-4">Aksi</th>
            </tr>
        </thead>
        <tbody class="text-gray-700">
            <?php if ($result_laporan->num_rows > 0): ?>
                <?php while ($row = $result_laporan->fetch_assoc()): ?>
                <tr class="border-b">
                    <td class="py-3 px-4"><?php echo date('d M Y, H:i', strtotime($row['tanggal_kumpul'])); ?></td>
                    <td class="py-3 px-4"><?php echo htmlspecialchars($row['nama_mahasiswa']); ?></td>
                    <td class="py-3 px-4"><?php echo htmlspecialchars($row['nama_praktikum']); ?></td>
                    <td class="py-3 px-4"><?php echo htmlspecialchars($row['nama_modul']); ?></td>
                    <td class="py-3 px-4">
                        <?php if (is_null($row['nilai'])): ?>
                            <span class="bg-yellow-200 text-yellow-800 py-1 px-3 rounded-full text-xs">Belum Dinilai</span>
                        <?php else: ?>
                            <span class="bg-green-200 text-green-800 py-1 px-3 rounded-full text-xs">Sudah Dinilai (<?php echo $row['nilai']; ?>)</span>
                        <?php endif; ?>
                    </td>
                    <td class="py-3 px-4">
                        <a href="grade_report.php?id=<?php echo $row['id']; ?>" class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-1 px-3 rounded text-xs">Lihat & Nilai</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center py-4">Tidak ada laporan yang ditemukan.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
$conn->close();
require_once 'templates/footer.php';
?>