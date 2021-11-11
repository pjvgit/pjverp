<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
// For check email markdown

use Illuminate\Mail\Markdown;
use Illuminate\Support\Facades\Route;

Route::get('mail', function () {
    $markdown = new Markdown(view(), config('mail.markdown'));

    // return $markdown->render('emails.invitation_mail');
    return $markdown->render('emails.event_reminder_email');
    return view('emails.invitation-email');
});

Route::view('dashboard/dashboard1', 'dashboard.dashboardv1')->name('dashboard_version_1');
Route::view('dashboard/dashboard2', 'dashboard.dashboardv2')->name('dashboard_version_2');
Route::view('dashboard/dashboard3', 'dashboard.dashboardv3')->name('dashboard_version_3');
Route::view('dashboard/dashboard4', 'dashboard.dashboardv4')->name('dashboard_version_4');

// uiKits
Route::view('uikits/alerts', 'uiKits.alerts')->name('alerts');
Route::view('uikits/accordion', 'uiKits.accordion')->name('accordion');
Route::view('uikits/buttons', 'uiKits.buttons')->name('buttons');
Route::view('uikits/badges', 'uiKits.badges')->name('badges');
Route::view('uikits/bootstrap-tab', 'uiKits.bootstrap-tab')->name('bootstrap-tab');
Route::view('uikits/carousel', 'uiKits.carousel')->name('carousel');
Route::view('uikits/collapsible', 'uiKits.collapsible')->name('collapsible');
Route::view('uikits/lists', 'uiKits.lists')->name('lists');
Route::view('uikits/pagination', 'uiKits.pagination')->name('pagination');
Route::view('uikits/popover', 'uiKits.popover')->name('popover');
Route::view('uikits/progressbar', 'uiKits.progressbar')->name('progressbar');
Route::view('uikits/tables', 'uiKits.tables')->name('tables');
Route::view('uikits/tabs', 'uiKits.tabs')->name('tabs');
Route::view('uikits/tooltip', 'uiKits.tooltip')->name('tooltip');
Route::view('uikits/modals', 'uiKits.modals')->name('modals');
Route::view('uikits/NoUislider', 'uiKits.NoUislider')->name('NoUislider');
Route::view('uikits/cards', 'uiKits.cards')->name('cards');
Route::view('uikits/cards-metrics', 'uiKits.cards-metrics')->name('cards-metrics');
Route::view('uikits/typography', 'uiKits.typography')->name('typography');

// extra kits
Route::view('extrakits/dropDown', 'extraKits.dropDown')->name('dropDown');
Route::view('extrakits/imageCroper', 'extraKits.imageCroper')->name('imageCroper');
Route::view('extrakits/loader', 'extraKits.loader')->name('loader');
Route::view('extrakits/laddaButton', 'extraKits.laddaButton')->name('laddaButton');
Route::view('extrakits/toastr', 'extraKits.toastr')->name('toastr');
Route::view('extrakits/sweetAlert', 'extraKits.sweetAlert')->name('sweetAlert');
Route::view('extrakits/tour', 'extraKits.tour')->name('tour');
Route::view('extrakits/upload', 'extraKits.upload')->name('upload');


// Apps
Route::view('apps/invoice', 'apps.invoice')->name('invoice');
Route::view('apps/inbox', 'apps.inbox')->name('inbox');
Route::view('apps/chat', 'apps.chat')->name('chat');
Route::view('apps/calendar', 'apps.calendar')->name('calendar');
Route::view('apps/task-manager-list', 'apps.task-manager-list')->name('task-manager-list');
Route::view('apps/task-manager', 'apps.task-manager')->name('task-manager');
Route::view('apps/toDo', 'apps.toDo')->name('toDo');
Route::view('apps/ecommerce/products', 'apps.ecommerce.products')->name('ecommerce-products');
Route::view('apps/ecommerce/product-details', 'apps.ecommerce.product-details')->name('ecommerce-product-details');
Route::view('apps/ecommerce/cart', 'apps.ecommerce.cart')->name('ecommerce-cart');
Route::view('apps/ecommerce/checkout', 'apps.ecommerce.checkout')->name('ecommerce-checkout');


Route::view('apps/contacts/lists', 'apps.contacts.lists')->name('contacts-lists');
Route::view('apps/contacts/contact-details', 'apps.contacts.contact-details')->name('contact-details');
Route::view('apps/contacts/grid', 'apps.contacts.grid')->name('contacts-grid');
Route::view('apps/contacts/contact-list-table', 'apps.contacts.contact-list-table')->name('contact-list-table');

// forms
Route::view('forms/basic-action-bar', 'forms.basic-action-bar')->name('basic-action-bar');
Route::view('forms/multi-column-forms', 'forms.multi-column-forms')->name('multi-column-forms');
Route::view('forms/smartWizard', 'forms.smartWizard')->name('smartWizard');
Route::view('forms/tagInput', 'forms.tagInput')->name('tagInput');
Route::view('forms/forms-basic', 'forms.forms-basic')->name('forms-basic');
Route::view('forms/form-layouts', 'forms.form-layouts')->name('form-layouts');
Route::view('forms/form-input-group', 'forms.form-input-group')->name('form-input-group');
Route::view('forms/form-validation', 'forms.form-validation')->name('form-validation');
Route::view('forms/form-editor', 'forms.form-editor')->name('form-editor');

// Charts
Route::view('charts/echarts', 'charts.echarts')->name('echarts');
Route::view('charts/chartjs', 'charts.chartjs')->name('chartjs');
Route::view('charts/apexLineCharts', 'charts.apexLineCharts')->name('apexLineCharts');
Route::view('charts/apexAreaCharts', 'charts.apexAreaCharts')->name('apexAreaCharts');
Route::view('charts/apexBarCharts', 'charts.apexBarCharts')->name('apexBarCharts');
Route::view('charts/apexColumnCharts', 'charts.apexColumnCharts')->name('apexColumnCharts');
Route::view('charts/apexRadialBarCharts', 'charts.apexRadialBarCharts')->name('apexRadialBarCharts');
Route::view('charts/apexRadarCharts', 'charts.apexRadarCharts')->name('apexRadarCharts');
Route::view('charts/apexPieDonutCharts', 'charts.apexPieDonutCharts')->name('apexPieDonutCharts');
Route::view('charts/apexSparklineCharts', 'charts.apexSparklineCharts')->name('apexSparklineCharts');
Route::view('charts/apexScatterCharts', 'charts.apexScatterCharts')->name('apexScatterCharts');
Route::view('charts/apexBubbleCharts', 'charts.apexBubbleCharts')->name('apexBubbleCharts');
Route::view('charts/apexCandleStickCharts', 'charts.apexCandleStickCharts')->name('apexCandleStickCharts');
Route::view('charts/apexMixCharts', 'charts.apexMixCharts')->name('apexMixCharts');

// datatables
Route::view('datatables/basic-tables', 'datatables.basic-tables')->name('basic-tables');

// sessions
Route::view('sessions/signIn', 'sessions.signIn')->name('signIn');
Route::view('sessions/signUp', 'sessions.signUp')->name('signUp');
Route::view('sessions/forgot', 'sessions.forgot')->name('forgot');

// widgets
Route::view('widgets/card', 'widgets.card')->name('widget-card');
Route::view('widgets/statistics', 'widgets.statistics')->name('widget-statistics');
Route::view('widgets/list', 'widgets.list')->name('widget-list');
Route::view('widgets/app', 'widgets.app')->name('widget-app');
Route::view('widgets/weather-app', 'widgets.weather-app')->name('widget-weather-app');

// others
Route::view('others/notFound', 'others.notFound')->name('notFound');
Route::view('others/user-profile', 'others.user-profile')->name('user-profile');
Route::view('others/starter', 'starter')->name('starter');
Route::view('others/faq', 'others.faq')->name('faq');
Route::view('others/pricing-table', 'others.pricing-table')->name('pricing-table');
Route::view('others/search-result', 'others.search-result')->name('search-result');

Route::get('/', function () {
    return redirect()->intended('/dashboard');
});  
Route::get('locale/{locale}', function ($locale){
    Session::put('locale', $locale);
    return redirect()->back();
});

Auth::routes();
//Pages
Route::get('/privacy', 'PageController@privacy')->name('privacy');
Route::get('/terms', 'PageController@terms')->name('terms');
Route::get('/', 'PageController@index')->name('index');

//Without login can access this route
Route::post('login','UserController@login');
Route::get('autologout', 'UserController@autoLogout')->name('autologout');
Route::post('register','UserController@store');
Route::post('password/email', 'UserController@sendResetLinkEmail')->name('password.email');
Route::post('password/update', 'UserController@resetPassword')->name('password.update');
Route::get('password/reset/{token}', 'UserController@showPasswordRestPage')->name('password.reset.token');
Route::get('/user/verify/{token}', 'UserController@verifyUser');
Route::post('/setupsave', 'UserController@setupsave')->name('setup');
Route::get('/setupprofile/{token}', 'UserController@setupprofile')->name('setupprofile');
Route::get('/users/email_template', 'UserController@email_template')->name('users.email_template');
Route::get('testmail', 'UserController@testmail')->name('users.testmail');
Route::get('/firmuser/verify/{token}', 'ContractController@verifyUser');
Route::get('/setupuserpprofile/{token}', 'ContractController@setupuserpprofile')->name('setupuserpprofile');
Route::post('/setupusersave', 'ContractController@setupusersave')->name('setupusersave');
Route::get('/firmclient/verify/{token}', 'ContractController@verifyClient');

/**
 * For client portal
 */
Route::group(['namespace' => "ClientPortal"], function () {
    Route::get('activate_account/web_token/{token}', 'AuthController@activeClientAccount')->name("client/activate/account");
    Route::get('setup/client/profile/{token}', 'AuthController@setupClientProfile')->name("setup/client/profile");
    Route::post('save/client/profile/{token}', 'AuthController@saveClientProfile')->name("save/client/profile");
    Route::get('get/client/profile/{token}', 'AuthController@getClientProfile')->name("get/client/profile");
    Route::post('update/client/profile/{token}', 'AuthController@updateClientProfile')->name("update/client/profile");
    Route::get('terms/client/portal', 'AuthController@termsCondition')->name("terms/client/portal");
    Route::post('get/timezone', 'AuthController@getTimezone')->name("get/timezone");
});

