<?php

namespace Tests\Api;

use App\Models\LegacyEnrollment;
use App_Model_MatriculaSituacao;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ProgressionWithAverageCalculationWeightedRecoveryTest extends TestCase
{
    use DatabaseTransactions;
    use DiarioApiFakeDataTestTrait;
    use DiarioApiRequestTestTrait;

    /**
     * @var LegacyEnrollment
     */
    private $enrollment;

    protected function setUp(): void
    {
        parent::setUp();
        $this->enrollment = $this->getProgressionWithAverageCalculationWeightedRecovery();
    }

    /**
     * O aluno deverá ser Aprovado depois dos lançamentos (notas e faltas) nas etapas
     */
    public function test_approved_after_all_score_and_absence_posted()
    {
        $schoolClass = $this->enrollment->schoolClass;
        $school = $schoolClass->school;

        $this->createStages($school, 4);
        $this->createDisciplines($schoolClass, 2);

        $disciplines = $schoolClass->disciplines;

        $score = [
            1 => 8,
            2 => 8,
            3 => 8,
            4 => 8,
        ];

        $absence = [
            1 => 0,
            2 => 0,
            3 => 0,
            4 => 0,
        ];

        foreach ($disciplines as $discipline) {
            $this->postAbsenceForStages($absence, $discipline);
            $response = $this->postScoreForStages($score, $discipline);

            self::assertEquals('Aprovado', $response->situacao);
        }

        $registration = $this->enrollment->registration;
        self::assertEquals(App_Model_MatriculaSituacao::APROVADO, $registration->refresh()->aprovado);
    }

    /**
     * O aluno deverá ser Reprovado por Falta depois do lançamento (notas e faltas) nas etapas
     */
    public function test_reproved_per_absence_after_all_score_and_absence_posted()
    {
        $schoolClass = $this->enrollment->schoolClass;
        $school = $schoolClass->school;

        $this->createStages($school, 4);
        $this->createDisciplines($schoolClass, 2);

        $disciplines = $schoolClass->disciplines;

        $score = [
            1 => 8,
            2 => 8,
            3 => 8,
            4 => 8,
        ];

        $absence = [
            1 => 48,
            2 => 45,
            3 => 17,
            4 => 24,
        ];

        foreach ($disciplines as $discipline) {
            $this->postAbsenceForStages($absence, $discipline);
            $response = $this->postScoreForStages($score, $discipline);

            self::assertEquals('Reprovado por faltas', $response->situacao);
        }

        $registration = $this->enrollment->registration;
        self::assertEquals(App_Model_MatriculaSituacao::REPROVADO_POR_FALTAS, $registration->refresh()->aprovado);
    }

    /**
     * O Aluno deverá continuar como Cursando depois dos lançamentos (notas e faltas) nas etapas, exceto para etapa de recuperação
     */
    public function test_studying_after_all_score_and_absence_posted()
    {
        $schoolClass = $this->enrollment->schoolClass;
        $school = $schoolClass->school;

        $this->createStages($school, 4);
        $this->createDisciplines($schoolClass, 2);

        $disciplines = $schoolClass->disciplines;

        $score = [
            1 => 5,
            2 => 5,
            3 => 5,
            4 => 5,
        ];

        $absence = [
            1 => 3,
            2 => 3,
            3 => 3,
            4 => 3,
        ];

        foreach ($disciplines as $discipline) {
            $this->postAbsenceForStages($absence, $discipline);
            $response = $this->postScoreForStages($score, $discipline);

            self::assertEquals('Em exame', $response->situacao);
        }

        $registration = $this->enrollment->registration;
        self::assertEquals(App_Model_MatriculaSituacao::EM_ANDAMENTO, $registration->refresh()->aprovado);
    }

    /**
     * Os lançamentos (notas e faltas) nas etapas deverá deixar o aluno com a situação Cursando e os componentes como Em Exame,
     * depois o aluno deverá ser Aprovado após o lançamento da nota de Recuperação
     */
    public function test_approved_after_exam_all_score_and_absence_posted()
    {
        $schoolClass = $this->enrollment->schoolClass;
        $school = $schoolClass->school;

        $this->createStages($school, 4);
        $this->createDisciplines($schoolClass, 2);

        $disciplines = $schoolClass->disciplines;

        $score = [
            1 => 6,
            2 => 5,
            3 => 6,
            4 => 5,
        ];

        $absence = [
            1 => 4,
            2 => 7,
            3 => 5,
            4 => 2,
        ];

        foreach ($disciplines as $discipline) {
            $this->postAbsenceForStages($absence, $discipline);
            $response = $this->postScoreForStages($score, $discipline);

            self::assertEquals('Em exame', $response->situacao);
        }

        // Nota da etapa de recuperação
        $score = [
            'Rc' => 8,
        ];

        // Lança nota para a etapa de Recuperação
        foreach ($disciplines as $discipline) {
            $this->postAbsenceForStages($absence, $discipline);
            $response = $this->postScoreForStages($score, $discipline);

            self::assertEquals('Aprovado após exame', $response->situacao);
        }

        $registration = $this->enrollment->registration;
        self::assertEquals(App_Model_MatriculaSituacao::APROVADO, $registration->refresh()->aprovado);
    }

    /**
     * Os lançamentos (notas e faltas) nas etapas deverá deixar o aluno com a situação Cursando e os componentes como Em Exame,
     * depois o aluno deverá ser Reprovado após o lançamento da nota de Recuperação
     */
    public function test_reproved_after_exam_all_score_and_absence_posted()
    {
        $schoolClass = $this->enrollment->schoolClass;
        $school = $schoolClass->school;

        $this->createStages($school, 4);
        $this->createDisciplines($schoolClass, 2);

        $disciplines = $schoolClass->disciplines;

        $score = [
            1 => 6,
            2 => 5,
            3 => 6,
            4 => 5,
        ];

        $absence = [
            1 => 4,
            2 => 7,
            3 => 5,
            4 => 2,
        ];

        foreach ($disciplines as $discipline) {
            $this->postAbsenceForStages($absence, $discipline);
            $response = $this->postScoreForStages($score, $discipline);

            self::assertEquals('Em exame', $response->situacao);
        }

        // Nota da etapa de recuperação
        $score = [
            'Rc' => 5,
        ];

        // Lança nota para a etapa de Recuperação
        foreach ($disciplines as $discipline) {
            $this->postAbsenceForStages($absence, $discipline);
            $response = $this->postScoreForStages($score, $discipline);

            self::assertEquals('Retido', $response->situacao);
        }

        $registration = $this->enrollment->registration;
        self::assertEquals(App_Model_MatriculaSituacao::REPROVADO, $registration->refresh()->aprovado);
    }

    /**
     * O aluno deverá continuar com a situação Cursando e os componentes como Em Exame, depois dos lançamentos (notas e faltas) nas etapas,
     * exceto para etapa de recuperação. As faltas devem ser altas para que o aluno reprove por falta
     */
    public function test_in_exam_after_all_score_and_absence_posted_with_absence_high()
    {
        $schoolClass = $this->enrollment->schoolClass;
        $school = $schoolClass->school;

        $this->createStages($school, 4);
        $this->createDisciplines($schoolClass, 8);

        $disciplines = $schoolClass->disciplines;

        $score = [
            1 => 6,
            2 => 5,
            3 => 6,
            4 => 5,
        ];

        $absence = [
            1 => 24,
            2 => 17,
            3 => 51,
            4 => 22,
        ];

        foreach ($disciplines as $discipline) {
            $this->postAbsenceForStages($absence, $discipline);
            $response = $this->postScoreForStages($score, $discipline);

            self::assertEquals('Em exame', $response->situacao);
        }

        $registration = $this->enrollment->registration;
        self::assertEquals(App_Model_MatriculaSituacao::EM_ANDAMENTO, $registration->refresh()->aprovado);
    }

    /**
     * O aluno deverá ganhar a situação Reprovado Por Falta, depois dos lançamentos (notas e faltas) nas etapas
     */
    public function test_reproved_per_absence_after_exam_all_score_and_absence_posted()
    {
        $schoolClass = $this->enrollment->schoolClass;
        $school = $schoolClass->school;

        $this->createStages($school, 4);
        $this->createDisciplines($schoolClass, 2);

        $disciplines = $schoolClass->disciplines;

        $score = [
            1 => 6,
            2 => 5,
            3 => 6,
            4 => 5,
        ];

        $absence = [
            1 => 32,
            2 => 27,
            3 => 51,
            4 => 32,
        ];

        foreach ($disciplines as $discipline) {
            $this->postAbsenceForStages($absence, $discipline);
            $response = $this->postScoreForStages($score, $discipline);

            self::assertEquals('Em exame', $response->situacao);
        }

        // Nota da etapa de recuperação
        $score = [
            'Rc' => 8,
        ];

        // Lança nota para a etapa de Recuperação
        foreach ($disciplines as $discipline) {
            $this->postAbsenceForStages($absence, $discipline);
            $response = $this->postScoreForStages($score, $discipline);

            self::assertEquals('Reprovado por faltas', $response->situacao);
        }

        $registration = $this->enrollment->registration;
        self::assertEquals(App_Model_MatriculaSituacao::REPROVADO_POR_FALTAS, $registration->refresh()->aprovado);
    }

    /**
     * O aluno deverá continuar com a situação Cursando, depois dos lançamentos (notas e faltas) em algumas etapas
     */
    public function test_studying_after_not_all_stage_score_and_absence_posted()
    {
        $schoolClass = $this->enrollment->schoolClass;
        $school = $schoolClass->school;

        $this->createStages($school, 4);
        $this->createDisciplines($schoolClass, 2);

        $disciplines = $schoolClass->disciplines;

        // Lança notas e faltas das etapas 1 e 2
        $score = [
            1 => 6,
            2 => 5,
        ];

        $absence = [
            1 => 2,
            2 => 2,
        ];

        foreach ($disciplines as $discipline) {
            $this->postAbsenceForStages($absence, $discipline);
            $response = $this->postScoreForStages($score, $discipline);

            self::assertEquals('Cursando', $response->situacao);
        }

        $registration = $this->enrollment->registration;
        self::assertEquals(App_Model_MatriculaSituacao::EM_ANDAMENTO, $registration->refresh()->aprovado);
    }

    /**
     * O aluno deverá continuar com a situação Cursando, depois dos lançamentos (notas e faltas) nas etapas e ter a falta removida da última etapa
     */
    public function test_studying_after_remove_stage_score_and_absence_posted()
    {
        $schoolClass = $this->enrollment->schoolClass;
        $school = $schoolClass->school;

        $this->createStages($school, 4);
        $this->createDisciplines($schoolClass, 2);

        $disciplines = $schoolClass->disciplines;

        $score = [
            1 => 7,
            2 => 7,
            3 => 7,
            4 => 7,
        ];

        $absence = [
            1 => 2,
            2 => 2,
            3 => 2,
            4 => 2,
        ];

        foreach ($disciplines as $discipline) {
            $this->postAbsenceForStages($absence, $discipline);
            $response = $this->postScoreForStages($score, $discipline);

            self::assertEquals('Aprovado', $response->situacao);
        }

        $registration = $this->enrollment->registration;
        self::assertEquals(App_Model_MatriculaSituacao::APROVADO, $registration->refresh()->aprovado);

        // Remove falta da última etapa, de um dos componentes
        $randomDiscipline = $schoolClass->disciplines->random()->id;
        $response = $this->deleteAbsence($this->enrollment, $randomDiscipline, 4);
        self::assertEquals('Cursando', $response->situacao);

        $registration = $this->enrollment->registration;
        self::assertEquals(App_Model_MatriculaSituacao::EM_ANDAMENTO, $registration->refresh()->aprovado);
    }

    /**
     * Deverá retornar erro ao tentar remover falta de uma etapa, quando não é a útima etapa
     */
    public function test_error_after_remove_not_last_stage_score_and_absence_posted()
    {
        $schoolClass = $this->enrollment->schoolClass;
        $school = $schoolClass->school;

        $this->createStages($school, 4);
        $this->createDisciplines($schoolClass, 2);

        $disciplines = $schoolClass->disciplines;

        $score = [
            1 => 7,
            2 => 7,
            3 => 7,
            4 => 7,
        ];

        $absence = [
            1 => 2,
            2 => 2,
            3 => 2,
            4 => 2,
        ];

        foreach ($disciplines as $discipline) {
            $this->postAbsenceForStages($absence, $discipline);
            $response = $this->postScoreForStages($score, $discipline);

            self::assertEquals('Aprovado', $response->situacao);
        }

        // Remove falta de uma etapa
        $randomDiscipline = $schoolClass->disciplines->random()->id;
        $response = $this->deleteAbsence($this->enrollment, $randomDiscipline, 3);
        self::assertTrue($response->any_error_msg);

        $registration = $this->enrollment->registration;
        self::assertEquals(App_Model_MatriculaSituacao::APROVADO, $registration->refresh()->aprovado);
    }
}
