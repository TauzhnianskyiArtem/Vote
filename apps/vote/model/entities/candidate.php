<?php


namespace Model\Entities;


class Candidate
{
    use \Library\Shared;
    use \Library\Entity;

    private ?\Library\MySQL $db;

    public function vote()
    {
        $this->addExistNumber(['votes' => 1]);
    }

    public static function search(?string $label = "", ?int $person = 0, ?int $position = 0, ?string $department = '', ?int $limit = 1): self|array|null
    {
        $result = [];
        foreach (['label', 'person', 'position', 'department'] as $var)
            if ($$var)
                $filters[$var] = $$var;
        $db = self::getDB();
        $candidates = $db->select(['Candidates' => []]);
        if (!empty($filters))
            $candidates->where(['Candidates' => $filters]);
        foreach ($candidates->many($limit) as $candidate) {
            $class = __CLASS__;
            $result[] = new $class($candidate['id'], $candidate['label'], $candidate['firstname'], $candidate['lastname'], $candidate['structure'], $candidate['department'], $candidate['person'], $candidate['position'], $candidate['votes']);
        }
        return $limit == 1 ? ($result[0] ?? null) : $result;
    }

    public function save(): self
    {
        $db = $this->db;
        if (!$this->id) {
            $insert = [
                'label' => $this->label,
                'firstname' => $this->firstname,
                'lastname' => $this->lastname,
                'structure' => $this->structure,
                'department' => $this->department,
                'person' => $this->person,
                'position' => $this->position,
                'votes' => $this->votes
            ];
            $this->id = $db->insert([
                'Candidates' => $insert
            ])->run(true)->storage['inserted'];
        }

        if (!empty($this->_changed)) {
            $db->update('Candidates', (array)$this->_changed)
                ->where(['Candidates' => ['id' => $this->id]])
                ->run();
        }

        return $this;
    }


    public static function getRSIHead()
    {
        $db = self::getDB();
        return $db->select(['Candidates' => null])->where(['Candidates' =>['position' => 2]])->sort('votes DESC')->many(1);
    }

    public static function getRSIMembers()
    {
        $db = self::getDB();
        return $db->select(['Candidates' => null])->where(['Candidates' =>['position' => 3]])->sort('votes DESC')->many(7);
    }

    /**
     * Candidate constructor.
     * @param int $id
     * @param string $label
     * @param string|null $firstname
     * @param string|null $lastname
     * @param string|null $structure
     * @param string|null $department
     * @param int $person
     * @param int $position
     * @param int|null $votes
     */
    public function __construct(public int $id = 0, public ?string $label = null, public ?string $firstname = null, public ?string $lastname = null, public ?string $structure = null, public ?string $department = null, public int $person = 0, public int $position = 0, public ?int $votes = 0)
    {
        $this->db = $this->getDB();
    }

}