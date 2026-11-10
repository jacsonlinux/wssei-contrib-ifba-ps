<?php
require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/doubles.php';

// Middleware doubles: this suite exercises routing/validation, not authentication.
class TokenValidationMiddleware implements Psr\Http\Server\MiddlewareInterface
{
    public function __construct($factory) {}
    public function process($request, $handler): Psr\Http\Message\ResponseInterface
    {
        return $handler->handle($request);
    }
}
class ModuleVerificationMiddleware extends TokenValidationMiddleware {}
class EncodingMiddleware extends TokenValidationMiddleware {}
class MdWsSeiRest
{
    public static function formataRetornoErroREST($e)
    {
        return ['sucesso' => false, 'mensagem' => $e->getMessage(), 'exception' => 'synthetic'];
    }
}
class MdWsSeiProcedimentoRN
{
    public static int $calls = 0;
    public static ?MdWsSeiPesquisaProtocoloSolrDTO $last = null;
    public function pesquisarProcessosSolar($dto)
    {
        self::$calls++;
        self::$last = $dto;
        return ['sucesso' => true, 'data' => [], 'total' => 0];
    }
}
require __DIR__ . '/../../src/versao/v2/MdWsSeiServicosV2.php';
$app = Slim\Factory\AppFactory::create();
MdWsSeiServicosV2::getInstance($app)->registrarServicos();
function request(array $query): array
{
    global $app;
    $request = (new Slim\Psr7\Factory\ServerRequestFactory())->createServerRequest(
        'GET', 'http://localhost/api/v2/processo/pesquisar'
    )->withQueryParams($query);
    $response = $app->handle($request);
    return [$response->getStatusCode(), json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR)];
}
$legacy = [
    'grupo' => '7', 'palavrasChave' => 'capacitacao', 'descricao' => 'teste',
    'limit' => '2', 'start' => '4', 'staTipoData' => '0',
    'dataInicio' => '01/01/2026', 'dataFim' => '31/01/2026',
    'idUnidadeGeradora' => '9', 'idAssunto' => '8'
];
[$status, $body] = request($legacy);
check($status === 200 && $body === ['sucesso' => true, 'data' => [], 'total' => 0], 'Legacy response');
$before = clone MdWsSeiProcedimentoRN::$last;
check(!$before->isSetNumIdSerie() && !$before->isSetNumIdTipoProcedimento() && !$before->isSetNumIdContatoAssinante(), 'Absent filters');
check($before->getNumPaginaAtual() === '4' && $before->getNumMaxRegistrosRetorno() === '2', 'Offset/limit');
foreach ([['idSerie' => '321'], ['idTipoProcedimento' => '654'], ['idSerie' => '321', 'idTipoProcedimento' => '654']] as $filters) {
    foreach ([$filters, $filters + $legacy] as $query) {
        [$status] = request($query);
        check($status === 200, 'Valid filters');
        $dto = MdWsSeiProcedimentoRN::$last;
        check($dto->getNumIdSerie() === (isset($filters['idSerie']) ? 321 : null), 'Serie mapping');
        check($dto->getNumIdTipoProcedimento() === (isset($filters['idTipoProcedimento']) ? 654 : null), 'Type mapping');
        if (isset($query['palavrasChave'])) {
            $expected = clone $before;
            if (isset($filters['idSerie'])) { $expected->setNumIdSerie(321); }
            if (isset($filters['idTipoProcedimento'])) { $expected->setNumIdTipoProcedimento(654); }
            check($dto == $expected, 'All legacy criteria preserved');
        }
    }
}
foreach ([['idContatoAssinante' => '7'], ['idContatoAssinante' => '7', 'idSerie' => '321'],
    ['idContatoAssinante' => '7', 'idTipoProcedimento' => '654'],
    ['idContatoAssinante' => '7', 'idSerie' => '321', 'idTipoProcedimento' => '654']] as $filters) {
    foreach ([$filters, $filters + $legacy] as $query) {
        [$status] = request($query);
        check($status === 200, 'Signer combinations');
        $dto = MdWsSeiProcedimentoRN::$last;
        check($dto->getNumIdContatoAssinante() === 7, 'Contact signer mapping');
        if (isset($query['palavrasChave'])) {
            $expected = clone $before;
            $expected->setNumIdContatoAssinante(7);
            if (isset($filters['idSerie'])) { $expected->setNumIdSerie(321); }
            if (isset($filters['idTipoProcedimento'])) { $expected->setNumIdTipoProcedimento(654); }
            check($dto == $expected, 'Signer preserves legacy criteria');
        }
    }
}
$invalid = ['', '0', '-1', '1.0', '1e2', '+1', ' 1', '1 ', '01', 'abc', [], ['321'],
    '1 OR *:*', '2147483648', str_repeat('9', 300), null, false, true, 1.5, "1\n"];
foreach (['idSerie', 'idTipoProcedimento', 'idContatoAssinante'] as $name) {
    foreach ($invalid as $value) {
        $calls = MdWsSeiProcedimentoRN::$calls;
        [$status, $body] = request([$name => $value, 'palavrasChave' => 'teste']);
        check($status === 400 && $body['sucesso'] === false, 'Invalid input: ' . $name);
        check(str_contains($body['mensagem'], $name), 'Parameter-specific error');
        check(MdWsSeiProcedimentoRN::$calls === $calls, 'Invalid filter must not reach RN');
    }
    foreach (['1', '2147483647', 321] as $value) {
        [$status] = request([$name => $value]);
        check($status === 200, 'Valid boundary');
    }
}
$calls = MdWsSeiProcedimentoRN::$calls;
[$status] = request(['idSerie' => '321', 'idTipoProcedimento' => '0']);
check($status === 400 && MdWsSeiProcedimentoRN::$calls === $calls, 'No partial search');
echo "OK: handler, legacy criteria, combinations, boundaries and invalid input (RN/middleware doubles)\n";
