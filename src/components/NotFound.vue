<template>
    <h2>{{ t("demo", "The page was not found") }}</h2>
</template>

<script>
export default {
    name: "NotFound",
    mounted() {
        this.$store.dispatch("setPage", { page: "notfound" })
    },
}
</script>

<style scoped>
h2 {
    margin: 1rem;
}
</style>
