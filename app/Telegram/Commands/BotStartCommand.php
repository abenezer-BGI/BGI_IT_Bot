<?php


namespace App\Telegram\Commands;


use App\Models\BotStatus;
use App\Models\BotUser;
use App\Telegram\Handlers\BotUpdateHandler;
use App\Telegram\Handlers\eLeader\BotELeaderCallbackHandler;
use Illuminate\Support\Facades\Log;
use WeStacks\TeleBot\Exception\TeleBotObjectException;
use WeStacks\TeleBot\Handlers\CommandHandler;
use WeStacks\TeleBot\Objects\InlineKeyboardButton;
use WeStacks\TeleBot\Objects\Keyboard\InlineKeyboardMarkup;

class BotStartCommand extends CommandHandler
{

    protected static $aliases = ['/start'];

    protected static $description = 'Introductory command when the bot is started for the first time';

    public function handle()
    {
        // This will send a message using `sendMessage` method behind the scenes to
        // the user/chat id who triggered this command.
        // `replyWith<Message|Photo|Audio|Video|Voice|Document|Sticker|Location|ChatAction>()` all the available methods are dynamically
        // handled when you replace `send<Method>` with `replyWith` and use the same parameters - except chat_id does NOT need to be included in the array.
//
//        $keyboard = Keyboard::make()
//            ->inline()
//            ->row(
//                Keyboard::inlineButton(['text' => 'Button 1', 'callback_data' => 'data']),
//                Keyboard::inlineButton(['text' => 'Button 2', 'callback_data' => 'data_from_btn2'])
//            );
//
//        $this->replyWithMessage(['text' => 'Hello! Welcome to BGI IT Bot. This is a bot made for BGI IT Department. It\'s in testing mode for now. Below this you can find the list of commands:', 'reply_markup' => $keyboard]);
//        // Trigger another command dynamically from within this command
//        // When you want to chain multiple commands within one or process the request further.
//        // The method supports second parameter arguments which you can optionally pass, By default
//        // it'll pass the same arguments that are received for this command originally.
//        $this->triggerCommand('help');

//        $this->sendMessage([
//            'chat_id' => $this->update->message->chat->id,
//            'text' => 'Hello '.$this->update->message->from->first_name.'!'.chr(10).'Welcome to BGI IT Bot. Want to know what i can do? Click this command here👉 /help',
//        ]);

        try {
            $bot_user = BotUser::query()->updateOrCreate(
                [
                    'chat_id' => $this->update->message->chat->id,
                ], [
                    'first_name' => $this->update->message->from->first_name,
                    'last_name' => $this->update->message->from->last_name ?? null,
                    'username' => $this->update->message->from->username ?? null,
                    'telegram_user_id' => $this->update->message->from->id,
                    'is_bot' => $this->update->message->from->is_bot,
                ]
            );

            BotStatus::query()->updateOrCreate(
                [
                    'user_id' => $bot_user->id,
                ], [
                    'last_question' => '',
                    'last_answer' => '',
                    'path' => '',
                    'back_path' => 'root',
                    'root_path' => 'root',
                ]
            );

            $this->welcome_message($this->update);

        } catch (TeleBotObjectException $e) {
            Log::info($e->getMessage());
        }

    }

    /**
     * Send the first start message
     * @param $update
     * @throws TeleBotObjectException
     */
    public function welcome_message($update)
    {
        if (isset($update->callback_query)) {
            $message = $this->update->callback_query->message;
            $bot_user = BotUser::query()->firstWhere('telegram_user_id', '=', $this->update->callback_query->message->chat->id);
            $bot_status = BotStatus::query()->firstWhere('user_id', '=', $bot_user->id);
//            $this->sendMessage([
//                'text' => 'ሰላም ውድ ደንበኛችን' . chr(10) . 'ምን ማድረግ ይፈልጋሉ?',
//                'chat_id' => $update->callback_query->message->chat->id,
////                'message_id'=>$update->callback_query->message->message_id,
//                'reply_markup' => new InlineKeyboardMarkup([
//                    'inline_keyboard' => [
//                        [
//                            new InlineKeyboardButton([
//                                'text' => 'ቢ.ጂ.አይ ቤተኛ',
//                                'callback_data' => 'eLeader',
//                            ]),
////                                new InlineKeyboardButton([
////                                    'text' => 'Bill Report',
////                                    'callback_data' => 'telecom_bill',
////                                ]),
//                        ]
//                    ],
//                ]),
//            ]);
            (new BotELeaderCallbackHandler())->request_phone_number($this->bot, $bot_user, $bot_status, $message, $update);

        } elseif (isset($update->message)) {
//            $this->sendMessage([
//                'text' => 'ሰላም ' . $update->message->from->first_name . ', ' . chr(10) . 'ምን ማድረግ ይፈልጋሉ?',
//                'chat_id' => $update->message->chat->id,
//                'reply_markup' => new InlineKeyboardMarkup([
//                    'inline_keyboard' => [
//                        [
//                            new InlineKeyboardButton([
//                                'text' => 'ቢ.ጂ.አይ ቤተኛ',
//                                'callback_data' => 'eLeader',
//                            ]),
////                                new InlineKeyboardButton([
////                                    'text' => 'Bill Report',
////                                    'callback_data' => 'telecom_bill',
////                                ]),
//                        ]
//                    ],
//                ]),
//            ]);
            $bot_user = BotUser::query()->firstWhere('telegram_user_id', '=', $this->update->message->chat->id);
            $bot_status = BotStatus::query()->firstWhere('user_id', '=', $bot_user->id);

            (new BotELeaderCallbackHandler())->request_phone_number($this->bot, $bot_user, $bot_status, $update->message, $update);
        }
    }

}
