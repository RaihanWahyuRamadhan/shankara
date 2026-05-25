<?php
// Ambil data users di-join dengan nama warehouse agar bisa menampilkan lokasi gudang[cite: 1]
$users = fetchAll(query("
    SELECT u.*, w.nama_warehouse 
    FROM users u 
    LEFT JOIN warehouse w ON u.warehouse_id = w.id 
    ORDER BY u.id DESC
"));

// Ambil daftar warehouse untuk pilihan di dropdown modal[cite: 1]
$warehouses = fetchAll(query("SELECT * FROM warehouse"));
?>

<div class="d-flex justify-content-between mb-4 align-items-center">
    <div>
        <h3 class="fw-bold text-dark mb-0"><i class="bi bi-people"></i> Manajemen Pengguna</h3>
        <p class="text-muted small">Kelola akun, hak akses, dan alokasi gudang untuk staff.</p>
    </div>
    <button class="btn btn-primary px-4 shadow-sm fw-bold" data-bs-toggle="modal" data-bs-target="#tambahUserModal" style="border-radius: 10px;">
        <i class="bi bi-person-plus me-2"></i> Tambah Akun Baru
    </button>
</div>

<div class="card border-0 shadow-sm" style="border-radius: 15px;">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4" width="80">NO</th>
                        <th>NAMA LENGKAP</th>
                        <th>USERNAME</th>
                        <th>HAK AKSES</th>
                        <th>LOKASI GUDANG</th>
                        <th class="text-center" width="150">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($users)): ?>
                        <tr><td colspan="6" class="text-center py-4 text-muted">Belum ada data pengguna.</td></tr>
                    <?php else: ?>
                        <?php 
                        // Inisialisasi nomor urut dari 1[cite: 1]
                        $no = 1; 
                        foreach($users as $u): 
                        ?>
                        <tr>
                            <!-- Menampilkan nomor urut dinamis (bukan ID database)[cite: 1] -->
                            <td class="ps-4 fw-bold text-secondary">#<?php echo $no++; ?></td>
                            
                            <td class="fw-bold text-dark"><?php echo $u['nama_lengkap']; ?></td>
                            <td><span class="text-primary fw-bold">@<?php echo $u['username']; ?></span></td>
                            <td>
                                <?php if($u['role'] == 'admin'): ?>
                                    <span class="badge bg-danger px-3 py-2 rounded-pill"><i class="bi bi-shield-lock me-1"></i>Administrator</span>
                                <?php elseif($u['role'] == 'logistik'): ?>
                                    <span class="badge bg-warning text-dark px-3 py-2 rounded-pill"><i class="bi bi-box-seam me-1"></i>Logistik</span>
                                <?php elseif($u['role'] == 'driver'): ?>
                                    <span class="badge bg-success px-3 py-2 rounded-pill"><i class="bi bi-truck me-1"></i>Driver / Supir</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php 
                                // Tampilkan alokasi gudang hanya untuk logistik dan driver[cite: 1]
                                if($u['role'] == 'logistik' || $u['role'] == 'driver') {
                                    echo $u['nama_warehouse'] ? '<i class="bi bi-building me-1 text-primary"></i> <span class="fw-bold">'.$u['nama_warehouse'].'</span>' : '<span class="text-danger small fst-italic">Belum dialokasikan</span>';
                                } else {
                                    echo '<span class="text-muted small">-</span>';
                                }
                                ?>
                            </td>
                            <td class="text-center">
                                <!-- Tombol Aksi tetap mengirimkan ID asli ($u['id']) ke JavaScript[cite: 1] -->
                                <button class="btn btn-sm btn-outline-warning shadow-sm me-1" 
                                        onclick="editUser(<?php echo $u['id']; ?>, '<?php echo htmlspecialchars($u['nama_lengkap'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($u['username'], ENT_QUOTES); ?>', '<?php echo $u['role']; ?>', '<?php echo $u['warehouse_id']; ?>')" title="Edit Akun">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <?php if($u['id'] != $_SESSION['user_id']): ?>
                                <button class="btn btn-sm btn-outline-danger shadow-sm" 
                                        onclick="hapusUser(<?php echo $u['id']; ?>)" title="Hapus Akun">
                                    <i class="bi bi-trash"></i>
                                </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah Akun -->
<div class="modal fade" id="tambahUserModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
            <div class="modal-header bg-primary text-white border-0 py-3">
                <h5 class="modal-title fw-bold"><i class="bi bi-person-plus me-2"></i>Buat Akun Baru</h5>
                <button type="button" class="bg-transparent border-0 text-white" data-bs-dismiss="modal" style="font-size: 1.5rem; line-height: 1;"><i class="bi bi-x-lg"></i></button>
            </div>
            <form action="tambah.php" method="POST">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nama Lengkap</label>
                        <input type="text" name="nama_lengkap" class="form-control shadow-sm" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Username</label>
                        <input type="text" name="username" class="form-control shadow-sm" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Password Baru</label>
                        <input type="password" name="password" class="form-control shadow-sm" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Hak Akses (Role)</label>
                        <select name="role" id="roleTambah" class="form-select shadow-sm" required>
                            <option value="admin">Administrator</option>
                            <option value="logistik" selected>Logistik</option>
                            <option value="driver">Driver / Supir</option>
                        </select>
                    </div>
                    <div class="mb-3 bg-light p-3 border rounded-3" id="divWarehouseTambah">
                        <label class="form-label small fw-bold text-primary"><i class="bi bi-building me-1"></i>Alokasi Gudang</label>
                        <select name="warehouse_id" id="warehouseTambah" class="form-select border-primary shadow-sm">
                            <option value="" selected disabled>-- Pilih Gudang --</option>
                            <?php foreach($warehouses as $w): ?>
                                <option value="<?php echo $w['id']; ?>"><?php echo $w['nama_warehouse']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 py-2">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Buat Akun</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Akun -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
            <div class="modal-header bg-warning text-dark border-0 py-3">
                <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2"></i>Edit Data Akun</h5>
                <button type="button" class="bg-transparent border-0 text-dark" data-bs-dismiss="modal" style="font-size: 1.5rem; line-height: 1;"><i class="bi bi-x-lg"></i></button>
            </div>
            <form action="edit.php" method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nama Lengkap</label>
                        <input type="text" name="nama_lengkap" id="edit_nama_lengkap" class="form-control shadow-sm" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Username</label>
                        <input type="text" name="username" id="edit_username" class="form-control shadow-sm" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Hak Akses (Role)</label>
                        <select name="role" id="edit_role" class="form-select shadow-sm" required>
                            <option value="admin">Administrator</option>
                            <option value="logistik">Logistik</option>
                            <option value="driver">Driver / Supir</option>
                        </select>
                    </div>
                    <div class="mb-3 bg-light p-3 border rounded-3" id="divWarehouseEdit">
                        <label class="form-label small fw-bold text-primary"><i class="bi bi-building me-1"></i>Alokasi Gudang</label>
                        <select name="warehouse_id" id="edit_warehouse" class="form-select border-primary shadow-sm">
                            <option value="">-- Pilih Gudang --</option>
                            <?php foreach($warehouses as $w): ?>
                                <option value="<?php echo $w['id']; ?>"><?php echo $w['nama_warehouse']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="p-3 bg-light rounded border border-warning mt-3">
                        <label class="form-label small fw-bold text-danger"><i class="bi bi-key me-1"></i>Ganti Password?</label>
                        <input type="password" name="password" class="form-control shadow-sm" placeholder="Ketik password baru...">
                        <small class="text-muted" style="font-size: 0.7rem;">Biarkan kosong jika tidak ingin mengubah password.</small>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 py-2">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold">Update Akun</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Logika dinamis untuk menampilkan alokasi gudang berdasarkan role[cite: 1]
$('#roleTambah').on('change', function() {
    if($(this).val() == 'logistik' || $(this).val() == 'driver') {
        $('#divWarehouseTambah').show();
        $('#warehouseTambah').prop('required', true);
    } else {
        $('#divWarehouseTambah').hide();
        $('#warehouseTambah').prop('required', false);
        $('#warehouseTambah').val(''); 
    }
});
$('#roleTambah').trigger('change');

$('#edit_role').on('change', function() {
    if($(this).val() == 'logistik' || $(this).val() == 'driver') {
        $('#divWarehouseEdit').show();
        $('#edit_warehouse').prop('required', true);
    } else {
        $('#divWarehouseEdit').hide();
        $('#edit_warehouse').prop('required', false);
        $('#edit_warehouse').val('');
    }
});

function editUser(id, nama, username, role, warehouse_id) {
    $('#edit_id').val(id);
    $('#edit_nama_lengkap').val(nama);
    $('#edit_username').val(username);
    $('#edit_role').val(role).trigger('change');
    
    if(warehouse_id && warehouse_id !== 'null' && warehouse_id !== '') {
        $('#edit_warehouse').val(warehouse_id);
    } else {
        $('#edit_warehouse').val('');
    }
    
    var editModal = new bootstrap.Modal(document.getElementById('editUserModal'));
    editModal.show();
}

function hapusUser(id) {
    Swal.fire({
        title: 'Hapus Akun?',
        text: "Pengguna tidak akan bisa login lagi.",
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
</script>