/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 18 Oct 2026 21:30:00 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

import fs from "node:fs";
import { spawn } from "node:child_process";
import { hasPhpTranslations, prepareExtendedParsedLangFiles, generateFiles } from "laravel-vue-i18n/loader";

const langPath = "lang/";
const frameworkLangPath = "vendor/laravel/framework/src/Illuminate/Translation/lang/";
const soloScript = "build.grp";

const sequentialBuild = JSON.parse(fs.readFileSync("package.json", "utf8")).scripts.build;
const scripts = sequentialBuild.split("&&").map((step) => step.trim().replace(/^npm run /, ""));
if (!scripts.includes(soloScript) || scripts.some((script) => script.includes(" "))) {
    console.error(`build-parallel: cannot split the build script into lanes: ${sequentialBuild}`);
    process.exit(1);
}
const lanes = [[soloScript], scripts.filter((script) => script !== soloScript)];

const runScript = (script) => new Promise((resolve) => {
    const child = spawn("npm", ["run", script], {
        stdio: "inherit",
        env: { ...process.env, LANG_FILES_PREGENERATED: "1" }
    });
    child.on("error", (error) => {
        console.error(`build-parallel: ${script} could not start: ${error.message}`);
        resolve(false);
    });
    child.on("close", (code) => resolve(code === 0));
});

const runLane = async (lane) => {
    for (const script of lane) {
        if (!(await runScript(script))) {
            console.error(`build-parallel: ${script} failed`);
            return false;
        }
    }

    return true;
};

let langFiles = [];
if (hasPhpTranslations(frameworkLangPath) || hasPhpTranslations(langPath)) {
    langFiles = generateFiles(langPath, prepareExtendedParsedLangFiles([frameworkLangPath, langPath]));
}

let succeeded = false;
try {
    succeeded = (await Promise.all(lanes.map(runLane))).every(Boolean);
} finally {
    for (const { name } of langFiles) {
        fs.rmSync(langPath + name, { force: true });
    }
}

process.exit(succeeded ? 0 : 1);
