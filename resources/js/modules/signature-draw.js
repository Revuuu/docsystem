const SignatureDraw = {

    init() {

        const canvas =
            document.getElementById(
                'signatureCanvas'
            );

        if (!canvas) return;

        const ctx =
            canvas.getContext('2d');

        let drawing = false;

        ctx.lineWidth = 2;
        ctx.lineCap = 'round';
        ctx.strokeStyle = '#000';

        const getPosition = (e) => {

            const rect =
                canvas.getBoundingClientRect();

            if (
                e.touches &&
                e.touches.length > 0
            ) {
                return {
                    x:
                        e.touches[0].clientX -
                        rect.left,

                    y:
                        e.touches[0].clientY -
                        rect.top,
                };
            }

            return {
                x:
                    e.clientX -
                    rect.left,

                y:
                    e.clientY -
                    rect.top,
            };
        };

        const startDrawing = (e) => {

            drawing = true;

            const pos =
                getPosition(e);

            ctx.beginPath();

            ctx.moveTo(
                pos.x,
                pos.y
            );
        };

        const draw = (e) => {

            if (!drawing) return;

            e.preventDefault();

            const pos =
                getPosition(e);

            ctx.lineTo(
                pos.x,
                pos.y
            );

            ctx.stroke();
        };

        const stopDrawing = () => {
            drawing = false;
        };

        canvas.addEventListener(
            'mousedown',
            startDrawing
        );

        canvas.addEventListener(
            'mousemove',
            draw
        );

        canvas.addEventListener(
            'mouseup',
            stopDrawing
        );

        canvas.addEventListener(
            'mouseleave',
            stopDrawing
        );

        canvas.addEventListener(
            'touchstart',
            startDrawing
        );

        canvas.addEventListener(
            'touchmove',
            draw
        );

        canvas.addEventListener(
            'touchend',
            stopDrawing
        );
    },

    clear() {

        const canvas =
            document.getElementById(
                'signatureCanvas'
            );

        if (!canvas) return;

        const ctx =
            canvas.getContext('2d');

        ctx.clearRect(
            0,
            0,
            canvas.width,
            canvas.height
        );
    },

    save() {

        const canvas =
            document.getElementById(
                'signatureCanvas'
            );

        const input =
            document.getElementById(
                'signatureData'
            );

        if (!canvas || !input) {
            return;
        }

        input.value =
            canvas.toDataURL(
                'image/png'
            );
    }
};

window.clearSignatureCanvas =
    () => SignatureDraw.clear();

window.saveDrawnSignature =
    () => SignatureDraw.save();

export default SignatureDraw;