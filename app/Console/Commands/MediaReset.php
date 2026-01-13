<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use App\Models\File;
use App\Models\Folder;
use App\Models\RealEstateListing;

class MediaReset extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'media:reset';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset media data: truncate files/folders and clear listing images';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!$this->confirm('Do you really want to reset ALL media data? This cannot be undone.', true)) {
            $this->info('Operation cancelled.');
            return;
        }

        $this->info('Resetting media data...');

        Schema::disableForeignKeyConstraints();

        try {
            $this->info('Truncating files table...');
            File::truncate();

            $this->info('Truncating folders table...');
            Folder::truncate();

            $this->info('Clearing images from real_estate_listings...');
            RealEstateListing::query()->update(['images' => null]);

            $this->info('Media reset completed successfully!');
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
        } finally {
            Schema::enableForeignKeyConstraints();
        }
    }
}
