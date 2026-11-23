<?php
require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../dao/UserDao.php';

class UserService extends BaseService
{
    private const ROLES = ['customer', 'admin'];

    public function __construct()
    {
        parent::__construct(new UserDao(), 'User');
    }

    public function getByRole(string $role): array
    {
        if (!in_array($role, self::ROLES, true)) {
            throw new ApiException("Role must be one of: " . implode(', ', self::ROLES));
        }
        return $this->dao->getByRole($role);
    }

    // The client sends "password"; only its hash is stored, and the hash is never returned.
    protected function validate(array $data, ?array $current): array
    {
        $this->requireText($data, ['first_name', 'last_name'], $current, 50);
        $this->requireText($data, ['email'], $current, 100);

        $clean = array_intersect_key($data, array_flip(['first_name', 'last_name', 'email', 'phone', 'role']));
        $clean = array_map(fn($v) => is_string($v) ? trim($v) : $v, $clean);

        if (isset($clean['email'])) {
            $clean['email'] = strtolower($clean['email']);
            if (!filter_var($clean['email'], FILTER_VALIDATE_EMAIL)) {
                throw new ApiException('Email address is not valid');
            }
            $owner = $this->dao->getByEmail($clean['email']);
            if ($owner !== null && ($current === null || $owner['id'] != $current['id'])) {
                throw new ApiException('A user with this email already exists', 409);
            }
        }

        if (isset($clean['role']) && !in_array($clean['role'], self::ROLES, true)) {
            throw new ApiException("Role must be one of: " . implode(', ', self::ROLES));
        }

        if ($current === null && !isset($data['password'])) {
            throw new ApiException("Field 'password' is required");
        }
        if (isset($data['password'])) {
            if (!is_string($data['password']) || strlen($data['password']) < 8) {
                throw new ApiException('Password must have at least 8 characters');
            }
            $clean['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        return $clean;
    }
}
