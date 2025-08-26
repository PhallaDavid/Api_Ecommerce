<?php
namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Telegram
{
    public static function sendMessage($chatId, $message)
    {
        $botToken = env('TELEGRAM_BOT_TOKEN');

        if (!$botToken || !$chatId) {
            Log::warning("Telegram bot token or chat ID missing.");
            return;
        }

        $url = "https://api.telegram.org/bot{$botToken}/sendMessage";

        try {
            // Skip SSL verification locally
            Http::withoutVerifying()->post($url, [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'HTML',
            ]);
        } catch (\Exception $e) {
            Log::error("Telegram sendMessage failed: " . $e->getMessage());
        }
    }
}