//After Login can access this routes
Route::group(['middleware'=>['auth', 'role:user']], function () {
    // Route::get('/home', 'HomeController@index')->name('home');
    Route::get('/execute', 'MysqlController@executeQuery');
    Route::get('/load_profile', 'UserController@profile_load')->name('load_profile');
    Route::get('/profile', 'UserController@profile_load')->name('profile');
    Route::post('/saveBasicInfo', 'UserController@saveBasicInfo')->name('users.saveBasicInfo');
    Route::post('/saveEmail', 'UserController@saveEmail')->name('users.saveEmail');
    Route::post('/savePassword', 'UserController@savePassword')->name('users.savePassword');
    Route::post('/saveProfileimage', 'UserController@saveProfileimage')->name('users.saveProfileimage');
    Route::post('/saveCropedProfileimage', 'UserController@saveCropedProfileimage')->name('users.saveCropedProfileimage');
    Route::post('/removeProfileImage', 'UserController@removeProfileImage')->name('removeProfileImage');
    
    Route::get('/account/preferences', 'UserController@preferences')->name('account/preferences');
    Route::post('/account/savePreferences', 'UserController@savePreferences')->name('account/savePreferences');
    
    // user notifications
    Route::get('/account/notifications', 'UserController@notificationSetting')->name('account/notifications');
    Route::post('/account/update_notifications', 'UserController@updateNotifications')->name('account/update_notifications');

    // For Popup Notification
    Route::get('get/popup/notification', 'HomeController@popupNotification')->name('get/popup/notification');
    Route::get('update/popup/notification', 'HomeController@updatePopupNotification')->name('update/popup/notification');
    
    // Store user interest after profile setup
    Route::get('save/user/interested/detail', 'HomeController@saveUserInterestDetail')->name('save/user/interested/detail');

    //Dashboard
    Route::get('/dashboard', 'HomeController@index')->name('dashboard');
    Route::post('dashboard/dismissWidget', 'HomeController@dismissWidget')->name('dismissWidget');
    Route::post('dashboard/loadAllHistoryForDashboard', 'HomeController@loadAllHistoryForDashboard')->name('dashboard/loadAllHistoryForDashboard');
    Route::post('dashboard/loadEventHistoryForDashboard', 'HomeController@loadEventHistoryForDashboard')->name('dashboard/loadEventHistoryForDashboard');
    Route::post('dashboard/loadTaskHistoryForDashboard', 'HomeController@loadTaskHistoryForDashboard')->name('dashboard/loadTaskHistoryForDashboard');
    Route::post('dashboard/loadDocumentHistoryForDashboard', 'HomeController@loadDocumentHistoryForDashboard')->name('dashboard/loadTaskHistoryForDashboard');
    Route::post('dashboard/AddBulkUserModal', 'HomeController@addBulkUserPopup')->name('dashboard/AddBulkUserModal');
    Route::post('dashboard/saveBulkUserPopup', 'HomeController@saveBulkUserPopup')->name('dashboard/saveBulkUserPopup');

    Route::get('/notifications', 'HomeController@notification')->name('notifications');
    Route::any('notifications/loadAllNotification', 'HomeController@loadAllNotification')->name('notifications/loadAllNotification');
    Route::any('notifications/loadInvoiceNotification', 'HomeController@loadInvoiceNotification')->name('notifications/loadInvoiceNotification');
    Route::any('notifications/loadDepositRequestsNotification', 'HomeController@loadDepositRequestsNotification')->name('notifications/loadDepositRequestsNotification');
    Route::any('notifications/loadTimeEntryNotification', 'HomeController@loadTimeEntryNotification')->name('notifications/loadTimeEntryNotification');    
    Route::any('notifications/loadExpensesNotification', 'HomeController@loadExpensesNotification')->name('notifications/loadExpensesNotification');
    Route::any('notifications/loadEventsNotification', 'HomeController@loadEventsNotification')->name('notifications/loadEventsNotification');  
    Route::any('notifications/loadTasksNotification', 'HomeController@loadTasksNotification')->name('notifications/loadTasksNotification');  
    Route::any('notifications/loadDocumentNotification', 'HomeController@loadDocumentNotification')->name('notifications/loadDocumentNotification');
    
    //User / Contacts
    Route::get('contacts/attorneys','ContractController@index')->name('contacts/attorneys');
    Route::post('contacts/loadContract','ContractController@loadUser')->name('contacts/loadContract');
    Route::post('contacts/loadStep1', 'ContractController@loadStep1')->name('contacts/loadStep1');
    Route::post('contacts/saveStep1', 'ContractController@saveStep1')->name('contacts/saveStep1');
    Route::post('contacts/loadStep2', 'ContractController@loadStep2')->name('contacts/loadStep2');
    Route::post('contacts/saveStep2', 'ContractController@saveStep2')->name('contacts/saveStep2');
    Route::post('contacts/loadStep3', 'ContractController@loadStep3')->name('contacts/loadStep3');
    Route::post('contacts/saveStep3', 'ContractController@saveStep3')->name('contacts/saveStep3');
    Route::post('contacts/loadStep4', 'ContractController@loadStep4')->name('contacts/loadStep4');
    Route::post('contacts/saveStep4', 'ContractController@saveStep4')->name('contacts/saveStep4');
    Route::post('contacts/loadFinishStep', 'ContractController@loadFinishStep')->name('contacts/loadFinishStep');
    
    Route::post('contacts/loadColorPicker', 'ContractController@loadColorPicker')->name('contacts/loadColorPicker');
    Route::post('contacts/saveColorCode', 'ContractController@saveColorCode')->name('contacts/saveColorCode');
 
    Route::post('contacts/loadRateBox', 'ContractController@loadRateBox')->name('contacts/loadRateBox');
    Route::post('contacts/saveRate', 'ContractController@saveRate')->name('contacts/saveRate');
    Route::post('contacts/loadPermissionModel', 'ContractController@loadPermissionModel')->name('contacts/loadPermissionModel');
    Route::post('contacts/savePermissionModel', 'ContractController@savePermissionModel')->name('contacts/savePermissionModel');
    Route::post('contacts/SendWelcomeEmail', 'ContractController@SendWelcomeEmail')->name('contacts/SendWelcomeEmail');

    Route::get('account/dashboard', 'ContractController@dashboard')->name('account/dashboard');
    Route::get('contacts/attorneys/{id}', 'ContractController@attorneysView')->name('contacts/attorneys/info');
    Route::get('contacts/attorneys/{id}/cases','ContractController@attorneysView')->name('contacts/attorneys/cases');
    Route::post('contacts/attorneys/loadStaffCase', 'ContractController@staffCaseList')->name('contacts/loadStaffCase');
    Route::post('contacts/attorneys/updateCaseRateForStaff', 'ContractController@updateCaseRateForStaff')->name('contacts/attorneys/updateCaseRateForStaff');
    Route::post('contacts/attorneys/updateDefaultRateForStaff', 'ContractController@updateDefaultRateForStaff')->name('contacts/attorneys/updateDefaultRateForStaff');
    Route::post('contacts/attorneys/unlinkStaffFromCase', 'ContractController@unlinkStaffFromCase')->name('contacts/attorneys/unlinkStaffFromCase');
    Route::post('contacts/attorneys/linkMultipleCaseToStaff', 'ContractController@linkMultipleCaseToStaff')->name('contacts/attorneys/linkMultipleCaseToStaff');
    
    Route::post('contacts/loadProfile', 'ContractController@loadProfile')->name('contacts/loadProfile');
    Route::post('contacts/saveProfile', 'ContractController@saveProfile')->name('contacts/saveProfile');
    
    Route::post('validate_email', 'BaseController@validate_email')->name('validate_email');
    Route::post('contacts/loadDeactivateUser', 'ContractController@loadDeactivateUser')->name('contacts/loadDeactivateUser');
    Route::post('contacts/saveDeactivate', 'ContractController@saveDeactivate')->name('contacts/saveDeactivate');
    Route::post('contacts/createCompany', 'ContractController@createCompany')->name('contacts/createCompany');
    Route::post('contacts/realoadCompanySelection', 'ContractController@realoadCompanySelection')->name('contacts/realoadCompanySelection');
    Route::post('contacts/removeCompany', 'ContractController@removeCompany')->name('contacts/removeCompany');
    Route::post('contacts/reactivateStaff', 'ContractController@reactivateStaff')->name('contacts/reactivateStaff');
    Route::post('contacts/saveAssignTaskForm', 'ContractController@saveAssignTaskForm')->name('contacts/saveAssignTaskForm');    
    

    //Case 
    Route::get('court_cases','CaseController@index')->name('court_cases');
    Route::post('loadCase','CaseController@loadCase')->name('loadCase');
    Route::post('case/loadAllStep', 'CaseController@loadAllStep')->name('case/loadAllStep');
    Route::post('case/saveAllStep', 'CaseController@saveAllStep')->name('case/saveAllStep');
    Route::post('case/loadBillingContact', 'CaseController@loadBillingContact')->name('case/loadBillingContact');
    Route::post('case/checkCaseNameExists', 'CaseController@checkCaseNameExists')->name('case/checkCaseNameExists');
    

    Route::post('case/loadStep1', 'CaseController@loadStep1')->name('case/loadStep1');
    Route::post('case/loadStep1FromCompany', 'CaseController@loadStep1FromCompany')->name('case/loadStep1FromCompany');

    Route::post('case/saveStep1', 'CaseController@saveStep1')->name('case/saveStep1');
    Route::post('case/saveSelectdUser', 'CaseController@saveSelectdUser')->name('case/saveSelectdUser');
    Route::post('case/saveSelectdUserFromCompany', 'CaseController@saveSelectdUserFromCompany')->name('case/saveSelectdUserFromCompany');
    Route::post('case/remomeSelectedUser', 'CaseController@remomeSelectedUser')->name('case/remomeSelectedUser');
    Route::post('case/loadStep2', 'CaseController@loadStep2')->name('case/loadStep2');
    Route::post('case/saveStep2', 'CaseController@saveStep2')->name('case/saveStep2');
    Route::post('case/loadStep3', 'CaseController@loadStep3')->name('case/loadStep3');
    Route::post('case/saveStep3', 'CaseController@saveStep3')->name('case/saveStep3');
    Route::post('case/loadStep4', 'CaseController@loadStep4')->name('case/loadStep4');
    Route::post('case/saveStep4', 'CaseController@saveStep4')->name('case/saveStep4');

    Route::post('case/loadStatus', 'CaseController@loadStatus')->name('case/loadStatus');
    Route::post('case/saveStatus', 'CaseController@saveStatus')->name('case/saveStatus');

    Route::post('case/loadCaseUpdate', 'CaseController@loadCaseUpdate')->name('case/loadCaseUpdate');
    Route::post('case/saveCaseUpdate', 'CaseController@saveCaseUpdate')->name('case/saveCaseUpdate');
    Route::post('case/editCase', 'CaseController@editCase')->name('case/editCase');
    Route::post('case/saveEditCase', 'CaseController@saveEditCase')->name('case/saveEditCase');
    Route::get('court_cases/{id}','CaseController@showCaseDetails')->name('caseview');
    Route::post('case/updateCaseUpdate', 'CaseController@updateCaseUpdate')->name('case/updateCaseUpdate');
    Route::post('case/deleteCaseUpdate', 'CaseController@deleteCaseUpdate')->name('case/deleteCaseUpdate');
    Route::get('case_stages','CaseController@case_stages')->name('case_stages');
    Route::post('case_stages/saveCaseStages','CaseController@saveCaseStages')->name('case_stages/saveCaseStages');
    Route::post('case_stages/deleteCaseStages','CaseController@deleteCaseStages')->name('case_stages/deleteCaseStages');
    Route::post('case_stages/reorderStages','CaseController@reorderStages')->name('case_stages/reorderStages');
    Route::post('case_stages/editCaseStage','CaseController@editCaseStage')->name('case_stages/editCaseStage');
    Route::post('case_stages/saveEditCaseStage','CaseController@saveEditCaseStage')->name('case_stages/saveEditCaseStage');
    Route::post('case_stages/reloadCaserStages','CaseController@reloadCaserStages')->name('case_stages/reloadCaserStages');
    Route::post('court_cases/loadRate','CaseController@loadRate')->name('court_cases/loadRate');
    Route::post('court_cases/saveRate','CaseController@saveCaseRate')->name('court_cases/saveRate');
    Route::post('case_stages/saveTypeOfCase','CaseController@saveTypeOfCase')->name('case_stages/saveTypeOfCase');

    Route::post('court_cases/addIntakeForm', 'CaseController@addIntakeForm')->name('court_cases/addIntakeForm');
    Route::post('court_cases/saveIntakeForm', 'CaseController@saveIntakeForm')->name('court_cases/saveIntakeForm');
    Route::post('court_cases/loadIntakeForms', 'CaseController@loadIntakeForms')->name('court_cases/loadIntakeForms');
    Route::post('court_cases/popupOpenSendEmailIntakeFormFromList', 'CaseController@popupOpenSendEmailIntakeFormFromList')->name('court_cases/popupOpenSendEmailIntakeFormFromList');
    Route::post('court_cases/sendEmailIntakeFormCase', 'CaseController@sendEmailIntakeFormCase')->name('court_cases/sendEmailIntakeFormCase');
    Route::post('court_cases/deleteIntakeFormFromList', 'CaseController@deleteIntakeFormFromList')->name('court_cases/deleteIntakeFormFromList');
    Route::post('court_cases/saveDeleteIntakeFormFromList', 'CaseController@saveDeleteIntakeFormFromList')->name('court_cases/saveDeleteIntakeFormFromList');
    Route::post('court_cases/downloadIntakeForm', 'CaseController@downloadIntakeForm')->name('court_cases/downloadIntakeForm');


    //Calender Tab
    Route::post('court_cases/loadAddEventPage', 'CaseController@loadAddEventPage')->name('court_cases/loadAddEventPage');
    Route::post('court_cases/saveAddEventPage', 'CaseController@saveAddEventPage')->name('court_cases/saveAddEventPage');
    Route::post('court_cases/loadCaseClientAndLeads', 'CaseController@loadCaseClientAndLeads')->name('court_cases/loadCaseClientAndLeads');
    Route::post('court_cases/loadCaseLinkedStaff', 'CaseController@loadCaseLinkedStaff')->name('court_cases/loadCaseLinkedStaff');
    Route::post('court_cases/loadCaseNoneLinkedStaff', 'CaseController@loadCaseNoneLinkedStaff')->name('court_cases/loadCaseNoneLinkedStaff');
    Route::post('court_cases/deleteEventPopup', 'CaseController@deleteEventPopup')->name('court_cases/deleteEventPopup');
    Route::post('court_cases/deleteEventFromCommentPopup', 'CaseController@deleteEventFromCommentPopup')->name('court_cases/deleteEventFromCommentPopup');
    Route::post('court_cases/load/firm/defaultReminder', 'CaseController@loadFirmDefaultReminder')->name('court_cases/load/firm/defaultReminder');

    Route::post('court_cases/deleteEvent', 'CaseController@deleteEvent')->name('court_cases/deleteEvent');
    Route::post('court_cases/loadAfterFirstCase', 'CaseController@loadAfterFirstCase')->name('court_cases/loadAfterFirstCase');
    Route::post('court_cases/loadEditEventPage', 'CaseController@loadEditEventPage')->name('court_cases/loadEditEventPage');
    Route::post('court_cases/loadSingleEditEventPage', 'CaseController@loadSingleEditEventPage')->name('court_cases/loadSingleEditEventPage');
    Route::post('court_cases/loadCaseTimeline', 'CaseController@loadCaseTimeline')->name('court_cases/loadCaseTimeline');

    Route::post('court_cases/saveEditEventPage', 'CaseController@saveEditEventPage')->name('court_cases/saveEditEventPage');
    Route::post('court_cases/confirmBeforeEditEventPopup', 'CaseController@confirmBeforeEditEventPopup')->name('court_cases/confirmBeforeEditEventPopup');
    Route::post('court_cases/loadCommentPopup', 'CaseController@loadCommentPopup')->name('court_cases/loadCommentPopup');
    Route::post('court_cases/loadCommentHistory', 'CaseController@loadCommentHistory')->name('court_cases/loadCommentHistory');
    Route::post('court_cases/saveEventComment', 'CaseController@saveEventComment')->name('court_cases/saveEventComment');
    Route::post('court_cases/loadReminderPopup', 'CaseController@loadReminderPopup')->name('court_cases/loadReminderPopup');
    Route::post('court_cases/saveReminderPopup', 'CaseController@saveReminderPopup')->name('court_cases/saveReminderPopup');
    Route::post('court_cases/loadReminderHistory', 'CaseController@loadReminderHistory')->name('court_cases/loadReminderHistory');
    Route::post('court_cases/loadReminderPopupIndex', 'CaseController@loadReminderPopupIndex')->name('court_cases/loadReminderPopupIndex');
    Route::post('court_cases/saveEventHistory', 'CaseController@saveEventHistory')->name('court_cases/saveEventHistory');

    Route::post('court_cases/closeCase', 'CaseController@closeCase')->name('court_cases/closeCase');
    Route::post('court_cases/ProcessCloseCase', 'CaseController@ProcessCloseCase')->name('court_cases/ProcessCloseCase');
    Route::post('court_cases/ReopenClosedCase', 'CaseController@ReopenClosedCase')->name('court_cases/ReopenClosedCase');
    Route::post('court_cases/DeleteClosedCase', 'CaseController@DeleteClosedCase')->name('court_cases/DeleteClosedCase');
    Route::post('court_cases/case/addNotes', 'CaseController@addNotes')->name('court_cases/case/addNotes');
    Route::post('court_cases/case/loadTaskPortion', 'CaseController@loadTaskPortion')->name('court_cases/case/loadTaskPortion');
    Route::post('court_cases/case/loadTaskPortion', 'CaseController@loadTaskPortion')->name('court_cases/case/loadTaskPortion');
    Route::post('court_cases/saveCaseHistory', 'CaseController@saveCaseHistory')->name('court_cases/saveCaseHistory');


    //Practice Area
    Route::get('practice_areas','CaseController@practice_areas')->name('practice_areas');    
    Route::post('loadPracticeArea','CaseController@loadPracticeArea')->name('loadPracticeArea');
    Route::post('case/deletePracticeArea', 'CaseController@deletePracticeArea')->name('case/deletePracticeArea');
    Route::post('case/loadAddPracticeArea', 'CaseController@loadAddPracticeArea')->name('contacts/loadAddPracticeArea');
    Route::post('case/saveAddPracticeArea', 'CaseController@saveAddPracticeArea')->name('contacts/saveAddPracticeArea');
    Route::post('case/loadEditPracticeArea', 'CaseController@loadEditPracticeArea')->name('contacts/loadEditPracticeArea');
    Route::post('case/saveEditPracticeArea', 'CaseController@saveEditPracticeArea')->name('contacts/saveEditPracticeArea');
   

    // Inner tabs
    Route::get('court_cases/{id}/info','CaseController@showCaseDetails')->name('info');
    Route::get('court_cases/{id}/recent_activity','CaseController@showCaseDetails')->name('recent_activity');
    Route::get('court_cases/{id}/calendars','CaseController@showCaseDetails')->name('calendars');
    Route::get('court_cases/{id}/documents','CaseController@showCaseDetails')->name('documents');
    Route::get('court_cases/{id}/tasks','CaseController@showCaseDetails')->name('tasks');
    Route::get('court_cases/{id}/notes','CaseController@showCaseDetails')->name('notes');
    Route::get('court_cases/{id}/intake_forms','CaseController@showCaseDetails')->name('intake_forms');
    Route::get('court_cases/{id}/workflows','CaseController@showCaseDetails')->name('workflows');

    //Main Tabs
    Route::get('court_cases/{id}/status_updates','CaseController@showCaseDetails')->name('status_updates');
    Route::get('court_cases/{id}/case_link','CaseController@showCaseDetails')->name('case_link');
    Route::post('court_cases/loadTypeSelection','CaseController@loadTypeSelection')->name('court_cases/loadTypeSelection');
    Route::post('court_cases/unlinkSelection','CaseController@unlinkSelection')->name('court_cases/unlinkSelection');
    Route::post('court_cases/loadExisting','CaseController@loadExisting')->name('court_cases/loadExisting');
    Route::post('court_cases/checkBeforeLinking','CaseController@checkBeforeLinking')->name('court_cases/checkBeforeLinking');
    Route::post('court_cases/saveLinkSelection','CaseController@saveLinkSelection')->name('court_cases/saveLinkSelection');


    //Time and billing
    Route::get('court_cases/{id}/overview','CaseController@showCaseDetails')->name('overview');
    Route::get('court_cases/{id}/time_entries','CaseController@showCaseDetails')->name('time_entries');
    Route::get('court_cases/{id}/expenses','CaseController@showCaseDetails')->name('expenses');
    Route::get('court_cases/{id}/invoices','CaseController@showCaseDetails')->name('invoices'); 
    Route::get('court_cases/{id}/payment_activity','CaseController@showCaseDetails')->name('payment_activity');
    
    Route::get('court_cases/{id}/communications/messages','CaseController@showCaseDetails')->name('communications/messages');
    Route::post('court_cases/communications/loadMessagesEntry', 'CaseController@loadMessagesEntry')->name('court_cases/communications/loadMessagesEntry');
    Route::get('court_cases/{id}/communications/calls','CaseController@showCaseDetails')->name('communications/calls');
    Route::get('court_cases/{id}/communications/emails','CaseController@showCaseDetails')->name('communications/emails');
    Route::get('court_cases/{id}/communications/chat_conversations','CaseController@showCaseDetails')->name('communications/chat_conversations');

    Route::post('court_cases/overview/editBillingContactPopup','CaseController@editBillingContactPopup')->name('court_cases/overview/editBillingContactPopup');
    Route::post('court_cases/overview/saveBillingContactPopup','CaseController@saveBillingContactPopup')->name('court_cases/overview/saveBillingContactPopup');
    Route::post('court_cases/time_entries/loadTimeEntryBlocks','CaseController@loadTimeEntryBlocks')->name('court_cases/time_entries/loadTimeEntryBlocks');


    //Inner tab -> Staff tab
    Route::post('court_cases/loadLeadAttorney','CaseController@loadLeadAttorney')->name('court_cases/loadLeadAttorney');
    Route::post('court_cases/saveLeadAttorney','CaseController@saveLeadAttorney')->name('court_cases/saveLeadAttorney');
    Route::post('court_cases/loadOriginatingAttorney','CaseController@loadOriginatingAttorney')->name('court_cases/loadOriginatingAttorney');
    Route::post('court_cases/saveOriginatingAttorney','CaseController@saveOriginatingAttorney')->name('court_cases/saveOriginatingAttorney');
    Route::post('court_cases/UnlinkAttorney','CaseController@UnlinkAttorney')->name('court_cases/UnlinkAttorney');
    Route::post('court_cases/loadStaffSelection','CaseController@loadStaffSelection')->name('court_cases/loadStaffSelection');
    Route::post('court_cases/loadStaff','CaseController@loadStaff')->name('court_cases/loadStaff');
    Route::post('court_cases/loadExistingStaff','CaseController@loadExistingStaff')->name('court_cases/loadExistingStaff');
    Route::post('court_cases/checkStaffBeforeLinking','CaseController@checkStaffBeforeLinking')->name('court_cases/checkStaffBeforeLinking');
    Route::post('court_cases/saveStaffLinkSelection','CaseController@saveStaffLinkSelection')->name('court_cases/saveStaffLinkSelection');
    Route::post('court_cases/hideAddEventGuide', 'CaseController@hideAddEventGuide')->name('court_cases/hideAddEventGuide');
    Route::post('court_cases/loadEventRightSection', 'CaseController@loadEventRightSection')->name('court_cases/loadEventRightSection');
    Route::post('court_cases/loadLeadRightSection', 'CaseController@loadLeadRightSection')->name('court_cases/loadLeadRightSection');
    Route::post('court_cases/dismissCaseModal', 'CaseController@dismissCaseModal')->name('court_cases/dismissCaseModal');

    Route::post('court_cases/addCaseReminderPopup', 'CaseController@addCaseReminderPopup')->name('court_cases/addCaseReminderPopup');
    Route::post('court_cases/saveCaseReminderPopup', 'CaseController@saveCaseReminderPopup')->name('court_cases/saveCaseReminderPopup');
    Route::post('court_cases/saveSolStatus', 'CaseController@saveSolStatus')->name('court_cases/saveSolStatus');
    Route::post('court_cases/saveEventPrefernace', 'CaseController@saveEventPrefernace')->name('court_cases/saveEventPrefernace');

    //Client
    Route::get('contacts/client','ContractController@clientIndex')->name('contacts/client');
    Route::post('contacts/loadClient','ContractController@loadClient')->name('contacts/loadClient');
    Route::post('contacts/loadAddContact', 'ContractController@loadAddContact')->name('contacts/loadAddContact');
    Route::post('contacts/saveAddContact', 'ContractController@saveAddContact')->name('contacts/saveAddContact');
    Route::post('contacts/loadEditContact', 'ContractController@loadEditContact')->name('contacts/loadEditContact');
    Route::post('contacts/saveEditContact', 'ContractController@saveEditContact')->name('contacts/saveEditContact');
    Route::post('contacts/loadAllClient', 'ContractController@loadAllClient')->name('contacts/loadAllClient');
    
    //Company
    Route::get('contacts/company','ContractController@companyIndex')->name('contacts/company');
    Route::post('contacts/loadCompany','ContractController@loadCompany')->name('contacts/loadCompany');
    Route::post('contacts/loadAddCompany', 'ContractController@loadAddCompany')->name('contacts/loadAddCompany');
    Route::post('contacts/saveAddCompany', 'ContractController@saveAddCompany')->name('contacts/saveAddCompany');
    Route::post('contacts/loadEditCompany', 'ContractController@loadEditCompany')->name('contacts/loadEditCompany');
    Route::post('contacts/saveEditCompany', 'ContractController@saveEditCompany')->name('contacts/saveEditCompany');
    Route::post('contacts/changeRolePopup', 'ContractController@changeRolePopup')->name('contacts/changeRolePopup');
    Route::post('contacts/saveRolePopup', 'ContractController@saveRolePopup')->name('contacts/saveRolePopup');

    
    //Client Group
    Route::get('contacts/contact_groups','ContractController@clientgroupIndex')->name('contacts/contact_groups');
    Route::post('contacts/loadClientgroup','ContractController@loadClientgroup')->name('contacts/loadClientgroup');
    Route::post('contacts/loadAddContactGroup', 'ContractController@loadAddContactGroup')->name('contacts/loadAddContactGroup');
    Route::post('contacts/saveAddContactGroup', 'ContractController@saveAddContactGroup')->name('contacts/saveAddContactGroup');
    Route::post('contacts/deleteClientGroup', 'ContractController@deleteClientGroup')->name('contacts/deleteClientGroup');
    Route::post('contacts/loadEditClientGroup', 'ContractController@loadEditClientGroup')->name('contacts/loadEditClientGroup');
    Route::post('contacts/saveEditClientGroup', 'ContractController@saveEditClientGroup')->name('contacts/saveEditClientGroup');
   
    

     //Task module
     Route::get('tasks','TaskController@index')->name('tasks');
     Route::post('loadTask','TaskController@loadTask')->name('loadTask');
     Route::post('tasks/loadAddTaskPopup', 'TaskController@loadAddTaskPopup')->name('tasks/loadAddTaskPopup');
     Route::post('tasks/saveAddTaskPopup', 'TaskController@saveAddTaskPopup')->name('tasks/saveAddTaskPopup');
     Route::post('tasks/loadCaseLinkedStaffForTask', 'TaskController@loadCaseLinkedStaffForTask')->name('tasks/loadCaseLinkedStaffForTask');
     Route::post('tasks/loadCaseNoneLinkedStaffForTask', 'TaskController@loadCaseNoneLinkedStaffForTask')->name('tasks/loadCaseNoneLinkedStaffForTask');
     Route::post('tasks/loadCaseClientAndLeadsForTask', 'TaskController@loadCaseClientAndLeadsForTask')->name('tasks/loadCaseClientAndLeadsForTask');
     Route::post('tasks/hideTaskGuide', 'TaskController@hideTaskGuide')->name('tasks/hideTaskGuide');
     Route::post('tasks/loadAllStaffMember', 'TaskController@loadAllStaffMember')->name('tasks/loadAllStaffMember');
     Route::post('tasks/loadTimeEstimationUsersList', 'TaskController@loadTimeEstimationUsersList')->name('tasks/loadTimeEstimationUsersList');
     Route::post('tasks/loadTimeEstimationCaseWiseUsersList', 'TaskController@loadTimeEstimationCaseWiseUsersList')->name('tasks/loadTimeEstimationCaseWiseUsersList');
     Route::post('tasks/deleteTask', 'TaskController@deleteTask')->name('tasks/deleteTask');
     Route::post('tasks/taskStatus', 'TaskController@taskStatus')->name('tasks/taskStatus');

     Route::post('tasks/loadEditTaskPopup', 'TaskController@loadEditTaskPopup')->name('tasks/loadEditTaskPopup');
     Route::post('tasks/saveEditTaskPopup', 'TaskController@saveEditTaskPopup')->name('tasks/saveEditTaskPopup');
     Route::post('tasks/loadTaskReminderPopupIndex', 'TaskController@loadTaskReminderPopupIndex')->name('tasks/loadTaskReminderPopupIndex');
     Route::post('tasks/loadReminderPopupIndexDontRefresh', 'TaskController@loadReminderPopupIndexDontRefresh')->name('tasks/loadReminderPopupIndexDontRefresh');
     Route::post('tasks/saveTaskReminderPopup', 'TaskController@saveTaskReminderPopup')->name('tasks/saveTaskReminderPopup');
     Route::post('tasks/loadReminderArea', 'TaskController@loadReminderArea')->name('tasks/loadReminderArea');
    
     Route::post('tasks/loadTimeEntryPopup', 'TaskController@loadTimeEntryPopup')->name('tasks/loadTimeEntryPopup');
     Route::post('tasks/saveTimeEntryPopup', 'TaskController@saveTimeEntryPopup')->name('tasks/saveTimeEntryPopup');
     Route::get('tasks/markasread', 'TaskController@markasread')->name('tasks/markasread');
     Route::post('tasks/bulkMarkAsRead', 'TaskController@bulkMarkAsRead')->name('tasks/bulkMarkAsRead');
     Route::post('tasks/taskAllReadFromCalender', 'TaskController@taskAllReadFromCalender')->name('tasks/taskAllReadFromCalender');
     
     Route::post('tasks/markAsCompleted', 'TaskController@markAsCompleted')->name('tasks/markAsCompleted');
     Route::post('tasks/changeDueDate', 'TaskController@changeDueDate')->name('tasks/changeDueDate');
     Route::post('tasks/loadTaskActivity', 'TaskController@loadTaskActivity')->name('tasks/loadTaskActivity');
     Route::post('tasks/savebulkTimeEntry', 'TaskController@savebulkTimeEntry')->name('tasks/savebulkTimeEntry');
     Route::post('tasks/getAndCheckDefaultCaseRate', 'TaskController@getAndCheckDefaultCaseRate')->name('tasks/getAndCheckDefaultCaseRate');
     Route::post('tasks/loadTaskDetailPage', 'TaskController@loadTaskDetailPage')->name('tasks/loadTaskDetailPage');
     Route::post('tasks/loadTaskViewPage', 'TaskController@loadTaskViewPage')->name('tasks/loadTaskViewPage');
     Route::post('tasks/saveTaskComment', 'TaskController@saveTaskComment')->name('tasks/saveTaskComment');
     Route::post('tasks/loadTaskComment', 'TaskController@loadTaskComment')->name('tasks/loadTaskComment');
     Route::post('tasks/loadTaskCommentUpdatedView', 'TaskController@loadTaskCommentUpdatedView')->name('tasks/loadTaskCommentUpdatedView');
     Route::post('tasks/loadTaskHistory', 'TaskController@loadTaskHistory')->name('tasks/loadTaskHistory');
     Route::post('tasks/updateCheckList', 'TaskController@updateCheckList')->name('tasks/updateCheckList');
     Route::post('tasks/loadCheckListView', 'TaskController@loadCheckListView')->name('tasks/loadCheckListView');
     Route::post('tasks/loadCheckListViewForTask', 'TaskController@loadCheckListViewForTask')->name('tasks/loadCheckListViewForTask');
     Route::post('tasks/loadCheckListViewForTaskWithoutCheckbox', 'TaskController@loadCheckListViewForTask')->name('tasks/loadCheckListViewForTaskWithoutCheckbox');
     Route::post('tasks/reloadCounter', 'TaskController@reloadTaskCounter')->name('tasks/reloadCounter');
     Route::post('tasks/singleTaskMarkAsComplete', 'TaskController@singleTaskMarkAsComplete')->name('tasks/singleTaskMarkAsComplete');
     Route::post('tasks/loadTaskRightSection', 'TaskController@loadTaskRightSection')->name('tasks/loadTaskRightSection');
    


     //Calender Module
     Route::get('events/', 'CalendarController@index')->name('events/');
     Route::get('events/{id}', 'CalendarController@eventDetail')->name('events/detail');
     Route::get('print_events', 'CalendarController@printEvents')->name('print_events');
     
     Route::get('item_categories', 'CalendarController@item_categories')->name('item_categorie/');
     Route::post('loadEventCalendar/load', 'CalendarController@loadEventCalendar')->name('loadEventCalendar/load');
     Route::post('court_cases/loadAddEventPageFromCalendar', 'CalendarController@loadAddEventPageFromCalendar')->name('court_cases/loadAddEventPageFromCalendar');
     Route::post('court_cases/loadAddEventPageSpecificaDate', 'CalendarController@loadAddEventPageSpecificaDate')->name('court_cases/loadAddEventPageSpecificaDate');
     Route::post('court_cases/loadCommentPopupFromCalendar', 'CalendarController@loadCommentPopupFromCalendar')->name('court_cases/loadCommentPopupFromCalendar');
     Route::post('court_cases/loadSingleEditEventPageFromCalendar', 'CalendarController@loadSingleEditEventPageFromCalendar')->name('court_cases/loadSingleEditEventPageFromCalendar');
     Route::post('court_cases/loadFirmAllStaff', 'CalendarController@loadFirmAllStaff')->name('court_cases/loadFirmAllStaff');
     Route::post('court_cases/loadEditEventPageFromCalendarView', 'CalendarController@loadEditEventPageFromCalendarView')->name('court_cases/loadEditEventPageFromCalendarView');
     Route::post('court_cases/loadGrantAccessPage', 'CalendarController@loadGrantAccessPage')->name('court_cases/loadGrantAccessPage');
     Route::post('court_cases/saveGrantAccessPage', 'CalendarController@saveGrantAccessPage')->name('court_cases/saveGrantAccessPage');
     Route::post('court_cases/saveConfirmGrantAccessPage', 'CalendarController@saveConfirmGrantAccessPage')->name('court_cases/saveConfirmGrantAccessPage');

     Route::post('loadEventCalendar/loadAgendaView', 'CalendarController@loadAgendaView')->name('loadEventCalendar/loadAgendaView');
     Route::post('loadEventCalendar/loadStaffView', 'CalendarController@loadStaffView')->name('loadEventCalendar/loadStaffView');
     Route::get('item_categories', 'CalendarController@itemCategories')->name('item_categories');
     

     //Locations
     Route::get('locations','LocationController@index')->name('locations');
     Route::post('loadLocation','LocationController@loadLocation')->name('loadLocation');
     Route::post('loadAddLocationPopup', 'LocationController@loadAddLocationPopup')->name('loadAddLocationPopup');
     Route::post('saveAddLocationPopup', 'LocationController@saveAddLocationPopup')->name('saveAddLocationPopup');
     Route::post('loadEditLocationPopup', 'LocationController@loadEditLocationPopup')->name('loadEditLocationPopup');
     Route::post('saveEditLocationPopup', 'LocationController@saveEditLocationPopup')->name('saveEditLocationPopup');
     Route::post('deleteLocation', 'LocationController@deleteLocation')->name('deleteLocation');
     

     //Lead
     Route::get('leads/statuses','LeadController@index')->name('leads/statuses');
     Route::get('leads/active','LeadController@active')->name('leads/active');
     Route::get('leads/donthire','LeadController@donthire')->name('leads/donthire');
     Route::get('leads/converted','LeadController@converted')->name('leads/converted');
     Route::get('leads/onlineleads','LeadController@onlineleads')->name('leads/onlineleads');

     Route::post('leads/loadActive','LeadController@loadActive')->name('leads/loadActive');
     Route::post('leads/loadDonthire','LeadController@loadDonthire')->name('leads/loadDonthire');
     Route::get('leads/exportdonthireLead', 'LeadController@exportdonthireLead')->name('leads/exportdonthireLead');
     Route::post('leads/loadConverted', 'LeadController@loadConverted')->name('leads/loadConverted');
     Route::get('leads/exportConvertedLead', 'LeadController@exportConvertedLead')->name('leads/exportConvertedLead');
     Route::post('leads/loadOnlineLeads','LeadController@loadOnlineLeads')->name('leads/loadOnlineLeads');
     Route::post('leads/approveLead','LeadController@approveLead')->name('leads/approveLead');
     Route::post('leads/approveBulkLead','LeadController@approveBulkLead')->name('leads/approveBulkLead');

     Route::post('leads/addLead', 'LeadController@addLead')->name('leads/addLead');
     Route::post('leads/saveLead', 'LeadController@saveLead')->name('leads/saveLead');
     Route::post('leads/changeLeadOrder', 'LeadController@changeLeadOrder')->name('leads/changeLeadOrder');
     Route::post('leads/reorderStages','LeadController@reorderStages')->name('leads/reorderStages');
     Route::post('leads/addStatus', 'LeadController@addStatus')->name('leads/addStatus');
     Route::post('leads/saveStatus', 'LeadController@saveStatus')->name('leads/saveStatus');
     Route::post('leads/editLead', 'LeadController@editLead')->name('leads/editLead');
     Route::post('leads/updateLead', 'LeadController@updateLead')->name('leads/updateLead');
     Route::post('leads/editStatus', 'LeadController@editStatus')->name('leads/editStatus');
     Route::post('leads/updateStatus', 'LeadController@updateStatus')->name('leads/updateStatus');
     Route::post('leads/deleteStatus', 'LeadController@deleteStatus')->name('leads/deleteStatus');
     Route::post('leads/deleteLead', 'LeadController@deleteLead')->name('leads/deleteLead');
     Route::post('leads/deleteSubmittedLead', 'LeadController@deleteSubmittedLead')->name('leads/deleteSubmittedLead');
     Route::post('leads/loadStep1', 'LeadController@loadStep1')->name('leads/loadStep1');
     Route::post('leads/saveStep1', 'LeadController@saveStep1')->name('leads/saveStep1');
     Route::post('leads/loadStep2', 'LeadController@loadStep2')->name('leads/loadStep2');
     Route::post('leads/saveStep2', 'LeadController@saveStep2')->name('leads/saveStep2');
     Route::post('leads/loadStep3', 'LeadController@loadStep3')->name('leads/loadStep3');
     Route::post('leads/saveStep3', 'LeadController@saveStep3')->name('leads/saveStep3');
     Route::post('leads/loadStep4', 'LeadController@loadStep4')->name('leads/loadStep4');
     Route::post('leads/saveStep4', 'LeadController@saveStep4')->name('leads/saveStep4');
     Route::get('leads/exportLead', 'LeadController@exportLead')->name('leads/exportLead');
     Route::post('leads/loadAssignPopup', 'LeadController@loadAssignPopup')->name('leads/loadAssignPopup');
     Route::post('leads/saveBulkAssignLeads', 'LeadController@saveBulkAssignLeads')->name('leads/saveBulkAssignLeads');

     Route::post('leads/loadChangeBulkStatus', 'LeadController@loadChangeBulkStatus')->name('leads/loadChangeBulkStatus');
     Route::post('leads/saveChangeBulkStatus', 'LeadController@saveChangeBulkStatus')->name('leads/saveChangeBulkStatus');

     Route::post('leads/loadChangeBulkDonothire', 'LeadController@loadChangeBulkDonothire')->name('leads/loadChangeBulkDonothire');
     Route::post('leads/saveChangeBulkDonothire', 'LeadController@saveChangeBulkDonothire')->name('leads/saveChangeBulkDonothire');

     Route::post('leads/deleteBulkLead', 'LeadController@deleteBulkLead')->name('leads/deleteBulkLead');
     Route::post('leads/saveDeleteBulkLead', 'LeadController@saveDeleteBulkLead')->name('leads/saveDeleteBulkLead');
     Route::post('leads/reactiveLead', 'LeadController@reactiveLead')->name('leads/reactiveLead');

     Route::get('leads/tasks','LeadController@leadTasks')->name('leads/tasks');
     Route::post('leads/loadLeadTask', 'LeadController@loadLeadTask')->name('leads/loadLeadTask');
     
    Route::post('leads/addLoadSingleTask', 'LeadController@addLoadSingleTask')->name('leads/addLoadSingleTask');
     Route::post('leads/loadAddTaskPopup', 'LeadController@loadAddTaskPopup')->name('leads/loadAddTaskPopup');
     Route::post('leads/saveAddTaskPopup', 'LeadController@saveAddTaskPopup')->name('leads/saveAddTaskPopup');
     Route::post('leads/loadRightSection', 'LeadController@loadRightSection')->name('leads/loadRightSection');
     Route::post('leads/loadAllStaffMember', 'LeadController@loadAllStaffMember')->name('leads/loadAllStaffMember');
     Route::post('leads/markAsCompleted', 'LeadController@markAsCompleted')->name('leads/markAsCompleted');
     Route::post('leads/loadEditTaskPopup', 'LeadController@loadEditTaskPopup')->name('leads/loadEditTaskPopup');
     Route::post('leads/saveEditTaskPopup', 'LeadController@saveEditTaskPopup')->name('leads/saveEditTaskPopup');
     Route::post('leads/loadTaskDetailPage', 'LeadController@loadTaskDetailPage')->name('leads/loadTaskDetailPage');
     Route::post('leads/loadTimeEstimationCaseWiseUsersList', 'LeadController@loadTimeEstimationCaseWiseUsersList')->name('leads/loadTimeEstimationCaseWiseUsersList');



     //Lead Setting.
     Route::get('lead_setting','LeadController@LeadSetting')->name('lead_setting');
     Route::post('lead_setting/addReferalSource', 'LeadController@addReferalSource')->name('lead_setting/editReferalSource');
     Route::post('lead_setting/saveReferalSource', 'LeadController@saveReferalSource')->name('lead_setting/saveReferalSource');
     Route::post('lead_setting/editReferalResource', 'LeadController@editReferalResource')->name('lead_setting/editReferalResource');
     Route::post('lead_setting/updateReferalSource', 'LeadController@updateReferalSource')->name('lead_setting/updateReferalSource');
     Route::post('lead_setting/deleteReferalSource', 'LeadController@deleteReferalSource')->name('lead_setting/deleteReferalSource');
     Route::post('lead_setting/changeReferalResource', 'LeadController@changeReferalResource')->name('lead_setting/changeReferalResource');
     Route::post('lead_setting/changeSaveReferalResource', 'LeadController@changeSaveReferalResource')->name('lead_setting/changeSaveReferalResource');
     Route::post('lead_setting/doNotHire', 'LeadController@doNotHire')->name('lead_setting/doNotHire');
     Route::post('lead_setting/SavedoNotHire', 'LeadController@SavedoNotHire')->name('lead_setting/SavedoNotHire');
     
     Route::post('lead_setting/addReason', 'LeadController@addReason')->name('lead_setting/addReason');
     Route::post('lead_setting/saveReason', 'LeadController@saveReason')->name('lead_setting/saveReason');
     Route::post('lead_setting/editReason', 'LeadController@editReason')->name('lead_setting/editReason');
     Route::post('lead_setting/updateReason', 'LeadController@updateReason')->name('lead_setting/updateReason');
     Route::post('lead_setting/deleteReason', 'LeadController@deleteReason')->name('lead_setting/deleteReason');
     Route::get('custom_fields', 'LeadController@custom_fields')->name('custom_fields');



     //Lead details 
     Route::get('leads/{id}/lead_details/info','LeadController@leadIno')->name('lead_details/info');
     Route::get('leads/{id}/lead_details/notes','LeadController@leadIno')->name('lead_details/notes');
     Route::get('leads/{id}/lead_details/activity','LeadController@leadIno')->name('lead_details/activity');

     Route::get('leads/{id}/case_details/info','LeadController@leadIno')->name('case_details/info');
     Route::get('leads/{id}/case_details/activity','LeadController@leadIno')->name('case_details/activity');
     Route::get('leads/{id}/case_details/tasks','LeadController@leadIno')->name('case_details/tasks');
     Route::get('leads/{id}/case_details/notes','LeadController@leadIno')->name('case_details/notes');
     Route::get('leads/{id}/case_details/calendars','LeadController@leadIno')->name('case_details/calendars');
     Route::get('leads/{id}/case_details/intake_forms','LeadController@leadIno')->name('case_details/intake_forms');
     Route::get('leads/{id}/case_details/invoices','LeadController@leadIno')->name('case_details/invoices');
     Route::get('leads/{id}/case_details/trust_history','LeadController@leadIno')->name('case_details/trust_history');
     Route::get('leads/{id}/case_details/credit_history','LeadController@leadIno')->name('case_details/credit_history');

     Route::get('leads/{id}/communications/text_messages','LeadController@leadIno')->name('communications/text_messages');
     Route::get('leads/{id}/communications/calls','LeadController@leadIno')->name('communications/calls');
     Route::get('leads/{id}/communications/emails','LeadController@leadIno')->name('communications/emails');

     Route::post('leads/addCall','LeadController@addCall')->name('leads/addCall');
     Route::post('leads/getMobileNumber','LeadController@getMobileNumber')->name('leads/getMobileNumber');
     Route::post('leads/saveCall','LeadController@saveCall')->name('leads/saveCall');
     Route::post('leads/loadCalls', 'LeadController@loadCalls')->name('leads/loadCalls');
     Route::post('leads/deleteCallLog','LeadController@deleteCallLog')->name('leads/deleteCallLog');
     Route::post('leads/editCall','LeadController@editCall')->name('leads/editCall');
     Route::post('leads/updateCall','LeadController@updateCall')->name('leads/updateCall');
     Route::post('leads/changeCallType','LeadController@changeCallType')->name('leads/changeCallType');
     
     Route::post('leads/reactivateLead','LeadController@reactivateLead')->name('leads/reactivateLead');
     Route::post('leads/doNotHireFromDetail', 'LeadController@doNotHireFromDetail')->name('leads/doNotHireFromDetail');
     Route::post('leads/editLeadFromDetail', 'LeadController@editLeadFromDetail')->name('leads/editLeadFromDetail');
     Route::post('leads/addLeadPopup','LeadController@addLeadPopup')->name('leads/addLeadPopup');
     Route::post('leads/saveNote','LeadController@saveNote')->name('leads/saveNote');
     Route::post('leads/editLeadPopup','LeadController@editLeadPopup')->name('leads/editLeadPopup');
     Route::post('leads/updateNote','LeadController@updateNote')->name('leads/updateNote');
     Route::post('leads/deleteNote','LeadController@deleteNote')->name('leads/deleteNote');


    Route::post('leads/leadActivity', 'LeadController@leadActivity')->name('leads/leadActivity');
    Route::post('leads/editPotentailCase','LeadController@editPotentailCase')->name('leads/editPotentailCase');
    Route::post('leads/savePotentailCase','LeadController@savePotentailCase')->name('leads/savePotentailCase');
    Route::post('leads/caseActivityHistory', 'LeadController@caseActivityHistory')->name('leads/caseActivityHistory');
    Route::post('leads/loadAllTaskByLead', 'LeadController@loadAllTaskByLead')->name('leads/loadAllTaskByLead');
    Route::post('leads/addCaseNotePopup','LeadController@addCaseNotePopup')->name('leads/addCaseNotePopup');
     Route::post('leads/saveCaseNotePopup','LeadController@saveCaseNotePopup')->name('leads/saveCaseNotePopup');
     Route::post('leads/editCaseNotePopup','LeadController@editCaseNotePopup')->name('leads/editCaseNotePopup');
     Route::post('leads/updateCaseNotePopup','LeadController@updateCaseNotePopup')->name('leads/updateCaseNotePopup');
     Route::post('leads/deleteCaseNote','LeadController@deleteCaseNote')->name('leads/deleteCaseNote');

    Route::post('leads/loadAddEventPage', 'LeadController@loadAddEventPage')->name('leads/loadAddEventPage');
    Route::post('leads/saveCaseEvent', 'LeadController@saveCaseEvent')->name('leads/saveCaseEvent');
    Route::post('leads/loadEditEventPage', 'LeadController@loadEditEventPage')->name('leads/loadEditEventPage');
    Route::post('leads/loadSingleEditEventPage', 'LeadController@loadSingleEditEventPage')->name('leads/loadSingleEditEventPage');
    Route::post('leads/saveEditEventPage', 'LeadController@saveEditEventPage')->name('leads/saveEditEventPage');

    Route::post('leads/loadEventRightSection', 'LeadController@loadEventRightSection')->name('leads/loadEventRightSection');
    Route::post('leads/loadAllCaseStaffMember', 'LeadController@loadAllCaseStaffMember')->name('leads/loadAllCaseStaffMember');

    Route::post('leads/loadIntakeForms', 'LeadController@loadIntakeForms')->name('leads/loadIntakeForms');
    Route::post('leads/deleteIntakeFormFromList', 'LeadController@deleteIntakeFormFromList')->name('leads/deleteIntakeFormFromList');
    Route::post('leads/saveDeleteIntakeFormFromList', 'LeadController@saveDeleteIntakeFormFromList')->name('leads/saveDeleteIntakeFormFromList');
    
    Route::post('leads/popupOpenSendEmailIntakeFormFromList', 'LeadController@popupOpenSendEmailIntakeFormFromList')->name('leads/popupOpenSendEmailIntakeFormFromList');
    Route::post('leads/sendEmailIntakeFormPC', 'LeadController@sendEmailIntakeFormPC')->name('leads/sendEmailIntakeFormPC');
    Route::post('leads/addIntakeForm', 'LeadController@addIntakeForm')->name('leads/addIntakeForm');
    Route::post('leads/saveIntakeForm', 'LeadController@saveIntakeForm')->name('leads/saveIntakeForm');

    Route::post('leads/downloadIntakeForm', 'LeadController@downloadIntakeForm')->name('leads/downloadIntakeForm');
    Route::get('forms/{id}/show_pdf', 'LeadController@inlineViewIntakeForm')->name('forms/{id}/show_pdf');
    Route::get('online_lead_forms/{id}/show_pdf', 'LeadController@inlineViewOnlineLeadForm')->name('formsonline_lead_forms/{id}/show_pdf');
    Route::get('pdf/downloadIntakeForm', 'PDFController@downloadIntakeForm')->name('pdf/downloadIntakeForm');
    Route::post('leads/printLead', 'LeadController@printLead')->name('leads/printLead');

    // Route::post('leads/collectFormData', 'LeadController@collectFormData')->name('leads/collectFormData');

    //Invoice
    Route::post('leads/loadInvoices', 'LeadController@loadInvoices')->name('leads/loadInvoices');
    Route::post('leads/addNewInvoices', 'LeadController@addNewInvoices')->name('leads/addNewInvoices');
    Route::post('leads/saveInvoices', 'LeadController@saveInvoices')->name('leads/saveInvoices');
    Route::post('leads/editInvoice', 'LeadController@editInvoice')->name('leads/editInvoice');
    Route::post('leads/updateInvoice', 'LeadController@updateInvoice')->name('leads/updateInvoice');
    Route::post('leads/deleteInvoice', 'LeadController@deleteInvoice')->name('leads/deleteInvoice');
    Route::post('leads/openSendInvoicePopup', 'LeadController@openSendInvoicePopup')->name('leads/openSendInvoicePopup');
    Route::post('leads/sendInvoice', 'LeadController@sendInvoice')->name('leads/sendInvoice');
    Route::post('leads/downloadInvoiceForm', 'LeadController@downloadInvoiceForm')->name('leads/downloadInvoiceForm');
    Route::post('leads/payInvoice', 'LeadController@payInvoice')->name('leads/payInvoice');
    Route::post('leads/savePayment', 'LeadController@savePayment')->name('leads/savePayment');


    //INTAKE FORM
    Route::get('form_templates','IntakeformController@form_templates')->name('form_templates');
    Route::post('load_form_templates','IntakeformController@load_form_templates')->name('load_form_templates');
    Route::get('form_templates/new','IntakeformController@newForm')->name('form_templates/new');
    Route::post('intake_form/loadFields','IntakeformController@loadFeilds')->name('intake_form/loadFields');
    Route::post('intake_form/loadFieldsSelected','IntakeformController@loadFieldsSelected')->name('intake_form/loadFieldsSelected');
    Route::post('intake_form/saveIntakeForm','IntakeformController@saveIntakeForm')->name('intake_form/saveIntakeForm');
    Route::get('form_templates/{id}','IntakeformController@updateIntakeForm')->name('form_templates/view');
    Route::post('intake_form/saveUpdateIntakeForm','IntakeformController@saveUpdateIntakeForm')->name('intake_form/saveUpdateIntakeForm');
    Route::post('intake_form/deleteIntakeForm','IntakeformController@deleteIntakeForm')->name('intake_form/deleteIntakeForm');
    Route::post('intake_form/cloneIntakeForm','IntakeformController@cloneIntakeForm')->name('intake_form/cloneIntakeForm');
    Route::post('intake_form/cloneSaveIntakeForm','IntakeformController@cloneSaveIntakeForm')->name('intake_form/cloneSaveIntakeForm');
    Route::post('intake_form/emailIntakeForm','IntakeformController@emailIntakeForm')->name('intake_form/emailIntakeForm');
    Route::post('intake_form/loadClientCase','IntakeformController@loadClientCase')->name('intake_form/loadClientCase');
    Route::post('intake_form/loadLeadCase','IntakeformController@loadLeadCase')->name('intake_form/loadLeadCase');
    Route::post('intake_form/sentEmailIntakeForm','IntakeformController@sentEmailIntakeForm')->name('intake_form/sentEmailIntakeForm');
    Route::post('intake_form/dismissContactUs', 'IntakeformController@dismissContactUs')->name('intake_form/dismissContactUs');

    //INTAKE FORM

    Route::get('preview', 'PDFController@preview');
    Route::get('download', 'PDFController@download')->name('download');

    //CLIENT Dashboard
    Route::get('contacts/clients/{id}', 'ClientdashboardController@clientDashboardView')->name('contacts/clients/view');
    Route::get('contacts/clients/{id}/cases', 'ClientdashboardController@clientDashboardView')->name('contacts_clients_cases');
    Route::get('contacts/clients/{id}/activity', 'ClientdashboardController@clientDashboardView')->name('contacts_clients_activity');
    Route::get('contacts/clients/{id}/notes', 'ClientdashboardController@clientDashboardView')->name('contacts_clients_notes');
    Route::get('contacts/clients/{id}/billing/trust_history', 'ClientdashboardController@clientDashboardView')->name('contacts_clients_billing_trust_history');
    Route::get('contacts/clients/{id}/billing/request_fund', 'ClientdashboardController@clientDashboardView')->name('contacts_clients_billing_trust_request_fund');
    Route::get('contacts/clients/{id}/billing/invoice', 'ClientdashboardController@clientDashboardView')->name('contacts_clients_billing_invoice');
    Route::get('contacts/clients/{id}/messages', 'ClientdashboardController@clientDashboardView')->name('contacts_clients_messages');
    Route::get('contacts/clients/{id}/text_messages', 'ClientdashboardController@clientDashboardView')->name('contacts_clients_text_messages');
    Route::get('contacts/clients/{id}/email', 'ClientdashboardController@clientDashboardView')->name('contacts_clients_email');
    Route::get('contacts/clients/{id}/billing/credit/history', 'ClientdashboardController@clientDashboardView')->name('contacts/clients/billing/credit/history');
    Route::get('contacts/clients/{id}/billing/trust/allocation', 'ClientdashboardController@clientDashboardView')->name('contacts/clients/billing/trust/allocation');

    Route::post('contacts/clients/casesLoad', 'ClientdashboardController@clientCaseList');
    Route::post('contacts/clients/unlinkFromCase', 'ClientdashboardController@unlinkFromCase');
    Route::post('contacts/clients/addExistingCase', 'ClientdashboardController@addExistingCase');
    Route::post('contacts/clients/loadCaseData', 'ClientdashboardController@loadCaseData');
    Route::post('contacts/clients/saveLinkCase', 'ClientdashboardController@saveLinkCase');
    Route::post('contacts/clients/saveStaffLinkCase', 'ClientdashboardController@saveStaffLinkCase');
    Route::post('contacts/clients/ClientActivityHistory', 'ClientdashboardController@ClientActivityHistory');
    Route::post('contacts/clients/ClientNotes', 'ClientdashboardController@ClientNotes')->name('contacts/clients/ClientNotes');
    Route::post('contacts/clients/addNotesFromDashboard', 'ClientdashboardController@addNotesFromDashboard')->name('contacts/clients/addNotesFromDashboard');
    Route::post('contacts/clients/addNotes', 'ClientdashboardController@addNotes')->name('contacts/clients/addNotes');
    Route::post('contacts/clients/saveNote', 'ClientdashboardController@saveNote')->name('contacts/clients/saveNote');
    Route::post('contacts/clients/saveNoteForDashboard', 'ClientdashboardController@saveNoteForDashboard')->name('contacts/clients/saveNoteForDashboard');
    Route::post('contacts/clients/editNotes', 'ClientdashboardController@editNotes')->name('contacts/clients/editNotes');
    Route::post('contacts/clients/updateNote', 'ClientdashboardController@updateNote')->name('contacts/clients/updateNote');
    Route::post('contacts/clients/discardNote', 'ClientdashboardController@discardNote')->name('contacts/clients/discardNote');
    Route::post('contacts/clients/discardDeleteNote', 'ClientdashboardController@discardDeleteNote')->name('contacts/clients/discardDeleteNote');
    Route::post('contacts/clients/deleteNote', 'ClientdashboardController@deleteNote')->name('contacts/clients/deleteNote');
    Route::post('contacts/clients/loadTimeEntryPopup', 'ClientdashboardController@loadTimeEntryPopup')->name('contacts/clients/loadTimeEntryPopup');
    Route::post('contacts/clients/savebulkTimeEntry', 'ClientdashboardController@savebulkTimeEntry')->name('contacts/clients/savebulkTimeEntry');
    Route::post('contacts/clients/saveTimeEntryPopup', 'ClientdashboardController@saveTimeEntryPopup')->name('contacts/clients/saveTimeEntryPopup');
    
    Route::post('contacts/clients/loadTrustHistory', 'ClientdashboardController@loadTrustHistory')->name('contacts/clients/loadTrustHistory');
    // Made common code for client, comapny and billing in billing controller
    // Route::post('contacts/clients/addTrustEntry', 'ClientdashboardController@addTrustEntry')->name('contacts/clients/addTrustEntry');
    // Route::post('contacts/clients/saveTrustEntry', 'ClientdashboardController@saveTrustEntry')->name('contacts/clients/saveTrustEntry');
    Route::post('contacts/clients/withdrawFromTrust', 'ClientdashboardController@withdrawFromTrust')->name('contacts/clients/withdrawFromTrust');
    Route::post('contacts/clients/saveWithdrawFromTrust', 'ClientdashboardController@saveWithdrawFromTrust')->name('contacts/clients/saveWithdrawFromTrust');
    Route::post('contacts/clients/refundPopup', 'ClientdashboardController@refundPopup')->name('contacts/clients/refundPopup');
    Route::post('contacts/clients/saveRefundPopup', 'ClientdashboardController@saveRefundPopup')->name('contacts/clients/saveRefundPopup');
    Route::post('contacts/clients/deletePaymentEntry', 'ClientdashboardController@deletePaymentEntry')->name('contacts/clients/deletePaymentEntry');
    Route::post('contacts/clients/exportPDFpopupForm', 'ClientdashboardController@exportPDFpopupForm')->name('contacts/clients/exportPDFpopupForm');
    // Route::get('downloadTrustHistory', 'ClientdashboardController@downloadTrustActivity')->name('downloadTrustHistory');
    Route::post('downloadTrustHistory', 'ClientdashboardController@downloadTrustActivity')->name('downloadTrustHistory');
    
    Route::post('contacts/clients/loadRequestedFundHistory', 'ClientdashboardController@loadRequestedFundHistory')->name('contacts/clients/loadRequestedFundHistory');
    Route::post('contacts/clients/addRequestFundPopup', 'ClientdashboardController@addRequestFundPopup')->name('contacts/clients/addRequestFundPopup');
    Route::post('contacts/clients/saveRequestFundPopup', 'ClientdashboardController@saveRequestFundPopup')->name('contacts/clients/saveRequestFundPopup');
    Route::post('contacts/clients/reloadAmount', 'ClientdashboardController@reloadAmount')->name('contacts/clients/reloadAmount');
    Route::post('contacts/clients/addEmailtouser', 'ClientdashboardController@addEmailtouser')->name('contacts/clients/addEmailtouser');
    Route::post('contacts/clients/editFundRequest', 'ClientdashboardController@editFundRequest')->name('contacts/clients/editFundRequest');
    Route::post('contacts/clients/saveEditFundRequest', 'ClientdashboardController@saveEditFundRequest')->name('contacts/clients/saveEditFundRequest');
    Route::post('contacts/clients/deleteRequestedFundEntry', 'ClientdashboardController@deleteRequestedFundEntry')->name('contacts/clients/deleteRequestedFundEntry');
    Route::post('contacts/clients/sendFundReminder', 'ClientdashboardController@sendFundReminder')->name('contacts/clients/sendFundReminder');
    Route::post('contacts/clients/saveSendFundReminder', 'ClientdashboardController@saveSendFundReminder')->name('contacts/clients/saveSendFundReminder');

    Route::post('contacts/clients/archiveContactForm', 'ClientdashboardController@archiveContactForm');
    Route::post('contacts/clients/unarchiveContactForm', 'ClientdashboardController@unarchiveContactForm');
    Route::post('contacts/clients/deleteContactForm', 'ClientdashboardController@deleteContactForm');

    Route::post('contacts/clients/addNewMessagePopup', 'ClientdashboardController@addNewMessagePopup')->name('contacts/clients/addNewMessagePopup');
    Route::post('contacts/clients/checkBeforProceed', 'ClientdashboardController@checkBeforProceed')->name('contacts/clients/checkBeforProceed');
    Route::post('contacts/clients/searchValue', 'ClientdashboardController@searchValue')->name('contacts/clients/searchValue');
    Route::post('contacts/clients/sendNewMessageToUser', 'ClientdashboardController@sendNewMessageToUser')->name('contacts/clients/sendNewMessageToUser');
    Route::post('contacts/clients/uploadImage', 'ClientdashboardController@imageUploadSync')->name('contacts/clients/uploadImage');
    Route::post('contacts/clients/cropImage', 'ClientdashboardController@cropImageSync')->name('contacts/clients/cropImage');
    Route::post('contacts/clients/deleteProfileImageForm', 'ClientdashboardController@deleteProfileImageForm')->name('contacts/clients/deleteProfileImageForm');
    Route::post('contacts/clients/submitAndSaveImageForm', 'ClientdashboardController@submitAndSaveImageForm')->name('contacts/clients/submitAndSaveImageForm');
    Route::post('contacts/clients/updateProfileImageForm', 'ClientdashboardController@updateProfileImageForm')->name('contacts/clients/updateProfileImageForm');
    Route::post('contacts/clients/loadMessagesEntryPopup', 'ClientdashboardController@loadMessagesEntryPopup')->name('contacts/clients/loadMessagesEntryPopup');
    Route::post('contacts/clients/replyMessageToUserCase', 'ClientdashboardController@replyMessageToUserCase')->name('contacts/clients/replyMessageToUserCase');
    Route::post('contacts/clients/archiveMessageToUserCase', 'ClientdashboardController@archiveMessageToUserCase')->name('contacts/clients/archiveMessageToUserCase');
    Route::post('contacts/clients/unarchiveMessageToUserCase', 'ClientdashboardController@unarchiveMessageToUserCase')->name('contacts/clients/unarchiveMessageToUserCase');
    
    Route::post('contacts/changeAccess', 'ClientdashboardController@changeAccessFromDashboard');
    Route::post('contacts/sendWelcomeEmailAgain', 'ClientdashboardController@ReSendWelcomeEmail');
    // Route::get('activate_account/web_token/{id}', 'ClientdashboardController@activeClientAccount');
    Route::post('contacts/saveTrustAmount', 'ClientdashboardController@saveTrustAmount');

    // For client -> billing credit history
    Route::get('contacts/clients/load/credit/history', 'ClientdashboardController@loadCreditHistory')->name('contacts/clients/loadCreditHistory');
    Route::post('contacts/clients/withdrawFromCredit', 'ClientdashboardController@withdrawFromCredit')->name('contacts/clients/withdrawFromCredit');
    Route::post('contacts/clients/saveWithdrawFromCredit', 'ClientdashboardController@saveWithdrawFromCredit')->name('contacts/clients/saveWithdrawFromCredit');
    Route::post('contacts/clients/credit/refundPopup', 'ClientdashboardController@refundCreditPopup')->name('contacts/clients/credit/refundPopup');
    Route::post('contacts/clients/credit/saveRefundPopup', 'ClientdashboardController@saveCreditRefund')->name('contacts/clients/credit/saveRefundPopup');
    Route::post('contacts/clients/deleteCreditHistoryEntry', 'ClientdashboardController@deleteCreditHistoryEntry')->name('contacts/clients/deleteCreditHistoryEntry');
    Route::post('contacts/clients/export/credit/history', 'ClientdashboardController@exportCreditHistory')->name('contacts/clients/export/credit/history');

    // For client -> billing invoice
    Route::get('contacts/clients/load/invoices', 'ClientdashboardController@loadInvoices')->name('contacts/clients/load/invoices');
    
    // For Client -> Trust allocation
    Route::get('contacts/clients/trust/allocation/list', 'ClientdashboardController@listTrustAllocation')->name('contacts/clients/trust/allocation/list');
    Route::post('contacts/clients/save/min/trust/balance', 'ClientdashboardController@saveMinTrustBalance')->name('contacts/clients/save/min/trust/balance');
    Route::get('contacts/clients/trust/allocation/detail', 'ClientdashboardController@getTrustAllocationDetail')->name('contacts/clients/trust/allocation/detail');
    Route::post('contacts/clients/save/trust/allocation', 'ClientdashboardController@saveTrustAllocation')->name('contacts/clients/save/trust/allocation');


    //Company Dashboard
    Route::get('contacts/companies/{id}', 'CompanydashboardController@companyDashboardView')->name('contacts/companies/view');
    Route::get('contacts/companies/{id}/clients', 'CompanydashboardController@companyDashboardView')->name('contacts_company_client');
    Route::get('contacts/companies/{id}/cases', 'CompanydashboardController@companyDashboardView')->name('contacts_company_cases');
    Route::get('contacts/companies/{id}/notes', 'CompanydashboardController@companyDashboardView')->name('contacts_company_notes');
    Route::get('contacts/companies/{id}/billing/trust_history', 'CompanydashboardController@companyDashboardView')->name('contacts_company_billing_trust_history');
    Route::get('contacts/companies/{id}/billing/request_fund', 'CompanydashboardController@companyDashboardView')->name('contacts_company_billing_trust_request_fund');
    Route::get('contacts/companies/{id}/billing/invoice', 'CompanydashboardController@companyDashboardView')->name('contacts_company_billing_invoice');
    Route::get('contacts/companies/{id}/messages', 'CompanydashboardController@companyDashboardView')->name('contacts_company_messages');
    Route::get('contacts/companies/{id}/email', 'CompanydashboardController@companyDashboardView')->name('contacts_company_email');
    Route::get('contacts/companies/{id}/billing/credit/history', 'CompanydashboardController@companyDashboardView')->name('contacts/company/billing/credit/history');
    Route::get('contacts/companies/{id}/billing/trust/allocation', 'CompanydashboardController@companyDashboardView')->name('contacts/companies/billing/trust/allocation');

    Route::post('contacts/companies/clientLoad', 'CompanydashboardController@clientList');
    Route::post('contacts/companies/clientArchiveLoad', 'CompanydashboardController@clientArchiveList');
    Route::post('contacts/companies/addExistingContact', 'CompanydashboardController@addExistingContact');
    Route::post('contacts/companies/loadClientData', 'CompanydashboardController@loadClientData');
    Route::post('contacts/companies/saveLinkContact', 'CompanydashboardController@saveLinkContact');
    Route::post('contacts/companies/saveUnLinkContact', 'CompanydashboardController@saveUnLinkContact');
    Route::post('contacts/companies/casesLoad', 'CompanydashboardController@clientCaseList');
    Route::post('contacts/companies/unlinkFromCase', 'CompanydashboardController@unlinkFromCase');
    Route::post('contacts/companies/addExistingCase', 'CompanydashboardController@addExistingCase');
    Route::post('contacts/companies/loadCaseData', 'CompanydashboardController@loadCaseData');
    Route::post('contacts/companies/saveLinkCase', 'CompanydashboardController@saveLinkCase');

    Route::post('contacts/companies/ClientNotes', 'CompanydashboardController@ClientNotes')->name('contacts/companies/ClientNotes');
    Route::post('contacts/companies/addNotes', 'CompanydashboardController@addNotes')->name('contacts/companies/addNotes');
    Route::post('contacts/companies/saveNote', 'CompanydashboardController@saveNote')->name('contacts/companies/saveNote');
    Route::post('contacts/companies/editNotes', 'CompanydashboardController@editNotes')->name('contacts/companies/editNotes');
    Route::post('contacts/companies/updateNote', 'CompanydashboardController@updateNote')->name('contacts/companies/updateNote');
    Route::post('contacts/companies/discardNote', 'CompanydashboardController@discardNote')->name('contacts/companies/discardNote');
    Route::post('contacts/companies/discardDeleteNote', 'CompanydashboardController@discardDeleteNote')->name('contacts/companies/discardDeleteNote');
    Route::post('contacts/companies/deleteNote', 'CompanydashboardController@deleteNote')->name('contacts/companies/deleteNote');
    Route::post('contacts/companies/loadTimeEntryPopup', 'CompanydashboardController@loadTimeEntryPopup')->name('contacts/companies/loadTimeEntryPopup');
    Route::post('contacts/companies/savebulkTimeEntry', 'CompanydashboardController@savebulkTimeEntry')->name('contacts/companies/savebulkTimeEntry');
    Route::post('contacts/companies/saveTimeEntryPopup', 'CompanydashboardController@saveTimeEntryPopup')->name('contacts/companies/saveTimeEntryPopup');
    
    
    Route::post('contacts/companies/loadTrustHistory', 'CompanydashboardController@loadTrustHistory')->name('contacts/companies/loadTrustHistory');
    // Made common code for client and company in client dashboard controller and billing controller
    // Route::post('contacts/companies/addTrustEntry', 'CompanydashboardController@addTrustEntry')->name('contacts/companies/addTrustEntry');
    // Route::post('contacts/companies/saveTrustEntry', 'CompanydashboardController@saveTrustEntry')->name('contacts/companies/saveTrustEntry');
    // Route::post('contacts/companies/withdrawFromTrust', 'CompanydashboardController@withdrawFromTrust')->name('contacts/companies/withdrawFromTrust');
    // Route::post('contacts/companies/saveWithdrawFromTrust', 'CompanydashboardController@saveWithdrawFromTrust')->name('contacts/companies/saveWithdrawFromTrust');
    // Route::post('contacts/companies/refundPopup', 'CompanydashboardController@refundPopup')->name('contacts/companies/refundPopup');
    // Route::post('contacts/companies/saveRefundPopup', 'CompanydashboardController@saveRefundPopup')->name('contacts/companies/saveRefundPopup');
    // Route::post('contacts/companies/deletePaymentEntry', 'CompanydashboardController@deletePaymentEntry')->name('contacts/companies/deletePaymentEntry');
    Route::post('contacts/companies/exportPDFpopupForm', 'CompanydashboardController@exportPDFpopupForm')->name('contacts/companies/exportPDFpopupForm');
    Route::post('contacts/companies/downloadTrustHistory', 'CompanydashboardController@downloadTrustActivity')->name('contacts/companies/downloadTrustHistory');
    
    
    Route::post('contacts/companies/loadRequestedFundHistory', 'CompanydashboardController@loadRequestedFundHistory')->name('contacts/companies/loadRequestedFundHistory');
    Route::post('contacts/companies/addRequestFundPopup', 'CompanydashboardController@addRequestFundPopup')->name('contacts/companies/addRequestFundPopup');
    Route::post('contacts/companies/saveRequestFundPopup', 'CompanydashboardController@saveRequestFundPopup')->name('contacts/companies/saveRequestFundPopup');
    Route::post('contacts/companies/reloadAmount', 'CompanydashboardController@reloadAmount')->name('contacts/companies/reloadAmount');
    Route::post('contacts/companies/addEmailtouser', 'CompanydashboardController@addEmailtouser')->name('contacts/companies/addEmailtouser');
    Route::post('contacts/companies/editFundRequest', 'CompanydashboardController@editFundRequest')->name('contacts/companies/editFundRequest');
    Route::post('contacts/companies/saveEditFundRequest', 'CompanydashboardController@saveEditFundRequest')->name('contacts/companies/saveEditFundRequest');
    Route::post('contacts/companies/deleteRequestedFundEntry', 'CompanydashboardController@deleteRequestedFundEntry')->name('contacts/companies/deleteRequestedFundEntry');
    Route::post('contacts/companies/sendFundReminder', 'CompanydashboardController@sendFundReminder')->name('contacts/companies/sendFundReminder');
    Route::post('contacts/companies/saveSendFundReminder', 'CompanydashboardController@saveSendFundReminder')->name('contacts/companies/saveSendFundReminder');


    Route::post('contacts/companies/addNewMessagePopup', 'CompanydashboardController@addNewMessagePopup')->name('contacts/companies/addNewMessagePopup');
    Route::post('contacts/companies/checkBeforProceed', 'CompanydashboardController@checkBeforProceed')->name('contacts/companies/checkBeforProceed');
    Route::post('contacts/companies/searchValue', 'CompanydashboardController@searchValue')->name('contacts/companies/searchValue');
    Route::post('contacts/companies/sendNewMessageToUser', 'CompanydashboardController@sendNewMessageToUser')->name('contacts/companies/sendNewMessageToUser');
    Route::post('contacts/companies/saveTrustAmount', 'CompanydashboardController@saveTrustAmount');

    Route::post('contacts/companies/deleteCompanyPopup', 'CompanydashboardController@deleteCompanyPopup');
    Route::post('contacts/companies/deleteCompany', 'CompanydashboardController@deleteCompany');
    Route::post('contacts/companies/archiveCompanyPopup', 'CompanydashboardController@archiveCompanyPopup');
    Route::post('contacts/companies/archiveCompany', 'CompanydashboardController@archiveCompany');
    Route::post('contacts/companies/unarchiveCompanyPopup', 'CompanydashboardController@unarchiveCompanyPopup');
    Route::post('contacts/companies/unarchiveCompany', 'CompanydashboardController@unarchiveCompany');


    //Firm Setting
    Route::get('firms/setting', 'FirmController@index')->name('firms/setting');
    Route::post('firms/updateFirm', 'FirmController@updateFirm')->name('firms/updateFirm');
    Route::post('firms/addNewFirm', 'FirmController@addNewFirm')->name('firms/addNewFirm');
    Route::post('firms/saveNewFirm', 'FirmController@saveNewFirm')->name('firms/saveNewFirm');
    Route::post('firms/editFirm', 'FirmController@editFirm')->name('firms/editFirm');
    Route::post('firms/UpdateNewFirm', 'FirmController@UpdateNewFirm')->name('firms/UpdateNewFirm');
    Route::post('firms/deleteFirm', 'FirmController@deleteFirm')->name('firms/deleteFirm');
    Route::post('firms/editPreferance', 'FirmController@editPreferance')->name('firms/editPreferance');

    //Billing Module
    Route::get('bills/dashboard', 'BillingController@dashboard')->name('bills/dashboard');
    Route::get('bills/time_entries', 'BillingController@time_entries')->name('bills/time_entries');
    Route::get('bills/expenses', 'BillingController@expenses')->name('bills/expenses');
    Route::get('bills/retainer_requests', 'BillingController@retainer_requests')->name('bills/retainer_requests');
    Route::get('bills/activities', 'BillingController@activities')->name('bills/activities');
    Route::get('bills/invoices', 'BillingController@invoices')->name('bills/invoices');
    Route::post('bills/getrate', 'BillingController@getRate')->name('bills/getrate');



    Route::post('bills/time_entries/loadTimeEntry', 'BillingController@loadTimeEntry')->name('bills/time_entries/loadTimeEntry');
    Route::post('bills/loadTimeEntryPopup', 'BillingController@loadTimeEntryPopup')->name('bills/loadTimeEntryPopup');
    Route::post('bills/loadTimeEntryPopupDontRefresh', 'BillingController@loadTimeEntryPopupDontRefresh')->name('bills/loadTimeEntryPopupDontRefresh');

    Route::post('bills/loadEditTimeEntryPopup', 'BillingController@loadEditTimeEntryPopup')->name('bills/loadEditTimeEntryPopup');
    Route::post('bills/updatedTimeEntryPopup', 'BillingController@updatedTimeEntryPopup')->name('bills/updatedTimeEntryPopup');
    Route::post('bills/deleteTimeEntryForm', 'BillingController@deleteTimeEntryForm')->name('bills/deleteTimeEntryForm');
    
    Route::post('bills/expenses/loadExpensesEntry', 'BillingController@loadExpensesEntry')->name('bills/expenses/loadExpensesEntry');
    Route::post('bills/expenses/loadExpenseEntryPopup', 'BillingController@loadExpenseEntryPopup')->name('bills/expenses/loadExpenseEntryPopup');
    Route::post('bills/expenses/saveExpenseEntryPopup', 'BillingController@saveExpenseEntryPopup')->name('bills/expenses/saveExpenseEntryPopup');
    Route::post('bills/expenses/saveExpenseBulkEntryPopup', 'BillingController@saveExpenseBulkEntryPopup')->name('bills/expenses/saveExpenseBulkEntryPopup');
    Route::post('bills/expenses/loadEditExpenseEntryPopup', 'BillingController@loadEditExpenseEntryPopup')->name('bills/expenses/loadEditExpenseEntryPopup');
    Route::post('bills/expenses/updateEditExpenseEntry', 'BillingController@updateEditExpenseEntry')->name('bills/expenses/updateEditExpenseEntry');
    Route::post('bills/expenses/deleteExpenseEntryForm', 'BillingController@deleteExpenseEntryForm')->name('bills/expenses/deleteExpenseEntryForm');
    Route::post('bills/expenses/deleteExpenseEntryFormUnique', 'BillingController@deleteExpenseEntryForm')->name('bills/expenses/deleteExpenseEntryFormUnique');
    Route::post('bills/expenses/deleteBulkExpenseEntryForm', 'BillingController@deleteBulkExpenseEntryForm')->name('bills/expenses/deleteBulkExpenseEntryForm');
    Route::post('bills/expenses/bulkAssignCase', 'BillingController@bulkAssignCase')->name('bills/expenses/bulkAssignCase');
    Route::post('bills/expenses/saveBulkAssignCase', 'BillingController@saveBulkAssignCase')->name('bills/expenses/saveBulkAssignCase');
    Route::post('bills/expenses/bulkAssignUser', 'BillingController@bulkAssignUser')->name('bills/expenses/bulkAssignUser');
    Route::post('bills/expenses/saveBulkAssignUser', 'BillingController@saveBulkAssignUser')->name('bills/expenses/saveBulkAssignUser');
    
    Route::post('bills/retainer_requests/loadRetainerRequestsEntry', 'BillingController@loadRetainerRequestsEntry')->name('bills/retainer_requests/loadRetainerRequestsEntry');
    
    Route::post('bills/activities/loadActivity', 'BillingController@loadActivity')->name('bills/activities/loadActivity');
    Route::post('bills/activities/newActivity', 'BillingController@newActivity')->name('bills/activities/newActivity');
    Route::post('bills/activities/saveActivity', 'BillingController@saveActivity')->name('bills/activities/saveActivity');
    Route::post('bills/activities/editActivity', 'BillingController@editActivity')->name('bills/activities/editActivity');
    Route::post('bills/activities/updateActivity', 'BillingController@updateActivity')->name('bills/activities/updateActivity');
    Route::post('bills/activities/deleteActivity', 'BillingController@deleteActivity')->name('bills/activities/deleteActivity');
    

    Route::post('bills/invoices/loadInvoices', 'BillingController@loadInvoices')->name('bills/invoices/loadInvoices');
    Route::post('bills/invoices/loadBatchInvoices', 'BillingController@loadBatchInvoices')->name('bills/invoices/loadBatchInvoices');

    Route::post('bills/invoices/sendInvoiceReminder', 'BillingController@sendInvoiceReminder')->name('bills/invoices/sendInvoiceReminder');
    Route::post('bills/invoices/saveInvoiceReminder', 'BillingController@saveInvoiceReminder')->name('bills/invoices/saveInvoiceReminder');
    Route::post('bills/invoices/payInvoicePopup', 'BillingController@payInvoicePopup')->name('bills/invoices/payInvoicePopup');
    Route::post('bills/invoices/saveTrustInvoicePayment', 'BillingController@saveTrustInvoicePayment')->name('bills/invoices/saveTrustInvoicePayment');
    Route::post('bills/invoices/saveInvoicePayment', 'BillingController@saveInvoicePayment')->name('bills/invoices/saveInvoicePayment');
    Route::post('bills/invoices/deleteInvoiceForm', 'BillingController@deleteInvoiceForm')->name('bills/invoices/deleteInvoiceForm');
    Route::post('bills/invoices/deleteLeadInvoiceForm', 'BillingController@deleteLeadInvoiceForm')->name('bills/invoices/deleteLeadInvoiceForm');
    Route::get('bills/invoices/paymentHistory', 'BillingController@invoicePaymentHistory')->name('bills/invoices/paymentHistory');
    Route::get('bills/invoices/activityHistory', 'BillingController@invoiceActivityHistory')->name('bills/invoices/activityHistory');
    Route::get('bills/invoices/refreshAccountHistory', 'BillingController@invoiceAccountHistory')->name('bills/invoices/refreshAccountHistory');
    
    Route::get('bills/invoices/open', 'BillingController@open')->name('bills/invoices/open');
    Route::post('bills/invoices/loadUpcomingInvoices', 'BillingController@loadUpcomingInvoices')->name('bills/invoices/loadUpcomingInvoices');
    Route::post('bills/invoices/loadUpcomingInvoicesWithLoader', 'BillingController@loadUpcomingInvoicesWithLoader')->name('bills/invoices/loadUpcomingInvoicesWithLoader');
    Route::get('bills/invoices/new', 'BillingController@newInvoiceScratch')->name('bills/invoices/new');
    Route::get('bills/invoices/load_new', 'BillingController@newInvoice')->name('bills/invoices/load_new');
    Route::post('bills/invoices/deleteTimeEntry', 'BillingController@deleteTimeEntry')->name('bills/invoices/deleteTimeEntry');
    Route::post('bills/invoices/deleteAllTimeEntry', 'BillingController@deleteAllTimeEntry')->name('bills/invoices/deleteAllTimeEntry');
    Route::post('bills/invoices/addSingleTimeEntry', 'BillingController@addSingleTimeEntry')->name('bills/invoices/addSingleTimeEntry');
    Route::post('bills/invoices/saveSingleTimeEntry', 'BillingController@saveSingleTimeEntry')->name('bills/invoices/saveSingleTimeEntry');
    Route::post('bills/invoices/editSingleTimeEntry', 'BillingController@editSingleTimeEntry')->name('bills/invoices/editSingleTimeEntry');
    Route::post('bills/invoices/updateSingleTimeEntry', 'BillingController@updateSingleTimeEntry')->name('bills/invoices/updateSingleTimeEntry');
    Route::post('bills/invoices/deleteAllFlatFeeEntry', 'BillingController@deleteAllFlatFeeEntry')->name('bills/invoices/deleteAllFlatFeeEntry');
    Route::get('bills/invoices/save/nonbillable/check', 'BillingController@saveNonbillableCheck')->name('bills/invoices/save/nonbillable/check');
    Route::get('bills/invoices/save/forwardInvoice/check', 'BillingController@saveforwardInvoiceCheck')->name('bills/invoices/save/forwardInvoice/check');

    Route::post('bills/invoices/addSingleFlatFeeEntry', 'BillingController@addSingleFlatFeeEntry')->name('bills/invoices/addSingleFlatFeeEntry');
    Route::post('bills/invoices/saveSingleFlatFeeEntry', 'BillingController@saveSingleFlatFeeEntry')->name('bills/invoices/saveSingleFlatFeeEntry');
    Route::post('bills/invoices/editSingleFlatFeeEntry', 'BillingController@editSingleFlatFeeEntry')->name('bills/invoices/editSingleFlatFeeEntry');
    Route::post('bills/invoices/updateSingleFlatFeeEntry', 'BillingController@updateSingleFlatFeeEntry')->name('bills/invoices/updateSingleFlatFeeEntry');

    Route::post('bills/invoices/deleteFlatFeeEntry', 'BillingController@deleteFlatFeeEntry')->name('bills/invoices/deleteFlatFeeEntry');
    Route::post('bills/invoices/deleteAdustmentEntry', 'BillingController@deleteAdustmentEntry')->name('bills/invoices/deleteAdustmentEntry');
    Route::post('bills/invoices/deleteExpenseEntry', 'BillingController@deleteExpenseEntry')->name('bills/invoices/deleteExpenseEntry');
    Route::post('bills/invoices/deleteAllExpenseEntry', 'BillingController@deleteAllExpenseEntry')->name('bills/invoices/deleteAllExpenseEntry');
    Route::post('bills/invoices/addSingleExpenseEntry', 'BillingController@addSingleExpenseEntry')->name('bills/invoices/addSingleExpenseEntry');
    Route::post('bills/invoices/saveSingleExpenseEntry', 'BillingController@saveSingleExpenseEntry')->name('bills/invoices/saveSingleExpenseEntry');
    Route::post('bills/invoices/editSingleExpenseEntry', 'BillingController@editSingleExpenseEntry')->name('bills/invoices/editSingleExpenseEntry');
    Route::post('bills/invoices/updateSingleExpenseEntry', 'BillingController@updateSingleExpenseEntry')->name('bills/invoices/updateSingleExpenseEntry');

    Route::post('bills/invoices/addAdjustmentEntry', 'BillingController@addAdjustmentEntry')->name('bills/invoices/addAdjustmentEntry');
    Route::post('bills/invoices/saveAdjustmentEntry', 'BillingController@saveAdjustmentEntry')->name('bills/invoices/saveAdjustmentEntry');
    Route::post('bills/invoices/editAdjustmentEntry', 'BillingController@editAdjustmentEntry')->name('bills/invoices/editAdjustmentEntry');
    Route::post('bills/invoices/updateAdjustmentEntry', 'BillingController@updateAdjustmentEntry')->name('bills/invoices/updateAdjustmentEntry');
    Route::post('bills/invoices/graantAccess', 'BillingController@graantAccess')->name('bills/invoices/graantAccess');
    Route::post('bills/invoices/addInvoiceEntry', 'BillingController@addInvoiceEntry')->name('bills/invoices/addInvoiceEntry');
    Route::post('bills/invoices/updateInvoiceEntry', 'BillingController@updateInvoiceEntry')->name('bills/invoices/updateInvoiceEntry');
    Route::post('bills/invoices/getAddress', 'BillingController@getAddress')->name('bills/invoices/getAddress');
    Route::post('bills/invoices/checkAccess', 'BillingController@checkAccess')->name('bills/invoices/checkAccess');
    Route::post('bills/invoices/changeAccess', 'BillingController@changeAccess')->name('bills/invoices/changeAccess');
    Route::post('bills/invoices/reloadRow', 'BillingController@reloadRow')->name('bills/invoices/reloadRow');
    Route::post('bills/invoices/getCaseList', 'BillingController@getCaseList')->name('bills/invoices/getCaseList');
    Route::post('bills/invoices/removeAdjustmentEntry', 'BillingController@removeAdjustmentEntry')->name('bills/invoices/removeAdjustmentEntry');

    Route::get('bills/invoices/view/{id}', 'BillingController@viewInvoice')->name('bills/invoices/view');
    Route::post('bills/invoices/deleteInvoice', 'BillingController@deleteInvoice')->name('bills/invoices/deleteInvoice');
    Route::post('bills/invoices/shareInvoice', 'BillingController@shareInvoice')->name('bills/invoices/shareInvoice');
    Route::post('bills/invoices/saveShareInvoice', 'BillingController@saveShareInvoice')->name('bills/invoices/saveShareInvoice');
    Route::post('bills/invoices/view/checkAccessFromViewInvoice', 'BillingController@checkAccessFromViewInvoice')->name('bills/invoices/view/checkAccessFromViewInvoice');
    Route::post('bills/invoices/view/reloadRowForViewInvoice', 'BillingController@reloadRowForViewInvoice')->name('bills/invoices/view/reloadRowForViewInvoice');
    Route::post('bills/invoices/sendReminder', 'BillingController@sendReminder')->name('bills/invoices/sendReminder');
    Route::post('bills/invoices/saveSendReminder', 'BillingController@saveSendReminder')->name('bills/invoices/saveSendReminder');
    Route::get('bills/invoices/downloadInvoice/{id}', 'BillingController@downloaInvoivePdfView')->name('bills/invoices/downloadInvoice');
    Route::post('bills/invoices/downloadInvoice', 'BillingController@downloaInvoivePdf')->name('bills/invoices/downloadInvoice');
    Route::post('bills/invoices/emailInvoice', 'BillingController@emailInvoice')->name('bills/invoices/emailInvoice');
    Route::post('bills/invoices/SendEmailInvoice', 'BillingController@saveSendReminderWithAttachment')->name('bills/invoices/SendEmailInvoice');

    Route::get('bills/invoices/invoiceInlineView/{id}', 'BillingController@invoiceInlineView')->name('bills/invoices/invoiceInlineView');

    Route::get('bills/invoices/{id}/edit', 'BillingController@editInvoice')->name('bills/invoices/edit');
    Route::post('bills/invoices/resendUpdatedInvoice', 'BillingController@resendUpdatedInvoice')->name('bills/invoices/resendUpdatedInvoice');
    Route::post('bills/invoices/payInvoice', 'BillingController@payInvoice')->name('bills/invoices/payInvoice');
    Route::post('bills/invoices/saveTrustInvoicePaymentWithHistory', 'BillingController@saveTrustInvoicePaymentWithHistory')->name('bills/invoices/saveTrustInvoicePaymentWithHistory');
    // Route::post('bills/invoices/saveInvoicePaymentWithHistory', 'BillingController@saveInvoicePaymentWithHistory')->name('bills/invoices/saveInvoicePaymentWithHistory');
    Route::post('bills/invoices/refundPopup', 'BillingController@refundPopup')->name('bills/invoices/refundPopup');
    Route::post('bills/invoices/saveRefundPopup', 'BillingController@saveRefundPopup')->name('bills/invoices/saveRefundPopup');
    Route::post('bills/invoices/deletePaymentEntry', 'BillingController@deletePaymentEntry')->name('bills/invoices/deletePaymentEntry');
    Route::post('bills/invoices/InvoiceHistoryInlineView', 'BillingController@InvoiceHistoryInlineView')->name('bills/invoices/InvoiceHistoryInlineView');
    Route::post('bills/invoices/setBulkStatusActionForm', 'BillingController@setBulkStatusActionForm')->name('bills/invoices/setBulkStatusActionForm');
    Route::post('bills/invoices/setBulkSharesActionForm', 'BillingController@setBulkSharesActionForm')->name('bills/invoices/setBulkSharesActionForm');
    Route::post('bills/invoices/deleteBulkInvoiceForm', 'BillingController@deleteBulkInvoice')->name('bills/invoices/deleteBulkInvoiceForm');
    Route::post('bills/invoices/adjustmentBulkInvoiceForm', 'BillingController@adjustmentBulkInvoiceForm')->name('bills/invoices/adjustmentBulkInvoiceForm');
    Route::post('bills/invoices/downloadBulkInvoice', 'BillingController@downloadBulkInvoice')->name('bills/invoices/downloadBulkInvoice');
    Route::post('bills/invoices/applyTrustBalanceForm', 'BillingController@applyTrustBalanceForm')->name('bills/invoices/applyTrustBalanceForm');
    Route::post('bills/invoices/trustBalanceResponse', 'BillingController@trustBalanceResponse')->name('bills/invoices/trustBalanceResponse');
    Route::post('bills/invoices/save/credit/payment', 'BillingController@saveInvoicePaymentFromCredit')->name('bills/invoices/save/credit/payment');
    Route::post('bills/invoices/applyCreditBalanceForm', 'BillingController@applyCreditBalanceForm')->name('bills/invoices/applyCreditBalanceForm');
    Route::post('bills/invoices/creditBalanceResponse', 'BillingController@creditBalanceResponse')->name('bills/invoices/creditBalanceResponse');

    //Account Activity
    Route::get('bills/account_activity', 'BillingController@account_activity')->name('bills/account_activity');
    Route::post('bills/activities/loadAccountActivity', 'BillingController@loadAccountActivity')->name('bills/activities/loadAccountActivity');
    Route::post('bills/activities/loadMixAccountActivity', 'BillingController@loadMixAccountActivity')->name('bills/activities/loadMixAccountActivity');
    Route::get('bills/trust_account_activity', 'BillingController@trust_account_activity')->name('bills/trust_account_activity');
    Route::post('bills/activities/loadTrustAccountActivity', 'BillingController@loadTrustAccountActivity')->name('bills/activities/loadTrustAccountActivity');

    //Potentail case invoice
    Route::get('bills/invoices/potentialview/{id}', 'BillingController@viewPotentailInvoice')->name('bills/invoices/potentialview');


    //Financial Insights
    Route::get('insights/financials', 'BillingController@insights_financials')->name('insights/financials');
    Route::post('insights/financials/printInsightActivity', 'BillingController@printInsightActivity')->name('insights/financials/printInsightActivity');
    Route::get('insights/financials/printInsightActivity', 'BillingController@printInsightActivity')->name('insights/financials/printInsightActivity');
    
    //Dashboard [Billing]
    Route::post('bills/dashboard/recordPayment', 'BillingController@recordPayment')->name('dashboard/recordPayment');
    Route::post('bills/dashboard/depositIntoTrust', 'BillingController@depositIntoTrust')->name('dashboard/depositIntoTrust');
    Route::post('bills/dashboard/depositIntoTrust/clientCases', 'BillingController@depositIntoTrustClientCase')->name('dashboard/depositIntoTrust/clientCases');
    // Made common code for trust deposit
    // Route::post('bills/dashboard/depositIntoTrustByCase', 'BillingController@depositIntoTrustByCase')->name('dashboard/depositIntoTrustByCase');
    
    Route::post('bills/dashboard/depositIntoTrustPopup', 'BillingController@depositIntoTrustPopup')->name('dashboard/depositIntoTrustPopup');
    Route::post('bills/dashboard/saveDepositIntoTrustPopup', 'BillingController@saveDepositIntoTrustPopup')->name('dashboard/saveDepositIntoTrustPopup');
    Route::post('bills/dashboard/loadExpenseHistory', 'BillingController@loadExpenseHistory')->name('bills/dashboard/loadExpenseHistory');
    Route::post('bills/dashboard/loadTimeEntryHistory', 'BillingController@loadTimeEntryHistory')->name('bills/dashboard/loadTimeEntryHistory');
    Route::post('bills/dashboard/loadInvoiceHistory', 'BillingController@loadInvoiceHistory')->name('bills/dashboard/loadInvoiceHistory');
    Route::post('bills/dashboard/loadAllHistory', 'BillingController@loadAllHistory')->name('bills/dashboard/loadAllHistory');
    Route::post('bills/dashboard/loadTimeEntryOverview', 'BillingController@loadTimeEntryOverview')->name('bills/dashboard/loadTimeEntryOverview');
    Route::post('bills/dashboard/loadInvoiceOverview', 'BillingController@loadInvoiceOverview')->name('bills/dashboard/loadInvoiceOverview');
    Route::post('bills/dashboard/loadTrustAccountOverview', 'BillingController@loadTrustAccountOverview')->name('bills/dashboard/loadTrustAccountOverview');
    Route::post('bills/dashboard/loadCalender', 'BillingController@loadCalender')->name('bills/dashboard/loadCalender');
    Route::post('bills/dashboard/loadDataOnly', 'BillingController@loadDataOnly')->name('bills/dashboard/loadDataOnly');
    Route::post('bills/dashboard/loadSummary', 'BillingController@loadSummary')->name('bills/dashboard/loadSummary');
    Route::post('bills/dashboard/saveDailyGoal', 'BillingController@saveDailyGoal')->name('bills/dashboard/saveDailyGoal');
    Route::post('bills/dashboard/deleteGoalEntry', 'BillingController@deleteGoalEntry')->name('bills/dashboard/deleteGoalEntry');
    Route::post('bills/dashboard/loadDepositIntoCredit', 'BillingController@loadDepositIntoCredit')->name('bills/dashboard/loadDepositIntoCredit');
    Route::post('bills/dashboard/depositIntoNonTrustPopup', 'BillingController@depositIntoNonTrustPopup')->name('bills/dashboard/depositIntoNonTrustPopup');
    Route::post('bills/dashboard/saveDepositIntoNonTrustPopup', 'BillingController@saveDepositIntoNonTrustPopup')->name('bills/dashboard/saveDepositIntoNonTrustPopup');
    
     //Timesheet
     Route::get('time_entries/timesheet_calendar', 'BillingController@viewTimesheet')->name('time_entries/timesheet_calendar');
     Route::post('time_entries/timesheet_calendar/loadAllSavedTimeEntry', 'BillingController@loadAllSavedTimeEntry')->name('time_entries/timesheet_calendar/loadAllSavedTimeEntry');
     Route::post('time_entries/timesheet_calendar/reloadTimeEntry', 'BillingController@reloadTimeEntry')->name('time_entries/timesheet_calendar/reloadTimeEntry');
     
    Route::post('bills/invoices/createInvoiceBatch', 'BillingController@createInvoiceBatch')->name('bills/invoices/createInvoiceBatch');

    //Payment Plan
    Route::get('payment_plans', 'BillingController@paymentPlans')->name('payment_plans');
    Route::post('payment_plans/loadPlannedPayment', 'BillingController@loadPlannedPayment')->name('payment_plans/loadPlannedPayment');
    Route::post('payment_plans/loadAveragePlannedPayment', 'BillingController@loadAveragePlannedPayment')->name('payment_plans/loadAveragePlannedPayment');
    Route::post('payment_plans/PaymentInstallmentsOverTime', 'BillingController@PaymentInstallmentsOverTime')->name('payment_plans/PaymentInstallmentsOverTime');
    Route::post('payment_plans/loadAllPlans', 'BillingController@loadAllPlans')->name('payment_plans/loadAllPlans');

    
    //Prints
    Route::post('bills/invoices/printTimeEntry', 'BillingController@printTimeEntry')->name('bills/invoices/printTimeEntry');
    Route::get('bills/invoices/printTimeEntry', 'BillingController@printTimeEntry')->name('bills/invoices/printTimeEntry');
    Route::post('bills/invoices/printExpenseEntry', 'BillingController@printExpenseEntry')->name('bills/invoices/printExpenseEntry');
    Route::get('bills/invoices/printExpenseEntry', 'BillingController@printExpenseEntry')->name('bills/invoices/printExpenseEntry');
    Route::post('bills/invoices/printRequestFundEntry', 'BillingController@printRequestFundEntry')->name('bills/invoices/printRequestFundEntry');
    Route::get('bills/invoices/printRequestFundEntry', 'BillingController@printRequestFundEntry')->name('bills/invoices/printRequestFundEntry');
    Route::post('bills/invoices/printSavedActivity', 'BillingController@printSavedActivity')->name('bills/invoices/printSavedActivity');
    Route::get('bills/invoices/printSavedActivity', 'BillingController@printSavedActivity')->name('bills/invoices/printSavedActivity');
    Route::post('bills/invoices/printTrustAccountActivity', 'BillingController@printTrustAccountActivity')->name('bills/invoices/printTrustAccountActivity');
    Route::get('bills/invoices/printTrustAccountActivity', 'BillingController@printTrustAccountActivity')->name('bills/invoices/printTrustAccountActivity');
    Route::post('bills/invoices/printAccountActivity', 'BillingController@printAccountActivity')->name('bills/invoices/printAccountActivity');
    Route::get('bills/invoices/printAccountActivity', 'BillingController@printAccountActivity')->name('bills/invoices/printAccountActivity');


    Route::get('imports/contacts', 'ClientdashboardController@imports_contacts')->name('imports/contacts');
    Route::get('imports/court_cases', 'ClientdashboardController@imports_cases')->name('imports/court_cases');

    Route::post('imports/download_template', 'ClientdashboardController@downloadFormat')->name('imports/download_template');
    Route::post('imports/createAndImports', 'ClientdashboardController@createAndImports')->name('imports/createAndImports');
    Route::post('imports/importContacts', 'ClientdashboardController@importContacts')->name('imports/importContacts');
    Route::post('imports/loadImportHistory', 'ClientdashboardController@loadImportHistory')->name('imports/loadImportHistory');
    Route::get('imports/{id}', 'ClientdashboardController@viewLog')->name('imports/{id}');
    Route::post('imports/revertImport', 'ClientdashboardController@revertImport')->name('imports/revertImport');
    Route::post('imports/loadErrorData', 'ClientdashboardController@loadErrorData')->name('imports/loadErrorData');

    // exports
    Route::post('imports/exportCases', 'ClientdashboardController@exportCases')->name('imports/exportCases');
    Route::post('imports/importCases', 'ClientdashboardController@importCases')->name('imports/importCases');
    Route::post('imports/loadImportCasesHistory', 'ClientdashboardController@loadImportCasesHistory')->name('imports/loadImportCasesHistory');
    Route::get('imports/cases/{id}', 'ClientdashboardController@viewCasesLog')->name('imports/cases/{id}');
    Route::post('imports/revertImportCases', 'ClientdashboardController@revertImportCases')->name('imports/revertImportCases');
    
    Route::get('exports', 'ClientdashboardController@exportFullBackup')->name('exports');
    Route::post('imports/backupCases', 'ClientdashboardController@backupCases')->name('imports/backupCases');
    Route::post('imports/loadFullBackupHistory', 'ClientdashboardController@loadFullBackupHistory')->name('imports/loadFullBackupHistory');
    //Scripts
    Route::get('assignTokenToInvoice','CustomScriptController@assignTokenToInvoice')->name('assignTokenToInvoice');


    Route::get('print', 'BillingController@printView')->name('print');
    Route::post('bills/loadCaseList', 'BillingController@loadCaseList')->name('bills/loadCaseList');
    
    // For Billing & Invoice Settings
    Route::get('billing/settings', 'BillingSettingController@index')->name('billing/settings');
    Route::get('billing/settings/edit/preferences', 'BillingSettingController@editPreferences')->name('billing/settings/edit/preferences');
    Route::post('billing/settings/update/preferences', 'BillingSettingController@updatePreferences')->name('billing/settings/update/preferences');
    Route::get('billing/settings/view/preferences', 'BillingSettingController@viewPreferences')->name('billing/settings/view/preferences');
    Route::get('billing/settings/edit/customization', 'BillingSettingController@editCustomization')->name('billing/settings/edit/customization');
    Route::post('billing/settings/update/customization', 'BillingSettingController@updateCustomization')->name('billing/settings/update/customization');
    Route::get('billing/settings/view/customization', 'BillingSettingController@viewCustomization')->name('billing/settings/view/customization');

    //messages
    Route::get('messages/{id}/info','ClientdashboardController@messageInfo')->name('messages/info');

    // feedback
    Route::post('saveFeedback', 'HomeController@saveFeedback')->name('saveFeedback');
    
});

