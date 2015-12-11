<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLimitOrdersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('limit_orders', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('memberId');
			$table->foreign('memberId')->references('id')->on('members');
			$table->integer('productId');
			$table->foreign('productId')->references('id')->on('products');
			$table->integer('branchId');
			$table->foreign('branchId')->references('id')->on('branches');
			$table->integer('lot');
			$table->integer('priceMax');
			$table->integer('priceMin');
			$table->enum('status',['0','1','2']);
			$table->integer('client_stockId')->nullable();
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
		Schema::drop('limit_orders');
	}

}
