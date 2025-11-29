<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Telegram\Bot\Api;
use App\Models\RepairReport as Report;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class TelegramBot extends Command
{
    protected $signature = 'bot:run';
    protected $description = 'Run Telegram bot with polling';

    private $telegram;

    // Mapping dari pilihan bot (Indonesian) ke database (English)
    private $penyelesaianMap = [
        'Permanen' => 'Permanent',
        'Temporer' => 'Temporary'
    ];

    private $penyebabMap = [
        'Vandalisme' => 'Vandalism',
        'Gangguan Hewan' => 'Animal Disturbance',
        'Aktivitas Pihak Ketiga' => 'Third Party Activity',
        'Gangguan Alam' => 'Natural Disturbance',
        'Masalah Listrik' => 'Electrical Issue',
        'Kecelakaan Lalu Lintas' => 'Traffic Accident'
    ];

    private $tipeKabelMap = [
        'Network' => 'Network',
        'Access' => 'Access'
    ];

    public function __construct()
    {
        parent::__construct();
        $this->telegram = new Api(env('TELEGRAM_BOT_TOKEN'));
    }

    // Method untuk POLLING (dipanggil saat php artisan bot:run)
    public function handle()
    {
        $offset = 0;
        $this->info("🤖 Bot is running with polling...");

        while (true) {
            $updates = $this->telegram->getUpdates([
                'offset' => $offset + 1,
                'timeout' => 60,
            ]);

            foreach ($updates as $update) {
                $offset = $update->getUpdateId();
                
                // Panggil method yang sama untuk process update
                $this->processUpdate($update);
            }
            
            sleep(1);
        }
    }

    // Method untuk WEBHOOK (dipanggil dari controller)
    public function handleWebhookUpdate($updateData)
    {
        // Convert array to Update object
        $update = new \Telegram\Bot\Objects\Update($updateData);
        
        // Panggil method yang sama untuk process update
        $this->processUpdate($update);
    }

    // Method utama untuk process update (dipakai oleh polling DAN webhook)
    private function processUpdate($update)
    {
        $message = $update->getMessage();
        $callback = $update->getCallbackQuery();

        // ⬇️ Handle callback queries
        if ($callback) {
            $chatId = $callback->getMessage()->getChat()->getId();
            $data = $callback->getData();
            $state = Cache::get("report_state_$chatId");
            $reportData = Cache::get("report_data_$chatId", []);

            // Answer callback query immediately
            try {
                $this->telegram->answerCallbackQuery([
                    'callback_query_id' => $callback->getId(),
                ]);
            } catch (\Exception $e) {
                // Ignore timeout
            }

            // 🔹 Handle menu utama
            if ($state === 'main_menu') {
                if ($data === 'create_report') {
                    $this->telegram->sendMessage([
                        'chat_id' => $chatId,
                        'text' => "📝 Mari isi laporan gangguan.\nMasukkan *No Tiket*:",
                        'parse_mode' => 'Markdown'
                    ]);
                    Cache::put("report_state_$chatId", 'no_tiket', 300);
                } elseif ($data === 'update_report') {
                    $this->telegram->sendMessage([
                        'chat_id' => $chatId,
                        'text' => "🔄 Masukkan *No Tiket* yang ingin diupdate:",
                        'parse_mode' => 'Markdown'
                    ]);
                    Cache::put("report_state_$chatId", 'update_ticket', 300);
                } elseif ($data === 'get_data_by_ticket') {
                    $this->telegram->sendMessage([
                        'chat_id' => $chatId,
                        'text' => "🔍 Masukkan *No Tiket* yang ingin dicari:",
                        'parse_mode' => 'Markdown'
                    ]);
                    Cache::put("report_state_$chatId", 'search_ticket', 300);
                } elseif ($data === 'get_temporary_reports') {
                    $this->sendTemporaryReports($chatId);
                }
                return;
            }

            // 🆕 Handle tipe kabel untuk tiket BARU
            if ($state === 'tipe_kabel') {
                if ($data === 'Network' || $data === 'Access') {
                    $reportData['cable_type'] = $data;
                    Cache::put("report_data_$chatId", $reportData, 300);

                    $this->telegram->sendMessage([
                        'chat_id' => $chatId,
                        'text' => "⚠️ Pilih penyebab gangguan:",
                        'reply_markup' => json_encode([
                            'inline_keyboard' => [
                                [
                                    ['text' => 'Vandalisme', 'callback_data' => 'Vandalisme'],
                                    ['text' => 'Gangguan Hewan', 'callback_data' => 'Gangguan Hewan']
                                ],
                                [
                                    ['text' => 'Aktivitas Pihak Ketiga', 'callback_data' => 'Aktivitas Pihak Ketiga'],
                                    ['text' => 'Gangguan Alam', 'callback_data' => 'Gangguan Alam']
                                ],
                                [
                                    ['text' => 'Masalah Listrik', 'callback_data' => 'Masalah Listrik'],
                                    ['text' => 'Kecelakaan Lalu Lintas', 'callback_data' => 'Kecelakaan Lalu Lintas']
                                ]
                            ]
                        ])
                    ]);

                    Cache::put("report_state_$chatId", 'penyebab_gangguan', 300);
                }
                return;
            }

            // 🆕 Handle penyebab gangguan untuk tiket BARU
            if ($state === 'penyebab_gangguan') {
                $validCauses = array_keys($this->penyebabMap);
                
                if (in_array($data, $validCauses)) {
                    $reportData['disruption_cause'] = $this->penyebabMap[$data];
                    Cache::put("report_data_$chatId", $reportData, 300);

                    $this->telegram->sendMessage([
                        'chat_id' => $chatId,
                        'text' => "🔧 Pilih jenis penyelesaian gangguan:",
                        'reply_markup' => json_encode([
                            'inline_keyboard' => [
                                [
                                    ['text' => 'Permanen', 'callback_data' => 'Permanen'],
                                    ['text' => 'Temporer', 'callback_data' => 'Temporer']
                                ]
                            ]
                        ])
                    ]);

                    Cache::put("report_state_$chatId", 'penyelesaian', 300);
                }
                return;
            }

            // 🔹 Handle penyelesaian untuk tiket BARU
            if ($state === 'penyelesaian') {
                if ($data === 'Permanen' || $data === 'Temporer') {
                    $reportData['repair_type'] = $this->penyelesaianMap[$data];
                    Cache::put("report_data_$chatId", $reportData, 300);

                    $this->telegram->sendMessage([
                        'chat_id' => $chatId,
                        'text' => "📝 Masukkan *Detail Pekerjaan*:",
                        'parse_mode' => 'Markdown'
                    ]);

                    Cache::put("report_state_$chatId", 'detail', 300);
                }
                return;
            }

            // 🔹 Handle penyelesaian untuk UPDATE tiket
            if ($state === 'update_penyelesaian') {
                if ($data === 'Permanen') {
                    $reportData['repair_type'] = 'Permanent';
                } elseif ($data === 'Temporer') {
                    $reportData['repair_type'] = 'Temporary';
                }

                Cache::put("report_data_$chatId", $reportData, 300);

                $this->telegram->sendMessage([
                    'chat_id' => $chatId,
                    'text' => "📸 Silakan kirim foto dokumentasi (bisa beberapa kali).\nKlik tombol *Selesai* jika sudah cukup.",
                    'parse_mode' => 'Markdown',
                    'reply_markup' => json_encode([
                        'keyboard' => [
                            [['text' => '✅ Selesai']]
                        ],
                        'resize_keyboard' => true,
                        'one_time_keyboard' => true
                    ])
                ]);

                Cache::put("report_state_$chatId", 'update_dokumentasi', 300);
                return;
            }

            // ✅ Handle relasi tiket - tombol selesai
            if ($state === 'relasi' && $data === 'relasi_selesai') {
                $selectedRelations = Cache::get("report_relations_$chatId", []);

                // 🔹 Cek apakah tiket sudah ada
                $existingReport = Report::where('ticket_number', $reportData['ticket_number'])->first();

                if ($existingReport) {
                    // Append detail lama + baru
                    $newDetail = $existingReport->work_details
                        ? $existingReport->work_details . "\n---\n" . ($reportData['work_details'] ?? '')
                        : ($reportData['work_details'] ?? '');

                    // Gabungkan dokumentasi lama + baru
                    if (is_string($existingReport->documentation)) {
                        $oldDocs = json_decode($existingReport->documentation, true) ?: [];
                    } elseif (is_array($existingReport->documentation)) {
                        $oldDocs = $existingReport->documentation;
                    } else {
                        $oldDocs = [];
                    }
                    $newDocs = $reportData['documentation'] ?? [];
                    $mergedDocs = array_merge($oldDocs, $newDocs);

                    $existingReport->update([
                        'technician_name' => $reportData['technician_name'] ?? $existingReport->technician_name,
                        'work_details' => $newDetail,
                        'repair_type' => $reportData['repair_type'] ?? $existingReport->repair_type,
                        'cable_type' => $reportData['cable_type'] ?? $existingReport->cable_type,
                        'disruption_cause' => $reportData['disruption_cause'] ?? $existingReport->disruption_cause,
                        'documentation' => $mergedDocs,
                    ]);

                    $report = $existingReport;

                    $this->telegram->sendMessage([
                        'chat_id' => $chatId,
                        'text' => "✅ Data laporan untuk *No Tiket {$reportData['ticket_number']}* berhasil *diperbarui*.",
                        'parse_mode' => 'Markdown'
                    ]);
                } else {
                    $report = Report::create([
                        'ticket_number' => $reportData['ticket_number'],
                        'technician_name' => $reportData['technician_name'] ?? null,
                        'latitude' => $reportData['latitude'] ?? null,
                        'longitude' => $reportData['longitude'] ?? null,
                        'work_details' => $reportData['work_details'] ?? null,
                        'repair_type' => $reportData['repair_type'] ?? null,
                        'cable_type' => $reportData['cable_type'] ?? null,
                        'disruption_cause' => $reportData['disruption_cause'] ?? null,
                        'documentation' => $reportData['documentation'] ?? []
                    ]);

                    $this->telegram->sendMessage([
                        'chat_id' => $chatId,
                        'text' => "✅ Laporan baru berhasil disimpan dengan No Tiket {$report->ticket_number}."
                    ]);
                }

                // 🔹 Simpan semua relasi yang dipilih
                if (!empty($selectedRelations)) {
                    foreach ($selectedRelations as $ticketNumber) {
                        $relatedReport = Report::where('ticket_number', $ticketNumber)->first();
                        
                        if ($relatedReport) {
                            $report->relatedReports()->syncWithoutDetaching($relatedReport->id_repair_reports);
                            $relatedReport->relatedReports()->syncWithoutDetaching($report->id_repair_reports);
                        }
                    }
                }

                // Reset cache dan kembali ke menu utama
                $this->resetCacheAndShowMenu($chatId);
                return;
            }

            // ✅ Handle relasi - tombol tambah lagi
            if ($state === 'relasi_confirm' && $data === 'relasi_tambah_lagi') {
                $this->telegram->sendMessage([
                    'chat_id' => $chatId,
                    'text' => "🔗 Masukkan *No Tiket* yang ingin direlasikan:",
                    'parse_mode' => 'Markdown'
                ]);

                Cache::put("report_state_$chatId", 'relasi', 300);
                return;
            }

            // ✅ Handle relasi confirm - tombol selesai
            if ($state === 'relasi_confirm' && $data === 'relasi_confirm_selesai') {
                $selectedRelations = Cache::get("report_relations_$chatId", []);

                // Simpan laporan
                $existingReport = Report::where('ticket_number', $reportData['ticket_number'])->first();

                if ($existingReport) {
                    $newDetail = $existingReport->work_details
                        ? $existingReport->work_details . "\n---\n" . ($reportData['work_details'] ?? '')
                        : ($reportData['work_details'] ?? '');

                    if (is_string($existingReport->documentation)) {
                        $oldDocs = json_decode($existingReport->documentation, true) ?: [];
                    } elseif (is_array($existingReport->documentation)) {
                        $oldDocs = $existingReport->documentation;
                    } else {
                        $oldDocs = [];
                    }
                    $newDocs = $reportData['documentation'] ?? [];
                    $mergedDocs = array_merge($oldDocs, $newDocs);

                    $existingReport->update([
                        'technician_name' => $reportData['technician_name'] ?? $existingReport->technician_name,
                        'work_details' => $newDetail,
                        'repair_type' => $reportData['repair_type'] ?? $existingReport->repair_type,
                        'cable_type' => $reportData['cable_type'] ?? $existingReport->cable_type,
                        'disruption_cause' => $reportData['disruption_cause'] ?? $existingReport->disruption_cause,
                        'documentation' => $mergedDocs, 
                    ]);

                    $report = $existingReport;

                    $this->telegram->sendMessage([
                        'chat_id' => $chatId,
                        'text' => "✅ Data laporan untuk *No Tiket {$reportData['ticket_number']}* berhasil *diperbarui*.",
                        'parse_mode' => 'Markdown'
                    ]);
                } else {
                    $report = Report::create([
                        'ticket_number' => $reportData['ticket_number'],
                        'technician_name' => $reportData['technician_name'] ?? null,
                        'latitude' => $reportData['latitude'] ?? null,
                        'longitude' => $reportData['longitude'] ?? null,
                        'work_details' => $reportData['work_details'] ?? null,
                        'repair_type' => $reportData['repair_type'] ?? null,
                        'cable_type' => $reportData['cable_type'] ?? null,
                        'disruption_cause' => $reportData['disruption_cause'] ?? null,
                        'documentation' => $reportData['documentation'] ?? [],
                    ]);

                    $this->telegram->sendMessage([
                        'chat_id' => $chatId,
                        'text' => "✅ Laporan baru berhasil disimpan dengan No Tiket {$report->ticket_number}."
                    ]);
                }

                // Simpan relasi
                if (!empty($selectedRelations)) {
                    foreach ($selectedRelations as $ticketNumber) {
                        $relatedReport = Report::where('ticket_number', $ticketNumber)->first();
                        
                        if ($relatedReport) {
                            $report->relatedReports()->syncWithoutDetaching($relatedReport->id_repair_reports);
                            $relatedReport->relatedReports()->syncWithoutDetaching($report->id_repair_reports);
                        }
                    }
                }

                $this->resetCacheAndShowMenu($chatId);
                return;
            }
        }

        // 🔹 Handle normal messages
        if ($message) {
            $chatId = $message->getChat()->getId();
            $text = $message->getText();
            $location = $message->getLocation();
            $photo = $message->getPhoto();

            $state = Cache::get("report_state_$chatId", 'start');
            $data = Cache::get("report_data_$chatId", []);

            // ✅ Handle commands
            if ($text === '/cancel') {
                $this->resetCacheAndShowMenu($chatId);
                return;
            }

            if ($text === '/start' || $text === '/menu') {
                $this->showMainMenu($chatId);
                return;
            }

            // 🔹 Handle states
            switch ($state) {
                case 'start':
                    $this->showMainMenu($chatId);
                    break;

                case 'search_ticket':
                    $noTiket = trim($text);
                    if (empty($noTiket)) {
                        $this->telegram->sendMessage([
                            'chat_id' => $chatId,
                            'text' => "❌ No Tiket tidak boleh kosong. Silakan masukkan lagi."
                        ]);
                        break;
                    }

                    $this->searchReportByTicket($chatId, $noTiket);
                    break;

                case 'update_ticket':
                    $noTiket = trim($text);
                    if (empty($noTiket)) {
                        $this->telegram->sendMessage([
                            'chat_id' => $chatId,
                            'text' => "❌ No Tiket tidak boleh kosong. Silakan masukkan lagi."
                        ]);
                        break;
                    }

                    // Cek apakah tiket ada di database
                    $existingReport = Report::where('ticket_number', $noTiket)->first();
                    if (!$existingReport) {
                        $this->telegram->sendMessage([
                            'chat_id' => $chatId,
                            'text' => "❌ No Tiket *$noTiket* tidak ditemukan di database.\nSilakan periksa kembali atau 📝 *Buat Laporan baru*.",
                            'parse_mode' => 'Markdown'
                        ]);
                        $this->showMainMenu($chatId);
                        break;
                    }

                    // Simpan tiket untuk update
                    $data['ticket_number'] = $noTiket;
                    Cache::put("report_data_$chatId", $data, 300);

                    $this->telegram->sendMessage([
                        'chat_id' => $chatId,
                        'text' => "✅ Tiket *$noTiket* ditemukan.\nMasukkan *Nama Teknisi* untuk update:",
                        'parse_mode' => 'Markdown'
                    ]);
                    Cache::put("report_state_$chatId", 'update_nama_teknisi', 300);
                    break;

                case 'no_tiket':
                    $noTiket = trim($text);

                    if (empty($noTiket)) {
                        $this->telegram->sendMessage([
                            'chat_id' => $chatId,
                            'text' => "❌ No Tiket tidak boleh kosong. Silakan masukkan lagi."
                        ]);
                        break;
                    }

                    // Cek apakah tiket sudah ada di database
                    $existing = Report::where('ticket_number', $noTiket)->first();

                    if ($existing) {
                        $this->telegram->sendMessage([
                            'chat_id' => $chatId,
                            'text' => "⚠️ Tiket *$noTiket* sudah ada di database.\nUntuk update data, gunakan menu *🔄 Update Laporan*.",
                            'parse_mode' => 'Markdown'
                        ]);
                        $this->showMainMenu($chatId);
                        break;
                    }

                    // Tiket baru → lanjut normal
                    $this->telegram->sendMessage([
                        'chat_id' => $chatId,
                        'text' => "✅ No Tiket *$noTiket* dicatat. Masukkan *Nama Teknisi*:",
                        'parse_mode' => 'Markdown'
                    ]);

                    $data['ticket_number'] = $noTiket;
                    Cache::put("report_data_$chatId", $data, 300);
                    Cache::put("report_state_$chatId", 'nama_teknisi', 300);
                    break;

                case 'nama_teknisi':
                    $data['technician_name'] = $text;
                    Cache::put("report_data_$chatId", $data, 300);

                    $this->telegram->sendMessage([
                        'chat_id' => $chatId,
                        'text' => "📍 Silakan kirim lokasi GPS Anda dengan menekan tombol di bawah:",
                        'reply_markup' => json_encode([
                            'keyboard' => [
                                [
                                    [
                                        'text' => '📍 Share Location',
                                        'request_location' => true
                                    ]
                                ]
                            ],
                            'resize_keyboard' => true,
                            'one_time_keyboard' => true
                        ])
                    ]);
                    Cache::put("report_state_$chatId", 'location', 300);
                    break;

                case 'location':
                    if ($location) {
                        $data['latitude'] = $location->getLatitude();
                        $data['longitude'] = $location->getLongitude();
                        Cache::put("report_data_$chatId", $data, 300);

                        // Setelah lokasi, tanyakan tipe kabel - hapus reply keyboard
                        $this->telegram->sendMessage([
                            'chat_id' => $chatId,
                            'text' => "🔌 Pilih jenis tipe kabel:",
                            'reply_markup' => json_encode([
                                'inline_keyboard' => [
                                    [
                                        ['text' => 'Network', 'callback_data' => 'Network'],
                                        ['text' => 'Access', 'callback_data' => 'Access']
                                    ]
                                ],
                                'remove_keyboard' => true
                            ])
                        ]);
                        Cache::put("report_state_$chatId", 'tipe_kabel', 300);
                    } else {
                        $this->telegram->sendMessage([
                            'chat_id' => $chatId,
                            'text' => "⚠️ Lokasi tidak valid. Silakan tekan tombol *📍 Share Location* di bawah:",
                            'parse_mode' => 'Markdown',
                            'reply_markup' => json_encode([
                                'keyboard' => [
                                    [
                                        [
                                            'text' => '📍 Share Location',
                                            'request_location' => true
                                        ]
                                    ]
                                ],
                                'resize_keyboard' => true,
                                'one_time_keyboard' => true
                            ])
                        ]);
                    }
                    break;

                case 'detail':
                    $data['work_details'] = $text;
                    Cache::put("report_data_$chatId", $data, 300);

                    $this->telegram->sendMessage([
                        'chat_id' => $chatId,
                        'text' => "📸 Silakan kirim foto dokumentasi (bisa beberapa kali).\nKlik tombol *Selesai* jika sudah cukup.",
                        'parse_mode' => 'Markdown',
                        'reply_markup' => json_encode([
                            'keyboard' => [
                                [['text' => '✅ Selesai']]
                            ],
                            'resize_keyboard' => true,
                            'one_time_keyboard' => true
                        ])
                    ]);
                    Cache::put("report_state_$chatId", 'dokumentasi', 300);
                    break;

                case 'dokumentasi':
                    // ✅ Tombol selesai ditekan
                    if ($text === '✅ Selesai') {
                        $this->telegram->sendMessage([
                            'chat_id' => $chatId,
                            'text' => "🔗 Apakah Anda ingin menambahkan relasi tiket? (opsional)\n\nMasukkan *No Tiket* yang ingin direlasikan atau tekan *Selesai*:",
                            'parse_mode' => 'Markdown',
                            'reply_markup' => json_encode([
                                'inline_keyboard' => [
                                    [['text' => '⏭ Selesai', 'callback_data' => 'relasi_selesai']]
                                ]
                            ])
                        ]);

                        Cache::put("report_state_$chatId", 'relasi', 300);
                        break;
                    }

                    // ✅ Simpan foto dokumentasi
                    if ($photo && count($photo) > 0) {
                        $this->savePhoto($chatId, $photo, $data);
                    }
                    break;

                case 'relasi':
                    $noTiketRelasi = trim($text);
                    
                    if (empty($noTiketRelasi)) {
                        $this->telegram->sendMessage([
                            'chat_id' => $chatId,
                            'text' => "❌ No Tiket tidak boleh kosong. Silakan masukkan lagi atau tekan tombol *Selesai*.",
                            'parse_mode' => 'Markdown'
                        ]);
                        break;
                    }

                    // Cek apakah tiket ada di database
                    $relatedTicket = Report::where('ticket_number', $noTiketRelasi)->first();

                    if (!$relatedTicket) {
                        $this->telegram->sendMessage([
                            'chat_id' => $chatId,
                            'text' => "❌ No Tiket *$noTiketRelasi* tidak ditemukan di database.\n\nSilakan masukkan No Tiket lain atau tekan tombol *Selesai*.",
                            'parse_mode' => 'Markdown',
                            'reply_markup' => json_encode([
                                'inline_keyboard' => [
                                    [['text' => '⏭ Selesai', 'callback_data' => 'relasi_selesai']]
                                ]
                            ])
                        ]);
                        break;
                    }

                    // Cek apakah tiket sama dengan tiket yang sedang dibuat
                    if ($noTiketRelasi === $data['ticket_number']) {
                        $this->telegram->sendMessage([
                            'chat_id' => $chatId,
                            'text' => "❌ Tidak bisa merelasikan tiket dengan dirinya sendiri.\n\nSilakan masukkan No Tiket lain atau tekan tombol *Selesai*.",
                            'parse_mode' => 'Markdown',
                            'reply_markup' => json_encode([
                                'inline_keyboard' => [
                                    [['text' => '⏭ Selesai', 'callback_data' => 'relasi_selesai']]
                                ]
                            ])
                        ]);
                        break;
                    }

                    // Cek apakah sudah ditambahkan sebelumnya
                    $selectedRelations = Cache::get("report_relations_$chatId", []);
                    if (in_array($noTiketRelasi, $selectedRelations)) {
                        $this->telegram->sendMessage([
                            'chat_id' => $chatId,
                            'text' => "⚠️ Tiket *$noTiketRelasi* sudah ditambahkan sebelumnya.\n\nSilakan masukkan No Tiket lain atau tekan tombol *Selesai*.",
                            'parse_mode' => 'Markdown',
                            'reply_markup' => json_encode([
                                'inline_keyboard' => [
                                    [['text' => '⏭ Selesai', 'callback_data' => 'relasi_selesai']]
                                ]
                            ])
                        ]);
                        break;
                    }

                    // Tambahkan ke list relasi
                    $selectedRelations[] = $noTiketRelasi;
                    Cache::put("report_relations_$chatId", $selectedRelations, 300);

                    $this->telegram->sendMessage([
                        'chat_id' => $chatId,
                        'text' => "✅ Tiket *$noTiketRelasi* berhasil ditambahkan!\n\n🔗 Apakah Anda ingin menambahkan tiket lain?",
                        'parse_mode' => 'Markdown',
                        'reply_markup' => json_encode([
                            'inline_keyboard' => [
                                [
                                    ['text' => '➕ Tambah Lagi', 'callback_data' => 'relasi_tambah_lagi'],
                                    ['text' => '✅ Selesai', 'callback_data' => 'relasi_confirm_selesai']]
                            ]
                        ])
                    ]);

                    Cache::put("report_state_$chatId", 'relasi_confirm', 300);
                    break;

                case 'update_nama_teknisi':
                    $data['technician_name'] = $text;
                    Cache::put("report_data_$chatId", $data, 300);

                    $this->telegram->sendMessage([
                        'chat_id' => $chatId,
                        'text' => "📝 Masukkan *Detail Pekerjaan* untuk di-append:",
                        'parse_mode' => 'Markdown'
                    ]);
                    Cache::put("report_state_$chatId", 'update_detail', 300);
                    break;

                case 'update_detail':
                    $data['work_details'] = $text;
                    Cache::put("report_data_$chatId", $data, 300);

                    $this->telegram->sendMessage([
                        'chat_id' => $chatId,
                        'text' => "🔧 Pilih jenis penyelesaian gangguan:",
                        'reply_markup' => json_encode([
                            'inline_keyboard' => [
                                [
                                    ['text' => 'Permanen', 'callback_data' => 'Permanen'],
                                    ['text' => 'Temporer', 'callback_data' => 'Temporer']
                                ]
                            ]
                        ])
                    ]);
                    Cache::put("report_state_$chatId", 'update_penyelesaian', 300);
                    break;

                case 'update_dokumentasi':
                    if ($text === '✅ Selesai') {
                        $this->updateExistingReport($chatId, $data);
                        break;
                    }

                    // Simpan foto dokumentasi untuk update
                    if ($photo && count($photo) > 0) {
                        $this->savePhoto($chatId, $photo, $data, true);
                    }
                    break;
            }
        }
    }

    // 🔹 Method untuk menampilkan menu utama
    private function showMainMenu($chatId)
    {
        Cache::forget("report_state_$chatId");
        Cache::forget("report_data_$chatId");
        Cache::forget("report_relations_$chatId");

        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => "🏠 *Menu Utama Bot Laporan Gangguan*\n\nSilakan pilih menu yang diinginkan:",
            'parse_mode' => 'Markdown',
            'reply_markup' => json_encode([
                'inline_keyboard' => [
                    [
                        ['text' => '📝 Buat Laporan', 'callback_data' => 'create_report']
                    ],
                    [
                        ['text' => '🔄 Update Laporan', 'callback_data' => 'update_report']
                    ],
                    [
                        ['text' => '🔍 Cari Data by Tiket', 'callback_data' => 'get_data_by_ticket']
                    ],
                    [
                        ['text' => '⏰ Data Status Temporer', 'callback_data' => 'get_temporary_reports']
                    ]
                ]
            ])
        ]);

        Cache::put("report_state_$chatId", 'main_menu', 300);
    }

    // 🔹 Method untuk reset cache dan kembali ke menu
    private function resetCacheAndShowMenu($chatId)
    {
        Cache::forget("report_state_$chatId");
        Cache::forget("report_data_$chatId");
        Cache::forget("report_relations_$chatId");

        $this->showMainMenu($chatId);
    }

    // 🔹 Method untuk mencari data berdasarkan no tiket
    private function searchReportByTicket($chatId, $noTiket)
    {
        $report = Report::where('ticket_number', $noTiket)->first();

        if (!$report) {
            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => "❌ Data dengan No Tiket *$noTiket* tidak ditemukan.",
                'parse_mode' => 'Markdown'
            ]);
        } else {
            $createdAtWITA = Carbon::parse($report->created_at)
                ->setTimezone('Asia/Makassar')
                ->format('d/m/Y H:i');

            $message = "🔍 *Data Laporan*\n";
            $message .= "━━━━━━━━━━━━━━━\n";
            $message .= "📋 *No Tiket:* `" . ($report->ticket_number ?? 'N/A') . "`\n";
            $message .= "📅 *Tanggal:* {$createdAtWITA} WITA\n";
            $message .= "👨‍🔧 *Nama Teknisi:* {$report->technician_name}\n";

            $statusDisplay = $report->repair_type === 'Permanent' ? 'Permanen' : 'Temporer';
            $message .= "🔧 *Status:* {$statusDisplay}\n";

            if ($report->cable_type) {
                $message .= "🔌 *Tipe Kabel:* {$report->cable_type}\n";
            }

            if ($report->disruption_cause) {
                $causeIndonesian = array_search($report->disruption_cause, $this->penyebabMap) ?: $report->disruption_cause;
                $message .= "⚠️ *Penyebab:* {$causeIndonesian}\n";
            }

            if ($report->latitude && $report->longitude) {
                $mapsUrl = "https://maps.google.com/?q={$report->latitude},{$report->longitude}";
                $message .= "📍 *Lokasi:* [Buka di Google Maps]($mapsUrl)\n";
                $message .= "🌐 *Koordinat:* {$report->latitude}, {$report->longitude}\n\n";
            }

            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'Markdown',
                'disable_web_page_preview' => true
            ]);

            if ($report->latitude && $report->longitude) {
                $this->telegram->sendLocation([
                    'chat_id' => $chatId,
                    'latitude' => $report->latitude,
                    'longitude' => $report->longitude
                ]);
            }

            // Kirim foto dokumentasi jika ada
            $this->sendDocumentationPhotos($chatId, $report);
        }

        $this->showMainMenu($chatId);
    }

    // 🔹 Method untuk menampilkan data dengan status temporer
    private function sendTemporaryReports($chatId)
    {
        $temporaryReports = Report::where('repair_type', 'Temporary')
            ->orderBy('created_at', 'desc')
            ->get();

        if ($temporaryReports->isEmpty()) {
            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => "❌ Tidak ada data dengan status Temporer."
            ]);
        } else {
            $message = "⏰ *Data Laporan dengan Status Temporer*\n\n";

            foreach ($temporaryReports as $index => $report) {
                $createdAtWITA = Carbon::parse($report->created_at)
                    ->setTimezone('Asia/Makassar')
                    ->format('d/m/Y H:i');

                $message .= "📋 No Tiket: `" . ($report->ticket_number ?? 'N/A') . "`\n";
                $message .= "👨‍🔧 Teknisi: " . ($report->technician_name ?? 'N/A') . "\n";
                $message .= "📅 Tanggal: " . $createdAtWITA . " WITA\n";
                $message .= "🔧 Status: Temporer\n";

                if ($report->cable_type) {
                    $message .= "🔌 Tipe Kabel: {$report->cable_type}\n";
                }

                if ($report->disruption_cause) {
                    $causeIndonesian = array_search($report->disruption_cause, $this->penyebabMap) ?: $report->disruption_cause;
                    $message .= "⚠️ Penyebab: {$causeIndonesian}\n";
                }

                if ($report->latitude && $report->longitude) {
                    $mapsUrl = "https://maps.google.com/?q={$report->latitude},{$report->longitude}";
                    $message .= "📍 *Lokasi:* [Buka di Google Maps]($mapsUrl)\n";
                }

                $message .= "\n";

                if (strlen($message) > 3500) {
                    $this->telegram->sendMessage([
                        'chat_id' => $chatId,
                        'text' => $message,
                        'parse_mode' => 'Markdown',
                        'disable_web_page_preview' => true
                    ]);
                    $message = "";
                }
            }

            if (!empty($message)) {
                $this->telegram->sendMessage([
                    'chat_id' => $chatId,
                    'text' => $message,
                    'parse_mode' => 'Markdown',
                    'disable_web_page_preview' => true
                ]);
            }
        }

        $this->showMainMenu($chatId);
    }

    // 🔹 Method untuk menyimpan foto
    private function savePhoto($chatId, $photo, &$data, $isUpdate = false)
    {
        $lastPhoto = $photo[count($photo) - 1];
        $fileId = $lastPhoto->getFileId();

        try {
            $file = $this->telegram->getFile(['file_id' => $fileId]);
            $filePath = $file->getFilePath();
            $url = "https://api.telegram.org/file/bot" . env('TELEGRAM_BOT_TOKEN') . "/" . $filePath;

            $contents = file_get_contents($url);
            $fileName = "dokumentasi/" . $data['ticket_number'] . "_" . uniqid() . ".jpg";
            Storage::disk('public')->put($fileName, $contents);

            $docs = $data['documentation'] ?? [];
            $docs[] = $fileName;
            $data['documentation'] = $docs;
            Cache::put("report_data_$chatId", $data, 300);

            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => "✅ Foto berhasil disimpan. Kirim lagi jika masih ada, atau tekan tombol *Selesai*.",
                'parse_mode' => 'Markdown'
            ]);
        } catch (\Exception $e) {
            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => "❌ Gagal menyimpan foto. Coba lagi."
            ]);
        }
    }

    // 🔹 Method untuk update laporan yang sudah ada
    private function updateExistingReport($chatId, $data)
    {
        $existingReport = Report::where('ticket_number', $data['ticket_number'])->first();

        if ($existingReport) {
            $newDetail = $existingReport->work_details
                ? $existingReport->work_details . "\n--------------------------------------------------------------------\n" . ($data['work_details'] ?? '')
                : ($data['work_details'] ?? '');

            if (is_string($existingReport->documentation)) {
                $oldDocs = json_decode($existingReport->documentation, true) ?: [];
            } elseif (is_array($existingReport->documentation)) {
                $oldDocs = $existingReport->documentation;
            } else {
                $oldDocs = [];
            }
            $newDocs = $data['documentation'] ?? [];
            $mergedDocs = array_merge($oldDocs, $newDocs);

            $existingReport->update([
                'technician_name' => $data['technician_name'] ?? $existingReport->technician_name,
                'work_details' => $newDetail,
                'repair_type' => $data['repair_type'] ?? $existingReport->repair_type,
                'documentation' => $mergedDocs
            ]);

            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => "✅ Data laporan untuk *No Tiket {$data['ticket_number']}* berhasil *diperbarui*.",
                'parse_mode' => 'Markdown'
            ]);
        }

        $this->resetCacheAndShowMenu($chatId);
    }

    // 🔹 Method untuk mengirim foto dokumentasi
    private function sendDocumentationPhotos($chatId, $report)
    {
        if ($report->documentation) {
            $dokumentasi = is_string($report->documentation)
                ? json_decode($report->documentation, true)
                : $report->documentation;

            if (is_array($dokumentasi) && !empty($dokumentasi)) {
                $this->telegram->sendMessage([
                    'chat_id' => $chatId,
                    'text' => "📸 *Foto Dokumentasi:*",
                    'parse_mode' => 'Markdown'
                ]);

                foreach ($dokumentasi as $index => $fileName) {
                    $filePath = storage_path('app/public/' . $fileName);

                    if (file_exists($filePath)) {
                        try {
                            $this->telegram->sendPhoto([
                                'chat_id' => $chatId,
                                'photo' => new \CURLFile($filePath),
                                'caption' => "📷 Dokumentasi " . ($index + 1)
                            ]);
                        } catch (\Exception $e) {
                            $this->telegram->sendMessage([
                                'chat_id' => $chatId,
                                'text' => "❌ Gagal mengirim foto dokumentasi " . ($index + 1)
                            ]);
                        }
                    }
                }
            }
        }
    }
}