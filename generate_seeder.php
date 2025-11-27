<?php

/**
 * CSV to Laravel Seeder Generator
 * 
 * Script untuk menggenerate Laravel Seeder dari file CSV
 * 
 * Cara Pakai:
 * 1. Simpan script ini sebagai generate_seeder.php di root project Laravel
 * 2. Letakkan file CSV di folder yang sama atau tentukan path-nya
 * 3. Jalankan: php generate_seeder.php
 * 4. File seeder akan dibuat di database/seeders/
 * 
 * Konfigurasi dapat disesuaikan di bagian CONFIG
 */

// ==================== CONFIG ====================
$config = [
    // Path file CSV (relatif terhadap script ini)
    'csv_file' => 'complete_koordinatsite.csv',
    
    // Nama seeder class yang akan dibuat
    'seeder_class' => 'SitesTableSeeder',
    
    // Nama table database
    'table_name' => 'sites',
    
    // Mapping kolom CSV ke kolom database
    // Format: 'kolom_database' => 'index_csv' atau ['index' => 0, 'type' => 'string|numeric|null']
    'column_mapping' => [
        'site_id' => ['index' => 0, 'type' => 'string'],
        'site_name' => ['index' => 1, 'type' => 'string'],
        'description' => ['index' => 2, 'type' => 'string'],
        'latitude' => ['index' => 3, 'type' => 'numeric'],
        'longitude' => ['index' => 4, 'type' => 'numeric'],
    ],
    
    // Jumlah data per batch insert (untuk performa)
    'batch_size' => 500,
    
    // Skip baris dengan nilai kosong di kolom tertentu
    'required_columns' => ['site_id', 'latitude', 'longitude'],
    
    // Nilai yang dianggap sebagai NULL
    'null_values' => ['', '#N/A', 'NULL', 'null', 'N/A'],
    
    // Validasi range untuk koordinat
    'coordinate_validation' => [
        'latitude' => ['min' => -90, 'max' => 90],
        'longitude' => ['min' => -180, 'max' => 180],
    ],
    
    // Truncate table sebelum insert?
    'truncate_table' => true,
    
    // Tambahkan timestamps?
    'add_timestamps' => true,
];
// ==================== END CONFIG ====================

class SeederGenerator
{
    private $config;
    private $errors = [];
    private $warnings = [];
    
    public function __construct($config)
    {
        $this->config = $config;
    }
    
    public function generate()
    {
        echo "=== CSV to Laravel Seeder Generator ===\n\n";
        
        if (!file_exists($this->config['csv_file'])) {
            $this->error("File CSV tidak ditemukan: {$this->config['csv_file']}");
            return false;
        }
        
        echo "Membaca file CSV: {$this->config['csv_file']}\n";
        
        $data = $this->readCSV();
        
        if (empty($data)) {
            $this->error("Tidak ada data valid yang ditemukan di CSV");
            return false;
        }
        
        echo "Berhasil membaca " . count($data) . " baris data\n";
        
        if (!empty($this->warnings)) {
            echo "\nPeringatan:\n";
            foreach ($this->warnings as $warning) {
                echo "  - $warning\n";
            }
        }
        
        echo "\nMenggenerate seeder...\n";
        $seederContent = $this->generateSeederContent($data);
        
        $outputPath = $this->getOutputPath();
        $this->saveSeeder($outputPath, $seederContent);
        
        echo "\n✓ Seeder berhasil dibuat: $outputPath\n";
        echo "\nCara menggunakan:\n";
        echo "1. Jalankan: php artisan db:seed --class={$this->config['seeder_class']}\n";
        echo "2. Atau tambahkan ke DatabaseSeeder.php:\n";
        echo "   \$this->call({$this->config['seeder_class']}::class);\n";
        
        return true;
    }
    
    private function readCSV()
    {
        $data = [];
        $lineNumber = 0;
        
        if (($handle = fopen($this->config['csv_file'], 'r')) === false) {
            $this->error("Gagal membuka file CSV");
            return $data;
        }
        
        $headers = fgetcsv($handle);
        $lineNumber++;
        
        while (($row = fgetcsv($handle)) !== false) {
            $lineNumber++;
            
            $expectedColumns = max(array_column($this->config['column_mapping'], 'index')) + 1;
            if (count($row) < $expectedColumns) {
                $this->warning("Baris $lineNumber: Kolom tidak lengkap, dilewati");
                continue;
            }
            
            $processedRow = $this->processRow($row, $lineNumber);
            
            if ($processedRow !== null) {
                $data[] = $processedRow;
            }
        }
        
        fclose($handle);
        return $data;
    }
    
