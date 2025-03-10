<?php

use iEducar\Legacy\Model;

class clsPmieducarTurmaModulo extends Model
{
    public $ref_cod_turma;

    public $ref_cod_modulo;

    public $sequencial;

    public $data_inicio;

    public $data_fim;

    public $dias_letivos;

    public function __construct($ref_cod_turma = null, $ref_cod_modulo = null, $sequencial = null, $data_inicio = null, $data_fim = null, $dias_letivos = null)
    {
        $this->_schema = 'pmieducar.';
        $this->_tabela = "{$this->_schema}turma_modulo";

        $this->_campos_lista = $this->_todos_campos = 'ref_cod_turma, ref_cod_modulo, sequencial, data_inicio, data_fim, dias_letivos';

        if (is_numeric($ref_cod_modulo)) {
            $this->ref_cod_modulo = $ref_cod_modulo;
        }
        if (is_numeric($ref_cod_turma)) {
            $this->ref_cod_turma = $ref_cod_turma;
        }

        if (is_numeric($sequencial)) {
            $this->sequencial = $sequencial;
        }
        if (is_string($data_inicio)) {
            $this->data_inicio = $data_inicio;
        }
        if (is_string($data_fim)) {
            $this->data_fim = $data_fim;
        }
        if (is_numeric($dias_letivos)) {
            $this->dias_letivos = $dias_letivos;
        }
    }

    /**
     * Cria um novo registro
     *
     * @return bool
     */
    public function cadastra()
    {
        if (is_numeric($this->ref_cod_turma) && is_numeric($this->ref_cod_modulo) && is_numeric($this->sequencial) && is_string($this->data_inicio) && is_string($this->data_fim)) {
            $db = new clsBanco;

            $campos = '';
            $valores = '';
            $gruda = '';

            if (is_numeric($this->ref_cod_turma)) {
                $campos .= "{$gruda}ref_cod_turma";
                $valores .= "{$gruda}'{$this->ref_cod_turma}'";
                $gruda = ', ';
            }
            if (is_numeric($this->ref_cod_modulo)) {
                $campos .= "{$gruda}ref_cod_modulo";
                $valores .= "{$gruda}'{$this->ref_cod_modulo}'";
                $gruda = ', ';
            }
            if (is_numeric($this->sequencial)) {
                $campos .= "{$gruda}sequencial";
                $valores .= "{$gruda}'{$this->sequencial}'";
                $gruda = ', ';
            }
            if (is_string($this->data_inicio)) {
                $campos .= "{$gruda}data_inicio";
                $valores .= "{$gruda}'{$this->data_inicio}'";
                $gruda = ', ';
            }
            if (is_string($this->data_fim)) {
                $campos .= "{$gruda}data_fim";
                $valores .= "{$gruda}'{$this->data_fim}'";
                $gruda = ', ';
            }
            if (is_numeric($this->dias_letivos)) {
                $campos .= "{$gruda}dias_letivos";
                $valores .= "{$gruda}'{$this->dias_letivos}'";
                $gruda = ', ';
            }

            $db->Consulta("INSERT INTO {$this->_tabela} ( $campos ) VALUES( $valores )");

            return true;
        }

        return false;
    }

    /**
     * Edita os dados de um registro
     *
     * @return bool
     */
    public function edita()
    {
        if (is_numeric($this->ref_cod_turma) && is_numeric($this->ref_cod_modulo) && is_numeric($this->sequencial)) {
            $db = new clsBanco;
            $gruda = '';
            $set = '';

            if (is_string($this->data_inicio)) {
                $set .= "{$gruda}data_inicio = '{$this->data_inicio}'";
                $gruda = ', ';
            }
            if (is_string($this->data_fim)) {
                $set .= "{$gruda}data_fim = '{$this->data_fim}'";
                $gruda = ', ';
            }
            if (is_numeric($this->dias_letivos)) {
                $set .= "{$gruda}dias_letivos = '{$this->dias_letivos}'";
                $gruda = ', ';
            }

            if ($set) {
                $db->Consulta("UPDATE {$this->_tabela} SET $set WHERE ref_cod_turma = '{$this->ref_cod_turma}' AND ref_cod_modulo = '{$this->ref_cod_modulo}' AND sequencial = '{$this->sequencial}'");

                return true;
            }
        }

        return false;
    }

