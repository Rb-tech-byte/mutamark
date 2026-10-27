import { NavItem } from '@/types';
import { usePage } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { getSuperAdminMenu } from './menus/superadmin-menu';
import { getCompanyMenu } from './menus/company-menu';

// Pre-index package menu glob at module level
const allPackageModules = import.meta.glob('../../../packages/workdo/*/src/Resources/js/menus/*.ts', { eager: true });

// Get role-based core menu items
const getCoreMenuItems = (userRoles: string[], t: (key: string) => string): NavItem[] => {
    if (Array.isArray(userRoles) && userRoles.includes('superadmin')) {
        return getSuperAdminMenu(t) || [];
    }
    return getCompanyMenu(t) || [];
};

// Auto-load package menus based on activated packages
const getPackageMenuItems = (userRoles: string[], activatedPackages: string[], t: (key: string) => string): NavItem[] => {
    const menuItems: NavItem[] = [];
    const isSuperAdmin = Array.isArray(userRoles) && userRoles.includes('superadmin');
    const menuType = isSuperAdmin ? 'superadmin-menu' : 'company-menu';

    if (!Array.isArray(activatedPackages)) {
        return menuItems;
    }

    activatedPackages.forEach(packageName => {
        const menuPath = `../../../packages/workdo/${packageName}/src/Resources/js/menus/${menuType}.ts`;
        const module = allPackageModules[menuPath] as any;

        if (module) {
            Object.values(module).forEach((item: any) => {
                const result = typeof item === 'function' ? item(t) : item;
                if (Array.isArray(result)) {
                    menuItems.push(...result);
                } else if (result && typeof result === 'object') {
                    menuItems.push(result);
                }
            });
        }
    });

    return menuItems;
};

// Get custom menu items from database
const getCustomMenuItems = (userRoles: string[], t: (key: string) => string): NavItem[] => {
    const { auth } = usePage().props as any;
    const customMenus = Array.isArray(auth?.customMenus) ? auth.customMenus : [];
    
    return customMenus.map((menu: any) => ({
        ...menu,
        icon: null,
    }));
};

// Group menu items by parent and intelligently merge duplicates
const groupMenusByParent = (menuItems: NavItem[], packageMenuItems: NavItem[]): NavItem[] => {
    if (!Array.isArray(menuItems)) return [];
    if (!Array.isArray(packageMenuItems)) return menuItems;
    const groupedItems = [...menuItems];

    packageMenuItems.forEach(packageItem => {
        if (!packageItem) return;

        if (packageItem.parent) {
            const parentMenu = groupedItems.find(item =>
                item && item.name === packageItem.parent
            );

            if (parentMenu) {
                if (!Array.isArray(parentMenu.children)) {
                    parentMenu.children = [];
                }

                const alreadyHasChild = parentMenu.children.some(c =>
                    c && ((packageItem.href && c.href === packageItem.href) ||
                    (packageItem.title && c.title === packageItem.title))
                );

                if (!alreadyHasChild) {
                    parentMenu.children.push({
                        ...packageItem,
                        parent: undefined
                    });
                }

                if (Array.isArray(parentMenu.children)) {
                    parentMenu.children.sort((a, b) => ((a?.order || 999) - (b?.order || 999)));
                }
            } else {
                groupedItems.push(packageItem);
            }
        } else {
            const pkgTitle = packageItem.title ? packageItem.title.toLowerCase().trim() : '';
            const existingMenu = groupedItems.find(item => {
                if (!item) return false;
                if (packageItem.name && item.name === packageItem.name) return true;
                const itemTitle = item.title ? item.title.toLowerCase().trim() : '';
                if (pkgTitle && itemTitle === pkgTitle) return true;
                if (item.name === 'pos' && (pkgTitle === 'pos' || pkgTitle.includes('point of sale'))) return true;
                if (item.name === 'project' && (pkgTitle === 'project' || pkgTitle.includes('projects'))) return true;
                if (item.name === 'hrm' && (pkgTitle === 'hrm' || pkgTitle.includes('human resource'))) return true;
                if (item.name === 'accounting' && pkgTitle === 'accounting') return true;
                if (item.name === 'crm' && pkgTitle === 'crm') return true;
                if (item.name === 'mobile-service' && (pkgTitle === 'mobile service' || packageItem.name === 'mobile-service')) return true;
                if (item.name === 'product-service' && (pkgTitle === 'product & service' || packageItem.name === 'product-service')) return true;
                return false;
            });

            if (existingMenu) {
                if (Array.isArray(packageItem.children) && packageItem.children.length > 0) {
                    if (!Array.isArray(existingMenu.children)) {
                        existingMenu.children = [];
                    }
                    packageItem.children.forEach(pkgChild => {
                        if (!pkgChild) return;
                        const childExists = existingMenu.children?.some(c =>
                            c && ((pkgChild.href && c.href === pkgChild.href) ||
                            (pkgChild.title && c.title === pkgChild.title))
                        );
                        if (!childExists) {
                            existingMenu.children?.push(pkgChild);
                        }
                    });
                    if (Array.isArray(existingMenu.children)) {
                        existingMenu.children.sort((a, b) => ((a?.order || 999) - (b?.order || 999)));
                    }
                }
            } else {
                groupedItems.push(packageItem);
            }
        }
    });

    return groupedItems;
};

