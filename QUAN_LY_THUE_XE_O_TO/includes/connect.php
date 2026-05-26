<?php
$conn = mysqli_connect("localhost", "root", "", "quan_ly_thue_xe_o_to");

if (!$conn) {
    die("Ket noi that bai: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");
?>
