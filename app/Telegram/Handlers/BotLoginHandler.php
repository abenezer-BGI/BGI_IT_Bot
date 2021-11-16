<?php


namespace App\Telegram\Handlers;


use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use WeStacks\TeleBot\Interfaces\UpdateHandler;
use WeStacks\TeleBot\Objects\Update;
use WeStacks\TeleBot\TeleBot;

class BotLoginHandler extends UpdateHandler
{

    /**
     * @inheritDoc
     */
    public static function trigger(Update $update, TeleBot $bot)
    {
        // TODO: Implement trigger() method.
    }

    /**
     * @inheritDoc
     */
    public function handle()
    {
        $this->sendMessage([
            'chat_id'=>$this->update->message->from->id,
            'text'=>'Please send me your email:',
        ]);

        if(is_numeric($this->update->message->text)){
            $this->sendMessage([
                'chat_id'=>$this->update->message->from->id,
                'text'=>'Please send me a valid email',
            ]);
        }
    }
}
