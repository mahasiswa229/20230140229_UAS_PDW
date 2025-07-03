<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config.php';

// Ambil semua data mata praktikum
$result = $conn->query("SELECT * FROM mata_praktikum ORDER BY nama_praktikum");

$pageTitle = 'Cari Praktikum';
$activePage = 'courses';
// Jika user adalah mahasiswa, gunakan header mahasiswa. Jika tidak, gunakan header standar atau tidak sama sekali.
if (isset($_SESSION['role']) && $_SESSION['role'] == 'mahasiswa') {
    require_once 'mahasiswa/templates/header_mahasiswa.php';
} else {
    // Pengguna publik (belum login atau bukan mahasiswa)
    // Anda bisa membuat header publik sederhana jika perlu
    echo "<!DOCTYPE html><html lang='id'><head><meta charset='UTF-8'><title>Cari Praktikum</title><script src='https://cdn.tailwindcss.com'></script></head><body class='bg-gray-100'>";
    echo '<nav class="bg-blue-600 p-4 text-white">
            <div class="container mx-auto flex justify-between">
                <a href="#" class="font-bold text-xl">SIMPRAK</a>
                <div>
                    <a href="login.php" class="hover:underline">Login</a>
                    <a href="register.php" class="ml-4 hover:underline">Register</a>
                </div>
            </div>
          </nav>';
    echo '<div class="container mx-auto p-6 lg:p-8">';
}
?>

<h1 class="text-3xl font-bold text-gray-800 mb-6">Katalog Mata Praktikum</h1>

<?php if (isset($_GET['status'])): ?>
    <div class="mb-4 p-4 rounded-md <?php echo $_GET['status'] == 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
        <?php echo htmlspecialchars($_GET['message']); ?>
    </div>
<?php endif; ?>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php if ($result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): ?>
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-900"><?php echo htmlspecialchars($row['nama_praktikum']); ?></h3>
                    <p class="text-gray-600 mt-2"><?php echo htmlspecialchars($row['deskripsi']); ?></p>
                </div>
                <div class="bg-gray-50 px-6 py-4">
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'mahasiswa'): ?>
                        <a href="enroll.php?course_id=<?php echo $row['id']; ?>" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded transition-colors duration-300">
                            Daftar Praktikum
                        </a>
                    <?php else: ?>
                         <span class="text-sm text-gray-500">Login sebagai mahasiswa untuk mendaftar.</span>
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p class="text-gray-500 col-span-full">Belum ada mata praktikum yang tersedia.</p>
    <?php endif; ?>
</div>

<?php
if (isset($_SESSION['role']) && $_SESSION['role'] == 'mahasiswa') {
    require_once 'mahasiswa/templates/footer_mahasiswa.php';
} else {
    echo '</div></body></html>';
}
$conn->close();
?>