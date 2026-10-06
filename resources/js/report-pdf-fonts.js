// Fontkit's Devanagari shaping path uses this runtime in its browser build.
import 'regenerator-runtime/runtime';
import fontkit from '@pdf-lib/fontkit';
import regularUrl from '../fonts/NotoSansDevanagari-Regular.ttf?url';
import boldUrl from '../fonts/NotoSansDevanagari-Bold.ttf?url';

let fontBytes;

export async function embedReportFonts(pdfDoc) {
    pdfDoc.registerFontkit(fontkit);
    fontBytes ??= Promise.all([regularUrl, boldUrl].map(async (url) => {
        const response = await fetch(url);
        if (!response.ok) throw new Error('The report PDF font could not be loaded.');
        return response.arrayBuffer();
    })).catch((error) => {
        fontBytes = undefined;
        throw error;
    });

    const [regular, bold] = await fontBytes;
    return {
        regular: await pdfDoc.embedFont(regular, { subset: true }),
        bold: await pdfDoc.embedFont(bold, { subset: true }),
    };
}
