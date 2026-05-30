import fs from 'node:fs/promises';
import path from 'node:path';
import crypto from 'node:crypto';
import * as esbuild from 'esbuild';

const projectRoot = path.resolve(process.cwd());

const themeCssSource = 'public/css/ui-theme.css';
const themeJsSource = 'public/js/ui-theme.js';

const jsInputs = [
  'public/js/api-client.js',
  'public/js/utils.js',
  'public/js/auth.js',
];

const cssInputs = [
  'public/css/variables.css',
  'public/css/dark-mode.css',
  'public/css/custom.css',
];

const outDir = 'public/build';
const outJs = 'public/build/app.min.js';
const outCss = 'public/build/app.min.css';
const outManifest = 'public/build/manifest.json';

function sha256(buffer) {
  return crypto.createHash('sha256').update(buffer).digest('hex');
}

async function readFiles(filePaths) {
  const outputs = [];
  for (const filePath of filePaths) {
    const abs = path.join(projectRoot, filePath);
    outputs.push(await fs.readFile(abs, 'utf8'));
  }
  return outputs;
}

async function ensureOutDir() {
  await fs.mkdir(path.join(projectRoot, outDir), { recursive: true });
}

async function buildOnce() {
  await ensureOutDir();

  const [themeCss, themeJs, jsParts, cssParts] = await Promise.all([
    fs.readFile(path.join(projectRoot, themeCssSource), 'utf8'),
    fs.readFile(path.join(projectRoot, themeJsSource), 'utf8'),
    readFiles(jsInputs),
    readFiles(cssInputs),
  ]);

  // Concatenate in the same order as existing <script> / <link> tags.
  // We intentionally avoid esbuild "bundle" mode to preserve global-scope
  // variables (some inline scripts rely on `apiClient` being globally visible).
  const jsCombined = jsParts.join('\n;\n');
  const jsResult = await esbuild.transform(jsCombined, {
    loader: 'js',
    // Avoid renaming/inlining because inline scripts reference globals by name.
    minifyIdentifiers: false,
    minifySyntax: false,
    minifyWhitespace: true,
    sourcemap: false,
    charset: 'utf8',
    target: 'es2018',
  });

  const cssCombined = cssParts.join('\n');
  const cssResult = await esbuild.transform(cssCombined, {
    loader: 'css',
    minify: true,
    sourcemap: false,
    charset: 'utf8',
  });

  await Promise.all([
    fs.writeFile(path.join(projectRoot, outJs), jsResult.code, 'utf8'),
    fs.writeFile(path.join(projectRoot, outCss), cssResult.code, 'utf8'),
  ]);

  const manifest = {
    generated_at: new Date().toISOString(),
    theme: {
      css: {
        file: '/css/ui-theme.css',
        source: themeCssSource,
        sha256: sha256(Buffer.from(themeCss, 'utf8')),
      },
      js: {
        file: '/js/ui-theme.js',
        source: themeJsSource,
        sha256: sha256(Buffer.from(themeJs, 'utf8')),
      },
    },
    js: {
      file: '/build/app.min.js',
      sha256: sha256(Buffer.from(jsResult.code, 'utf8')),
      inputs: jsInputs,
    },
    css: {
      file: '/build/app.min.css',
      sha256: sha256(Buffer.from(cssResult.code, 'utf8')),
      inputs: cssInputs,
    },
  };

  await fs.writeFile(
    path.join(projectRoot, outManifest),
    JSON.stringify(manifest, null, 2) + '\n',
    'utf8'
  );

  // eslint-disable-next-line no-console
  console.log(`Built: ${outJs}, ${outCss}`);
}

function parseArgs(argv) {
  return {
    watch: argv.includes('--watch'),
  };
}

async function watchMode() {
  let building = false;
  let pending = false;

  const rebuild = async () => {
    if (building) {
      pending = true;
      return;
    }
    building = true;
    try {
      await buildOnce();
    } finally {
      building = false;
      if (pending) {
        pending = false;
        await rebuild();
      }
    }
  };

  await rebuild();

  const watchPaths = [
    ...jsInputs.map((p) => path.join(projectRoot, p)),
    ...cssInputs.map((p) => path.join(projectRoot, p)),
    path.join(projectRoot, themeCssSource),
    path.join(projectRoot, themeJsSource),
  ];

  const watchers = [];
  for (const absPath of watchPaths) {
    const w = (await import('node:fs')).watch(absPath, { persistent: true }, () => {
      void rebuild();
    });
    watchers.push(w);
  }

  // eslint-disable-next-line no-console
  console.log('Watching asset files for changes...');

  // Keep process alive
  await new Promise(() => { });
}

const { watch } = parseArgs(process.argv.slice(2));
if (watch) {
  await watchMode();
} else {
  await buildOnce();
}
