<?php

namespace Model;

use Library\Buttons;
use Library\MySQL;
use Library\Shared;
use Library\Uniroad;
use Model\Entities\Candidate;
use Model\Services\Registrar;

class Main
{
    use Shared;
    use Buttons;
    use Uniroad;

    private Registrar $registrar;
    private MySQL $db;

    /**
     * @param string $type
     * @param string $value
     * @param string $code
     * @return array|null
     */
    public function uniwebhook(string $type = '', string $value = '', string $code = '0'): ?array
    {
        $result = null;
        switch ($type) {
            case 'message':
                if ($value == '/start') {
                    $buttons = $this->getMainButtons();
                    try {
                        $response = $this->uni()->get('accounts', ['type' => 'person', 'user' => $GLOBALS['uni.user']], 'account/list')->one()[0];
                        if ($response['isLecturer']) {
                            $text = $this->getText('1');
                            $result = $this->complete($text);
                        } else
                            $result = [
                                'to' => $GLOBALS['uni.user'],
                                'type' => 'message',
                                'value' => $this->getText('30'),
                                'keyboard' => [
                                    'buttons' => $buttons
                                ]
                            ];
                    } catch (\Exception $e) {
                        $result = [
                            'to' => $GLOBALS['uni.user'],
                            'type' => 'message',
                            'value' => $this->getText('30'),
                            'keyboard' => [
                                'buttons' => $buttons
                            ]
                        ];
                    }
                } elseif ($value == 'вихід') {
                    $result = ['type' => 'context', 'set' => null];
                } else {
                    $result = [
                        'to' => $GLOBALS['uni.user'],
                        'type' => 'message',
                        'value' => "Сервіс `Вибори` отримав повідомлення $value"
                    ];
                }
                break;
            case 'click':
                switch ($code) {
                    case '30':
                        $buttons = $this->getMainButtons();
                        try {
                            $response = $this->uni()->get('accounts', ['type' => 'person', 'user' => $GLOBALS['uni.user']], 'account/list')->one()[0];
                            if ($response['isLecturer']) {
                                $text = $this->getText('1');
                                $result = $this->complete($text);
                            } else
                                $result = [
                                    'to' => $GLOBALS['uni.user'],
                                    'type' => 'message',
                                    'value' => $this->getText($code),
                                    'keyboard' => [
                                        'buttons' => $buttons
                                    ]
                                ];
                        } catch (\Exception $e) {
                            $result = [
                                'to' => $GLOBALS['uni.user'],
                                'type' => 'message',
                                'value' => $this->getText($code),
                                'keyboard' => [
                                    'buttons' => $buttons
                                ]
                            ];
                        }

                        break;
                    case '26':
                        $buttons = $this->getButtons($code);
                        $result = [
                            'to' => $GLOBALS['uni.user'],
                            'type' => 'message',
                            'value' => $this->getText($code),
                            'keyboard' => [
                                'buttons' => $buttons
                            ]
                        ];
                        break;
                    case '25':
                    case '27':

                        try {
                            $response = $this->uni()->get('accounts', ['type' => 'person', 'user' => $GLOBALS['uni.user']], 'account/list')->one()[0];
                            $department = $response['department'] == null ? 'IКС' : $response['department'];
                        } catch (\Exception $e) {
                            $department = 'IКС';
                        }

                        $candidates = $this->getCandidates(department: $department, code: $code);
                        if (empty($candidates)) {
                            $text = $this->getText('8');
                            $result = $this->complete($text);
                        } else
                            $result = [
                                'to' => $GLOBALS['uni.user'],
                                'type' => 'message',
                                'value' => $this->getText($code),
                                'keyboard' => [
                                    'buttons' => $candidates
                                ]
                            ];
                        break;
                    case '24':
                    case '31':
                    case '32':
                        $this->registrar->registrationCandidate(code: $code);
                        $text = $this->getText($code);
                        $result = $this->complete($text);
                        break;
                    case 'revert':
                        $buttons = $this->getMainButtons();
                        $result = [
                            'to' => $GLOBALS['uni.user'],
                            'type' => 'message',
                            'value' => $this->getText('30'),
                            'keyboard' => [
                                'buttons' => $buttons
                            ]
                        ];
                        break;
                }

                $firstsSymbols = substr($code, 0, 2);

                if ($firstsSymbols == 'rh') {
                    try {
                        $response = $this->uni()->get('accounts', ['type' => 'person', 'user' => $GLOBALS['uni.user']], 'account/list')->one()[0];
                        $department = $response['department'] == null ? 'IКС' : $response['department'];
                    } catch (\Exception $e) {
                        $department = 'IКС';
                    }

                    // Збереження в базі данних голосу виборця
                    $candidate = Candidate::search(label: $code);
                    $candidate->vote();


                    $candidates = $this->getCandidates(department: $department, position: 3);
                    if (empty($candidates)) {
                        $text = $this->getText('8');
                        $result = $this->complete($text);
                    } else
                        $result = [
                            'to' => $GLOBALS['uni.user'],
                            'type' => 'message',
                            'value' => $this->getText('5'),
                            'keyboard' => [
                                'buttons' => $candidates
                            ]
                        ];
                } elseif ($firstsSymbols == 'vk' || $firstsSymbols == 'rm') {
                    $this->approvalVoice($code);
                    $text = $this->getText('2');
                    $result = $this->complete($text);
                }


        }

        return $result;
    }


