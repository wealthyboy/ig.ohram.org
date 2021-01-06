<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentStatusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {   
        Schema::dropIfExists('payment_statuses');

        Schema::create('payment_statuses', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('user_id')->unsigned();
            $table->string('transaction_reference')->nullable();
            $table->string('amount')->nullable();
            $table->string('approved_amount')->nullable();
            $table->string('response_description')->nullable();
            $table->string('status')->nullable();
            $table->string('response_code')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_statuses');
    }
}
