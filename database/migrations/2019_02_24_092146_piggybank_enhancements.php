<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PiggybankEnhancements extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // not really needed can track changes through new columns to events
        // if (!Schema::hasTable('piggy_bank_accounts')) {
        //     Schema::create(
        //         'piggy_bank_accounts',
        //         function (Blueprint $table) {
        //             $table->integer('piggy_bank_id', false, true);
        //             $table->integer('account_id', false, true);
        //             $table->foreign('piggy_bank_id')->references('id')->onUpdate('cascade')->on('piggy_banks')->onDelete('cascade');
        //             $table->foreign('account_id')->references('id')->onUpdate('cascade')->on('accounts')->onDelete('cascade');
        //             $table->primary(['piggy_bank_id', 'account_id']);
        //         }
        //     );
        // }

        Schema::table('piggy_bank_events',
            function (Blueprint $table) {
                $table->decimal('transfer', 22, 12)->after('amount')->nullable();
                $table->unsignedInteger('account_id', false, true)->after('piggy_bank_id')->nullable();
                $table->unsignedInteger('from_account_id', false, true)->after('account_id')->nullable();
                $table->foreign('from_account_id')->references('id')->onUpdate('cascade')->on('accounts')->onDelete('cascade');
            }
        );

        // may need to do this statment per db type, e.g. mysql, sqlite, pgres
        DB::statement('
        UPDATE
                piggy_bank_events t1,
                piggy_banks t2
        SET
                t1.account_id = t2.account_id
        WHERE
                t1.piggy_bank_id = t2.id;
        ');

        Schema::table('piggy_bank_events', function (Blueprint $table) {
            $table->unsignedInteger('account_id')->nullable(false)->change();
            $table->foreign('account_id')->references('id')->onUpdate('cascade')->on('accounts')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Schema::dropIfExists('piggy_bank_accounts');
        Schema::table('piggy_bank_events', function (Blueprint $table) {
            $table->dropColumn(['transfer']);
            $table->dropForeign('piggy_bank_events_account_id_foreign');
            $table->dropColumn(['account_id']);

            $table->dropForeign('piggy_bank_events_from_account_id_foreign');
            $table->dropColumn(['from_account_id']);
        });
    }
}