/**
 * For client portal
 */
Route::group(['middleware' => ['auth', 'role:client', 'clientportal.access'], 'namespace' => "ClientPortal", 'prefix' => 'client'], function () {
    Route::get('home', 'HomeController@index')->name("client/home");
    Route::get('notifications', 'HomeController@allNotification')->name("client/notifications");

    // For billing > invoice
    Route::get('bills', 'BillingController@index')->name('client/bills');
    Route::get('bills/{id}', 'BillingController@show')->name('client/bills/detail');
    Route::get('bills/invoices/download/{id}', 'BillingController@downloaInvoivePdf')->name('client/bills/invoices/download');

    // For billing > fund request
    Route::get('bills/request/{id}', 'BillingController@showFundRequest')->name('client/bills/request/detail');

    // For events
    Route::get('events', 'EventController@index')->name('client/events');
    Route::get('events/{id}', 'EventController@show')->name('client/events/detail');
    Route::post('events/save/comment', 'EventController@saveComment')->name('client/events/save/comment');
    Route::get('events/comment/history', 'EventController@eventCommentHistory')->name('client/events/comment/history');
    
    // For profile settings
    Route::get('account', 'ProfileController@edit')->name('client/account');
    Route::post('account/save', 'ProfileController@update')->name('client/account/save');
    Route::post('account/change/password', 'ProfileController@changePassword')->name('client/change/password');
    Route::get('account/preferences', 'ProfileController@edit')->name('client/account/preferences');
    Route::post('account/save/preferences', 'ProfileController@savePreferences')->name('client/account/save/preferences');

    // For tasks
    Route::get('tasks', 'TaskController@index')->name('client/tasks');
    Route::get('tasks/completed', 'TaskController@index')->name('client/tasks/completed');
    Route::get('tasks/{id}', 'TaskController@show')->name('client/tasks/detail');
    Route::get('tasks/update/detail', 'TaskController@updateDetail')->name('client/tasks/update/detail');
    Route::post('tasks/save/comment', 'TaskController@saveComment')->name('client/tasks/save/comment');
    Route::get('tasks/comment/history', 'TaskController@taskCommentHistory')->name('client/tasks/comment/history');
});

