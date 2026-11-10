import { spawnSync } from 'node:child_process';
import { fileURLToPath } from 'node:url';

const base = process.env.WSSEI_LAB_BASEURL || 'http://127.0.0.1:8000/sei/modulos/wssei/controlador_ws.php/api/v2';
const url = new URL(base);
if (process.env.WSSEI_LAB_DISPOSABLE !== '1' || url.hostname !== '127.0.0.1' ||
    url.protocol !== 'http:' || url.username || url.password || url.search || url.hash ||
    url.pathname !== '/sei/modulos/wssei/controlador_ws.php/api/v2') {
  throw new Error('Exige WSSEI_LAB_DISPOSABLE=1 e HTTP em 127.0.0.1, no caminho REST local.');
}
if (!process.env.WSSEI_LAB_TOKEN) throw new Error('Defina WSSEI_LAB_TOKEN de usuário sintético.');
const collection = fileURLToPath(new URL('./Filtros-Pesquisa.postman_collection.json', import.meta.url));
const result = spawnSync('newman', ['run', collection, '--ignore-redirects', '--bail',
  '--env-var', `labBaseUrl=${base}`, '--env-var', `token=${process.env.WSSEI_LAB_TOKEN}`,
  '--env-var', 'laboratorioDescartavel=SIM'], { stdio: 'inherit' });
if (result.error) throw result.error;
process.exit(result.status ?? 1);
