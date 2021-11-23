<?php


namespace App\Telegram\Handlers\TelecomBill;


use App\Models\BotStatus;
use App\Models\BotUser;
use WeStacks\TeleBot\Interfaces\UpdateHandler;
use WeStacks\TeleBot\Objects\InlineKeyboardButton;
use WeStacks\TeleBot\Objects\Keyboard\InlineKeyboardMarkup;
use WeStacks\TeleBot\Objects\Update;
use WeStacks\TeleBot\TeleBot;

class BotTelecomBillUpdateHandler extends UpdateHandler
{

    /**
     * @inheritDoc
     */
    public static function trigger(Update $update, TeleBot $bot)
    {
        if ($update->type() !== 'callback_query' and isset($update->message)) {
            return true;
        }
        return false;
    }

    /**
     * @inheritDoc
     * @throws \WeStacks\TeleBot\Exception\TeleBotObjectException
     */
    public function handle()
    {
        $update = $this->update;
        $message = $update->message;
        $bot_user = BotUser::query()->firstWhere('telegram_user_id', '=', $message->from->id);
        $bot_status = BotStatus::query()->firstWhere('user_id', '=', $message->from->id);

        if ($bot_status->last_question === 'telecom_bill.service_number') {
            if (preg_match('/^[0-9]+$/', $update->message->text)) {
                $bot_user->update([
                    'service_number' => $update->message->text,
                ]);
                $bot_status->update([
                    'last_question' => null,
                ]);
                $this->sendMessage([
                    'chat_id' => $message->chat->id,
                    'text' => 'Success! I have successfully received your service number.' . chr(10) .
                        'Now your telegram account @' . $bot_user->username . ' is connected to the service number ' . $bot_user->service_number,
                ]);

                $this->sendMessage([
                    'chat_id' => $message->chat->id,
                    'message_id' => $message->message_id,
                    'text' => 'Please select the bill YEAR you want to browse',
                    'reply_markup' => new InlineKeyboardMarkup([
                        'inline_keyboard' => [
                            [
                                new InlineKeyboardButton([
                                    'text' => now()->subYears(2)->format('Y'),
                                    'callback_data' => 'telecom_bill.year.'.now()->subYears(2)->format('Y'),
                                ]),
                                new InlineKeyboardButton([
                                    'text' => now()->subYears(1)->format('Y'),
                                    'callback_data' => 'telecom_bill.year.'.now()->subYears(1)->format('Y'),
                                ]),
                                new InlineKeyboardButton([
                                    'text' => now()->format('Y'),
                                    'callback_data' => 'telecom_bill.year.'.now()->format('Y'),
                                ]),
                            ],
                        ],
                    ]),
                ]);
            } else {
                $this->sendMessage([
                    'chat_id' => $message->chat->id,
                    'text' => 'Error! Please send a valid phone number as per requested format.' . chr(10) .
                        'Format: 9xxxxxxxx',
                ]);
            }
        }
    }

    public function path_append($path, $text)
    {
        $array_path = explode('.', $path);
        return end($array_path) === 'telecom_bill' ? $path : $path . $text;
    }
}
