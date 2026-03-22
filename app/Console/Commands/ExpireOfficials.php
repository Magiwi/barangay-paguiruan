<?php

namespace App\Console\Commands;

use App\Http\Controllers\Admin\OfficialController;
use Illuminate\Console\Command;

class ExpireOfficials extends Command
{
    protected $signature = 'officials:expire';

    protected $description = 'Deactivate officials with expired terms and revoke their permissions';

    public function handle(): int
    {
        $count = OfficialController::processExpiredOfficials();

        if ($count > 0) {
            $this->info("Processed {$count} expired official(s).");
        } else {
            $this->info('No expired officials found.');
        }

        return self::SUCCESS;
    }
}
