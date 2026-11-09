import { cp, mkdir, writeFile } from "node:fs/promises";
import { dirname, resolve } from "node:path";
import { fileURLToPath } from "node:url";

const [, , input = "http://127.0.0.1:8793/", output = ".local/pages"] = process.argv;
const local = new URL(input);
const destination = resolve(output);
const repository = fileURLToPath(new URL("../", import.meta.url));
const assetPath = "/wp-content/themes/brow-boutique/assets/";
const mediaPath = "/wp-content/uploads/";
const publicRoot = "https://fedinartemii.github.io/BrowBoutique/";

const response = await fetch(local);
if (!response.ok) throw new Error("WordPress responded with " + response.status);
const html = await response.text();
const title = html.match(/<title>[^<]+<\/title>/)?.[0];
const body = html.match(/<header class="nav site-header"[\s\S]*?<\/main>/)?.[0];
const description = html.match(/<meta name="description" content="[^"]+">/)?.[0];
const styleVersion = html.match(/\/assets\/site\.css(\?ver=[\w.-]+)['"]/)?.[1] ?? "";
if (!title || !description || !body || !body.includes('id="contacts"')) {
  throw new Error("The WordPress home page did not render.");
}

const main = body
  .replaceAll(local.origin + assetPath, "./assets/")
  .replaceAll(assetPath, "./assets/")
  .replaceAll(local.origin + mediaPath, "./media/")
  .replaceAll(mediaPath, "./media/")
  .replaceAll('href="' + local.origin + '/#', 'href="#')
  .replaceAll('href="' + local.origin + '/"', 'href="./"');
if (/localhost|127\.0\.0\.1|\/wp-content\//.test(main)) {
  throw new Error("A local WordPress URL remains in the export.");
}

const page = `<!doctype html>
<html lang="ru" data-type="place" data-motion data-radius="sharp" data-buttons="solid">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
${title}
${description}
<meta name="page-type" content="place">
<meta name="page-kind" content="place">
<meta name="site-kind" content="other">
<meta name="theme-color" content="#f3e1ca">
<meta property="og:type" content="website">
<meta property="og:title" content="Бутик Бровей">
<meta property="og:description" content="Школа-студия бровей и ресниц в Челябинске. Онлайн-запись в DIKIDI.">
<meta property="og:image" content="${publicRoot}assets/images/profile.webp">
<link rel="canonical" href="${publicRoot}">
<link rel="icon" href="./assets/images/logo.png">
<link rel="stylesheet" href="./assets/site.css${styleVersion}">
<link rel="stylesheet" href="./assets/motion/motion.css">
<script>document.documentElement.classList.add('m-enter');setTimeout(()=>document.documentElement.classList.add('m-enter-done'),700)</script>
<script type="module" src="./assets/site.js"></script>
</head>
<body class="home">
${main}
</body>
</html>
`;

await mkdir(destination, { recursive:true });
await cp(resolve(repository, "theme/brow-boutique/assets"), resolve(destination, "assets"), { recursive:true });
const mediaFiles = new Set([...main.matchAll(/\.\/media\/([^\s"',<>?#)]+)/g)].map((match) => match[1]));
for (const file of mediaFiles) {
  const url = new URL(mediaPath + file, local);
  const target = resolve(destination, "media", decodeURIComponent(file));
  const mediaDirectory = resolve(destination, "media");
  if (dirname(target) !== mediaDirectory && !target.startsWith(mediaDirectory + (process.platform === "win32" ? "\\" : "/"))) {
    throw new Error("Invalid media path");
  }
  const asset = await fetch(url);
  if (!asset.ok) throw new Error("Cannot export media: " + url.pathname);
  await mkdir(dirname(target), { recursive:true });
  await writeFile(target, Buffer.from(await asset.arrayBuffer()));
}
await writeFile(resolve(destination, "index.html"), page);
await writeFile(resolve(destination, ".nojekyll"), "");
console.log("Export: " + destination);
