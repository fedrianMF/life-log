<script setup>
import Icon from '../Components/Icon.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { cilAccountLogout } from '@coreui/icons/dist/esm/free/cil-account-logout.js';
import { cilChartLine } from '@coreui/icons/dist/esm/free/cil-chart-line.js';
import { cilChevronBottom } from '@coreui/icons/dist/esm/free/cil-chevron-bottom.js';
import { cilContrast } from '@coreui/icons/dist/esm/free/cil-contrast.js';
import { cilHome } from '@coreui/icons/dist/esm/free/cil-home.js';
import { cilMenu } from '@coreui/icons/dist/esm/free/cil-menu.js';
import { cilMoon } from '@coreui/icons/dist/esm/free/cil-moon.js';
import { cilPlus } from '@coreui/icons/dist/esm/free/cil-plus.js';
import { cilSun } from '@coreui/icons/dist/esm/free/cil-sun.js';
import { cilUser } from '@coreui/icons/dist/esm/free/cil-user.js';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';

const page = usePage();
const sidebarOpen = ref(false);
const sidebarNarrow = ref(false);
const isDesktop = ref(false);
const openMenu = ref(null);
const theme = ref('auto');

const user = computed(() => page.props.auth.user);
const currentPath = computed(() => page.url.split('?')[0]);
const isTimelineActive = computed(() => currentPath.value === '/');
const isCaptureActive = computed(() => currentPath.value === '/events/create');
const pageTitle = computed(() => {
    if (isCaptureActive.value) {
        return 'Capture';
    }

    if (/^\/events\/\d+\/edit$/.test(currentPath.value)) {
        return 'Edit entry';
    }

    return 'Timeline';
});
const isSidebarExpanded = computed(() =>
    isDesktop.value ? !sidebarNarrow.value : sidebarOpen.value,
);

const navItems = [
    { label: 'Timeline', href: '/', icon: cilHome, active: isTimelineActive },
    { label: 'Capture', href: '/events/create', icon: cilPlus, active: isCaptureActive },
    { label: 'Stats', href: '#', icon: cilChartLine, active: computed(() => false) },
];

const themeOptions = [
    { value: 'light', label: 'Light', icon: cilSun },
    { value: 'dark', label: 'Dark', icon: cilMoon },
    { value: 'auto', label: 'Auto', icon: cilContrast },
];

const activeThemeIcon = computed(
    () => themeOptions.find((option) => option.value === theme.value)?.icon ?? cilContrast,
);

function closeMenus() {
    openMenu.value = null;
}

function toggleMenu(menu) {
    openMenu.value = openMenu.value === menu ? null : menu;
}

function closeSidebar() {
    sidebarOpen.value = false;
}

function toggleSidebar() {
    if (isDesktop.value) {
        sidebarNarrow.value = !sidebarNarrow.value;
        return;
    }

    sidebarOpen.value = !sidebarOpen.value;
}

function applyTheme(selectedTheme) {
    const resolvedTheme =
        selectedTheme === 'auto'
            ? window.matchMedia('(prefers-color-scheme: dark)').matches
                ? 'dark'
                : 'light'
            : selectedTheme;

    document.documentElement.setAttribute('data-coreui-theme', resolvedTheme);
}

function selectTheme(selectedTheme) {
    theme.value = selectedTheme;
    applyTheme(selectedTheme);

    try {
        localStorage.setItem('life-log-theme', selectedTheme);
    } catch {
        // The selected theme still applies when storage is unavailable.
    }

    closeMenus();
}

function handleDocumentClick(event) {
    if (!event.target.closest('[data-app-menu]')) {
        closeMenus();
    }
}

function handleKeydown(event) {
    if (event.key === 'Escape') {
        closeMenus();
        closeSidebar();
    }
}

function handleSystemThemeChange() {
    if (theme.value === 'auto') {
        applyTheme('auto');
    }
}

function handleBreakpointChange(event) {
    isDesktop.value = event.matches;

    if (event.matches) {
        closeSidebar();
    }
}

let colorSchemeQuery;
let desktopQuery;

onMounted(() => {
    try {
        theme.value = localStorage.getItem('life-log-theme') ?? 'auto';
    } catch {
        theme.value = 'auto';
    }

    applyTheme(theme.value);
    colorSchemeQuery = window.matchMedia('(prefers-color-scheme: dark)');
    colorSchemeQuery.addEventListener('change', handleSystemThemeChange);
    desktopQuery = window.matchMedia('(min-width: 992px)');
    isDesktop.value = desktopQuery.matches;
    desktopQuery.addEventListener('change', handleBreakpointChange);
    document.addEventListener('click', handleDocumentClick);
    document.addEventListener('keydown', handleKeydown);
});

onBeforeUnmount(() => {
    colorSchemeQuery?.removeEventListener('change', handleSystemThemeChange);
    desktopQuery?.removeEventListener('change', handleBreakpointChange);
    document.removeEventListener('click', handleDocumentClick);
    document.removeEventListener('keydown', handleKeydown);
});
</script>

