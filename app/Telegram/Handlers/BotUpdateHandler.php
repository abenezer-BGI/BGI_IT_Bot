<?php


namespace App\Telegram\Handlers;


use App\Models\BotStatus;
use App\Models\BotUser;
use App\Telegram\Commands\BotStartCommand;
use App\Telegram\Handlers\eLeader\BotELeaderCallbackHandler;
use App\Telegram\Handlers\eLeader\BotELeaderUpdateHandler;
use App\Traits\TelegramCustomTrait;
use Exception;
use Illuminate\Support\Facades\Log;
use WeStacks\TeleBot\Exception\TeleBotObjectException;
use WeStacks\TeleBot\Interfaces\UpdateHandler;
use WeStacks\TeleBot\Objects\Update;
use WeStacks\TeleBot\TeleBot;

class BotUpdateHandler extends UpdateHandler
{
    use TelegramCustomTrait;

    /**
     * @inheritDoc
     */
    public static function trigger(Update $update, TeleBot $bot)
    {
        if ((isset($update->message->text) and !str_starts_with($update->message->text, '/')) or isset($update->callback_query->data)) {
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

        try {
            if ($this->update->type() === 'callback_query') {
                $message = $this->update->callback_query->message;
                $bot_user = BotUser::query()->firstWhere('telegram_user_id', '=', $this->update->callback_query->message->chat->id);
                $bot_status = BotStatus::query()->firstWhere('user_id', '=', $bot_user->id);
                $callbackData = $this->update->callback_query->data;
                switch ($callbackData) {
                    case 'root':
                        (new BotStartCommand($this->bot, $this->update))->welcome_message($update);
                        break;
                    case 'eLeader':
                        (new BotELeaderCallbackHandler())->request_phone_number($bot, $bot_user, $bot_status, $message, $update);
                        break;
                    case 'eLeader.enqu_amount':
                        (new BotELeaderCallbackHandler())->send_enqu_amount($bot, $bot_user, $update);
                        break;
                    case 'eLeader.client_info':
                        (new BotELeaderCallbackHandler())->send_client_info($bot, $bot_user, $message, $update);
                        break;
                    case 'eLeader.visit_data':
                        (new BotELeaderCallbackHandler())->visit_info($bot, $bot_user, $message);
                        break;
                    case 'eLeader.customer_service':
                        (new BotELeaderCallbackHandler())->customer_service_contact($bot, $message);
                        break;
                    default:
                        $this->error_message($bot, $update, 'amharic');
                        break;
                }

                $this->answerCallbackQuery();
            } elseif ($this->update->type() === 'message') {
                $bot_user = BotUser::query()->firstWhere('telegram_user_id', '=', $this->update->message->chat->id);
                $bot_status = BotStatus::query()->firstWhere('user_id', '=', $bot_user->id);

                switch ($bot_status->last_question) {
                    case 'otp_confirmation':
                        (new BotELeaderUpdateHandler())->otp_confirmation($bot, $bot_user, $bot_status, $update);
                        break;
                    case 'eLeader_phone_number_request':
                        (new BotELeaderUpdateHandler())->phone_number_request($bot, $bot_user, $bot_status, $update);
                        break;
                    default:
                        $this->error_message($bot, $update, 'amharic');
                        break;
                }
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }

    }
}
