<?php

namespace Model\Services;

use Library\Buttons;
use Library\Shared;
use Model\Entities\Candidate;
use Model\Entities\User;

class Registrar
{
    use Shared;
    use Buttons;
    use \Library\Uniroad;

    /**
     * @var string[]
     */
    private array $buttonsPositions;
    /**
     * @var string[]
     */
    private array $positionsLabels;

    public function __construct()
    {
        $this->buttonsPositions = [
            '24' => [
                'label' => 'vk',
                'code' => 1
            ],
            '31' => [
                'label' => 'rh',
                'code' => 2
            ],
            '32' => [
                'label' => 'rm',
                'code' => 3
            ]
        ];
    }

    public function registrationCandidate(string $code): bool
    {
        $position = $this->buttonsPositions[$code];
        $label = $position['label'] . $this->generateToken(8);
        $user = User::search(guid: $GLOBALS['uni.user'], limit: 1);

        $response = $this->uni()->get('accounts', ['type' => 'person', 'user' => $GLOBALS['uni.user']], 'account/list')->one()[0];
        $firstname = $response['name'] == null ? 'Іван' : $response['name'];
        $lastname = $response['middlename'] == null ? 'Іванов' : $response['middlename'];
        $structure = $response['party'] == null ? 'АІ-192' : $response['party'];
        $department = $response['department'] == null ? 'IКС' : $response['department'];
        $candidate = new Candidate(label: $label, firstname: $firstname, lastname: $lastname, structure: $structure, department: $department, person: $user->id, position: $position['code'], votes: 0);
        return !empty($candidate->save());
    }


}