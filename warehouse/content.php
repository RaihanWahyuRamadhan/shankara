<?php
// Mengambil semua data warehouse
$warehouse = fetchAll(query("SELECT * FROM warehouse ORDER BY id DESC"));
?>

<div class="d-flex justify-content-between mb-4 align-items-center">
    <div>
        <h3 class="fw-bold text-dark mb-0"><i class="bi bi-building"></i> Manajemen Gudang</h3>
        <p class="text-muted small">Kelola data warehouse (gudang) untuk alokasi stok.</p>
    </div>
    <button class="btn btn-primary px-4 shadow-sm fw-bold" data-bs-toggle="modal" data-bs-target="#tambahWarehouseModal" style="border-radius: 10px;">
        <i class="bi bi-plus-circle me-2"></i> Tambah Gudang
    </button>
</div>

<div class="card border-0 shadow-sm" style="border-radius: 15px;">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4" width="80">ID</th>
                        <th>NAMA WAREHOUSE / GUDANG</th>
                        <th class="text-center" width="150">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($warehouse)): ?>
                        <tr><td colspan="3" class="text-center py-4 text-muted">Belum ada data warehouse.</td></tr>
                    <?php else: ?>
                        <?php foreach($warehouse as $w): ?>
                        <tr>
                            <td class="ps-4 fw-bold text-secondary">#<?php echo $w['id']; ?></td>
                            <td class="fw-bold text-dark"><?php echo $w['nama_warehouse']; ?></td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-warning shadow-sm me-1" 
                                        onclick="editWarehouse(<?php echo $w['id']; ?>, '<?php echo htmlspecialchars($w['nama_warehouse'], ENT_QUOTES); ?>')" title="Edit">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger shadow-sm" 
                                        onclick="hapusWarehouse(<?php echo $w['id']; ?>)" title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah Warehouse -->
<div class="modal fade" id="tambahWarehouseModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
            <div class="modal-header bg-primary text-white border-0 py-3">
                <h5 class="modal-title fw-bold"><i class="bi bi-building me-2"></i>Tambah Gudang Baru</h5>
                <button type="button" class="bg-transparent border-0 text-white" data-bs-dismiss="modal" style="font-size: 1.5rem; line-height: 1;">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <form action="tambah.php" method="POST">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nama Warehouse / Gudang</label>
                        <input type="text" name="nama_warehouse" class="form-control shadow-sm" placeholder="Contoh: Warehouse 4 - Surabaya" required>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 py-2">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Warehouse -->
<div class="modal fade" id="editWarehouseModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
            <div class="modal-header bg-warning text-dark border-0 py-3">
                <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2"></i>Edit Data Gudang</h5>
                <button type="button" class="bg-transparent border-0 text-dark" data-bs-dismiss="modal" style="font-size: 1.5rem; line-height: 1;">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <form action="edit.php" method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nama Warehouse / Gudang</label>
                        <input type="text" name="nama_warehouse" id="edit_nama_warehouse" class="form-control shadow-sm" required>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 py-2">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold">Update Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editWarehouse(id, nama) {
    $('#edit_id').val(id);
    $('#edit_nama_warehouse').val(nama);
    var editModal = new bootstrap.Modal(document.getElementById('editWarehouseModal'));
    editModal.show();
}

function hapusWarehouse(id) {
    Swal.fire({
        title: 'Hapus Gudang ini?',
        text: "Jika gudang ini sudah berisi stok atau pernah dipakai pengiriman, penghapusan akan ditolak sistem.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus!'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'hapus.php?id=' + id;
        }
    });
}

const urlParams = new URLSearchParams(window.location.search);
if (urlParams.has('error') && urlParams.get('error') === 'in_use') {
    Swal.fire('Gagal Dihapus!', 'Gudang ini tidak bisa dihapus karena masih menyimpan data stok atau terikat dengan riwayat pengiriman.', 'error');
} else if (urlParams.has('success')) {
    Swal.fire('Berhasil!', 'Data warehouse berhasil disimpan.', 'success');
}
</script>