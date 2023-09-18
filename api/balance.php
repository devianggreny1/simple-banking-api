<?php
    require_once('../config/koneksi.php');
    
    function getBalance($id) {
        global $conn;
    
        $id = mysqli_real_escape_string($conn, $id);
    
        $query = "SELECT * FROM accounts WHERE id = '$id'";
        $result = mysqli_query($conn, $query);
    
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            mysqli_close($conn);
            return json_encode(['name' => $row['name'], 'balance' => $row['balance']]);
        } else {
            mysqli_close($conn);
            http_response_code(404);
            return json_encode(['message' => 'Akun tidak ditemukan']);
        }
    }

    if (isset($_GET['id'])) {
        $id = $_GET['id'];
        $response = getBalance($id);
        echo $response;
    } 
?>