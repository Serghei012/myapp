<?php
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateAdoreMeStorageTestsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        Schema::create(
            'tests',
            function (Blueprint $table) {
                $table->increments('id');
                $table->timestamps();
                /** @noinspection PhpUndefinedMethodInspection */
                $table->string('name')->nullable();
                /** @noinspection PhpUndefinedMethodInspection */
                $table->string('code')->nullable();
                /** @noinspection PhpUndefinedMethodInspection */
                $table->string('title')->nullable();
                /** @noinspection PhpUndefinedMethodInspection */
                $table->integer('priority')->unsigned()->default(null)->nullable();
                /** @noinspection PhpUndefinedMethodInspection */
                $table->boolean('enabled')->default(false);
                /** @noinspection PhpUndefinedMethodInspection */
                $table->text('extra')->nullable();

                // Indexes
                $table->unique(['code']);
                $table->unique(['priority']);
                $table->index(['code']);
                $table->index(['enabled']);
                $table->index(['priority']);
            }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        Schema::drop('tests');
    }
}
