<?php

use App\Support\Database\MigrationUtils;
use Illuminate\Database\Migrations\Migration;

class FixExporterStudentView extends Migration
{
    use MigrationUtils;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->dropView('public.exporter_student');

        $this->executeSqlFile(
            database_path('sqls/views/public.exporter_student-2020-04-17.sql')
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->dropView('public.exporter_student');

        $this->executeSqlFile(
            database_path('sqls/views/public.exporter_student-2020-04-03.sql')
        );
    }
}
