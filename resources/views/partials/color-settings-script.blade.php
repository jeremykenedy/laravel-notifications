{{--
    Alpine behaviour for the colour settings widget. Keeps the picker, the hex
    field and the preview in step without a round trip, using the same tint
    maths the server uses so the preview matches what will render.
--}}
<script>
function notificationColorSettings(config) {
    return {
        colors: Object.assign({}, config.current),
        defaults: Object.assign({}, config.defaults),

        rgb(hex) {
            const value = /^#[0-9a-f]{6}$/i.test(hex || '') ? hex : '#6b7280';

            return [
                parseInt(value.slice(1, 3), 16),
                parseInt(value.slice(3, 5), 16),
                parseInt(value.slice(5, 7), 16),
            ];
        },

        rgba(hex, alpha) {
            const [r, g, b] = this.rgb(hex);

            return `rgba(${r}, ${g}, ${b}, ${alpha})`;
        },

        readableOn(hex) {
            const channel = (value) => {
                const v = value / 255;

                return v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4);
            };

            const [r, g, b] = this.rgb(hex);
            const luminance = 0.2126 * channel(r) + 0.7152 * channel(g) + 0.0722 * channel(b);

            return luminance > 0.179 ? '#111827' : '#ffffff';
        },

        rowStyle(key) {
            return {
                backgroundColor: this.rgba(this.colors[key], 0.07),
                borderColor: this.rgba(this.colors[key], 0.35),
            };
        },

        iconStyle(key) {
            return {
                backgroundColor: this.rgba(this.colors[key], 0.14),
                color: this.colors[key],
            };
        },

        badgeStyle() {
            return {
                backgroundColor: this.colors.badge,
                color: this.readableOn(this.colors.badge),
            };
        },
    };
}
</script>
