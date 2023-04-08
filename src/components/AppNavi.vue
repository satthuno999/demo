<template>
    <!-- This component should ideally not have a conflicting name with AppNavigation from the nextcloud/vue package -->
    <NcAppNavigation>
        <router-link :to="'/recipe/create'">
            <NcAppNavigationNew
                class="create"
                :text="t('demo', 'Create recipe')"
            >
                <template #icon><plus-icon :size="20" /> </template>
            </NcAppNavigationNew>
        </router-link>

        <template slot="list">
            <NcActionInput
                class="download"
                :disabled="downloading ? 'disabled' : null"
                :icon="downloading ? 'icon-loading-small' : 'icon-download'"
                @submit="downloadRecipe"
                @update:value="updateUrl"
            >
                {{ t("demo", "Download recipe from URL") }}
            </NcActionInput>

            <NcAppNavigationItem
                :title="t('demo', 'All recipes')"
                icon="icon-category-organization"
                :to="'/'"
            >
                <template #counter>
                    <nc-counter-bubble>{{
                        totalRecipeCount
                    }}</nc-counter-bubble>
                </template>
            </NcAppNavigationItem>

            <NcAppNavigationItem
                :title="t('demo', 'Uncategorized recipes')"
                icon="icon-category-organization"
                :to="'/category/_/'"
            >
                <template #counter>
                    <nc-counter-bubble>{{ uncatRecipes }}</nc-counter-bubble>
                </template>
            </NcAppNavigationItem>

            <AppNavigationCaption
                v-if="loading.categories || categories.length > 0"
                :title="t('demo', 'Categories')"
                :loading="loading.categories"
            >
            </AppNavigationCaption>

            <NcAppNavigationItem
                v-for="(cat, idx) in categories"
                :key="cat + idx"
                :ref="'app-navi-cat-' + idx"
                :title="cat.name"
                :icon="'icon-category-files'"
                :to="'/category/' + cat.name"
                :editable="true"
                :edit-label="t('demo', 'Rename')"
                :edit-placeholder="t('demo', 'Enter new category name')"
                @update:open="categoryOpen(idx)"
                @update:title="
                    (val) => {
                        categoryUpdateName(idx, val)
                    }
                "
            >
                <template #counter>
                    <nc-counter-bubble>{{ cat.recipeCount }}</nc-counter-bubble>
                </template>
            </NcAppNavigationItem>
        </template>

        <template slot="footer">
            <NcAppNavigationNew
                :text="t('demo', 'demo settings')"
                :button-class="['create', 'icon-settings']"
                @click="handleOpenSettings"
            />
        </template>
    </NcAppNavigation>
    <div class="left-nav-container rounded-l-3xl dark:bg-$dark_strong text-gray-500  dark:text-gray-400">
        <div class="p-6">
          <div class="flex justify-between items-center">
            <!-- <CDarkMode /> -->
            <i class="fas fa-stream js-hide-navbar cursor-pointer"></i>
          </div>
          <div class="logo py-7">
            <i class="navbar__logo fab fa-forumbee text-orange-300 text-3xl"></i>
            <span class="navbar__primary-text text-orange-300 text-xl">SPARK</span><span class="text-xl">music</span>
          </div>
          <nav class="relative">
            <li>
              <a href="#"><i class="fas fa-home"></i>Albums</a>
            </li>
            <li>
              <a href="#"><i class="fas fa-chart-line"></i>Folders</a>
            </li>
            <li>
              <a href="#"><i class="far fa-compass"></i>Geners</a>
            </li>
            <li>
                  <a href="#"><i class="far fa-compass"></i>All tracks</a>
            </li>
          </nav>

          <nav class="relative">
            <p class="text-[#9ca3af] dark:text-[#9ca3af99] text-xs">Network</p>
            <ul>
              <li>
                <a href="#"><i class="far fa-newspaper"></i>Internet Radio</a>
              </li>
              <li>
                <a href="#"><i class="far fa-calendar-alt"></i>Podcasts</a>
              </li>
            </ul>
          </nav>

          <nav class="relative">
            <p class="text-[#9ca3af] dark:text-[#9ca3af99] text-xs">Your Collection</p>
            <ul>
              <li>
                <a href="#"><i class="far fa-heart"></i>Favorite Songs</a>
              </li>
              <li>
                <a href="#"><i class="far fa-user"></i>Artist</a>
              </li>
              <li>
                <a href="#"><i class="far fa-star"></i>Albums</a>
              </li>
            </ul>
          </nav>
        </div>
        <div
          class="navbar-user flex justify-between items-center p-5 border-1 border-solid border-t-[#e9e9e9] dark:border-t-gray-600 border-b-transparent border-l-transparent border-r-transparent rounded-bl-3xl dark:bg-[#1E293B]">
          <a href="">
            <div class="user-avt flex items-center">
              <img src="https://avatars.githubusercontent.com/satthuno99?v=2&s=37" alt="avatar"
                class="rounded-full mr-2" />
              <p class="text-sm cursor-pointer">satthuno99</p>
            </div>
          </a>
          <RouterLink to="/about"><i class="fas fa-chevron-right text-gray-400"></i></RouterLink>
        </div>
      </div>
