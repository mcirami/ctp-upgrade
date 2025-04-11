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

		Schema::table('bonus', function (Blueprint $table) {
			// Drop by explicit constraint name, e.g. 'FK_author'
			$table->dropForeign('FK_author');
		});

		Schema::table('user_has_bonus', function (Blueprint $table) {
			// Drop by explicit constraint name, e.g. 'FK_user'
			$table->dropForeign('FK_user');
		});

		Schema::table('user_has_notification', function (Blueprint $table) {
			// Drop by explicit constraint name, e.g. 'FK_user'
			$table->dropForeign('FK_user_id');
		});

		Schema::table('adjustments_log', function (Blueprint $table) {
			// Drop by explicit constraint name, e.g. 'FK_user'
			$table->dropForeign('adjustments_log_ibfk_2');
		});

		Schema::table('affiliate_email_pools', function (Blueprint $table) {
			// Drop by explicit constraint name, e.g. 'FK_user'
			$table->dropForeign('affiliate_email_pools_user_id_foreign');
		});

		Schema::table('aggregate_reports', function (Blueprint $table) {
			// Drop by explicit constraint name, e.g. 'FK_user'
			$table->dropForeign('aggregate_reports_user_id_foreign');
		});

		Schema::table('banned_users', function (Blueprint $table) {
			// Drop by explicit constraint name, e.g. 'FK_user'
			$table->dropForeign('banned_users_ibfk_1');
		});

		Schema::table('blocked_sub_ids', function (Blueprint $table) {
			$table->dropForeign('blocked_sub_ids_rep_idrep_foreign');
		});

		Schema::table('click_bonus', function (Blueprint $table) {
			$table->dropForeign('click_bonus_ibfk_1');
		});

		Schema::table('conversions', function (Blueprint $table) {
			$table->dropForeign('conversions_ibfk_1');
		});

		Schema::table('clicks', function (Blueprint $table) {
			$table->dropForeign('fk_clicks_rep1');
		});

		Schema::table('privileges', function (Blueprint $table) {
			$table->dropForeign('fk_privileges_rep1');
		});

		Schema::table('rep_has_offer', function (Blueprint $table) {
			$table->dropForeign('fk_rep_has_offer_rep');
		});

		Schema::table('rep', function (Blueprint $table) {
			$table->dropForeign('fk_rep_rep1');
		});

	    Schema::table('free_sign_ups', function (Blueprint $table) {
		    $table->dropForeign('free_sign_ups_ibfk_2');
	    });

	    Schema::table('notifications', function (Blueprint $table) {
		    $table->dropForeign('notifications_ibfk_1');
	    });

	    Schema::table('payout_data', function (Blueprint $table) {
		    $table->dropForeign('payout_data_rep_idrep_foreign');
	    });

	    Schema::table('payout_logs', function (Blueprint $table) {
		    $table->dropForeign('payout_logs_user_id_foreign');
	    });

	    Schema::table('referrals', function (Blueprint $table) {
		    $table->dropForeign('referrals_ibfk_1');
	    });

	    Schema::table('referrals', function (Blueprint $table) {
		    $table->dropForeign('referrals_ibfk_2');
	    });

	    Schema::table('referrals_paid', function (Blueprint $table) {
		    $table->dropForeign('referrals_paid_ibfk_1');
	    });


	    Schema::table('referrals_paid', function (Blueprint $table) {
		    $table->dropForeign('referrals_paid_ibfk_2');
	    });

	    Schema::table('report_permissions', function (Blueprint $table) {
		    $table->dropForeign('report_permissions_ibfk_1');
	    });

	    Schema::table('salary', function (Blueprint $table) {
		    $table->dropForeign('salary_ibfk_1');
	    });

	    Schema::table('sms_clients', function (Blueprint $table) {
		    $table->dropForeign('sms_clients_user_id_foreign');
	    });

	    Schema::table('user_offer_caps', function (Blueprint $table) {
		    $table->dropForeign('user_offer_caps_rep_idrep_foreign');
	    });

	    Schema::table('user_postbacks', function (Blueprint $table) {
		    $table->dropForeign('user_postbacks_ibfk_1');
	    });

	    Schema::table('rep', function (Blueprint $table) {
            $table->dropPrimary('rep_idrep_primary');
			$table->unsignedInteger('idrep')->change();
			$table->primary('idrep');
        });

	    Schema::table('rep', function (Blueprint $table) {
		    $table->foreign('referrer_repid', 'fk_rep_rep1')
		          ->references('idrep')
		          ->on('rep')
		          ->onDelete('cascade')
		          ->onUpdate('cascade');
	    });

	    Schema::table('bonus', function (Blueprint $table) {
		    $table->foreign('author', 'FK_author')
		          ->references('idrep')->on('rep')
		          ->onUpdate('cascade')
		          ->onDelete('cascade');
	    });

	    Schema::table('user_has_bonus', function (Blueprint $table) {
		    $table->foreign('user_id', 'FK_user')
		          ->references('idrep')->on('rep')
		          ->onUpdate('cascade')
		          ->onDelete('cascade');
	    });

	    Schema::table('user_has_notification', function (Blueprint $table) {
		    $table->foreign('user_id', 'FK_user_id')
		          ->references('idrep')->on('rep')
		          ->onUpdate('cascade')
		          ->onDelete('cascade');
	    });

	    Schema::table('adjustments_log', function (Blueprint $table) {
		    $table->foreign('user_id', 'adjustments_log_ibfk_2')
		          ->references('idrep')->on('rep')
		          ->onUpdate('cascade')
		          ->onDelete('cascade');
	    });

	    Schema::table('affiliate_email_pools', function (Blueprint $table) {
		    $table->foreign('user_id', 'affiliate_email_pools_user_id_foreign')
		          ->references('idrep')->on('rep')
		          ->onUpdate('cascade')
		          ->onDelete('cascade');
	    });

	    Schema::table('aggregate_reports', function (Blueprint $table) {
		    $table->foreign('user_id', 'aggregate_reports_user_id_foreign')
		          ->references('idrep')->on('rep')
		          ->onUpdate('cascade')
		          ->onDelete('cascade');
	    });

	    Schema::table('banned_users', function (Blueprint $table) {
		    $table->foreign('user_id', 'banned_users_ibfk_1')
		          ->references('idrep')->on('rep')
		          ->onUpdate('cascade')
		          ->onDelete('cascade');
	    });

	    Schema::table('blocked_sub_ids', function (Blueprint $table) {
		    $table->foreign('rep_idrep', 'blocked_sub_ids_rep_idrep_foreign')
		          ->references('idrep')->on('rep')
		          ->onUpdate('cascade')
		          ->onDelete('cascade');
	    });

	    Schema::table('click_bonus', function (Blueprint $table) {
		    $table->foreign('aff_id', 'click_bonus_ibfk_1')
		          ->references('idrep')->on('rep')
		          ->onUpdate('cascade')
		          ->onDelete('cascade');
	    });

	    Schema::table('conversions', function (Blueprint $table) {
		    $table->foreign('user_id', 'conversions_ibfk_1')
		          ->references('idrep')->on('rep')
		          ->onUpdate('cascade')
		          ->onDelete('cascade');
	    });

	    Schema::table('clicks', function (Blueprint $table) {
		    $table->foreign('rep_idrep', 'fk_clicks_rep1')
		          ->references('idrep')->on('rep')
		          ->onUpdate('cascade')
		          ->onDelete('cascade');
	    });

	    Schema::table('privileges', function (Blueprint $table) {
		    $table->foreign('rep_idrep', 'fk_privileges_rep1')
		          ->references('idrep')->on('rep')
		          ->onUpdate('cascade')
		          ->onDelete('cascade');
	    });

	    Schema::table('rep_has_offer', function (Blueprint $table) {
		    $table->foreign('rep_idrep', 'fk_rep_has_offer_rep')
		          ->references('idrep')->on('rep')
		          ->onUpdate('cascade')
		          ->onDelete('cascade');
	    });

	    Schema::table('free_sign_ups', function (Blueprint $table) {
		    $table->foreign('user_id', 'free_sign_ups_ibfk_2')
		          ->references('idrep')->on('rep')
		          ->onUpdate('cascade')
		          ->onDelete('cascade');
	    });

	    Schema::table('notifications', function (Blueprint $table) {
		    $table->foreign('author', 'notifications_ibfk_1')
		          ->references('idrep')->on('rep')
		          ->onUpdate('cascade')
		          ->onDelete('cascade');
	    });

	    Schema::table('payout_data', function (Blueprint $table) {
		    $table->foreign('rep_idrep', 'payout_data_rep_idrep_foreign')
		          ->references('idrep')->on('rep')
		          ->onUpdate('cascade')
		          ->onDelete('cascade');
	    });

	    Schema::table('payout_logs', function (Blueprint $table) {
		    $table->foreign('user_id', 'payout_logs_user_id_foreign')
		          ->references('idrep')->on('rep')
		          ->onUpdate('cascade')
		          ->onDelete('cascade');

	    });

	    Schema::table('referrals', function (Blueprint $table) {
		    $table->foreign('aff_id', 'referrals_ibfk_1')
		          ->references('idrep')->on('rep')
		          ->onUpdate('cascade')
		          ->onDelete('cascade');
	    });

	    Schema::table('referrals', function (Blueprint $table) {
		    $table->foreign('referrer_user_id', 'referrals_ibfk_2')
		          ->references('idrep')->on('rep')
		          ->onUpdate('cascade')
		          ->onDelete('cascade');
	    });

	    Schema::table('referrals_paid', function (Blueprint $table) {
		    $table->foreign('aff_id', 'referrals_paid_ibfk_1')
		          ->references('idrep')->on('rep')
		          ->onUpdate('cascade')
		          ->onDelete('cascade');
	    });

	    Schema::table('referrals_paid', function (Blueprint $table) {
		    $table->foreign('referred_aff_id', 'referrals_paid_ibfk_2')
		          ->references('idrep')->on('rep')
		          ->onUpdate('cascade')
		          ->onDelete('cascade');
	    });

	    Schema::table('report_permissions', function (Blueprint $table) {
		    $table->foreign('user_id', 'report_permissions_ibfk_1')
		          ->references('idrep')->on('rep')
		          ->onUpdate('cascade')
		          ->onDelete('cascade');
	    });

	    Schema::table('salary', function (Blueprint $table) {
		    $table->foreign('user_id', 'salary_ibfk_1')
		          ->references('idrep')->on('rep')
		          ->onUpdate('cascade')
		          ->onDelete('cascade');
	    });

	    Schema::table('sms_clients', function (Blueprint $table) {
		    $table->foreign('user_id', 'sms_clients_user_id_foreign')
		          ->references('idrep')->on('rep')
		          ->onUpdate('cascade')
		          ->onDelete('cascade');
	    });

	    Schema::table('user_offer_caps', function (Blueprint $table) {
		    $table->foreign('rep_idrep', 'user_offer_caps_rep_idrep_foreign')
		          ->references('idrep')->on('rep')
		          ->onUpdate('cascade')
		          ->onDelete('cascade');
	    });

	    Schema::table('user_postbacks', function (Blueprint $table) {
		    $table->foreign('user_id', 'user_postbacks_ibfk_1')
		          ->references('idrep')->on('rep')
		          ->onUpdate('cascade')
		          ->onDelete('cascade');
	    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
	    Schema::table('bonus', function (Blueprint $table) {
		    $table->dropForeign('FK_author');
	    });

	    Schema::table('user_has_bonus', function (Blueprint $table) {
		    // Drop by explicit constraint name, e.g. 'FK_user'
		    $table->dropForeign('FK_user');
	    });

	    Schema::table('user_has_notification', function (Blueprint $table) {
		    // Drop by explicit constraint name, e.g. 'FK_user'
		    $table->dropForeign('FK_user_id');
	    });

	    Schema::table('adjustments_log', function (Blueprint $table) {
		    // Drop by explicit constraint name, e.g. 'FK_user'
		    $table->dropForeign('adjustments_log_ibfk_2');
	    });

	    Schema::table('affiliate_email_pools', function (Blueprint $table) {
		    // Drop by explicit constraint name, e.g. 'FK_user'
		    $table->dropForeign('affiliate_email_pools_user_id_foreign');
	    });

	    Schema::table('aggregate_reports', function (Blueprint $table) {
		    // Drop by explicit constraint name, e.g. 'FK_user'
		    $table->dropForeign('aggregate_reports_user_id_foreign');
	    });

	    Schema::table('banned_users', function (Blueprint $table) {
		    // Drop by explicit constraint name, e.g. 'FK_user'
		    $table->dropForeign('banned_users_ibfk_1');
	    });

	    Schema::table('blocked_sub_ids', function (Blueprint $table) {
		    $table->dropForeign('blocked_sub_ids_rep_idrep_foreign');
	    });

	    Schema::table('click_bonus', function (Blueprint $table) {
		    $table->dropForeign('click_bonus_ibfk_1');
	    });

	    Schema::table('conversions', function (Blueprint $table) {
		    $table->dropForeign('conversions_ibfk_1');
	    });

	    Schema::table('clicks', function (Blueprint $table) {
		    $table->dropForeign('fk_clicks_rep1');
	    });

	    Schema::table('privileges', function (Blueprint $table) {
		    $table->dropForeign('fk_privileges_rep1');
	    });

	    Schema::table('rep_has_offer', function (Blueprint $table) {
		    $table->dropForeign('fk_rep_has_offer_rep');
	    });

	    Schema::table('rep', function (Blueprint $table) {
		    $table->dropForeign('fk_rep_rep1');
	    });

	    Schema::table('free_sign_ups', function (Blueprint $table) {
		    $table->dropForeign('free_sign_ups_ibfk_2');
	    });

	    Schema::table('notifications', function (Blueprint $table) {
		    $table->dropForeign('notifications_ibfk_1');
	    });

	    Schema::table('payout_data', function (Blueprint $table) {
		    $table->dropForeign('payout_data_rep_idrep_foreign');
	    });

	    Schema::table('payout_logs', function (Blueprint $table) {
		    $table->dropForeign('payout_logs_user_id_foreign');
	    });

	    Schema::table('referrals', function (Blueprint $table) {
		    $table->dropForeign('referrals_ibfk_1');
	    });

	    Schema::table('referrals', function (Blueprint $table) {
		    $table->dropForeign('referrals_ibfk_2');
	    });

	    Schema::table('referrals_paid', function (Blueprint $table) {
		    $table->dropForeign('referrals_paid_ibfk_1');
	    });

	    Schema::table('referrals_paid', function (Blueprint $table) {
		    $table->dropForeign('referrals_paid_ibfk_2');
	    });

	    Schema::table('report_permissions', function (Blueprint $table) {
		    $table->dropForeign('report_permissions_ibfk_1');
	    });

	    Schema::table('salary', function (Blueprint $table) {
		    $table->dropForeign('salary_ibfk_1');
	    });

	    Schema::table('sms_clients', function (Blueprint $table) {
		    $table->dropForeign('sms_clients_user_id_foreign');
	    });

	    Schema::table('user_offer_caps', function (Blueprint $table) {
		    $table->dropForeign('user_offer_caps_rep_idrep_foreign');
	    });

	    Schema::table('user_postbacks', function (Blueprint $table) {
		    $table->dropForeign('user_postbacks_ibfk_1');
	    });

	    // 2) Restore original rep table structure
	    Schema::table('rep', function (Blueprint $table) {
		    $table->dropPrimary(['idrep']);
		    // or if originally it was ->increments('idrep')
		    // we can do:
		    // $table->increments('idrep')->change();
		    $table->unsignedInteger('idrep')->autoIncrement()->change();
		    // Re-add primary if needed:
		    $table->primary('idrep');
	    });

	    Schema::table('rep', function (Blueprint $table) {
		    $table->foreign('referrer_repid', 'fk_rep_rep1')
		          ->references('idrep')->on('rep')
		          ->onUpdate('cascade')
		          ->onDelete('cascade');
	    });

	    // 3) Re-add the old foreign key
	    Schema::table('bonus', function (Blueprint $table) {
		    $table->foreign('author', 'FK_author')
		          ->references('idrep')->on('rep')
		          ->onUpdate('cascade')
		          ->onDelete('cascade');
	    });

	    Schema::table('user_has_bonus', function (Blueprint $table) {
		    $table->foreign('user_id', 'FK_user')
		          ->references('idrep')->on('rep')
		          ->onUpdate('cascade')
		          ->onDelete('cascade');
	    });

	    Schema::table('user_has_notification', function (Blueprint $table) {
		    $table->foreign('user_id', 'FK_user_id')
		          ->references('idrep')->on('rep')
		          ->onUpdate('cascade')
		          ->onDelete('cascade');
	    });

	    Schema::table('adjustments_log', function (Blueprint $table) {
		    $table->foreign('user_id', 'adjustments_log_ibfk_2')
		          ->references('idrep')->on('rep')
		          ->onUpdate('cascade')
		          ->onDelete('cascade');
	    });

	    Schema::table('affiliate_email_pools', function (Blueprint $table) {
		    $table->foreign('user_id', 'affiliate_email_pools_user_id_foreign')
		          ->references('idrep')->on('rep')
		          ->onUpdate('cascade')
		          ->onDelete('cascade');
	    });

	    Schema::table('aggregate_reports', function (Blueprint $table) {
		    $table->foreign('user_id', 'aggregate_reports_user_id_foreign')
		          ->references('idrep')->on('rep')
		          ->onUpdate('cascade')
		          ->onDelete('cascade');
	    });

	    Schema::table('banned_users', function (Blueprint $table) {
		    $table->foreign('user_id', 'banned_users_ibfk_1')
		          ->references('idrep')->on('rep')
		          ->onUpdate('cascade')
		          ->onDelete('cascade');
	    });

	    Schema::table('blocked_sub_ids', function (Blueprint $table) {
		    $table->foreign('rep_idrep', 'blocked_sub_ids_rep_idrep_foreign')
		          ->references('idrep')->on('rep')
		          ->onUpdate('cascade')
		          ->onDelete('cascade');
	    });

	    Schema::table('click_bonus', function (Blueprint $table) {
		    $table->foreign('aff_id', 'click_bonus_ibfk_1')
		          ->references('idrep')->on('rep')
		          ->onUpdate('cascade')
		          ->onDelete('cascade');
	    });

	    Schema::table('conversions', function (Blueprint $table) {
		    $table->foreign('user_id', 'conversions_ibfk_1')
		          ->references('idrep')->on('rep')
		          ->onUpdate('cascade')
		          ->onDelete('cascade');
	    });

	    Schema::table('clicks', function (Blueprint $table) {
		    $table->foreign('rep_idrep', 'fk_clicks_rep1')
		          ->references('idrep')->on('rep')
		          ->onUpdate('cascade')
		          ->onDelete('cascade');
	    });

	    Schema::table('privileges', function (Blueprint $table) {
		    $table->foreign('rep_idrep', 'fk_privileges_rep1')
		          ->references('idrep')->on('rep')
		          ->onUpdate('cascade')
		          ->onDelete('cascade');
	    });

	    Schema::table('rep_has_offer', function (Blueprint $table) {
		    $table->foreign('rep_idrep', 'fk_rep_has_offer_rep')
		          ->references('idrep')->on('rep')
		          ->onUpdate('cascade')
		          ->onDelete('cascade');
	    });

	    Schema::table('free_sign_ups', function (Blueprint $table) {
		    $table->foreign('user_id', 'free_sign_ups_ibfk_2')
		          ->references('idrep')->on('rep')
		          ->onUpdate('cascade')
		          ->onDelete('cascade');
	    });

	    Schema::table('notifications', function (Blueprint $table) {
		    $table->foreign('author', 'notifications_ibfk_1')
		          ->references('idrep')->on('rep')
		          ->onUpdate('cascade')
		          ->onDelete('cascade');
	    });

	    Schema::table('payout_data', function (Blueprint $table) {
		    $table->foreign('rep_idrep', 'payout_data_rep_idrep_foreign')
		          ->references('idrep')->on('rep')
		          ->onUpdate('cascade')
		          ->onDelete('cascade');
	    });

	    Schema::table('payout_logs', function (Blueprint $table) {
		    $table->foreign('user_id', 'payout_logs_user_id_foreign')
		          ->references('idrep')->on('rep')
		          ->onUpdate('cascade')
		          ->onDelete('cascade');
	    });

	    Schema::table('referrals', function (Blueprint $table) {
		    $table->foreign('aff_id', 'referrals_ibfk_1')
		          ->references('idrep')->on('rep')
		          ->onUpdate('cascade')
		          ->onDelete('cascade');
	    });

	    Schema::table('referrals', function (Blueprint $table) {
		    $table->foreign('referrer_user_id', 'referrals_ibfk_2')
		          ->references('idrep')->on('rep')
		          ->onUpdate('cascade')
		          ->onDelete('cascade');
	    });

	    Schema::table('referrals_paid', function (Blueprint $table) {
		    $table->foreign('aff_id', 'referrals_paid_ibfk_1')
		          ->references('idrep')->on('rep')
		          ->onUpdate('cascade')
		          ->onDelete('cascade');
	    });

	    Schema::table('referrals_paid', function (Blueprint $table) {
		    $table->foreign('referred_aff_id', 'referrals_paid_ibfk_2')
		          ->references('idrep')->on('rep')
		          ->onUpdate('cascade')
		          ->onDelete('cascade');
	    });

	    Schema::table('report_permissions', function (Blueprint $table) {
		    $table->foreign('user_id', 'report_permissions_ibfk_1')
		          ->references('idrep')->on('rep')
		          ->onUpdate('cascade')
		          ->onDelete('cascade');
	    });

	    Schema::table('salary', function (Blueprint $table) {
		    $table->foreign('user_id', 'salary_ibfk_1')
		          ->references('idrep')->on('rep')
		          ->onUpdate('cascade')
		          ->onDelete('cascade');
	    });

	    Schema::table('sms_clients', function (Blueprint $table) {
		    $table->foreign('user_id', 'sms_clients_user_id_foreign')
		          ->references('idrep')->on('rep')
		          ->onUpdate('cascade')
		          ->onDelete('cascade');
	    });

	    Schema::table('user_offer_caps', function (Blueprint $table) {
		    $table->foreign('rep_idrep', 'user_offer_caps_rep_idrep_foreign')
		          ->references('idrep')->on('rep')
		          ->onUpdate('cascade')
		          ->onDelete('cascade');
	    });

	    Schema::table('user_postbacks', function (Blueprint $table) {
		    $table->foreign('user_id', 'user_postbacks_ibfk_1')
		          ->references('idrep')->on('rep')
		          ->onUpdate('cascade')
		          ->onDelete('cascade');
	    });


    }
};
