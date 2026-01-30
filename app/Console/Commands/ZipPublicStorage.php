<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use ZipArchive;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class ZipPublicStorage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'storage:zip-backup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a ZIP backup of the public storage directory (images) for easy download.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Starting storage backup process...");

        if (!class_exists('ZipArchive')) {
            $this->error("The ZipArchive class is not available. Please enable the php-zip extension.");
            return 1;
        }

        // Source: storage/app/public
        $sourcePath = storage_path('app/public');
        
        // Destination: storage/app/backups/ (safe from public access)
        $backupDir = storage_path('app/backups');
        if (!file_exists($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $fileName = 'storage_backup_' . date('Y-m-d_H-i-s') . '.zip';
        $zipFilePath = $backupDir . DIRECTORY_SEPARATOR . $fileName;

        $this->comment("Source: {$sourcePath}");
        $this->comment("Destination: {$zipFilePath}");

        $zip = new ZipArchive();

        if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            $this->error("Failed to create ZIP file at {$zipFilePath}");
            return 1;
        }

        // Create recursive iterator
        if (!is_dir($sourcePath)) {
            $this->error("Source directory does not exist.");
            return 1;
        }

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($sourcePath, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        $this->info("Scanning files...");
        
        // Count files for progress bar (optional, but good for UX)
        $fileCount = 0;
        foreach ($files as $file) {
            $fileCount++;
        }
        
        // Reset iterator
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($sourcePath, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        $bar = $this->output->createProgressBar($fileCount);
        $bar->start();

        foreach ($files as $name => $file) {
            // Skip directories (they are added automatically when files are added)
            if (!$file->isFile()) {
                continue;
            }

            $filePath = $file->getRealPath();
            
            // Calculate relative path for the zip
            // If Source is /var/www/storage/app/public
            // And File is /var/www/storage/app/public/businesses/1/img.jpg
            // Relative path should be businesses/1/img.jpg
            $relativePath = substr($filePath, strlen($sourcePath) + 1);

            // Add to zip
            $zip->addFile($filePath, $relativePath);
            
            $bar->advance();
        }

        $zip->close();
        $bar->finish();

        $this->newLine(2);
        $this->info("✅ Backup created successfully!");
        $this->line("Location: <fg=yellow>{$zipFilePath}</>");
        $this->line("Size: " . round(filesize($zipFilePath) / 1024 / 1024, 2) . " MB");
        $this->comment("You can now download this single ZIP file via FileZilla from the /storage/app/backups/ directory.");

        return 0;
    }
}
