<?php


namespace App\Telegram\Commands;


use App\Http\Requests\Auth\LoginRequest;
use App\Telegram\Handlers\BotLoginHandler;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use WeStacks\TeleBot\Handlers\CommandHandler;
use WeStacks\TeleBot\Laravel\TeleBot;

class BotLoginCommand extends CommandHandler
{
    protected static $aliases = ['/login'];

    protected static $description = 'Login to the system';

    /**
     * @inheritDoc
     */
    public function handle()
    {
        $this->sendMessage([
            'chat_id'=>$this->update->message->chat->id,
            'text'=>'Let\'s sign you in, shall we?',
        ]);

        TeleBot::callHandler(BotLoginHandler::class,$this->update,true);
    }

//    /**
//     * Handle an incoming authentication request.
//     *
//     * @param LoginRequest $request
//     * @return RedirectResponse
//     */
//    public function store(LoginRequest $request)
//    {
//        $request->authenticate();
//
//        $request->session()->regenerate();
//
//    }
//
//    /**
//     * Attempt to authenticate the request's credentials.
//     *
//     * @return void
//     *
//     * @throws \Illuminate\Validation\ValidationException
//     */
//    public function authenticate()
//    {
//
//        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
//            RateLimiter::hit($this->throttleKey());
//
//            throw ValidationException::withMessages([
//                'email' => __('auth.failed'),
//            ]);
//        }
//
//    }
}
