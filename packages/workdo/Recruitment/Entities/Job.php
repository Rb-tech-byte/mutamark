<?php

namespace Modules\Recruitment\Entities;

use App\Models\Permission;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use App\Models\WorkSpace;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Job extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'requirement',
        'terms_and_conditions',
        'branch',
        'category',
        'skill',
        'position',
        'start_date',
        'end_date',
        'status',
        'applicant',
        'visibility',
        'code',
        'custom_question',
        'workspace',
        'created_by',
    ];

    protected static function newFactory()
    {
        return \Modules\Recruitment\Database\factories\JobFactory::new();
    }

    public static $status = [
        'active' => 'Active',
        'in_active' => 'In Active',
    ];

    public static $job_type = [
        '' => 'Select Job Type',
        'full_time' => 'Full Time',
        'part_time' => 'Part Time',
    ];

    public function branches()
    {
        return $this->hasOne(\Modules\Hrm\Entities\Branch::class, 'id', 'branch');
    }

    public function categories()
    {
        return $this->hasOne(JobCategory::class, 'id', 'category');
    }

    public function questions()
    {
        $ids = explode(',', $this->custom_question);

        return CustomQuestion::whereIn('id', $ids)->get();
    }

    public function createdBy()
    {
        return $this->hasOne(User::class, 'id', 'created_by');
    }

    public static function defaultdata($company_id = null, $workspace_id = null)
    {
        $company_setting = [
            "job_template" => "template1",
            "job_color" => "2d2e31",
        ];

        $job_stages = [
            "Applied",
            "Phone Screen",
            "Interview",
            "Hired",
            "Rejected",
        ];

        if ($company_id == Null) {
            $companys = User::where('type', 'company')->get();
            foreach ($companys as $company) {
                $WorkSpaces = WorkSpace::where('created_by', $company->id)->get();
                foreach ($WorkSpaces as $WorkSpace) {
                    OfferLetter::defaultOfferLetter($company->id, $WorkSpace->id);

                    foreach ($job_stages as $job_stage) {
                        $jobstage = JobStage::where('title', $job_stage)->where('workspace', $WorkSpace->id)->where('created_by', $company->id)->first();
                        if ($jobstage == null) {
                            $jobstage = new JobStage();
                            $jobstage->title = $job_stage;
                            $jobstage->workspace =  !empty($WorkSpace->id) ? $WorkSpace->id : 0;
                            $jobstage->created_by = !empty($company->id) ? $company->id : 2;
                            $jobstage->save();
                        }
                    }

                    foreach ($company_setting as $key => $value) {
                        // Define the data to be updated or inserted
                        $data = [
                            'key' => $key,
                            'workspace' => !empty($WorkSpace->id) ? $WorkSpace->id : 0,
                            'created_by' => $company->id,
                        ];

                        // Check if the record exists, and update or insert accordingly
                        Setting::updateOrInsert($data, ['value' => $value]);
                    }
                }
            }
        } elseif ($workspace_id == Null) {
            $company = User::where('type', 'company')->where('id', $company_id)->first();
            $WorkSpaces = WorkSpace::where('created_by', $company->id)->get();
            foreach ($WorkSpaces as $WorkSpace) {
                foreach ($job_stages as $job_stage) {
                    OfferLetter::defaultOfferLetter($company->id, $WorkSpace->id);

                    $jobstage = JobStage::where('title', $job_stage)->where('workspace', $WorkSpace->id)->where('created_by', $company->id)->first();
                    if ($jobstage == null) {
                        $jobstage = new JobStage();
                        $jobstage->title = $job_stage;
                        $jobstage->workspace =  !empty($WorkSpace->id) ? $WorkSpace->id : 0;
                        $jobstage->created_by = !empty($company->id) ? $company->id : 2;
                        $jobstage->save();
                    }

                    foreach ($company_setting as $key => $value) {
                        // Define the data to be updated or inserted
                        $data = [
                            'key' => $key,
                            'workspace' => !empty($WorkSpace->id) ? $WorkSpace->id : 0,
                            'created_by' => $company->id,
                        ];
    
                        // Check if the record exists, and update or insert accordingly
                        Setting::updateOrInsert($data, ['value' => $value]);
                    }
                }
            }
        } else {
            $company = User::where('type', 'company')->where('id', $company_id)->first();
            $WorkSpace = WorkSpace::where('created_by', $company->id)->where('id', $workspace_id)->first();
            foreach ($job_stages as $job_stage) {
                OfferLetter::defaultOfferLetter($company->id, $WorkSpace->id);

                $jobstage = JobStage::where('title', $job_stage)->where('workspace', $WorkSpace->id)->where('created_by', $company->id)->first();
                if ($jobstage == null) {
                    $jobstage = new JobStage();
                    $jobstage->title = $job_stage;
                    $jobstage->workspace =  !empty($WorkSpace->id) ? $WorkSpace->id : 0;
                    $jobstage->created_by = !empty($company->id) ? $company->id : 2;
                    $jobstage->save();
                }

                foreach ($company_setting as $key => $value) {
                    // Define the data to be updated or inserted
                    $data = [
                        'key' => $key,
                        'workspace' => !empty($WorkSpace->id) ? $WorkSpace->id : 0,
                        'created_by' => $company->id,
                    ];
    
                    // Check if the record exists, and update or insert accordingly
                    Setting::updateOrInsert($data, ['value' => $value]);
                }
            }
        }
    }

    public static function GivePermissionToRoles($role_id = null, $rolename = null)
    {
        $staff_permission = [
            'career manage',
            'recruitment manage',
        ];

        $hr_permission = [
            'recruitment manage',
            'recruitment dashboard manage',
            'job manage',
            'job create',
            'job edit',
            'job delete',
            'job show',
            'jobcategory manage',
            'jobcategory create',
            'jobcategory edit',
            'jobcategory delete',
            'jobstage manage',
            'jobstage create',
            'jobstage edit',
            'jobstage delete',
            'jobapplication manage',
            'jobapplication create',
            'jobapplication show',
            'jobapplication delete',
            'jobapplication move',
            'jobapplication add skill',
            'jobapplication add note',
            'jobapplication delete note',
            'jobapplication archived manage',
            'jobapplication candidate manage',
            'jobapplication candidate create',
            'jobapplication candidate edit',
            'jobapplication candidate delete',
            'jobonboard manage',
            'jobonboard create',
            'jobonboard edit',
            'jobonboard delete',
            'jobonboard convert',
            'custom question manage',
            'custom question create',
            'custom question edit',
            'custom question delete',
            'interview schedule manage',
            'interview schedule create',
            'interview schedule edit',
            'interview schedule delete',
            'interview schedule show',
            'career manage',
            'letter offer manage',
            'job candidate manage',
            'job candidate create',
            'job candidate edit',
            'job candidate delete',
            'job experience manage',
            'job experience create',
            'job experience edit',
            'job experience show',
            'job experience delete',
            'job project manage',
            'job project create',
            'job project show',
            'job project edit',
            'job project delete',
            'experience candidate job manage',
            'experience candidate job create',
            'experience candidate job show',
            'experience candidate job edit',
            'experience candidate job delete',
            'job qualification manage',
            'job qualification create',
            'job qualification show',
            'job qualification edit',
            'job qualification delete',
            'job skill manage',
            'job skill create',
            'job skill show',
            'job skill edit',
            'job skill delete',
            'job award manage',
            'job award create',
            'job award show',
            'job award edit',
            'job award delete',
        ];

        if ($role_id == Null) {
            // staff
            $roles_v = Role::where('name', 'staff')->get();

            foreach ($roles_v as $role) {
                foreach ($staff_permission as $permission_v) {
                    $permission = Permission::where('name', $permission_v)->first();
                    if (!empty($permission)) {
                        if (!$role->hasPermission($permission_v)) {
                            $role->givePermission($permission);
                        }
                    }
                }
            }

            // hr
            $roles_v = Role::where('name', 'hr')->get();

            foreach ($roles_v as $role) {
                foreach ($hr_permission as $permission_v) {
                    $permission = Permission::where('name', $permission_v)->first();
                    if (!empty($permission)) {
                        if (!$role->hasPermission($permission_v)) {
                            $role->givePermission($permission);
                        }
                    }
                }
            }
        } else {
            if ($rolename == 'staff') {
                $roles_v = Role::where('name', 'staff')->where('id', $role_id)->first();
                if ($roles_v) {
                    foreach ($staff_permission as $permission_v) {
                        $permission = Permission::where('name', $permission_v)->first();
                        if (!empty($permission)) {
                            try {
                                if (!$roles_v->hasPermission($permission_v)) {
                                    $roles_v->givePermission($permission);
                                }
                            } catch (\Throwable $e) {
                                // Already assigned
                            }
                        }
                    }
                }
            }

            if ($rolename == 'hr') {
                $roles_v = Role::where('name', 'hr')->where('id', $role_id)->first();
                if ($roles_v) {
                    foreach ($hr_permission as $permission_v) {
                        $permission = Permission::where('name', $permission_v)->first();
                        if (!empty($permission)) {
                            try {
                                if (!$roles_v->hasPermission($permission_v)) {
                                    $roles_v->givePermission($permission);
                                }
                            } catch (\Throwable $e) {
                                // Already assigned
                            }
                        }
                    }
                }
            }
        }
    }
}
