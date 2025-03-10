<?php

use App\Support\Database\MigrationUtils;
use Illuminate\Database\Migrations\Migration;

class AddTechnologicalResourcesInViewExporterStudent extends Migration
{
    use MigrationUtils;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->dropView('public.exporter_social_assistance');
        $this->dropView('public.exporter_student');

        $this->executeSqlFile(
            database_path('sqls/views/public.exporter_student-2020-05-14_v2.sql')
        );

        $this->executeSqlFile(
            database_path('sqls/views/public.exporter_social_assistance-2020-05-07.sql')
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->dropView('public.exporter_social_assistance');
        $this->dropView('public.exporter_student');

        $this->executeSqlFile(
            database_path('sqls/views/public.exporter_student-2020-05-14_v2.sql')
        );

        $this->executeSqlFile(
            database_path('sqls/views/public.exporter_social_assistance-2020-05-07.sql')
        );
    }
}
