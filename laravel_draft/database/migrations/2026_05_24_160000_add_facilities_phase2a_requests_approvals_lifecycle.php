<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('facility_service_requests')) {
            Schema::create('facility_service_requests', function (Blueprint $table) {
                $table->id();
                $table->string('request_no', 40)->unique();
                $table->string('request_type', 40)->default('REPAIR')->index();
                $table->foreignId('facility_registry_id')->nullable()->constrained('facility_registries')->nullOnDelete();
                $table->foreignId('facility_component_id')->nullable()->constrained('facility_components')->nullOnDelete();
                $table->text('location_text')->nullable();
                $table->foreignId('work_category_id')->constrained('facility_work_categories')->restrictOnDelete();
                $table->text('problem_description');
                $table->string('priority', 20)->default('NORMAL')->index();
                $table->boolean('emergency_flag')->default(false)->index();
                $table->text('emergency_reason')->nullable();
                $table->string('requested_by_user_id', 120)->nullable()->index();
                $table->dateTime('requested_at')->index();
                $table->string('status', 40)->default('SUBMITTED')->index();
                $table->boolean('material_required')->default(false);
                $table->text('material_remarks')->nullable();
                $table->decimal('estimated_cost', 12, 2)->nullable();
                $table->string('approval_required_level', 40)->default('SUPERVISOR')->index();
                $table->string('reviewed_by_user_id', 120)->nullable();
                $table->dateTime('reviewed_at')->nullable();
                $table->string('approval_decision', 40)->nullable();
                $table->text('approval_remarks')->nullable();
                $table->text('rejected_reason')->nullable();
                $table->text('cancelled_reason')->nullable();
                $table->string('created_by_user_id', 120)->nullable();
                $table->string('updated_by_user_id', 120)->nullable();
                $table->timestamps();

                $table->index(['status', 'priority'], 'fsr_status_priority_idx');
                $table->index(['facility_registry_id', 'status'], 'fsr_facility_status_idx');
            });
        }

        if (!Schema::hasTable('facility_request_approvals')) {
            Schema::create('facility_request_approvals', function (Blueprint $table) {
                $table->id();
                $table->foreignId('facility_service_request_id')->constrained('facility_service_requests')->cascadeOnDelete();
                $table->string('decision', 40)->index();
                $table->string('decision_level', 40)->index();
                $table->string('decided_by_user_id', 120)->nullable()->index();
                $table->dateTime('decided_at')->index();
                $table->text('remarks')->nullable();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('facility_work_orders')) {
            Schema::table('facility_work_orders', function (Blueprint $table) {
                if (!Schema::hasColumn('facility_work_orders', 'source_request_id')) {
                    $table->foreignId('source_request_id')->nullable()->after('id')->constrained('facility_service_requests')->nullOnDelete();
                    $table->unique('source_request_id', 'fwo_source_request_unique');
                }
                if (!Schema::hasColumn('facility_work_orders', 'work_type')) {
                    $table->string('work_type', 40)->nullable()->after('title')->index();
                }
                if (!Schema::hasColumn('facility_work_orders', 'description')) {
                    $table->text('description')->nullable()->after('work_type');
                }
                if (!Schema::hasColumn('facility_work_orders', 'assigned_to')) {
                    $table->string('assigned_to', 160)->nullable()->after('description');
                }
                if (!Schema::hasColumn('facility_work_orders', 'assigned_at')) {
                    $table->dateTime('assigned_at')->nullable()->after('assigned_to');
                }
                if (!Schema::hasColumn('facility_work_orders', 'started_at')) {
                    $table->dateTime('started_at')->nullable()->after('assigned_at');
                }
                if (!Schema::hasColumn('facility_work_orders', 'completed_at')) {
                    $table->dateTime('completed_at')->nullable()->after('completed_on');
                }
                if (!Schema::hasColumn('facility_work_orders', 'completion_remarks')) {
                    $table->text('completion_remarks')->nullable()->after('completed_at');
                }
                if (!Schema::hasColumn('facility_work_orders', 'verified_by')) {
                    $table->string('verified_by', 120)->nullable()->after('verified_on');
                }
                if (!Schema::hasColumn('facility_work_orders', 'verified_at')) {
                    $table->dateTime('verified_at')->nullable()->after('verified_by');
                }
                if (!Schema::hasColumn('facility_work_orders', 'verification_result')) {
                    $table->string('verification_result', 40)->nullable()->after('verified_at');
                }
                if (!Schema::hasColumn('facility_work_orders', 'verification_remarks')) {
                    $table->text('verification_remarks')->nullable()->after('verification_result');
                }
                if (!Schema::hasColumn('facility_work_orders', 'closed_at')) {
                    $table->dateTime('closed_at')->nullable()->after('verification_remarks');
                }
                if (!Schema::hasColumn('facility_work_orders', 'cancelled_reason')) {
                    $table->text('cancelled_reason')->nullable()->after('closed_at');
                }
            });
        }

        if (!Schema::hasTable('facility_work_order_status_histories')) {
            Schema::create('facility_work_order_status_histories', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('facility_work_order_id');
                $table->string('from_status', 40)->nullable();
                $table->string('to_status', 40);
                $table->string('action_by_user_id', 120)->nullable();
                $table->dateTime('action_at');
                $table->text('remarks')->nullable();
                $table->timestamps();

                $table->foreign('facility_work_order_id', 'fwoh_order_fk')
                    ->references('id')
                    ->on('facility_work_orders')
                    ->cascadeOnDelete();

                $table->index('to_status', 'fwoh_to_status_idx');
                $table->index('action_by_user_id', 'fwoh_actor_idx');
                $table->index('action_at', 'fwoh_action_at_idx');
                $table->index(['facility_work_order_id', 'action_at'], 'fwoh_order_action_idx');
            });
        } elseif (DB::connection()->getDriverName() === 'mysql') {
            $tableName = 'facility_work_order_status_histories';

            $hasForeignKey = (int) DB::selectOne(
                "SELECT COUNT(*) AS aggregate
                 FROM information_schema.KEY_COLUMN_USAGE
                 WHERE TABLE_SCHEMA = DATABASE()
                   AND TABLE_NAME = ?
                   AND COLUMN_NAME = 'facility_work_order_id'
                   AND REFERENCED_TABLE_NAME = 'facility_work_orders'",
                [$tableName]
            )->aggregate > 0;

            if (!$hasForeignKey) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->foreign('facility_work_order_id', 'fwoh_order_fk')
                        ->references('id')
                        ->on('facility_work_orders')
                        ->cascadeOnDelete();
                });
            }

            $hasIndex = function (string $indexName) use ($tableName): bool {
                return (int) DB::selectOne(
                    "SELECT COUNT(*) AS aggregate
                     FROM information_schema.STATISTICS
                     WHERE TABLE_SCHEMA = DATABASE()
                       AND TABLE_NAME = ?
                       AND INDEX_NAME = ?",
                    [$tableName, $indexName]
                )->aggregate > 0;
            };

            if (!$hasIndex('fwoh_to_status_idx')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->index('to_status', 'fwoh_to_status_idx');
                });
            }

            if (!$hasIndex('fwoh_actor_idx')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->index('action_by_user_id', 'fwoh_actor_idx');
                });
            }

            if (!$hasIndex('fwoh_action_at_idx')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->index('action_at', 'fwoh_action_at_idx');
                });
            }

            if (!$hasIndex('fwoh_order_action_idx')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->index(['facility_work_order_id', 'action_at'], 'fwoh_order_action_idx');
                });
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('facility_work_order_status_histories');

        if (Schema::hasTable('facility_work_orders')) {
            Schema::table('facility_work_orders', function (Blueprint $table) {
                if (Schema::hasColumn('facility_work_orders', 'source_request_id')) {
                    $table->dropUnique('fwo_source_request_unique');
                    $table->dropConstrainedForeignId('source_request_id');
                }
                foreach (['work_type','description','assigned_to','assigned_at','started_at','completed_at','completion_remarks','verified_by','verified_at','verification_result','verification_remarks','closed_at','cancelled_reason'] as $column) {
                    if (Schema::hasColumn('facility_work_orders', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        Schema::dropIfExists('facility_request_approvals');
        Schema::dropIfExists('facility_service_requests');
    }
};
