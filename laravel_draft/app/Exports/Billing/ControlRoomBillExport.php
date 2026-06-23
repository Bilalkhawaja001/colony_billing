<?php

namespace App\Exports\Billing;

class ControlRoomBillExport
{
    public function headings(): array
    {
        return [
            'Month',
            'Company ID',
            'Name',
            'Scope',
            'Colony Type',
            'Block/Floor',
            'Unit ID',
            'Room No',
            'Active Days',
            'Previous Reading',
            'Current Reading',
            'Gross Units',
            'Free Allowance',
            'Billable Units',
            'Rate',
            'Amount',
            'Exception',
        ];
    }
}
