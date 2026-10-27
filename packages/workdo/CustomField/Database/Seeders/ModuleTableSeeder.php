<?php

namespace Modules\CustomField\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class ModuleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();


        $custom_fileds = [];

        $custom_fileds['Base'] =  ['Proposal', 'Invoice'];
        $custom_fileds['Account'] = ['Customer', 'Vendor', 'Bill'];
        $custom_fileds['Assets'] =  ['Assets'];
        $custom_fileds['Contract'] =  ['Contract'];
        $custom_fileds['Hrm'] =  ['Employee'];
        $custom_fileds['Lead'] =  ['Lead', 'Deal'];
        $custom_fileds['Performance'] =  ['Goal Tracking'];
        $custom_fileds['Pos'] =  ['Warehouse', 'Purchase'];
        $custom_fileds['ProductService'] =  ['Product & Service'];
        $custom_fileds['Retainer'] =  ['Retainer'];
        $custom_fileds['Rotas'] =  ['RotaEmployee'];
        $custom_fileds['Sales'] =  ['Quotes', 'Sales Invoice', 'Sales Order', 'Account', 'Contact', 'Opportunities', 'Case'];
        $custom_fileds['Taskly'] =  ['Projects', 'Tasks', 'Bugs'];
        $custom_fileds['ChildcareManagement'] =  ['Inquiry','Parent', 'Child'];
        $custom_fileds['Holidayz'] =  ['Hotel Customers'];
        $custom_fileds['CMMS'] =  ['Component', 'Supplier', 'Purchase Orders'];
        $custom_fileds['Fleet'] =  ['Driver', 'Customer', 'Vehicle'];
        $custom_fileds['TourTravelManagement'] =  ['Tour'];
        $custom_fileds['DoubleEntry'] =  ['Journal Account'];
        $custom_fileds['Internalknowledge'] =  ['Book', 'Article'];
        $custom_fileds['School'] =  ['Addmission', 'Student', 'Parent', 'Home Work'];
        $custom_fileds['MusicInstitute'] =  ['Student', 'Teacher', 'Instrument', 'Class'];
        $custom_fileds['GymManagement'] =  ['Trainer', 'Member', 'Measurement'];
        $custom_fileds['AgricultureManagement'] =  ['Agriculture Fleet', 'Agriculture Process', 'Agriculture Equipment', 'Agriculture Crop', 'Agriculture User', 'Agriculture Cultivation'];
        $custom_fileds['GarageManagement'] =  ['Vehicle', 'Service'];
        $custom_fileds['CourierManagement'] =  ['Courier'];
        $custom_fileds['Newspaper'] =  ['Agent', 'Journalist', 'News paper', 'Advertisement'];
        $custom_fileds['LegalCaseManagement'] =  ['Advocate', 'Courts', 'Cases'];
        $custom_fileds['PropertyManagement'] =  ['Units', 'Tenants', 'Maintenance Request'];
        $custom_fileds['HospitalManagement'] =  [ 'Doctor', 'Patient', 'Appointment'];
        $custom_fileds['BeautySpaManagement'] =  ['Booking'];
        $custom_fileds['ParkingManagement'] =  ['Parking'];
        $custom_fileds['MachineRepairManagement'] =  ['Machine'];
        $custom_fileds['BeverageManagement'] =  ['Raw Material', 'Manufacturing'];
        $custom_fileds['SalesAgent'] =  ['Sales Agent'];
        

        foreach ($custom_fileds as $module => $custom_filed) {
            foreach ($custom_filed as $sm) {
                $check = \Modules\CustomField\Entities\CustomFieldsModuleList::where('module', $module)->where('sub_module', $sm)->first();
                if (!$check) {
                    $new = new \Modules\CustomField\Entities\CustomFieldsModuleList();
                    $new->module = $module;
                    $new->sub_module = $sm;
                    $new->save();
                }
            }
        }
    }
}
