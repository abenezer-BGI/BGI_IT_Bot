<?php


namespace App\Telegram\Handlers;

use App\Models\DeviceInventory\Computer;
use App\Models\DeviceInventory\Monitor;
use App\Models\DeviceInventory\Printer;
use Illuminate\Support\Facades\Log;
use WeStacks\TeleBot\Exception\TeleBotObjectException;
use WeStacks\TeleBot\Interfaces\UpdateHandler;
use WeStacks\TeleBot\Objects\InlineKeyboardButton;
use WeStacks\TeleBot\Objects\Keyboard\InlineKeyboardMarkup;
use WeStacks\TeleBot\Objects\Update;
use WeStacks\TeleBot\TeleBot;

class BotUpdateHandler extends UpdateHandler
{

    public static function trigger(Update $update, TeleBot $bot)
    {
//        if(isset($update->message)){
//            return false;
//        }
//        return !str_starts_with($update->message->text,'/');
        return true;
    }

    /**
     * @throws TeleBotObjectException
     */
    public function handle()
    {
        $update = $this->update;
        $bot = $this->bot;

        // Handle all callback queries
        if ($update->type() === 'callback_query') {
            $callback = $update->callback_query;
//            $this->sendMessage([
//                'chat_id' => $callback->message->chat->id,
//                'text' => 'You have chosen the ' . $callback->data . ' app.',
//            ]);


            // Device inventory section
            if ($callback->data === 'device_inventory') {
                $this->editMessageText([
                    'chat_id' => $callback->message->chat->id,
                    'message_id' => $callback->message->message_id,
                    'text' => 'Choose what you want to do',
                    'reply_markup' => new InlineKeyboardMarkup([
                        'inline_keyboard' => [
                            [
                                new InlineKeyboardButton([
                                    'text' => 'Dashboard',
                                    'callback_data' => 'device_inventory.dashboard',
                                ]),
                            ],
                            [
                                new InlineKeyboardButton([
                                    'text' => 'Device Count',
                                    'callback_data' => 'device_inventory.device_per_site',
                                ]),
                            ]
                        ],
                    ]),
                ]);
            }

            if ($callback->data === 'device_inventory.device_per_site') {
                $this->editMessageText([
                    'chat_id' => $callback->message->chat->id,
                    'message_id' => $callback->message->message_id,
                    'text' => 'Choose device type',
                    'reply_markup' => new InlineKeyboardMarkup([
                        'inline_keyboard' => [
                            [
                                new InlineKeyboardButton([
                                    'text' => 'Computer',
                                    'callback_data' => 'device_inventory.device_per_site.computer',
                                ]),
                                new InlineKeyboardButton([
                                    'text' => 'Monitor',
                                    'callback_data' => 'device_inventory.device_per_site.monitor',
                                ]),
                                new InlineKeyboardButton([
                                    'text' => 'Printer',
                                    'callback_data' => 'device_inventory.device_per_site.printer',
                                ]),
                            ]
                        ],
                    ]),
                ]);
            }

            Log::debug('Callback data: ' . $callback->data);
            if ($callback->data === 'device_inventory.device_per_site.computer') {
                $computer_data = Computer::with('site:id,display_name')->selectRaw('count(site_id) as count, device_type, site_id')->groupBy(['device_type', 'site_id'])->get();
                $text = 'Computers Data' . chr(10);
                foreach ($computer_data as $computer) {
                    $text .= $computer->site->display_name .' [' . $computer->device_type . '] = ' . $computer->count . chr(10);
                }
                Log::debug($text);
                $this->sendMessage([
                    'chat_id' => $callback->message->chat->id,
                    'text' => $text,
                ]);
            }

            if ($callback->data === 'device_inventory.device_per_site.monitor') {
                $monitor_data = Monitor::with('site:id,display_name')->selectRaw('count(site_id) as count, device_type, site_id')->groupBy(['device_type', 'site_id'])->get();
                $text = 'Monitors Data' . chr(10);
                foreach ($monitor_data as $monitor) {
                    $text .= $monitor->site->display_name .' [' . $monitor->device_type . '] = ' . $monitor->count . chr(10);
                }
                Log::debug($text);
                $this->sendMessage([
                    'chat_id' => $callback->message->chat->id,
                    'text' => $text,
                ]);
            }

            if ($callback->data === 'device_inventory.device_per_site.printer') {
                $printer_data = Printer::with('site:id,display_name')->selectRaw('count(site_id) as count, device_type, site_id')->groupBy(['device_type', 'site_id'])->get();
                $text = 'Printers Data' . chr(10);
                foreach ($printer_data as $printer) {
                    $text .= $printer->site->display_name .' [' . $printer->device_type . '] = ' . $printer->count . chr(10);
                }
                Log::debug($text);
                $this->sendMessage([
                    'chat_id' => $callback->message->chat->id,
                    'text' => $text,
                ]);
            }

            $this->answerCallbackQuery();
        }
    }
}
