import fs from 'node:fs/promises';
import path from 'node:path';

const projectRoot = path.resolve(process.cwd());
const viewsRoot = path.join(projectRoot, 'src', 'views');
const publicInlineRoot = path.join(projectRoot, 'public', 'inline-assets');
const viewAssetRegex = /\/peace_seafood\/inline-assets\/([A-Za-z0-9._-]+)\.(style|script|scripts)\.(\d+)\.(css|js)/g;
const flatFileRegex = /^(.+)\.(style|script|scripts)\.(\d+)\.(css|js)$/;

function splitViewName(viewStem) {
    const separatorIndex = viewStem.indexOf('_');
    if (separatorIndex < 0) {
        return { moduleName: 'shared', pageName: viewStem };
    }

    return {
        moduleName: viewStem.slice(0, separatorIndex),
        pageName: viewStem.slice(separatorIndex + 1),
    };
}

function structuredRelativePath(viewStem, kind, extension) {
    const { moduleName, pageName } = splitViewName(viewStem);
    const folder = kind === 'style' ? 'css' : 'js';
    return `${folder}/${moduleName}/${pageName}.${extension}`;
}

async function readFlatAssets() {
    const entries = await fs.readdir(publicInlineRoot, { withFileTypes: true });
    const assets = [];
    for (const entry of entries) {
        if (!entry.isFile()) continue;
        const match = entry.name.match(flatFileRegex);
        if (!match) continue;
        const [, viewStem, kind, order, extension] = match;
        assets.push({
            fileName: entry.name,
            viewStem,
            kind,
            order: Number(order),
            extension,
            absPath: path.join(publicInlineRoot, entry.name),
        });
    }
    return assets;
}

async function groupAssets(assets) {
    const grouped = new Map();
    for (const asset of assets) {
        const key = `${asset.viewStem}:${asset.kind}`;
        if (!grouped.has(key)) grouped.set(key, []);
        grouped.get(key).push(asset);
    }
    return grouped;
}

async function writeStructuredAssets(grouped, assets) {
    const fileMap = new Map();

    for (const [groupKey, groupAssetsList] of grouped.entries()) {
        groupAssetsList.sort((left, right) => left.order - right.order);
        const [viewStem, kind] = groupKey.split(':');
        const extension = kind === 'style' ? 'css' : 'js';
        const relativePath = structuredRelativePath(viewStem, kind, extension);
        const absolutePath = path.join(publicInlineRoot, relativePath);

        await fs.mkdir(path.dirname(absolutePath), { recursive: true });

        const contents = [];
        for (const asset of groupAssetsList) {
            const assetContent = await fs.readFile(asset.absPath, 'utf8');
            contents.push(`/* extracted from ${asset.fileName} */\n${assetContent.trim()}`);
            fileMap.set(asset.fileName, relativePath);
        }

        await fs.writeFile(absolutePath, contents.join('\n\n') + '\n', 'utf8');
    }

    return fileMap;
}

async function updateViewReferences(fileMap) {
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

    for (const filePath of files) {
        const original = await fs.readFile(filePath, 'utf8');
        const updated = original.replace(viewAssetRegex, (match, fileName) => {
            const mapped = fileMap.get(fileName);
            if (!mapped) return match;
            return `/peace_seafood/inline-assets/${mapped}`;
        });
        if (updated !== original) {
            await fs.writeFile(filePath, updated, 'utf8');
        }
    }
}

async function removeFlatAssets(assets) {
    for (const asset of assets) {
        await fs.rm(asset.absPath, { force: true });
    }

    const retainedRoots = new Set(['css', 'js']);
    const entries = await fs.readdir(publicInlineRoot, { withFileTypes: true });
    for (const entry of entries) {
        if (entry.isDirectory() && !retainedRoots.has(entry.name)) {
            await fs.rm(path.join(publicInlineRoot, entry.name), { recursive: true, force: true });
        }
    }
}

async function main() {
    const assets = await readFlatAssets();
    if (assets.length === 0) {
        console.log('No flat inline assets found to organize.');
        return;
    }

    const grouped = await groupAssets(assets);
    const fileMap = await writeStructuredAssets(grouped, assets);
    await updateViewReferences(fileMap);
    await removeFlatAssets(assets);

    console.log(`Organized ${assets.length} flat inline assets into structured css/js folders.`);
}

main().catch((error) => {
    console.error(error);
    process.exit(1);
});