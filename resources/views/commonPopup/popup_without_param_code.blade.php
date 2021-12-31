<div id="payInvoice" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Record Payment</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <span class="showError"></span>
                        <div id="payInvoiceArea">
                        </div>
                    </div>
                </div><!-- end of main-content -->
            </div>
        </div>
    </div>
</div>
<div id="depositIntoTrustAccount" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="depositIntoTrustTitle">Deposit Trust Funds for <span id="dynTitle"></span>
                </h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <span class="showError"></span>
                        <div id="depositIntoTrustAccountArea">
                        </div>
                    </div>
                </div><!-- end of main-content -->
            </div>
        </div>
    </div>
</div>
<div id="depositIntoNonTrustAccount" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="depositIntoTrustTitle">Deposit Non-Trust Funds for <span
                        id="dynTitleForNonTrust"></span></h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <span class="showError"></span>
                        <div id="depositIntoNonTrustAccountArea">
                        </div>
                    </div>
                </div><!-- end of main-content -->
            </div>
        </div>
    </div>
</div>

{{-- For add email popup --}}
<div id="addEmailToClient" class="modal fade show modal-overlay" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <form class="addEmailtouser" id="addEmailtouser" name="addEmailtouser" method="POST">
            @csrf
            <input type="hidden" value="" name="client_id" id="client_id_for_email">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">Enter Email</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="showErrorOver" style="display:none"></div>
                    <div class="row">
                        <div class="col-md-12" id="confirmAccess">
                            In order to send a request, they must have a valid email address. Please enter this information below and click "Save Email" to add it to their record and continue with this request.
                      </div>
                    </div>
                    <br>
                    <div class="form-group row">
                        <label for="due-date" class="col-4 pt-2">E-mail Addess</label>
                        <div class="date-input-wrapper col-8">
                            <div class="">
                                <div>
                                    <input class="form-control" id="email" maxlength="250" name="email" type="text">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="col-md-12  text-center">
                        <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoader" style="display: none;"></div>
                        <div class="form-group row float-right">
                            <button class="btn btn-secondary m-1 close-modal" type="button" data-dismiss="modal">Cancel</button>
                            <button class="btn btn-primary ladda-button example-button m-1 submit" id="submit"
                                type="submit">Save Email</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div id="loadTimeEntryPopup" class="modal fade bd-example-modal-lg modal-overlay" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Add Time Entry</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div id="addTimeEntry">
                </div>
            </div>
        </div>
    </div>
</div>
<div id="loadTimeEntryPopupTask" class="modal fade bd-example-modal-lg modal-overlay" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Add Time Entry</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div id="addTimeEntryTask">
                </div>
            </div>
        </div>
    </div>
</div>

<div id="loadExpenseEntryPopup" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Add Expense</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div id="loadExpenseEntryPopupArea">
                </div>
            </div>
        </div>
    </div>
</div>

<div id="loadAddEventPopup" class="modal fade bd-example-modal-xl" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Add Event</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12" id="AddEventPage">

                    </div>
                </div><!-- end of main-content -->
            </div>

        </div>
    </div>
</div>


<div id="loadAddTaskPopup" class="modal fade bd-example-modal-xl" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Add Task</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div id="addTaskArea">
                        </div>
                    </div>
                </div><!-- end of main-content -->
            </div>

        </div>
    </div>
</div>
<div id="addLead" class="modal fade bd-example-modal-lg" role="dialog" aria-labelledby="exampleModalCenterTitle"
    aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Add Lead</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div id="addLeadArea">
                        </div>
                    </div>
                </div><!-- end of main-content -->
            </div>
        </div>
    </div>
</div>
<div id="typeSelectDashboard" class="modal fade bd-example-modal-lg show" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog ">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Add Contact</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12" id="ModelData">
                    <h5 class="text-center my-4"> What type of contact would you like to add?                    </h5>
                    <div class="row">
                       
                        <div class="col-lg-6 col-md-6 col-sm-6">
                            <a data-toggle="modal" data-target="#AddContactModal" data-placement="bottom"
                                onclick="AddContactModal();" href="javascript:;">

                                <div class="card card-icon-bg card-icon-bg-primary o-hidden mb-4">
                                    <div class="card-body text-center"> 
                                        <img src="{{ asset('svg/contact.svg') }}" width="60" height="60">
                   
                                        <div class="content">
                                            <p class="text-muted mt-2 mb-0">New Contact</p>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <div class="col-lg-6 col-md-6 col-sm-6">
                            <a data-toggle="modal" data-target="#addCompany" data-placement="bottom"
                                onclick="addCompany();" href="javascript:;">

                                <div class="card card-icon-bg card-icon-bg-primary o-hidden mb-4">
                                    <div class="card-body text-center"><img src="{{ asset('svg/company.svg') }}" width="60" height="60">
                                        <div class="content">
                                            <p class="text-muted mt-2 mb-0">Add Company</p>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="addContact" class="modal fade bd-example-modal-lg show"  role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Add Contact</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body"  >
                <div class="row">
                    <div class="col-md-12" id="AddContact">
                        <div class="loader-bubble loader-bubble-primary" style="display: block;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="addCompany" class="modal fade bd-example-modal-lg show" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Add Company</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12" id="AddCompany">
                        <div class="loader-bubble loader-bubble-primary" style="display: block;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div id="addNewMessagePopup" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog  modal-lg ">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="depostifundtitle">New Message</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span>
                    </button>
            </div>
            <div class="modal-body">
                <div id="addNewMessagePopupArea">
                </div>
            </div>
        </div>
    </div>
