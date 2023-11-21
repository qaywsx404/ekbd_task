<?php

namespace App\Models\ebd_ekbd\dictionaries;

use Illuminate\Database\Eloquent\Model;

class DictionaryModel extends Model
{
    public $incrementing = false;
    public $timestamps = false;
    const CREATED_AT = 'cdate';
    protected $guarded = ['id'];
}

// class DicLicenseType extends DictionaryModel { protected $table = 'dic_license_type'; }
// class DicPi extends DictionaryModel { protected $table = 'dic_pi'; }
// class DicPurpose extends DictionaryModel { protected $table = 'dic_purpose'; }
// class DicReason extends DictionaryModel { protected $table = 'dic_reason'; }
// class DicFlangStatus extends DictionaryModel { protected $table = 'dic_flang_status'; }
// class DicCompForm extends DictionaryModel { protected $table = 'dic_comp_form'; }
// class DicArcticZone extends DictionaryModel { protected $table = 'dic_arctic_zone'; }
// class DicDepositStage extends DictionaryModel { protected $table = 'dic_deposit_stage'; }
// class DicDepositSize extends DictionaryModel { protected $table = 'dic_deposit_size'; }
// class DicDepositType extends DictionaryModel { protected $table = 'dic_deposit_type'; }
// class DicDepositSubstance extends DictionaryModel { protected $table = 'dic_deposit_substance'; }
// class DicZapovednikCategory extends DictionaryModel { protected $table = 'dic_zapovednik_category'; }
// class DicZapovednikImportance extends DictionaryModel { protected $table = 'dic_zapovednik_importance'; }
// class DicZapovednikProfile extends DictionaryModel { protected $table = 'dic_zapovednik_profile'; }
// class DicZapovednikState extends DictionaryModel { protected $table = 'dic_zapovednik_state'; }
// class DicNgpType extends DictionaryModel { protected $table = 'dic_ngp_type'; }
// class DicNgoType extends DictionaryModel { protected $table = 'dic_ngo_type'; }
// class DicNgrType extends DictionaryModel { protected $table = 'dic_ngr_type'; }