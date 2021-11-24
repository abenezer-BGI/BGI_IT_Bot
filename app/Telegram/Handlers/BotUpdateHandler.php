<?php


namespace App\Telegram\Handlers;


use App\Models\BotStatus;
use App\Models\BotUser;
use Illuminate\Support\Facades\Log;
use WeStacks\TeleBot\Exception\TeleBotObjectException;
use WeStacks\TeleBot\Interfaces\UpdateHandler;
use WeStacks\TeleBot\Objects\InlineKeyboardButton;
use WeStacks\TeleBot\Objects\Keyboard\InlineKeyboardMarkup;
use WeStacks\TeleBot\Objects\Update;
use WeStacks\TeleBot\TeleBot;

class BotUpdateHandler extends UpdateHandler
{

    /**
     * @inheritDoc
     */
    public static function trigger(Update $update, TeleBot $bot)
    {
        if (isset($update->message) or isset($update->callback_query)) {
            return true;
        }
        return false;
    }

    /**
     * @inheritDoc
     */
    public function handle()
    {
        if (isset($this->update->callback_query) and $this->update->callback_query->data === 'root') {
            try {
                BotUser::query()->updateOrCreate(
                    [
                        'telegram_user_id' => $this->update->callback_query->id,
                    ], [
                        'first_name' => $this->update->callback_query->from->first_name,
                        'last_name' => $this->update->callback_query->from->last_name ?? '',
                        'username' => $this->update->callback_query->from->username ?? null,
                        'is_bot' => $this->update->callback_query->from->is_bot,
                        'chat_id' => $this->update->callback_query->message->chat->id,
                    ]
                );

                BotStatus::query()->updateOrCreate(
                    [
                        'user_id' => $this->update->callback_query->from->id,
                    ], [
                        'path' => ''
                    ]
                );

                $this->editMessageText([
                    'chat_id' => $this->update->callback_query->message->chat->id,
                    'message_id'=>$this->update->callback_query->message->message_id,
                    'text' => 'Hello ' . $this->update->callback_query->from->first_name . ', ' . chr(10) . 'Choose what you want to do',
                    'reply_markup' => new InlineKeyboardMarkup([
                        'inline_keyboard' => [
                            [
                                new InlineKeyboardButton([
                                    'text' => 'Bill Report',
                                    'callback_data' => 'telecom_bill',
                                ]),
                            ]
                        ],
                    ]),
                ]);
            } catch (TeleBotObjectException $e) {
                Log::info($e->getMessage());
            }

            $this->answerCallbackQuery([
                'callback_query_id' => $this->update->callback_query->id,
                'text' => 'Went to home',
            ]);
        }
    }
}
