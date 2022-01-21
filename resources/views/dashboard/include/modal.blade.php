{{-- <div class="modal fade your_firm_popup" id="your_firm_popup" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog width_set margin_set_top" role="document">
        <div class="modal-content">
            <div class="modal-header header_bg_color">
                <h5 class="modal-title title_font-weight" id="exampleModalLabel">Tell us about your firm</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="user_interest_form" method="POST">
                @csrf
                <div class="modal-body">
                    <h6>What are you most intrested in? Select 2.</h6>
                    <p class="font-color">We will help you find the most important information quickly </p>
                    <div class="row content_set">
                        <ul class="ul_set">
                            <li class="style_set btn btn-primary"><input type="checkbox" name="user_interest[]" value="billing and invoice">Billing & Invoice</li>
                            <li class="style_set"><input type="checkbox" name="user_interest[]" value="time tracking">Time Tracking</li>
                            <li class="style_set"><input type="checkbox" name="user_interest[]" value="case management">Case Management</li>
                            <li class="style_set"><input type="checkbox" name="user_interest[]" value="document management">Document Management</li>
                            <li class="style_set"><input type="checkbox" name="user_interest[]" value="task automation">Task Automation</li>
                            <li class="style_set"><input type="checkbox" name="user_interest[]" value="case communication">Case Communication</li>
                            <li class="style_set"><input type="checkbox" name="user_interest[]" value="document automation">Document Automation</li>
                            <li class="style_set"><input type="checkbox" name="user_interest[]" value="intake management">Intake Management</li>
                            <li class="style_set"><input type="checkbox" name="user_interest[]" value="calendaring and deadlines">Calendering & Deadlines</li>
                        </ul>
                    </div>
                    <h6>What are you looking to get out of this trial.</h6>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="looking_out" id="flexRadioDefault1" value="practice management software">
                        <label class="form-check-label" for="flexRadioDefault1">
                            Learn what pratice management software is and how it can help me.
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="looking_out" id="flexRadioDefault2" value="understand legalcase features">
                        <label class="form-check-label" for="flexRadioDefault2">
                            Understand MyCase features and see if it is best fit for me.
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="looking_out" id="flexRadioDefault3" value="ready to use leagalcase">
                        <label class="form-check-label" for="flexRadioDefault3">
                            I'm ready to use MyCase and want to get up and running.
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary btn_radius" id="take_to_legalcase">Take me to MyCase</button>
                </div>
            </form>
        </div>
    </div>
</div> --}}

<div class="modal fade your_firm_popup" id="your_firm_popup" tabindex="-1" role="dialog" aria-label="Modal"  data-keyboard="false" data-backdrop="static" style="display: none;">
	<div class="modal-dialog width_set margin_set_top" style="touch-action: none; max-width: 800px; visibility: initial; transform: translate(0px, 0px);">
        <div class="modal-content">
            <div class="modal-header header_bg_color">
                <div class="header-left-group">
                    <h5 class="mb-0"><span class="modal-title">Tell us about your firm</span></h5></div>
                <div class="header-right-group">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                </div>
            </div>
            <div class="modal-body">
                <div>
                    <div class="mb-2">
                        <div class="font-weight-bold mr-1">What are you most interested in? Select 2.</div>We will help you find the most important information quickly.</div>
                    <div class="row mx-0">
                        <div class="col-4 mb-2">
                            <button type="button" class="module-btn col-12 p-2 btn btn-secondary" data-option="billing and invoice">Billing &amp; Invoicing</button>
                        </div>
                        <div class="col-4 mb-2">
                            <button type="button" class="module-btn col-12 p-2 btn btn-secondary" data-option="time tracking">Time Tracking</button>
                        </div>
                        <div class="col-4 mb-2">
                            <button type="button" class="module-btn col-12 p-2 btn btn-secondary" data-option="case management">Case Management</button>
                        </div>

                        <div class="col-4 mb-2">
                            <button type="button" class="module-btn col-12 p-2 btn btn-secondary" data-option="document management">Document Management</button>
                        </div>
                        <div class="col-4 mb-2">
                            <button type="button" class="module-btn col-12 p-2 btn btn-secondary" data-option="task automation">Task Automation</button>
                        </div>
                        <div class="col-4 mb-2">
                            <button type="button" class="module-btn col-12 p-2 btn btn-secondary" data-option="client communication">Client Communication</button>
                        </div>

                        <div class="col-4 mb-2">
                            <button type="button" class="module-btn col-12 p-2 btn btn-secondary" data-option="document automation">Document Automation</button>
                        </div>
                        <div class="col-4 mb-2">
                            <button type="button" class="module-btn col-12 p-2 btn btn-secondary" data-option="intake management">Intake Management</button>
                        </div>
                        <div class="col-4 mb-2">
                            <button type="button" class="module-btn col-12 p-2 btn btn-secondary" data-option="calendar and deadline">Calendaring &amp; Deadlines</button>
                        </div>
                        
                    </div>
                    <span class="module-error error"></span>
                    <input type="hidden" name="interest_module" id="interest_module">
                </div>
                <div class="mt-3">
                    <div class="font-weight-bold mb-2">What are you looking to get out of this trial?</div>
                    <div>
                        <div>
                            <label>
                                <input class="mr-2" type="radio" name="looking_out" id="PMS" value="practice management software">Learn what practice management software is and how it can help me.</label>
                        </div>
                        <div>
                            <label>
                                <input type="radio" class="mr-2" id="FEATURES" name="looking_out" value="understand legalcase features">Understand MyCase features and see if it is the best fit for me.</label>
                        </div>
                        <div>
                            <label>
                                <input type="radio" class="mr-2" id="SETUP" name="looking_out" value="ready to use leagalcase">I'm ready to use MyCase and want to get up and running.</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="take_to_legalcase">Take me to LegalCase</button>
                </div>
            </div>
        </div>
	</div>
</div>