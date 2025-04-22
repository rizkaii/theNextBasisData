<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data
    $username = $_POST['username'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $store_name = $_POST['store_name'];
    $gender = $_POST['gender'];
    $birth_day = $_POST['birth_day'];
    $birth_month = $_POST['birth_month'];
    $birth_year = $_POST['birth_year'];

    // Upload foto profil (simpan ke folder /uploads)
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $target_dir = "uploads/";
        $file_name = basename($_FILES["profile_picture"]["name"]);
        $target_file = $target_dir . $file_name;
        move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file);
    }

    // Simpan ke DB di sini...
    echo "Data berhasil diperbarui!";
}
?>