</div>
<div id="loadMessagesEntryPopup" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog  modal-lg ">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="depostifundtitle">View Message</h5>
                    <div style="float: right;">    
                    <button onclick="printData()" class="btn btn-link text-black-50 pendo-case-print d-print-none">
                        <i class="fas fa-print"></i> Print
                    </button>
                    <a class="btn btn-sm btn-secondary ml-1 archiveMessage" style="display:none;" href="javascript:void(0);" onclick="archiveMessage(); return false;">Archive</a>    
                    <a class="btn btn-sm btn-secondary ml-1 unarchiveMessage" style="display:none;" href="javascript:void(0);" onclick="unarchiveMessage(); return false;">Unarchive</a>    
                </div>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span>
                    </button>
            </div>
            <div class="modal-body">
                <div id="loadMessagesEntryPopupArea">
                </div>
            </div>
        </div>
    </div>
</div>
<div id="addNoteModal" class="modal fade show" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Add Note</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12" id="addNoteModalArea">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div id="depositIntoTrust" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog  modal-lg ">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="depostifundtitle">Deposit Into Trust</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="showError" style="display:none"></div>
                <div id="depositIntoTrustArea">
                </div>
            </div>
        </div>
    </div>
</div>


<div id="loadEditTimeEntryPopup" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Edit Time Entry</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div id="loadEditTimeEntryPopupArea">
                </div>
            </div>
        </div>
    </div>
</div>
<div id="deleteTimeEntry" class="modal fade show modal-overlay" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <form class="deleteTimeEntryForm" id="deleteTimeEntryForm" name="deleteTimeEntryForm" method="POST">
            @csrf
            <input type="hidden" value="" name="entry_id" id="delete_entry_id">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">Delete Time Entry</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="showError" style="display:none"></div>
                    <div class="row">
                        <div class="col-md-12" id="confirmAccess">
                            Are you sure you want to delete this time entry?
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="col-md-12  text-center">
                        <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoader" style="display: none;"></div>
                        <div class="form-group row float-right">
                            <button class="btn btn-secondary m-1" type="button" data-dismiss="modal">Cancel</button>
                            <button class="btn btn-primary ladda-button example-button m-1 submit" id="submit"
                                type="submit">Delete</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<div id="loadEditExpenseEntryPopup" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Edit Expense</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div id="loadEditExpenseEntryPopupArea">
                </div>
            </div>
        </div>
    </div>
</div>

<div id="deleteExpenseEntryCommon" class="modal fade show modal-overlay" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <form class="deleteExpenseEntryFormUnique" id="deleteExpenseEntryFormUnique" name="deleteExpenseEntryFormUnique" method="POST">
            @csrf
            <input type="hidden" value="" name="entry_id" id="delete_expense_entry_id">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">Confirm Delete</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="showError" style="display:none"></div>
                    <div class="row">
                        <div class="col-md-12" id="confirmAccess">
                            Are you sure you want to delete this Expense?
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="col-md-12  text-center">
                        <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoader"
                            style="display: none;"></div>
                        <div class="form-group row float-right">
                            <button class="btn btn-secondary m-1" type="button" data-dismiss="modal">Cancel</button>
                            <button class="btn btn-primary ladda-button example-button m-1 submit" id="submit"
                                type="submit">Delete</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>


<div id="deleteInvoiceCommon" class="modal fade show modal-overlay" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <form class="deleteInvoiceFormCommon" id="deleteInvoiceFormCommon" name="deleteInvoiceFormCommon" method="POST">
            <input type="hidden" id="delete_invoice_id" name="invoice_id">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">Confirm Delete</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="showError" style="display:none"></div>
                    <div class="row">
                        <div class="col-md-12" id="confirmAccess">
                            Are you sure you want to delete this Invoice?
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="col-md-12  text-center">
                        <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoader"
                            style="display: none;"></div>
                        <div class="form-group row float-right">
                            <button class="btn btn-secondary m-1" type="button" data-dismiss="modal">Cancel</button>
                            <button class="btn btn-primary ladda-button example-button m-1 submit" id="submit"
                                type="submit">Delete</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div id="sendInvoiceReminder" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog  modal-lg ">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="depostifundtitle">Send Reminder</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span>
                    </button>
            </div>
            <div class="modal-body">
                <span class="showError"></span>
                <div id="sendInvoiceReminderArea">
                </div>
            </div>
        </div>
    </div>
</div>

<div id="addNoteModal" class="modal fade show" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Add Note</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12" id="addNoteModalArea">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="editNoteModal" class="modal fade show" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Edit Note</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12" id="editNoteModalArea">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="discardNotes" class="modal fade show modal-overlay" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <form class="discardNotesForm" id="discardNotesForm" name="discardNotesForm" method="POST">
            @csrf
            <input type="hidden" value="" name="note_id" id="discard_note_id">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">Discard Note</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="showError" style="display:none"></div>
                    <div class="row">
                        <div class="col-md-12" id="confirmAccess">
                            Are you sure you want to discard this note?
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="col-md-12  text-center">
                        <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoader" style="display: none;"></div>
                        <div class="form-group row float-right">
                            <button class="btn btn-secondary m-1" type="button" data-dismiss="modal">Cancel</button>
                            <button class="btn btn-primary ladda-button example-button m-1 submit" id="submit"
                                type="submit">Discard</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>


