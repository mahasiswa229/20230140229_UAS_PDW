<?php
$pageTitle = 'Praktikum Saya';
$activePage = 'my_courses';
require_once 'templates/header_mahasiswa.php';
require_once '../config.php';

$mahasiswa_id = $_SESSION['user_id'];

// Ambil data praktikum yang diikuti oleh mahasiswa
$sql = "SELECT mp.id, mp.nama_praktikum, mp.deskripsi
        FROM mata_praktikum mp
        JOIN pendaftaran_praktikum pp ON mp.id = pp.praktikum_id
        WHERE pp.mahasiswa_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $mahasiswa_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<h1 class="text-3xl font-bold text-gray-800 mb-6">Praktikum yang Saya Ikuti</h1>

<?php if (isset($_GET['status'])): ?>
    <div class="mb-4 p-4 rounded-md bg-green-100 text-green-800">
        <?php echo htmlspecialchars($_GET['message']); ?>
    </div>
<?php endif; ?>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php if ($result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): ?>
            <div class="bg-white rounded-lg shadow-md overflow-hidden flex flex-col">
                <div class="p-6 flex-grow">
                    <h3 class="text-xl font-bold text-gray-900"><?php echo htmlspecialchars($row['nama_praktikum']); ?></h3>
                    <p class="text-gray-600 mt-2"><?php echo htmlspecialchars($row['deskripsi']); ?></p>
                </div>
                <div class="bg-gray-50 px-6 py-4">
                    <a href="course_detail.php?id=<?php echo $row['id']; ?>" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded transition-colors duration-300">
                        Lihat Detail
                    </a>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="col-span-full bg-white p-8 rounded-lg shadow-md text-center">
             <p class="text-gray-500">Anda belum mengikuti mata praktikum apapun.</p>
             <a href="courses.php" class="mt-4 inline-block bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">Cari Praktikum Sekarang</a>
        </div>
    <?php endif; ?>
</div>

<?php
$stmt->close();
$conn->close();
require_once 'templates/footer_mahasiswa.php';
?>