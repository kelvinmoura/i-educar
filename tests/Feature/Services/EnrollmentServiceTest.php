<?php

namespace Tests\Feature\Services;

use App\Exceptions\Enrollment\CancellationDateAfterAcademicYearException;
use App\Exceptions\Enrollment\CancellationDateBeforeAcademicYearException;
use App\Exceptions\Enrollment\EnrollDateAfterAcademicYearException;
use App\Exceptions\Enrollment\EnrollDateBeforeAcademicYearException;
use App\Exceptions\Enrollment\ExistsActiveEnrollmentException;
use App\Exceptions\Enrollment\NoVacancyException;
use App\Exceptions\Enrollment\PreviousCancellationDateException;
use App\Exceptions\Enrollment\PreviousEnrollDateException;
use App\Models\LegacyEnrollment;
use App\Models\LegacySchoolClass;
use App\Services\EnrollmentService;
use App\User;
use Carbon\Carbon;
use Database\Factories\LegacyEnrollmentFactory;
use Database\Factories\LegacySchoolClassFactory;
use Database\Factories\LegacySchoolClassStageFactory;
use Database\Factories\LegacyUserFactory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Throwable;

class EnrollmentServiceTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @var LegacySchoolClass
     */
    private $schoolClass;

    /**
     * @var EnrollmentService
     */
    private $service;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $user = LegacyUserFactory::new()->unique()->make();

        $user = User::find($user->id);

        $schoolClass = LegacySchoolClassFactory::new()->create();

        LegacySchoolClassStageFactory::new()->create([
            'ref_cod_turma' => $schoolClass,
        ]);

        $this->schoolClass = $schoolClass;
        $this->service = new EnrollmentService($user);
    }

    /**
     * Cancelamento de enturmação com sucesso.
     *
     * @return void
     *
     * @throws Throwable
     */
    public function test_cancel_enrollment()
    {
        $enrollment = LegacyEnrollmentFactory::new()->create([
            'ref_cod_turma' => $this->schoolClass,
        ]);

        $result = $this->service->cancelEnrollment($enrollment, now());

        $this->assertTrue($result);
        $this->assertDatabaseHas($enrollment->getTable(), [
            'ref_cod_matricula' => $enrollment->ref_cod_matricula,
            'ref_cod_turma' => $enrollment->ref_cod_turma,
            'ativo' => 0,
        ]);
    }

    /**
     * Erro ao cancelar uma enturmação devido a data de saída ser anterior ao
     * início do ano letivo.
     *
     * @return void
     *
     * @throws Throwable
     */
    public function test_cancellation_date_before_academic_year_exception()
    {
        $this->expectException(CancellationDateBeforeAcademicYearException::class);

        $stage = $this->schoolClass->stages()->first();

        $stage->data_inicio = now()->addDay();
        $stage->save();

        $enrollment = LegacyEnrollmentFactory::new()->create([
            'ref_cod_turma' => $this->schoolClass,
        ]);

        $enrollment->schoolClass->school->institution->permitir_matricula_fora_periodo_letivo = false;
        $enrollment->schoolClass->school->institution->save();

        $this->service->cancelEnrollment($enrollment, now());
    }

    /**
     * Erro ao cancelar uma enturmação devido a data de saída ser posterior ao
     * fim do ano letivo.
     *
     * @return void
     *
     * @throws Throwable
     */
    public function test_cancellation_date_after_academic_year_exception()
    {
        $this->expectException(CancellationDateAfterAcademicYearException::class);

        $stage = $this->schoolClass->stages()->first();

        $stage->data_fim = now()->subDay();
        $stage->save();

        $enrollment = LegacyEnrollmentFactory::new()->create([
            'ref_cod_turma' => $this->schoolClass,
        ]);

        $this->service->cancelEnrollment($enrollment, now());
    }

    /**
     * Erro ao cancelar uma enturmação devido a data de saída ser anterior que
     * a data de enturmação.
     *
     * @return void
     *
     * @throws Throwable
     */
    public function test_previous_cancellation_date_exception()
    {
        $this->expectException(PreviousCancellationDateException::class);

        $enrollment = LegacyEnrollmentFactory::new()->create([
            'ref_cod_turma' => $this->schoolClass,
        ]);

        $this->service->cancelEnrollment($enrollment, now()->subDay(1));
    }

    /**
     * Enturmação feita com sucesso.
     *
     * @return void
     */
    public function test_enroll()
    {
        $enrollment = LegacyEnrollmentFactory::new()->make([
            'ref_cod_turma' => $this->schoolClass,
        ]);

        $result = $this->service->enroll(
            $enrollment->registration,
            $enrollment->schoolClass,
            now()
        );

        $this->assertInstanceOf(LegacyEnrollment::class, $result);
        $this->assertDatabaseHas($enrollment->getTable(), [
            'ref_cod_matricula' => $enrollment->registration_id,
            'ref_cod_turma' => $enrollment->school_class_id,
            'ativo' => 1,
        ]);
    }

    /**
     * Sem vagas na turma.
     *
     * @return void
     */
    public function test_no_vacancy_exception()
    {
        $this->expectException(NoVacancyException::class);

        $enrollment = LegacyEnrollmentFactory::new()->make([
            'ref_cod_turma' => $this->schoolClass,
        ]);

        $enrollment->schoolClass->max_aluno = 0;

        $this->service->enroll(
            $enrollment->registration,
            $enrollment->schoolClass,
            now()
        );
    }

    /**
     * Existe uma outra enturmação ativa para a matrícula na turma.
     *
     * @return void
     */
    public function test_exists_active_enrollment_exception()
    {
        $this->expectException(ExistsActiveEnrollmentException::class);

        $enrollment = LegacyEnrollmentFactory::new()->create([
            'ref_cod_turma' => $this->schoolClass,
        ]);

        $this->service->enroll(
            $enrollment->registration,
            $enrollment->schoolClass,
            now()
        );
    }

    /**
     * Erro ao enturmar uma matrícula devido a data de entrada ser anterior ao
     * início do ano letivo.
     *
     * @return void
     *
     * @throws Throwable
     */
    public function test_enroll_date_before_academic_year_exception()
    {
        $this->expectException(EnrollDateBeforeAcademicYearException::class);

        $enrollment = LegacyEnrollmentFactory::new()->make([
            'ref_cod_turma' => $this->schoolClass,
        ]);

        $enrollment->schoolClass->school->institution->permitir_matricula_fora_periodo_letivo = false;
        $enrollment->schoolClass->school->institution->save();

        $stage = $this->schoolClass->stages()->first();

        $stage->data_inicio = now()->addDay();
        $stage->save();

        $this->service->enroll(
            $enrollment->registration,
            $enrollment->schoolClass,
            now()
        );
    }

    /**
     * Permite matrícular antes do início do ano letivo se o parâmetro permitir_matricula_fora_periodo_letivo
     * estiver habilitado na instituição
     *
     * @return void
     *
     * @throws Throwable
     */
    public function test_enroll_date_before_academic_year_allowed()
    {
        $enrollment = LegacyEnrollmentFactory::new()->make([
            'ref_cod_turma' => $this->schoolClass,
        ]);

        $enrollment->schoolClass->school->institution->permitir_matricula_fora_periodo_letivo = true;
        $enrollment->schoolClass->school->institution->save();

        $stage = $this->schoolClass->stages()->first();

        $stage->data_inicio = now()->addDay();
        $stage->save();

        $enrollment = $this->service->enroll(
            $enrollment->registration,
            $enrollment->schoolClass,
            now()
        );

        $this->assertInstanceOf(LegacyEnrollment::class, $enrollment);
    }

    /**
     * Erro ao enturmar uma matrícula devido a data de entrada ser posterior ao
     * fim do ano letivo.
     *
     * @return void
     *
     * @throws Throwable
     */
    public function test_enroll_date_after_academic_year_exception()
    {
        $this->expectException(EnrollDateAfterAcademicYearException::class);

        $enrollment = LegacyEnrollmentFactory::new()->make([
            'ref_cod_turma' => $this->schoolClass,
        ]);

        $stage = $this->schoolClass->stages()->first();

        $stage->data_fim = now()->subDay();
        $stage->save();

        $this->service->enroll(
            $enrollment->registration,
            $enrollment->schoolClass,
            now()
        );
    }

    /**
     * A data de enturmação é anterior a data de matrícula.
     *
     * @return void
     *
     * @throws Throwable
     */
    public function test_previous_enroll_date_exception()
    {
        $this->expectException(PreviousEnrollDateException::class);

        $enrollment = LegacyEnrollmentFactory::new()->create([
            'ref_cod_turma' => $this->schoolClass,
        ]);

        $this->service->cancelEnrollment($enrollment, now());

        $this->service->enroll(
            $enrollment->registration,
            $enrollment->schoolClass,
            now()->subDay(1)
        );
    }

    /**
     * Instituição sem data base, a ultima enturmação deverá ser retornada
     */
    public function test_get_previous_enrollment_withou_relocation_date()
    {
        /** @var LegacyEnrollment $enrollment */
        $enrollment = LegacyEnrollmentFactory::new()->create([
            'ref_cod_turma' => $this->schoolClass,
        ]);

        $enrollment->schoolClass->school->institution->data_base_remanejamento = null;
        $enrollment->schoolClass->school->institution->save();

        $lastEnrollment = $this->service->getPreviousEnrollmentAccordingToRelocationDate($enrollment->registration);

        $this->assertEquals($enrollment->id, $lastEnrollment->id);
    }

    /**
     * Instituição com data base antes da data de remanejamento, devera retornar null
     */
    public function test_get_previous_enrollment_with_relocation_date_before_departed_date()
    {
        /** @var LegacyEnrollment $enrollment */
        $enrollment = LegacyEnrollmentFactory::new()->create([
            'ref_cod_turma' => $this->schoolClass,
            'data_exclusao' => now(),
        ]);

        $enrollment->schoolClass->school->institution->data_base_remanejamento = Carbon::yesterday();
        $enrollment->schoolClass->school->institution->save();

        $lastEnrollment = $this->service->getPreviousEnrollmentAccordingToRelocationDate($enrollment->registration);

        $this->assertEquals($enrollment->id, $lastEnrollment->id);
    }

    /**
     * Instituição com data base depois da data de remanejamento, a ultima enturmação deverá ser retornada
     */
    public function test_get_previous_enrollment_with_relocation_date_after_departed_date()
    {
        /** @var LegacyEnrollment $enrollment */
        $enrollment = LegacyEnrollmentFactory::new()->create([
            'ref_cod_turma' => $this->schoolClass,
            'data_exclusao' => Carbon::yesterday(),
        ]);

        $enrollment->schoolClass->school->institution->data_base_remanejamento = now();
        $enrollment->schoolClass->school->institution->save();

        $lastEnrollment = $this->service->getPreviousEnrollmentAccordingToRelocationDate($enrollment->registration);

        $this->assertNull($lastEnrollment);
    }

    public function test_reorder()
    {
        $schoolClass = $this->schoolClass->getKey();

        $enrollment = LegacyEnrollmentFactory::new()->create([
            'ref_cod_turma' => $schoolClass,
        ]);

        $registration = $enrollment->registration->getKey();

        LegacyEnrollmentFactory::new()->create([
            'ref_cod_turma' => $schoolClass,
            'ref_cod_matricula' => $registration,
            'sequencial' => 3,
        ]);

        LegacyEnrollmentFactory::new()->create([
            'ref_cod_turma' => $schoolClass,
            'ref_cod_matricula' => $registration,
            'sequencial' => 5,
        ]);

        $this->service->reorder($enrollment->registration);

        $this->assertDatabaseHas('pmieducar.matricula_turma', [
            'ref_cod_turma' => $schoolClass,
            'ref_cod_matricula' => $registration,
            'sequencial' => 2,
        ]);
        $this->assertDatabaseHas('pmieducar.matricula_turma', [
            'ref_cod_turma' => $schoolClass,
            'ref_cod_matricula' => $registration,
            'sequencial' => 3,
        ]);
        $this->assertDatabaseMissing('pmieducar.matricula_turma', [
            'ref_cod_turma' => $schoolClass,
            'ref_cod_matricula' => $registration,
            'sequencial' => 5,
        ]);
    }
}
