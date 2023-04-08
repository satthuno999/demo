<template>
    <div class="wrapper">
        <div class="overlay" :class="{ hidden: !overlayVisible }" />
        <EditInputField
            v-model="recipe['name']"
            :field-type="'text'"
            :field-label="t('demo', 'Name')"
        />
        <EditInputField
            v-model="recipe['description']"
            :field-type="'markdown'"
            :field-label="t('demo', 'Description')"
            :suggestion-options="allRecipeOptions"
        />
        <EditInputField
            v-model="recipe['url']"
            :field-type="'url'"
            :field-label="t('demo', 'URL')"
        />
        <EditImageField
            v-model="recipe['image']"
            :field-label="t('demo', 'Image')"
        />
        <EditTimeField
            v-model="prepTime"
            :field-label="t('demo', 'Preparation time (hours:minutes)')"
        />
        <EditTimeField
            v-model="cookTime"
            :field-label="t('demo', 'Cooking time (hours:minutes)')"
        />
        <EditTimeField
            v-model="totalTime"
            :field-label="t('demo', 'Total time (hours:minutes)')"
        />
        <EditMultiselect
            v-model="recipe['recipeCategory']"
            :field-label="t('demo', 'Category')"
            :placeholder="t('demo', 'Choose category')"
            :options="allCategories"
            :taggable="true"
            :multiple="false"
            :loading="isFetchingCategories"
            @tag="addCategory"
        />
        <EditMultiselect
            v-model="selectedKeywords"
            :field-label="t('demo', 'Keywords')"
            :placeholder="t('demo', 'Choose keywords')"
            :options="allKeywords"
            :taggable="true"
            :multiple="true"
            :tag-width="60"
            :loading="isFetchingKeywords"
            @tag="addKeyword"
        />
        <EditInputField
            v-model="recipe['recipeYield']"
            :field-type="'number'"
            :field-label="t('demo', 'Servings')"
            :hide="!showRecipeYield"
        >
            <nc-actions>
                <nc-action-button
                    class="btn-enable-recipe-yield"
                    :aria-label="
                        // prettier-ignore
                        t('demo', 'Toggle if the number of servings is present')
                    "
                    @click="toggleShowRecipeYield"
                >
                    <template #icon><numeric-icon :size="20" /></template>
                </nc-action-button>
            </nc-actions>
        </EditInputField>
        <EditMultiselectInputGroup
            v-model="recipe['nutrition']"
            :field-label="t('demo', 'Nutrition Information')"
            :options="availableNutritionFields"
            :label-select-placeholder="t('demo', 'Pick option')"
        />
        <EditInputGroup
            v-model="recipe['tool']"
            :field-name="'tool'"
            :field-type="'text'"
            :field-label="t('demo', 'Tools')"
            :create-fields-on-newlines="true"
            :suggestion-options="allRecipeOptions"
        />
        <EditInputGroup
            v-model="recipe['recipeIngredient']"
            :field-name="'recipeIngredient'"
            :field-type="'text'"
            :field-label="t('demo', 'Ingredients')"
            :create-fields-on-newlines="true"
            :suggestion-options="allRecipeOptions"
        />
        <EditInputGroup
            v-model="recipe['recipeInstructions']"
            :field-name="'recipeInstructions'"
            :field-type="'textarea'"
            :field-label="t('demo', 'Instructions')"
            :create-fields-on-newlines="true"
            :show-step-number="true"
            :suggestion-options="allRecipeOptions"
        />
        <div class="demo-footer">
            <button class="button" @click="save()">
                <span
                    :class="
                        $store.state.savingRecipe
                            ? 'icon-loading-small'
                            : 'icon-checkmark'
                    "
                ></span>
                {{ t("demo", "Save") }}
            </button>
        </div>
    </div>
</template>

<script>
import Vue from "vue"

import NcActions from "@nextcloud/vue/dist/Components/NcActions"
import NcActionButton from "@nextcloud/vue/dist/Components/NcActionButton"

import api from "demo/js/api-interface"
import helpers from "demo/js/helper"
import NumericIcon from "icons/Numeric.vue"
import {
    showSimpleAlertModal,
    showSimpleConfirmModal,
} from "demo/js/modals"

import EditImageField from "./EditImageField.vue"
import EditInputField from "./EditInputField.vue"
import EditInputGroup from "./EditInputGroup.vue"
import EditMultiselect from "./EditMultiselect.vue"
import EditMultiselectInputGroup from "./EditMultiselectInputGroup.vue"
import EditTimeField from "./EditTimeField.vue"

