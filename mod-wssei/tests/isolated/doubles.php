<?php
// Test doubles only. These classes do not implement SEI authorization or indexing.
class InfraException extends Exception {}
class InfraRN {}
class InfraDTO
{
    public static $PREFIXO_STR = 'Str';
    public static $PREFIXO_NUM = 'Num';
    public static $PREFIXO_DTA = 'Dta';
    public static $PREFIXO_DBL = 'Dbl';
    private array $values = [];
    public function __construct() { if (method_exists($this, 'montar')) { $this->montar(); } }
    public function adicionarAtributo($prefix, $name) {}
    public function __call($name, $args)
    {
        if (str_starts_with($name, 'isSet')) { return array_key_exists(substr($name, 5), $this->values); }
        if (str_starts_with($name, 'set')) { $this->values[substr($name, 3)] = $args[0]; return; }
        if (str_starts_with($name, 'get')) { return $this->values[substr($name, 3)] ?? null; }
        if (str_starts_with($name, 'ret')) { return; }
        throw new LogicException($name);
    }
}
function check(bool $ok, string $message): void
{
    if (!$ok) { throw new RuntimeException($message); }
}
set_error_handler(static function ($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) { return false; }
    throw new ErrorException($message, 0, $severity, $file, $line);
});
require __DIR__ . '/../../src/dto/MdWsSeiPesquisaProtocoloSolrDTO.php';
