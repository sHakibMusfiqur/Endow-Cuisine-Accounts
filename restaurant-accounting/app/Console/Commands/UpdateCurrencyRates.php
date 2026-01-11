<?php

namespace App\Console\Commands;

use App\Services\CurrencyRateService;
use Illuminate\Console\Command;

class UpdateCurrencyRates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'currency:update-rates 
                            {--force : Force update even if recently updated}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update currency exchange rates (USD, BDT, KRW) from external API';

    /**
     * Execute the console command.
     */
    public function handle(CurrencyRateService $currencyRateService): int
    {
        $this->info('Starting currency rate update...');
        
        // Check if update is needed
        if (!$this->option('force') && !$currencyRateService->needsUpdate()) {
            $this->info('Rates were recently updated. Skipping...');
            $this->info('Last update: ' . $currencyRateService->getLastUpdateTime());
            return self::SUCCESS;
        }

        // Update rates
        $results = $currencyRateService->updateAllRates();

        // Display results
        if ($results['success']) {
            $this->info('✓ Currency rates updated successfully!');
            
            if (!empty($results['updated'])) {
                $this->info('Updated currencies: ' . implode(', ', $results['updated']));
            }
            
            if (!empty($results['failed'])) {
                $this->warn('Failed currencies: ' . implode(', ', $results['failed']));
            }
            
            $this->info('Last update: ' . $currencyRateService->getLastUpdateTime());
            
            return self::SUCCESS;
        } else {
            $this->error('✗ Failed to update currency rates');
            $this->error($results['message']);
            
            return self::FAILURE;
        }
    }
}
