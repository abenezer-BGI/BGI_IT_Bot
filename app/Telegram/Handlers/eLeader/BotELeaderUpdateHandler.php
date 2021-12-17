<?php


namespace App\Telegram\Handlers\eLeader;


use App\Models\BotStatus;
use App\Models\BotUser;
use App\Models\ELeader;
use App\Traits\TelegramCustomTrait;
use Illuminate\Support\Facades\DB;
use Nette\Utils\Random;
use WeStacks\TeleBot\Exception\TeleBotObjectException;
use WeStacks\TeleBot\Interfaces\UpdateHandler;
use WeStacks\TeleBot\Objects\InlineKeyboardButton;
use WeStacks\TeleBot\Objects\Keyboard\InlineKeyboardMarkup;
use WeStacks\TeleBot\Objects\Update;
use WeStacks\TeleBot\TeleBot;

class BotELeaderUpdateHandler extends UpdateHandler
{
    use TelegramCustomTrait;

    /**
     * @inheritDoc
     */
    public static function trigger(Update $update, TeleBot $bot)
    {
        if ($update->type() !== 'callback_query' and isset($update->message->text)) {
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
        $bot_user = BotUser::query()->firstWhere('chat_id', '=', $update->message->chat->id);
        $bot_status = BotStatus::query()->firstWhere('user_id', '=', $bot_user->id);

        if ($bot_status->last_question === 'otp_confirmation') {
            $eLeaderObject = collect(DB::connection('eLeader')->select("SELECT TOP (1000) [ID] ,[ObjectID] ,[TaskDefID] ,[FieldID] ,[FieldCode] ,[FieldName] ,[FieldValue] ,[ExportDate] FROM [ELeader_DB].[dbo].[_tbEleaderExportObjectParameters] where [_tbEleaderExportObjectParameters].FieldCode = 'OBJ_PARAM_7774424' and [_tbEleaderExportObjectParameters].FieldName='SMS phone number' and [_tbEleaderExportObjectParameters].FieldValue = '" . $bot_user->service_number . "'"));
            if ($update->message->text === $bot_status->last_answer) {
                if ($eLeaderObject->isNotEmpty()) {
                    $fidelityData = collect(DB::connection('eLeader')->select("SELECT TOP (1000) [ID]
                                        ,[ObjectID]
                                        ,[TaskDefID]
                                        ,[FieldID]
                                        ,[FieldCode]
                                        ,[FieldName]
                                        ,[FieldValue]
                                        ,[ExportDate]
                                        FROM [ELeader_DB].[dbo].[_tbEleaderExportObjectParameters]
                                        where ObjectID= '".$eLeaderObject->first()->ObjectID."'
                                        and (FieldCode='OBJ_PARAM_EarnedPoints' or FieldCode='OBJ_PARAM_Fidelity_ID')
                                        and (FieldName ='Earned points' or FieldName='Fidelity ID')"));
                    foreach ($fidelityData as $fidelity) {
                        ELeader::query()->updateOrCreate(
                            [
                                'fidelity_id' => $fidelity->where('FieldCode','OBJ_PARAM_Fidelity_ID')->first()->FieldValue,
                            ], [
                                'client_name' => $fidelity->client_name,
                                'user_id' => $bot_user->id,
                                'bgi_id' => $fidelity->bgi_id,
                                'phone_number' => $fidelity->phone_number,
                            ]
                        );
                        $this->eLeader_starting_menu($update, $bot_status);
                    }
                }
            } else {
                $this->sendMessage([
                    'chat_id' => $update->message->chat->id,
                    'text' => 'ያስገቡት ቁጥር እና እኛ የላክነው ቁጥር አይመሳሰሉም። እባክዎን ትክክለኛውን ቁጥር ያስገቡ።',
                    'reply_markup' => new InlineKeyboardMarkup([
                        'inline_keyboard' => [
                            [
                                new InlineKeyboardButton([
                                    'text' => '<< ተመለስ',
                                    'callback_data' => $bot_status->back_path,
                                ]),
                            ],
                        ],
                    ]),
                ]);
            }
        }
        if ($bot_status->last_question === 'eLeader_phone_number_request') {

            if (preg_match('/^[0-9]+$/', $update->message->text) and strlen($update->message->text) === 10) {
                if ($eLeaderObject = DB::table('sample')->where('phone_number', '=', $bot_user->service_number)->exists()) {
                    $bot_status->update([
                        'path' => $this->path_append($bot_status->path, '/phone_number_received'),
                        'last_question' => 'otp_confirmation',
                        'back_path' => 'eLeader',
                        'last_answer' => $update->message->text,
                    ]);
                    $bot_user->update([
                        'service_number' => $update->message->text,
                    ]);

                    $otp_code = Random::generate(6, '0-9');
                    $otp_message = 'BGI+Code:+' . $otp_code;
                    $url = 'http://10.10.1.59:9501/api?action=sendmessage&username=' . env('OZEKING_USERNAME', 'admin') . '&password=' . env('OZEKING_PASSWORD', 'admin') . '&recipient=' . $bot_status->last_answer . '&messagetype=SMS:TEXT&messagedata=' . $otp_message;
                    var_dump($bot_status->last_answer);

                    $curl = curl_init();
                    curl_setopt($curl, CURLOPT_URL, $url);
                    curl_exec($curl);

                    $this->sendMessage([
                        'chat_id' => $update->message->chat->id,
                        'text' => 'ስልክዎን ስለላኩልን በጣም እናመሰግናለን።' . chr(10) .
                            'ያስገቡት ስልክ ቁጥር የእርስዎ እንደሆነ ለማረጋገጥ አጭር የጽሁፍ መልእክት ልከንበታል።' . chr(10) .
                            'መልእክቱ ሲደርሶት በውስጡ የተካተተውን የሚስጥር ቁጥር ይላኩልን።',
                        'reply_markup' => new InlineKeyboardMarkup([
                            'inline_keyboard' => [
                                [
                                    new InlineKeyboardButton([
                                        'text' => '<< ተመለስ',
                                        'callback_data' => $bot_status->back_path,
                                    ]),
                                ]
                            ],
                        ]),
                    ]);

                    $bot_status->update([
                        'path' => $this->path_append($bot_status->path, '/otp_sent'),
                        'last_answer' => $otp_code,
                    ]);
                } else {
                    $this->sendMessage([
                        'chat_id' => $update->message->chat->id,
                        'text' => 'ይቅርታ! ባስገቡት የስልክ ቁጥር የተመዘገብ ቤት የለም።',
                        'reply_markup' => new InlineKeyboardMarkup([
                            'inline_keyboard' => [
                                [
                                    new InlineKeyboardButton([
                                        'text' => '<< ተመለስ',
                                        'callback_data' => $bot_status->back_path,
                                    ]),
                                ],
                            ],
                        ]),
                    ]);
                }
            } else {
                $this->error_message($update, 'amharic');
            }

        }
    }

    /**
     * Displays the eLeader starting menu
     * @param $update
     * @param $bot_status
     * @param $bot_user
     * @throws TeleBotObjectException
     */
    public function eLeader_starting_menu($update, $bot_status)
    {
        $this->sendMessage([
            'chat_id' => $update->message->chat->id ?? $update->callback_query->message->chat->id,
            'text' => 'ውድ የቢ.ጂ.አይ ቤተኛ ደንበኛችንመጡ እንኳን ወደ ቴሌግራም ቦታችን በሰላም መጡ።' . chr(10) .
                'ከታች ያሉትን አገልግሎቶች ማግኘት ይችላሉ።',
            'reply_markup' => new InlineKeyboardMarkup([
                'inline_keyboard' => [
                    [
                        new InlineKeyboardButton([
                            'text' => 'እንቁ ብዛት',
                            'callback_data' => 'eLeader.enqu_amount',
                        ]),
                    ],
                    [
                        new InlineKeyboardButton([
                            'text' => '<< ተመለስ',
                            'callback_data' => $bot_status->back_path,
                        ]),
                    ],
                ],
            ]),
        ]);
    }
}
