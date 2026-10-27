import { useState } from 'react';
import { Head, usePage, router } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { useFlashMessages } from '@/hooks/useFlashMessages';
import AuthenticatedLayout from "@/layouts/authenticated-layout";
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from "@/components/ui/card";
import { Package, Power, PowerOff, Eye, CheckCircle2 } from "lucide-react";
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from "@/components/ui/tooltip";
import { SearchInput } from "@/components/ui/search-input";
import NoRecordsFound from '@/components/no-records-found';
import { ModulesIndexProps, Module } from './types';
import { getPackageFavicon, getPackageAlias } from '@/utils/helpers';

import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from "@/components/ui/dialog";

export default function Index() {
    const { modules, auth } = usePage<ModulesIndexProps>().props;
    const { t } = useTranslation();

    const [searchTerm, setSearchTerm] = useState('');
    const [selectedModule, setSelectedModule] = useState<Module | null>(null);
    const [isDetailsOpen, setIsDetailsOpen] = useState(false);

    useFlashMessages();

    const filteredModules = modules.filter(module =>
        module.display !== false &&
        (module.alias.toLowerCase().includes(searchTerm.toLowerCase()) ||
        module.description.toLowerCase().includes(searchTerm.toLowerCase()))
    );

    const activeCount = modules.filter(m => m.display !== false && m.is_enabled).length;
    const totalCount = modules.filter(m => m.display !== false).length;

    const handleToggleModule = (moduleName: string, isEnabled: boolean) => {
        router.post(route('add-on.enable', moduleName), {}, {
            preserveState: true,
        });
    };

    const handleViewDetails = (module: Module) => {
        setSelectedModule(module);
        setIsDetailsOpen(true);
    };

    return (
        <AuthenticatedLayout
            breadcrumbs={[{label: t('Modules')}]}
            pageTitle={t('System Modules')}
        >
            <Head title={t('System Modules - Mutamark ERP')} />

            <Card>
                <CardHeader className="pb-3">
                    <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-3">
                        <div>
                            <h3 className="font-semibold text-lg text-gray-900">{t('Core & Business Modules')}</h3>
                            <p className="text-xs text-gray-500 mt-0.5">
                                {t('Native codebase enterprise modules for Mutamark Enterprises Ltd')}
                            </p>
                        </div>
                        <div className="flex items-center gap-2">
                            <div className="flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-700 text-xs font-semibold">
                                <CheckCircle2 className="h-3.5 w-3.5" />
                                <span>{activeCount} / {totalCount} {t('Active')}</span>
                            </div>
                        </div>
                    </div>
                    <SearchInput
                        value={searchTerm}
                        onChange={setSearchTerm}
                        onSearch={() => {}}
                        placeholder={t('Search system modules...')}
                        className="w-full"
                    />
                </CardHeader>

                <CardContent>
                    {filteredModules.length > 0 ? (
                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-6 gap-4">
                            {filteredModules.map((module) => (
                                <Card key={module.name} className="relative hover:shadow-lg transition-all duration-200 border border-gray-200 flex flex-col">
                                    <div className="p-4 flex-1 flex flex-col">
                                        <div className="flex items-start justify-between mb-3">
                                            <div className="flex items-center gap-3">
                                                <div className="relative">
                                                    <img
                                                        src={getPackageFavicon(module.name)}
                                                        alt={getPackageAlias(module.name)}
                                                        className="h-10 w-10 object-contain rounded-lg"
                                                        onError={(e) => {
                                                            const target = e.target as HTMLImageElement;
                                                            target.style.display = 'none';
                                                            target.nextElementSibling?.classList.remove('hidden');
                                                        }}
                                                    />
                                                    <Package className="h-10 w-10 text-primary hidden" />
                                                </div>
                                            </div>
                                            <div className="flex items-center gap-1 flex-shrink-0">
                                                <span className={`px-2 py-0.5 rounded-md text-xs font-medium whitespace-nowrap ${
                                                    module.is_enabled
                                                        ? 'bg-emerald-600 text-white'
                                                        : 'bg-gray-400 text-white'
                                                }`}>
                                                    {module.is_enabled ? t('Active') : t('Inactive')}
                                                </span>
                                            </div>
                                        </div>

                                        <div className="mb-4">
                                            <h3 className="font-semibold text-gray-900 text-sm mb-1 line-clamp-2">{module.alias}</h3>
                                            <p className="text-xs text-gray-500 line-clamp-2">{module.description || t('Enterprise module component')}</p>
                                        </div>

                                        <div className="mt-auto flex gap-2">
                                            <Button
                                                variant="outline"
                                                size="sm"
                                                onClick={() => handleViewDetails(module)}
                                                className="flex-1 h-8 text-xs"
                                            >
                                                <Eye className="mr-1 h-3 w-3" />
                                                {t('Details')}
                                            </Button>
                                            {auth.user?.permissions?.includes('manage-actions') && (
                                                <TooltipProvider>
                                                    <Tooltip delayDuration={0}>
                                                        <TooltipTrigger asChild>
                                                            <Button
                                                                variant="outline"
                                                                size="sm"
                                                                onClick={() => handleToggleModule(module.name, module.is_enabled)}
                                                                className={`h-8 px-2 ${module.is_enabled ? 'bg-red-50 hover:bg-red-100 border-red-200' : 'bg-green-50 hover:bg-green-100 border-green-200'}`}
                                                            >
                                                                {module.is_enabled ? (
                                                                    <PowerOff className="h-3 w-3 text-red-600" />
                                                                ) : (
                                                                    <Power className="h-3 w-3 text-green-600" />
                                                                )}
                                                            </Button>
                                                        </TooltipTrigger>
                                                        <TooltipContent>
                                                            <p>{module.is_enabled ? t('Disable Module') : t('Enable Module')}</p>
                                                        </TooltipContent>
                                                    </Tooltip>
                                                </TooltipProvider>
                                            )}
                                        </div>
                                    </div>
                                </Card>
                            ))}
                        </div>
                    ) : (
                        <NoRecordsFound
                            icon={Package}
                            title={t('No modules found')}
                            description={searchTerm ? t('No modules match your search criteria.') : t('No modules are installed.')}
                            hasFilters={!!searchTerm}
                            onClearFilters={() => setSearchTerm('')}
                        />
                    )}
                </CardContent>
            </Card>

            <Dialog open={isDetailsOpen} onOpenChange={setIsDetailsOpen}>
                <DialogContent className="max-w-md">
                    <DialogHeader>
                        <DialogTitle className="flex items-center gap-3">
                            <img
                                src={selectedModule?.image}
                                alt={selectedModule?.alias}
                                className="h-8 w-8 object-contain rounded"
                                onError={(e) => {
                                    const target = e.target as HTMLImageElement;
                                    target.style.display = 'none';
                                    target.nextElementSibling?.classList.remove('hidden');
                                }}
                            />
                            <Package className="h-8 w-8 text-primary hidden" />
                            {selectedModule?.alias}
                        </DialogTitle>
                        <DialogDescription>
                            {selectedModule?.description || t('Enterprise module component')}
                        </DialogDescription>
                    </DialogHeader>
                    <div className="space-y-4">
                        <div className="text-sm">
                            <span className="font-medium text-gray-600">{t('Module Status')}:</span>
                            <p className={`font-semibold mt-0.5 ${
                                selectedModule?.is_enabled ? 'text-emerald-600' : 'text-gray-500'
                            }`}>
                                {selectedModule?.is_enabled ? t('Active') : t('Inactive')}
                            </p>
                        </div>
                        {auth.user?.permissions?.includes('manage-actions') && (
                            <div className="flex gap-2 pt-4 border-t">
                                <Button
                                    variant="outline"
                                    size="sm"
                                    onClick={() => {
                                        if (selectedModule) {
                                            handleToggleModule(selectedModule.name, selectedModule.is_enabled);
                                            setIsDetailsOpen(false);
                                        }
                                    }}
                                    className={`flex-1 ${selectedModule?.is_enabled ? 'bg-red-50 hover:bg-red-100 border-red-200 text-red-600' : 'bg-emerald-50 hover:bg-emerald-100 border-emerald-200 text-emerald-600'}`}
                                >
                                    {selectedModule?.is_enabled ? (
                                        <>
                                            <PowerOff className="mr-2 h-4 w-4" />
                                            {t('Disable Module')}
                                        </>
                                    ) : (
                                        <>
                                            <Power className="mr-2 h-4 w-4" />
                                            {t('Enable Module')}
                                        </>
                                    )}
                                </Button>
                            </div>
                        )}
                    </div>
                </DialogContent>
            </Dialog>
        </AuthenticatedLayout>
    );
}