<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Telegram\Bot\Api;
use App\Models\Report;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class TelegramBot extends Command
{
    protected $signature = 'bot:run';
    protected $description = 'Run Telegram bot with polling';

    // Mapping dari pilihan bot (English) ke database (Indonesian)
    private $penyelesaianMap = [
        'Permanen' => 'Permanen',
        'Temporer' => 'Temporer'
    ];

    private $penyebabMap = [
        'Vandalisme' => 'Vandalisme',
        'Gangguan Hewan' => 'Gangguan Hewan',
        'Aktivitas Pihak Ketiga' => 'Aktivitas Pihak Ketiga',
        'Gangguan Alam' => 'Gangguan Alam',
        'Masalah Listrik' => 'Masalah Listrik',
        'Kecelakaan Lalu Lintas' => 'Kecelakaan Lalu Lintas'
    ];

    // Tipe kabel tetap menggunakan English
    private $tipeKabelMap = [
        'Network' => 'Jaringan',
        'Access' => 'Akses'
    ];

    // 🆕 Mapping untuk menampilkan kembali ke bahasa Inggris
    private $tipeKabelDisplayMap = [
        'Jaringan' => 'Network',
        'Akses' => 'Access'
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

                            // Simpan ke database dengan mapping bahasa Indonesia
                            $reportData['Tipe_Kabel'] = $this->tipeKabelMap[$data];
                            Cache::put("report_data_$chatId", $reportData, 300);

                            $this->info("DEBUG - Saved Tipe_Kabel for NEW ticket: " . $this->tipeKabelMap[$data]);

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

                            // Simpan langsung dalam bahasa Indonesia
                            $reportData['Penyebab_Gangguan'] = $data;
                            Cache::put("report_data_$chatId", $reportData, 300);

                            $this->info("DEBUG - Saved Penyebab_Gangguan for NEW ticket: " . $data);

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

                            // Simpan langsung dalam bahasa Indonesia
                            $reportData['Penyelesaian_Gangguan'] = $data;
                            Cache::put("report_data_$chatId", $reportData, 300);

                            $this->info("DEBUG - Saved Penyelesaian for NEW ticket: " . $data);

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
                            $reportData['Penyelesaian_Gangguan'] = 'Permanen';
                        } elseif ($data === 'Temporer') {
                            $reportData['Penyelesaian_Gangguan'] = 'Temporer';
                        }

                        Cache::put("report_data_$chatId", $reportData, 300);
                        $this->info("DEBUG - Saved Penyelesaian for UPDATE: " . ($reportData['Penyelesaian_Gangguan'] ?? 'NULL'));

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

                    // ✅ Handle relasi tiket - tombol selesai
                    if ($state === 'relasi' && $data === 'relasi_selesai') {
                        $telegram->answerCallbackQuery([
                            'callback_query_id' => $callback->getId(),
                        ]);

                        $selectedRelations = Cache::get("report_relations_$chatId", []);

                        // 🔹 Cek apakah tiket sudah ada
                        $existingReport = Report::where('No_Tiket', $reportData['No_Tiket'])->first();

                        if ($existingReport) {
                            // Append detail lama + baru
                            $newDetail = $existingReport->Detail_Pekerjaan
                                ? $existingReport->Detail_Pekerjaan . "\n---\n" . ($reportData['Detail_Pekerjaan'] ?? '')
                                : ($reportData['Detail_Pekerjaan'] ?? '');

                            // Gabungkan dokumentasi lama + baru
                            if (is_string($existingReport->Dokumentasi)) {
                                $oldDocs = json_decode($existingReport->Dokumentasi, true) ?: [];
                            } elseif (is_array($existingReport->Dokumentasi)) {
                                $oldDocs = $existingReport->Dokumentasi;
                            } else {
                                $oldDocs = [];
                            }
                            $newDocs = $reportData['Dokumentasi'] ?? [];
                            $mergedDocs = array_merge($oldDocs, $newDocs);

                            $existingReport->update([
                                'Nama_Teknisi' => $reportData['Nama_Teknisi'] ?? $existingReport->Nama_Teknisi,
                                'Detail_Pekerjaan' => $newDetail,
                                'Penyelesaian_Gangguan' => $reportData['Penyelesaian_Gangguan'] ?? $existingReport->Penyelesaian_Gangguan,
                                'Tipe_Kabel' => $reportData['Tipe_Kabel'] ?? $existingReport->Tipe_Kabel,
                                'Penyebab_Gangguan' => $reportData['Penyebab_Gangguan'] ?? $existingReport->Penyebab_Gangguan,
                                'Dokumentasi' => json_encode($mergedDocs),
                            ]);

                            $report = $existingReport;

                            $telegram->sendMessage([
                                'chat_id' => $chatId,
                                'text' => "✅ Data laporan untuk *No Tiket {$reportData['No_Tiket']}* berhasil *diperbarui*.",
                                'parse_mode' => 'Markdown'
                            ]);
                        } else {
                            $report = Report::create([
                                'No_Tiket' => $reportData['No_Tiket'],
                                'Nama_Teknisi' => $reportData['Nama_Teknisi'] ?? null,
                                'Latitude' => $reportData['Latitude'] ?? null,
                                'Longitude' => $reportData['Longitude'] ?? null,
                                'Detail_Pekerjaan' => $reportData['Detail_Pekerjaan'] ?? null,
                                'Penyelesaian_Gangguan' => $reportData['Penyelesaian_Gangguan'] ?? null,
                                'Tipe_Kabel' => $reportData['Tipe_Kabel'] ?? null,
                                'Penyebab_Gangguan' => $reportData['Penyebab_Gangguan'] ?? null,
                                'Dokumentasi' => isset($reportData['Dokumentasi'])
                                    ? json_encode($reportData['Dokumentasi'])
                                    : json_encode([]),
                            ]);

                            $telegram->sendMessage([
                                'chat_id' => $chatId,
                                'text' => "✅ Laporan baru berhasil disimpan dengan No Tiket {$report->No_Tiket}."
                            ]);
                        }

                        // 🔹 Simpan semua relasi yang dipilih
                        if (!empty($selectedRelations)) {
                            foreach ($selectedRelations as $tiket) {
                                if (Report::where('No_Tiket', $tiket)->exists()) {
                                    $report->relatedReports()->syncWithoutDetaching($tiket);

                                    // Relasi balik
                                    $relatedReport = Report::where('No_Tiket', $tiket)->first();
                                    $relatedReport->relatedReports()->syncWithoutDetaching($report->No_Tiket);
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
                        $existingReport = Report::where('No_Tiket', $reportData['No_Tiket'])->first();

                        if ($existingReport) {
                            $newDetail = $existingReport->Detail_Pekerjaan
                                ? $existingReport->Detail_Pekerjaan . "\n---\n" . ($reportData['Detail_Pekerjaan'] ?? '')
                                : ($reportData['Detail_Pekerjaan'] ?? '');

                            if (is_string($existingReport->Dokumentasi)) {
                                $oldDocs = json_decode($existingReport->Dokumentasi, true) ?: [];
                            } elseif (is_array($existingReport->Dokumentasi)) {
                                $oldDocs = $existingReport->Dokumentasi;
                            } else {
                                $oldDocs = [];
                            }
                            $newDocs = $reportData['Dokumentasi'] ?? [];
                            $mergedDocs = array_merge($oldDocs, $newDocs);

                            $existingReport->update([
                                'Nama_Teknisi' => $reportData['Nama_Teknisi'] ?? $existingReport->Nama_Teknisi,
                                'Detail_Pekerjaan' => $newDetail,
                                'Penyelesaian_Gangguan' => $reportData['Penyelesaian_Gangguan'] ?? $existingReport->Penyelesaian_Gangguan,
                                'Tipe_Kabel' => $reportData['Tipe_Kabel'] ?? $existingReport->Tipe_Kabel,
                                'Penyebab_Gangguan' => $reportData['Penyebab_Gangguan'] ?? $existingReport->Penyebab_Gangguan,
                                'Dokumentasi' => json_encode($mergedDocs),
                            ]);

                            $report = $existingReport;

                            $telegram->sendMessage([
                                'chat_id' => $chatId,
                                'text' => "✅ Data laporan untuk *No Tiket {$reportData['No_Tiket']}* berhasil *diperbarui*.",
                                'parse_mode' => 'Markdown'
                            ]);
                        } else {
                            $report = Report::create([
                                'No_Tiket' => $reportData['No_Tiket'],
                                'Nama_Teknisi' => $reportData['Nama_Teknisi'] ?? null,
                                'Latitude' => $reportData['Latitude'] ?? null,
                                'Longitude' => $reportData['Longitude'] ?? null,
                                'Detail_Pekerjaan' => $reportData['Detail_Pekerjaan'] ?? null,
                                'Penyelesaian_Gangguan' => $reportData['Penyelesaian_Gangguan'] ?? null,
                                'Tipe_Kabel' => $reportData['Tipe_Kabel'] ?? null,
                                'Penyebab_Gangguan' => $reportData['Penyebab_Gangguan'] ?? null,
                                'Dokumentasi' => isset($reportData['Dokumentasi'])
                                    ? json_encode($reportData['Dokumentasi'])
                                    : json_encode([]),
                            ]);

                            $telegram->sendMessage([
                                'chat_id' => $chatId,
                                'text' => "✅ Laporan baru berhasil disimpan dengan No Tiket {$report->No_Tiket}."
                            ]);
                        }

                        // Simpan relasi
                        if (!empty($selectedRelations)) {
                            foreach ($selectedRelations as $tiket) {
                                if (Report::where('No_Tiket', $tiket)->exists()) {
                                    $report->relatedReports()->syncWithoutDetaching($tiket);

                                    $relatedReport = Report::where('No_Tiket', $tiket)->first();
                                    $relatedReport->relatedReports()->syncWithoutDetaching($report->No_Tiket);
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
                            $existingReport = Report::where('No_Tiket', $noTiket)->first();
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
                            $data['No_Tiket'] = $noTiket;
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
                            $existing = Report::where('No_Tiket', $noTiket)->first();

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

                            $data['No_Tiket'] = $noTiket;
                            Cache::put("report_data_$chatId", $data, 300);
                            Cache::put("report_state_$chatId", 'nama_teknisi', 300);
                            break;

                        case 'nama_teknisi':
                            $data['Nama_Teknisi'] = $text;
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
                                $data['Latitude'] = $location->getLatitude();
                                $data['Longitude'] = $location->getLongitude();
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
                            $data['Detail_Pekerjaan'] = $text;
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
                            $relatedTicket = Report::where('No_Tiket', $noTiketRelasi)->first();
                            
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
                            if ($noTiketRelasi === $data['No_Tiket']) {
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
                            $data['Nama_Teknisi'] = $text;
                            Cache::put("report_data_$chatId", $data, 300);

                            $telegram->sendMessage([
                                'chat_id' => $chatId,
                                'text' => "📝 Masukkan *Detail Pekerjaan* untuk di-append:",
                                'parse_mode' => 'Markdown'
                            ]);
                            Cache::put("report_state_$chatId", 'update_detail', 300);
                            break;

                        case 'update_detail':
                            $data['Detail_Pekerjaan'] = $text;
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
        $report = Report::where('No_Tiket', $noTiket)->first();

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
            $message .= "📋 *No Tiket:* `" . ($report->No_Tiket ?? 'N/A') . "`\n";
            $message .= "📅 *Tanggal:* {$createdAtWITA} WITA\n";
            $message .= "👨‍🔧 *Nama Teknisi:* {$report->Nama_Teknisi}\n";
            $message .= "🔧 *Status:* {$report->Penyelesaian_Gangguan}\n";

            // 🆕 Tampilkan tipe kabel dalam bahasa Inggris
            if ($report->Tipe_Kabel) {
                $displayTipeKabel = $this->tipeKabelDisplayMap[$report->Tipe_Kabel] ?? $report->Tipe_Kabel;
                $message .= "🔌 *Tipe Kabel:* {$displayTipeKabel}\n";
            }

            // Tampilkan penyebab gangguan jika ada
            if ($report->Penyebab_Gangguan) {
                $message .= "⚠️ *Penyebab:* {$report->Penyebab_Gangguan}\n";
            }

            // Tambahkan lokasi dengan link ke maps
            if ($report->Latitude && $report->Longitude) {
                $mapsUrl = "https://maps.google.com/?q={$report->Latitude},{$report->Longitude}";
                $message .= "📍 *Lokasi:* [Buka di Google Maps]($mapsUrl)\n";
                $message .= "🌐 *Koordinat:* {$report->Latitude}, {$report->Longitude}\n\n";
            }

            $telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'Markdown',
                'disable_web_page_preview' => true
            ]);

            $telegram->sendLocation([
                'chat_id'   => $chatId,
                'latitude'  => $report->Latitude,
                'longitude' => $report->Longitude
            ]);
        }

        // Kembali ke menu utama
        $this->showMainMenu($telegram, $chatId);
    }

    // 🔹 Method untuk menampilkan data dengan status temporer
    private function sendTemporaryReports($telegram, $chatId)
    {
        $temporaryReports = Report::where('Penyelesaian_Gangguan', 'Temporer')
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

                $message .= "📋 No Tiket: `" . ($report->No_Tiket ?? 'N/A') . "`\n";
                $message .= "👨‍🔧 Teknisi: " . ($report->Nama_Teknisi ?? 'N/A') . "\n";
                $message .= "📅 Tanggal: " . $createdAtWITA . " WITA\n";
                $message .= "🔧 Status: {$report->Penyelesaian_Gangguan}\n";

                // 🆕 Tampilkan tipe kabel dalam bahasa Inggris
                if ($report->Tipe_Kabel) {
                    $displayTipeKabel = $this->tipeKabelDisplayMap[$report->Tipe_Kabel] ?? $report->Tipe_Kabel;
                    $message .= "🔌 Tipe Kabel: {$displayTipeKabel}\n";
                }

                // Tampilkan penyebab gangguan jika ada
                if ($report->Penyebab_Gangguan) {
                    $message .= "⚠️ Penyebab: {$report->Penyebab_Gangguan}\n";
                }

                if ($report->Latitude && $report->Longitude) {
                    $mapsUrl = "https://maps.google.com/?q={$report->Latitude},{$report->Longitude}";
                    $message .= "📍 *Lokasi:* [Buka di Google Maps]($mapsUrl)\n";
                    $message .= "🌐 *Koordinat:* {$report->Latitude}, {$report->Longitude}\n\n";
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
            $fileName = "dokumentasi/" . $data['No_Tiket'] . "_" . uniqid() . ".jpg";
            Storage::disk('public')->put($fileName, $contents);

            $docs = $data['Dokumentasi'] ?? [];
            $docs[] = $fileName;
            $data['Dokumentasi'] = $docs;
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
        $existingReport = Report::where('No_Tiket', $data['No_Tiket'])->first();

        if ($existingReport) {
            // Append detail pekerjaan
            $newDetail = $existingReport->Detail_Pekerjaan
                ? $existingReport->Detail_Pekerjaan . "\n--------------------------------------------------------------------\n" . ($data['Detail_Pekerjaan'] ?? '')
                : ($data['Detail_Pekerjaan'] ?? '');

            // Gabungkan dokumentasi lama + baru
            if (is_string($existingReport->Dokumentasi)) {
                $oldDocs = json_decode($existingReport->Dokumentasi, true) ?: [];
            } elseif (is_array($existingReport->Dokumentasi)) {
                $oldDocs = $existingReport->Dokumentasi;
            } else {
                $oldDocs = [];
            }
            $newDocs = $data['Dokumentasi'] ?? [];
            $mergedDocs = array_merge($oldDocs, $newDocs);

            // DEBUG: Log sebelum update
            $this->info("DEBUG - Before UPDATE: Penyelesaian = " . ($data['Penyelesaian_Gangguan'] ?? 'NULL'));

            $existingReport->update([
                'Nama_Teknisi' => $data['Nama_Teknisi'] ?? $existingReport->Nama_Teknisi,
                'Detail_Pekerjaan' => $newDetail,
                'Penyelesaian_Gangguan' => $data['Penyelesaian_Gangguan'] ?? $existingReport->Penyelesaian_Gangguan,
                'Dokumentasi' => json_encode($mergedDocs),
            ]);

            $telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => "✅ Data laporan untuk *No Tiket {$data['No_Tiket']}* berhasil *diperbarui*.",
                'parse_mode' => 'Markdown'
            ]);
        }

        // Reset cache dan kembali ke menu
        $this->resetCacheAndShowMenu($telegram, $chatId);
    }

    // 🔹 Method untuk mengirim foto dokumentasi
    private function sendDocumentationPhotos($telegram, $chatId, $report)
    {
        if ($report->Dokumentasi) {
            $dokumentasi = is_string($report->Dokumentasi)
                ? json_decode($report->Dokumentasi, true)
                : $report->Dokumentasi;

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