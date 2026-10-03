window.PagedConfig = {
    before: async function () {
        try {
            if (document.fonts) {
                const latin = await document.fonts.load('400 12px Montserrat', 'Ohnisko jedálny lístok žluťoučký');
                const display = await document.fonts.load('400 14px "Bebas Neue"', 'OHNISKO ŽLUŤOUČKÝ');
                await document.fonts.ready;
                if (!latin.length || !display.length) throw new Error('A required Ohnisko font did not load.');
            }
        } catch (error) {
            window.__OHNISKO_PRINT_ERROR__ = String(error && error.message || error);
            throw error;
        }
    },
    after: async function () {
        try {
            if (document.fonts) await document.fonts.ready;
            const images = Array.from(document.images);
            await Promise.all(images.map(async function (image) {
                if (!image.complete) {
                    await new Promise(function (resolve, reject) {
                        image.addEventListener('load', resolve, { once: true });
                        image.addEventListener('error', reject, { once: true });
                    });
                }
                if (image.decode) await image.decode();
                if (image.naturalWidth === 0) throw new Error('A print image failed to load.');
            }));
            await Promise.all(Array.from(document.querySelectorAll('link[data-print-critical]')).map(async function (link) {
                const response = await fetch(link.href, { cache: 'force-cache' });
                if (!response.ok) throw new Error('A critical print asset failed to load.');
            }));
            window.__OHNISKO_PRINT_READY__ = true;
        } catch (error) {
            window.__OHNISKO_PRINT_ERROR__ = String(error && error.message || error);
        }
    }
};
