<div>
    <div class="apply-funds-summary-dialog">
        
        <div id="apply-funds-failures">
            <div>
                <b>The following invoices could not be deleted.</b>
            </div><br>
            <ul>
                @forelse($nonDeleted as $k=>$v)
                <li>{{ $v->id }} ({{ @$v->portalAccessUserAdditionalInfo->user->full_name }})</li>
                @empty
                @endforelse
            </ul>
            <div>An invoice cannot be deleted if it has been forwarded to another invoice, if deletion causes a trust balance to become negative, or if online payments have been applied.</div>
        </div>
    </div>
</div>
