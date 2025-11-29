<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Telegram\Bot\Api;

class SetTelegramWebhook extends Command
{
    protected $signature = 'telegram:webhook {action=info}';
    protected $description = 'Manage Telegram webhook (set, remove, info)';

    public function handle()
    {
        $telegram = new Api(env('TELEGRAM_BOT_TOKEN'));
        $action = $this->argument('action');

        try {
            switch ($action) {
                case 'set':
                    $webhookUrl = env('APP_URL') . '/telegram/webhook';
                    
                    $this->info("Setting webhook to: $webhookUrl");
                    
                    $response = $telegram->setWebhook(['url' => $webhookUrl]);
                    
                    if ($response) {
                        $this->info("✅ Webhook berhasil diset!");
                    }
                    break;

                case 'remove':
                    $this->info("Removing webhook...");
                    $response = $telegram->removeWebhook();
                    
                    if ($response) {
                        $this->info("✅ Webhook berhasil dihapus!");
                    }
                    break;

                case 'info':
                    $webhookInfo = $telegram->getWebhookInfo();
                    
                    $this->info("📋 Webhook Information:");
                    $this->line("URL: " . ($webhookInfo['url'] ?: 'Not set'));
                    $this->line("Pending Updates: " . $webhookInfo['pending_update_count']);
                    
                    if (isset($webhookInfo['last_error_date'])) {
                        $this->warn("Last Error: " . $webhookInfo['last_error_message']);
                    }
                    break;

                default:
                    $this->error("Unknown action. Use: set, remove, or info");
                    break;
            }
        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
        }
    }
}