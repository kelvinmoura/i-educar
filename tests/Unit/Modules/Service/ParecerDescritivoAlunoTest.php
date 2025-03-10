<?php

use PHPUnit\Framework\MockObject\MockObject;

class Avaliacao_Service_ParecerDescritivoAlunoTest extends Avaliacao_Service_TestCommon
{
    protected function setUp(): void
    {
        $this->_setRegraOption('parecerDescritivo', RegraAvaliacao_Model_TipoParecerDescritivo::ANUAL_COMPONENTE);
        parent::setUp();
    }

    public function test_cria_nova_instancia_de_parecer_descritivo_aluno()
    {
        $parecerAluno = $this->_getConfigOption('parecerDescritivoAluno', 'instance');

        $parecerSave = clone $parecerAluno;
        $parecerSave->id = null;

        // Configura mock para Avaliacao_Model_ParecerDescritivoAlunoDataMapper
        /** @var Avaliacao_Model_ParecerDescritivoAlunoDataMapper|MockObject $mock */
        $mock = $this->getCleanMock('Avaliacao_Model_ParecerDescritivoAlunoDataMapper');

        $mock
            ->method('save')
            ->with($parecerSave)
            ->willReturn(true);

        $mock
            ->expects(self::exactly(2))
            ->method('findAll')
            ->willReturnOnConsecutiveCalls([], [$parecerAluno]);
        $this->assertEquals(1, $this->_getConfigOption('matricula', 'cod_matricula'));

        $this->_setParecerDescritivoAlunoDataMapperMock($mock);

        $this->_getServiceInstance();
    }
}