<template>
    <div
        id="app-sidebar"
        class="sidebar sidebar-dark sidebar-fixed border-end"
        :class="{ show: sidebarOpen, 'sidebar-narrow': isDesktop && sidebarNarrow }"
    >
        <div class="sidebar-header border-bottom">
            <Link href="/" class="sidebar-brand text-decoration-none" @click="closeSidebar">
                <span class="sidebar-brand-full fw-semibold fs-4">Life Log</span>
                <span class="sidebar-brand-narrow fw-semibold fs-4" aria-hidden="true">LL</span>
            </Link>
            <button
                type="button"
                class="btn-close btn-close-white d-lg-none"
                aria-label="Close navigation"
                @click="closeSidebar"
            ></button>
        </div>

        <nav class="sidebar-nav" aria-label="Primary navigation">
            <ul class="nav">
                <li v-for="item in navItems" :key="item.label" class="nav-item">
                    <Link
                        v-if="item.href !== '#'"
                        :href="item.href"
                        class="nav-link"
                        :class="{ active: item.active.value }"
                        :aria-current="item.active.value ? 'page' : undefined"
                        @click="closeSidebar"
                    >
                        <Icon :icon="item.icon" class="nav-icon" />
                        {{ item.label }}
                    </Link>
                    <a v-else :href="item.href" class="nav-link">
                        <Icon :icon="item.icon" class="nav-icon" />
                        {{ item.label }}
                    </a>
                </li>
            </ul>
        </nav>
    </div>

    <button
        v-if="sidebarOpen"
        type="button"
        class="sidebar-backdrop d-lg-none"
        aria-label="Close navigation"
        @click="closeSidebar"
    ></button>

    <div
        class="app-wrapper wrapper d-flex flex-column min-vh-100"
        :class="{ 'app-sidebar-narrow': isDesktop && sidebarNarrow }"
    >
        <header class="header header-sticky p-0 border-bottom">
            <div class="container-fluid px-3 px-lg-4 app-header-inner">
                <button
                    type="button"
                    class="header-toggler app-tap-target"
                    aria-label="Toggle navigation"
                    aria-controls="app-sidebar"
                    :aria-expanded="isSidebarExpanded"
                    @click="toggleSidebar"
                >
                    <Icon :icon="cilMenu" :size="28" />
                </button>

                <h1 class="h5 mb-0 ms-2 me-auto text-truncate">
                    {{ pageTitle }}
                </h1>

                <div class="header-nav align-items-center gap-1">
                    <div class="nav-item dropdown" data-app-menu>
                        <button
                            type="button"
                            class="btn btn-link nav-link app-tap-target"
                            aria-label="Choose color mode"
                            :aria-expanded="openMenu === 'theme'"
                            @click.stop="toggleMenu('theme')"
                        >
                            <Icon :icon="activeThemeIcon" />
                        </button>
                        <ul
                            class="dropdown-menu dropdown-menu-end"
                            :class="{ show: openMenu === 'theme' }"
                        >
                            <li v-for="option in themeOptions" :key="option.value">
                                <button
                                    type="button"
                                    class="dropdown-item d-flex align-items-center gap-2"
                                    :class="{ active: theme === option.value }"
                                    @click="selectTheme(option.value)"
                                >
                                    <Icon :icon="option.icon" :size="20" />
                                    {{ option.label }}
                                </button>
                            </li>
                        </ul>
                    </div>

                    <div class="vr my-2 text-body text-opacity-50"></div>

                    <div class="nav-item dropdown" data-app-menu>
                        <button
                            type="button"
                            class="btn btn-link nav-link app-user-toggle app-tap-target"
                            :aria-expanded="openMenu === 'user'"
                            @click.stop="toggleMenu('user')"
                        >
                            <span class="avatar avatar-md bg-body-secondary" aria-hidden="true">
                                <Icon :icon="cilUser" :size="22" />
                            </span>
                            <span class="d-none d-md-inline text-body fw-medium">{{
                                user.name
                            }}</span>
                            <Icon :icon="cilChevronBottom" :size="16" />
                        </button>
                        <div
                            class="dropdown-menu dropdown-menu-end app-user-menu"
                            :class="{ show: openMenu === 'user' }"
                        >
                            <div class="px-3 py-2">
                                <div class="fw-semibold text-body">{{ user.name }}</div>
                                <div class="small text-body-secondary text-break">
                                    {{ user.email }}
                                </div>
                            </div>
                            <div class="dropdown-divider"></div>
                            <Link
                                href="/logout"
                                method="post"
                                as="button"
                                class="dropdown-item d-flex align-items-center gap-2"
                            >
                                <Icon :icon="cilAccountLogout" :size="20" />
                                Log out
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <main class="body flex-grow-1 app-main">
            <div class="container-lg px-3 px-lg-4 py-4">
                <slot />
            </div>
        </main>
    </div>

    <nav class="mobile-navigation d-lg-none" aria-label="Mobile navigation">
        <Link
            href="/"
            class="mobile-navigation-item"
            :class="{ active: isTimelineActive }"
            :aria-current="isTimelineActive ? 'page' : undefined"
        >
            <Icon :icon="cilHome" />
            <span>Timeline</span>
        </Link>
        <Link
            href="/events/create"
            class="mobile-navigation-item mobile-navigation-capture"
            :class="{ active: isCaptureActive }"
            :aria-current="isCaptureActive ? 'page' : undefined"
        >
            <span class="mobile-navigation-capture-icon"><Icon :icon="cilPlus" :size="30" /></span>
            <span>Capture</span>
        </Link>
        <a href="#" class="mobile-navigation-item">
            <Icon :icon="cilChartLine" />
            <span>Stats</span>
        </a>
    </nav>
</template>
