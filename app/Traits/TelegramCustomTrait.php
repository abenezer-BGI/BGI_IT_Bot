<?php

namespace App\Traits;

use WeStacks\TeleBot\TeleBot;
use WeStacks\TeleBot\Objects\Update;

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
     * @param TeleBot $bot
     * @param Update $update
     * @param string $language
     */
    public function error_message(TeleBot $bot,Update $update, string $language){
        if($language === 'amharic') {
            $bot->sendMessage([
                'chat_id' => $update->message->chat->id,
                'text' => 'ያስገቡት መልእክት ልክ አይደለም!',
            ]);
        }elseif ($language === 'english'){
            $bot->sendMessage([
                'chat_id' => $update->message->chat->id,
                'text' => 'The reply you sent is not correct!',
            ]);
        }
    }

    /**
     * Reply with BGI Betegna error message
     * @param TeleBot $bot
     * @param Update $update
     * @param string $language
     */
    public function not_registered_to_bgi_betegna(TeleBot $bot, Update $update, string $language){
        if($language === 'amharic') {
            $bot->sendMessage([
                'chat_id' => $update->message->chat->id,
                'text' => 'ውድ ደንበኛችን የቢ.ጂ.አይ ቤተኛ አገልግሎት ተጠቃሚዎች ዝርዝር ውስጥ አላገኘንዎትም።'.chr(10).'ለመመዝገብ ፕሮሞተሮን ያነጋግሩ።',
            ]);
        }elseif ($language === 'english'){
            $bot->sendMessage([
                'chat_id' => $update->message->chat->id,
                'text' => 'Deer esteemed customer, we couldn\'t find you in the BGI Betegna subscribers list.'.chr(10 ).'To subscribe to the service please contact your promoter.',
            ]);
        }
    }
}