//Without login 
Route::get('clientPortal/bills/{id}', 'BillingController@ClientViewInvoice')->name('clientPortal/bills/{id}');
Route::get('bills/invoice/{id}', 'LeadController@viewInvoice')->name('bills/invoice/{id}');
Route::get('bills/invoicepdf/{id}', 'LeadController@viewInvoiceForPdf')->name('bills/invoicepdf/{id}');
Route::get('preview/{id}','IntakeformController@formPreview')->name('preview/{id}');
Route::get('form/{id}','IntakeformController@formSent')->name('form/{id}');
Route::get('cform/{id}','LeadController@CaseFormSent')->name('cform/{id}');
Route::get('contact_us/{id}','LeadController@contact_us')->name('contact_us/view');

Route::post('leads/collectFormData', 'LeadController@collectFormData')->name('leads/collectFormData');
Route::post('collectContactUSFormData', 'LeadController@collectContactUSFormData')->name('collectContactUSFormData');
Route::get('clear','CronController@index')->name('clear');
Route::get('deletepdf','CronController@deletePdf')->name('deletepdf');
Route::get('removeuser','CronController@removeDuplicateUser')->name('removeuser');
Route::get('adduser','CronController@addUser')->name('adduser');
Route::get('sentInvoiceReminder','CronController@sentInvoiceReminder')->name('sentInvoiceReminder');
Route::get('createEventType','CronController@createEventType')->name('createEventType');
Route::get('createPracticeArea','CronController@createPracticeArea')->name('createPracticeArea');

// check email

Route::post('check-email', 'LeadController@checkUserEmail')->name('check.email');
Route::get('setbilling', 'CronController@setBillingMethod');
Route::get('setgroup', 'CronController@setContactGroup');
Route::get('check/job', 'PodcastController@index');