// Remove items whose add-on (item.module) is disabled in the super admin module manager.
// Parents emptied by this filter (and without their own link) are removed as well.
const filterByActiveModule = (items: NavItem[], activeModuleSet: Set<string>): NavItem[] => {
    if (!Array.isArray(items)) return [];
    return items.filter(item => {
        if (!item) return false;
        if (item.module && !activeModuleSet.has(item.module)) {
            return false;
        }
        if (Array.isArray(item.children)) {
            const hadChildren = item.children.length > 0;
            item.children = filterByActiveModule(item.children, activeModuleSet);
            if (hadChildren && item.children.length === 0 && !item.href) {
                return false;
            }
        }
        return true;
    });
};

// Filter menu items based on permissions with O(1) Set lookup
const filterByPermission = (items: NavItem[], permissionSet: Set<string>): NavItem[] => {
    if (!Array.isArray(items)) return [];
    return items.filter(item => {
        if (!item) return false;

        // If item has children, recursively filter them
        if (Array.isArray(item.children)) {
            item.children = filterByPermission(item.children, permissionSet);
        }

        // If no permission specified, item is visible
        if (!item.permission) {
            return true;
        }

        // Check permission
        if (!permissionSet.has(item.permission)) {
            return false;
        }

        return true;
    });
};

// Global cache for computed menu
let cachedMenuKey = '';
let cachedMenuResult: NavItem[] = [];

// Main function to get filtered menu items
export const allMenuItems = (): NavItem[] => {
    const { auth } = usePage().props as any;
    const { t, i18n } = useTranslation();
    const userPermissions: string[] = Array.isArray(auth?.user?.permissions) ? auth.user.permissions : [];
    const userRoles: string[] = Array.isArray(auth?.user?.roles) ? auth.user.roles : [];
    const activatedPackages: string[] = Array.isArray(auth?.user?.activatedPackages) ? auth.user.activatedPackages : [];
    const userId = auth?.user?.id || 'guest';
    const currentLang = i18n?.language || 'en';

    const cacheKey = `${userId}_${currentLang}_${userRoles.join(',')}_${activatedPackages.join(',')}_${userPermissions.length}`;
    if (cachedMenuKey === cacheKey && Array.isArray(cachedMenuResult) && cachedMenuResult.length > 0) {
        return cachedMenuResult;
    }

    const coreMenuItems = filterByActiveModule(getCoreMenuItems(userRoles, t), new Set(activatedPackages));
    const packageMenuItems = getPackageMenuItems(userRoles, activatedPackages, t);
    const customMenuItems = getCustomMenuItems(userRoles, t);
    
    // Separate custom menus into parents and children
    const customParentMenus = customMenuItems.filter(menu => !menu.parent);
    const customChildMenus = customMenuItems.filter(menu => menu.parent);
    
    // First add custom parent menus to core menus
    const coreWithCustomParents = [...coreMenuItems, ...customParentMenus];
    
    // Then group all children (package + custom children) with their parents
    const allChildMenus = [...packageMenuItems, ...customChildMenus];
    const finalGroupedMenuItems = groupMenusByParent(coreWithCustomParents, allChildMenus);

    const sortedMenuItems = finalGroupedMenuItems.sort((a, b) => ((a?.order || 999) - (b?.order || 999)));

    const permissionSet = new Set(userPermissions);
    const finalMenuItems = filterByPermission(sortedMenuItems, permissionSet);

    cachedMenuKey = cacheKey;
    cachedMenuResult = finalMenuItems;
    return finalMenuItems;
};