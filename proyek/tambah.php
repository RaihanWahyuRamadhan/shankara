<?php
require_once '../config/database.php';
if(!isLoggedIn()) redirect('../auth/login.php');

$id = $_GET['id'] ?? null;

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_proyek = $_POST['nama_proyek'];
    $lokasi = $_POST['lokasi'];
    $client = $_POST['client'];
    $tanggal_mulai = $_POST['tanggal_mulai'];
    $status = $_POST['status'];
    $created_by = $_SESSION['user_id'];
    
    if($id) {
        // Update
        $sql = "UPDATE proyek SET 
                nama_proyek='$nama_proyek', 
                lokasi='$lokasi', 
                client='$client', 
                tanggal_mulai='$tanggal_mulai', 
                status='$status' 
                WHERE id=$id";
    } else {
        // Insert
        $sql = "INSERT INTO proyek (nama_proyek, lokasi, client, tanggal_mulai, status, created_by) 
                VALUES ('$nama_proyek', '$lokasi', '$client', '$tanggal_mulai', '$status', $created_by)";
    }
    
    if(query($sql)) {
        // Log activity
        $aktivitas = $id ? "Update proyek ID: $id" : "Tambah proyek baru: $nama_proyek";
        query("INSERT INTO riwayat_aktivitas (user_id, aktivitas, tabel_terkait, record_id) 
               VALUES ({$_SESSION['user_id']}, '$aktivitas', 'proyek', " . ($id ?: mysqli_insert_id($conn)) . ")");
        
        header("Location: index.php?success=1");
    } else {
        header("Location: index.php?error=1");
    }
} elseif($id) {
    $result = query("SELECT * FROM proyek WHERE id=$id");
    $data = fetchOne($result);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Tambah/Edit Proyek</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4><?php echo $id ? 'Edit' : 'Tambah'; ?> Proyek</h4>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label>Nama Proyek</label>
                                <input type="text" name="nama_proyek" class="form-control" 
                                       value="<?php echo $data['nama_proyek'] ?? ''; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label>Lokasi</label>
                                <input type="text" name="lokasi" class="form-control" 
                                       value="<?php echo $data['lokasi'] ?? ''; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label>Client</label>
                                <input type="text" name="client" class="form-control" 
                                       value="<?php echo $data['client'] ?? ''; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label>Tanggal Mulai</label>
                                <input type="date" name="tanggal_mulai" class="form-control" 
                                       value="<?php echo $data['tanggal_mulai'] ?? ''; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label>Status</label>
                                <select name="status" class="form-control">
                                    <option value="planning" <?php echo (isset($data['status']) && $data['status']=='planning') ? 'selected' : ''; ?>>Planning</option>
                                    <option value="ongoing" <?php echo (isset($data['status']) && $data['status']=='ongoing') ? 'selected' : ''; ?>>Ongoing</option>
                                    <option value="completed" <?php echo (isset($data['status']) && $data['status']=='completed') ? 'selected' : ''; ?>>Completed</option>
                                    <option value="delayed" <?php echo (isset($data['status']) && $data['status']=='delayed') ? 'selected' : ''; ?>>Delayed</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                            <a href="index.php" class="btn btn-secondary">Batal</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>