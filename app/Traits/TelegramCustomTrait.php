<?php

namespace App\Traits;

trait TelegramCustomTrait{

    /**
     * To append the current path to the DB's already existing path column value
     * @param $path
     * @param $text
     * @return string
     */
    public function path_append($path, $text)
    {
        $array_path = explode('.', $path);
        return end($array_path) === $text ? $path : $path . $text;
    }

    /**
     * Reply to the bot user with an error message
     * @param $update
     * @param $language
     */
    public function error_message($update, $language){
        if($language === 'amharic') {
            $this->sendMessage([
                'chat_id' => $update->message->chat->id,
                'text' => 'ያስገቡት መልስ ልክ አይደለም! እባክዎን ለተጠየቀው ጥያቄ ትክክለኛ መልስ ይስጡ።',
            ]);
        }elseif ($language === 'english'){
            $this->sendMessage([
                'chat_id' => $update->message->chat->id,
                'text' => 'The reply you sent is not correct! Please answer the quest correctly.',
            ]);
        }
    }
}
