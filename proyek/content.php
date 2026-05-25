<?php
// Get all projects
$proyek = fetchAll(query("
    SELECT p.*, u.nama_lengkap as created_by_name 
    FROM proyek p 
    LEFT JOIN users u ON p.created_by = u.id 
    ORDER BY p.created_at DESC
"));
?>

<div class="d-flex justify-content-between mb-3">
    <h2>Manajemen Proyek</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahProyekModal">
        <i class="bi bi-plus-circle"></i> Tambah Proyek
    </button>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="dataTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama Proyek</th>
                        <th>Lokasi</th>
                        <th>Client</th>
                        <th>Tanggal Mulai</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($proyek as $p): ?>
                    <tr>
                        <td><?php echo $p['id']; ?></td>
                        <td><?php echo $p['nama_proyek']; ?></td>
                        <td><?php echo $p['lokasi']; ?></td>
                        <td><?php echo $p['client']; ?></td>
                        <td><?php echo $p['tanggal_mulai']; ?></td>
                        <td>
                            <?php
                            $statusClass = [
                                'planning' => 'secondary',
                                'ongoing' => 'primary',
                                'completed' => 'success',
                                'delayed' => 'danger'
                            ];
                            ?>
                            <span class="badge bg-<?php echo $statusClass[$p['status']]; ?>">
                                <?php echo $p['status']; ?>
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-info editProyek" 
                                    data-id="<?php echo $p['id']; ?>"
                                    data-nama="<?php echo $p['nama_proyek']; ?>"
                                    data-lokasi="<?php echo $p['lokasi']; ?>"
                                    data-client="<?php echo $p['client']; ?>"
                                    data-tanggal="<?php echo $p['tanggal_mulai']; ?>"
                                    data-status="<?php echo $p['status']; ?>">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-danger deleteProyek" data-id="<?php echo $p['id']; ?>">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah Proyek -->
<div class="modal fade" id="tambahProyekModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Proyek Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="tambah.php" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Nama Proyek</label>
                        <input type="text" name="nama_proyek" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Lokasi</label>
                        <input type="text" name="lokasi" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Client</label>
                        <input type="text" name="client" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Tanggal Mulai</label>
                        <input type="date" name="tanggal_mulai" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="planning">Planning</option>
                            <option value="ongoing">Ongoing</option>
                            <option value="completed">Completed</option>
                            <option value="delayed">Delayed</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Edit proyek
document.querySelectorAll('.editProyek').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.dataset.id;
        // Implement edit functionality
        window.location.href = 'tambah.php?id=' + id;
    });
});

// Delete proyek
document.querySelectorAll('.deleteProyek').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.dataset.id;
        Swal.fire({
            title: 'Yakin hapus?',
            text: "Data proyek akan dihapus permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Ya, hapus!'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'hapus.php?id=' + id;
            }
        });
    });
});
</script>