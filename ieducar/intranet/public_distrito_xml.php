<?php

header(header: 'Content-type: text/xml');

Portabilis_Utils_DeprecatedXmlApi::returnEmptyQueryUnlessUserIsLoggedIn();

echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<query xmlns=\"sugestoes\">\n";
if (is_numeric(value: $_GET['idmun'])) {
    $db = new clsBanco;
    $db->Consulta(
        consulta: "
            SELECT iddis, nome
            FROM public.distrito
            WHERE idmun = '{$_GET['idmun']}'
            ORDER BY nome ASC
        "
    );

    while ($db->ProximoRegistro()) {
        [$cod, $nome] = $db->Tupla();
        echo " <distrito iddis=\"{$cod}\">{$nome}</distrito>\n";
    }
}
echo '</query>';
