<?php

namespace Library;

use Model\Entities\Candidate;
use Model\Entities\Message;
use Model\Entities\User;

trait Buttons
{
    /**
     * @param string $parent
     * @return array|null
     */
    public function getButtons(string $parent): array|null
    {
        $result = [];
        $buttons = Message::search(parent: (int)$parent, limit: 10);
        foreach ($buttons as $button) {
            $result [] = ['id' => (string)$button->id, 'title' => $button->title];
        }

        return array_chunk($result, 2);
    }

    /**
     * @param string $id
     * @return string
     */
    public function getText(string $id): string
    {
        $message = Message::search((int)$id, limit: 1);
        $text = $message->text;
        return $text;
    }

    /**
     * @param string $position
     * @return array|null
     */
    private function getCandidates(string $department, ?int $position = 0, ?string $code = null): array|null
    {
        $result = [];
        $codePosition = [
            '25' => 1,
            '27' => 2
        ];
        if (!empty($code)) {
            $position = $codePosition[$code];
        }
        $candidates = Candidate::search(position: $position, department: $department, limit: 10000);
        foreach ($candidates as $candidate) {
            $title = $candidate->lastname . " " . $candidate->firstname . " | " . $candidate->structure . " |";
            $result [] = ['id' => $candidate->label, 'title' => $title];
        }

        return array_chunk($result, 1);
    }

    public function getMainButtons(): array
    {
        $mainButton = '30';
        $buttons = $this->getButtons($mainButton);
        $user_from_db = User::search(guid: $GLOBALS['uni.user'], limit: 1);
        if ($user_from_db == null) {
            $user = new User(guid: $GLOBALS['uni.user'], voted_vki: 0, voted_rsi: 0);
            $user->save();
        }
        if ($user_from_db->voted_vki == 1) {
            unset($buttons[0][1]); // Видалення кнопки для голосування за ВКІ
        }

        if ($user_from_db->voted_rsi == 1) {
            unset($buttons[1][1]); // Видалення кнопки для голосування за РСІ
        }
        return $buttons;

    }

}