<?php

namespace App\Console\Commands;

use App\Actions\Maintenance\GeneratePreventiveScheduleAction;
use Illuminate\Console\Command;

class CheckPreventiveMaintenanceSchedule extends Command
{
    protected $signature = 'pm:check';

    protected $description = 'Cek interval PM tiap aset, generate WO draft untuk aset yang jatuh tempo, dan notifikasi Supervisor';

    public function handle(GeneratePreventiveScheduleAction $action): int
    {
        $this->info('Mengecek jadwal PM...');

        $generated = $action->execute();

        if (empty($generated)) {
            $this->info('Tidak ada aset yang jatuh tempo PM hari ini.');

            return self::SUCCESS;
        }

        $this->info(count($generated).' work order PM berhasil digenerate:');

        foreach ($generated as $wo) {
            $this->line("- WO #{$wo->id} untuk aset \"{$wo->asset->name}\"");
        }

        return self::SUCCESS;
    }
}
