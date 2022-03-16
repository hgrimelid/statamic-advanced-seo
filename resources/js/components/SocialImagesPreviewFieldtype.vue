<template>

    <element-container @resized="resize">
        <div ref="container" class="relative w-full overflow-hidden" :style="{'padding-top': aspectRatio}">
            <iframe ref="iframe" class="absolute inset-0 w-full h-full" :src="this.meta.url" frameborder="0" scrolling="no"></iframe>
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

            socialImagesFields() {
                return _.filter(this.$store.state.publish.base.values, (value, key) => key.includes('seo_social_images_'));
            },
        },

        watch: {

            socialImagesFields() {
                this.refresh()
            }

        },

        methods: {
            refresh() {
                this.$refs.iframe.src = this.$refs.iframe.src;
            },

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
