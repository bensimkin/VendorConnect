<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('estimates_invoice_item');
        Schema::dropIfExists('contracts');
        Schema::dropIfExists('contract_types');
        Schema::dropIfExists('time_trackers');
        Schema::dropIfExists('allowance_payslip');
        Schema::dropIfExists('deduction_payslip');   
        Schema::dropIfExists('meeting_user');
        Schema::dropIfExists('client_meeting');
        Schema::dropIfExists('todos');
        Schema::dropIfExists('leave_request_visibility');
        Schema::dropIfExists('allowances');
        Schema::dropIfExists('estimates_invoices');
        Schema::dropIfExists('meetings');
        Schema::dropIfExists('deductions');
        Schema::dropIfExists('payslips');
        Schema::dropIfExists('payment_methods');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
