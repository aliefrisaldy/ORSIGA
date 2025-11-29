<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Console\Commands\TelegramBot;
use App\Console\Commands\Log;

class TelegramWebhookController extends Controller
{
    /**
     * Handle incoming webhook from Telegram
     */
    public function handle(Request $request)
    {
        try {
            // Ambil data update dari Telegram
            $updateData = $request->all();
            
            // Log untuk debugging (opsional, hapus di production)
            // \Log::info('Telegram Webhook received:', $updateData);
            
            // Panggil TelegramBot untuk process update
            $bot = new TelegramBot();
            $bot->handleWebhookUpdate($updateData);
            
            // Return 200 OK agar Telegram tidak retry
            return response()->json(['status' => 'ok'], 200);
            
        } catch (\Exception $e) {
            // \Log::error('Telegram Webhook Error: ' . $e->getMessage());
            return response()->json(['status' => 'error'], 500);
        }
    }
}