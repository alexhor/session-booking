<?php

namespace App\Models;
use CodeIgniter\I18n\Time;
use CodeIgniter\Model;

class UserAuthentication extends Model
{
    private $token_expire_time_in_seconds = 1 * 24 * 60 * 60;// 1 day
    protected $table            = 'user_authentication';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['user_id', 'token_hash', 'created_at'];
    protected array $casts = [
        'id' => 'int',
        'user_id' => 'int',
        'created_at' => 'datetime',
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

    protected function generate_token() : string
    {
        return bin2hex(random_bytes(36));
    }

    protected function hash_token(string $token) : string
    {
        return password_hash($token, PASSWORD_DEFAULT);
    }

    public function token_valid(int $user_id, string $token) : bool
    {
        $user_model = new User();
        if (null == $user_model->find($user_id)) return false;
        $user_authentication = $this->where('user_id', $user_id)->findAll();
        if (1 != count($user_authentication)) return false;
        $user_authentication = $user_authentication[0];
        // Check if token is expired
        if (Time::now()->isAfter($user_authentication['created_at']->addSeconds($this->token_expire_time_in_seconds))) {
            return false;
        }
        // Check if token is valid
        return password_verify($token, $user_authentication['token_hash']);
    }

    public function delete_by_user_id($user_id, bool $purge = false)
    {
        $this->where('user_id', $user_id)->delete();
    }

    public function insert($data=null, bool $returnId = true): string
    {
        // Check that the user exists
        $user_model = new User();
        if (!array_key_exists('user_id', $data) || null == $user_model->find($data['user_id'])) {
            throw new \CodeIgniter\Database\Exceptions\DatabaseException("No user could be found.");
        }

        // Delete any preexisting token
        $this->delete_by_user_id($data['user_id']);

        // Generate new token
        $token = $this->generate_token();
        $data['token_hash'] = $this->hash_token($token);
        $data['created_at'] = Time::now();
        parent::insert($data);

        return $token;
    }

    public function update($id=null, $data=null): bool {
        throw new \BadMethodCallException('Updating user authentication is not supported. Use insert to get updated token.');
    }
}
