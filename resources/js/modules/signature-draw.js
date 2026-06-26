const SignatureDraw = {
    canvas: null,
    ctx: null,
    drawing: false,
    lastX: 0,
    lastY: 0,

    init() {
        this.canvas =
            document.getElementById('signatureCanvas');

        this.cursor =
            document.getElementById('drawCursor');

        if (!this.canvas) return;

        this.ctx =
            this.canvas.getContext('2d');

        this.canvas.style.touchAction = 'none';

        this.ctx.lineWidth = 1.5;
        this.ctx.lineCap = 'round';
        this.ctx.lineJoin = 'round';
        this.ctx.strokeStyle = '#000';

        this.bindDrawingEvents();

        window.clearSignatureCanvas =
            () => this.clear();

        window.saveDrawnSignature =
            () => this.save();
    },

    bindDrawingEvents() {

    this.canvas.addEventListener('pointerenter', (event) => {

        if (this.cursor) {
            this.cursor.style.display = 'block';
            this.moveCursor(event);
        }

    });

    this.canvas.addEventListener('pointermove', (event) => {

        this.moveCursor(event);
        this.draw(event);

    });

    this.canvas.addEventListener('pointerdown', (event) => {

        this.moveCursor(event);
        this.startDrawing(event);

    });

    this.canvas.addEventListener('pointerup', (event) => {

        this.stopDrawing(event);

    });

    this.canvas.addEventListener('pointerleave', (event) => {

        if (this.cursor) {
            this.cursor.style.display = 'none';
        }

        this.stopDrawing(event);

    });

    this.canvas.addEventListener('pointercancel', (event) => {

        if (this.cursor) {
            this.cursor.style.display = 'none';
        }

        this.stopDrawing(event);

    });
},

    startDrawing(event) {
        event.preventDefault();

        this.drawing = true;

        this.canvas.setPointerCapture?.(
            event.pointerId
        );

        const pos =
            this.getPosition(event);

        this.lastX = pos.x;
        this.lastY = pos.y;

        this.ctx.beginPath();

        this.ctx.moveTo(
            this.lastX,
            this.lastY
        );
    },

    draw(event) {
        if (!this.drawing) return;

        event.preventDefault();

        const pos =
            this.getPosition(event);

        const midX =
            (this.lastX + pos.x) / 2;

        const midY =
            (this.lastY + pos.y) / 2;

        this.ctx.quadraticCurveTo(
            this.lastX,
            this.lastY,
            midX,
            midY
        );

        this.ctx.stroke();

        this.lastX = pos.x;
        this.lastY = pos.y;
    },

    stopDrawing(event) {
        if (!this.drawing) return;

        this.drawing = false;

        this.ctx.beginPath();

        if (event?.pointerId) {
            this.canvas.releasePointerCapture?.(
                event.pointerId
            );
        }
    },

    getPosition(event) {
        const rect =
            this.canvas.getBoundingClientRect();

        return {
            x:
                (event.clientX - rect.left) *
                (this.canvas.width / rect.width),

            y:
                (event.clientY - rect.top) *
                (this.canvas.height / rect.height),
        };
    },

    moveCursor(event) {

    if (!this.cursor) return;

    this.cursor.style.left =
        `${event.clientX}px`;

    this.cursor.style.top =
        `${event.clientY}px`;
},

    clear() {
        if (!this.canvas || !this.ctx) return;

        this.ctx.clearRect(
            0,
            0,
            this.canvas.width,
            this.canvas.height
        );
    },

    save() {
        const input =
            document.getElementById('signatureData');

        if (!this.canvas || !input) {
            return false;
        }

        input.value =
            this.canvas.toDataURL('image/png');

        return true;
    }
};

export default SignatureDraw;