    private function processRow($row, $lineNumber)
    {
        $processedRow = [];
        
        foreach ($this->config['column_mapping'] as $dbColumn => $mapping) {
            $index = $mapping['index'];
            $type = $mapping['type'];
            $value = trim($row[$index]);
            
            if ($this->isNullValue($value)) {
                $processedRow[$dbColumn] = null;
                continue;
            }
            
            switch ($type) {
                case 'numeric':
                    if (!is_numeric($value)) {
                        $processedRow[$dbColumn] = null;
                        $this->warning("Baris $lineNumber: '$dbColumn' bukan angka valid, diset NULL");
                    } else {
                        if (isset($this->config['coordinate_validation'][$dbColumn])) {
                            $min = $this->config['coordinate_validation'][$dbColumn]['min'];
                            $max = $this->config['coordinate_validation'][$dbColumn]['max'];
                            
                            if ($value < $min || $value > $max) {
                                $processedRow[$dbColumn] = null;
                                $this->warning("Baris $lineNumber: '$dbColumn' = $value diluar range ($min s/d $max), diset NULL");
                            } else {
                                $processedRow[$dbColumn] = $value;
                            }
                        } else {
                            $processedRow[$dbColumn] = $value;
                        }
                    }
                    break;
                    
                case 'string':
                default:
                    $processedRow[$dbColumn] = $value;
                    break;
            }
        }
        
        foreach ($this->config['required_columns'] as $requiredCol) {
            if (!isset($processedRow[$requiredCol]) || $processedRow[$requiredCol] === null) {
                $this->warning("Baris $lineNumber: Kolom '$requiredCol' kosong/NULL, baris dilewati");
                return null;
            }
        }
        
        if ($this->config['add_timestamps']) {
            $processedRow['created_at'] = date('Y-m-d H:i:s');
            $processedRow['updated_at'] = date('Y-m-d H:i:s');
        }
        
        return $processedRow;
    }
    
    private function isNullValue($value)
    {
        return in_array($value, $this->config['null_values'], true);
    }
    
    private function generateSeederContent($data)
    {
        $className = $this->config['seeder_class'];
        $tableName = $this->config['table_name'];
        $batchSize = $this->config['batch_size'];
        $truncate = $this->config['truncate_table'] ? 'true' : 'false';
        
        $seeder = "<?php\n\n";
        $seeder .= "namespace Database\\Seeders;\n\n";
        $seeder .= "use Illuminate\\Database\\Seeder;\n";
        $seeder .= "use Illuminate\\Support\\Facades\\DB;\n\n";
        $seeder .= "class $className extends Seeder\n";
        $seeder .= "{\n";
        $seeder .= "    public function run(): void\n";
        $seeder .= "    {\n";
        $seeder .= "        DB::statement('SET FOREIGN_KEY_CHECKS=0;');\n\n";
        
        if ($this->config['truncate_table']) {
            $seeder .= "        DB::table('$tableName')->truncate();\n\n";
        }
        
        $seeder .= "        \$data = [\n";
        
        foreach ($data as $index => $row) {
            $seeder .= "            [\n";
            foreach ($row as $column => $value) {
                if ($value === null) {
                    $seeder .= "                '$column' => null,\n";
                } elseif (is_numeric($value)) {
                    $seeder .= "                '$column' => $value,\n";
                } else {
                    $escapedValue = addslashes($value);
                    $seeder .= "                '$column' => '$escapedValue',\n";
                }
            }
            $seeder .= "            ]";
            $seeder .= ($index < count($data) - 1) ? ",\n" : "\n";
        }
        
        $seeder .= "        ];\n\n";
        $seeder .= "        \$data = array_filter(\$data, function(\$row) {\n";
        
        $requiredCols = $this->config['required_columns'];
        if (!empty($requiredCols)) {
            $conditions = [];
            foreach ($requiredCols as $col) {
                $conditions[] = "isset(\$row['$col']) && \$row['$col'] !== null";
            }
            $filterCondition = implode(' && ', $conditions);
            $seeder .= "            return $filterCondition;\n";
        } else {
            $seeder .= "            return true;\n";
        }
        
        $seeder .= "        });\n\n";
        $seeder .= "        \$validCount = count(\$data);\n\n";
        $seeder .= "        if (\$validCount === 0) {\n";
        $seeder .= "            \$this->command->warn('Tidak ada data valid untuk diinsert!');\n";
        $seeder .= "            DB::statement('SET FOREIGN_KEY_CHECKS=1;');\n";
        $seeder .= "            return;\n";
        $seeder .= "        }\n\n";
        $seeder .= "        foreach (array_chunk(\$data, $batchSize) as \$chunk) {\n";
        $seeder .= "            DB::table('$tableName')->insert(\$chunk);\n";
        $seeder .= "        }\n\n";
        $seeder .= "        DB::statement('SET FOREIGN_KEY_CHECKS=1;');\n\n";
        $seeder .= "        \$this->command->info('Berhasil insert ' . \$validCount . ' data ke table $tableName!');\n";
        $seeder .= "    }\n";
        $seeder .= "}\n";
        
        return $seeder;
    }
    
    private function getOutputPath()
    {
        $dir = 'database/seeders';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        return $dir . '/' . $this->config['seeder_class'] . '.php';
    }
    
    private function saveSeeder($path, $content)
    {
        file_put_contents($path, $content);
    }
    
    private function error($message)
    {
        $this->errors[] = $message;
        echo "✗ ERROR: $message\n";
    }
    
    private function warning($message)
    {
        $this->warnings[] = $message;
    }
}

$generator = new SeederGenerator($config);
$generator->generate();