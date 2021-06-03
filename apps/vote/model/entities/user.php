<?php
/**
 * User entity
 *
 * @global object $CORE- >model
 * @package Model\Entities
 */

namespace Model\Entities;

class User
{
    use \Library\Shared;
    use \Library\Entity;
    use \Library\Uniroad;

    private ?\Library\MySQL $db;

    public static function search(?int $chat = 0, ?string $guid = '', int $limit = 0): self|array|null
    {
        $result = [];
        foreach (['chat', 'guid'] as $var)
            if ($$var)
                $filters[$var] = $$var;
        $db = self::getDB();
        $users = $db->select(['Users' => []]);
        if (!empty($filters))
            $users->where(['Users' => $filters]);
        foreach ($users->many($limit) as $user) {
            $class = __CLASS__;
            $result[] = new $class($user['id'], $user['guid'], $user['voted_vki'], $user['voted_rsi'], $user['message'], $user['service'], $user['input']);
        }
        return $limit == 1 ? (isset($result[0]) ? $result[0] : null) : $result;
    }

    public function save():self {
        $db = $this->db;
        if (!$this->id) {
            $insert = [
                'guid' => $this->guid,
                'voted_vki' => $this->voted_vki,
                'voted_rsi' => $this->voted_rsi
            ];
            $this->id = $db -> insert([
                'Users' => $insert
            ])->run(true)->storage['inserted'];
        }

        if ($this->_changed)
            $db -> update('Users', (array)$this->_changed)
                -> where(['Users'=> ['id' => $this->id]])
                -> run();
        return $this;
    }
    public function __construct(public int $id = 0, public ?string $guid = null, public int $voted_vki = 0, public int $voted_rsi = 0, public ?int $message = null, public ?int $service = null, public string|array|null $input = '')
    {
        $this->db = $this->getDB();
        $this->input = $this->input ? json_decode($this->input, true) : [];
        if (!$guid) {
            $response = $this->uni()->get('accounts', ['type' => 'user'], 'account/create')->one();
            if (property_exists($response, 'guid')) {
                $this->set(['guid' => $response->guid]);
            }
        }
    }
}