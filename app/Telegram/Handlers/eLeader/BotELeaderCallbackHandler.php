<?php


namespace App\Telegram\Handlers\eLeader;


use App\Models\BotStatus;
use App\Models\BotUser;
use App\Models\ELeader;
use Illuminate\Support\Facades\DB;
use WeStacks\TeleBot\Exception\TeleBotObjectException;
use WeStacks\TeleBot\Interfaces\UpdateHandler;
use WeStacks\TeleBot\Objects\Update;
use WeStacks\TeleBot\TeleBot;

class BotELeaderCallbackHandler extends UpdateHandler
{

    /**
     * @inheritDoc
     */
    public static function trigger(Update $update, TeleBot $bot)
    {
        if ($update->type() === 'callback_query' and str_starts_with($update->callback_query->data, 'eLeader')) {
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
        $update = $this->update;
        $callback = $update->callback_query;
        $callbackData = $callback->data;
        $bot_user = BotUser::query()->firstWhere('telegram_user_id', '=', $callback->message->chat->id);
        $bot_status = BotStatus::query()->firstWhere('user_id', '=', $bot_user->id);

        if ($callbackData === 'eLeader') {
            if (ELeader::query()->where('user_id', '=', $bot_user->id)->doesntExist()) {
                $bot_status->update([
                    'path' => 'eLeader',
                    'last_question' => 'eLeader_phone_number_request',
                    'last_answer' => '',
                    'back_path' => 'root',
                ]);

                $this->sendMessage([
                    'chat_id' => $callback->message->chat->id,
//                    'photo' => public_path('assets/images/bgi_betegna_image.jpg'),
                    'text' => 'ሰላም' . chr(10) .
                        'ይህ የቢ.ጂ.አይ ኢትዮጵያ ቤተኛ ቴሌግራም ቦት ነው።' . chr(10) .
                        'እባክዎን የእርሶ ቤት የተመዘገበትን ስልክ ቁጥር ይላኩልን።' . chr(10) .
                        'ስልክዎን ሲያስገቡ 09 ብሎ እንዲጀምር ያድርጉት። ለምሳሌ፡ 0900110011',
                ]);
            } else {
                (new BotELeaderUpdateHandler($this->bot, $update))->eLeader_starting_menu($update, $bot_status);
            }
        }
        if ($callbackData === 'eLeader.enqu_amount') {
            $eLeaderUserData = collect(DB::connection('eLeader')->select("SELECT TOP (1000) [ID] ,[ObjectID] ,[TaskDefID] ,[FieldID] ,[FieldCode] ,[FieldName] ,[FieldValue] ,[ExportDate] FROM [ELeader_DB].[dbo].[_tbEleaderExportObjectParameters] where [_tbEleaderExportObjectParameters].FieldCode = 'OBJ_PARAM_7774424' and [_tbEleaderExportObjectParameters].FieldName='SMS phone number' and [_tbEleaderExportObjectParameters].FieldValue = '" . $bot_user->service_number . "'"));
            $enquMessage = '';

            if ($eLeaderUserData->isNotEmpty()) {
                $enquData = collect(DB::connection('eLeader')->select("SELECT TOP (1000) [ID] ,[ObjectID] ,[TaskDefID] ,[FieldID] ,[FieldCode] ,[FieldName] ,[FieldValue] ,[ExportDate] FROM [ELeader_DB].[dbo].[_tbEleaderExportObjectParameters] where ObjectID='" . $eLeaderUserData->first()->ObjectID . "' and (FieldCode='OBJ_PARAM_EarnedPoints' or FieldCode='OBJ_PARAM_Fidelity_ID' or FieldCode='OBJ_PARAM_BGIID') and (FieldName ='Earned points' or FieldName='Fidelity ID' or FieldName='BGI ID')"));
                if ($enqu = $enquData->where('FieldName', '=', 'Earned points')->first()) {
                    $enquMessage .= 'ያለዎት የእንቁ ብዛት ' . $enqu->FieldValue . ' እንቁዎች ነው።';
                } else {
                    $enquMessage .= 'ውድ ደንበኛችን የቢ.ጂ.አይ ቤተኛ አገልግሎት አልተመዘገቡም።' . chr(10) .
                        'ለመመዝገብ ፕሮሞተርዎን ያነጋግሩ።';
                }
            }else{
                $enquMessage .= 'ውድ ደንበኛችን የቢ.ጂ.አይ ቤተኛ አገልግሎት ተጠቃሚዎች ዝርዝር ውስጥ አላገኘንዎትም።';
            }

            $this->sendMessage([
                'chat_id' => $update->callback_query->message->chat->id,
                'text' => $enquMessage,
            ]);

        }


        $this->answerCallbackQuery();
    }
}
