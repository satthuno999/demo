<script setup>
if (
    localStorage.getItem("color-theme") === "dark" ||
    (!("color-theme" in localStorage) &&
        window.matchMedia("(prefers-color-scheme: dark)").matches)
) {
    document.documentElement.classList.add("dark")
} else {
    document.documentElement.classList.remove("dark")
}
</script>

<template>
    <NcContent app-name="demo"
        class="bg-[#e9e9e9] dark:bg-[#888] px-[12%] py-5 min-h-screen grid justify-center items-center">
        <AppNavi class="app-navigation col-span-1 bg-white" />
        <NcAppContent>
            <div>
                <AppControls />
                <div class="demo-app-content">
                    <router-view></router-view>
                </div>
            </div>
            <div v-if="isMobile" class="navigation-overlay" :class="{ 'stay-open': isNavigationOpen }"
                @click="closeNavigation" />
        </NcAppContent>
        <dialogs-wrapper></dialogs-wrapper>
        <SettingsDialog />
    </NcContent>
</template>

<script>
import isMobile from "@nextcloud/vue/dist/Mixins/isMobile"
import NcAppContent from "@nextcloud/vue/dist/Components/NcAppContent"
import NcContent from "@nextcloud/vue/dist/Components/NcContent"
import AppControls from "demo/components/AppControls/AppControls.vue"
import { emit, subscribe, unsubscribe } from "@nextcloud/event-bus"
import AppNavi from "./AppNavi.vue"
import SettingsDialog from "./SettingsDialog.vue"
export default {
    name: "AppMain",
    components: {
        NcAppContent,
        AppControls,
        AppNavi,
        SettingsDialog,
        // eslint-disable-next-line vue/no-reserved-component-names
        NcContent,
    },
    mixins: [isMobile],
    data() {
        return {
            isNavigationOpen: false,
        }
    },
    watch: {
        $route(to, from) {
            this.$log.debug(
                this.$window.isSameBaseRoute(from.fullPath, to.fullPath)
            )
        },
    },
    mounted() {
        this.$log.info("AppMain mounted")
        const stylefontawesome = document.createElement("style");
        stylefontawesome.setAttribute(
            "ref",
            "stylesheet"
        );
        stylefontawesome.setAttribute(
            "href",
            "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css"
        );
        document.head.appendChild(stylefontawesome);
        const styleggfont = document.createElement("style");
        styleggfont.setAttribute(
            "ref",
            "stylesheet"
        );
        styleggfont.setAttribute(
            "href",
            "https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500&display=swap"
        );
        document.head.appendChild(styleggfont);
        const scriptjquery = document.createElement("script");
        scriptjquery.setAttribute(
            "src",
            "https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"
        );
        document.head.appendChild(scriptjquery);
        subscribe("navigation-toggled", this.updateAppNavigationOpen)
    },
    unmounted() {
        unsubscribe("navigation-toggled", this.updateAppNavigationOpen)
    },
    methods: {
        /**
         * Listen for event-bus events about the app navigation opening and closing
         */
        updateAppNavigationOpen({ open }) {
            this.isNavigationOpen = open
        },
        closeNavigation() {
            emit("toggle-navigation", { open: false })
        },
    },
}
</script>
<style>
@import "~/assets/base.css";
@import "~/assets/_variables.css";
</style>

<!-- <style lang="scss" scoped>
.app-navigation {
    /* Content has z-index 1000 */
    z-index: 2000;
}

.demo-app-content {
    position: relative;
    z-index: 0;
}

/**
 * The open event is only emitted when the animation stops
 * In order to match their animation, we need to start fading in the overlay
 * as soon as the .app-navigation--close` class goes away
 * using a sibling selector
 *
 * We still need to listen for events to help us close the overlay.
 * We cannot set `display: none` in an animation.
 * We need to start fading out when the .app-navigation--close` class comes back,
 * and use the close event that fired when the animation stops to reset
 * `display: none`
 */
.navigation-overlay {
    position: absolute;
    /* Top bar has z-index 2 */
    z-index: 3;
    display: none;
    animation: fade-out var(--animation-quick) forwards;
    background: rgba(0, 0, 0, 0.5);
    cursor: pointer;
    inset: 0;
}

.navigation-overlay.stay-open {
    display: block;
}

#app-navigation-vue:not(.app-navigation--close)+#app-content-vue .navigation-overlay {
    display: block;
    animation: fade-in var(--animation-quick) forwards;
}

@keyframes fade-in {
    0% {
        opacity: 0;
    }

    100% {
        opacity: 1;
    }
}

@keyframes fade-out {
    0% {
        opacity: 1;
    }

    100% {
        opacity: 0;
    }
}
</style>

<style>
@media print {
    #app-content-vue {
        display: block !important;
        overflow: visible !important;
        padding: 0 !important;
        margin-left: 0 !important;
    }

    #app-navigation-vue {
        display: none !important;
    }

    #header {
        display: none !important;
    }

    a:link::after,
    a:visited::after {
        content: " [" attr(href) "] ";
    }

    body {
        position: static;
    }

    #content-vue {
        position: static;
        width: 100%;
        height: initial;
        border-radius: 0;
        margin: 0;
    }
}</style> -->