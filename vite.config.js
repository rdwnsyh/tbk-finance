import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import { bunny } from "laravel-vite-plugin/fonts";
import tailwindcss from "@tailwindcss/vite";
import vue from "@vitejs/plugin-vue";

export default defineConfig({
    // Hanya boleh ada SATU key `plugins`.
    // Sebelumnya file ini punya dua key `plugins`; key kedua menimpa key pertama,
    // sehingga konfigurasi `laravel()` hilang dan plugin Vue tidak pernah aktif.
    plugins: [
        // Plugin inti Laravel: menentukan entry point asset, hot reload, dan font Bunny.
        laravel({
            input: ["resources/css/app.css", "resources/js/app.js"],
            refresh: true,
            fonts: [
                // Font ini dipakai lewat variabel CSS `--font-sans` di resources/css/app.css.
                bunny("Instrument Sans", {
                    weights: [400, 500, 600],
                    // Fitur fallback teroptimasi membutuhkan paket opsional `fontaine`.
                    // Karena paket itu di luar daftar yang diizinkan, fitur ini dimatikan
                    // agar build tidak memunculkan peringatan. Font tetap dimuat normal.
                    optimizedFallbacks: false,
                }),
            ],
        }),
        // Tailwind 4 diproses lewat plugin Vite (bukan PostCSS) agar build lebih cepat.
        tailwindcss(),
        // Plugin inilah yang meng-compile file `.vue`; tanpa ini Vue SFC tidak bisa dibundel.
        vue(),
    ],
    server: {
        // Folder view hasil kompilasi Blade sering berubah dan bukan source,
        // jadi diabaikan agar hot reload tidak memicu rebuild berulang.
        watch: {
            ignored: ["**/storage/framework/views/**"],
        },
    },
});