    /**
     * Retorna uma lista filtrados de acordo com os parametros
     *
     * @return array|false
     */
    public function lista($int_ref_cod_turma = null, $int_ref_cod_modulo = null, $int_sequencial = null, $date_data_inicio_ini = null, $date_data_inicio_fim = null, $date_data_fim_ini = null, $date_data_fim_fim = null, $dias_letivos = null)
    {
        $sql = "SELECT {$this->_campos_lista} FROM {$this->_tabela}";
        $filtros = '';

        $whereAnd = ' WHERE ';

        if (is_numeric($int_ref_cod_turma)) {
            $filtros .= "{$whereAnd} ref_cod_turma = '{$int_ref_cod_turma}'";
            $whereAnd = ' AND ';
        }
        if (is_numeric($int_ref_cod_modulo)) {
            $filtros .= "{$whereAnd} ref_cod_modulo = '{$int_ref_cod_modulo}'";
            $whereAnd = ' AND ';
        }
        if (is_numeric($int_sequencial)) {
            $filtros .= "{$whereAnd} sequencial = '{$int_sequencial}'";
            $whereAnd = ' AND ';
        }
        if (is_string($date_data_inicio_ini)) {
            $filtros .= "{$whereAnd} data_inicio >= '{$date_data_inicio_ini}'";
            $whereAnd = ' AND ';
        }
        if (is_string($date_data_inicio_fim)) {
            $filtros .= "{$whereAnd} data_inicio <= '{$date_data_inicio_fim}'";
            $whereAnd = ' AND ';
        }
        if (is_string($date_data_fim_ini)) {
            $filtros .= "{$whereAnd} data_fim >= '{$date_data_fim_ini}'";
            $whereAnd = ' AND ';
        }
        if (is_string($date_data_fim_fim)) {
            $filtros .= "{$whereAnd} data_fim <= '{$date_data_fim_fim}'";
            $whereAnd = ' AND ';
        }
        if (is_numeric($dias_letivos)) {
            $filtros .= "{$whereAnd} dias_letivos <= '{$dias_letivos}'";
            $whereAnd = ' AND ';
        }

        $db = new clsBanco;
        $countCampos = count(explode(',', $this->_campos_lista));
        $resultado = [];

        $sql .= $filtros . $this->getOrderby() . $this->getLimite();

        $this->_total = $db->CampoUnico("SELECT COUNT(0) FROM {$this->_tabela} {$filtros}");

        $db->Consulta($sql);

        if ($countCampos > 1) {
            while ($db->ProximoRegistro()) {
                $tupla = $db->Tupla();

                $tupla['_total'] = $this->_total;
                $resultado[] = $tupla;
            }
        } else {
            while ($db->ProximoRegistro()) {
                $tupla = $db->Tupla();
                $resultado[] = $tupla[$this->_campos_lista];
            }
        }
        if (count($resultado)) {
            return $resultado;
        }

        return false;
    }

    /**
     * Retorna um array com os dados de um registro
     *
     * @return array|false
     */
    public function detalhe()
    {
        if (is_numeric($this->ref_cod_turma) && is_numeric($this->ref_cod_modulo) && is_numeric($this->sequencial)) {
            $db = new clsBanco;
            $db->Consulta("SELECT {$this->_todos_campos} FROM {$this->_tabela} WHERE ref_cod_turma = '{$this->ref_cod_turma}' AND ref_cod_modulo = '{$this->ref_cod_modulo}' AND sequencial = '{$this->sequencial}'");
            $db->ProximoRegistro();

            return $db->Tupla();
        }

        return false;
    }

    /**
     * Retorna um array com os dados de um registro
     *
     * @return array|false
     */
    public function existe()
    {
        if (is_numeric($this->ref_cod_turma) && is_numeric($this->ref_cod_modulo) && is_numeric($this->sequencial)) {
            $db = new clsBanco;
            $db->Consulta("SELECT 1 FROM {$this->_tabela} WHERE ref_cod_turma = '{$this->ref_cod_turma}' AND ref_cod_modulo = '{$this->ref_cod_modulo}' AND sequencial = '{$this->sequencial}'");
            $db->ProximoRegistro();

            return $db->Tupla();
        }

        return false;
    }

    /**
     * Exclui um registro
     *
     * @return bool
     */
    public function excluir()
    {
        return false;
    }

    /**
     * Exclui todos os registros referentes a uma turma
     */
    public function excluirTodos($ref_cod_turma = null)
    {
        if (is_numeric($ref_cod_turma)) {
            $db = new clsBanco;
            $db->Consulta("DELETE FROM {$this->_tabela} WHERE ref_cod_turma = '{$ref_cod_turma}'");

            return true;
        }

        return false;
    }

    public function removeStepsOfClassesForCourseAndYear($courseCode, $year)
    {
        if (!is_numeric($courseCode) || !is_numeric($year)) {
            return false;
        }

        $sql = "
            DELETE FROM {$this->_tabela}
            WHERE ref_cod_turma IN (
                SELECT cod_turma FROM pmieducar.turma
                WHERE ref_cod_curso = {$courseCode}
                AND ano = {$year}
            ) RETURNING *;
        ";

        try {
            $db = new clsBanco;
            $db->Consulta($sql);
            while ($db->ProximoRegistro()) {
                $tupla[] = $db->Tupla();
            }
        } catch (Throwable) {
            return false;
        }

        return true;
    }

    public function copySchoolStepsIntoClassesForCourseAndYear($courseCode, $year)
    {
        if (!is_numeric($courseCode) || !is_numeric($year)) {
            return false;
        }

        $sql = "
            INSERT INTO pmieducar.turma_modulo
            SELECT cod_turma, ref_cod_modulo, sequencial, data_inicio, data_fim, dias_letivos
            FROM pmieducar.turma
            INNER JOIN pmieducar.ano_letivo_modulo ON (turma.ref_ref_cod_escola = ano_letivo_modulo.ref_ref_cod_escola
                AND turma.ano = ano_letivo_modulo.ref_ano)
            WHERE ref_cod_curso = {$courseCode} AND ano = {$year}
            RETURNING *;
        ";

        try {
            $db = new clsBanco;
            $db->Consulta($sql);
            while ($db->ProximoRegistro()) {
                $tupla[] = $db->Tupla();
            }
        } catch (Throwable) {
            return false;
        }

        return true;
    }
}
