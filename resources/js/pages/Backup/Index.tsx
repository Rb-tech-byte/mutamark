import React, { useState } from 'react';
import { Head, useForm, router } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import AuthenticatedLayout from "@/layouts/authenticated-layout";
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/components/ui/card";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter, DialogDescription } from "@/components/ui/dialog";
import { Badge } from '@/components/ui/badge';
import {
    Database,
    Download,
    RotateCcw,
    Trash2,
    Plus,
    HardDrive,
    Server,
    ShieldCheck,
    AlertTriangle,
    Clock,
    CheckCircle2,
    RefreshCw
} from 'lucide-react';

interface BackupItem {
    filename: string;
    size_human: string;
    size_bytes: number;
    created_at: string;
    age: string;
}

interface Props {
    backups: BackupItem[];
    system_info: {
        database_name: string;
        tables_count: number;
        backup_directory: string;
        total_backups: number;
        latest_backup: string | null;
        disk_free_space: string;
    };
}

export default function BackupIndex({ backups, system_info }: Props) {
    const { t } = useTranslation();
    const [isCreating, setIsCreating] = useState(false);
    const [selectedBackup, setSelectedBackup] = useState<BackupItem | null>(null);
    const [restoreModalOpen, setRestoreModalOpen] = useState(false);
    const [deleteModalOpen, setDeleteModalOpen] = useState(false);
    const [isRestoring, setIsRestoring] = useState(false);

    const handleCreateBackup = () => {
        setIsCreating(true);
        router.post(route('backups.create'), {}, {
            onFinish: () => setIsCreating(false),
        });
    };

    const confirmRestore = () => {
        if (!selectedBackup) return;
        setIsRestoring(true);
        router.post(route('backups.restore', selectedBackup.filename), {}, {
            onFinish: () => {
                setIsRestoring(false);
                setRestoreModalOpen(false);
                setSelectedBackup(null);
            },
        });
    };

    const confirmDelete = () => {
        if (!selectedBackup) return;
        router.delete(route('backups.destroy', selectedBackup.filename), {
            onFinish: () => {
                setDeleteModalOpen(false);
                setSelectedBackup(null);
            },
        });
    };

    return (
        <AuthenticatedLayout>
            <Head title={t('Database Backups & Disaster Recovery')} />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <div className="flex items-center gap-2">
                            <Badge className="bg-primary/10 text-primary border-primary/20 text-xs">
                                {t('System Administration & Disaster Recovery')}
                            </Badge>
                        </div>
                        <h1 className="text-2xl font-bold tracking-tight text-foreground mt-1 flex items-center gap-2">
                            <Database className="h-7 w-7 text-primary" />
                            {t('Database Backups & Recovery')}
                        </h1>
                        <p className="text-muted-foreground text-sm">
                            {t('Encrypted database snapshots, integrity verification and administrator recovery')}
                        </p>
                    </div>

                    <div className="flex items-center gap-2">
                        <Button
                            onClick={handleCreateBackup}
                            disabled={isCreating}
                            className="flex items-center gap-2 bg-primary hover:bg-primary/90 text-primary-foreground font-semibold"
                        >
                            {isCreating ? (
                                <RefreshCw className="h-4 w-4 animate-spin" />
                            ) : (
                                <Plus className="h-4 w-4" />
                            )}
                            {t('Create Full Backup Now')}
                        </Button>
                    </div>
                </div>

                {/* System Diagnostics Cards */}
                <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <Card className="shadow-sm border-l-4 border-l-blue-500">
                        <CardContent className="p-4">
                            <div className="flex items-center justify-between">
                                <span className="text-xs font-semibold text-muted-foreground uppercase">{t('Database')}</span>
                                <Server className="h-4 w-4 text-blue-500" />
                            </div>
                            <div className="text-xl font-bold mt-2 font-mono text-foreground">{system_info.database_name}</div>
                            <div className="text-xs text-muted-foreground mt-1">{system_info.tables_count} {t('relational tables')}</div>
                        </CardContent>
                    </Card>

                    <Card className="shadow-sm border-l-4 border-l-emerald-500">
                        <CardContent className="p-4">
                            <div className="flex items-center justify-between">
                                <span className="text-xs font-semibold text-muted-foreground uppercase">{t('Snapshots Stored')}</span>
                                <Database className="h-4 w-4 text-emerald-500" />
                            </div>
                            <div className="text-xl font-bold mt-2 text-emerald-600 dark:text-emerald-400">{system_info.total_backups} {t('files')}</div>
                            <div className="text-xs text-muted-foreground mt-1">{t('Storage: storage/app/backups')}</div>
                        </CardContent>
                    </Card>

                    <Card className="shadow-sm border-l-4 border-l-purple-500">
                        <CardContent className="p-4">
                            <div className="flex items-center justify-between">
                                <span className="text-xs font-semibold text-muted-foreground uppercase">{t('Latest Snapshot')}</span>
                                <Clock className="h-4 w-4 text-purple-500" />
                            </div>
                            <div className="text-base font-bold mt-2 truncate">
                                {system_info.latest_backup || t('None Created')}
                            </div>
                            <div className="text-xs text-muted-foreground mt-1">{t('Point-in-time state')}</div>
                        </CardContent>
                    </Card>

                    <Card className="shadow-sm border-l-4 border-l-teal-500">
                        <CardContent className="p-4">
                            <div className="flex items-center justify-between">
                                <span className="text-xs font-semibold text-muted-foreground uppercase">{t('Free Storage')}</span>
                                <HardDrive className="h-4 w-4 text-teal-500" />
                            </div>
                            <div className="text-xl font-bold mt-2 text-teal-600 dark:text-teal-400">{system_info.disk_free_space}</div>
                            <div className="text-xs text-muted-foreground mt-1">{t('Available drive capacity')}</div>
                        </CardContent>
                    </Card>
                </div>

                {/* Backups Table */}
                <Card className="shadow-sm">
                    <CardHeader className="pb-3 flex flex-row items-center justify-between">
                        <div>
                            <CardTitle className="text-base font-semibold">{t('Backup Archives & Snapshots')}</CardTitle>
                            <CardDescription>{t('Download, restore or manage SQL database snapshot files')}</CardDescription>
                        </div>
                    </CardHeader>
                    <CardContent className="p-0">
                        <div className="overflow-x-auto">
                            <Table>
                                <TableHeader>
                                    <TableRow className="bg-muted/40">
                                        <TableHead>{t('File Name')}</TableHead>
                                        <TableHead>{t('Size')}</TableHead>
                                        <TableHead>{t('Timestamp')}</TableHead>
                                        <TableHead>{t('Age')}</TableHead>
                                        <TableHead>{t('Integrity')}</TableHead>
                                        <TableHead className="text-right">{t('Actions')}</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {backups.length === 0 ? (
                                        <TableRow>
                                            <TableCell colSpan={6} className="text-center py-10 text-muted-foreground">
                                                <div className="flex flex-col items-center justify-center space-y-2">
                                                    <Database className="h-10 w-10 text-muted-foreground/50" />
                                                    <p>{t('No database backups generated yet.')}</p>
                                                    <Button size="sm" onClick={handleCreateBackup} disabled={isCreating}>
                                                        <Plus className="h-4 w-4 mr-1" />
                                                        {t('Create Your First Backup')}
                                                    </Button>
                                                </div>
                                            </TableCell>
                                        </TableRow>
                                    ) : (
                                        backups.map((b) => (
                                            <TableRow key={b.filename} className="hover:bg-muted/30">
                                                <TableCell>
                                                    <div className="font-mono text-sm font-semibold text-primary">{b.filename}</div>
                                                </TableCell>
                                                <TableCell className="font-medium text-xs">{b.size_human}</TableCell>
                                                <TableCell className="text-xs text-muted-foreground">{b.created_at}</TableCell>
                                                <TableCell className="text-xs">{b.age}</TableCell>
                                                <TableCell>
                                                    <Badge className="bg-emerald-600/90 text-white text-[11px] font-medium flex items-center gap-1 w-fit">
                                                        <CheckCircle2 className="h-3 w-3" />
                                                        {t('Ready')}
                                                    </Badge>
                                                </TableCell>
                                                <TableCell className="text-right space-x-1">
                                                    <a
                                                        href={route('backups.download', b.filename)}
                                                        className="inline-flex items-center justify-center rounded-md text-xs font-medium border border-input bg-background hover:bg-accent hover:text-accent-foreground h-8 px-2.5 transition-colors"
                                                    >
                                                        <Download className="h-3.5 w-3.5 mr-1" />
                                                        {t('Download')}
                                                    </a>

                                                    <Button
                                                        size="sm"
                                                        variant="outline"
                                                        className="text-amber-600 border-amber-300 hover:bg-amber-50 h-8 text-xs"
                                                        onClick={() => {
                                                            setSelectedBackup(b);
                                                            setRestoreModalOpen(true);
                                                        }}
                                                    >
                                                        <RotateCcw className="h-3.5 w-3.5 mr-1" />
                                                        {t('Restore')}
                                                    </Button>

                                                    <Button
                                                        size="sm"
                                                        variant="ghost"
                                                        className="text-rose-600 hover:bg-rose-50 h-8 text-xs"
                                                        onClick={() => {
                                                            setSelectedBackup(b);
                                                            setDeleteModalOpen(true);
                                                        }}
                                                    >
                                                        <Trash2 className="h-3.5 w-3.5" />
                                                    </Button>
                                                </TableCell>
                                            </TableRow>
                                        ))
                                    )}
                                </TableBody>
                            </Table>
                        </div>
                    </CardContent>
                </Card>
            </div>

            {/* RESTORE CONFIRMATION DIALOG */}
            <Dialog open={restoreModalOpen} onOpenChange={setRestoreModalOpen}>
                <DialogContent className="max-w-md">
                    <DialogHeader>
                        <DialogTitle className="flex items-center gap-2 text-amber-600">
                            <AlertTriangle className="h-5 w-5" />
                            {t('Confirm Database Restore')}
                        </DialogTitle>
                        <DialogDescription>
                            {t('Are you sure you want to restore the database from snapshot:')} <strong className="font-mono">{selectedBackup?.filename}</strong>?
                        </DialogDescription>
                    </DialogHeader>
                    <div className="p-3 bg-amber-50 dark:bg-amber-950/40 border border-amber-200 rounded-lg text-amber-800 dark:text-amber-300 text-xs space-y-1">
                        <div className="font-semibold">{t('Warning: High Impact Operation')}</div>
                        <p>{t('Restoring this snapshot will replace active table records with the contents of this backup file. All recent changes made after this backup date will be overwritten.')}</p>
                    </div>
                    <DialogFooter className="pt-2">
                        <Button variant="outline" onClick={() => setRestoreModalOpen(false)}>
                            {t('Cancel')}
                        </Button>
                        <Button
                            variant="destructive"
                            onClick={confirmRestore}
                            disabled={isRestoring}
                            className="bg-amber-600 hover:bg-amber-700"
                        >
                            {isRestoring ? <RefreshCw className="h-4 w-4 animate-spin mr-1" /> : <RotateCcw className="h-4 w-4 mr-1" />}
                            {t('Proceed with Restore')}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            {/* DELETE CONFIRMATION DIALOG */}
            <Dialog open={deleteModalOpen} onOpenChange={setDeleteModalOpen}>
                <DialogContent className="max-w-md">
                    <DialogHeader>
                        <DialogTitle>{t('Delete Backup Snapshot')}</DialogTitle>
                        <DialogDescription>
                            {t('Permanently remove backup file:')} <strong className="font-mono">{selectedBackup?.filename}</strong>?
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter className="pt-2">
                        <Button variant="outline" onClick={() => setDeleteModalOpen(false)}>
                            {t('Cancel')}
                        </Button>
                        <Button variant="destructive" onClick={confirmDelete}>
                            <Trash2 className="h-4 w-4 mr-1" />
                            {t('Delete Permanently')}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </AuthenticatedLayout>
    );
}
