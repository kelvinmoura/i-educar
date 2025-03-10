<?php

class Avaliacao_Service_FaltaGeralTest extends Avaliacao_Service_FaltaCommon
{
    protected function setUp(): void
    {
        $this->_setRegraOption('tipoPresenca', RegraAvaliacao_Model_TipoPresenca::GERAL);
        parent::setUp();
    }

    protected function _getFaltaTestInstanciaDeFaltaERegistradaApenasUmaVezNoBoletim()
    {
        return new Avaliacao_Model_FaltaGeral([
            'quantidade' => 10,
        ]);
    }

    protected function _getFaltaTestAdicionaFaltaNoBoletim()
    {
        return new Avaliacao_Model_FaltaComponente([
            'quantidade' => 10,
        ]);
    }

    protected function _testAdicionaFaltaNoBoletimVerificaValidadores(Avaliacao_Model_FaltaAbstract $falta)
    {
        $this->markTestSkipped();

        $this->assertEquals(1, $falta->etapa);
        $this->assertEquals(10, $falta->quantidade);

        $validators = $falta->getValidatorCollection();
        $this->assertInstanceOf('CoreExt_Validate_Choice', $validators['etapa']);
        $this->assertFalse(isset($validators['componenteCurricular']));

        // Opções dos validadores

        // Etapas possíveis para o lançamento de nota
        $this->assertEquals(
            array_merge(range(1, count($this->_getConfigOptions('anoLetivoModulo'))), ['Rc']),
            $validators['etapa']->getOption('choices')
        );
    }

    /**
     * Testa o service adicionando faltas de apenas um componente curricular,
     * para todas as etapas regulares (1 a 4).
     */
    public function test_salvar_faltas_no_boletim()
    {
        $this->markTestSkipped();

        $faltaAluno = $this->_getConfigOption('faltaAluno', 'instance');

        $faltas = [
            new Avaliacao_Model_FaltaGeral([
                'quantidade' => 7,
                'etapa' => 1,
            ]),
            new Avaliacao_Model_FaltaGeral([
                'quantidade' => 11,
                'etapa' => 2,
            ]),
            new Avaliacao_Model_FaltaGeral([
                'quantidade' => 8,
                'etapa' => 3,
            ]),
            new Avaliacao_Model_FaltaGeral([
                'quantidade' => 8,
                'etapa' => 4,
            ]),
        ];

        // Configura mock para Avaliacao_Model_FaltaGeralDataMapper
        $mock = $this->getCleanMock('Avaliacao_Model_FaltaGeralDataMapper');

        $mock->expects($this->at(0))
            ->method('findAll')
            ->with([], ['faltaAluno' => $faltaAluno->id], ['etapa' => 'ASC'])
            ->will($this->returnValue([]));

        $mock->expects($this->at(1))
            ->method('save')
            ->with($faltas[0])
            ->will($this->returnValue(true));

        $mock->expects($this->at(2))
            ->method('save')
            ->with($faltas[1])
            ->will($this->returnValue(true));

        $mock->expects($this->at(3))
            ->method('save')
            ->with($faltas[2])
            ->will($this->returnValue(true));

        $mock->expects($this->at(4))
            ->method('save')
            ->with($faltas[3])
            ->will($this->returnValue(true));

        $this->_setFaltaAbstractDataMapperMock($mock);

        $service = $this->_getServiceInstance();
        $service->addFaltas($faltas);
        $service->saveFaltas();
    }

    /**
     * Testa o service adicionando novas faltas para um componente curricular,
     * que inclusive já tem a falta lançada para a segunda etapa.
     */
    public function test_salvas_faltas_no_boletim_com_etapas_lancadas()
    {
        $this->markTestSkipped();

        $faltaAluno = $this->_getConfigOption('faltaAluno', 'instance');

        $faltas = [
            new Avaliacao_Model_FaltaGeral([
                'quantidade' => 7,
                'etapa' => 2,
            ]),
            new Avaliacao_Model_FaltaGeral([
                'quantidade' => 9,
                'etapa' => 3,
            ]),
        ];

        $faltasPersistidas = [
            new Avaliacao_Model_FaltaGeral([
                'id' => 1,
                'faltaAluno' => $faltaAluno->id,
                'quantidade' => 8,
                'etapa' => 1,
            ]),
            new Avaliacao_Model_FaltaGeral([
                'id' => 2,
                'faltaAluno' => $faltaAluno->id,
                'quantidade' => 11,
                'etapa' => 2,
            ]),
        ];

        // Configura mock para Avaliacao_Model_FaltaGeralDataMapper
        $mock = $this->getCleanMock('Avaliacao_Model_FaltaGeralDataMapper');

        $mock->expects($this->at(0))
            ->method('findAll')
            ->with([], ['faltaAluno' => $faltaAluno->id], ['etapa' => 'ASC'])
            ->will($this->returnValue($faltasPersistidas));

        $mock->expects($this->at(1))
            ->method('save')
            ->with($faltas[0])
            ->will($this->returnValue(true));

        $mock->expects($this->at(2))
            ->method('save')
            ->with($faltas[1])
            ->will($this->returnValue(true));

        $this->_setFaltaAbstractDataMapperMock($mock);

        $service = $this->_getServiceInstance();
        $service->addFaltas($faltas);
        $service->saveFaltas();
    }

    public function test_salvas_faltas_atualizando_etapa_da_ultima_instancia_adicionada_no_boletim_com_etapas_lancadas()
    {
        $this->markTestSkipped();

        $faltaAluno = $this->_getConfigOption('faltaAluno', 'instance');

        $faltas = [
            new Avaliacao_Model_FaltaGeral([
                'quantidade' => 7,
                'etapa' => 2,
            ]),
            // Etapa omitida, será atribuída a etapa '3'
            new Avaliacao_Model_FaltaGeral([
                'quantidade' => 9,
            ]),
        ];

        $faltasPersistidas = [
            new Avaliacao_Model_FaltaGeral([
                'id' => 1,
                'faltaAluno' => $faltaAluno->id,
                'quantidade' => 8,
                'etapa' => 1,
            ]),
            new Avaliacao_Model_FaltaGeral([
                'id' => 2,
                'faltaAluno' => $faltaAluno->id,
                'quantidade' => 11,
                'etapa' => 2,
            ]),
        ];

        // Configura mock para Avaliacao_Model_FaltaGeralDataMapper
        $mock = $this->getCleanMock('Avaliacao_Model_FaltaGeralDataMapper');

        $mock->expects($this->at(0))
            ->method('findAll')
            ->with([], ['faltaAluno' => $faltaAluno->id], ['etapa' => 'ASC'])
            ->will($this->returnValue($faltasPersistidas));

        $mock->expects($this->at(1))
            ->method('save')
            ->with($faltas[0])
            ->will($this->returnValue(true));

        $mock->expects($this->at(2))
            ->method('save')
            ->with($faltas[1])
            ->will($this->returnValue(true));

        $this->_setFaltaAbstractDataMapperMock($mock);

        $service = $this->_getServiceInstance();
        $service->addFaltas($faltas);
        $service->saveFaltas();

        $faltas = $service->getFaltas();

        $falta = array_shift($faltas);
        $this->assertEquals(2, $falta->etapa);

        // Etapa atribuída automaticamente
        $falta = array_shift($faltas);
        $this->assertEquals(3, $falta->etapa);
    }
}
