<?php

namespace App\Console\Commands;

use App\Exports\DicSsubRfExport;
use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;

class ExportSsubRf extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:export-ssub-rf
                            {--o|only : запись только встретившихся субъектов};
                            {--i|information : вывод несопаставленных субъектов}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Экспорт импортируемых субъектов в ekbd_sub_m.xlsx: names';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if($this->option('only')) DicSsubRfExport::$isOnlyEnc = true;
        if($this->option('information')) DicSsubRfExport::$showInf = true;
        
        Excel::store(new DicSsubRfExport, 'ekbd_sub_m.xlsx');
        
        dump('done');
    }
}
