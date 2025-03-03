<?php

namespace App\Console\Commands;

use App\Models\Taxpayer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;


class DownloadCompanies extends Command
{
    protected $signature = 'app:download-companies';
    protected $description = 'Downloads taxpayer data CSV from data.gov.lt and imports it to database';

    public function handle(): int
    {
        $this->info('Preparing to download taxpayer data...');

        $url = 'https://get.data.gov.lt/datasets/gov/vmi/mm_registras/MokesciuMoketojas/:format/csv';
        $folder = 'companies';
        $filename = sprintf('data_company_list_%s.csv', now()->format('Y-m-d'));
        $filePath = "$folder/$filename";
        $fullPath = public_path($filePath);

        try {
            $this->ensureDirectoryExists($folder);

            if (File::exists($fullPath)) {
                if (File::delete($fullPath)) {
                    $this->info("Removed existing file: $filePath");
                } else {
                    throw new \RuntimeException("Failed to remove existing file: $filePath");
                }
            }

            $this->downloadFile($url, $fullPath);

            if (file_exists($fullPath) && filesize($fullPath) > 0) {
                $this->info("Download finished");
                $this->info("Saved to: $filePath");
                $this->info("Size: " . $this->formatSize(filesize($fullPath)));

                $this->importCsvToDatabase($fullPath);

                return Command::SUCCESS;
            }

            throw new \RuntimeException('Download succeeded but file is missing or empty');
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    private function ensureDirectoryExists(string $folder): void
    {
        $path = public_path($folder);

        if (!File::exists($path)) {
            if (!File::makeDirectory($path, 0755, true)) {
                throw new \RuntimeException("Failed to create directory: $folder");
            }
            $this->info("Created directory: $folder");
        } elseif (!File::isDirectory($path)) {
            throw new \RuntimeException("Path exists but is not a directory: $folder");
        }
    }

    private function downloadFile(string $url, string $filePath): void
    {
        $this->info('Started download...');

        $fileHandle = fopen($filePath, 'w');
        if ($fileHandle === false) {
            throw new \RuntimeException('Failed to open file for writing');
        }

        try {
            $response = Http::withOptions(['stream' => true])->get($url);
            if (!$response->successful()) {
                throw new \RuntimeException('Download failed: ' . $response->status());
            }

            $stream = $response->toPsrResponse()->getBody();

            while (!$stream->eof()) {
                $chunk = $stream->read(8192);
                $written = fwrite($fileHandle, $chunk);

                if ($written === false) {
                    throw new \RuntimeException('Failed to write to file');
                }
            }
        } finally {
            fclose($fileHandle);
        }

        $response = Http::head($url);
        if ($response->successful()) {
            $totalSize = (int) $response->header('Content-Length') ?: 0;
            if ($totalSize > 0 && filesize($filePath) !== $totalSize) {
                throw new \RuntimeException('Download incomplete: Size mismatch');
            }
        }
    }

    private function importCsvToDatabase(string $filePath): void
    {
        $this->info('Starting CSV import to database...');

        $file = fopen($filePath, 'r');
        if ($file === false) {
            throw new \RuntimeException('Failed to open CSV file for reading');
        }

        try {
            $header = fgetcsv($file);
            if ($header === false) {
                throw new \RuntimeException('Failed to read CSV header');
            }

            $fieldMap = [
                'id' => 'id',
                'ja_kodas' => 'ja_kodas',
                'pavadinimas' => 'pavadinimas',
                'ireg_data' => 'ireg_data',
                'isreg_data' => 'isreg_data',
                'anul_data' => 'anul_data',
                'valstybe' => 'valstybe',
                'tipo_aprasymas' => 'tipo_aprasymas',
                'pvm_kodas_pref' => 'pvm_kodas_pref',
                'pvm_kodas' => 'pvm_kodas',
                'pvm_iregistruota' => 'pvm_iregistruota',
                'pvm_isregistruota' => 'pvm_isregistruota',
                'padalinio_nr' => 'padalinio_nr',
                'padalinio_pvd' => 'padalinio_pvd',
                'padalinio_savivaldybe' => 'padalinio_savivaldybe',
                'padalinio_kodas' => 'padalinio_kodas',
                'suformuota' => 'suformuota',
                'isformuota' => 'isformuota',
                'veiklos_pradzia' => 'veiklos_pradzia',
                'veiklos_pabaiga' => 'veiklos_pabaiga',
                'veikla_anuliuota' => 'veikla_anuliuota',
                'pagrindine' => 'pagrindine',
                'vv_savivaldybe' => 'vv_savivaldybe',
                'vv_adresas_nuo' => 'vv_adresas_nuo',
                'vv_adresas_iki' => 'vv_adresas_iki',
                'vv_adresas_anul' => 'vv_adresas_anul',
                'mm_grupe' => 'mm_grupe',
                'grup_aprasymas' => 'grup_aprasymas',
                'grupe_nuo' => 'grupe_nuo',
                'grupe_iki' => 'grupe_iki',
                'grupe_anul' => 'grupe_anul',
            ];

            $header = array_map('trim', $header);
            $rowCount = 0;
            $batch = [];

            while (($row = fgetcsv($file)) !== false) {
                $data = array_combine($header, $row);
                if ($data === false) {
                    $this->warn("Skipping malformed row at line " . ($rowCount + 2));
                    continue;
                }

                $insertData = [];
                foreach ($fieldMap as $csvField => $dbField) {
                    $value = $data[$csvField] ?? null;
                    $insertData[$dbField] = $value === '' ? null : $value;
                }

                $batch[] = $insertData;

                if (count($batch) >= 1000) { // Batch insert every 1000 rows
                    Taxpayer::insert($batch);
                    $rowCount += count($batch);
                    $batch = [];
                }
            }

            // Insert remaining rows
            if (!empty($batch)) {
                Taxpayer::insert($batch);
                $rowCount += count($batch);
            }

            $this->info("Imported $rowCount records to database");
        } finally {
            fclose($file);
        }
    }

    private function formatSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $size = $bytes;
        $index = 0;

        while ($size >= 1024 && $index < count($units) - 1) {
            $size /= 1024;
            $index++;
        }

        return sprintf('%.2f %s', $size, $units[$index]);
    }
}