</template>

<script>
import { emit } from "@nextcloud/event-bus"

import NcActionInput from "@nextcloud/vue/dist/Components/NcActionInput"
import NcAppNavigation from "@nextcloud/vue/dist/Components/NcAppNavigation"
import NcAppNavigationItem from "@nextcloud/vue/dist/Components/NcAppNavigationItem"
import NcAppNavigationNew from "@nextcloud/vue/dist/Components/NcAppNavigationNew"
import NcCounterBubble from "@nextcloud/vue/dist/Components/NcCounterBubble"

import Vue from "vue"

import PlusIcon from "icons/Plus.vue"

import api from "demo/js/api-interface"
import helpers from "demo/js/helper"
import { showSimpleAlertModal } from "demo/js/modals"

import { SHOW_SETTINGS_EVENT } from "./SettingsDialog.vue"
import AppNavigationCaption from "./AppNavigationCaption.vue"

export default {
    name: "AppNavi",
    components: {
        NcActionInput,
        NcAppNavigation,
        NcAppNavigationItem,
        NcAppNavigationNew,
        NcCounterBubble,
        AppNavigationCaption,
        PlusIcon,
    },
    data() {
        return {
            catRenamingEnabled: false,
            categories: [],
            downloading: false,
            isCategoryUpdating: [],
            loading: { categories: true },
            uncatRecipes: 0,
            importUrl: "",
        }
    },
    computed: {
        totalRecipeCount() {
            this.$log.debug("Calling totalRecipeCount")
            let total = this.uncatRecipes
            for (let i = 0; i < this.categories.length; i++) {
                total += this.categories[i].recipeCount
            }
            return total
        },
        // Computed property to watch the Vuex state. If there are more in the
        // future, consider using the Vue mapState helper
        refreshRequired() {
            return this.$store.state.appNavigation.refreshRequired
        },
        categoryUpdating() {
            return this.isCategoryUpdating
        },
    },
    watch: {
        // Register a method hook for navigation refreshing
        refreshRequired(newVal, oldVal) {
            if (newVal !== oldVal && newVal === true) {
                this.$log.debug("Calling getCategories from refreshRequired")
                this.getCategories()
            }
        },
    },
    mounted() {
        this.$log.info("AppNavi mounted")
        this.getCategories()
    },
    methods: {
        /**
         * Enable renaming of categories.
         */
        toggleCategoryRenaming() {
            this.catRenamingEnabled = !this.catRenamingEnabled
        },

        /**
         * Opens a category
         */
        async categoryOpen(idx) {
            if (
                !this.categories[idx].recipes.length ||
                this.categories[idx].recipes[0].id
            ) {
                // Recipes have already been loaded
                return
            }
            const cat = this.categories[idx]
            const $this = this
            Vue.set(this.isCategoryUpdating, idx, true)

            try {
                const response = await api.recipes.allInCategory(cat.name)
                cat.recipes = response.data
            } catch (e) {
                cat.recipes = []
                await showSimpleAlertModal(
                    // prettier-ignore
                    t("demo", "Failed to load category {category} recipes",
                        {
                            category: cat.name,
                        }
                    )
                )
                if (e && e instanceof Error) {
                    throw e
                }
            } finally {
                Vue.set($this.isCategoryUpdating, idx, false)
            }
        },

        /**
         * Updates the name of a category
         */
        async categoryUpdateName(idx, newName) {
            if (!this.categories[idx]) {
                return
            }
            Vue.set(this.isCategoryUpdating, idx, true)
            const oldName = this.categories[idx].name
            const $this = this

            try {
                await this.$store.dispatch("updateCategoryName", {
                    categoryNames: [oldName, newName],
                })
                $this.categories[idx].name = newName
                $this.$root.$emit("categoryRenamed", [newName, oldName])
            } catch (e) {
                await showSimpleAlertModal(
                    // prettier-ignore
                    t("demo",'Failed to update name of category "{category}"',
                        {
                            category: oldName,
                        }
                    )
                )
                if (e && e instanceof Error) {
                    throw e
                }
            } finally {
                Vue.set($this.isCategoryUpdating, idx, false)
            }
        },

        updateUrl(e) {
            this.importUrl = e
        },
        /**
         * Download and import the recipe at given URL
         */
        async downloadRecipe() {
            this.downloading = true
            const $this = this
            try {
                const response = await api.recipes.import(this.importUrl)
                const recipe = response.data
                $this.downloading = false
                helpers.goTo(`/recipe/${recipe.id}`)
                // Refresh left navigation pane to display changes
                $this.$store.dispatch("setAppNavigationRefreshRequired", {
                    isRequired: true,
                })
            } catch (e2) {
                $this.downloading = false

                if (e2.response) {
                    if (e2.response.status >= 400 && e2.response.status < 500) {
                        if (e2.response.status === 409) {
                            // There was a recipe found with the same name

                            await showSimpleAlertModal(e2.response.data.msg)
                        } else {
                            await showSimpleAlertModal(e2.response.data)
                        }
                    } else {
                        // eslint-disable-next-line no-console
                        console.error(e2)
                        await showSimpleAlertModal(
                            // prettier-ignore
                            t("demo","The server reported an error. Please check.")
                        )
                    }
                } else {
                    // eslint-disable-next-line no-console
                    console.error(e2)
                    await showSimpleAlertModal(
                        // prettier-ignore
                        t("demo", "Could not query the server. This might be a network problem.")
                    )
                }
            }
        },

        /**
         * Fetch and display recipe categories
         */
        async getCategories() {
            this.$log.debug("Calling getCategories")
            const $this = this
            this.loading.categories = true
            try {
                const response = await api.categories.getAll()
                const json = response.data || []
                // Reset the old values
                $this.uncatRecipes = 0
                $this.categories = []
                $this.isCategoryUpdating = []

                for (let i = 0; i < json.length; i++) {
                    if (json[i].name === "*") {
                        $this.uncatRecipes = parseInt(json[i].recipe_count, 10)
                    } else {
                        $this.categories.push({
                            name: json[i].name,
                            recipeCount: parseInt(json[i].recipe_count, 10),
                            recipes: [
                                {
                                    id: 0,
                                    // prettier-ignore
                                    name: t("demo","Loading category recipes …"),
                                },
                            ],
                        })
                        $this.isCategoryUpdating.push(false)
                    }
                }
                $this.$nextTick(() => {
                    for (let i = 0; i < $this.categories.length; i++) {
                        // Reload recipes in open categories
                        if (!$this.$refs[`app-navi-cat-${i}`]) {
                            // eslint-disable-next-line no-continue
                            continue
                        }
                        if ($this.$refs[`app-navi-cat-${i}`][0].opened) {
                            this.$log.info(
                                `Reloading recipes in ${
                                    $this.$refs[`app-navi-cat-${i}`][0].title
                                }`
                            )
                            $this.categoryOpen(i)
                        }
                    }
                    // Refreshing component data has been finished
                    $this.$store.dispatch("setAppNavigationRefreshRequired", {
                        isRequired: false,
                    })
                })
            } catch (e) {
                await showSimpleAlertModal(
                    t("demo", "Failed to fetch categories")
                )
                if (e && e instanceof Error) {
                    throw e
                }
            } finally {
                $this.loading.categories = false
            }
        },

        /**
         * Set loading recipe index to show the loading icon
         */
        setLoadingRecipe(id) {
            this.$store.dispatch("setLoadingRecipe", { recipe: id })
        },

        /**
         * Toggle the left navigation pane
         */
        toggleNavigation() {
            this.$store.dispatch("setAppNavigationVisible", {
                isVisible: !this.$store.state.appNavigation.visible,
            })
        },

        handleOpenSettings() {
            emit(SHOW_SETTINGS_EVENT)
        },
    },
}
</script>

<style scoped>
@media print {
    * {
        display: none !important;
    }
}

i {
    margin-right: 0.8rem;
    margin-left: 0.2rem;
}

nav p {
    padding: 0.6rem 0.5rem;
}

nav li {
    padding: 0.6rem 0.5rem;
    transition: 0.1s linear;
    border-radius: 0.75rem;
    cursor: pointer;
    font-size: 0.875rem;
}

nav li:hover {
    color: white;
    background: black;
}

nav li::after {
    content: "";
    position: absolute;
    background: transparent;
    width: 2px;
    transform: translateY(-0.5rem);
    height: 2.6rem;
    right: -1.5rem;
}

nav li:hover::after {
    background: black;
}

@media (max-width: 800px) {
    li {
        font-size: 0.75rem;
    }
}
</style>