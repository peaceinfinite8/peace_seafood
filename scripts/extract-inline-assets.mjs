import fs from 'node:fs/promises';
import path from 'node:path';

const projectRoot = path.resolve(process.cwd());
const viewsRoot = path.join(projectRoot, 'src', 'views');
const publicRoot = path.join(projectRoot, 'public', 'inline-assets');
const baseUrl = '/peace_seafood/inline-assets';

const scriptHeredocRegex = /<\?php\s+\$scripts\s*=\s*<<<['"]?([A-Z_][A-Z0-9_]*)['"]?\r?\n([\s\S]*?)^\s*\1;\s*\r?\n\s*\?>/gm;
const styleBlockRegex = /<style\b[^>]*>([\s\S]*?)<\/style>/gi;
const inlineScriptRegex = /<script(?![^>]*\bsrc=)[^>]*>([\s\S]*?)<\/script>/gi;

function sanitizeName(value) {
    return value.replace(/[^a-zA-Z0-9._-]+/g, '_').replace(/^_+|_+$/g, '');
}

function stripScriptWrapper(code) {
    const trimmed = code.trim();
    const match = trimmed.match(/^<script[^>]*>([\s\S]*?)<\/script>$/i);
    return match ? match[1].trim() : trimmed;
}

function toPublicPath(viewFile, kind, index, ext) {
    const rel = path.relative(viewsRoot, viewFile).replace(/\\/g, '/');
    const stem = sanitizeName(rel.replace(/\.php$/i, ''));
    return `inline-assets/${stem}.${kind}.${index}.${ext}`;
}

function toPublicUrl(assetRelPath) {
    return `${baseUrl}/${assetRelPath.replace(/^inline-assets\//, '')}`;
}

async function ensureDir(dirPath) {
    await fs.mkdir(dirPath, { recursive: true });
}

async function writeAsset(assetRelPath, content) {
    const abs = path.join(projectRoot, 'public', assetRelPath);
    await ensureDir(path.dirname(abs));
    await fs.writeFile(abs, content.replace(/\s+$/u, '') + '\n', 'utf8');
}

async function processFile(filePath) {
    let content = await fs.readFile(filePath, 'utf8');
    const extracted = [];
    const rel = path.relative(projectRoot, filePath).replace(/\\/g, '/');

    let styleIndex = 0;
    content = content.replace(styleBlockRegex, (match, cssCode) => {
        const cleaned = cssCode.trim();
        if (!cleaned) return '';
        styleIndex += 1;
        const assetRel = toPublicPath(filePath, 'style', styleIndex, 'css');
        extracted.push(writeAsset(assetRel, `/* extracted from ${rel} */\n${cleaned}`));
        return `<link rel="stylesheet" href="${toPublicUrl(assetRel)}">`;
    });

    let heredocIndex = 0;
    content = content.replace(scriptHeredocRegex, (match, label, scriptBody) => {
        const cleaned = stripScriptWrapper(scriptBody);
        if (!cleaned) return '';
        heredocIndex += 1;
        const assetRel = toPublicPath(filePath, 'scripts', heredocIndex, 'js');
        extracted.push(writeAsset(assetRel, `// extracted from ${rel}\n${cleaned}`));
        return `<?php $scripts = '<script src="${toPublicUrl(assetRel)}"></script>'; ?>`;
    });

    let scriptIndex = 0;
    content = content.replace(inlineScriptRegex, (match, jsCode, offset) => {
        const trimmed = jsCode.trim();
        if (!trimmed) return '';
        scriptIndex += 1;
        const assetRel = toPublicPath(filePath, 'script', scriptIndex, 'js');
        extracted.push(writeAsset(assetRel, `// extracted from ${rel}\n${trimmed}`));
        return `<script src="${toPublicUrl(assetRel)}"></script>`;
    });

    if (extracted.length > 0) {
        await fs.writeFile(filePath, content, 'utf8');
    }

    await Promise.all(extracted);
    return extracted.length;
}

async function main() {
    await ensureDir(publicRoot);
    const files = [];

    async function walk(dirPath) {
        const entries = await fs.readdir(dirPath, { withFileTypes: true });
        for (const entry of entries) {
            const fullPath = path.join(dirPath, entry.name);
            if (entry.isDirectory()) {
                await walk(fullPath);
            } else if (entry.isFile() && entry.name.endsWith('.php')) {
                files.push(fullPath);
            }
        }
    }

    await walk(viewsRoot);

    let touched = 0;
    for (const filePath of files) {
        touched += await processFile(filePath);
    }

    // eslint-disable-next-line no-console
    console.log(`Extracted ${touched} inline assets from ${files.length} view files.`);
}

main().catch((error) => {
    console.error(error);
    process.exit(1);
});