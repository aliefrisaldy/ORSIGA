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

    // Tipe kabel menggunakan English
    private $tipeKabelMap = [
        'Network' => 'Network',
        'Access' => 'Access'
    ];

    public function handle()
    {
        $telegram = new Api(env('TELEGRAM_BOT_TOKEN'));

        $offset = 0;
        $this->info("🤖 Bot is running with polling...");

        while (true) {
            $updates = $telegram->getUpdates([
                'offset' => $offset + 1,
                'timeout' => 60,
            ]);

            foreach ($updates as $update) {
                $offset = $update->getUpdateId();

                $message = $update->getMessage();
                $callback = $update->getCallbackQuery();

                // ⬇️ Handle callback queries
                if ($callback) {
                    $chatId = $callback->getMessage()->getChat()->getId();
                    $data = $callback->getData();
                    $state = Cache::get("report_state_$chatId");
                    $reportData = Cache::get("report_data_$chatId", []);

                    $this->info("DEBUG - Callback: state=$state, data=$data");

                    // 🔹 Handle menu utama
                    if ($state === 'main_menu') {
                        if ($data === 'create_report') {
                            $telegram->sendMessage([
                                'chat_id' => $chatId,
                                'text' => "📝 Mari isi laporan gangguan.\nMasukkan *No Tiket*:",
                                'parse_mode' => 'Markdown'
                            ]);
                            Cache::put("report_state_$chatId", 'no_tiket', 300);
                        } elseif ($data === 'update_report') {
                            $telegram->sendMessage([
                                'chat_id' => $chatId,
                                'text' => "🔄 Masukkan *No Tiket* yang ingin diupdate:",
                                'parse_mode' => 'Markdown'
                            ]);
                            Cache::put("report_state_$chatId", 'update_ticket', 300);
                        } elseif ($data === 'get_data_by_ticket') {
                            $telegram->sendMessage([
                                'chat_id' => $chatId,
                                'text' => "🔍 Masukkan *No Tiket* yang ingin dicari:",
                                'parse_mode' => 'Markdown'
                            ]);
                            Cache::put("report_state_$chatId", 'search_ticket', 300);
                        } elseif ($data === 'get_temporary_reports') {
                            $this->sendTemporaryReports($telegram, $chatId);
                        }
                        continue;
                    }

                    // 🆕 Handle tipe kabel untuk tiket BARU
                    if ($state === 'tipe_kabel') {
                        if ($data === 'Network' || $data === 'Access') {
                            $telegram->answerCallbackQuery([
                                'callback_query_id' => $callback->getId(),
                            ]);

                            // Simpan langsung (sudah English)
                            $reportData['cable_type'] = $data;
                            Cache::put("report_data_$chatId", $reportData, 300);

                            $this->info("DEBUG - Saved cable_type for NEW ticket: " . $data);

                            // Setelah tipe kabel, tanyakan penyebab gangguan
                            $telegram->sendMessage([
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
                        continue;
                    }

                    // 🆕 Handle penyebab gangguan untuk tiket BARU
                    if ($state === 'penyebab_gangguan') {
                        $validCauses = array_keys($this->penyebabMap);

                        if (in_array($data, $validCauses)) {
                            $telegram->answerCallbackQuery([
                                'callback_query_id' => $callback->getId(),
                            ]);

                            // Simpan dengan mapping ke English
                            $reportData['disruption_cause'] = $this->penyebabMap[$data];
                            Cache::put("report_data_$chatId", $reportData, 300);

                            $this->info("DEBUG - Saved disruption_cause for NEW ticket: " . $this->penyebabMap[$data]);

                            // Setelah penyebab gangguan, tanyakan penyelesaian gangguan
                            $telegram->sendMessage([
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
                        continue;
                    }

                    // 🔹 Handle penyelesaian untuk tiket BARU
                    if ($state === 'penyelesaian') {
                        if ($data === 'Permanen' || $data === 'Temporer') {
                            $telegram->answerCallbackQuery([
                                'callback_query_id' => $callback->getId(),
                            ]);

                            // Simpan dengan mapping ke English
                            $reportData['repair_type'] = $this->penyelesaianMap[$data];
                            Cache::put("report_data_$chatId", $reportData, 300);

                            $this->info("DEBUG - Saved repair_type for NEW ticket: " . $this->penyelesaianMap[$data]);

                            // Setelah penyelesaian gangguan, tanyakan detail pekerjaan
                            $telegram->sendMessage([
                                'chat_id' => $chatId,
                                'text' => "📝 Masukkan *Detail Pekerjaan*:",
                                'parse_mode' => 'Markdown'
                            ]);

                            Cache::put("report_state_$chatId", 'detail', 300);
                        }
                        continue;
                    }

                    // 🔹 Handle penyelesaian untuk UPDATE tiket
                    if ($state === 'update_penyelesaian') {
                        $telegram->answerCallbackQuery([
                            'callback_query_id' => $callback->getId(),
                        ]);

                        if ($data === 'Permanen') {
                            $reportData['repair_type'] = 'Permanent';
                        } elseif ($data === 'Temporer') {
                            $reportData['repair_type'] = 'Temporary';
                        }

                        Cache::put("report_data_$chatId", $reportData, 300);
                        $this->info("DEBUG - Saved repair_type for UPDATE: " . ($reportData['repair_type'] ?? 'NULL'));

                        $telegram->sendMessage([
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
                        continue;
                    }

                    if ($state === 'relasi' && $data === 'relasi_selesai') {
                        $telegram->answerCallbackQuery([
                            'callback_query_id' => $callback->getId(),
                        ]);

                        $selectedRelations = Cache::get("report_relations_$chatId", []);

                        // Cek apakah tiket sudah ada
                        $existingReport = Report::where('ticket_number', $reportData['ticket_number'])->first();

                        if ($existingReport) {
                            // ... update logic ...
                            $report = $existingReport;

                            $telegram->sendMessage([
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

                            $telegram->sendMessage([
                                'chat_id' => $chatId,
                                'text' => "✅ Laporan baru berhasil disimpan dengan No Tiket {$report->ticket_number}."
                            ]);
                        }

                        // ✅ PERBAIKAN: Convert ticket_number ke ID
                        if (!empty($selectedRelations)) {
                            foreach ($selectedRelations as $ticketNumber) {
                                $relatedReport = Report::where('ticket_number', $ticketNumber)->first();

                                if ($relatedReport) {
                                    // Gunakan ID, bukan ticket_number
                                    $report->relatedReports()->syncWithoutDetaching($relatedReport->id_repair_reports);

                                    // Relasi balik juga gunakan ID
                                    $relatedReport->relatedReports()->syncWithoutDetaching($report->id_repair_reports);
                                }
                            }
                        }

                        // Reset cache dan kembali ke menu utama
                        $this->resetCacheAndShowMenu($telegram, $chatId);
                        continue;
                    }

                    // ✅ Handle relasi - tombol tambah lagi
                    if ($state === 'relasi_confirm' && $data === 'relasi_tambah_lagi') {
                        $telegram->answerCallbackQuery([
                            'callback_query_id' => $callback->getId(),
                        ]);

                        $telegram->sendMessage([
                            'chat_id' => $chatId,
                            'text' => "🔗 Masukkan *No Tiket* yang ingin direlasikan:",
                            'parse_mode' => 'Markdown'
                        ]);

                        Cache::put("report_state_$chatId", 'relasi', 300);
                        continue;
                    }

                    // ✅ Handle relasi confirm - tombol selesai
                    if ($state === 'relasi_confirm' && $data === 'relasi_confirm_selesai') {
                        $telegram->answerCallbackQuery([
                            'callback_query_id' => $callback->getId(),
                        ]);

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

                            $telegram->sendMessage([
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

                            $telegram->sendMessage([
                                'chat_id' => $chatId,
                                'text' => "✅ Laporan baru berhasil disimpan dengan No Tiket {$report->ticket_number}."
                            ]);
                        }

                        // Simpan relasi
// Simpan relasi
                        if (!empty($selectedRelations)) {
                            foreach ($selectedRelations as $ticketNumber) {
                                $relatedReport = Report::where('ticket_number', $ticketNumber)->first();

                                if ($relatedReport) {
                                    // Gunakan ID, bukan ticket_number
                                    $report->relatedReports()->syncWithoutDetaching($relatedReport->id_repair_reports);

                                    // Relasi balik juga gunakan ID
                                    $relatedReport->relatedReports()->syncWithoutDetaching($report->id_repair_reports);
                                }
                            }
                        }
                        $this->resetCacheAndShowMenu($telegram, $chatId);
                        continue;
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

                    $this->info("DEBUG - Message: state=$state, text=" . substr($text ?? '', 0, 20));

                    // ✅ Handle commands
                    if ($text === '/cancel') {
                        $this->resetCacheAndShowMenu($telegram, $chatId);
                        continue;
                    }

                    if ($text === '/start' || $text === '/menu') {
                        $this->showMainMenu($telegram, $chatId);
                        continue;
                    }

                    // 🔹 Handle states
                    switch ($state) {
                        case 'start':
                            $this->showMainMenu($telegram, $chatId);
                            break;

                        case 'search_ticket':
                            $noTiket = trim($text);
                            if (empty($noTiket)) {
                                $telegram->sendMessage([
                                    'chat_id' => $chatId,
                                    'text' => "❌ No Tiket tidak boleh kosong. Silakan masukkan lagi."
                                ]);
                                break;
                            }

                            $this->searchReportByTicket($telegram, $chatId, $noTiket);
                            break;

                        case 'update_ticket':
                            $noTiket = trim($text);
                            if (empty($noTiket)) {
                                $telegram->sendMessage([
                                    'chat_id' => $chatId,
                                    'text' => "❌ No Tiket tidak boleh kosong. Silakan masukkan lagi."
                                ]);
                                break;
                            }

                            // Cek apakah tiket ada di database
                            $existingReport = Report::where('ticket_number', $noTiket)->first();
                            if (!$existingReport) {
                                $telegram->sendMessage([
                                    'chat_id' => $chatId,
                                    'text' => "❌ No Tiket *$noTiket* tidak ditemukan di database.\nSilakan periksa kembali atau 📝 *Buat Laporan baru*.",
                                    'parse_mode' => 'Markdown'
                                ]);
                                $this->showMainMenu($telegram, $chatId);
                                break;
                            }

                            // Simpan tiket untuk update
                            $data['ticket_number'] = $noTiket;
                            Cache::put("report_data_$chatId", $data, 300);

                            $telegram->sendMessage([
                                'chat_id' => $chatId,
                                'text' => "✅ Tiket *$noTiket* ditemukan.\nMasukkan *Nama Teknisi* untuk update:",
                                'parse_mode' => 'Markdown'
                            ]);
                            Cache::put("report_state_$chatId", 'update_nama_teknisi', 300);
                            break;

                        case 'no_tiket':
                            $noTiket = trim($text);

                            if (empty($noTiket)) {
                                $telegram->sendMessage([
                                    'chat_id' => $chatId,
                                    'text' => "❌ No Tiket tidak boleh kosong. Silakan masukkan lagi."
                                ]);
                                break;
                            }

                            // Cek apakah tiket sudah ada di database
                            $existing = Report::where('ticket_number', $noTiket)->first();

                            if ($existing) {
                                $telegram->sendMessage([
                                    'chat_id' => $chatId,
                                    'text' => "⚠️ Tiket *$noTiket* sudah ada di database.\nUntuk update data, gunakan menu *🔄 Update Laporan*.",
                                    'parse_mode' => 'Markdown'
                                ]);
                                $this->showMainMenu($telegram, $chatId);
                                break;
                            }

                            // Tiket baru → lanjut normal
                            $telegram->sendMessage([
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

                            $telegram->sendMessage([
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
                                $telegram->sendMessage([
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
                                $telegram->sendMessage([
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

                            $telegram->sendMessage([
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
                                $telegram->sendMessage([
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
                                $this->savePhoto($telegram, $chatId, $photo, $data);
                            }
                            break;

                        case 'relasi':
                            $noTiketRelasi = trim($text);

                            if (empty($noTiketRelasi)) {
                                $telegram->sendMessage([
                                    'chat_id' => $chatId,
                                    'text' => "❌ No Tiket tidak boleh kosong. Silakan masukkan lagi atau tekan tombol *Selesai*.",
                                    'parse_mode' => 'Markdown'
                                ]);
                                break;
                            }

                            // Cek apakah tiket ada di database
                            $relatedTicket = Report::where('ticket_number', $noTiketRelasi)->first();

                            if (!$relatedTicket) {
                                $telegram->sendMessage([
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
                                $telegram->sendMessage([
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
                                $telegram->sendMessage([
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

                            $telegram->sendMessage([
                                'chat_id' => $chatId,
                                'text' => "✅ Tiket *$noTiketRelasi* berhasil ditambahkan!\n\n🔗 Apakah Anda ingin menambahkan tiket lain?",
                                'parse_mode' => 'Markdown',
                                'reply_markup' => json_encode([
                                    'inline_keyboard' => [
                                        [
                                            ['text' => '➕ Tambah Lagi', 'callback_data' => 'relasi_tambah_lagi'],
                                            ['text' => '✅ Selesai', 'callback_data' => 'relasi_confirm_selesai']
                                        ]
                                    ]
                                ])
                            ]);

                            Cache::put("report_state_$chatId", 'relasi_confirm', 300);
                            break;

                        case 'update_nama_teknisi':
                            $data['technician_name'] = $text;
                            Cache::put("report_data_$chatId", $data, 300);

                            $telegram->sendMessage([
                                'chat_id' => $chatId,
                                'text' => "📝 Masukkan *Detail Pekerjaan* untuk di-append:",
                                'parse_mode' => 'Markdown'
                            ]);
                            Cache::put("report_state_$chatId", 'update_detail', 300);
                            break;

                        case 'update_detail':
                            $data['work_details'] = $text;
                            Cache::put("report_data_$chatId", $data, 300);

                            $telegram->sendMessage([
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
                                $this->updateExistingReport($telegram, $chatId, $data);
                                break;
                            }

                            // Simpan foto dokumentasi untuk update
                            if ($photo && count($photo) > 0) {
                                $this->savePhoto($telegram, $chatId, $photo, $data, true);
                            }
                            break;
                    }
                }
            }
            sleep(1);
        }
    }

    // 🔹 Method untuk menampilkan menu utama
    private function showMainMenu($telegram, $chatId)
    {
        Cache::forget("report_state_$chatId");
        Cache::forget("report_data_$chatId");
        Cache::forget("report_relations_$chatId");

        $telegram->sendMessage([
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
    private function resetCacheAndShowMenu($telegram, $chatId)
    {
        Cache::forget("report_state_$chatId");
        Cache::forget("report_data_$chatId");
        Cache::forget("report_relations_$chatId");

        $this->showMainMenu($telegram, $chatId);
    }

    // 🔹 Method untuk mencari data berdasarkan no tiket
    private function searchReportByTicket($telegram, $chatId, $noTiket)
    {
        $report = Report::where('ticket_number', $noTiket)->first();

        if (!$report) {
            $telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => "❌ Data dengan No Tiket *$noTiket* tidak ditemukan.",
                'parse_mode' => 'Markdown'
            ]);
        } else {
            // Format tanggal ke WITA (Asia/Makassar)
            $createdAtWITA = \Carbon\Carbon::parse($report->created_at)
                ->setTimezone('Asia/Makassar')
                ->format('d/m/Y H:i');

            $message = "🔍 *Data Laporan*\n";
            $message .= "━━━━━━━━━━━━━━━\n";
            $message .= "📋 *No Tiket:* `" . ($report->ticket_number ?? 'N/A') . "`\n";
            $message .= "📅 *Tanggal:* {$createdAtWITA} WITA\n";
            $message .= "👨‍🔧 *Nama Teknisi:* {$report->technician_name}\n";

            // Map repair_type ke bahasa Indonesia untuk tampilan
            $statusDisplay = $report->repair_type === 'Permanent' ? 'Permanen' : 'Temporer';
            $message .= "🔧 *Status:* {$statusDisplay}\n";

            // Tampilkan tipe kabel
            if ($report->cable_type) {
                $message .= "🔌 *Tipe Kabel:* {$report->cable_type}\n";
            }

            // Tampilkan penyebab gangguan jika ada (map ke bahasa Indonesia)
            if ($report->disruption_cause) {
                $causeIndonesian = array_search($report->disruption_cause, $this->penyebabMap) ?: $report->disruption_cause;
                $message .= "⚠️ *Penyebab:* {$causeIndonesian}\n";
            }

            // Tambahkan lokasi dengan link ke maps
            if ($report->latitude && $report->longitude) {
                $mapsUrl = "https://maps.google.com/?q={$report->latitude},{$report->longitude}";
                $message .= "📍 *Lokasi:* [Buka di Google Maps]($mapsUrl)\n";
                $message .= "🌐 *Koordinat:* {$report->latitude}, {$report->longitude}\n\n";
            }

            $telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'Markdown',
                'disable_web_page_preview' => true
            ]);

            $telegram->sendLocation([
                'chat_id' => $chatId,
                'latitude' => $report->latitude,
                'longitude' => $report->longitude
            ]);
        }

        // Kembali ke menu utama
        $this->showMainMenu($telegram, $chatId);
    }

    // 🔹 Method untuk menampilkan data dengan status temporer
    private function sendTemporaryReports($telegram, $chatId)
    {
        $temporaryReports = Report::where('repair_type', 'Temporary')
            ->orderBy('created_at', 'desc')
            ->get();

        if ($temporaryReports->isEmpty()) {
            $telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => "❌ Tidak ada data dengan status Temporer."
            ]);
        } else {
            $message = "⏰ *Data Laporan dengan Status Temporer*\n\n";

            foreach ($temporaryReports as $index => $report) {
                // Format tanggal ke WITA (Asia/Makassar)
                $createdAtWITA = Carbon::parse($report->created_at)
                    ->setTimezone('Asia/Makassar')
                    ->format('d/m/Y H:i');

                $message .= "📋 No Tiket: `" . ($report->ticket_number ?? 'N/A') . "`\n";
                $message .= "👨‍🔧 Teknisi: " . ($report->technician_name ?? 'N/A') . "\n";
                $message .= "📅 Tanggal: " . $createdAtWITA . " WITA\n";
                $message .= "🔧 Status: Temporer\n";

                // Tampilkan tipe kabel
                if ($report->cable_type) {
                    $message .= "🔌 Tipe Kabel: {$report->cable_type}\n";
                }

                // Tampilkan penyebab gangguan jika ada (map ke bahasa Indonesia)
                if ($report->disruption_cause) {
                    $causeIndonesian = array_search($report->disruption_cause, $this->penyebabMap) ?: $report->disruption_cause;
                    $message .= "⚠️ Penyebab: {$causeIndonesian}\n";
                }

                if ($report->latitude && $report->longitude) {
                    $mapsUrl = "https://maps.google.com/?q={$report->latitude},{$report->longitude}";
                    $message .= "📍 *Lokasi:* [Buka di Google Maps]($mapsUrl)\n";
                    $message .= "🌐 *Koordinat:* {$report->latitude}, {$report->longitude}\n\n";
                }

                $message .= "\n";

                // Jika pesan terlalu panjang, kirim dan reset
                if (strlen($message) > 3500) {
                    $telegram->sendMessage([
                        'chat_id' => $chatId,
                        'text' => $message,
                        'parse_mode' => 'Markdown',
                        'disable_web_page_preview' => true
                    ]);
                    $message = "";
                }
            }

            // Kirim sisa pesan jika ada
            if (!empty($message)) {
                $telegram->sendMessage([
                    'chat_id' => $chatId,
                    'text' => $message,
                    'parse_mode' => 'Markdown',
                    'disable_web_page_preview' => true
                ]);
            }
        }

        // Kembali ke menu utama
        $this->showMainMenu($telegram, $chatId);
    }

    // 🔹 Method untuk menyimpan foto
    private function savePhoto($telegram, $chatId, $photo, &$data, $isUpdate = false)
    {
        $lastPhoto = $photo[count($photo) - 1];
        $fileId = $lastPhoto->getFileId();

        try {
            $file = $telegram->getFile(['file_id' => $fileId]);
            $filePath = $file->getFilePath();
            $url = "https://api.telegram.org/file/bot" . env('TELEGRAM_BOT_TOKEN') . "/" . $filePath;

            $contents = file_get_contents($url);
            $fileName = "dokumentasi/" . $data['ticket_number'] . "_" . uniqid() . ".jpg";
            Storage::disk('public')->put($fileName, $contents);

            $docs = $data['documentation'] ?? [];
            $docs[] = $fileName;
            $data['documentation'] = $docs;
            Cache::put("report_data_$chatId", $data, 300);

            $telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => "✅ Foto berhasil disimpan. Kirim lagi jika masih ada, atau tekan tombol *Selesai*.",
                'parse_mode' => 'Markdown'
            ]);
        } catch (\Exception $e) {
            $telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => "❌ Gagal menyimpan foto. Coba lagi."
            ]);
        }
    }

    // 🔹 Method untuk update laporan yang sudah ada
    private function updateExistingReport($telegram, $chatId, $data)
    {
        $existingReport = Report::where('ticket_number', $data['ticket_number'])->first();

        if ($existingReport) {
            // Append detail pekerjaan
            $newDetail = $existingReport->work_details
                ? $existingReport->work_details . "\n--------------------------------------------------------------------\n" . ($data['work_details'] ?? '')
                : ($data['work_details'] ?? '');

            // Gabungkan dokumentasi lama + baru
            if (is_string($existingReport->documentation)) {
                $oldDocs = json_decode($existingReport->documentation, true) ?: [];
            } elseif (is_array($existingReport->documentation)) {
                $oldDocs = $existingReport->documentation;
            } else {
                $oldDocs = [];
            }
            $newDocs = $data['documentation'] ?? [];
            $mergedDocs = array_merge($oldDocs, $newDocs);

            // DEBUG: Log sebelum update
            $this->info("DEBUG - Before UPDATE: repair_type = " . ($data['repair_type'] ?? 'NULL'));

            $existingReport->update([
                'technician_name' => $data['technician_name'] ?? $existingReport->technician_name,
                'work_details' => $newDetail,
                'repair_type' => $data['repair_type'] ?? $existingReport->repair_type,
                'documentation' => $mergedDocs
            ]);

            $telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => "✅ Data laporan untuk *No Tiket {$data['ticket_number']}* berhasil *diperbarui*.",
                'parse_mode' => 'Markdown'
            ]);
        }

        // Reset cache dan kembali ke menu
        $this->resetCacheAndShowMenu($telegram, $chatId);
    }

    // 🔹 Method untuk mengirim foto dokumentasi
    private function sendDocumentationPhotos($telegram, $chatId, $report)
    {
        if ($report->documentation) {
            $dokumentasi = is_string($report->documentation)
                ? json_decode($report->documentation, true)
                : $report->documentation;

            if (is_array($dokumentasi) && !empty($dokumentasi)) {
                $telegram->sendMessage([
                    'chat_id' => $chatId,
                    'text' => "📸 *Foto Dokumentasi:*",
                    'parse_mode' => 'Markdown'
                ]);

                foreach ($dokumentasi as $index => $fileName) {
                    $filePath = storage_path('app/public/' . $fileName);

                    if (file_exists($filePath)) {
                        try {
                            $telegram->sendPhoto([
                                'chat_id' => $chatId,
                                'photo' => new \CURLFile($filePath),
                                'caption' => "📷 Dokumentasi " . ($index + 1)
                            ]);
                        } catch (\Exception $e) {
                            $telegram->sendMessage([
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