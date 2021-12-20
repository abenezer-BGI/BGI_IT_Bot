<?php


namespace App\Telegram\Handlers\eLeader;


use App\Models\BotStatus;
use App\Models\BotUser;
use App\Models\ELeader;
use App\Traits\TelegramCustomTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use WeStacks\TeleBot\Exception\TeleBotObjectException;
use WeStacks\TeleBot\Objects\Message;
use WeStacks\TeleBot\Objects\Update;
use WeStacks\TeleBot\TeleBot;

class BotELeaderCallbackHandler
{
    use TelegramCustomTrait;

    /**
     * @param TeleBot $bot
     * @param Builder|Model $bot_user
     * @param Builder|Model $bot_status
     * @param Message $message
     * @param Update $update
     * @throws TeleBotObjectException
     */
    public function request_phone_number(TeleBot $bot, BotUser $bot_user, BotStatus $bot_status, Message $message, Update $update)
    {
        if (ELeader::query()->where('user_id', '=', $bot_user->id)->doesntExist()) {
            $bot_status->update([
                'path' => 'eLeader',
                'last_question' => 'eLeader_phone_number_request',
                'last_answer' => '',
                'back_path' => 'root',
            ]);

            $bot->sendMessage([
                'chat_id' => $message->chat->id,
                'text' => 'ሰላም' . chr(10) .
                    'ይህ የቢ.ጂ.አይ ኢትዮጵያ ቤተኛ ቴሌግራም ቦት ነው።' . chr(10) .
                    'እባክዎን የእርሶ ቤት የተመዘገበትን ስልክ ቁጥር ይላኩልን።' . chr(10) .
                    'ስልክዎን ሲያስገቡ 09 ብሎ እንዲጀምር ያድርጉት። ለምሳሌ፡ 0900110011',
            ]);
        } else {
            (new BotELeaderUpdateHandler())->eLeader_starting_menu($update, $bot_status,$bot);
        }
    }

    /**
     * @param TeleBot $bot
     * @param Builder|Model $bot_user
     * @param Update $update
     */
    public function send_enqu_amount(TeleBot $bot, $bot_user, Update $update)
    {
        $eLeaderUserData = collect(DB::connection('eLeader')->select("SELECT TOP (1000) [ID] ,[ObjectID] ,[TaskDefID] ,[FieldID] ,[FieldCode] ,[FieldName] ,[FieldValue] ,[ExportDate] FROM [ELeader_DB].[dbo].[_tbEleaderExportObjectParameters] where [_tbEleaderExportObjectParameters].FieldCode = 'OBJ_PARAM_7774424' and [_tbEleaderExportObjectParameters].FieldName='SMS phone number' and [_tbEleaderExportObjectParameters].FieldValue = '" . $bot_user->service_number . "'"));
        $enquMessage = '';

        if ($eLeaderUserData->isNotEmpty()) {
            $enquData = collect(DB::connection('eLeader')->select("SELECT TOP (1000) [ID] ,[ObjectID] ,[TaskDefID] ,[FieldID] ,[FieldCode] ,[FieldName] ,[FieldValue] ,[ExportDate] FROM [ELeader_DB].[dbo].[_tbEleaderExportObjectParameters] where ObjectID='" . $eLeaderUserData->first()->ObjectID . "' and (FieldCode='OBJ_PARAM_EarnedPoints' or FieldCode='OBJ_PARAM_Fidelity_ID' or FieldCode='OBJ_PARAM_BGIID') and (FieldName ='Earned points' or FieldName='Fidelity ID' or FieldName='BGI ID')"));
            if ($enqu = $enquData->where('FieldName', '=', 'Earned points')->first()) {
                $enquMessage .= 'ያለዎት የእንቁ ብዛት ' . $enqu->FieldValue . ' 💎 ነው።';
            } else {
                $enquMessage .= 'ውድ ደንበኛችን የቢ.ጂ.አይ ቤተኛ አገልግሎት አልተመዘገቡም።' . chr(10) .'ለመመዝገብ ፕሮሞተርዎን ያነጋግሩ።';
            }
        } else {
            $enquMessage .= 'ውድ ደንበኛችን የቢ.ጂ.አይ ቤተኛ አገልግሎት ተጠቃሚዎች ዝርዝር ውስጥ አላገኘንዎትም።';
        }

        $bot->sendMessage([
            'chat_id' => $update->callback_query->message->chat->id,
            'text' => $enquMessage,
        ]);
    }

}

/*
 *  Type
 *  Territory
 *  Tin Number
 *  Route
 *  BGI ID
 *  Bottle Distributor
 *  Outlet Tag
 *  Machine Cleaning contact person
 *  Customer Service
 */
