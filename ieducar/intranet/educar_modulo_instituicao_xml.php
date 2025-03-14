<?php

header(header: 'Content-type: text/xml');

Portabilis_Utils_DeprecatedXmlApi::returnEmptyQueryUnlessUserIsLoggedIn();

echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<query xmlns=\"sugestoes\">\n";
if (is_numeric(value: $_GET['inst'])) {
    $db = new clsBanco;
    $consulta = "SELECT cod_modulo
                            ,nm_tipo
                       FROM pmieducar.modulo m
                      WHERE m.ref_cod_instituicao = {$_GET['inst']}
                        AND m.ativo = 1
                      ORDER BY 2
                        ";

    $db->Consulta(consulta: $consulta);

    while ($db->ProximoRegistro()) {
        [$cod_modulo, $nm_tipo] = $db->Tupla();
        echo "  <item>{$cod_modulo}</item>\n";
        echo "  <item>{$nm_tipo}</item>\n";
    }
}
echo '</query>';
