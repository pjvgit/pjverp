<h5>Time Entries</h5>

<table class="table">
    <thead>
        <tr>
            <th scope="col">&nbsp;</th>
            <th scope="col">Billable</th>
            <th scope="col">Non-billable</th>
            <th scope="col">Total</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <th scope="row">Today</th>
            <td class="test-today-billable">{{number_format($FinalArray['todayBillableTimeEntry'],1)}}</td>
            <td class="test-today-nonbillable">{{number_format($FinalArray['todayNonBillableTimeEntry'],1)}}</td>
            <td class="test-today-total">${{number_format($FinalArray['todayTotal'],2)}}</td>
        </tr>
        <tr>
            <th scope="row">This week</th>
            <td class="test-today-billable">{{number_format($FinalArray['thisBillableWeekTimeEntry'],1)}}</td>
            <td class="test-today-nonbillable">{{number_format($FinalArray['thisNonBillableWeekTimeEntry'],1)}}</td>
            <td class="test-today-total">${{number_format($FinalArray['weekTotal'],2)}}</td>
 
        </tr>
        <tr>
            <th scope="row">This month</th>
            <td class="test-today-billable">{{number_format($FinalArray['thisBillableMonthTimeEntry'],1)}}</td>
            <td class="test-today-nonbillable">{{number_format($FinalArray['thisNonBillableMonthTimeEntry'],1)}}</td>
            <td class="test-today-total">${{number_format($FinalArray['monthTotal'],2)}}</td>
   </tr>
    </tbody>
</table>
