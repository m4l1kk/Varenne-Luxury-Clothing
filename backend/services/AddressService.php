<?php
require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../dao/AddressDao.php';
require_once __DIR__ . '/../dao/UserDao.php';

class AddressService extends BaseService
{
    public function __construct()
    {
        parent::__construct(new AddressDao(), 'Address');
    }

    public function getByUserId(int $userId): array
    {
        $this->requireExisting(new UserDao(), $userId, 'user_id', 'User');
        return $this->dao->getByUserId($userId);
    }

    public function add(array $data): array
    {
        $this->rejectNestedValues($data);
        $data = $this->validate($data, null);
        $this->keepOneDefault($data, $data['user_id']);
        return $this->dao->add($data);
    }

    public function update(int $id, array $data): array
    {
        $current = $this->getById($id);
        $this->rejectNestedValues($data);
        $data = $this->validate($data, $current);
        if (empty($data)) {
            throw new ApiException('Nothing to update');
        }
        $this->keepOneDefault($data, $data['user_id'] ?? $current['user_id']);
        return $this->dao->update($id, $data);
    }

    // A user has only one default address: marking a new one clears the old one first.
    private function keepOneDefault(array $data, int $userId): void
    {
        if (!empty($data['is_default'])) {
            $this->dao->clearDefault($userId);
        }
    }

    protected function validate(array $data, ?array $current): array
    {
        $this->requireText($data, ['street'], $current, 150);
        $this->requireText($data, ['city', 'country'], $current, 80);
        $this->requireText($data, ['postal_code'], $current, 20);

        $clean = array_intersect_key($data, array_flip(['user_id', 'street', 'city', 'postal_code', 'country', 'is_default']));

        if ($current === null || isset($clean['user_id'])) {
            $clean['user_id'] = $this->requireExisting(new UserDao(), $clean['user_id'] ?? null, 'user_id', 'User');
        }
        if (isset($clean['is_default'])) {
            $clean['is_default'] = filter_var($clean['is_default'], FILTER_VALIDATE_BOOLEAN);
        }

        return $clean;
    }
}
