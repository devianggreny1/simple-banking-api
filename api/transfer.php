<?php
    require_once('../config/koneksi.php');

    function transfer($sender_account_id, $receiver_account_id, $amount) {
        global $conn;
    
        $sender_account_id = mysqli_real_escape_string($conn, $sender_account_id);
        $receiver_account_id = mysqli_real_escape_string($conn, $receiver_account_id);
        $amount = mysqli_real_escape_string($conn, $amount);
    
        // Ambil saldo pengirim
        $query = "SELECT balance FROM accounts WHERE id = '$sender_account_id' FOR UPDATE";
        $result = mysqli_query($conn, $query);
    
        if (!$result || mysqli_num_rows($result) !== 1) {
            mysqli_rollback($conn);
            return json_encode(['message' => 'Akun pengirim tidak valid.']);
        }
    
        $sender_data = mysqli_fetch_assoc($result);
        $sender_balance = $sender_data['balance'];
    
        // Validasi saldo cukup
        if ($sender_balance < $amount) {
            mysqli_rollback($conn);
            return json_encode(['message' => 'Saldo tidak mencukupi untuk melakukan transfer ini.']);
        }
    
        // Kurangi saldo pengirim
        $new_sender_balance = $sender_balance - $amount;
        $update_sender = mysqli_query($conn, "UPDATE accounts SET balance = '$new_sender_balance' WHERE id = '$sender_account_id'");
    
        // Tambah saldo penerima
        $query = "SELECT balance FROM accounts WHERE id = '$receiver_account_id' FOR UPDATE";
        $result = mysqli_query($conn, $query);
    
        if (!$result || mysqli_num_rows($result) !== 1) {
            mysqli_rollback($conn);
            return json_encode(['message' => 'Akun penerima tidak valid.']);
        }
    
        $receiver_data = mysqli_fetch_assoc($result);
        $receiver_balance = $receiver_data['balance'];
        $new_receiver_balance = $receiver_balance + $amount;
        $update_receiver = mysqli_query($conn, "UPDATE accounts SET balance = '$new_receiver_balance' WHERE id = '$receiver_account_id'");
    
        if ($update_sender && $update_receiver) {
            // Catat transaksi
            $insert_transaction_query = "INSERT INTO transactions (sender_account_id, receiver_account_id, amount) 
            VALUES ('$sender_account_id', '$receiver_account_id', '$amount')";

            $result_insert_transaction = mysqli_query($conn, $insert_transaction_query);

            if ($result_insert_transaction) {
                $transaction = [
                    'sender_account_id' => $sender_account_id,
                    'receiver_account_id' => $receiver_account_id,
                    'amount' => $amount,
                ];

                return json_encode(['message' => 'Transfer berhasil', 'transaction' => $transaction]);
            }
        } else {
            mysqli_rollback($conn);
            return json_encode(['message' => 'Gagal melakukan transfer.']);
        }
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents("php://input"));
        
        $sender_account_id = $data->sender_account_id;
        $receiver_account_id = $data->receiver_account_id;
        $amount = $data->amount;
    
        $result_message = transfer($sender_account_id, $receiver_account_id, $amount);
        echo $result_message;
    } else {
        http_response_code(405);
        echo json_encode(['message' => 'Metode HTTP tidak diizinkan']);
    }
?>