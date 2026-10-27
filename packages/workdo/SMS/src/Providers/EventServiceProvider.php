<?php

namespace Workdo\SMS\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as Provider;
use App\Events\CompanySettingEvent;
use App\Events\CompanySettingMenuEvent;
use Workdo\SMS\Listeners\CompanySettingListener;
use Workdo\SMS\Listeners\CompanySettingMenuListener;
use Workdo\Rotas\Events\AddDayoff;
use Workdo\Appointment\Events\AppointmentStatus;
use Workdo\AgricultureManagement\Events\AssignActivityCultivation;
use App\Events\BankTransferPaymentStatus;
use Workdo\Holidayz\Events\ChangeHotelTheme;
use Workdo\ToDo\Events\CompleteToDo;
use Workdo\Recruitment\Events\ConvertToEmployee;
use Workdo\FixEquipment\Events\CreateAccessories;
use Workdo\School\Events\CreateAdmission;
use Workdo\AgricultureManagement\Events\CreateAgricultureActivities;
use Workdo\AgricultureManagement\Events\CreateAgricultureCrop;
use Workdo\AgricultureManagement\Events\CreateAgricultureCultivation;
use Workdo\AgricultureManagement\Events\CreateAgricultureCycles;
use Workdo\AgricultureManagement\Events\CreateAgricultureOffices;
use Workdo\AgricultureManagement\Events\CreateAgricultureProcess;
use Workdo\AgricultureManagement\Events\CreateAgricultureSeason;
use Workdo\AgricultureManagement\Events\CreateAgricultureServices;
use Workdo\AgricultureManagement\Events\CreateAgriculturefleet;
use Workdo\Hrm\Events\CreateAnnouncement;
use Workdo\VCard\Events\CreateAppointment;
use Workdo\Appointment\Events\CreateAppointments;
use Workdo\Performance\Events\CreateAppraisal;
use Workdo\Internalknowledge\Events\CreateArticle;
use Workdo\Assets\Events\CreateAssetDefective;
use Workdo\Assets\Events\CreateAssetDistribution;
use Workdo\Assets\Events\CreateAssetExtra;
use Workdo\FixEquipment\Events\CreateAsset;
use Workdo\Assets\Events\CreateAssets;
use Workdo\FixEquipment\Events\CreateAudit;
use Workdo\Rotas\Events\CreateAvailability;
use Workdo\Hrm\Events\CreateAward;
use Workdo\Account\Events\CreateBill;
use Workdo\LMS\Events\CreateBlog;
use Workdo\Internalknowledge\Events\CreateBook;
use Workdo\Holidayz\Events\CreateBookingCoupon;
use Workdo\Fleet\Events\CreateBooking;
use Workdo\Taskly\Events\CreateBug;
use Workdo\VCard\Events\CreateBusiness;
use Workdo\InnovationCenter\Events\CreateCategory;
use Workdo\InnovationCenter\Events\CreateChallenge;
use Workdo\ChildcareManagement\Events\CreateChild;
use Workdo\CleaningManagement\Events\CreateCleaningBooking;
use Workdo\CleaningManagement\Events\CreateCleaningInvoice;
use Workdo\CleaningManagement\Events\CreateCleaningTeam;
use Workdo\CMMS\Events\CreateCmmspos;
use Workdo\Commission\Events\CreateCommissionPlan;
use Workdo\Commission\Events\CreateCommissionReceipt;
use Workdo\Hrm\Events\CreateCompanyPolicy;
use Workdo\CMMS\Events\CreateComponent;
use Workdo\ConsignmentManagement\Events\CreateConsignment;
use Workdo\ConsignmentManagement\Events\CreateProduct;
use Workdo\FixEquipment\Events\CreateConsumables;
use Workdo\VCard\Events\CreateContact;
use Workdo\Contract\Events\CreateContract;
use Workdo\LMS\Events\CreateCourse;
use Workdo\LMS\Events\CreateCourseOrder;
use Workdo\InnovationCenter\Events\CreateCreativity;
use Workdo\LMS\Events\CreateCustomPage;
use Workdo\Account\Events\CreateCustomer;
use Workdo\Lead\Events\CreateDeal;
use Workdo\HospitalManagement\Events\CreateDoctor;
use Workdo\Documents\Events\CreateDocuments;
use Workdo\Hrm\Events\CreateEvent;
use Workdo\Goal\Events\CreateFinacialGoal;
use Workdo\FixEquipment\Events\CreateComponent as CreateFixEquipmentComponent;
use Workdo\Fleet\Events\CreateFleetPayment;
use Workdo\FormBuilder\Events\CreateFormField;
use Workdo\FormBuilder\Events\CreateForm;
use Workdo\Fleet\Events\CreateFuel;
use Workdo\Performance\Events\CreateGoalTracking;
use Workdo\Hrm\Events\CreateHolidays;
use Workdo\HospitalManagement\Events\CreateHospitalAppointment;
use Workdo\HospitalManagement\Events\CreateHospitalMedicine;
use Workdo\Holidayz\Events\CreateHotelCustomer;
use Workdo\Holidayz\Events\CreateHotelService;
use Workdo\Performance\Events\CreateIndicator;
use Workdo\ChildcareManagement\Events\CreateInquiry;
use Workdo\VehicleInspectionManagement\Events\CreateInspectionList;
use Workdo\VehicleInspectionManagement\Events\CreateInspectionRequest;
use Workdo\VehicleInspectionManagement\Events\CreateInspectionVehicle;
use Workdo\Fleet\Events\CreateInsurance;
use Workdo\Recruitment\Events\CreateInterviewSchedule;
use App\Events\CreateInvoice;
use Workdo\Recruitment\Events\CreateJobApplication;
use Workdo\Recruitment\Events\CreateJob;
use Workdo\DoubleEntry\Events\CreateJournalAccount;
use Workdo\Lead\Events\CreateLead;
use Workdo\FixEquipment\Events\CreateLicence;
use Workdo\CMMS\Events\CreateLocation ;
use Workdo\MachineRepairManagement\Events\CreateMachine;
use Workdo\FixEquipment\Events\CreateMaintenance;
use Workdo\Fleet\Events\CreateMaintenances;
use Workdo\Sales\Events\CreateMeeting;
use Workdo\Taskly\Events\CreateMilestone;
use Workdo\Hrm\Events\CreateMonthlyPayslip;
use Workdo\Newspaper\Events\CreateNewspaperAds;
use Workdo\Newspaper\Events\CreateNewspaperAgent;
use Workdo\Newspaper\Events\CreateNewspaperDistributions;
use Workdo\Newspaper\Events\CreateNewspaperJournalistInfo;
use Workdo\Newspaper\Events\CreateNewspaperJournalist;
use Workdo\Newspaper\Events\CreateNewspaper;
use Workdo\Notes\Events\CreateNotes;
use Workdo\ChildcareManagement\Events\CreateParent;
use Workdo\CMMS\Events\CreatePart;
use Workdo\HospitalManagement\Events\CreatePatient;
use Workdo\Account\Events\CreatePayment;
use Workdo\TourTravelManagement\Events\CreatePersonDetail;
use Workdo\CMMS\Events\CreatePms;
use Workdo\Portfolio\Events\CreatePortfolio;
use Workdo\ProductService\Events\CreateProduct as CreateProductService;
use Workdo\Taskly\Events\CreateProject;
use Workdo\PropertyManagement\Events\CreatePropertyInvoice;
use Workdo\PropertyManagement\Events\CreateProperty;
use Workdo\PropertyManagement\Events\CreatePropertyUnit;
use App\Events\CreateProposal;
use App\Events\CreatePurchase;
use Workdo\ConsignmentManagement\Events\CreatePurchaseOrder;
use Workdo\Sales\Events\CreateQuote;
use Workdo\Feedback\Events\CreateRating;
use Workdo\LMS\Events\CreateRatting;
use Workdo\MachineRepairManagement\Events\CreateRepairRequest;
use Workdo\Retainer\Events\CreateRetainer;
use Workdo\Account\Events\CreateRevenue;
use Workdo\Holidayz\Events\CreateRoomBooking;
use Workdo\Holidayz\Events\CreateRoomFacility;
use Workdo\Holidayz\Events\CreateRoom;
use Workdo\Rotas\Events\CreateRota;
use Workdo\ConsignmentManagement\Events\CreateSaleOrder;
use Workdo\Sales\Events\CreateSalesInvoice;
use Workdo\Sales\Events\CreateSalesOrder;
use Workdo\School\Events\CreateSchoolEmployee;
use Workdo\School\Events\CreateSchoolHomework;
use Workdo\School\Events\CreateSchoolParent;
use Workdo\School\Events\CreateSchoolStudent;
use Workdo\TourTravelManagement\Events\CreateSeason;
use Workdo\Spreadsheet\Events\CreateSpreadsheet;
use Workdo\School\Events\CreateSubject;
use Workdo\CMMS\Events\CreateSupplier;
use Workdo\Taskly\Events\CreateTaskComment;
use Workdo\Taskly\Events\CreateTask;
use Workdo\Feedback\Events\CreateTemplate;
use Workdo\PropertyManagement\Events\CreateTenant;
use Workdo\SupportTicket\Events\CreateTicket;
use Workdo\TimeTracker\Events\CreateTimeTracker;
use Workdo\Timesheet\Events\CreateTimesheet;
use Workdo\School\Events\CreateTimetable;
use Workdo\ToDo\Events\CreateToDo;
use Workdo\TourTravelManagement\Events\CreateTourBooking;
use Workdo\TourTravelManagement\Events\CreateTourBookingPayment;
use Workdo\TourTravelManagement\Events\CreateTourDetail;
use Workdo\TourTravelManagement\Events\CreateTour;
use Workdo\Training\Events\CreateTrainer;
use Workdo\TourTravelManagement\Events\CreateTransportType;
use Workdo\Hrm\Events\CreateTrip;
use App\Events\CreateUser;
use Workdo\Fleet\Events\CreateVehicle;
use Workdo\Account\Events\CreateVendor;
use Workdo\VisitorManagement\Events\CreateVisitReason;
use Workdo\VisitorManagement\Events\CreateVisitor;
use Workdo\WordpressWoocommerce\Events\CreateWoocommerceProduct;
use Workdo\Workflow\Events\CreateWorkflow;
use Workdo\CMMS\Events\CreateWorkorder;
use Workdo\CMMS\Events\CreateWorkrequest;
use Workdo\ZoomMeeting\Events\CreateZoommeeting;
use Workdo\Lead\Events\DealMoved;
use Workdo\Rotas\Events\DestroyRota;
use Workdo\FixEquipment\Events\CreateLocation as CreateFixEquipmentLocation;
use Workdo\FormBuilder\Events\FormBuilderConvertTo;
use Workdo\LaundryManagement\Events\LaundryRequestCreate;
use Workdo\Lead\Events\LeadConvertDeal;
use Workdo\Lead\Events\LeadMoved;
use Workdo\Hrm\Events\LeaveStatus;
use Workdo\LaundryManagement\Events\LundaryRequestInvoiceCreate;
use Workdo\SupportTicket\Events\ReplyTicket;
use Workdo\Rotas\Events\RotaleaveStatus;
use Workdo\SalesAgent\Events\SalesAgentCreate;
use Workdo\SalesAgent\Events\SalesAgentOrderCreate;
use Workdo\SalesAgent\Events\SalesAgentOrderStatusUpdated;
use Workdo\SalesAgent\Events\SalesAgentProgramCreate;
use Workdo\SalesAgent\Events\SalesAgentRequestAccept;
use Workdo\SalesAgent\Events\SalesAgentRequestReject;
use Workdo\SalesAgent\Events\SalesAgentRequestSent;
use Workdo\VCard\Events\BusinessStatus;
use Workdo\Documents\Events\StatusChangeDocument;
use App\Events\SuperAdminMenuEvent;
use Workdo\Portfolio\Events\UpdatePortfolioStatus;
use Workdo\Rotas\Events\UpdateRota;
use Workdo\Taskly\Events\UpdateTaskStage;
use Workdo\WasteManagement\Events\WasteCollectionRequestCreate;
use Workdo\WasteManagement\Events\WasteInspectionStatusUpdate;
use Workdo\WasteManagement\Events\WastecollectionConvertedToTrip;
use Workdo\SMS\Listeners\AddDayoffLis;
use Workdo\SMS\Listeners\AppointmentStatusLis;
use Workdo\SMS\Listeners\AssignActivityCultivationLis;
use Workdo\SMS\Listeners\BankTransferPaymentStatusLis;
use Workdo\SMS\Listeners\ChangeHotelThemeLis;
use Workdo\SMS\Listeners\CompleteToDoLis;
use Workdo\SMS\Listeners\ConvertToEmployeeLis;
use Workdo\SMS\Listeners\CreateAccessoriesLis;
use Workdo\SMS\Listeners\CreateAdmissionLis;
use Workdo\SMS\Listeners\CreateAgricultureActivitiesLis;
use Workdo\SMS\Listeners\CreateAgricultureCropLis;
use Workdo\SMS\Listeners\CreateAgricultureCultivationLis;
use Workdo\SMS\Listeners\CreateAgricultureCyclesLis;
use Workdo\SMS\Listeners\CreateAgricultureOfficesLis;
use Workdo\SMS\Listeners\CreateAgricultureProcessLis;
use Workdo\SMS\Listeners\CreateAgricultureSeasonLis;
use Workdo\SMS\Listeners\CreateAgricultureServicesLis;
use Workdo\SMS\Listeners\CreateAgriculturefleetLis;
use Workdo\SMS\Listeners\CreateAnnouncementLis;
use Workdo\SMS\Listeners\CreateAppointmentLis;
use Workdo\SMS\Listeners\CreateAppointmentsLis;
use Workdo\SMS\Listeners\CreateAppraisalLis;
use Workdo\SMS\Listeners\CreateArticleLis;
use Workdo\SMS\Listeners\CreateAssetDefectiveLis;
use Workdo\SMS\Listeners\CreateAssetDistributionLis;
use Workdo\SMS\Listeners\CreateAssetExtraLis;
use Workdo\SMS\Listeners\CreateAssetLis;
use Workdo\SMS\Listeners\CreateAssetsLis;
use Workdo\SMS\Listeners\CreateAuditLis;
use Workdo\SMS\Listeners\CreateAvailabilityLis;
use Workdo\SMS\Listeners\CreateAwardLis;
use Workdo\SMS\Listeners\CreateBillLis;
use Workdo\SMS\Listeners\CreateBlogLis;
use Workdo\SMS\Listeners\CreateBookLis;
use Workdo\SMS\Listeners\CreateBookingCouponLis;
use Workdo\SMS\Listeners\CreateBookingLis;
use Workdo\SMS\Listeners\CreateBugLis;
use Workdo\SMS\Listeners\CreateBusinessLis;
use Workdo\SMS\Listeners\CreateCategoryLis;
use Workdo\SMS\Listeners\CreateChallengeLis;
use Workdo\SMS\Listeners\CreateChildLis;
use Workdo\SMS\Listeners\CreateCleaningBookingLis;
use Workdo\SMS\Listeners\CreateCleaningInvoiceLis;
use Workdo\SMS\Listeners\CreateCleaningTeamLis;
use Workdo\SMS\Listeners\CreateCmmsposLis;
use Workdo\SMS\Listeners\CreateCommissionPlanLis;
use Workdo\SMS\Listeners\CreateCommissionReceiptLis;
use Workdo\SMS\Listeners\CreateCompanyPolicyLis;
use Workdo\SMS\Listeners\CreateComponentLis;
use Workdo\SMS\Listeners\CreateConsignmentLis;
use Workdo\SMS\Listeners\CreateConsignmentProductLis;
use Workdo\SMS\Listeners\CreateConsumablesLis;
use Workdo\SMS\Listeners\CreateContactLis;
use Workdo\SMS\Listeners\CreateContractLis;
use Workdo\SMS\Listeners\CreateCourseLis;
use Workdo\SMS\Listeners\CreateCourseOrderLis;
use Workdo\SMS\Listeners\CreateCreativityLis;
use Workdo\SMS\Listeners\CreateCustomPageLis;
use Workdo\SMS\Listeners\CreateCustomerLis;
use Workdo\SMS\Listeners\CreateDealLis;
use Workdo\SMS\Listeners\CreateDoctorLis;
use Workdo\SMS\Listeners\CreateDocumentsLis;
use Workdo\SMS\Listeners\CreateEventLis;
use Workdo\SMS\Listeners\CreateFinacialGoalLis;
use Workdo\SMS\Listeners\CreateFixEquipComponentLis;
use Workdo\SMS\Listeners\CreateFleetPaymentLis;
use Workdo\SMS\Listeners\CreateFormFieldLis;
use Workdo\SMS\Listeners\CreateFormLis;
use Workdo\SMS\Listeners\CreateFuelLis;
use Workdo\SMS\Listeners\CreateGoalTrackingLis;
use Workdo\SMS\Listeners\CreateHolidayLis;
use Workdo\SMS\Listeners\CreateHospitalAppointmentLis;
use Workdo\SMS\Listeners\CreateHospitalMedicineLis;
use Workdo\SMS\Listeners\CreateHotelCustomerLis;
use Workdo\SMS\Listeners\CreateHotelServiceLis;
use Workdo\SMS\Listeners\CreateIndicatorLis;
use Workdo\SMS\Listeners\CreateInquiryLis;
use Workdo\SMS\Listeners\CreateInspectionListLis;
use Workdo\SMS\Listeners\CreateInspectionRequestLis;
use Workdo\SMS\Listeners\CreateInspectionVehicleLis;
use Workdo\SMS\Listeners\CreateInsuranceLis;
use Workdo\SMS\Listeners\CreateInterviewScheduleLis;
use Workdo\SMS\Listeners\CreateInvoiceLis;
use Workdo\SMS\Listeners\CreateJobApplicationLis;
use Workdo\SMS\Listeners\CreateJobLis;
use Workdo\SMS\Listeners\CreateJournalAccountLis;
use Workdo\SMS\Listeners\CreateLeadLis;
use Workdo\SMS\Listeners\CreateLicenceLis;
use Workdo\SMS\Listeners\CreateLocationLis;
use Workdo\SMS\Listeners\CreateMachineLis;
use Workdo\SMS\Listeners\CreateMaintenanceLis;
use Workdo\SMS\Listeners\CreateMaintenancesLis;
use Workdo\SMS\Listeners\CreateMeetingLis;
use Workdo\SMS\Listeners\CreateMilestoneLis;
use Workdo\SMS\Listeners\CreateMonthlyPayslipLis;
use Workdo\SMS\Listeners\CreateNewspaperAdsLis;
use Workdo\SMS\Listeners\CreateNewspaperAgentLis;
use Workdo\SMS\Listeners\CreateNewspaperDistributionsLis;
use Workdo\SMS\Listeners\CreateNewspaperJournalistInfoLis;
use Workdo\SMS\Listeners\CreateNewspaperJournalistLis;
use Workdo\SMS\Listeners\CreateNewspaperLis;
use Workdo\SMS\Listeners\CreateNotesLis;
use Workdo\SMS\Listeners\CreateParentLis;
use Workdo\SMS\Listeners\CreatePartLis;
use Workdo\SMS\Listeners\CreatePatientLis;
use Workdo\SMS\Listeners\CreatePaymentLis;
use Workdo\SMS\Listeners\CreatePersonDetailLis;
use Workdo\SMS\Listeners\CreatePmsLis;
use Workdo\SMS\Listeners\CreatePortfolioLis;
use Workdo\SMS\Listeners\CreateProductLis;
use Workdo\SMS\Listeners\CreateProjectLis;
use Workdo\SMS\Listeners\CreatePropertyInvoiceLis;
use Workdo\SMS\Listeners\CreatePropertyLis;
use Workdo\SMS\Listeners\CreatePropertyUnitLis;
use Workdo\SMS\Listeners\CreateProposalLis;
use Workdo\SMS\Listeners\CreatePurchaseLis;
use Workdo\SMS\Listeners\CreatePurchaseOrderLis;
use Workdo\SMS\Listeners\CreateQuoteLis;
use Workdo\SMS\Listeners\CreateRatingLis;
use Workdo\SMS\Listeners\CreateRattingLis;
use Workdo\SMS\Listeners\CreateRepairRequestLis;
use Workdo\SMS\Listeners\CreateRetainerLis;
use Workdo\SMS\Listeners\CreateRevenueLis;
use Workdo\SMS\Listeners\CreateRoomBookingLis;
use Workdo\SMS\Listeners\CreateRoomFacilityLis;
use Workdo\SMS\Listeners\CreateRoomLis;
use Workdo\SMS\Listeners\CreateRotaLis;
use Workdo\SMS\Listeners\CreateSaleOrderLis;
use Workdo\SMS\Listeners\CreateSalesInvoiceLis;
use Workdo\SMS\Listeners\CreateSalesOrderLis;
use Workdo\SMS\Listeners\CreateSchoolEmployeeLis;
use Workdo\SMS\Listeners\CreateSchoolHomeworkLis;
use Workdo\SMS\Listeners\CreateSchoolParentLis;
use Workdo\SMS\Listeners\CreateSchoolStudentLis;
use Workdo\SMS\Listeners\CreateSeasonLis;
use Workdo\SMS\Listeners\CreateSpreadsheetLis;
use Workdo\SMS\Listeners\CreateSubjectLis;
use Workdo\SMS\Listeners\CreateSupplierLis;
use Workdo\SMS\Listeners\CreateTaskCommentLis;
use Workdo\SMS\Listeners\CreateTaskLis;
use Workdo\SMS\Listeners\CreateTemplateLis;
use Workdo\SMS\Listeners\CreateTenantLis;
use Workdo\SMS\Listeners\CreateTicketLis;
use Workdo\SMS\Listeners\CreateTimeTrackerLis;
use Workdo\SMS\Listeners\CreateTimesheetLis;
use Workdo\SMS\Listeners\CreateTimetableLis;
use Workdo\SMS\Listeners\CreateToDoLis;
use Workdo\SMS\Listeners\CreateTourBookingLis;
use Workdo\SMS\Listeners\CreateTourBookingPaymentLis;
use Workdo\SMS\Listeners\CreateTourDetailLis;
use Workdo\SMS\Listeners\CreateTourLis;
use Workdo\SMS\Listeners\CreateTrainerLis;
use Workdo\SMS\Listeners\CreateTransportTypeLis;
use Workdo\SMS\Listeners\CreateTripLis;
use Workdo\SMS\Listeners\CreateUserLis;
use Workdo\SMS\Listeners\CreateVehicleLis;
use Workdo\SMS\Listeners\CreateVendorLis;
use Workdo\SMS\Listeners\CreateVisitReasonLis;
use Workdo\SMS\Listeners\CreateVisitorLis;
use Workdo\SMS\Listeners\CreateWoocommerceProductLis;
use Workdo\SMS\Listeners\CreateWorkflowLis;
use Workdo\SMS\Listeners\CreateWorkorderLis;
use Workdo\SMS\Listeners\CreateWorkrequestLis;
use Workdo\SMS\Listeners\CreateZoommeetingLis;
use Workdo\SMS\Listeners\DealMovedLis;
use Workdo\SMS\Listeners\DestroyRotaLis;
use Workdo\SMS\Listeners\EquipmentLocationLis;
use Workdo\SMS\Listeners\FormBuilderConvertToLis;
use Workdo\SMS\Listeners\LaundryRequestCreateLis;
use Workdo\SMS\Listeners\LeadConvertDealLis;
use Workdo\SMS\Listeners\LeadMovedLis;
use Workdo\SMS\Listeners\LeaveStatusLis;
use Workdo\SMS\Listeners\LundaryRequestInvoiceCreateLis;
use Workdo\SMS\Listeners\ReplyTicketLis;
use Workdo\SMS\Listeners\RotaleaveStatusLis;
use Workdo\SMS\Listeners\SalesAgentCreateLis;
use Workdo\SMS\Listeners\SalesAgentOrderCreateLis;
use Workdo\SMS\Listeners\SalesAgentOrderStatusUpdatedLis;
use Workdo\SMS\Listeners\SalesAgentProgramCreateLis;
use Workdo\SMS\Listeners\SalesAgentRequestAcceptLis;
use Workdo\SMS\Listeners\SalesAgentRequestRejectLis;
use Workdo\SMS\Listeners\SalesAgentRequestSentLis;
use Workdo\SMS\Listeners\StatusChangeBusinessLis;
use Workdo\SMS\Listeners\StatusChangeDocumentLis;
use Workdo\SMS\Listeners\UpdatePortfolioStatusLis;
use Workdo\SMS\Listeners\UpdateRotaLis;
use Workdo\SMS\Listeners\UpdateTaskStageLis;
use Workdo\SMS\Listeners\WasteCollectionRequestCreateLis;
use Workdo\SMS\Listeners\WasteInspectionStatusUpdateLis;
use Workdo\SMS\Listeners\WastecollectionConvertedToTripLis;