<div id="discardDeleteNotes" class="modal fade show modal-overlay" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <form class="discardDeleteNotesForm" id="discardDeleteNotesForm" name="discardDeleteNotesForm" method="POST">
            @csrf
            <input type="hidden" value="" name="note_id" id="discard_delete_note_id">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">Discard Note</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="showError" style="display:none"></div>
                    <div class="row">
                        <div class="col-md-12" id="confirmAccess">
                            Are you sure you want to discard the draft? All your changes to the note will be discarded. If the note has never been published, it will be deleted.
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="col-md-12  text-center">
                        <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoader" style="display: none;"></div>
                        <div class="form-group row float-right">
                            <button class="btn btn-secondary m-1" type="button" data-dismiss="modal">Cancel</button>
                            <button class="btn btn-primary ladda-button example-button m-1 submit" id="submit"
                                type="submit">Discard</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>


<div id="deleteNote" class="modal fade show modal-overlay" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <form class="deleteNoteForm" id="deleteNoteForm" name="deleteNoteForm" method="POST">
            @csrf
            <input type="hidden" value="" name="note_id" id="delete_note_id">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">Delete Note</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="showError" style="display:none"></div>
                    <div class="row">
                        <div class="col-md-12" id="confirmAccess">
                            Are you sure you want to delete this note?
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="col-md-12  text-center">
                        <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoader" style="display: none;"></div>
                        <div class="form-group row float-right">
                            <button class="btn btn-secondary m-1" type="button" data-dismiss="modal">Cancel</button>
                            <button class="btn btn-primary ladda-button example-button m-1 submit" id="submit"
                                type="submit">Delete</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>


<div id="depositAmountPopup" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="depostifundtitle">Deposit Trust Fund</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span>
                    </button>
            </div>
            <div class="modal-body">
                <div id="depositAmountPopupArea">
                </div>
            </div>
        </div>
    </div>
</div>
<div id="loadReminderPopupIndexCommon" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static" style="">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteSingle">Set Task Reminders</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12" id="reminderDataIndex">

                    </div>
                </div><!-- end of main-content -->
            </div>
        </div>
    </div>
</div>
<div id="editTask" class="modal fade bd-example-modal-xl" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Edit Task</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div id="editTaskArea">
                        </div>
                    </div>
                </div><!-- end of main-content -->
            </div>

        </div>
    </div>
</div>


<div id="deleteTask" class="modal fade " tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
    aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteSingle">Confirmation</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12" id="eventID">
                        <form class="deleteTaskForm" id="deleteTaskForm" name="deleteTaskForm" method="POST">
                            <div id="showError2" style="display:none"></div>
                            @csrf
                            <input class="form-control" id="task_id" value="" name="task_id" type="hidden">
                            <div class=" col-md-12">
                                <div class="form-group row">
                                    <label for="inputEmail3" class="col-sm-12 col-form-label">
                                        Are you sure you want to delete this task?
                                    </label>
                                </div>
                                <div class="form-group row float-right">
                                    <a href="#">
                                        <button class="btn btn-secondary  m-1" type="button"
                                            data-dismiss="modal">Cancel</button>
                                    </a>
                                    <button class="btn btn-primary ladda-button example-button m-1" id="submit"
                                        type="submit">
                                        <span class="ladda-label">Yes, Delete</span>
                                    </button>
                                </div>
                                <div class="form-group row">
                                    <label for="inputEmail3" class="col-sm-8 col-form-label"></label>
                                    <div class="col-md-2 form-group mb-3">
                                        <div class="loader-bubble loader-bubble-primary" id="innerLoader1"
                                            style="display: none;"></div>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="inputEmail3" class="col-sm-12 col-form-label"></label>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="changeDueDate" class="modal fade " tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
    aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteSingle">Change Due Date</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12" id="eventID">
                        <form class="dueDateChange" id="dueDateChange" name="dueDateChange" method="POST">
                            <div id="showError2" style="display:none"></div>
                            @csrf
                            <div class="col-md-12 form-group mb-3">
                                <label for="firstName1">Change due date to:</label>
                                <input type="text" class="form-control" name="duedate" id="duedate">

                            </div>
                            <div class=" col-md-12">
                                <div class="form-group row float-right">
                                    <a href="#">
                                        <button class="btn btn-secondary  m-1" type="button"
                                            data-dismiss="modal">Cancel</button>
                                    </a>
                                    <button class="btn btn-primary ladda-button example-button m-1" id="submit"
                                        type="submit">
                                        <span class="ladda-label">Update</span>
                                    </button>
                                </div>
                                <div class="form-group row">
                                    <label for="inputEmail3" class="col-sm-8 col-form-label"></label>
                                    <div class="col-md-2 form-group mb-3">
                                        <div class="loader-bubble loader-bubble-primary" id="innerLoader1"
                                            style="display: none;"></div>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="inputEmail3" class="col-sm-12 col-form-label"></label>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="addCaseLinkWithOption" class="modal fade show" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="exampleModalCenterTitle">Add Case Link</h5>
            <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                    aria-hidden="true">×</span></button>
        </div>
        <div class="modal-body">
            <h5 class="text-center my-4">Would you like to add a new or existing court case?</h5>

            <section class="ul-widget-stat-s1">
                <div class="row">
                    <div class="col-lg-6 col-md-6 col-sm-6">
                        <a data-toggle="modal"  data-target="#AddCaseModelUpdate" onclick="loadAllStep();" data-placement="bottom" href="javascript:;" >
                            <div class="card card-icon-bg card-icon-bg-primary o-hidden mb-4">
                            <div class="card-body text-center">
                                <img src="{{ asset('svg/court_case_add.svg') }}" width="60" height="60">
                                <div class="content">
                                    <p class="text-muted mt-2 mb-0">New Case</p>
                                    <p class="text-primary text-24 line-height-1 mb-2"></p>
                                </div>
                            </div>
                        </div></a>
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-6">
                        <a data-toggle="modal" data-target="#addExistingCase" data-placement="bottom" href="javascript:;" onclick="addExistingCase();"> 
                        <div class="card card-icon-bg card-icon-bg-primary o-hidden mb-4">
                            <div class="card-body text-center">
                                <img src="{{ asset('svg/exisiting_case.svg') }}" width="60" height="60">
                                <div class="content">
                                    <p class="text-muted mt-2 mb-0">Existing Case</p>
                                    <p class="text-primary text-24 line-height-1 mb-2"></p>
                                </div>
                            </div>
                        </div>
                        </a>
                    </div>
                </div>
            </section> 
            </div>
        </div>
    </div>