// prettier-ignore
const CONFIRM_MSG = t("demo", "You have unsaved changes! Do you still want to leave?")

export default {
    name: "RecipeEdit",
    components: {
        EditImageField,
        EditInputField,
        EditInputGroup,
        EditMultiselect,
        EditTimeField,
        EditMultiselectInputGroup,
        NcActions,
        NcActionButton,
        NumericIcon,
    },
    // We can check if the user has browsed from the same recipe's view to this
    // edit and save some time by not reloading the recipe data, leading to a
    // more seamless experience.
    // This assumes that the data has not been changed some other way between
    // loading the view component and loading this edit component. If that is
    // the case, the user can always manually reload by clicking the breadcrumb.
    beforeRouteEnter(to, from, next) {
        if (helpers.isSameItemInstance(from.fullPath, to.fullPath)) {
            next((vm) => {
                vm.setup()
            })
        } else if (to.params && to.params.id) {
            next((vm) => {
                vm.loadRecipeData()
            })
        } else {
            next((vm) => {
                vm.setup()
            })
        }
    },
    /**
     * This is one tricky feature of Vue router. If different paths lead to
     * the same component (such as '/recipe/create' and '/recipe/xxx/edit
     * or /recipe/xxx/edit and /recipe/yyy/edit)', the view will not automatically
     * reload. So we have to check for these conditions and reload manually.
     * This can also be used to confirm that the user wants to leave the page
     * if there are unsaved changes.
     */
    async beforeRouteLeave(to, from, next) {
        // beforeRouteLeave is called when the static route changes.
        // Cancel the navigation, if the form has unsaved edits and the user did not
        // confirm leave. This prevents accidentally losing changes
        if (
            this.isNavigationDangerous &&
            !(await showSimpleConfirmModal(CONFIRM_MSG))
        ) {
            next(false)
        } else {
            // We have to check if the target component stays the same and reload.
            // However, we should not reload if the component changes; otherwise
            // reloaded data may overwrite the data loaded at the target component
            // which will at the very least result in incorrect breadcrumb path!
            next()
        }
        // Check if we should reload the component content
        if (helpers.shouldReloadContent(from.fullPath, to.fullPath)) {
            this.setup()
        }
    },
    beforeRouteUpdate(to, from, next) {
        // beforeRouteUpdate is called when the static route stays the same
        next()
        // Check if we should reload the component content
        if (helpers.shouldReloadContent(from.fullPath, to.fullPath)) {
            this.setup()
        }
    },
    props: {
        id: {
            type: String,
            default: "",
        },
    },
    data() {
        return {
            // Initialize the recipe schema, otherwise v-models in child components may not work
            recipe: {
                id: 0,
                name: null,
                description: "",
                url: "",
                image: "",
                prepTime: "",
                cookTime: "",
                totalTime: "",
                recipeCategory: "",
                keywords: "",
                recipeYield: "",
                tool: [],
                recipeIngredient: [],
                recipeInstructions: [],
                nutrition: {},
            },
            // This will hold the above configuration after recipe is loaded, so we don't have to
            // keep it up to date in multiple places if it changes later
            recipeInit: null,

            // ==========================
            // These are helper variables

            // Changes have been made to the initial values of the form
            formDirty: false,
            // the save button has been clicked
            savingRecipe: false,
            prepTime: { time: [0, 0], paddedTime: "" },
            cookTime: { time: [0, 0], paddedTime: "" },
            totalTime: { time: [0, 0], paddedTime: "" },
            allCategories: [],
            isFetchingCategories: true,
            isFetchingKeywords: true,
            allKeywords: [],
            selectedKeywords: [],
            allRecipes: [],
            availableNutritionFields: [
                {
                    key: "calories",
                    label: t("demo", "Calories"),
                    // prettier-ignore
                    placeholder: t("demo","E.g.: 450 kcal (amount & unit)"),
                },
                {
                    key: "carbohydrateContent",
                    label: t("demo", "Carbohydrate content"),
                    placeholder: t("demo", "E.g.: 2 g (amount & unit)"),
                },
                {
                    key: "cholesterolContent",
                    label: t("demo", "Cholesterol content"),
                    placeholder: t("demo", "E.g.: 2 g (amount & unit)"),
                },
                {
                    key: "fatContent",
                    label: t("demo", "Fat content"),
                    placeholder: t("demo", "E.g.: 2 g (amount & unit)"),
                },
                {
                    key: "fiberContent",
                    label: t("demo", "Fiber content"),
                    placeholder: t("demo", "E.g.: 2 g (amount & unit)"),
                },
                {
                    key: "proteinContent",
                    label: t("demo", "Protein content"),
                    placeholder: t("demo", "E.g.: 2 g (amount & unit)"),
                },
                {
                    key: "saturatedFatContent",
                    label: t("demo", "Saturated-fat content"),
                    placeholder: t("demo", "E.g.: 2 g (amount & unit)"),
                },
                {
                    key: "servingSize",
                    label: t("demo", "Serving size"),
                    // prettier-ignore
                    placeholder: t("demo","Enter serving size (volume or mass)"),
                },
                {
                    key: "sodiumContent",
                    label: t("demo", "Sodium content"),
                    placeholder: t("demo", "E.g.: 2 g (amount & unit)"),
                },
                {
                    key: "sugarContent",
                    label: t("demo", "Sugar content"),
                    placeholder: t("demo", "E.g.: 2 g (amount & unit)"),
                },
                {
                    key: "transFatContent",
                    label: t("demo", "Trans-fat content"),
                    placeholder: t("demo", "E.g.: 2 g (amount & unit)"),
                },
                {
                    key: "unsaturatedFatContent",
                    label: t("demo", "Unsaturated-fat content"),
                    placeholder: t("demo", "E.g.: 2 g (amount & unit)"),
                },
            ],
            referencesPopupFocused: false,
            loadingRecipeReferences: true,
            showRecipeYield: true,
        }
    },
    computed: {
        allRecipeOptions() {
            return this.allRecipes.map((r) => ({
                recipe_id: r.recipe_id,
                title: `${r.recipe_id}: ${r.name}`,
            }))
        },
        categoryUpdating() {
            return this.$store.state.categoryUpdating
        },
        overlayVisible() {
            return (
                this.$store.state.loadingRecipe ||
                this.$store.state.reloadingRecipe ||
                (this.$store.state.categoryUpdating &&
                    this.$store.state.categoryUpdating ===
                        this.recipe.recipeCategory)
            )
        },
        recipeWithCorrectedYield() {
            const r = this.recipe
            if (!this.showRecipeYield) {
                r.recipeYield = null
            }
            return r
        },

        // Whether navigation would lose data, etc.
        isNavigationDangerous() {
            return !this.savingRecipe && this.formDirty
        },
    },
    watch: {
        prepTime: {
            handler() {
                this.recipe.prepTime = this.prepTime.paddedTime
            },
            deep: true,
        },
        cookTime: {
            handler() {
                this.recipe.cookTime = this.cookTime.paddedTime
            },
            deep: true,
        },
        totalTime: {
            handler() {
                this.recipe.totalTime = this.totalTime.paddedTime
            },
            deep: true,
        },
        selectedKeywords: {
            deep: true,
            handler() {
                // convert keyword array to comma-separated string
                this.recipe.keywords = this.selectedKeywords.join()
            },
        },
        recipe: {
            deep: true,
            handler() {
                this.formDirty = true
            },
        },
    },
    mounted() {
        this.$log.info("RecipeEdit mounted")
        const $this = this

        // Store the initial recipe configuration for possible later use
        if (this.recipeInit === null) {
            this.recipeInit = this.recipe
        }
        // Register save method hook for access from the controls components
        // The event hookmust first be destroyed to avoid it from firing multiple
        // times if the same component is loaded again
        this.$root.$off("saveRecipe")
        this.$root.$on("saveRecipe", () => {
            this.save()
        })
        // Register data load method hook for access from the controls components
        this.$root.$off("reloadRecipeEdit")
        this.$root.$on("reloadRecipeEdit", () => {
            this.loadRecipeData()
        })
        this.$root.$off("categoryRenamed")
        this.$root.$on("categoryRenamed", (val) => {
            // Update selectable categories
            const idx = this.allCategories.findIndex((c) => c === val[1])
            if (idx >= 0) {
                Vue.set(this.allCategories, idx, val[0])
                // this.allCategories[idx] = val[0]
            }
            // Update selected category if the currently selected was renamed
            if (this.recipe.recipeCategory === val[1]) {
                // eslint-disable-next-line prefer-destructuring
                this.recipe.recipeCategory = val[0]
            }
        })
        this.savingRecipe = false

        // Load data for all recipes to be used in recipe-reference popup suggestions
        api.recipes
            .getAll()
            .then((response) => {
                $this.allRecipes = response.data
            })
            .catch((e) => {
                this.$log.error(e)
            })
            .then(() => {
                // finally
                $this.loadingRecipeReferences = false
            })
    },
    beforeDestroy() {
        window.removeEventListener("beforeunload", this.beforeWindowUnload)
    },
    created() {
        window.addEventListener("beforeunload", this.beforeWindowUnload)
    },
    methods: {
        /**
         * Add newly created category and set as selected.
         */
        addCategory(newCategory) {
            this.allCategories.push(newCategory)
            this.recipe.recipeCategory = newCategory
        },
        /**
         * Add newly created keyword.
         */
        addKeyword(newKeyword) {
            this.allKeywords.push(newKeyword)
            this.selectedKeywords.push(newKeyword)
        },
        addEntry(field, index, content = "") {
            this.recipe[field].splice(index, 0, content)
        },
        beforeWindowUnload(e) {
            // We cannot use our fancy modal here because `beforeunload` does not wait for promises to resolve
            // However, we can avoid `window.confirm` by using `e.returnValue`
            if (this.isNavigationDangerous) {
                // Cancel the window unload event
                e.preventDefault()
                e.returnValue = CONFIRM_MSG
            }
        },
        deleteEntry(field, index) {
            this.recipe[field].splice(index, 1)
        },
        /**
         * Fetch and display recipe categories
         */
        async fetchCategories() {
            const $this = this
            try {
                const response = await api.categories.getAll()
                const json = response.data || []
                $this.allCategories = []
                for (let i = 0; i < json.length; i++) {
                    if (json[i].name !== "*") {
                        $this.allCategories.push(json[i].name)
                    }
                }
                $this.isFetchingCategories = false
            } catch (e) {
                await showSimpleAlertModal(
                    t("demo", "Failed to fetch categories")
                )
                if (e && e instanceof Error) {
                    throw e
                }
            }
        },
        /**
         * Fetch and display recipe keywords
         */
        async fetchKeywords() {
            const $this = this
            try {
                const response = await api.keywords.getAll()
                const json = response.data || []
                if (json) {
                    $this.allKeywords = []
                    for (let i = 0; i < json.length; i++) {
                        if (json[i].name !== "*") {
                            $this.allKeywords.push(json[i].name)
                        }
                    }
                }
                $this.isFetchingKeywords = false
            } catch (e) {
                await showSimpleAlertModal(
                    t("demo", "Failed to fetch keywords")
                )
                if (e && e instanceof Error) {
                    throw e
                }
            }
        },
        async loadRecipeData() {
            if (!this.$store.state.recipe) {
                // Make the control row show that a recipe is loading
                this.$store.dispatch("setLoadingRecipe", {
                    recipe: -1,
                })
            } else if (
                this.$store.state.recipe.id ===
                parseInt(this.$route.params.id, 10)
            ) {
                // Make the control row show that the recipe is reloading
                this.$store.dispatch("setReloadingRecipe", {
                    recipe: this.$route.params.id,
                })
            }
            const $this = this
            try {
                const response = await api.recipes.get(this.$route.params.id)
                const recipe = response.data
                $this.$store.dispatch("setRecipe", { recipe })
                $this.setup()
            } catch {
                await showSimpleAlertModal(
                    t("demo", "Loading recipe failed")
                )
                // Disable loading indicator
                if ($this.$store.state.loadingRecipe) {
                    $this.$store.dispatch("setLoadingRecipe", { recipe: 0 })
                } else if ($this.$store.state.reloadingRecipe) {
                    $this.$store.dispatch("setReloadingRecipe", {
                        recipe: 0,
                    })
                }
                // Browse to new recipe creation
                helpers.goTo("/recipe/create")
            }
        },
        async save() {
            this.savingRecipe = true
            this.$store.dispatch("setSavingRecipe", { saving: true })
            const $this = this

            const request = (() => {
                if (this.$route.params.id ?? false) {
                    return this.$store.dispatch("updateRecipe", {
                        recipe: this.recipeWithCorrectedYield,
                    })
                }
                return this.$store.dispatch("createRecipe", {
                    recipe: this.recipeWithCorrectedYield,
                })
            })()

            try {
                const response = await request
                helpers.goTo(`/recipe/${response.data}`)
            } catch (e) {
                if (e.response) {
                    // Non 2xx state returned

                    switch (e.response.status) {
                        case 409:
                        case 422:
                            await showSimpleAlertModal(e.response.data.msg)
                            break

                        default:
                            await showSimpleAlertModal(
                                // prettier-ignore
                                t("demo","Unknown answer returned from server. See logs.")
                            )
                            this.$log.error(e.response)
                    }
                } else if (e.request) {
                    await showSimpleAlertModal(
                        t("demo", "No answer for request was received.")
                    )
                    this.$log.error(e)
                } else {
                    await showSimpleAlertModal(
                        // prettier-ignore
                        t("demo","Could not start request to save recipe.")
                    )
                    this.$log.error(e)
                }
            } finally {
                $this.$store.dispatch("setSavingRecipe", {
                    saving: false,
                })
                $this.savingRecipe = false
            }
        },
        setup() {
            this.fetchCategories()
            this.fetchKeywords()
            if (this.$route.params.id) {
                // Load the recipe from store and make edits to a local copy first
                this.recipe = { ...this.$store.state.recipe }
                // Parse time values
                let timeComps = this.recipe.prepTime
                    ? this.recipe.prepTime.match(/PT(\d+?)H(\d+?)M/)
                    : null
                this.prepTime = {
                    time: timeComps ? [timeComps[1], timeComps[2]] : [0, 0],
                    paddedTime: this.recipe.prepTime,
                }

                timeComps = this.recipe.cookTime
                    ? this.recipe.cookTime.match(/PT(\d+?)H(\d+?)M/)
                    : null
                this.cookTime = {
                    time: timeComps ? [timeComps[1], timeComps[2]] : [0, 0],
                    paddedTime: this.recipe.cookTime,
                }

                timeComps = this.recipe.totalTime
                    ? this.recipe.totalTime.match(/PT(\d+?)H(\d+?)M/)
                    : null

                this.totalTime = {
                    time: timeComps ? [timeComps[1], timeComps[2]] : [0, 0],
                    paddedTime: this.recipe.totalTime,
                }

                this.selectedKeywords = this.recipe.keywords
                    .split(",")
                    .map((kw) => kw.trim())
                    // Remove any empty keywords
                    // If the response from the server is just an empty
                    // string, split will create an array of a single empty
                    // string
                    .filter((kw) => kw !== "")

                // fallback if fetching all keywords fails
                this.selectedKeywords.forEach((kw) => {
                    if (!this.allKeywords.includes(kw)) {
                        this.allKeywords.push(kw)
                    }
                })

                // fallback if fetching all categories fails
                if (!this.allCategories.includes(this.recipe.recipeCategory)) {
                    this.allCategories.push(this.recipe.recipeCategory)
                }

                if (this.recipe.recipeYield === null) {
                    this.showRecipeYield = false
                } else if (!this.recipe.recipeYield) {
                    this.showRecipeYield = false
                    this.recipe.recipeYield = null
                } else {
                    this.showRecipeYield = true
                }

                // Always set the active page last!
                this.$store.dispatch("setPage", { page: "edit" })
            } else {
                this.initEmptyRecipe()
                this.$store.dispatch("setPage", { page: "create" })
            }
            this.recipeInit = JSON.parse(JSON.stringify(this.recipe))
            this.$nextTick(function markDirty() {
                this.formDirty = false
            })
        },
        initEmptyRecipe() {
            this.prepTime = { time: [0, 0], paddedTime: "" }
            this.cookTime = { time: [0, 0], paddedTime: "" }
            this.totalTime = { time: [0, 0], paddedTime: "" }
            this.nutrition = {}
            this.recipe = {
                id: 0,
                name: null,
                description: "",
                url: "",
                image: "",
                prepTime: "",
                cookTime: "",
                totalTime: "",
                recipeCategory: "",
                keywords: "",
                recipeYield: "",
                tool: [],
                recipeIngredient: [],
                recipeInstructions: [],
                nutrition: {},
            }
            this.formDirty = false
            this.showRecipeYield = true
        },
        toggleShowRecipeYield() {
            this.showRecipeYield = !this.showRecipeYield
            this.formDirty = true
        },
    },
}
</script>

<style scoped>
.wrapper {
    width: 100%;
    padding: 1rem;
}

.overlay {
    position: absolute;
    z-index: 1000;
    top: 0;
    left: 0;
    display: block;
    width: 100%;
    height: 100%;
    background-color: var(--color-main-background);
    opacity: 0.75;
}

.overlay.hidden {
    display: none;
}

/* This is not used anywhere at the moment, but left here for future reference
form fieldset ul label input[type="checkbox"] {
    margin-left: 1em;
    vertical-align: middle;
    cursor: pointer;
} */

.references-popup {
    position: fixed;
    display: none;
}

.references-popup.visible {
    display: block;
}

.demo-footer {
    margin-top: 3.5em;
    text-align: end;
}

.btn-enable-recipe-yield {
    vertical-align: bottom;
}
</style>
