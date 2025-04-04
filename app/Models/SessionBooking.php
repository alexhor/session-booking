<?php

namespace App\Models;

use CodeIgniter\Model;

class SessionBooking extends Model
{
    protected $table            = 'session_bookings';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['user_id', 'start_time', 'title', 'title_is_public', 'description', 'description_is_public', 'reminder_send_at'];
    protected array $casts = [
        'id' => 'int',
        'user_id' => 'int',
        'start_time' => 'datetime',
        'created_at' => 'datetime',
        'title_is_public' => 'bool',
        'description_is_public' => 'bool',
        'reminder_send_at' => '?datetime',
    ];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    public function get_by_range($date_from, $date_to) {
        return $this->where("`start_time` BETWEEN '$date_from' AND '$date_to'")->findAll();
    }

    public function get_by_range_reminder_not_send($date_from, $date_to) {
        return $this->where("`start_time` BETWEEN '$date_from' AND '$date_to'")->where("`reminder_send_at` IS NULL")->findAll();
    }
}
