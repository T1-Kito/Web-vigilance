<?php

namespace App\Services;

use App\Models\RepairForm;

class PrintService
{
    public function generatePrintData(RepairForm $repairForm)
    {
        return [
            'form_number' => $repairForm->form_number,
            'received_date' => $repairForm->received_date,
            'customer_company' => strtoupper($repairForm->customer_company),
            'contact_person' => $repairForm->contact_person,
            'contact_phone' => $repairForm->contact_phone,
            'alternate_contact' => $repairForm->alternate_contact ?: 'Không',
            'alternate_phone' => $repairForm->alternate_phone ?: 'Không',
            'purchase_date' => $repairForm->purchase_date,
            'company_phone' => $repairForm->company_phone ?: 'Không',
            'fax' => $repairForm->fax ?: 'Không',
            'email' => $repairForm->email ?: '',
            'equipment_name' => $repairForm->equipment_name,
            'error_status' => $repairForm->error_status,
            'includes_adapter' => $repairForm->includes_adapter,
            'accessories' => $repairForm->accessories,
            'serial_numbers' => $repairForm->serial_numbers,
            'warranty_status' => $repairForm->warranty_status,
            'employee_count' => $repairForm->employee_count ?: '',
            'repair_time_required' => $repairForm->repair_time_required,
            'estimated_return_date' => $repairForm->estimated_return_date,
            'actual_return_date' => $repairForm->actual_return_date,
            'estimated_warranty_time' => $repairForm->estimated_warranty_time,
            'received_by' => $repairForm->received_by,
            'notes' => $repairForm->notes,
            'warranty_status_text' => $repairForm->warranty_status == 'under_warranty' ? 'Còn bảo hành' : 'Hết bảo hành',
            'warranty_expired' => $repairForm->warranty_status == 'expired' ? 'X' : '',
            'warranty_active' => $repairForm->warranty_status == 'under_warranty' ? 'X' : '',
        ];
    }
} 