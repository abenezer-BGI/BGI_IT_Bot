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
    public function send_client_info(TeleBot $bot, BotUser $bot_user, BotStatus $bot_status, Message $message, Update $update)
    {
        $eLeaderUserData = collect(DB::connection('eLeader')->select("SELECT TOP (1000) [ID] ,[ObjectID] ,[TaskDefID] ,[FieldID] ,[FieldCode] ,[FieldName] ,[FieldValue] ,[ExportDate] FROM [ELeader_DB].[dbo].[_tbEleaderExportObjectParameters] where [_tbEleaderExportObjectParameters].FieldCode = 'OBJ_PARAM_7774424' and [_tbEleaderExportObjectParameters].FieldName='SMS phone number' and [_tbEleaderExportObjectParameters].FieldValue = '" . $bot_user->service_number . "'"));

        if ($object = $eLeaderUserData->firstWhere('ObjectID', '!=', null)) {
            $clientInfo = collect(DB::connection('eLeader')->select("select top (1000) ObjectID,FieldCode,FieldName,FieldValue from _tbEleaderExportObjectParameters where ObjectID = '" . $object->ObjectID . "' and ( FieldName = 'Type' or FieldName = 'Territory' or FieldName = 'Tin Number' or FieldName = 'Route' or FieldName = 'BGI ID' or FieldName = 'Bottle Distributor' or FieldName = 'Outlet Tag' or FieldName = 'Machine Cleaning contact person' or FieldName = 'Customer Service' ) and ( FieldCode = 'OBJ_PARAM_TYPE' or FieldCode = 'OBJ_PARAM_TIN' or FieldCode = 'OBJ_PARAM_MCCP' or FieldCode = 'OBJ_PARAM_Territory' or FieldCode = 'OBJ_PARAM_Route' or FieldCode = 'OBJ_PARAM_BotlDsitr' or FieldCode = 'OBJ_PARAM_BGIID' or FieldCode = 'OBJ_PARAM_Outlet_Tag' )"));
            $clientLocation = collect(DB::connection('eLeader')->select("select ObjectID, ObjectName, City, Country from _tbEleaderExportObjects where ObjectID = '" . $object->ObjectID . "'"))->first();
            $clientInfoMessage = '✔️ Name: ' . $clientLocation->ObjectName . chr(10) . '✔️ Location: ' . $clientLocation->City . ', ' . $clientLocation->Country . chr(10);
            foreach ($clientInfo as $info) {
                $clientInfoMessage .= '✔️ ' . $info->FieldName . ': ' . $info->FieldValue . chr(10);
            }

            $bot->sendMessage([
                'chat_id' => $message->chat->id,
                'text' => $clientInfoMessage,
            ]);
        } else {
            $this->not_registered_to_bgi_betegna($bot, $update, 'amharic');
        }
    }

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

            $bot->sendPhoto([
                'chat_id' => $message->chat->id,
                'photo' => 'AgACAgQAAxkBAAIGt2HBcSbngYVZXSG0jyydl6nqQYSwAALytzEbe2oJUjEOj5ziRA8_AQADAgADcwADIwQ',
                'caption' => 'ሰላም ውድ የቦታችን ተጠቃሚ' . chr(10) .
                    'ይህ የቢ.ጂ.አይ ቤተኛ ቴሌግራም ቦት ነው።' . chr(10) .
                    'እባክዎን የእርሶ ቤት የተመዘገበትን ስልክ ቁጥር ይላኩልን።' . chr(10) .
                    'ስልክዎን ሲያስገቡ 09 ብሎ እንዲጀምር ያድርጉት። ለምሳሌ፡ 0900110011',
            ]);
        } else {
            (new BotELeaderUpdateHandler())->eLeader_starting_menu($update, $bot_status, $bot);
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
                $enquMessage .= 'ውድ ደንበኛችን የቢ.ጂ.አይ ቤተኛ አገልግሎት አልተመዘገቡም።' . chr(10) . 'ለመመዝገብ ፕሮሞተርዎን ያነጋግሩ።';
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