class EventServiceProvider extends Provider
{
    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    protected $listen = [
        AddDayoff::class => [
            AddDayoffLis::class
            ],
            AppointmentStatus::class => [
            AppointmentStatusLis::class
            ],
            AssignActivityCultivation::class => [
            AssignActivityCultivationLis::class
            ],
            BankTransferPaymentStatus::class => [
            BankTransferPaymentStatusLis::class
            ],
            ChangeHotelTheme::class => [
            ChangeHotelThemeLis::class
            ],
            CompanySettingEvent::class => [
            CompanySettingListener::class
            ],
            CompanySettingMenuEvent::class => [
            CompanySettingMenuListener::class
            ],
            CompleteToDo::class => [
            CompleteToDoLis::class
            ],
            ConvertToEmployee::class => [
            ConvertToEmployeeLis::class
            ],
            CreateAccessories::class => [
            CreateAccessoriesLis::class
            ],
            CreateAdmission::class => [
            CreateAdmissionLis::class
            ],
            CreateAgricultureActivities::class => [
            CreateAgricultureActivitiesLis::class
            ],
            CreateAgricultureCrop::class => [
            CreateAgricultureCropLis::class
            ],
            CreateAgricultureCultivation::class => [
            CreateAgricultureCultivationLis::class
            ],
            CreateAgricultureCycles::class => [
            CreateAgricultureCyclesLis::class
            ],
            CreateAgricultureOffices::class => [
            CreateAgricultureOfficesLis::class
            ],
            CreateAgricultureProcess::class => [
            CreateAgricultureProcessLis::class
            ],
            CreateAgricultureSeason::class => [
            CreateAgricultureSeasonLis::class
            ],
            CreateAgricultureServices::class => [
            CreateAgricultureServicesLis::class
            ],
            CreateAgriculturefleet::class => [
            CreateAgriculturefleetLis::class
            ],
            CreateAnnouncement::class => [
            CreateAnnouncementLis::class
            ],
            CreateAppointment::class => [
            CreateAppointmentLis::class
            ],
            CreateAppointments::class => [
            CreateAppointmentsLis::class
            ],
            CreateAppraisal::class => [
            CreateAppraisalLis::class
            ],
            CreateArticle::class => [
            CreateArticleLis::class
            ],
            CreateAssetDefective::class => [
            CreateAssetDefectiveLis::class
            ],
            CreateAssetDistribution::class => [
            CreateAssetDistributionLis::class
            ],
            CreateAssetExtra::class => [
            CreateAssetExtraLis::class
            ],
            CreateAsset::class => [
            CreateAssetLis::class
            ],
            CreateAssets::class => [
            CreateAssetsLis::class
            ],
            CreateAudit::class => [
            CreateAuditLis::class
            ],
            CreateAvailability::class => [
            CreateAvailabilityLis::class
            ],
            CreateAward::class => [
            CreateAwardLis::class
            ],
            CreateBill::class => [
            CreateBillLis::class
            ],
            CreateBlog::class => [
            CreateBlogLis::class
            ],
            CreateBook::class => [
            CreateBookLis::class
            ],
            CreateBookingCoupon::class => [
            CreateBookingCouponLis::class
            ],
            CreateBooking::class => [
            CreateBookingLis::class
            ],
            CreateBug::class => [
            CreateBugLis::class
            ],
            CreateBusiness::class => [
            CreateBusinessLis::class
            ],
            CreateCategory::class => [
            CreateCategoryLis::class
            ],
            CreateChallenge::class => [
            CreateChallengeLis::class
            ],
            CreateChild::class => [
            CreateChildLis::class
            ],
            CreateCleaningBooking::class => [
            CreateCleaningBookingLis::class
            ],
            CreateCleaningInvoice::class => [
            CreateCleaningInvoiceLis::class
            ],
            CreateCleaningTeam::class => [
            CreateCleaningTeamLis::class
            ],
            CreateCmmspos::class => [
            CreateCmmsposLis::class
            ],
            CreateCommissionPlan::class => [
            CreateCommissionPlanLis::class
            ],
            CreateCommissionReceipt::class => [
            CreateCommissionReceiptLis::class
            ],
            CreateCompanyPolicy::class => [
            CreateCompanyPolicyLis::class
            ],
            CreateComponent::class => [
            CreateComponentLis::class
            ],
            CreateConsignment::class => [
            CreateConsignmentLis::class
            ],
            CreateProduct::class => [
            CreateConsignmentProductLis::class
            ],
            CreateConsumables::class => [
            CreateConsumablesLis::class
            ],
            CreateContact::class => [
            CreateContactLis::class
            ],
            CreateContract::class => [
            CreateContractLis::class
            ],
            CreateCourse::class => [
            CreateCourseLis::class
            ],
            CreateCourseOrder::class => [
            CreateCourseOrderLis::class
            ],
            CreateCreativity::class => [
            CreateCreativityLis::class
            ],
            CreateCustomPage::class => [
            CreateCustomPageLis::class
            ],
            CreateCustomer::class => [
            CreateCustomerLis::class
            ],
            CreateDeal::class => [
            CreateDealLis::class
            ],
            CreateDoctor::class => [
            CreateDoctorLis::class
            ],
            CreateDocuments::class => [
            CreateDocumentsLis::class
            ],
            CreateEvent::class => [
            CreateEventLis::class
            ],
            CreateFinacialGoal::class => [
            CreateFinacialGoalLis::class
            ],
            CreateFixEquipmentComponent::class => [
            CreateFixEquipComponentLis::class
            ],
            CreateFleetPayment::class => [
            CreateFleetPaymentLis::class
            ],
            CreateFormField::class => [
            CreateFormFieldLis::class
            ],
            CreateForm::class => [
            CreateFormLis::class
            ],
            CreateFuel::class => [
            CreateFuelLis::class
            ],
            CreateGoalTracking::class => [
            CreateGoalTrackingLis::class
            ],
            CreateHolidays::class => [
            CreateHolidayLis::class
            ],
            CreateHospitalAppointment::class => [
            CreateHospitalAppointmentLis::class
            ],
            CreateHospitalMedicine::class => [
            CreateHospitalMedicineLis::class
            ],
            CreateHotelCustomer::class => [
            CreateHotelCustomerLis::class
            ],
            CreateHotelService::class => [
            CreateHotelServiceLis::class
            ],
            CreateIndicator::class => [
            CreateIndicatorLis::class
            ],
            CreateInquiry::class => [
            CreateInquiryLis::class
            ],
            CreateInspectionList::class => [
            CreateInspectionListLis::class
            ],
            CreateInspectionRequest::class => [
            CreateInspectionRequestLis::class
            ],
            CreateInspectionVehicle::class => [
            CreateInspectionVehicleLis::class
            ],
            CreateInsurance::class => [
            CreateInsuranceLis::class
            ],
            CreateInterviewSchedule::class => [
            CreateInterviewScheduleLis::class
            ],
            CreateInvoice::class => [
            CreateInvoiceLis::class
            ],
            CreateJobApplication::class => [
            CreateJobApplicationLis::class
            ],
            CreateJob::class => [
            CreateJobLis::class
            ],
            CreateJournalAccount::class => [
            CreateJournalAccountLis::class
            ],
            CreateLead::class => [
            CreateLeadLis::class
            ],
            CreateLicence::class => [
            CreateLicenceLis::class
            ],
            CreateLocation::class => [
            CreateLocationLis::class
            ],
            CreateMachine::class => [
            CreateMachineLis::class
            ],
            CreateMaintenance::class => [
            CreateMaintenanceLis::class
            ],
            CreateMaintenances::class => [
            CreateMaintenancesLis::class
            ],
            CreateMeeting::class => [
            CreateMeetingLis::class
            ],
            CreateMilestone::class => [
            CreateMilestoneLis::class
            ],
            CreateMonthlyPayslip::class => [
            CreateMonthlyPayslipLis::class
            ],
            CreateNewspaperAds::class => [
            CreateNewspaperAdsLis::class
            ],
            CreateNewspaperAgent::class => [
            CreateNewspaperAgentLis::class
            ],
            CreateNewspaperDistributions::class => [
            CreateNewspaperDistributionsLis::class
            ],
            CreateNewspaperJournalistInfo::class => [
            CreateNewspaperJournalistInfoLis::class
            ],
            CreateNewspaperJournalist::class => [
            CreateNewspaperJournalistLis::class
            ],
            CreateNewspaper::class => [
            CreateNewspaperLis::class
            ],
            CreateNotes::class => [
            CreateNotesLis::class
            ],
            CreateParent::class => [
            CreateParentLis::class
            ],
            CreatePart::class => [
            CreatePartLis::class
            ],
            CreatePatient::class => [
            CreatePatientLis::class
            ],
            CreatePayment::class => [
            CreatePaymentLis::class
            ],
            CreatePersonDetail::class => [
            CreatePersonDetailLis::class
            ],
            CreatePms::class => [
            CreatePmsLis::class
            ],
            CreatePortfolio::class => [
            CreatePortfolioLis::class
            ],
            CreateProductService::class => [
            CreateProductLis::class
            ],
            CreateProject::class => [
            CreateProjectLis::class
            ],
            CreatePropertyInvoice::class => [
            CreatePropertyInvoiceLis::class
            ],
            CreateProperty::class => [
            CreatePropertyLis::class
            ],
            CreatePropertyUnit::class => [
            CreatePropertyUnitLis::class
            ],
            CreateProposal::class => [
            CreateProposalLis::class
            ],
            CreatePurchase::class => [
            CreatePurchaseLis::class
            ],
            CreatePurchaseOrder::class => [
            CreatePurchaseOrderLis::class
            ],
            CreateQuote::class => [
            CreateQuoteLis::class
            ],
            CreateRating::class => [
            CreateRatingLis::class
            ],
            CreateRatting::class => [
            CreateRattingLis::class
            ],
            CreateRepairRequest::class => [
            CreateRepairRequestLis::class
            ],
            CreateRetainer::class => [
            CreateRetainerLis::class
            ],
            CreateRevenue::class => [
            CreateRevenueLis::class
            ],
            CreateRoomBooking::class => [
            CreateRoomBookingLis::class
            ],
            CreateRoomFacility::class => [
            CreateRoomFacilityLis::class
            ],
            CreateRoom::class => [
            CreateRoomLis::class
            ],
            CreateRota::class => [
            CreateRotaLis::class
            ],
            CreateSaleOrder::class => [
            CreateSaleOrderLis::class
            ],
            CreateSalesInvoice::class => [
            CreateSalesInvoiceLis::class
            ],
            CreateSalesOrder::class => [
            CreateSalesOrderLis::class
            ],
            CreateSchoolEmployee::class => [
            CreateSchoolEmployeeLis::class
            ],
            CreateSchoolHomework::class => [
            CreateSchoolHomeworkLis::class
            ],
            CreateSchoolParent::class => [
            CreateSchoolParentLis::class
            ],
            CreateSchoolStudent::class => [
            CreateSchoolStudentLis::class
            ],
            CreateSeason::class => [
            CreateSeasonLis::class
            ],
            CreateSpreadsheet::class => [
            CreateSpreadsheetLis::class
            ],
            CreateSubject::class => [
            CreateSubjectLis::class
            ],
            CreateSupplier::class => [
            CreateSupplierLis::class
            ],
            CreateTaskComment::class => [
            CreateTaskCommentLis::class
            ],
            CreateTask::class => [
            CreateTaskLis::class
            ],
            CreateTemplate::class => [
            CreateTemplateLis::class
            ],
            CreateTenant::class => [
            CreateTenantLis::class
            ],
            CreateTicket::class => [
            CreateTicketLis::class
            ],
            CreateTimeTracker::class => [
            CreateTimeTrackerLis::class
            ],
            CreateTimesheet::class => [
            CreateTimesheetLis::class
            ],
            CreateTimetable::class => [
            CreateTimetableLis::class
            ],
            CreateToDo::class => [
            CreateToDoLis::class
            ],
            CreateTourBooking::class => [
            CreateTourBookingLis::class
            ],
            CreateTourBookingPayment::class => [
            CreateTourBookingPaymentLis::class
            ],
            CreateTourDetail::class => [
            CreateTourDetailLis::class
            ],
            CreateTour::class => [
            CreateTourLis::class
            ],
            CreateTrainer::class => [
            CreateTrainerLis::class
            ],
            CreateTransportType::class => [
            CreateTransportTypeLis::class
            ],
            CreateTrip::class => [
            CreateTripLis::class
            ],
            CreateUser::class => [
            CreateUserLis::class
            ],
            CreateVehicle::class => [
            CreateVehicleLis::class
            ],
            CreateVendor::class => [
            CreateVendorLis::class
            ],
            CreateVisitReason::class => [
            CreateVisitReasonLis::class
            ],
            CreateVisitor::class => [
            CreateVisitorLis::class
            ],
            CreateWoocommerceProduct::class => [
            CreateWoocommerceProductLis::class
            ],
            CreateWorkflow::class => [
            CreateWorkflowLis::class
            ],
            CreateWorkorder::class => [
            CreateWorkorderLis::class
            ],
            CreateWorkrequest::class => [
            CreateWorkrequestLis::class
            ],
            CreateZoommeeting::class => [
            CreateZoommeetingLis::class
            ],
            DealMoved::class => [
            DealMovedLis::class
            ],
            DestroyRota::class => [
            DestroyRotaLis::class
            ],
            CreateFixEquipmentLocation::class => [
            EquipmentLocationLis::class
            ],
            FormBuilderConvertTo::class => [
            FormBuilderConvertToLis::class
            ],
            LaundryRequestCreate::class => [
            LaundryRequestCreateLis::class
            ],
            LeadConvertDeal::class => [
            LeadConvertDealLis::class
            ],
            LeadMoved::class => [
            LeadMovedLis::class
            ],
            LeaveStatus::class => [
            LeaveStatusLis::class
            ],
            LundaryRequestInvoiceCreate::class => [
            LundaryRequestInvoiceCreateLis::class
            ],
            ReplyTicket::class => [
            ReplyTicketLis::class
            ],
            RotaleaveStatus::class => [
            RotaleaveStatusLis::class
            ],
            SalesAgentCreate::class => [
            SalesAgentCreateLis::class
            ],
            SalesAgentOrderCreate::class => [
            SalesAgentOrderCreateLis::class
            ],
            SalesAgentOrderStatusUpdated::class => [
            SalesAgentOrderStatusUpdatedLis::class
            ],
            SalesAgentProgramCreate::class => [
            SalesAgentProgramCreateLis::class
            ],
            SalesAgentRequestAccept::class => [
            SalesAgentRequestAcceptLis::class
            ],
            SalesAgentRequestReject::class => [
            SalesAgentRequestRejectLis::class
            ],
            SalesAgentRequestSent::class => [
            SalesAgentRequestSentLis::class
            ],
            BusinessStatus::class => [
            StatusChangeBusinessLis::class
            ],
            StatusChangeDocument::class => [
            StatusChangeDocumentLis::class
            ],
            UpdatePortfolioStatus::class => [
            UpdatePortfolioStatusLis::class
            ],
            UpdateRota::class => [
            UpdateRotaLis::class
            ],
            UpdateTaskStage::class => [
            UpdateTaskStageLis::class
            ],
            WasteCollectionRequestCreate::class => [
            WasteCollectionRequestCreateLis::class
            ],
            WasteInspectionStatusUpdate::class => [
            WasteInspectionStatusUpdateLis::class
            ],
            WastecollectionConvertedToTrip::class => [
            WastecollectionConvertedToTripLis::class
            ],
    ];

    /**
     * Get the listener directories that should be used to discover events.
     *
     * @return array
     */
    protected function discoverEventsWithin()
    {
        return [
            __DIR__ . '/../Listeners',
        ];
    }
}
