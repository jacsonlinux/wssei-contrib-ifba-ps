<?php
require __DIR__ . '/doubles.php';
// The source requires SEI.php. Supply an empty synthetic bootstrap in this laboratory only.
define('DIR_SEI_WEB', __DIR__ . '/sei');
require __DIR__ . '/../../src/rn/MdWsSeiProcedimentoRN.php';
class SessaoSEI
{
    public static int $unit = 9;
    public static function getInstance() { return new self(); }
    public function getNumIdUnidadeAtual() { return self::$unit; }
}
class UnidadeDTO extends InfraDTO {}
class UnidadeRN
{
    public static string $protocol = 'N';
    public function consultarRN0125($dto) { $dto->setStrSinProtocolo(self::$protocol); return $dto; }
}
class AssuntoProxyDTO extends InfraDTO {}
class AssuntoProxyRN
{
    public function listar($dto) { $proxy = new AssuntoProxyDTO(); $proxy->setNumIdAssuntoProxy(8); return [$proxy]; }
}
class InfraSolrUtil
{
    public static function formatarOperadores($text, $field = null) { return $field ? "$field:$text" : $text; }
}
// ASCII fixtures only; not a replacement for mbstring in a real SEI runtime.
if (!function_exists('mb_convert_encoding')) {
    function mb_convert_encoding($text, $to, $from) { return iconv($from, $to, $text); }
}
$rn = new MdWsSeiProcedimentoRN();
$method = new ReflectionMethod($rn, 'montaConsultaSolr');
$base = new MdWsSeiPesquisaProtocoloSolrDTO();
$base->setStrPalavrasChave('capacitacao');
$base->setStrDescricao('teste');
$base->setNumIdUnidadeGeradora(9);
$base->setNumIdAssunto(8);
$base->setStrStaTipoData('0');
$base->setDtaInicio('01/01/2026');
$base->setDtaFim('31/01/2026');
$base->setNumPaginaAtual(4);
$base->setNumMaxRegistrosRetorno(2);
$original = $method->invoke($rn, $base);
check($original->q === '(capacitacao) AND (desc:teste) AND (id_uni_ger:9) AND (id_assun:*;8;*) AND dta_ger:[2026-01-01T00:00:00Z TO 2026-01-31T00:00:00Z] AND (tipo_aces_g:P OR id_uni_aces:*;9;*)', 'Baseline query');
foreach ([[321, null], [null, 654], [321, 654], [2147483647, 2147483647]] as [$serie, $type]) {
    foreach ([false, true] as $withLegacy) {
        $dto = $withLegacy ? clone $base : new MdWsSeiPesquisaProtocoloSolrDTO();
        if ($serie !== null) { $dto->setNumIdSerie($serie); }
        if ($type !== null) { $dto->setNumIdTipoProcedimento($type); }
        foreach ([0, 2, 4] as $offset) {
            $dto->setNumPaginaAtual($offset);
            $dto->setNumMaxRegistrosRetorno(2);
            $params = $method->invoke($rn, $dto);
            check($params->start === $offset && $params->rows === 2, 'Pagination');
            check($params->sort === 'dta_ger desc, id_prot desc', 'Stable ordering');
            check(str_contains($params->q, '(tipo_aces_g:P OR id_uni_aces:*;9;*)'), 'Access condition retained');
            if ($serie !== null) { check(str_contains($params->q, "(id_serie:$serie) AND "), 'Serie AND'); }
            if ($type !== null) { check(str_contains($params->q, "(id_tipo_proc:$type) AND "), 'Type AND'); }
            if ($withLegacy) {
                check(str_contains($params->q, '(capacitacao) AND (desc:teste) AND (id_uni_ger:9) AND (id_assun:*;8;*)'), 'Legacy intersection');
                check(str_contains($params->q, 'dta_ger:[2026-01-01T00:00:00Z TO 2026-01-31T00:00:00Z]'), 'Date intersection');
            }
        }
    }
}
SessaoSEI::$unit = 10;
$dto = new MdWsSeiPesquisaProtocoloSolrDTO();
$dto->setNumIdSerie(321);
check($method->invoke($rn, $dto)->q === '(id_serie:321) AND (tipo_aces_g:P OR id_uni_aces:*;10;*)', 'Current unit');
UnidadeRN::$protocol = 'S';
check($method->invoke($rn, $dto)->q === '(id_serie:321)', 'Existing protocol-unit rule');
echo "OK: actual Solr builder, AND, legacy criteria, unit condition and offsets (SEI doubles)\n";

$export = [];
UnidadeRN::$protocol = 'N';
SessaoSEI::$unit = 9;
foreach ([7, 17, 70, 2147483647] as $signer) {
    foreach ([false, true] as $combined) {
        $dto = new MdWsSeiPesquisaProtocoloSolrDTO();
        $dto->setNumIdContatoAssinante($signer);
        if ($combined) { $dto->setNumIdSerie(321); $dto->setNumIdTipoProcedimento(654); }
        $query = $method->invoke($rn, $dto);
        check(str_contains($query->q, '(id_assin:/(.*[^0-9])?' . $signer . '([^0-9].*)?/)'), 'Numeric signer token');
        check(str_contains($query->q, ' AND (tipo_aces_g:P OR id_uni_aces:*;9;*)'), 'Signer retains unit guard');
        $export[] = $signer . "\t" . ($combined ? 'combined' : 'single') . "\t" . $query->q;
    }
}
if (getenv('WSSEI_SIGNER_QUERY_OUTPUT')) {
    file_put_contents(getenv('WSSEI_SIGNER_QUERY_OUTPUT'), implode("\n", $export));
}
echo "OK: signer query, contact ID, combinations and access guard (SEI doubles)\n";
