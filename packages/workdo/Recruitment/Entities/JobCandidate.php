<?php

namespace Modules\Recruitment\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class JobCandidate extends Model
{
    use HasFactory;

    protected $fillable = [];

    public static $reference = [
        '' => 'Select reference',
        'yes' => 'Yes',
        'no' => 'No',
    ];
    
    protected static function newFactory()
    {
        return \Modules\Recruitment\Database\factories\JobCandidateFactory::new();
    }

    public static function templateData()
    {
        $arr = [];
        $arr['colors'] = [
            '2d2e31',
            '003580',
            '666666',
            '6676ef',
            'f50102',
            'f9b034',
            'fbdd03',
            'c1d82f',
            '37a4e4',
            '8a7966',
            '6a737b',
            '050f2c',
            '0e3666',
            '3baeff',
            '3368e6',
            'b84592',
            'f64f81',
            'f66c5f',
            'fac168',
            '46de98',
            '40c7d0',
            'be0028',
            '2f9f45',
            '371676',
            '52325d',
            '511378',
            '0f3866',
            '48c0b6',
            'ffffff',
            '297cc0',
            '000',
        ];
        $arr['templates'] = [
            "template1" => "Resume 1",
            "template2" => "Resume 2",
        ];
        return $arr;
    }

    // get font-color code accourding to bg-color
    public static function hex2rgb($hex)
    {
        $hex = str_replace("#", "", $hex);

        if (strlen($hex) == 3) {
            $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
            $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
            $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
        } else {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
        }
        $rgb = array(
            $r,
            $g,
            $b,
        );

        //return implode(",", $rgb); // returns the rgb values separated by commas
        return $rgb; // returns an array with the rgb values
    }

    public static function getFontColor($color_code)
    {
        $rgb = self::hex2rgb($color_code);
        $R = $G = $B = $C = $L = $color = '';

        $R = (floor($rgb[0]));
        $G = (floor($rgb[1]));
        $B = (floor($rgb[2]));

        $C = [
            $R / 255,
            $G / 255,
            $B / 255,
        ];

        for ($i = 0; $i < count($C); ++$i) {
            if ($C[$i] <= 0.03928) {
                $C[$i] = $C[$i] / 12.92;
            } else {
                $C[$i] = pow(($C[$i] + 0.055) / 1.055, 2.4);
            }
        }

        $L = 0.2126 * $C[0] + 0.7152 * $C[1] + 0.0722 * $C[2];

        if ($L > 0.179) {
            $color = 'black';
        } else {
            $color = 'white';
        }

        return $color;
    }

    public function JobExperience(){
        return $this->hasMany(JobExperience::class,'candidate_id','id');
    }

    public function JobProject(){
        return $this->hasMany(JobProject::class,'candidate_id','id');
    }
    public function JobExperienceCandidate(){
        return $this->hasMany(JobExperienceCandidate::class,'candidate_id','id');
    }
    public function JobQualification(){
        return $this->hasMany(JobQualification::class,'candidate_id','id');
    }
    public function JobSkill(){
        return $this->hasMany(JobSkill::class,'candidate_id','id');
    }
    public function JobAward(){
        return $this->hasMany(JobAward::class,'candidate_id','id');
    }
}
