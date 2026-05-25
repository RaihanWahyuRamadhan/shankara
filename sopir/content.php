<?php
// Ambil semua data supir
$sopir = fetchAll(query("SELECT * FROM sopir ORDER BY id DESC"));
?>

<div class="d-flex justify-content-between mb-4 align-items-center">
    <div>
        <h3 class="fw-bold text-dark mb-0"><i class="bi bi-person-badge"></i> Manajemen Supir</h3>
        <p class="text-muted small">Kelola data dan foto supir / kurir untuk pengiriman barang.</p>
    </div>
    <button class="btn btn-primary px-4 shadow-sm fw-bold" data-bs-toggle="modal" data-bs-target="#tambahSopirModal" style="border-radius: 10px;">
        <i class="bi bi-plus-circle me-2"></i> Tambah Supir
    </button>
</div>

<div class="card border-0 shadow-sm" style="border-radius: 15px;">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4" width="80">NO</th>
                        <th width="80">FOTO</th>
                        <th>NAMA SUPIR</th>
                        <th>NO. HANDPHONE</th>
                        <th>STATUS</th>
                        <th class="text-center" width="120">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($sopir)): ?>
                        <tr><td colspan="6" class="text-center py-4 text-muted">Belum ada data supir.</td></tr>
                    <?php else: ?>
                        <?php $no = 1; foreach($sopir as $s): ?>
                        <tr>
                            <td class="ps-4 fw-bold text-secondary">#<?php echo $no++; ?></td>
                            <td>
                                <?php 
                                if(!empty($s['foto_sopir'])) {
                                    // Deteksi otomatis apakah path diawali '/' (format baru) atau bukan (format lama)
                                    $foto_url = (strpos($s['foto_sopir'], '/') === 0) ? $s['foto_sopir'] : '../uploads/sopir/' . $s['foto_sopir'];
                                    
                                    echo '<img src="'.$foto_url.'" class="rounded-circle border shadow-sm" style="width:45px; height:45px; object-fit:cover; cursor:pointer;" onclick="showFoto(\''.$foto_url.'\', \''.htmlspecialchars($s['nama_sopir'], ENT_QUOTES).'\')">';
                                } else {
                                    echo '<div class="bg-light rounded-circle border d-flex align-items-center justify-content-center text-muted" style="width:45px; height:45px;"><i class="bi bi-person fs-4"></i></div>';
                                }
                                ?>
                            </td>
                            <td class="fw-bold text-dark"><?php echo $s['nama_sopir']; ?></td>
                            <td><?php echo $s['no_hp'] ?: '<span class="text-danger small fst-italic">Belum diisi</span>'; ?></td>
                            <td>
                                <?php if($s['status_aktif'] == 'Aktif'): ?>
                                    <span class="badge bg-success px-3 py-2 rounded-pill"><i class="bi bi-check-circle me-1"></i>Aktif</span>
                                <?php else: ?>
                                    <span class="badge bg-danger px-3 py-2 rounded-pill"><i class="bi bi-x-circle me-1"></i>Nonaktif</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-warning shadow-sm me-1" onclick="editSopir(<?php echo $s['id']; ?>, '<?php echo htmlspecialchars($s['nama_sopir'], ENT_QUOTES); ?>', '<?php echo $s['no_hp']; ?>', '<?php echo $s['status_aktif']; ?>')" title="Edit"><i class="bi bi-pencil-square"></i></button>
                                <button class="btn btn-sm btn-outline-danger shadow-sm" onclick="hapusSopir(<?php echo $s['id']; ?>)" title="Hapus"><i class="bi bi-trash"></i></button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah Supir -->
<div class="modal fade" id="tambahSopirModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
            <div class="modal-header bg-primary text-white border-0 py-3">
                <h5 class="modal-title fw-bold"><i class="bi bi-person-plus me-2"></i>Tambah Data Supir</h5>
                <button type="button" class="bg-transparent border-0 text-white" data-bs-dismiss="modal" style="font-size: 1.5rem; line-height: 1;"><i class="bi bi-x-lg"></i></button>
            </div>
            <!-- PENTING: Tambahkan enctype multipart/form-data agar bisa upload file -->
            <form action="tambah.php" method="POST" enctype="multipart/form-data">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nama Supir</label>
                        <input type="text" name="nama_sopir" class="form-control shadow-sm" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">No. Handphone</label>
                        <input type="number" name="no_hp" class="form-control shadow-sm">
                    </div>
                    <!-- KEMBALIKAN KOLOM UPLOAD FOTO -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Upload Foto Supir (Opsional)</label>
                        <input type="file" name="foto_sopir" class="form-control shadow-sm" accept="image/*">
                        <small class="text-muted" style="font-size: 0.7rem;">Bisa diisi oleh Admin atau dibiarkan agar Supir yang mengisi sendiri.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Status</label>
                        <select name="status_aktif" class="form-select shadow-sm" required>
                            <option value="Aktif" selected>Aktif</option>
                            <option value="Nonaktif">Nonaktif</option>
                        </select>
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

<!-- Modal Edit Supir -->
<div class="modal fade" id="editSopirModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
            <div class="modal-header bg-warning text-dark border-0 py-3">
                <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2"></i>Edit Data Supir</h5>
                <button type="button" class="bg-transparent border-0 text-dark" data-bs-dismiss="modal" style="font-size: 1.5rem; line-height: 1;"><i class="bi bi-x-lg"></i></button>
            </div>
            <!-- PENTING: Tambahkan enctype multipart/form-data agar bisa upload file -->
            <form action="edit.php" method="POST" enctype="multipart/form-data">
                <div class="modal-body p-4">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nama Supir</label>
                        <input type="text" name="nama_sopir" id="edit_nama_sopir" class="form-control shadow-sm" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">No. Handphone</label>
                        <input type="number" name="no_hp" id="edit_no_hp" class="form-control shadow-sm">
                    </div>
                    <!-- KEMBALIKAN KOLOM UPLOAD FOTO -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Ganti Foto Supir (Opsional)</label>
                        <input type="file" name="foto_sopir" class="form-control shadow-sm" accept="image/*">
                        <small class="text-muted" style="font-size: 0.7rem;">Biarkan kosong jika tidak ingin mengubah foto yang sudah ada.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Status</label>
                        <select name="status_aktif" id="edit_status_aktif" class="form-select shadow-sm" required>
                            <option value="Aktif">Aktif</option>
                            <option value="Nonaktif">Nonaktif</option>
                        </select>
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
function editSopir(id, nama, no_hp, status) {
    $('#edit_id').val(id);
    $('#edit_nama_sopir').val(nama);
    $('#edit_no_hp').val(no_hp);
    $('#edit_status_aktif').val(status);
    var editModal = new bootstrap.Modal(document.getElementById('editSopirModal'));
    editModal.show();
}

function hapusSopir(id) {
    Swal.fire({
        title: 'Hapus Supir?',
        text: "Data supir ini akan dihapus secara permanen.",
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

// Tangkap pesan sukses/error dari URL
const urlParams = new URLSearchParams(window.location.search);
if (urlParams.has('success')) {
    Swal.fire('Berhasil!', 'Data berhasil diperbarui.', 'success');
} else if (urlParams.has('error')) {
    Swal.fire('Gagal!', 'Terjadi kesalahan sistem.', 'error');
}
</script>