    /**
     * @return array
     */
    public function getresult(): array|null
    {
        $result = [];
        $headsRSIfromDB = Candidate::getRSIHeads();
        $membersRSIfromDB = Candidate::getRSIMembers();

        //Випадок, коли два кандидати набрали рівну кількість голосів
        if ($headsRSIfromDB[0]['votes'] == $headsRSIfromDB[1]['votes'])
            $result['headRSI'] = $this->getText('9');
        else
            $result['headRSI'] = $headsRSIfromDB[0]['lastname'] . " " . $headsRSIfromDB[0]['firstname'];
        $membersRSI = [];

        foreach ($membersRSIfromDB as $member) {
            $membersRSI[] = $member['lastname'] . " " . $member['firstname'];
        }
        $result['membersRSI'] = $membersRSI;
        return $result;
    }

    /**
     * @param array $data
     * @return string[]
     */
    public function addcandidate(string $firstname, string $lastname, string $structure, string $department, string $position): array
    {
        $this->positionsLabels = [
            '1' => 'vk',
            '2' => 'rh',
            '3' => 'rm'
        ];

        $label = $this->positionsLabels[$position] . $this->generateToken(8);
        $candidate = new Candidate(label: $label, firstname: $firstname, lastname: $lastname, structure: $structure, department: $department, person: 1, position: $position, votes: 0);
        $candidate->save();
        return ['Кандидат успішно доданий'];
    }


    private function approvalVoice(string $code)
    {

        $firstsSymbols = substr($code, 0, 2);

        $arrayVotes = [
            'vk' => 'voted_vki',
            'rm' => 'voted_rsi'
        ];

        $userFromDb = Entities\User::search(guid: $GLOBALS['uni.user'], limit: 1);
        $whoVotedFor = $arrayVotes[$firstsSymbols];
        $userFromDb->set([$whoVotedFor => 1]);

        // Збереження в базі данних голосу виборця
        $candidate = Candidate::search(label: $code);
        $candidate->vote();
    }

    public function __construct()
    {
        $this->db = new \Library\MySQL('core',
            \Library\MySQL::connect(
                $this->getVar('DB_HOST', 'e'),
                $this->getVar('DB_USER', 'e'),
                $this->getVar('DB_PASS', 'e')
            ));
        $this->setDB($this->db);

        $this->registrar = new Registrar();

    }
}