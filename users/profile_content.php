<div class="d-flex justify-content-between mb-4 align-items-center">
    <div>
        <h3 class="fw-bold text-dark mb-0"><i class="bi bi-person-circle text-primary me-2"></i> Lengkapi Profil Logistik</h3>
        <p class="text-muted small">Harap lengkapi Foto Wajah dan Nomor Handphone Anda sebagai identitas staf gudang resmi.</p>
    </div>
</div>

<?php if(isset($_GET['success'])): ?>
    <div class="alert alert-success shadow-sm rounded-3 fw-bold mb-4">
        <i class="bi bi-check-circle me-2"></i> Data profil berhasil diperbarui! Sekarang Anda bisa menggunakan fitur sistem.
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
                        <?php if(!empty($logistik['foto_user'])): ?>
                            <!-- Deteksi otomatis path foto (format lama vs format baru) -->
                            <?php $foto_url = (strpos($logistik['foto_user'], '/') === 0) ? $logistik['foto_user'] : '../uploads/users/' . $logistik['foto_user']; ?>
                            <img src="<?php echo $foto_url; ?>" alt="Foto Profil Logistik" class="rounded-circle shadow-sm border" style="width: 150px; height: 150px; object-fit: cover;">
                        <?php else: ?>
                            <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mx-auto border" style="width: 150px; height: 150px;">
                                <i class="bi bi-person text-secondary" style="font-size: 5rem;"></i>
                            </div>
                            <p class="text-danger small mt-2 fw-bold"><i class="bi bi-exclamation-circle me-1"></i>Foto Belum Diunggah</p>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">Nama Lengkap (Sesuai Akun)</label>
                        <input type="text" class="form-control shadow-sm bg-light" value="<?php echo $logistik['nama_lengkap']; ?>" readonly>
                    </div>

                    <!-- Menampilkan info Gudang yang dialokasikan Admin -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">Lokasi Penugasan Gudang</label>
                        <?php if(empty($logistik['warehouse_id'])): ?>
                            <input type="text" class="form-control shadow-sm bg-light text-danger fw-bold" value="Belum Dialokasikan (Hubungi Admin)" readonly>
                        <?php else: ?>
                            <input type="text" class="form-control shadow-sm bg-light text-primary fw-bold" value="<?php echo $logistik['nama_warehouse']; ?>" readonly>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nomor Handphone (Aktif)</label>
                        <input type="number" name="no_hp" class="form-control shadow-sm border-primary" value="<?php echo $logistik['no_hp'] ?? ''; ?>" placeholder="Contoh: 081234567890" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold">Upload/Ganti Foto Wajah Baru</label>
                        <input type="file" name="foto_user" class="form-control shadow-sm" accept="image/*" <?php echo empty($logistik['foto_user']) ? 'required' : ''; ?>>
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
        <!-- Kotak Informasi dengan gradient warna hijau agar berbeda dengan Driver -->
        <div class="card border-0 shadow-sm text-white" style="border-radius: 15px; background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-3"><i class="bi bi-info-circle me-2"></i>Informasi Staf Logistik</h5>
                <p style="font-size: 0.9rem;">Untuk menjaga keamanan dan kelancaran operasional sistem, Anda <strong>diwajibkan</strong> melengkapi profil di samping.</p>
                <ul style="font-size: 0.9rem;" class="mb-0">
                    <li class="mb-2"><strong>Foto Wajah:</strong> Akan ditampilkan sebagai identitas Anda saat melakukan konfirmasi pengiriman barang.</li>
                    <li class="mb-2"><strong>Nomor Handphone:</strong> Dibutuhkan agar Admin dan Driver bisa berkomunikasi langsung dengan staf gudang yang bertugas.</li>
                    <li class="mb-2"><strong>Alokasi Gudang:</strong> Hanya Admin yang bisa menyetel lokasi gudang Anda. Jika belum tersetting, laporkan ke Admin!</li>
                    <li>Sistem otomatis <strong>mengunci fitur Absensi dan Pengiriman</strong> jika Foto, Nomor HP, atau Alokasi Gudang masih kosong.</li>
                </ul>
            </div>
        </div>
    </div>
</div>