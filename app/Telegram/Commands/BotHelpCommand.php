<?php

namespace App\Telegram\Commands;

use Telegram;
use WeStacks\TeleBot\Handlers\CommandHandler;
use WeStacks\TeleBot\Laravel\TeleBot;

/**
 * Class BotHelpCommand.
 */
class BotHelpCommand extends CommandHandler
{
    /**
     * @var array Command Aliases
     */
    protected static $aliases = ['/help'];
    /**
     * @var string Command Description
     */
    protected static $description = 'To Get a list of all commands';
    /**
     * @var string Command Name
     */
    protected $name = 'help';

    /**
     * {@inheritdoc}
     */
    public function handle()
    {
        $commands = 'Hello '.$this->update->message->from->first_name.'!'.chr(10).'Here are the list of things I can do.'.chr(10);

        foreach (TeleBot::getLocalCommands() as $command) {
            $commands.=$command->command.' - '.$command->description.chr(10);
        }

        $this->sendMessage([
            'chat_id' => $this->update->message->chat->id,
            'text' => $commands,
        ]);
    }
}
