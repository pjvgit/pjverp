<div class="text-center col-md-12">
    <h2 data-testid="average-amount"><strong>${{number_format($AverageAmount,2)}}</strong></h2>
    <strong data-testid="payments-count">{{$totalInstalment}}</strong>&nbsp;payments,<br>
    total of&nbsp;<strong data-testid="total-amount">${{number_format($totalSum,2)}}</strong>

</div>
<script type="text/javascript">
    $(document).ready(function () {
        $('[data-toggle="tooltip"]').tooltip();
    });
   
</script>
