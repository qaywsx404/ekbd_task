<?php

namespace App\Console\Commands;

use App\Http\Controllers\ImportControllers\DicLicenceTypeImportController;
use App\Http\Controllers\ImportControllers\DicPiImportController;
use App\Http\Controllers\ImportControllers\DicPurposeImportController;
use App\Http\Controllers\ImportControllers\DicReasonImportController;
use App\Http\Controllers\ImportControllers\DicFlangStatusImportController;
use App\Http\Controllers\ImportControllers\DicCompFormImportController;
use App\Http\Controllers\ImportControllers\DicArcticZoneImportController;
use App\Http\Controllers\ImportControllers\DicDepositStageImportController;
use App\Http\Controllers\ImportControllers\DicDepositSizeImportController;
use App\Http\Controllers\ImportControllers\DicDepositTypeImportController;
use App\Http\Controllers\ImportControllers\DicDepositSubstanceImportController;
use App\Http\Controllers\ImportControllers\DicZapovednikCategoryImportController;
use App\Http\Controllers\ImportControllers\DicZapovednikImportanceImportController;
use App\Http\Controllers\ImportControllers\DicZapovednikProfileImportController;
use App\Http\Controllers\ImportControllers\DicZapovednikStateImportController;
use App\Http\Controllers\ImportControllers\DicNgpTypeImportController;
use App\Http\Controllers\ImportControllers\DicNgoTypeImportController;
use App\Http\Controllers\ImportControllers\DicNgrTypeImportController;
use App\Http\Controllers\ImportControllers\DicSsubRfImportController;
use App\Http\Controllers\ImportControllers\LicenseImportController;
use Illuminate\Console\Command;

class ImportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:import {table?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Импорт бд или отдельных таблиц. Параметр table - название заполняемой таблицы. Без параметра - заполнение всей БД';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        switch($this->argument('table')) {
            case null:
                dump( 'Импорт всей бд' );
                DicLicenceTypeImportController::import();
                DicPiImportController::import();
                DicPurposeImportController::import();
                DicReasonImportController::import();
                DicFlangStatusImportController::import();
                DicCompFormImportController::import();
                DicArcticZoneImportController::import();
                DicDepositStageImportController::import();
                DicDepositSizeImportController::import();
                DicDepositTypeImportController::import();
                DicDepositSubstanceImportController::import();
                DicZapovednikCategoryImportController::import();
                DicZapovednikImportanceImportController::import();
                DicZapovednikProfileImportController::import();
                DicZapovednikStateImportController::import();
                DicNgpTypeImportController::import();
                DicNgoTypeImportController::import();
                DicNgrTypeImportController::import();
                DicSsubRfImportController::import();
                LicenseImportController::import();
                dump( 'done' );
                break;

            case 'dic_license_type':
                dump( 'Импорт dic_license_type' );
                DicLicenceTypeImportController::import();
                dump( 'done' );
                break;

            case 'dic_pi':
                dump( 'Импорт dic_pi' );
                DicPiImportController::import();
                dump( 'done' );
                break;

            case 'dic_purpose':
                dump( 'Импорт dic_purpose' );
                DicPurposeImportController::import();
                dump( 'done' );
                break;

            case 'dic_reason':
                dump( 'Импорт dic_reason' );
                DicReasonImportController::import();
                dump( 'done' );
                break;

            case 'dic_flang_status':
                dump( 'Импорт dic_flang_status' );
                DicFlangStatusImportController::import();
                dump( 'done' );
                break;

            case 'dic_comp_form':
                dump( 'Импорт dic_comp_form' );
                DicCompFormImportController::import();
                dump( 'done' );
                break; 

            case 'dic_arctic_zone':
                dump( 'Импорт dic_arctic_zone' );
                DicArcticZoneImportController::import();
                dump( 'done' );
                break;

            case 'dic_deposit_stage':
                dump( 'Импорт dic_deposit_stage' );
                DicDepositStageImportController::import();
                dump( 'done' );
                break;

            case 'dic_deposit_size':
                dump( 'Импорт dic_deposit_size' );
                DicDepositSizeImportController::import();
                dump( 'done' );
                break; 

            case 'dic_deposit_type':
                dump( 'Импорт dic_deposit_type' );
                DicDepositTypeImportController::import();
                dump( 'done' );
                break;  

            case 'dic_deposit_substance':
                dump( 'Импорт dic_deposit_substance' );
                DicDepositSubstanceImportController::import();
                dump( 'done' );
                break;  

            case 'dic_zapovednik_category':
                dump( 'Импорт dic_zapovednik_category' );
                DicZapovednikCategoryImportController::import();
                dump( 'done' );
                break;  

            case 'dic_zapovednik_importance':
                dump( 'Импорт dic_zapovednik_importance' );
                DicZapovednikImportanceImportController::import();
                dump( 'done' );
                break; 

            case 'dic_zapovednik_profile':
                dump( 'Импорт dic_zapovednik_profile' );
                DicZapovednikProfileImportController::import();
                dump( 'done' );
                break; 

            case 'dic_zapovednik_state':
                dump( 'Импорт dic_zapovednik_state' );
                DicZapovednikStateImportController::import();
                dump( 'done' );
                break;   

            case 'dic_ngp_type':
                dump( 'Импорт dic_ngp_type' );
                DicNgpTypeImportController::import();
                dump( 'done' );
                break;  

            case 'dic_ngo_type':
                dump( 'Импорт dic_ngo_type' );
                DicNgoTypeImportController::import();
                dump( 'done' );
                break;  

            case 'dic_ngr_type':
                dump( 'Импорт dic_ngr_type' );
                DicNgrTypeImportController::import();
                dump( 'done' );
                break;  

            case 'dic_ssub_rf':
                dump( 'Импорт dic_ssub_rf' );
                DicSsubRfImportController::import();
                dump( 'done' );
                break;  
            
            // Сущности

            case 'license':
                dump( 'Импорт license' );
                LicenseImportController::import();
                dump( 'done' );
                break;  

            default:
            dump( 'Неверное имя таблицы, команда не выполнена' );
        }
    }
}