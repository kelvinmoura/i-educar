<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateRelatorioViewHistorico9anosView extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared(
            'DROP VIEW IF EXISTS relatorio.view_historico_9anos;'
        );

        DB::unprepared(
            file_get_contents(database_path('sqls/views/relatorio.view_historico_9_anos.sql'))
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared(
            'DROP VIEW IF EXISTS relatorio.view_historico_9anos;'
        );
    }
}
