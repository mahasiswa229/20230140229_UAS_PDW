<?php
session_start();
require_once 'config.php';

// Pastikan pengguna adalah mahasiswa dan sudah login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'mahasiswa') {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['course_id'])) {
    header("Location: courses.php?status=error&message=ID Praktikum tidak valid.");
    exit();
}

$mahasiswa_id = $_SESSION['user_id'];
$praktikum_id = (int)$_GET['course_id'];

// Cek apakah mahasiswa sudah terdaftar sebelumnya
$sql_check = "SELECT * FROM pendaftaran_praktikum WHERE mahasiswa_id = ? AND praktikum_id = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("ii", $mahasiswa_id, $praktikum_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows > 0) {
    header("Location: courses.php?status=error&message=Anda sudah terdaftar di praktikum ini.");
    exit();
}

// Daftarkan mahasiswa ke praktikum
$sql_enroll = "INSERT INTO pendaftaran_praktikum (mahasiswa_id, praktikum_id) VALUES (?, ?)";
$stmt_enroll = $conn->prepare($sql_enroll);
$stmt_enroll->bind_param("ii", $mahasiswa_id, $praktikum_id);

if ($stmt_enroll->execute()) {
    header("Location: my_courses.php?status=success&message=Berhasil mendaftar ke praktikum.");
} else {
    header("Location: courses.php?status=error&message=Gagal mendaftar, terjadi kesalahan.");
}

$stmt_check->close();
$stmt_enroll->close();
$conn->close();
?>