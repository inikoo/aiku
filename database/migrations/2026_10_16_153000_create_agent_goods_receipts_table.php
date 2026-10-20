<?php

/*
    * Author: Vika Aqordi
    * Created on: 2026-10-16 15:30
    * Github: https://github.com/aqordeon
    * Copyright: 2026
*/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('agent_goods_receipts', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedSmallInteger('group_id')->index();
            $table->foreign('group_id')->references('id')->on('groups');
            $table->unsignedSmallInteger('agent_id')->index();
            $table->foreign('agent_id')->references('id')->on('agents');
            $table->unsignedInteger('supplier_id')->nullable()->index();
            $table->foreign('supplier_id')->references('id')->on('suppliers')->nullOnDelete();
            $table->unsignedInteger('number');
            $table->string('reference')->index();
            $table->string('slug')->unique()->collation('und_ns');
            $table->string('state')->index();
            $table->date('received_at')->index();
            $table->unsignedSmallInteger('user_id')->nullable()->index();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->string('supplier_delivery_note')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedSmallInteger('number_items')->default(0);
            $table->decimal('quantity', 16, 3)->default(0);
            $table->timestampTz('cancelled_at')->nullable();
            $table->unsignedSmallInteger('cancelled_by_user_id')->nullable();
            $table->foreign('cancelled_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->timestampsTz();
            $table->unique(['agent_id', 'number']);
        });

        Schema::create('agent_goods_receipt_items', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('agent_goods_receipt_id')->index();
            $table->foreign('agent_goods_receipt_id')->references('id')->on('agent_goods_receipts')->cascadeOnDelete();
            $table->unsignedSmallInteger('organisation_id')->index();
            $table->foreign('organisation_id')->references('id')->on('organisations');
            $table->unsignedInteger('purchase_order_id')->nullable()->index();
            $table->foreign('purchase_order_id')->references('id')->on('purchase_orders')->nullOnDelete();
            $table->unsignedInteger('purchase_order_transaction_id')->nullable()->index();
            $table->foreign('purchase_order_transaction_id')->references('id')->on('purchase_order_transactions')->nullOnDelete();
            $table->unsignedInteger('supplier_product_id')->index();
            $table->foreign('supplier_product_id')->references('id')->on('supplier_products');
            $table->decimal('quantity', 16, 3);
            $table->timestampsTz();
        });

        Schema::table('agent_warehouse_stocks', function (Blueprint $table) {
            $table->unsignedInteger('agent_goods_receipt_id')->nullable()->index();
            $table->foreign('agent_goods_receipt_id')->references('id')->on('agent_goods_receipts')->nullOnDelete();
        });

        $this->receiptsForStockReceivedBefore();
    }

    /**
     * Goods received on the In production page before goods receipts existed: each receive stored its rows at one
     * instant, so the rows sharing agent, moment and supplier become one goods receipt.
     */
    private function receiptsForStockReceivedBefore(): void
    {
        $events = DB::table('agent_warehouse_stocks')
            ->join('supplier_products', 'supplier_products.id', 'agent_warehouse_stocks.supplier_product_id')
            ->join('agents', 'agents.id', 'agent_warehouse_stocks.agent_id')
            ->whereNotNull('agent_warehouse_stocks.purchase_order_transaction_id')
            ->groupBy('agent_warehouse_stocks.group_id', 'agent_warehouse_stocks.agent_id', 'agents.code', 'supplier_products.supplier_id', 'agent_warehouse_stocks.created_at', 'agent_warehouse_stocks.notes')
            ->orderBy('agent_warehouse_stocks.created_at')
            ->select([
                'agent_warehouse_stocks.group_id',
                'agent_warehouse_stocks.agent_id',
                'agents.code as agent_code',
                'supplier_products.supplier_id',
                'agent_warehouse_stocks.created_at',
                'agent_warehouse_stocks.notes',
            ])
            ->get();

        $numbers = [];
        foreach ($events as $event) {
            $number    = $numbers[$event->agent_id] = ($numbers[$event->agent_id] ?? 0) + 1;
            $reference = sprintf('GR-%05d', $number);

            $stocks = DB::table('agent_warehouse_stocks')
                ->join('supplier_products', 'supplier_products.id', 'agent_warehouse_stocks.supplier_product_id')
                ->where('agent_warehouse_stocks.agent_id', $event->agent_id)
                ->where('agent_warehouse_stocks.created_at', $event->created_at)
                ->where('supplier_products.supplier_id', $event->supplier_id)
                ->whereNotNull('agent_warehouse_stocks.purchase_order_transaction_id')
                ->when($event->notes === null, fn ($query) => $query->whereNull('agent_warehouse_stocks.notes'), fn ($query) => $query->where('agent_warehouse_stocks.notes', $event->notes))
                ->select('agent_warehouse_stocks.*')
                ->get();

            $receiptId = DB::table('agent_goods_receipts')->insertGetId([
                'group_id'     => $event->group_id,
                'agent_id'     => $event->agent_id,
                'supplier_id'  => $event->supplier_id,
                'number'       => $number,
                'reference'    => $reference,
                'slug'         => strtolower($event->agent_code.'-'.$reference),
                'state'        => 'received',
                'received_at'  => substr((string) $event->created_at, 0, 10),
                'notes'        => $event->notes,
                'number_items' => $stocks->count(),
                'quantity'     => $stocks->sum('quantity'),
                'created_at'   => $event->created_at,
                'updated_at'   => $event->created_at,
            ]);

            DB::table('agent_goods_receipt_items')->insert($stocks->map(fn ($stock) => [
                'agent_goods_receipt_id'        => $receiptId,
                'organisation_id'               => $stock->organisation_id,
                'purchase_order_id'             => $stock->purchase_order_id,
                'purchase_order_transaction_id' => $stock->purchase_order_transaction_id,
                'supplier_product_id'           => $stock->supplier_product_id,
                'quantity'                      => $stock->quantity,
                'created_at'                    => $stock->created_at,
                'updated_at'                    => $stock->created_at,
            ])->all());

            DB::table('agent_warehouse_stocks')->whereIn('id', $stocks->pluck('id'))->update(['agent_goods_receipt_id' => $receiptId]);
        }
    }

    public function down(): void
    {
        Schema::table('agent_warehouse_stocks', function (Blueprint $table) {
            $table->dropForeign(['agent_goods_receipt_id']);
            $table->dropColumn('agent_goods_receipt_id');
        });
        Schema::dropIfExists('agent_goods_receipt_items');
        Schema::dropIfExists('agent_goods_receipts');
    }
};
