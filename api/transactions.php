<?php
    require_once('../config/koneksi.php');
    
    function getAccountTransactions($id) {
        global $conn;
    
        $id = mysqli_real_escape_string($conn, $id);
    
        // Mencari akun berdasarkan ID
        $query = "SELECT * FROM accounts WHERE id = '$id'";
        $result = mysqli_query($conn, $query);
    
        if (!$result || mysqli_num_rows($result) !== 1) {
            mysqli_close($conn);
            http_response_code(404);
            return json_encode(['message' => 'Akun tidak ditemukan']);
        }
    
        $account = mysqli_fetch_assoc($result);
    
        // Mencari transaksi yang melibatkan akun
        $transactionQuery = "SELECT * FROM transactions
                             WHERE sender_account_id = '$id' OR receiver_account_id = '$id'
                             ORDER BY id DESC
                             LIMIT 1";
    
        $transactionResult = mysqli_query($conn, $transactionQuery);
    
        if ($transactionResult) {
            $transactions = [];
    
            while ($row = mysqli_fetch_assoc($transactionResult)) {
                $transactions[] = [
                    'sender_account_id' => $row['sender_account_id'],
                    'receiver_account_id' => $row['receiver_account_id'],
                    'receiver_account_id' => $row['receiver_account_id'],
                    'amount' => $row['amount']
                ];
            }
    
            mysqli_close($conn);
    
            return json_encode([
                'name' => $account['name'],
                'transactions' => $transactions
            ]);
        } else {
            mysqli_close($conn);
            http_response_code(500);
            return json_encode(['message' => 'Gagal mengambil data transaksi']);
        }
    }
    
    if (isset($_GET['id'])) {
        $id = $_GET['id'];
        $response = getAccountTransactions($id);
        echo $response;
    } else {
        http_response_code(400);
        echo json_encode(['message' => 'Parameter id harus disediakan']);
    } 
?>