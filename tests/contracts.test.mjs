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

test('one-shot menu snapshot uses only defined sections and supported item fields', async () => {
  const snapshot = JSON.parse(await source('tools/menu-2026.json'));
  const menu = await source('includes/menu.php');
  const importer = await source('tools/import-menu.php');
  const sectionsBlock = menu.split('function ohnisko_menu_sections(): array')[1].split('function ohnisko_menu_section_choices(): array')[0];
  const sections = [...sectionsBlock.matchAll(/^        '([a-z_]+)' => \[$/gm)].map(match => match[1]);
  const ids = snapshot.items.map(item => item.import_id);
  const allowedFields = new Set([
    'import_id', 'title', 'section', 'menu_section', 'sort_order', 'description', 'note', 'details',
    'portion', 'origin', 'allergens', 'badge', 'spiciness', 'price_type',
    'price_amount', 'price_unit', 'price_custom', 'meta_order', 'active',
  ]);
  const counts = Object.fromEntries(sections.map(section => [section, snapshot.items.filter(item => item.section === section).length]));

  assert.equal(snapshot.items.length, 51);
  assert.match(importer, /array_merge\(\['import_id', 'title', 'section'\]/);
  assert.match(importer, /'menu_section'=>\$item\['section'\]/);
  assert.equal(new Set(ids).size, ids.length);
  assert.deepEqual(counts, {
    wine_beer_snacks: 5, small_dishes: 11, josper_beef: 4, josper_rest: 5,
    josper_sides: 5, josper_sauces: 8, bbq: 4, sandwich: 3, desserts_cheese: 6,
  });
  for (const item of snapshot.items) {
    assert.deepEqual(Object.keys(item).filter(key => !allowedFields.has(key)), [], `${item.import_id}: unsupported field`);
    assert.ok(sections.includes(item.section), `${item.import_id}: unknown section`);
    assert.ok(['fixed', 'per_unit', 'custom', 'hidden'].includes(item.price_type));
    assert.ok((item.allergens ?? []).every(value => Number.isInteger(value) && value >= 1 && value <= 14));
    assert.ok((item.spiciness ?? 0) >= 0);
    if (['fixed', 'per_unit'].includes(item.price_type)) assert.ok(Number.isFinite(item.price_amount) && item.price_amount >= 0);
    if (item.price_type === 'per_unit') assert.ok(item.price_unit);
    if (item.price_type === 'custom') assert.ok(item.price_custom);
    if (item.price_type === 'hidden') assert.ok(!('price_amount' in item) && !('price_unit' in item) && !('price_custom' in item));
    assert.ok(!/intro|combo|dog|allergen|footer|chef|social|na objednavku/i.test(item.title));
  }
});
