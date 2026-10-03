import assert from 'node:assert/strict';
import { readFile } from 'node:fs/promises';
import test from 'node:test';

const source = async file => readFile(new URL(`../${file}`, import.meta.url), 'utf8');

test('sections have one ordered definition and drive ACF choices', async () => {
  const menu = await source('includes/menu.php');
  const acf = await source('includes/acf-fields.php');
  const types = await source('includes/content-types.php');
  const sectionConfig = menu.split('function ohnisko_menu_sections(): array')[1].split('function ohnisko_menu_section_choices(): array')[0];
  const keys = [...sectionConfig.matchAll(/^        '([a-z_]+)' => \[$/gm)].map((match) => match[1]);

  assert.deepEqual(keys, [
    'wine_beer_snacks', 'small_dishes', 'josper_beef', 'josper_rest',
    'josper_sides', 'josper_sauces', 'bbq', 'sandwich', 'desserts_cheese',
  ]);
  assert.match(acf, /'choices' => \$section_choices/);
  assert.match(menu, /foreach \(ohnisko_menu_sections\(\) as \$key => \$section\)/);
  assert.doesNotMatch(types, /register_taxonomy|ohnisko_menu_section/);
});

test('empty dynamic sections are suppressed and order-only copy stays in one unbroken wrapper', async () => {
  const renderer = await source('includes/menu-render.php');
  const endpoint = await source('includes/print-endpoint.php');
  const css = await source('assets/print.css');

  assert.match(renderer, /if \(\$items === \[\]\) \{\s*return '';\s*\}/);
  assert.match(endpoint, /<section class="menu-static menu-order-only">[\s\S]*?<\/section>/);
  assert.match(css, /\.menu-order-only\{[^}]*break-inside:avoid;[^}]*page-break-inside:avoid/);
});

test('runtime path and print token have shared private runtime sources', async () => {
  const runtime = await source('includes/runtime.php');
  const plugin = await source('ohnisko-menu.php');
  const admin = await source('includes/pdf-e2e-admin.php');
  const endpoint = await source('includes/print-endpoint.php');
  const cron = await source('cron-worker.php');
  const worker = await readFile(new URL('../../ohnisko-menu-renderer/worker.mjs', import.meta.url), 'utf8');
  const generator = await readFile(new URL('../../ohnisko-menu-renderer/generate-pdf.mjs', import.meta.url), 'utf8');

  assert.match(runtime, /dirname\(OHNISKO_MENU_DIR, 5\).*ohnisko-pdf-runtime/);
  assert.doesNotMatch(runtime + admin + cron, /getenv\(['"]HOME/);
  assert.match(plugin, /includes\/runtime\.php/);
  assert.match(cron, /ohnisko_menu_runtime_dir\(\)/);
  assert.match(runtime, /\$config\['print_token'\]/);
  assert.match(endpoint, /ohnisko_menu_print_token\(\)/);
  assert.doesNotMatch(endpoint, /get_option\(['"]ohnisko_menu_print_token/);
  assert.match(worker, /config\.print_token/);
  assert.doesNotMatch(generator, /console\.log\(`Opening: \$\{inputUrl\}`\)/);
});
