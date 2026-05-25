<div class="d-flex justify-content-between mb-4 align-items-center">
    <div>
        <h3 class="fw-bold text-dark mb-0"><i class="bi bi-person-bounding-box text-primary me-2"></i> Lengkapi Profil Driver</h3>
        <p class="text-muted small">Harap lengkapi Foto Wajah dan Nomor Handphone yang aktif untuk kebutuhan pengiriman.</p>
    </div>
</div>

<?php if(isset($_GET['success'])): ?>
    <div class="alert alert-success shadow-sm rounded-3 fw-bold mb-4">
        <i class="bi bi-check-circle me-2"></i> Data profil Anda berhasil diperbarui! Sekarang Anda bisa melakukan absensi dan pengiriman.
    </div>
<?php endif; ?>

<?php if(isset($_GET['error'])): ?>
    <div class="alert alert-danger shadow-sm rounded-3 fw-bold mb-4">
        <i class="bi bi-x-circle me-2"></i> Terjadi kesalahan saat menyimpan data.
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm" style="border-radius: 15px;">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="fw-bold m-0 text-dark">Data Diri Anda</h6>
            </div>
            <div class="card-body p-4">
                <form action="profile.php" method="POST" enctype="multipart/form-data">
                    
                    <div class="text-center mb-4">
                        <?php if(!empty($driver['foto_sopir'])): ?>
                            <img src="../uploads/sopir/<?php echo $driver['foto_sopir']; ?>" alt="Foto Profil" class="rounded-circle shadow-sm border" style="width: 150px; height: 150px; object-fit: cover;">
                        <?php else: ?>
                            <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mx-auto border" style="width: 150px; height: 150px;">
                                <i class="bi bi-person text-secondary" style="font-size: 5rem;"></i>
                            </div>
                            <p class="text-danger small mt-2 fw-bold"><i class="bi bi-exclamation-circle me-1"></i>Foto Belum Diunggah</p>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">Nama Lengkap (Sesuai Akun)</label>
                        <input type="text" class="form-control shadow-sm bg-light" value="<?php echo $driver['nama_sopir']; ?>" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nomor Handphone (Aktif)</label>
                        <input type="number" name="no_hp" class="form-control shadow-sm border-primary" value="<?php echo $driver['no_hp'] ?? ''; ?>" placeholder="Contoh: 081234567890" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold">Upload/Ganti Foto Wajah Baru</label>
                        <input type="file" name="foto_sopir" class="form-control shadow-sm" accept="image/*" <?php echo empty($driver['foto_sopir']) ? 'required' : ''; ?>>
                        <small class="text-muted" style="font-size: 0.75rem;">Format didukung: JPG, JPEG, PNG.</small>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 fw-bold py-2 shadow-sm rounded-pill">
                        <i class="bi bi-save me-2"></i> Simpan Profil
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card border-0 shadow-sm bg-primary text-white" style="border-radius: 15px; background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-3"><i class="bi bi-info-circle me-2"></i>Informasi Penting!</h5>
                <p style="font-size: 0.9rem;">Untuk menjaga keamanan dan validitas pengiriman barang, setiap Driver <strong>diwajibkan</strong> melengkapi profil ini.</p>
                <ul style="font-size: 0.9rem;" class="mb-0">
                    <li class="mb-2"><strong>Foto Wajah:</strong> Digunakan oleh Logistik dan Proyek untuk memvalidasi identitas pengantar barang.</li>
                    <li class="mb-2"><strong>Nomor Handphone:</strong> Digunakan agar divisi lain (Admin/Proyek) bisa menghubungi Anda jika terjadi kendala di jalan.</li>
                    <li>Sistem tidak akan mengizinkan Anda membuka menu <strong>Absensi</strong> dan <strong>Pengiriman</strong> sebelum kedua data ini terisi.</li>
                </ul>
            </div>
        </div>
    </div>
</div>