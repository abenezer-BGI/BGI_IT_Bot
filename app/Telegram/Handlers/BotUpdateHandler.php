<?php


namespace App\Telegram\Handlers;


use App\Models\BotStatus;
use App\Models\BotUser;
use App\Telegram\Commands\BotStartCommand;
use App\Telegram\Handlers\eLeader\BotELeaderCallbackHandler;
use App\Telegram\Handlers\eLeader\BotELeaderUpdateHandler;
use Illuminate\Support\Facades\DB;
use WeStacks\TeleBot\Exception\TeleBotObjectException;
use WeStacks\TeleBot\Interfaces\UpdateHandler;
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
     * @throws TeleBotObjectException
     */
    public function handle()
    {
        $bot = $this->bot;
        $update = $this->update;

        if ($this->update->type() === 'callback_query') {
            $message = $this->update->callback_query->message;
            $bot_user = BotUser::query()->firstWhere('telegram_user_id', '=', $this->update->callback_query->message->chat->id);
            $bot_status = BotStatus::query()->firstWhere('user_id', '=', $bot_user->id);
            $callbackData = $this->update->callback_query->data;
            if ($callbackData === 'root') {
                (new BotStartCommand($this->bot, $this->update))->welcome_message($update);
            }
            if ($callbackData === 'eLeader') {
                (new BotELeaderCallbackHandler())->request_phone_number($bot, $bot_user, $bot_status, $message, $update);
            }
            if ($callbackData === 'eLeader.enqu_amount') {
                (new BotELeaderCallbackHandler())->send_enqu_amount($bot, $bot_user, $update);
            }
            $this->answerCallbackQuery();
        } elseif ($this->update->type() === 'message') {
            $bot_user = BotUser::query()->firstWhere('telegram_user_id', '=', $this->update->message->chat->id);
            $bot_status = BotStatus::query()->firstWhere('user_id', '=', $bot_user->id);
            if ($bot_status->last_question === 'otp_confirmation') {
                (new BotELeaderUpdateHandler())->otp_confirmation($bot,$bot_user,$bot_status,$update);
            }if ($bot_status->last_question === 'eLeader_phone_number_request') {
                (new BotELeaderUpdateHandler())->phone_number_request($bot,$bot_user,$bot_status,$update);
            }
        }

    }
}

//collect(DB::connection('eLeader')->select("SELECT TOP (1000) [ID]
//,[ObjectID]
//,[TaskDefID]
//,[FieldID]
//,[FieldCode]
//,[FieldName]
//,[FieldValue]
//,[ExportDate]
//FROM [ELeader_DB].[dbo].[_tbEleaderExportObjectParameters] where [_tbEleaderExportObjectParameters].FieldCode = 'OBJ_PARAM_7774424' and [_tbEleaderExportObjectParameters].FieldName='SMS phone number' and [_tbEleaderExportObjectParameters].FieldValue = '0900000000'"))