</div>
<div id="AddContactModal" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Add Contact</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div id="step-1-again">
                        </div>
                    </div>
                </div><!-- end of main-content -->
            </div>
        </div>
    </div>
</div>
<div id="addExistingCase" class="modal fade bd-example-modal-lg " tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Add Existing Case</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div id="addExistingCaseArea">
                        </div>
                    </div>
                </div><!-- end of main-content -->
            </div>
        </div>
    </div>
</div>

<div id="typeSelect" class="modal fade bd-example-modal-lg show" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Add Contact</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12" id="ModelData">
                        <div class="loader-bubble loader-bubble-primary" style="display: block;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="trust_allocation_modal" class="modal fade bd-example-modal-md " tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Trust Allocation</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
				<div id="trust_allocation_modal_body">
				</div>
            </div>
        </div>
    </div>
</div>

<div id="loadAddFeedBack" class="modal fade bd-example-modal-md " tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title feedback_title" id="exampleModalCenterTitle">Make a Suggestion about <span id="feedback_title"></span></h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                
            <form id="feedback_form" name="feedback_form" method="post">
                <div id="feedback_form_errors"></div>
                @csrf
                            
                <input type="hidden" name="mailer_type" id="mailer_type" value="feedback">
                <input type="hidden" name="topic" id="topic" value="">
                <input type="hidden" name="rating" id="rating" value="">

                <div class="modal-body">
                    <p>Customer feedback helps us to improve our product!</p>
                    <p>
                        Please give us details about your idea and how it helps your business. If you need
                        immediate help or assistance, please access our <a href="javascript::void(0);" target="_blank" rel="noreferrer noopener">support page</a>.
                    </p>
                    <div class="form-group">
                    <label for="message">Details</label>
                    <textarea name="message" id="message" class="form-control" placeholder="Please try to be as specific as possible." style="resize: none; height: 140px;"></textarea>
                    </div>
                    <div class="rating-section">
                    <p class="mb-0">How easy or difficult was this feature to use?</p>
                    <div role="group" class="d-flex btn-group">
                        <button type="button" class="flex-fill rounded border mr-1 btn btn-secondary ratingButton">1</button>
                        <button type="button" class="flex-fill rounded border mr-1 btn btn-secondary ratingButton">2</button>
                        <button type="button" class="flex-fill rounded border mr-1 btn btn-secondary ratingButton">3</button>
                        <button type="button" class="flex-fill rounded border mr-1 btn btn-secondary ratingButton">4</button>
                        <button type="button" class="flex-fill rounded border mr-1 btn btn-secondary ratingButton">5</button>
                    </div>
                    <div class="d-flex"><p class="mr-auto"><em> Very Difficult </em></p><p><em>Very Easy </em></p></div>
                    </div>
                    <div class="row">
                    <div class="col-6">
                        <label>Name</label>
                    </div>
                    <div class="col">
                        <label>Email</label>
                    </div>
                    <div class="col-6">
                        <input type="text" name="name" id="name" value="{{ Auth::user()->first_name.' '.Auth::user()->last_name }}" class="form-control">
                    </div>
                    <div class="col">
                        <input type="text" name="email" id="email" value="{{ Auth::user()->email }}" class="form-control">
                    </div>
                    </div>

                </div>

                <div class="modal-footer">
                    <div id="link_button">
                        <button class="btn btn-primary" type="submit">
                            <span class="ladda-label">Submit Query</span>
                        </button>
                    </div>
                    
                    <div id="adding_box" style="display: none; width: 150px; text-align: center; height: 28px; padding-top: 7px;" class="standard adding ">
                    <img style="vertical-align: middle;" class="retina" src="https://assets.mycase.com/packs/retina/ajax_arrows-0ba8e6a4d4.gif" width="16" height="16"> <span id="adding_box_text" style="line-height: 16px;">Submitting...</span>
                    </div>
                    
                </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    function payinvoice(id) {
        console.log("Popup_without_param_code.blade.php > payinvoice for " + id);
        $('.showError').html('');
        beforeLoader();
        $("#preloader").show();
        $("#payInvoiceArea").html('');
        $("#payInvoiceArea").html('<img src="{{LOADER}}"> Loading...');
        $.ajax({
            type: "POST",
            url: baseUrl + "/bills/invoices/payInvoicePopup",
            data: {
                'id': id
            },
            success: function (res) {
                if (typeof (res.errors) != "undefined" && res.errors !== null) {
                    $('.showError').html('');
                    var errotHtml =
                        '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal server error. Please reload the screen.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                    $('.showError').append(errotHtml);
                    $('.showError').show();
                    afterLoader();
                    $("#preloader").hide();
                    $("#payInvoiceArea").html('');
                    return false;
                } else {
                    afterLoader()
                    $("#payInvoiceArea").html(res);
                    $("#preloader").hide();
                    return true;
                }
            },
            error: function (xhr, status, error) {
                $("#preloader").hide();
                $("#payInvoiceArea").html('');
                $('.showError').html('');
                var errotHtml =
                    '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                $('.showError').append(errotHtml);
                $('.showError').show();
                afterLoader();
            }
        })
    }

    function depositIntoTrustPopup(id, caseId = null, request_id = null) {
        $('.showError').html('');
        beforeLoader();
        $("#preloader").show();
        $("#depositIntoTrustAccountArea").html('');
        $("#depositIntoTrustAccountArea").html('<img src="{{LOADER}}"> Loading...');
        $.ajax({
            type: "POST",
            url: baseUrl + "/bills/dashboard/depositIntoTrustPopup",
            data: {
                'id': id, case_id: caseId,
                'request_id' : request_id
            },
            success: function (res) {
                if (typeof (res.errors) != "undefined" && res.errors !== null) {
                    $('.showError').html('');
                    var errotHtml =
                        '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal server error. Please reload the screen.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                    $('.showError').append(errotHtml);
                    $('.showError').show();
                    afterLoader();
                    $("#preloader").hide();
                    $("#depositIntoTrustAccountArea").html('');
                    return false;
                } else {
                    afterLoader()
                    $("#depositIntoTrustAccountArea").html(res);
                    $("#preloader").hide();
                    return true;
                }
            },
            error: function (xhr, status, error) {
                $("#preloader").hide();
                $("#depositIntoTrustAccountArea").html('');
                $('.showError').html('');
                var errotHtml =
                    '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                $('.showError').append(errotHtml);
                $('.showError').show();
                afterLoader();
            }
        })
    }

    function depositIntoNonTrustAccount(clinet_id, request_id = null) {        
        $('.showError').html('');
        $("#preloader").show();
        $("#depositIntoNonTrustAccountArea").html('');
        $("#depositIntoNonTrustAccountArea").html('<img src="{{LOADER}}"> Loading...');
        $.ajax({
            type: "POST",
            url: baseUrl + "/bills/dashboard/depositIntoNonTrustPopup",
            data: {
                'id': clinet_id, 
                'request_id' : request_id
            },
            success: function (res) {
                if (typeof (res.errors) != "undefined" && res.errors !== null) {
                    $('.showError').html('');
                    var errotHtml =
                        '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal server error. Please reload the screen.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                    $('.showError').append(errotHtml);
                    $('.showError').show();
                    $("#preloader").hide();
                    $("#depositIntoNonTrustAccountArea").html('');
                    return false;
                } else {
                    $("#depositIntoNonTrustAccountArea").html(res);
                    $("#preloader").hide();
                    return true;
                }
            },
            error: function (xhr, status, error) {
                $("#preloader").hide();
                $("#depositIntoNonTrustAccountArea").html('');
                $('.showError').html('');
                var errotHtml =
                    '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                $('.showError').append(errotHtml);
                $('.showError').show();
            }
        })
    }

    /* function addRequestFundPopup(caseId = null) {
        $("#preloader").show();
        $("#addRequestFundArea").html('<img src="{{LOADER}}"> Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/contacts/clients/addRequestFundPopup",
                data: {
                    "user_id": "", case_id: caseId
                },
                success: function (res) {
                    $("#addRequestFundArea").html(res);
                    $("#preloader").hide();
                }
            })
        })
    } */

    function loadTimeEntryPopup() {
        $("#preloader").show();
        $("#addTimeEntry").html('<img src="{{LOADER}}"> Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/bills/loadTimeEntryPopup", // json datasource
                data: {},
                success: function (res) {
                    $("#addTimeEntry").html('');
                    $("#addTimeEntry").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }
    function loadTimeEntryPopupByCase(case_id, description = null, duration = null, smart_timer_id = null) {
        $("#preloader").show();
        $("#addTimeEntry").html('<img src="{{LOADER}}"> Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/bills/loadTimeEntryPopup", // json datasource
                data: {"case_id":case_id, "description" : description, "duration" : duration, "smart_timer_id" : smart_timer_id},
                success: function (res) {
                    $("#addTimeEntry").html('');
                    $("#addTimeEntry").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }
    function loadTimeEntryPopupByCaseWithoutRefresh(case_id) {
        $("#preloader").show();
        $("#addTimeEntry").html('<img src="{{LOADER}}"> Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/bills/loadTimeEntryPopupDontRefresh", // json datasource
                data: {"case_id":case_id},
                success: function (res) {
                    $("#loadCommentPopup").modal("hide");
                    $("#addTimeEntry").html('');
                    $("#addTimeEntry").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }

    function loadTimeEntryPopupByCaseWithoutRefreshTask(case_id) {
        $("#preloader").show();
        $("#addTimeEntry").html('<img src="{{LOADER}}"> Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/bills/loadTimeEntryPopupDontRefresh", // json datasource
                data: {"case_id":case_id},
                success: function (res) {
                    $("#loadCommentPopup").modal("hide");
                    $("#addTimeEntry").html('');
                    $("#addTimeEntry").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }
    function loadExpenseEntryPopup(case_id=null) {
        $("#preloader").show();
        $("#loadExpenseEntryPopupArea").html('<img src="{{LOADER}}"> Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/bills/expenses/loadExpenseEntryPopup", // json datasource
                data: {"case_id":case_id},
                success: function (res) {
                    $("#loadExpenseEntryPopupArea").html('');
                    $("#loadExpenseEntryPopupArea").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }

    function loadAddEventPopup() {
        $("#AddEventPage").html('<img src="{{LOADER}}"> Loading...');
        $("#preloader").show();
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/court_cases/loadAddEventPageFromCalendar", // json datasource
                data: {},
                success: function (res) {
                    $("#AddEventPage").html('Loading...');
                    $("#AddEventPage").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }
    function loadAddTaskPopup(case_id=null, lead_id=null) {
        $("#addTaskArea").html('<img src="{{LOADER}}""> Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/tasks/loadAddTaskPopup", // json datasource
                data: {
                    "user_id":lead_id,
                    "case_id":case_id
                },
                success: function (res) {
                    $("#addTaskArea").html(res);
                }
            })
        })
    }

    function addLead() {
        $("#preloader").show();
        $("#addLeadArea").html('<img src="{{LOADER}}""> Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/leads/addLead", // json datasource
                data: 'loadStep1',
                success: function (res) {
                    $.fn.modal.Constructor.prototype._enforceFocus = function () {};

                    $("#addLeadArea").html(res);

                    $("#preloader").hide();
                }
            })
        })
    }
    
    // function addContact() {  
    //     $("#preloader").show();
    //     $("#AddContact").html('<img src="{{LOADER}}""> Loading...');
    //     $(function () {
    //         $.ajax({
    //             type: "POST",
    //             url: baseUrl + "/contacts/loadAddContact", // json datasource
    //             data: {},
    //             success: function (res) {
    //                 $("#AddContact").html('');
    //                 $("#AddContact").html(res);
    //                 $("#preloader").hide();
    //             }
    //         })
    //     })
    // }
    function addCompany() {  
        $("#preloader").show();
        $("#AddCompany").html('<img src="{{LOADER}}""> Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/contacts/loadAddCompany", // json datasource
                data: {},
                success: function (res) {
                    $("#AddCompany").html('');
                    $("#AddCompany").html(res);
                    $("#preloader").hide();
                }
            })
        })
    } 

    function addNewMessagePopup(page, id) {        
        $("#preloader").show();
        $("#addNewMessagePopupArea").html('<img src="{{LOADER}}""> Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/contacts/clients/addNewMessagePopup", 
                data: {"id": id, "page" : page},
                success: function (res) {
                    $("#addNewMessagePopupArea").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }

    function loadMessagesEntryPopup(id, subject) {
        $("#preloader").show();
        $("#loadMessagesEntryPopupArea").html('<img src="{{LOADER}}""> Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/contacts/clients/loadMessagesEntryPopup", 
                data: {"message_id": id},
                success: function (res) {
                    $("#loadMessagesEntryPopupArea").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }

    function loadAddNotBox() {
        $("#preloader").show();
        $("#addNoteModalArea").html('<img src="{{LOADER}}""> Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/contacts/clients/addNotesFromDashboard", 
                data: {"user_id": ""},
                success: function (res) {
                    $("#addNoteModalArea").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }
    function loadInvoiceActivity() {
        $('.showError').html('');
        
        $("#invoiceEntry").html('');
        $("#invoiceEntry").html('<img src="{{LOADER}}""> Loading...');
        $.ajax({
            type: "POST",
            url: baseUrl + "/notifications/loadInvoiceNotification",
            data: {
                "id": null
            },
            success: function (res) {
                if (typeof (res.errors) != "undefined" && res.errors !== null) {
                    $('.showError').html('');
                    $('.showError').html('');
                    var errotHtml =
                        '<div class="alert alert-danger"><strong>Whoops!</strong> There were some problems with your input.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
                    $.each(res.errors, function (key, value) {
                        errotHtml += '<li>' + value + '</li>';
                    });
                    errotHtml += '</ul></div>';
                    $('.showError').append(errotHtml);
                    $('.showError').show();
                  
                    $("#invoiceEntry").html('');
                    return false;
                } else {
                    afterLoader()
                    $("#invoiceEntry").html(res);
                    $("#preloader").hide();
                    return true;
                }
            },
            error: function (xhr, status, error) {
                $('.showError').html('');
                var errotHtml =
                    '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                $('.showError').append(errotHtml);
                $('.showError').show();
              
            }
        })
    }
    function loadAllActivity() {
        $('.showError').html('');
        $("#allEntry").html('');
        $("#allEntry").html('<img src="{{LOADER}}""> Loading...');
        $.ajax({
            type: "POST",
            url: baseUrl + "/notifications/loadAllNotification",
            data: {
                "id": null
            },
            success: function (res) {
                if (typeof (res.errors) != "undefined" && res.errors !== null) {
                    $('.showError').html('');
                    $('.showError').html('');
                    var errotHtml =
                        '<div class="alert alert-danger"><strong>Whoops!</strong> There were some problems with your input.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
                    $.each(res.errors, function (key, value) {
                        errotHtml += '<li>' + value + '</li>';
                    });
                    errotHtml += '</ul></div>';
                    $('.showError').append(errotHtml);
                    $('.showError').show();
                    $("#allEntry").html('');
                    return false;
                } else {
                    $("#allEntry").html(res);
                    $("#preloader").hide();
                    return true;
                }
            },
            error: function (xhr, status, error) {
                $('.showError').html('');
                var errotHtml =
                    '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                $('.showError').append(errotHtml);
                $('.showError').show();
            }
        })
    }
    function loadEventActivity() {
        $('.showError').html('');
        
        $("#eventEntry").html('');
        $("#eventEntry").html('<img src="{{LOADER}}""> Loading...');
        $.ajax({
            type: "POST",
            url: baseUrl + "/notifications/loadEventsNotification",
            data: {
                "id": null
            },
            success: function (res) {
                if (typeof (res.errors) != "undefined" && res.errors !== null) {
                    $('.showError').html('');
                    $('.showError').html('');
                    var errotHtml =
                        '<div class="alert alert-danger"><strong>Whoops!</strong> There were some problems with your input.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
                    $.each(res.errors, function (key, value) {
                        errotHtml += '<li>' + value + '</li>';
                    });
                    errotHtml += '</ul></div>';
                    $('.showError').append(errotHtml);
                    $('.showError').show();
                  
                    $("#eventEntry").html('');
                    return false;
                } else {
                    afterLoader()
                    $("#eventEntry").html(res);
                    $("#preloader").hide();
                    return true;
                }
            },
            error: function (xhr, status, error) {
                $('.showError').html('');
                var errotHtml =
                    '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                $('.showError').append(errotHtml);
                $('.showError').show();
              
            }
        })
    }
    function loadTaskActivity() {
        $('.showError').html('');
        
        $("#taskActivity").html('');
        $("#taskActivity").html('<img src="{{LOADER}}""> Loading...');
        $.ajax({
            type: "POST",
            url: baseUrl + "/notifications/loadTasksNotification",
            data: {
                "id": null
            },
            success: function (res) {
                if (typeof (res.errors) != "undefined" && res.errors !== null) {
                    $('.showError').html('');
                    $('.showError').html('');
                    var errotHtml =
                        '<div class="alert alert-danger"><strong>Whoops!</strong> There were some problems with your input.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
                    $.each(res.errors, function (key, value) {
                        errotHtml += '<li>' + value + '</li>';
                    });
                    errotHtml += '</ul></div>';
                    $('.showError').append(errotHtml);
                    $('.showError').show();
                  
                    $("#taskActivity").html('');
                    return false;
                } else {
                    afterLoader()
                    $("#taskActivity").html(res);
                    $("#preloader").hide();
                    return true;
                }
            },
            error: function (xhr, status, error) {
                $('.showError').html('');
                var errotHtml =
                    '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                $('.showError').append(errotHtml);
                $('.showError').show();
              
            }
        })
    }
    function loadDocumentActivity() {
        $('.showError').html('');
        
        $("#documentsEntry").html('');
        $("#documentsEntry").html('<img src="{{LOADER}}""> Loading...');
        $.ajax({
            type: "POST",
            url: baseUrl + "/notifications/loadDocumentNotification",
            data: {
                "id": null
            },
            success: function (res) {
                if (typeof (res.errors) != "undefined" && res.errors !== null) {
                    $('.showError').html('');
                    $('.showError').html('');
                    var errotHtml =
                        '<div class="alert alert-danger"><strong>Whoops!</strong> There were some problems with your input.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
                    $.each(res.errors, function (key, value) {
                        errotHtml += '<li>' + value + '</li>';
                    });
                    errotHtml += '</ul></div>';
                    $('.showError').append(errotHtml);
                    $('.showError').show();
                  
                    $("#documentsEntry").html('');
                    return false;
                } else {
                    afterLoader()
                    $("#documentsEntry").html(res);
                    $("#preloader").hide();
                    return true;
                }
            },
            error: function (xhr, status, error) {
                $('.showError').html('');
                var errotHtml =
                    '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                $('.showError').append(errotHtml);
                $('.showError').show();
              
            }
        })
    }
    function depositIntoTrust(clientId = null, caseId = null) {
        $('.showError').html('');
        
        // $("#preloader").show();
        $("#depositIntoTrustArea").html('');
        $("#depositIntoTrustArea").html('<img src="{{LOADER}}""> Loading...');
        $.ajax({
            type: "POST",
            url: baseUrl + "/bills/dashboard/depositIntoTrust",
            data: {
                "id": null, case_id: caseId
            },
            success: function (res) {
                if (typeof (res.errors) != "undefined" && res.errors !== null) {
                    $('.showError').html('');
                    $('.showError').html('');
                    var errotHtml =
                        '<div class="alert alert-danger"><strong>Whoops!</strong> There were some problems with your input.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
                    $.each(res.errors, function (key, value) {
                        errotHtml += '<li>' + value + '</li>';
                    });
                    errotHtml += '</ul></div>';
                    $('.showError').append(errotHtml);
                    $('.showError').show();
                  
                    $("#preloader").hide();
                    $("#depositIntoTrustArea").html('');
                    return false;
                } else {
                    afterLoader()
                    $("#depositIntoTrustArea").html(res);
                    $("#preloader").hide();
                    return true;
                }
            },
            error: function (xhr, status, error) {
                $('.showError').html('');
                var errotHtml =
                    '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                $('.showError').append(errotHtml);
                $('.showError').show();
              
            }
        })
    }
    function loadEditTimeEntryPopup(id=null) {
        // $("#preloader").show();
        $("#loadEditTimeEntryPopupArea").html('<img src="{{LOADER}}""> Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/bills/loadEditTimeEntryPopup", // json datasource
                data: {'entry_id':id},
                success: function (res) {
                    $("#loadEditTimeEntryPopupArea").html('');
                    $("#loadEditTimeEntryPopupArea").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }
    function deleteTimeEntry(id=null) {
       $("#deleteTimeEntry").modal("show");
       $("#delete_entry_id").val(id);
    }
    function loadEditExpenseEntryPopup(id) {
        // $("#preloader").show();
        $("#loadEditExpenseEntryPopupArea").html('<img src="{{LOADER}}""> Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/bills/expenses/loadEditExpenseEntryPopup", // json datasource
                data: {'entry_id': id},
                success: function (res) {
                    $("#loadEditExpenseEntryPopupArea").html('');
                    $("#loadEditExpenseEntryPopupArea").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }
    function deleteExpenseEntryCommon(id) {
        $("#deleteExpenseEntryCommon").modal("show");
        $("#delete_expense_entry_id").val(id);
    }
    function deleteInvoiceCommon(id) {
        $("#deleteInvoiceCommon").modal("show");
        $("#delete_invoice_id").val(id);
    }
    function sendInvoiceReminder(id,invoice_id) {
        beforeLoader();
        // $("#preloader").show();
        $("#sendInvoiceReminderArea").html('');
        $("#sendInvoiceReminderArea").html('<img src="{{LOADER}}""> Loading...');
        $.ajax({
            type: "POST",
            url: baseUrl + "/bills/invoices/sendInvoiceReminder", 
            data: {"id": id,"invoice_id":invoice_id},
            success: function (res) {
                if(typeof(res.errors) != "undefined" && res.errors !== null) {
                    $('.showError').html('');
                    var errotHtml =
                        '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal server error. Please reload the screen.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                    $('.showError').append(errotHtml);
                    $('.showError').show();
                    afterLoader();
                    $("#preloader").hide();
                    $("#sendInvoiceReminderArea").html('');
                    return false;
                } else {
                    afterLoader()
                    $("#sendInvoiceReminderArea").html(res);
                    $("#preloader").hide();
                    return true;
                }
            },error: function (xhr, status, error) {
                $("#preloader").hide();
                $("#sendInvoiceReminderArea").html('');
                $('.showError').html('');
                var errotHtml =
                    '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                $('.showError').append(errotHtml);
                $('.showError').show();
                afterLoader();
            }
        })
    }
    function discardNotes(id) {
       
       $("#discardNotes").modal("show");
       $("#discard_note_id").val(id);
   }
    function discardDeleteNotes(id) {
       $("#discardDeleteNotes").modal("show");
       $("#discard_delete_note_id").val(id);
   }
    function deleteNote(id) {
        $("#deleteNote").modal("show");
        $("#delete_note_id").val(id);
    }
    function deleteTaskFunction(id) {
        $("#task_id").val(id);
    }
    // function AddContactModal() {
    //     alert("resources/views/commonPopup/popup_without_param_code.blade.php > AddContactModal");
    //     $("#innerLoader").css('display', 'none');
    //     $("#preloader").show();
    //     $("#step-1-again").html('');
    //     $(function () {
    //         $.ajax({
    //             type: "POST",
    //             // url:  baseUrl +"/contacts/loadAddContactFromCase", // json datasource
    //             url:  baseUrl +"/contacts/loadAddContact", // json datasource
    //             data: 'loadStep1',
    //             success: function (res) {
    //                 $("#step-1-again").html(res);
    //                 $("#preloader").hide();
    //                 $("#innerLoader").css('display', 'none');
    //                 return false;
    //             }
    //         })
    //     })
    // }
    
    function typeSelection() {  
        $("#preloader").show();
      
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/court_cases/loadTypeSelection", // json datasource
                data: 'loadStep1',
                success: function (res) {
                     $("#ModelData").html('');
                    $("#ModelData").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }
</script>
