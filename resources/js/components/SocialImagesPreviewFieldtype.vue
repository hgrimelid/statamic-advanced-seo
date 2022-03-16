<template>

    <element-container @resized="resize">
        <div ref="container" class="relative w-full overflow-hidden" :style="{'padding-top': aspectRatio}">
            <iframe ref="iframe" class="absolute inset-0 w-full h-full" :src="this.meta.url" scrolling="no"></iframe>
        </div>
    </element-container>

</template>

<script>
    export default {
        name: 'social-images-preview-fieldtype',
        mixins: [Fieldtype],

        computed: {
            aspectRatio() {
                return `${(this.meta.height / this.meta.width) * 100}%`;
            },
        },

        methods: {
            resize() {
                this.$refs.iframe.contentDocument.body.style.transformOrigin="top left"
                this.$refs.iframe.contentDocument.body.style.transform=`scale(${this.getContainerWidth() / this.meta.width})`
            },

            getContainerWidth() {
                return this.$refs.container.offsetWidth;
            },
        }
    }
</script>
