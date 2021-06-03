<?php


namespace Model\Entities;


class Position
{
    public static function search(int $id): self|array|null
    {
        if ($id)
            $filters[$id] = $id;
        $db = self::getDB();
        $position = $db->select(['Positions' => []]);
        if (!empty($filters))
            $position->where(['Positions' => $filters]);
        $position = $position->one();
        $class = __CLASS__;
        $result = new $class($position['id'], $position['title'], $position['multiple']);
        return isset($result) ? $result : null;
    }


    /**
     * Position constructor.
     * @param int $id
     * @param string $title
     * @param int $multiple
     */
    public function __construct(public int $id, public string $title, public int $multiple)
    {
        $this->db = $this->getDB();
    }
}