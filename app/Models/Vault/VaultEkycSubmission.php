<?php

namespace App\Models\Vault;

use Illuminate\Database\Eloquent\Model;

class VaultEkycSubmission extends Model
{
    protected $table = 'vault_ekyc_submissions';

    protected $fillable = [
        'vault_user_id', 'id_number_encrypted', 'full_name_encrypted', 'date_of_birth',
        'front_image_path', 'back_image_path', 'status', 'rejection_reason',
        'reviewed_by_user_id', 'reviewed_at',
    ];

    // Số CCCD/họ tên là dữ liệu định danh nhạy cảm — không bao giờ xuất ra API
    // thô; controller tự chọn field cần trả (vd chỉ số CCCD che bớt ký tự).
    protected $hidden = ['id_number_encrypted', 'full_name_encrypted'];

    protected function casts(): array
    {
        return [
            'id_number_encrypted' => 'encrypted',
            'full_name_encrypted' => 'encrypted',
            'date_of_birth' => 'date',
            'reviewed_at' => 'datetime',
        ];
    }

    public function vaultUser()
    {
        return $this->belongsTo(VaultUser::class);
    }
}
