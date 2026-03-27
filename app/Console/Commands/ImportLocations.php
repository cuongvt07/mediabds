<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class ImportLocations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-locations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Vietnamese administrative units from provinces.open-api.vn and cache locally';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting location data import from provinces.open-api.vn...');

        try {
            // Fetch all provinces with districts and wards (depth=3)
            // This is a large response (~10MB)
            $this->info('Fetching all administrative units (depth=3)...');
            $response = Http::timeout(120)->get('https://provinces.open-api.vn/api/?depth=3');

            if (!$response->successful()) {
                $this->error('Failed to fetch data from API. Status: ' . $response->status());
                return 1;
            }

            $data = $response->json();
            
            $this->info('Restructuring data for faster lookup...');
            
            // We'll store a simplified structure to keep the file size reasonable
            // [province_id => [name => ..., districts => [district_id => [name => ..., wards => [ward_id => name]]]]]
            $structured = [];
            foreach ($data as $province) {
                $pCode = str_pad($province['code'], 2, '0', STR_PAD_LEFT);
                $structured[$pCode] = [
                    'name' => $province['name'],
                    'districts' => []
                ];
                
                foreach ($province['districts'] as $district) {
                    $dCode = str_pad($district['code'], 3, '0', STR_PAD_LEFT);
                    $structured[$pCode]['districts'][$dCode] = [
                        'name' => $district['name'],
                        'wards' => []
                    ];
                    
                    foreach ($district['wards'] as $ward) {
                        $wCode = str_pad($ward['code'], 5, '0', STR_PAD_LEFT);
                        $structured[$pCode]['districts'][$dCode]['wards'][$wCode] = $ward['name'];
                    }
                }
            }
            
            $this->info('Saving to local storage...');
            
            // Ensure the directory exists
            if (!Storage::disk('local')->exists('locations')) {
                Storage::disk('local')->makeDirectory('locations');
            }

            Storage::disk('local')->put('locations/all_vietnam.json', json_encode($structured, JSON_UNESCAPED_UNICODE));
            
            $this->info('Import completed! Data saved to storage/app/locations/all_vietnam.json');
            return 0;
            
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return 1;
        }
    }
}
