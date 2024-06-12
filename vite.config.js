import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";

export default defineConfig(({ mode }) => {
    const appUrl =
        process.env.VITE_APP_URL || process.env.APP_URL || "http://localhost";

    // Determine the protocol based on the hostname
    const url = new URL(appUrl);
    const protocol =
        url.hostname === "dev.byterevenue.local" ? "http" : "https";

    return {
        plugins: [
            laravel({
                input: ["resources/css/app.css", "resources/js/app.js"],
                refresh: true,
            }),
        ],
        server: {
            hmr: {
                host: url.hostname,
                protocol: protocol,
            },
        },
    };
});
